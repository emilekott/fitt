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
class BannerViewBanner extends JView{
	var $ctrl= 'banner';
	var $nameListing = 'HIKA_BANNERS';
	var $nameForm = 'HIKA_BANNERS';
	var $icon = 'banner';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.banner_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	=& JFactory::getDBO();
		$filters = array();
		$searchMap = array('a.banner_id','a.banner_title','a.banner_url','a.banner_image_url');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
			$filters[] =  implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE '. implode(' AND ',$filters);
		}else{
			$filters = '';
		}
		$query = ' FROM '.hikashop_table('banner').' AS a '.$filters.$order;
		$database->setQuery('SELECT a.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'banner_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$order = null;
		$order->ordering = true;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'a.banner_ordering'){
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}
		$this->assignRef('order',$order);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_banner_manage','all'));
		$this->assignRef('manage',$manage);
		if($manage){
			JToolBarHelper::addNew();
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_banner_delete','all'))) JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
	}
	function form(){
		$banner_id = hikashop_getCID('banner_id');
		$class = hikashop_get('class.banner');
		if(!empty($banner_id)){
			$element = $class->get($banner_id,true);
			$task='edit';
		}else{
			$element = null;
			$element->banner_url = HIKASHOP_LIVE;
			$element->banner_published = 1;
			$task='add';
		}
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&banner_id='.$banner_id);
		$bar = & JToolBar::getInstance('toolbar');
		JToolBarHelper::save();
		if(version_compare(JVERSION,'1.7','>=')) JToolBarHelper::save2new();
		JToolBarHelper::apply();
		JToolBarHelper::cancel();
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
		$this->assignRef('element',$element);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_banner',@$element->banner_id,$element);
			jimport('joomla.html.pane');
			$config =& hikashop_config();
			$multilang_display=$config->get('multilang_display','tabs');
			if($multilang_display=='popups') $multilang_display = 'tabs';
			$tabs	=& JPane::getInstance($multilang_display);
			$this->assignRef('tabs',$tabs);
			$this->assignRef('transHelper',$transHelper);
		}
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('translation',$translation);
	}
}