<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokCommon_Header_Platform_Joomla15 implements RokCommon_Header_Platform
{
    /**
     * @var \JDocument
     */
    protected $document;

    /**
     *
     */
    public function __construct()
    {
        $this->document =& JFactory::getDocument();
    }
    /**
     * @param $file
     */
    public function addScript($file)
    {
        $this->document->addScript($file);
    }

    /**
     * @param $text
     */
    public function addInlineScript($text)
    {
        $this->document->addScriptDeclaration($text);
    }

    /**
     * @param $file
     */
    public function addStyle($file)
    {
        $this->document->addStyleSheet($file);
    }

    /**
     * @param $text
     */
    public function addInlineStyle($text)
    {
        $this->document->addScriptDeclaration($text);
    }

}
