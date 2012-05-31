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
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','order',false);
		$pageInfo = null;
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'b.order_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->filter_status = $app->getUserStateFromRequest( $this->paramBase.".filter_status",'filter_status','','string');
		$pageInfo->filter->filter_payment = $app->getUserStateFromRequest( $this->paramBase.".filter_payment",'filter_payment','','string');
		$pageInfo->filter->filter_partner = $app->getUserStateFromRequest( $this->paramBase.".filter_partner",'filter_partner','','int');
		$database	=& JFactory::getDBO();
		$filters = array('b.order_type=\'sale\'');
		if(!empty($pageInfo->filter->filter_partner)){
			if($pageInfo->filter->filter_partner==1){
				$filters[]='b.order_partner_id != 0';
			}else{
				$filters[]='b.order_partner_id = 0';
			}
		}
		switch($pageInfo->filter->filter_status){
			case '':
				break;
			default:
				$filters[]='b.order_status = '.$database->Quote($pageInfo->filter->filter_status);
				break;
		}
		switch($pageInfo->filter->filter_payment){
			case '':
				break;
			default:
				$filters[]='b.order_payment_method = '.$database->Quote($pageInfo->filter->filter_payment);
				break;
		}
		$searchMap = array('c.id','c.username','c.name','a.user_email','b.order_user_id','b.order_number','b.order_id','b.order_full_price');
		foreach($fields as $field){
			$searchMap[]='b.'.$field->field_namekey;
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
			$filter = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
			$filters[] =  $filter;
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
		$query = ' FROM '.hikashop_table('order').' AS b LEFT JOIN '.hikashop_table('user').' AS a ON b.order_user_id=a.user_id LEFT JOIN '.hikashop_table('users',false).' AS c ON a.user_cms_id=c.id '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*,c.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'order_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_order_manage','all'));
		$this->assignRef('manage',$manage);
		$bar->appendButton( 'Standard', 'archive',JText::_('HIKA_EXPORT'), 'export', false, false );
		if($manage){
			$bar->appendButton( 'Link', 'new', JText::_('HIKA_NEW'),hikashop_completeLink('order&task=neworder'));
			JToolBarHelper::editList();
		}
		if(hikashop_isAllowed($config->get('acl_order_delete','all'))) JToolBarHelper::deleteList(JText::_('HIKA_VALIDDELETEITEMS'));
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$fieldsClass->handleZoneListing($fields,$rows);
		$pluginClass = hikashop_get('class.plugins');
		$payments = $pluginClass->getMethods('payment');
		$newPayments = array();
		foreach($payments as $payment){
			$newPayments[$payment->payment_type] = $payment;
		}
		$this->assignRef('payments',$newPayments);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$this->assignRef('category',$category);
		$payment = hikashop_get('type.payment');
		$this->assignRef('payment',$payment);
		$partner = hikashop_get('type.user_partner');
		$this->assignRef('partner',$partner);
		$plugin =& JPluginHelper::getPlugin('system', 'hikashop');
		if(empty($plugin) || !hikashop_level(2)){
			$affiliate_active = false;
		}else{
			$affiliate_active = true;
		}
		$this->assignRef('affiliate_active',$affiliate_active);
		JHTML::_('behavior.modal');
	}
	function form(){
		$order_id = hikashop_getCID('order_id');
		$fieldsClass = hikashop_get('class.field');
		$fields = null;
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadFullOrder($order_id,true);
			if(hikashop_level(2)){
				$fields['order'] = $fieldsClass->getFields('backend',$order,'order');
				$null = null;
				$fields['entry'] = $fieldsClass->getFields('backend_listing',$null,'entry');
				$fields['item'] = $fieldsClass->getFields('backend_listing',$null,'item');
			}
			$task='edit';
		}
		if(empty($order)){
			$app =& JFactory::getApplication();
			$app->redirect(hikashop_completeLink('order&task=listing',false,true));
		}
		$config =& hikashop_config();
		$order_status_for_download = $config->get('order_status_for_download','confirmed,shipped');
		$download_time_limit = $config->get('download_time_limit',0);
		$download_number_limit = $config->get('download_number_limit',0);
		$this->assignRef('order_status_for_download',$order_status_for_download);
		$this->assignRef('download_time_limit',$download_time_limit);
		$this->assignRef('download_number_limit',$download_number_limit);
		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$category->load(true);
		$this->assignRef('category',$category);
		$pluginsPayment = hikashop_get('type.plugins');
		$pluginsPayment->type='payment';
		$this->assignRef('payment',$pluginsPayment);
		$pluginsShipping = hikashop_get('type.plugins');
		$pluginsShipping->type='shipping';
		$this->assignRef('shipping',$pluginsShipping);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		JPluginHelper::importPlugin( 'hikashop' );
		JPluginHelper::importPlugin( 'hikashoppayment' );
		JPluginHelper::importPlugin( 'hikashopshipping' );
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onHistoryDisplay', array( & $order->history) );
		$this->assignRef('order',$order);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		JHTML::_('behavior.modal');
		$bar = & JToolBar::getInstance('toolbar');
		if(version_compare(JVERSION,'1.6','<')){
			$bar->appendButton( 'Popup','send',JText::_('HIKA_EMAIL'),hikashop_completeLink('order&task=mail&order_id='.$order_id,true),720);
			$bar->appendButton( 'Popup','preview',JText::_('INVOICE'),hikashop_completeLink('order&task=invoice&type=full&order_id='.$order_id,true));
			$bar->appendButton( 'Popup','preview',JText::_('SHIPPING_INVOICE'),hikashop_completeLink('order&task=invoice&type=shipping&order_id='.$order_id,true));
		}else{
			$bar->appendButton( 'Popup','send',JText::_('HIKA_EMAIL'),'index.php?option=com_hikashop&ctrl=order&task=mail&tmpl=component&order_id='.$order_id,720);
			$bar->appendButton( 'Popup','preview',JText::_('INVOICE'),'index.php?option=com_hikashop&ctrl=order&task=invoice&tmpl=component&type=full&order_id='.$order_id);
			$bar->appendButton( 'Popup','preview',JText::_('SHIPPING_INVOICE'),'index.php?option=com_hikashop&ctrl=order&task=invoice&tmpl=component&type=shipping&order_id='.$order_id);
		}
		$user_id = JRequest::getInt('user_id',0);
		if(!empty($user_id)){
			$user_info='&user_id='.$user_id;
			$url = hikashop_completeLink('user&task=edit&user_id='.$user_id);
		}else{
			$user_info='';
			$cancel_url = JRequest::getVar('cancel_redirect');
			if(!empty($cancel_url)){
				$url = base64_decode($cancel_url);
			}else{
				$url = hikashop_completeLink('order');
			}
		}
		$bar->appendButton( 'Link', 'cancel', JText::_('HIKA_CANCEL'), $url );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp',$this->ctrl.'-form');
		hikashop_setTitle(JText::_($this->nameForm),$this->icon,$this->ctrl.'&task='.$task.'&order_id='.$order_id.$user_info);
	}
	function changestatus(){
		$order_id = hikashop_getCID('order_id');
		$new_status = JRequest::getVar('status','');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->get($order_id,$new_status);
			$order->order_old_status = $order->order_status;
			$order->order_status = $new_status;
			$class->loadOrderNotification($order);
		}else{
			$order = null;
		}
		$order->order_status = $new_status;
		$this->assignRef('element',$order);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $order->mail->body;
		$this->assignRef('editor',$editor);
	}
	function partner(){
		$order_id = hikashop_getCID('order_id');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadNotification($order_id);
		}else{
			$order = null;
		}
		$this->assignRef('element',$order);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = '';
		$order->mail->altbody='';
		$this->assignRef('editor',$editor);
		$partners = hikashop_get('type.partners');
		$this->assignRef('partners',$partners);
		$currencyType=hikashop_get('type.currency');
		$this->assignRef('currencyType',$currencyType);
	}
	function discount(){
		$order_id = hikashop_getCID('order_id');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadNotification($order_id);
		}else{
			$order = null;
		}
		$this->assignRef('element',$order);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = '';
		$order->mail->altbody='';
		$this->assignRef('editor',$editor);
	}
	function fields(){
		$order_id = hikashop_getCID('order_id');
		$fieldsClass = hikashop_get('class.field');
		$fields = null;
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadNotification($order_id);
			if(hikashop_level(2)){
				$fields['order'] = $fieldsClass->getFields('backend',$order,'order');
			}
		}else{
			$order = null;
		}
		$this->assignRef('element',$order);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = '';
		$order->mail->altbody='';
		$this->assignRef('editor',$editor);
	}
	function changeplugin(){
		$order_id = hikashop_getCID('order_id');
		$new_status = JRequest::getVar('status','');
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadNotification($order_id);
		}else{
			$order = null;
		}
		$this->assignRef('element',$order);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = '';
		$order->mail->altbody='';
		$this->assignRef('editor',$editor);
		$pluginsPayment = hikashop_get('type.plugins');
		$pluginsPayment->type=JRequest::getWord('type');
		$this->assignRef($pluginsPayment->type,$pluginsPayment);
		$this->assignRef('type',$pluginsPayment->type);
		$full_id = JRequest::getCmd('plugin');
		$this->assignRef('full_id',$full_id);
		$parts = explode('_',$full_id);
		$id = array_pop($parts);
		$this->assignRef('id',$id);
		$method = implode('_',$parts);
		$this->assignRef('method',$method);
	}
	function mail(){
		$element = null;
		$element->order_id = JRequest::getInt('order_id',0);
		if(empty($element->order_id)){
			$user_id = JRequest::getInt('user_id',0);
			$userClass = hikashop_get('class.user');
			$element->customer = $userClass->get($user_id);
			$mailClass = hikashop_get('class.mail');
			$element->mail = null;
			$element->mail->body='';
			$element->mail->altbody='';
			$element->mail->html=1;
			$mailClass->loadInfos($element->mail, 'user_notification');
			$element->mail->dst_email =& $element->customer->user_email;
			if(!empty($element->customer->name)){
				$element->mail->dst_name =& $element->customer->name;
			}else{
				$element->mail->dst_name = '';
			}
		}else{
			$orderClass = hikashop_get('class.order');
			$orderClass->loadMail($element);
		}
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $element->mail->body;
		$this->assignRef('editor',$editor);
		$this->assignRef('element',$element);
	}
	function export(){
		$ids = JRequest::getVar( 'cid', array(), '', 'array' );
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('all','order',false);
		$database	=& JFactory::getDBO();
		$filters = array('b.order_type=\'sale\'');
		if(empty($ids)){
			$app =& JFactory::getApplication();
			$search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
			$filter_status = $app->getUserStateFromRequest( $this->paramBase.".filter_status",'filter_status','','string');
			$filter_payment = $app->getUserStateFromRequest( $this->paramBase.".filter_payment",'filter_payment','','string');
			$filter_partner = $app->getUserStateFromRequest( $this->paramBase.".filter_partner",'filter_partner','','int');
			if(!empty($filter_partner)){
				if($filter_partner==1){
					$filters[]='b.order_partner_id != 0';
				}else{
					$filters[]='b.order_partner_id = 0';
				}
			}
			switch($filter_status){
				case '':
					break;
				default:
					$filters[]='b.order_status = '.$database->Quote($filter_status);
					break;
			}
			switch($filter_payment){
				case '':
					break;
				default:
					$filters[]='b.order_payment_method = '.$database->Quote($filter_payment);
					break;
			}
			$searchMap = array('c.id','c.username','c.name','a.user_email','b.order_user_id','b.order_id','b.order_full_price');
			foreach($fields as $field){
				$searchMap[]='b.'.$field->field_namekey;
			}
			if(!empty($pageInfo->search)){
				$searchVal = '\'%'.$database->getEscaped(JString::strtolower( $pageInfo->search ),true).'%\'';
				$id = hikashop_decode($pageInfo->search);
				$filter = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
				if(!empty($id)){
					$filter .= " OR b.order_id LIKE '%".$database->getEscaped($id,true).'%\'';
				}
				$filters[] =  $filter;
			}
		}else{
			JArrayHelper::toInteger($ids,0);
			$filters[] =  'b.order_id IN ('.implode(',',$ids).')';
		}
		$filters = implode(' AND ', $filters);
		$query = ' FROM '.hikashop_table('order').' AS b LEFT JOIN '.hikashop_table('user').' AS a ON b.order_user_id=a.user_id LEFT JOIN '.hikashop_table('users',false).' AS c ON a.user_cms_id=c.id WHERE '.$filters;
		$database->setQuery('SELECT a.*,b.*,c.*'.$query);
		$rows = $database->loadObjectList('order_id');
		if(!empty($rows)){
			$addressIds = array();
			foreach($rows as $k => $row){
				$rows[$k]->products = array();
				$addressIds[$row->order_shipping_address_id]=$row->order_shipping_address_id;
				$addressIds[$row->order_billing_address_id]=$row->order_billing_address_id;
			}
			if(!empty($addressIds)){
				$database->setQuery('SELECT * FROM '.hikashop_table('address').' WHERE address_id IN ('.implode(',',$addressIds).')');
				$addresses = $database->loadObjectList('address_id');
				if(!empty($addresses)){
					$zoneNamekeys = array();
					foreach($addresses as $address){
						$zoneNamekeys[$address->address_country]=$database->Quote($address->address_country);
						$zoneNamekeys[$address->address_state]=$database->Quote($address->address_state);
					}
					if(!empty($zoneNamekeys)){
						$database->setQuery('SELECT zone_namekey,zone_name FROM '.hikashop_table('zone').' WHERE zone_namekey IN ('.implode(',',$zoneNamekeys).')');
						$zones = $database->loadObjectList('zone_namekey');
						if(!empty($zones)){
							foreach($addresses as $i => $address){
								if(!empty($zones[$address->address_country])){
									$addresses[$i]->address_country = $zones[$address->address_country]->zone_name;
								}
								if(!empty($zones[$address->address_state])){
									$addresses[$i]->address_state = $zones[$address->address_state]->zone_name;
								}
							}
						}
					}
					foreach($rows as $k => $row){
						if(!empty($addresses[$row->order_shipping_address_id])){
							foreach($addresses[$row->order_shipping_address_id] as $key => $val){
								$key = 'shipping_'.$key;
								$rows[$k]->$key = $val;
							}
						}
						if(!empty($addresses[$row->order_billing_address_id])){
							foreach($addresses[$row->order_billing_address_id] as $key => $val){
								$key = 'billing_'.$key;
								$rows[$k]->$key = $val;
							}
						}
					}
				}
			}
			$orderIds = array_keys($rows);
			$database->setQuery('SELECT * FROM '.hikashop_table('order_product').' WHERE order_id IN ('.implode(',',$orderIds).')');
			$products = $database->loadObjectList();
			foreach($products as $product){
				$order =& $rows[$product->order_id];
				$order->products[] = $product;
			}
		}
		$this->assignRef('orders',$rows);
	}
	function invoice(){
		$order_id = hikashop_getCID('order_id');
		$fieldsClass = hikashop_get('class.field');
		$fields = array();
		if(!empty($order_id)){
			$class = hikashop_get('class.order');
			$order = $class->loadFullOrder($order_id);
			$null = null;
			$fields['item'] = $fieldsClass->getFields('backend_listing',$null,'item');
			$task='edit';
		}else{
			$order = null;
			$task='add';
		}
		$config =& hikashop_config();
		$store = str_replace(array("\r\n","\n","\r"),array('<br/>','<br/>','<br/>'),$config->get('store_address',''));
		$this->assignRef('store_address',$store);
		$this->assignRef('element',$order);
		$this->assignRef('order',$order);
		$this->assignRef('fields',$fields);
		if(!empty($order->order_payment_id)){
			$pluginsPayment = hikashop_get('type.plugins');
			$pluginsPayment->type='payment';
			$this->assignRef('payment',$pluginsPayment);
		}
		if(!empty($order->order_shipping_id)){
			$pluginsShipping = hikashop_get('type.plugins');
			$pluginsShipping->type='shipping';
			$this->assignRef('shipping',$pluginsShipping);
		}
		$type = JRequest::getWord('type');
		$this->assignRef('invoice_type',$type);
		$nobutton = true;
		$this->assignRef('nobutton',$nobutton);
		$display_type = 'frontcomp';
		$this->assignRef('display_type',$display_type);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$this->assignRef('fieldsClass',$fieldsClass);
	}
	function product(){
		$product_id = hikashop_getCID('product_id');
		$orderClass = hikashop_get('class.order');
		if(!empty($product_id)){
			$class = hikashop_get('class.order_product');
			$product = $class->get($product_id);
		}else{
			$product = null;
			$product->order_id = JRequest::getInt('order_id');
			$product->mail->body = '';
		}
		$orderClass->loadMail($product);
		$this->assignRef('element',$product);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $product->mail->body;
		$this->assignRef('editor',$editor);
		$extraFields=array();
		$fieldsClass = hikashop_get('class.field');
		$this->assignRef('fieldsClass',$fieldsClass);
		$null=null;
		$extraFields['item'] = $fieldsClass->getFields('backend',$null,'item','user&task=state');
		$this->assignRef('extraFields',$extraFields);
	}
	function user(){
		$element = null;
		$element->order_id = JRequest::getInt('order_id');
		$element->mail->body = '';
		$orderClass = hikashop_get('class.order');
		$orderClass->loadMail($element);
		$this->assignRef('element',$element);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $element->mail->body;
		$this->assignRef('editor',$editor);
	}
	function product_delete(){
		$product_id = hikashop_getCID('product_id');
		$orderClass = hikashop_get('class.order');
		if(!empty($product_id)){
			$class = hikashop_get('class.order_product');
			$product = $class->get($product_id);
			$orderClass->loadMail($product);
			$this->assignRef('element',$product);
			$editor = hikashop_get('helper.editor');
			$editor->name = 'hikashop_mail_body';
			$editor->content = $product->mail->body;
			$this->assignRef('editor',$editor);
		}
	}
	function address(){
		$address_id = hikashop_getCID('address_id');
		$address_type = JRequest::getCmd('type');
		$fieldsClass = hikashop_get('class.field');
		$orderClass = hikashop_get('class.order');
		$order = null;
		$order->order_id = JRequest::getInt('order_id');
		$addressClass=hikashop_get('class.address');
		$name = $address_type.'_address';
		if(!empty($address_id)){
			$order->$name=$addressClass->get($address_id);
		}
		$fieldClass = hikashop_get('class.field');
		$order->fields = $fieldClass->getData('backend','address');
		$orderClass->loadMail($order);
		$name = $address_type.'_address';
		$fieldsClass->prepareFields($order->fields,$order->$name,'address','field&task=state');
		$this->assignRef('fieldsClass',$fieldsClass);
		$this->assignRef('element',$order);
		$this->assignRef('type',$address_type);
		$this->assignRef('id',$address_id);
		$editor = hikashop_get('helper.editor');
		$editor->name = 'hikashop_mail_body';
		$editor->content = $order->mail->body;
		$this->assignRef('editor',$editor);
	}
	function product_select(){
		$app =& JFactory::getApplication();
		$config =& hikashop_config();
		$this->assignRef('config',$config);
		$this->paramBase.="_product_select";
		$element = null;
		$element->order_id = JRequest::getInt('order_id');
		$this->assignRef('element',$element);
		$this->paramBase.="_product_select";
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.ordering','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'asc',	'word' );
		if(JRequest::getVar('search')!=$app->getUserState($this->paramBase.".search")){
			$app->setUserState( $this->paramBase.'.limitstart',0);
			$pageInfo->limit->start = 0;
		}else{
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		}
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->search = JString::strtolower( $pageInfo->search );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 500;
		$selectedType = $app->getUserStateFromRequest( $this->paramBase.".filter_type",'filter_type',0,'int');
		$pageInfo->filter->filter_id = $app->getUserStateFromRequest( $this->paramBase.".filter_id",'filter_id',0,'string');
		$pageInfo->filter->filter_product_type = $app->getUserStateFromRequest( $this->paramBase.".filter_product_type",'filter_product_type','main','word');
		$database	=& JFactory::getDBO();
		$filters = array();
		$searchMap = array('b.product_name','b.product_description','b.product_id','b.product_code');
		if(empty($pageInfo->filter->filter_id)|| !is_numeric($pageInfo->filter->filter_id)){
			$pageInfo->filter->filter_id='product';
			$class = hikashop_get('class.category');
			$class->getMainElement($pageInfo->filter->filter_id);
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.$database->getEscaped($pageInfo->search,true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal";
		}
		$order = '';
		if(!$selectedType){
			$filters[]='a.category_id='.(int)$pageInfo->filter->filter_id;
			$select='SELECT a.ordering, b.*';
		}else{
			$categoryClass = hikashop_get('class.category');
			$categoryClass->parentObject =& $this;
			$childs = $categoryClass->getChilds((int)$pageInfo->filter->filter_id,true,array(),'',0,0);
			$filter = 'a.category_id IN (';
			foreach($childs as $child){
				$filter .= $child->category_id.',';
			}
			$filters[]=$filter.(int)$pageInfo->filter->filter_id.')';
			$select='SELECT DISTINCT b.*';
		}
		if($pageInfo->filter->filter_product_type=='all'){
			if(!empty($pageInfo->filter->order->value)){
				$select.=','.$pageInfo->filter->order->value.' as sorting_column';
				$order = ' ORDER BY sorting_column '.$pageInfo->filter->order->dir;
			}
		}else{
			if(!empty($pageInfo->filter->order->value)){
				$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
			}
		}
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onBeforeProductListingLoad', array( & $filters, & $order, &$this) );
		if($pageInfo->filter->filter_product_type=='all'){
			$query = '( '.$select.' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_id WHERE '.implode(' AND ',$filters).' AND b.product_id IS NOT NULL )
			UNION
					  ( '.$select.' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON a.product_id=b.product_parent_id WHERE '.implode(' AND ',$filters).' AND b.product_parent_id IS NOT NULL ) ';
			$database->setQuery($query.$order,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		}else{
			$filters[]='b.product_type = '.$database->Quote($pageInfo->filter->filter_product_type);
			if($pageInfo->filter->filter_product_type!='variant'){
				$lf = 'a.product_id=b.product_id';
			}else{
				$lf = 'a.product_id=b.product_parent_id';
			}
			$query = ' FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('product').' AS b ON '.$lf.' WHERE '.implode(' AND ',$filters);
			$database->setQuery($select.$query.$order,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		}
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'product_id');
		}
		if($pageInfo->filter->filter_product_type=='all'){
			$database->setQuery('SELECT COUNT(*) FROM ('.$query.') as u');
		}else{
			$database->setQuery('SELECT COUNT(DISTINCT(b.product_id))'.$query);
		}
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->elements->page){
			$this->_loadPrices($rows);
		}
		jimport('joomla.html.pagination');
		if($pageInfo->limit->value == 500) $pageInfo->limit->value = 100;
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('pagination',$pagination);
		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$childClass = hikashop_get('type.childdisplay');
		$this->assignRef('childDisplay',$childClass->display('filter_type',$selectedType,false));
		$productClass = hikashop_get('type.product');
		$this->assignRef('productType',$productClass);
		$breadcrumbClass = hikashop_get('type.breadcrumb');
		$this->assignRef('breadCrumb',$breadcrumbClass->display('filter_id',$pageInfo->filter->filter_id,'product'));
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);
		$doOrdering = !$selectedType;
		if($doOrdering && !(empty($pageInfo->filter->filter_product_type) || $pageInfo->filter->filter_product_type=='main')){
			$doOrdering=false;
		}
		$this->assignRef('doOrdering',$doOrdering);
		if($doOrdering){
			$order = null;
			$order->ordering = false;
			$order->orderUp = 'orderup';
			$order->orderDown = 'orderdown';
			$order->reverse = false;
			if($pageInfo->filter->order->value == 'a.ordering'){
				$order->ordering = true;
				if($pageInfo->filter->order->dir == 'desc'){
					$order->orderUp = 'orderdown';
					$order->orderDown = 'orderup';
					$order->reverse = true;
				}
			}
			$this->assignRef('order',$order);
		}
	}
	function _loadPrices(&$rows){
		$ids = array();
		foreach($rows as $row){
			$ids[]=(int)$row->product_id;
		}
		$query = 'SELECT * FROM '.hikashop_table('price').' WHERE price_product_id IN ('.implode(',',$ids).')';
		$database =& JFactory::getDBO();
		$database->setQuery($query);
		$prices = $database->loadObjectList();
		if(!empty($prices)){
			foreach($prices as $price){
				foreach($rows as $k => $row){
					if($price->price_product_id==$row->product_id){
						if(!isset($row->prices)) $row->prices=array();
						$rows[$k]->prices[]=$price;
						break;
					}
				}
			}
		}
	}
}
