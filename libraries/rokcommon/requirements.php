<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
$errors = array();
if (version_compare(PHP_VERSION, '5.2.8') < 0) {
    $errors[] = 'Needs a minimum PHP version of 5.2.8.  You are running PHP version ' . PHP_VERSION;
}

if (!phpversion('PDO'))
    $errors[] = 'The PHP PDO module is needed but not installed.';

if (!phpversion('pdo_mysql'))
    $errors[] = 'The PHP MySQL PDO driver is needed but not installed.';

if (!phpversion('pdo_sqlite'))
    $errors[] = 'The PHP Sqlite PDO driver is needed but not installed.';

if (!empty($errors)) return $errors;

return true;
