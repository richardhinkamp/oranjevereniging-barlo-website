<?php

namespace Cowb\Controllers;

use Silex;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;

function getMollie() {
    $mollie = new MollieApiClient();
    $mollie->setApiKey(require(__DIR__ . '/../../../config/mollie.php'));
    return $mollie;
}

/**
 * Standard Frontend actions
 *
 * Strictly speaking this is no longer a controller, but logically
 * it still is.
 */
class Frontend
{
    public static function emailconfirm(Silex\Application $app, $data, $hash)
    {
        list($name, $email) = explode('#', base64_decode($data));
        if( md5(strtoupper($email)) == $hash ) {
            $confirm = new \Bolt\Content($app, 'emails', array());
            $confirm->values['email'] = $email;
            $confirm->values['confirmed'] = date("Y-m-d H:i:s");
            $app['storage']->saveContent($confirm);
            
            $app['twig']->addGlobal('name', $name);
            $app['twig']->addGlobal('email', $email);
            return $app['render']->render('emailconfirm.twig');
        } else {
            return $app['render']->render('emailconfirmfailed.twig');
        }
    }
    
    public static function sponsor(Silex\Application $app)
    {
        if($app['request']->server->get('REQUEST_METHOD') == 'POST') {
            $name = $app['request']->request->get('name');
            $email = $app['request']->request->get('email');
            $bouwstenen = intval($app['request']->request->get('bouwstenen'));
            $gift = intval($app['request']->request->get('gift'));
            $amount = ($bouwstenen * 50) + $gift;
            if(trim($name) != '' && trim($email) != '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $bouwstenen >=0 && $gift >= 0 && $amount > 0) {
                if(intval($app['request']->request->get('confirm')) == 1) {
                    $added = $app['request']->request->get('added');
                    $item = new \Bolt\Content($app, 'sponsoren', array());
                    $item->values['name'] = $name;
                    $item->values['email'] = $email;
                    $item->values['bouwstenen'] = $bouwstenen;
                    $item->values['gift'] = $gift;
                    $item->values['amount'] = $amount;
                    $item->values['added'] = $added;
                    $item->values['confirmed'] = date("Y-m-d H:i:s");
                    $id = $app['storage']->saveContent($item);
                    $mollie = getMollie();
                    $payment = $mollie->payments->create([
                        "amount" => [
                            "currency" => "EUR",
                            "value" => (string)$amount . '.00',
                        ],
                        "description" => "Bijdrage Groot Deunk",
                        "redirectUrl" => "http://oranjeverenigingbarlo.local/pay/return/" . $id . '/' . md5(join('#', array($name, $email, $bouwstenen, $gift, $added))),
                        // "webhookUrl"  => "http://oranjeverenigingbarlo.local/pay/webhook/",
                    ]);
                    $item->values['payment_id'] = $payment->id;
                    $item->values['payment_status'] = $payment->status;
                    $app['storage']->saveContent($item);
                    $payment->redirectUrl .= '/' . $id . '/' . md5($id . '#' . $payment->id);
                    header("Location: " . $payment->getCheckoutUrl(), true, 303);
                    die();
                    $app['storage']->saveContent($item);
                    return $app['render']->render('sponsor-bedankt.twig');
                } else {
                    $app['twig']->addGlobal('name', $name);
                    $app['twig']->addGlobal('email', $email);
                    $app['twig']->addGlobal('bouwstenen', $bouwstenen);
                    $app['twig']->addGlobal('gift', $gift);
                    $app['twig']->addGlobal('amount', $amount);
                    $app['twig']->addGlobal('added', date("Y-m-d H:i:s"));
                    return $app['render']->render('sponsor-overzicht.twig');
                }
            } else {
                $app['twig']->addGlobal('error', true);
                $app['twig']->addGlobal('name', $name);
                $app['twig']->addGlobal('email', $email);
                $app['twig']->addGlobal('bouwstenen', $bouwstenen > 0 ? $bouwstenen : '');
                $app['twig']->addGlobal('gift', $gift > 0 ? $gift : '');
                return $app['render']->render('sponsor.twig');
            }
        } else {
            $app['twig']->addGlobal('error', false);
            $app['twig']->addGlobal('name', '');
            $app['twig']->addGlobal('email', '');
            $app['twig']->addGlobal('bouwstenen', '');
            $app['twig']->addGlobal('gift', '');
            return $app['render']->render('sponsor.twig');
        }
    }
    
    public static function payReturnId(Silex\Application $app, $id, $hash)
    {
        $record = $app['db']->fetchAssoc("SELECT * FROM bolt_sponsoren WHERE id = ?", array(intval($id)));
        if($record) {
            $recordHash = md5(join('#', array($record['name'], $record['email'], $record['bouwstenen'], $record['gift'], $record['added'])));
            if($hash == $recordHash) {
                $item = new \Bolt\Content($app, 'sponsoren', $record);
                $mollie = getMollie();
                $payment = $mollie->payments->get($record['payment_id']);
                if($payment) {
                    $item->values['payment_status'] = $payment->status;
                    $status = $payment->status;
                    $app['storage']->saveContent($item);
                    if($status == PaymentStatus::STATUS_PAID) {
                        return $app['render']->render('sponsor-bedankt.twig');
                    } else if($status == PaymentStatus::STATUS_FAILED || $status == PaymentStatus::STATUS_CANCELED) {
                        return $app['render']->render('sponsor-failed.twig');
                    } else {
                        return $app['render']->render('sponsor-pending.twig');
                    }
                }
            }
        }
        header("Location: /sponsor", true, 301);
        die();
    }
    
    public static function payReturn(Silex\Application $app)
    {
        return $app['render']->render('sponsor-pending.twig');
    }
}
