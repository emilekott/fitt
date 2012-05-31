<?php
/**
 * @package		HikaShop for Joomla!
 * @version		1.5.5
 * @author		hikashop.com
 * @copyright	(C) 2010-2011 HIKARI SOFTWARE. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
class CategoryController extends hikashopController{
	var $type='category';
	var $pkey = 'category_id';
	var $table = 'category';
	var $groupMap = 'category_parent_id';
	var $orderingMap ='category_ordering';
	var $groupVal = 0;
	function __construct(){
		parent::__construct();
		$this->display[]='selectstatus';
		$this->modify_views[]='edit_translation';
		$this->modify[]='save_translation';
	}
	function edit_translation(){
		JRequest::setVar( 'layout', 'edit_translation'  );
		return parent::display();
	}
	function save_translation(){
		$element=null;
		$category_id = hikashop_getCID('category_id');
		$class = hikashop_get('class.category');
		$element = $class->get($category_id);
		if(!empty($element->category_id)){
			$class = hikashop_get('helper.translation');	
			$class->getTranslations($element);
			$class->handleTranslations('category',$element->category_id,$element);
		}
	}
	function orderdown(){
		$this->getGroupVal();
		return parent::orderdown();
	}
	function orderup(){
		$this->getGroupVal();
		return parent::orderup();
	}
	function saveorder(){
		$this->getGroupVal();
		return parent::saveorder();
	}
	function getGroupVal(){
		$app =& JFactory::getApplication();
		$this->groupVal = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.category.filter_id','filter_id',0,'string');
		if(!is_numeric($this->groupVal)){
			$class = hikashop_get('class.category');
			$class->getMainElement($this->groupVal);
		}
	}
	function selectparentlisting(){
		JRequest::setVar( 'layout', 'selectparentlisting'  );
		return parent::display();
	}
	function selectstatus(){
		JRequest::setVar( 'layout', 'selectstatus'  );
		return parent::display();
	}


}