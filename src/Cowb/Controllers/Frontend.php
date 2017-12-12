<?php

namespace Cowb\Controllers;

use Silex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
}
