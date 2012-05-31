<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokCommon_I18N_Platform_Joomla16 extends JText implements RokCommon_I18N_Platform
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
        $args = func_get_args();
        $out = call_user_func_array(array($this, 'sprintf'), $args);
        return $out;
    }

    /**
     * @param  $string
     * @param  $count
     * @return string
     */
    public function translatePlural($string, $count)
    {
        $args = func_get_args();
        $out = call_user_func_array(array($this, 'plural'), $args);
        return $out;
    }

    /**
     * @param  $string
     * @return string
     */
    public function translate($string)
    {
        $args = func_get_args();
        $out = call_user_func_array(array($this, '_'), $args);
        return $out;
    }
	
}
	