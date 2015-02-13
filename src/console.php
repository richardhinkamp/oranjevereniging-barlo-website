#!/usr/bin/env php
<?php

require_once( 'bootstrap.php' );

use Symfony\Component\Console\Application;

$application = new Application();

$application->setName("Oranjevereniging console tool");
$application->setVersion($app->getVersion());
$application->add(new BoltPicasa\Console\SyncPicasa($app));
$application->run();
