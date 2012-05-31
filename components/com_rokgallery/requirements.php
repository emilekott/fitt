<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

$query = 'show engines;';
$db =& JFactory::getDBO();
$db->setQuery($query);
$db->query();

$engines = $db->loadObjectList();
$found_engine = false;
foreach ($engines as $engine)
{
    if (strtolower($engine->Engine) == 'innodb' && (strtolower($engine->Support) == 'yes' || strtolower($engine->Support) == 'default')) {
        $found_engine = true;
    }
}
if (!$found_engine) {
    $errors[] = 'Your MySQL Database does not support the InnoDB Engine.';
}

if (!empty($errors)) return $errors;
return true;