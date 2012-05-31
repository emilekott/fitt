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
class PluginsViewPlugins extends JView{
	var $type = '';
	var $ctrl= 'plugins';
	var $nameListing = 'PLUGINS';
	var $nameForm = 'PLUGINS';
	var $icon = 'plugin';
	function display($tpl = null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function listing(){
		$app =& JFactory::getApplication();
		$type=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.plugin_type','plugin_type','shipping' );
		$db =& JFactory::getDBO();
		$group = 'hikashop'.$type;
		if(version_compare(JVERSION,'1.6','<')){
			$db->setQuery('SELECT * FROM '.hikashop_table('plugins',false).' WHERE `folder` = '.$db->Quote($group).' ORDER BY published DESC, ordering ASC');
		}else{
			$db->setQuery('SELECT extension_id as id, enabled as published,name,element FROM '.hikashop_table('extensions',false).' WHERE `folder` = '.$db->Quote($group).' AND type=\'plugin\' ORDER BY enabled DESC, ordering ASC');
		}
		$plugins = $db->loadObjectList();
		$query = 'SELECT * FROM '.hikashop_table($type);
		$db->setQuery($query);
		$obj = $db->loadObject();
		if(empty($obj)){
			$app->enqueueMessage(JText::_('EDIT_PLUGINS_BEFORE_DISPLAY'));
		}
		$this->assignRef('plugins',$plugins);
		$this->assignRef('plugin_type',$type);
		$toggle = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggle);
		$config =& hikashop_config();
		$manage = hikashop_isAllowed($config->get('acl_plugins_manage','all'));
		$this->assignRef('manage',$manage);
		hikashop_setTitle(JText::_($this->nameListing),$this->icon,$this->ctrl);
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Pophelp',$this->ctrl.'-listing');
		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_dashboard_view','all'))) $bar->appendButton( 'Link', 'hikashop', JText::_('HIKASHOP_CPANEL'), hikashop_completeLink('dashboard') );
	}
	function form(){
		JHTML::_('behavior.modal');
		$this->plugin = JRequest::getCmd('name','manual');
		$app =& JFactory::getApplication();
		$type=$app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.plugin_type','plugin_type','shipping' );
		if(in_array($type,array('shipping','payment'))){
			$db = JFactory::getDBO();
			$query = 'SELECT * FROM '.hikashop_table($type).' WHERE '.$type.'_type='.$db->Quote($this->plugin);
			if($type=='shipping'){
				$query.=' ORDER BY shipping_ordering ASC';
			}
	    	$db = JFactory::getDBO();
	    	$db->setQuery($query);
	    	$elements = $db->loadObjectList($type.'_id');
	    	if(!empty($elements)){
	    		$params_name = $type.'_params';
		    	foreach($elements as $k => $el){
		    		if(!empty($el->$params_name)){
		    			$elements[$k]->$params_name = unserialize($el->$params_name);
		    		}
		    	}
	    	}
			$this->assignRef('elements',$elements);
			$data = hikashop_import('hikashop'.$type,$this->plugin);
			$function = 'on'.ucfirst($type).'Configuration';
			if(method_exists($data,$function)){
				ob_start();
				$data->$function($elements);
				$this->content = ob_get_clean();
				$this->data=$data->getProperties();
			}else{
				$bar = & JToolBar::getInstance('toolbar');
				JToolBarHelper::save();
				JToolBarHelper::apply();
				JToolBarHelper::cancel();
				hikashop_setTitle('Plugin','plugin','plugins&plugin_type=payment');
			}
			$this->assignRef('noForm',$this->data['noForm']);
			if(empty($this->data['noForm'])){
				$element = null;
				$id=0;
				if(count($elements)){
					$id_name = $type.'_id';
					$id = hikashop_getCID($id_name);
					if(isset($elements[$id])){
						$element = $elements[$id];
						$id = @$element->$id_name;
					}elseif(empty($data->multiple_entries)){
						$element = array_pop($elements);
						$id = @$element->$id_name;
					}
				}
				$plugin_zone_namekey = $type .'_zone_namekey';
				if(!empty($element->$plugin_zone_namekey)){
		    		$zoneClass = hikashop_get('class.zone');
		    		$zone = $zoneClass->get($element->$plugin_zone_namekey);
		    		if(!empty($zone)){
		    			foreach(get_object_vars($zone)as $k => $v){
		    				$element->$k = $v;
		    			}
		    		}
		    	}
				$translation = false;
				$transHelper = hikashop_get('helper.translation');
				if($transHelper && $transHelper->isMulti()){
					$translation = true;
					$payment_id = $type.'_id';
					$transHelper->load('hikashop_'.$type,@$element->$payment_id,$element);
				}
				jimport('joomla.html.pane');
				$config =& hikashop_config();
				$multilang_display = $config->get('multilang_display','tabs');
				if($multilang_display=='popups') $multilang_display = 'tabs';
				$tabs	=& JPane::getInstance($multilang_display);
				$this->assignRef('config',$config);
				$editor = hikashop_get('helper.editor');
				$editor->name = $type.'_description';
				$name = $editor->name;
				$editor->content = @$element->$name;
				$toggle=hikashop_get('helper.toggle');
				$this->assignRef('transHelper',$transHelper);
				$this->assignRef('toggle',$toggle);
				$this->assignRef('tabs',$tabs);
				$this->assignRef('editor',$editor);
				$this->assignRef('translation',$translation);
				$this->assignRef('element',$element);
				$this->assignRef('id',$id);
			}
			if($type=='payment'){
				$shippingMethods = hikashop_get('type.plugins');
				$shippingMethods->type='shipping';
				$shippingMethods->manualOnly=true;
				if(!empty($this->element->payment_shipping_methods)){
					$methods = explode("\n",$this->element->payment_shipping_methods);
					$this->element->payment_shipping_methods_id = array();
					$this->element->payment_shipping_methods_type = array();
					foreach($methods as $method){
						list($shipping_type,$shipping_id) = explode('_',$method,2);
						$this->element->payment_shipping_methods_id[] = $shipping_id;
						$this->element->payment_shipping_methods_type[] = $shipping_type;
					}
				}else{
					$this->element->payment_shipping_methods_id = array();
					$this->element->payment_shipping_methods_type = array();
				}
				$this->assignRef('shippingMethods',$shippingMethods);
				$currencies = hikashop_get('type.currency');
				$this->element->payment_currency = explode(',',trim(@$this->element->payment_currency,','));
				$this->assignRef('currencies',$currencies);
			}
			$this->assignRef('plugin',$this->plugin);
			$this->assignRef('content',$this->content);
			$this->assignRef('plugin_type',$type);
			$this->content .= $this->loadPluginTemplate(@$data->view,$type);
		}
    	return true;
	}
	function edit_translation(){
		$language_id = JRequest::getInt('language_id',0);
		$type = JRequest::getString('type');
		$field = $type.'_id';
		$cid = hikashop_getCID($field);
		$class = hikashop_get('class.'.$type);
		$element = $class->get($cid);
		$translation = false;
		$transHelper = hikashop_get('helper.translation');
		if($transHelper && $transHelper->isMulti()){
			$translation = true;
			$transHelper->load('hikashop_'.$type,@$element->$field,$element,$language_id);
			$this->assignRef('transHelper',$transHelper);
		}
		$editor = hikashop_get('helper.editor');
		$desc = $type.'_description';
		$editor->name = $desc;
		$editor->content = @$element->$desc;
		$editor->height=300;
		$this->assignRef('editor',$editor);
		$toggle=hikashop_get('helper.toggle');
		$this->assignRef('toggle',$toggle);
		$this->assignRef('element',$element);
		$this->assignRef('plugin_type',$type);
		jimport('joomla.html.pane');
		$tabs	=& JPane::getInstance('tabs');
		$this->assignRef('tabs',$tabs);
	}
	function selectimages(){
		$type = JRequest::getCmd('type','shipping');
		if(!in_array($type,array('shipping','payment'))){
			$type = 'shipping';
		}
		$path = HIKASHOP_MEDIA.'images'.DS.$type.DS;
		jimport('joomla.filesystem.folder');
		$images = JFolder::files($path);
		$rows = array();
		foreach($images as $image){
			$parts = explode('.',$image);
			$row = null;
			$row->ext = array_pop($parts);
			if(!in_array(strtolower($row->ext),array('gif','png','jpg','jpeg'))) continue;
			$row->id = implode($parts);
			$row->name = str_replace('_',' ',$row->id);
			$row->file = $image;
			$row->full = HIKASHOP_IMAGES .$type.'/'. $row->file;
			$rows[]=$row;
		}
		$selectedImages = JRequest::getVar('values','','','string');
		if(strtolower($selectedImages) == 'all'){
			foreach($rows as $id => $oneRow){
				$rows[$id]->selected = true;
			}
		}elseif(!empty($selectedImages)){
			$selectedImages = explode(',',$selectedImages);
			foreach($rows as $id => $oneRow){
				if(in_array($oneRow->id,$selectedImages)){
					$rows[$id]->selected = true;
				}
			}
		}
		$this->assignRef('rows',$rows);
		$this->assignRef('selectedLists',$selectedImages);
		$this->assignRef('type',$type);
	}
	function loadPluginTemplate($view='',$type=''){
		static $previousType = '';
		if(empty($type)){
			$type=$previousType;
		}else{
			$previousType = $type;
		}
		$app =& JFactory::getApplication();
		if(empty($view)){
			$this->subview = '';
		}else{
			$this->subview = '_'.$view;
		}
		$name = $this->plugin.'_configuration'.$this->subview.'.php';
    	$path = JPATH_THEMES.DS.$app->getTemplate().DS.'hikashop'.$type.DS.$name;
    	if(!file_exists($path)){
    		if(version_compare(JVERSION,'1.6','<')){
    			$path = JPATH_PLUGINS .DS.'hikashop'.$type.DS.$name;
    		}else{
    			$path = JPATH_PLUGINS .DS.'hikashop'.$type.DS. $this->plugin.DS.$name;
    		}
    		if(!file_exists($path)){
    			return '';
    		}
    	}
    	ob_start();
    	require($path);
    	return ob_get_clean();
	}
}