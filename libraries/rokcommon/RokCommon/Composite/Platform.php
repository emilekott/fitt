<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
interface RokCommon_Composite_Platform {

    /**
     * Returns the URL for a given file based on the full file path passed in
     * @abstract
     * @param $filepath
     * @return string
     */
    public function getUrlForPath($filepath);

}