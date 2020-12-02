<?php

namespace Cowb\Controllers;

use Silex;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;

function getMollie(Silex\Application $app) {
    $mollie = new MollieApiClient();
    $mollie->setApiKey($app['config']->get('general/sponsor/mollie_api_key'));
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
    
    public static function sponsor(Silex\Application $app, $data = null)
    {
        $intentieId = $name = $email = $bouwstenen = $gift = '';
        $validData = false;
        if($data) {
            $parts = explode('|', $data);
            if(
                count($parts) == 5 && 
                is_numeric($parts[0]) && 
                $parts[1] != '' && 
                filter_var($parts[2], FILTER_VALIDATE_EMAIL) && 
                ($parts[3] == '' || is_numeric($parts[3])) && 
                ($parts[4] == '' || is_numeric($parts[4]))
            ) {
                $intentieId = intval($parts[0]);
                $name = $parts[1];
                $email = $parts[2];
                $bouwstenen = $parts[3];
                $gift = $parts[4];
                $validData = true;
            }
        }

        if($app['request']->server->get('REQUEST_METHOD') == 'POST') {
            $intentieId = $app['request']->request->get('intentie_id');
            $name = $app['request']->request->get('name');
            $email = $app['request']->request->get('email');
            $bouwstenen = intval($app['request']->request->get('bouwstenen'));
            $gift = intval($app['request']->request->get('gift'));
            $amount = ($bouwstenen * 50) + $gift;
            if(trim($name) != '' && trim($email) != '' && filter_var($email, FILTER_VALIDATE_EMAIL) && $bouwstenen >=0 && $gift >= 0 && $amount > 0) {
                if(intval($app['request']->request->get('confirm')) == 1) {
                    $added = $app['request']->request->get('added');
                    $item = new \Bolt\Content($app, 'sponsoren', array());
                    $item->values['intentie_id'] = $intentieId > 0 ? $intentieId : null;
                    $item->values['name'] = $name;
                    $item->values['email'] = $email;
                    $item->values['bouwstenen'] = $bouwstenen;
                    $item->values['gift'] = $gift;
                    $item->values['amount'] = $amount;
                    $item->values['added'] = $added;
                    $item->values['confirmed'] = date("Y-m-d H:i:s");
                    $id = $app['storage']->saveContent($item);
                    $mollie = getMollie($app);
                    $description = "Bijdrage Groot Deunk";
                    if($bouwstenen > 0) {
                        $description .= " - $bouwstenen bouwstenen à € 50,- (renteloze lening)";
                    }
                    if($gift > 0) {
                        $description .= " - gift € $gift";
                    }
                    $payment = $mollie->payments->create([
                        "amount" => [
                            "currency" => "EUR",
                            "value" => (string)$amount . '.00',
                        ],
                        "description" => $description,
                        "redirectUrl" => $app['config']->get('general/sponsor/hostname') . "/pay/return/" . $id . '/' . md5(join('#', array($id, $name, $email, $bouwstenen, $gift, $added))),
                        // "webhookUrl"  => "http://oranjeverenigingbarlo.local/pay/webhook/",
                    ]);
                    $item->values['payment_id'] = $payment->id;
                    $item->values['payment_status'] = $payment->status;
                    $app['storage']->saveContent($item);
                    $payment->redirectUrl .= '/' . $id . '/' . md5($id . '#' . $payment->id);
                    header("Location: " . $payment->getCheckoutUrl(), true, 303);
                    die();
                } else {
                    $app['twig']->addGlobal('intentie_id', $intentieId);
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
                $errorMsgs = array();
                if(trim($name) == '') {
                    $errorMsgs[] = 'Vul je naam in.';
                }
                if(trim($email) == '') {
                    $errorMsgs[] = 'Vul je e-mail adres in.';
                } else if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errorMsgs[] = 'Vul een geldig e-mail adres in.';
                }
                if($bouwstenen <= 0 && $gift <= 0) {
                    $errorMsgs[] = 'Vul een aantal bouwstenen of een vrije gift in.';
                }
                $app['twig']->addGlobal('error_messages', join(' ', $errorMsgs));
                $app['twig']->addGlobal('with_data', $validData);
                $app['twig']->addGlobal('intentie_id', $intentieId);
                $app['twig']->addGlobal('name', $name);
                $app['twig']->addGlobal('email', $email);
                $app['twig']->addGlobal('bouwstenen', $bouwstenen > 0 ? $bouwstenen : '');
                $app['twig']->addGlobal('gift', $gift > 0 ? $gift : '');
                return $app['render']->render('sponsor.twig');
            }
        } else {
            $app['twig']->addGlobal('error', false);
            $app['twig']->addGlobal('error_messages', '');
            $app['twig']->addGlobal('with_data', $validData);
            $app['twig']->addGlobal('intentie_id', $intentieId);
            $app['twig']->addGlobal('name', $name);
            $app['twig']->addGlobal('email', $email);
            $app['twig']->addGlobal('bouwstenen', $bouwstenen);
            $app['twig']->addGlobal('gift', $gift);
            return $app['render']->render('sponsor.twig');
        }
    }
    
    public static function payReturnId(Silex\Application $app, $id, $hash)
    {
        $record = $app['db']->fetchAssoc("SELECT * FROM bolt_sponsoren WHERE id = ?", array(intval($id)));
        if($record) {
            $recordHash = md5(join('#', array($id, $record['name'], $record['email'], $record['bouwstenen'], $record['gift'], $record['added'])));
            if($hash == $recordHash) {
                $item = new \Bolt\Content($app, 'sponsoren', $record);
                $mollie = getMollie($app);
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
        header("Location: /bouwstenen", true, 301);
        die();
    }
    
    public static function payReturn(Silex\Application $app)
    {
        return $app['render']->render('sponsor-pending.twig');
    }
}
