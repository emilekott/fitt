<?php
 /**
  * @version   $Id: rokgallerybytag.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

defined('_JEXEC' ) or die( 'Restricted access');
		
/**
 * @package     gantry
 * @subpackage  admin.elements
 */

class JFormFieldTag extends JFormField
{
    static $ROKGALLERYMODULE_ROOT;
    static $SOURCE_DIR;
	protected  $type = 'tag';
    protected $element_dirs  = array();

    public function __construct($parent = null)
    {
        if (!defined('ROKGALLERYMODULE')) define('ROKGALLERYMODULE','ROKGALLERYMODULE');
        
        // Set base dirs
        self::$ROKGALLERYMODULE_ROOT = JPATH_ROOT.'/modules/mod_rokgallery';

        //load up the RTCommon
        require_once(self::$ROKGALLERYMODULE_ROOT. '/lib/include.php');
        
        parent::__construct($parent);
    }

	protected function getInput()
	{
		
		$size = ( $this->element['size'] ? 'size="'.$this->element['size'].'"'  : 'size="5"' );
    	$class = ( $this->element['class'] ? 'class="'.$this->element['class'].'"' : 'class="inputbox"' );
		$multiple = ( $this->element['multiple'] ? 'multiple="'.$this->element['multiple'].'"' : 'multiple="multiple"' );
		$js = ( $this->element['js'] ? $this->element['js'] : '' );
		
    	$tags = RokGalleryModule_Tags::getall();
		if(count($tags)){
			$options = array();   
			foreach ($tags as $tag)
			{
				$options[] = JHTML::_('select.option', $tag, $tag, 'id', 'title');
			}
	        return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]',  $multiple.' '.$size.' '.$class.' '.$js, 'id', 'title', $value, $control_name.$name);
		}else 
		{
			return JText::_('ROKGALLERY_NO_TAGS');
		}
	}
}
