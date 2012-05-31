<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokCommon_Composite_Platform_Joomla15 implements RokCommon_Composite_Platform
{
    /**
     * Returns the URL for a given file based on the full file path passed in
     * @param $filepath
     * @return string
     */
    public function getUrlForPath($filepath)
    {
        jimport('joomla.environment.uri');
        jimport('joomla.filesystem.path');
        $base = JURI::root(true);
        $file_real_path = JPath::clean($filepath,'/');
        $site_real_path = JPath::clean(JPATH_SITE,'/');
        $url_path = $base.str_replace($site_real_path,'',$file_real_path);
        return $url_path;
    }
}
