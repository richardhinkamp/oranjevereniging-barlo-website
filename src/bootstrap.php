<?php
/*
 * This file is part of the Bolt skeleton package.
 *
 * (c) Richard Hinkamp <richard@hinkamp.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(__DIR__ . '/../vendor/zend/gdata/library'),
    get_include_path(),
)));

require_once( __DIR__ . '/../vendor/bolt/bolt/app/bootstrap.php' );
