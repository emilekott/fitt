<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokGallery_Doctrine_Migration extends RokCommon_Doctrine_Migration {
    public function __construct($directory = null, $connection = null)
    {
        if ($directory == null)
        {
            $directory = dirname(__FILE__).'/Migrations';
        }
        parent::__construct($directory, $connection);
        $this->setTableName('rokgallery_schema_version');
    }
}
