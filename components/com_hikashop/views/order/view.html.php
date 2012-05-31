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
class OrderViewOrder extends JView{
	var $ctrl= 'order';
	var $nameListing = 'ORDERS';
	var $nameForm = 'HIKASHOP_ORDER';
	var $icon = 'order';
	function display($tpl = null,$params=array()){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->params = $params;
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$pageInfo = null;
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.order_created','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$oldValue = $app->getUserState($this->paramBase.'.list_limit');
		if(empty($oldValue)){
			$oldValue =$app->getCfg('list_limit');
		}
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if($oldValue!=$pageInfo->limit->value){
			$pageInfo->limit->start = 0;
			$app->setUserState($this->paramBase.'.limitstart',0);
		}
		$database	=& JFactory::getDBO();
		$searchMap = array('a.order_id','a.order_status','a.order_number');
		$filters = array('a.order_user_id='.(int)hikashop_loadUser());
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
			$filter = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
			$filters[] =  $filter;
		}
		$query = 'FROM '.hikashop_table('order').' AS a WHERE ('.implode(') AND (',$filters).') '.$order;
		$database->setQuery('SELECT a.* '.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'order_id');
		}
		$database->setQuery('SELECT COUNT(*) '.$query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		if($pageInfo->elements->page){
		}else{
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('NO_ORDERS_FOUND'));
		}
		jimport('joomla.html.pagination');
		$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$pagination->hikaSuffix = '';
		$this->assignRef('pagination',$pagination);
		$this->assignRef('pageInfo',$pageInfo);
		$string='';
		jimport('joomla.html.parameter');
		$params = new JParameter($string);
		$params->set('show_quantity_field',0);
		$config =& hikashop_config();
		if(hikashop_level(1) && $config->get('allow_payment_button',1)){
			$default_status = $config->get('order_created_status','created');
			foreach($rows as $k => $order){
				if($order->order_status==$default_status){
					$rows[$k]->show_payment_button = true;
				}
			}
			$payment_change = $config->get('allow_payment_change',1);
			$this->assignRef('payment_change',$payment_change);
			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type='payment';
			$this->assignRef('payment',$pluginsPayment);
		}
		if( $config->get('cancellable_order_status','') != '' ) {
			$cancellable_order_status = explode(',',$config->get('cancellable_order_status',''));
			foreach($rows as $k => $order){
				if( in_array($order->order_status, $cancellable_order_status) ){
					$rows[$k]->show_cancel_button = true;
				}
			}
		}
		$this->assignRef('params',$params);
		$this->assignRef('rows',$rows);
		$cart = hikashop_get('helper.cart');
		$this->assignRef('cart',$cart);
		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$category->load(true);
		$this->assignRef('order_statuses',$category);
	}
	function show(){
		$type = 'order';
		$order =& $this->_order($type);
		$config =& hikashop_config();
		$download_time_limit = $config->get('download_time_limit',0);
		$this->assignRef('download_time_limit',$download_time_limit);
		$download_number_limit = $config->get('download_number_limit',0);
		$this->assignRef('download_number_limit',$download_number_limit);
		$order_status_download_ok=false;
		$order_status_for_download = $config->get('order_status_for_download','confirmed,shipped');
		if(in_array($order->order_status,explode(',',$order_status_for_download))){
			$order_status_download_ok=true;
		}
		$this->assignRef('order_status_download_ok',$order_status_download_ok);
		JHTML::_('behavior.modal');
	}
	function invoice(){
		$type = 'invoice';
		$this->setLayout('show');
		$order =& $this->_order($type);
		$js = "window.addEvent('domready', function() {window.focus();window.print();setTimeout(function(){try{	window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }}, 1000);});";
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration("<!--\n".$js."\n//-->");
		JHTML::_('behavior.mootools');
	}
	function &_order($type){
		$order_id = hikashop_getCID('order_id');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadFullOrder($order_id,($type=='order'?true:false));
		}
		if(empty($order)){
			$app =& JFactory::getApplication();
			$app->redirect(hikashop_completeLink('order&task=listing',false,true));
		}
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$store = str_replace(array("\r\n","\n","\r"),array('<br/>','<br/>','<br/>'),$config->get('store_address',''));
		$this->assignRef('store_address',$store);
		$this->assignRef('element',$order);
		$this->assignRef('order',$order);
		$this->assignRef('invoice_type',$type);
		$display_type = 'frontcomp';
		$this->assignRef('display_type',$display_type);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$fields = array();
		if(hikashop_level(2)){
			$null = null;
			$fields['entry'] = $fieldsClass->getFields('frontcomp',$null,'entry');
			$fields['item'] = $fieldsClass->getFields('frontcomp',$null,'item');
			$fields['order'] = $fieldsClass->getFields('',$null,'order');
		}
		$this->assignRef('fields',$fields);
		return $order;
	}
}