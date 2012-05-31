<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

interface RokCommon_I18N_Platform
{
    /**
     * @abstract
     * @param  $string
     * @return string
     */
    public function translate($string);

    /**
     * @abstract
     * @param  $count
     * @param  $string
     * @param  $multistring
     * @return string
     */
    public function translatePlural($count, $string);

    /**
     * @abstract
     * @param  $string
     * @param  mixed    Mixed number of arguments for the sprintf function.
     * @return string
     */
    public function translateFormatted($string);
   
}
