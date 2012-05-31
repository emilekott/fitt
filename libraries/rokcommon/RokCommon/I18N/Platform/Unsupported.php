<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokCommon_I18N_Platform_Unsupported extends JText implements RokCommon_I18N_Platform
{

    /**
	 * javascript strings
	 */
	protected static $strings=array();

    /**
     * @param  $string
     * @return string
     */
    public function translateFormatted($string)
    {
        return $string;
    }

    /**
     * @param  $count
     * @param  $string
     * @return string
     */
    public function translatePlural($string, $count)
    {
        return $string;
    }

    /**
     * @param  $string
     * @return string
     */
    public function translate($string)
    {
        return $string;
    }
	
}
	