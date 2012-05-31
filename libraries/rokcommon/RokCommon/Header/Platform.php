<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
interface RokCommon_Header_Platform
{
    /**
     * @param $file
     */
    public function addScript($file);

    /**
     * @param $text
     */
    public function addInlineScript($text);

    /**
     * @param $file
     */
    public function addStyle($file);

    /**
     * @param $text
     */
    public function addInlineStyle($text);
}
