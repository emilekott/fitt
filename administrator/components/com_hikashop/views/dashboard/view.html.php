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
class dashboardViewDashboard extends JView{
	function display($tpl = null,$params=null){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function($params);
		parent::display($tpl);
	}
	function listing(){
		$this->widgets();
		$this->links();
		hikashop_setTitle( HIKASHOP_NAME , 'hikashop' ,'dashboard' );
		$bar = & JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Pophelp','dashboard');
		$this->assignRef('toggleClass',hikashop_get('helper.toggle'));
	}
	function cpanel(){
		$this->links();
	}
	function widgets(){
		$widgetClass = hikashop_get('class.widget');
		$widgets = $widgetClass->get();
		foreach($widgets as $k => $widget){
			$content = @$widget->widget_params->content;
			if(!empty($content)){
				$this->data($widgets[$k]);
			}
		}
		$this->assignRef('widgets',$widgets);
		$doc =& JFactory::getDocument();
		$doc->addScript((hikashop_isSSL() ? 'https://' : 'http://').'www.google.com/jsapi');
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
		if(hikashop_level(1)){
			$config =& hikashop_config();
			$manage = hikashop_isAllowed($config->get('acl_dashboard_manage','all'));
			$this->assignRef('manage',$manage);
			$delete = hikashop_isAllowed($config->get('acl_dashboard_delete','all'));
			$this->assignRef('delete',$delete);
			if($manage){
				$bar = & JToolBar::getInstance('toolbar');
				if(version_compare(JVERSION,'1.6','<')){
					$bar->appendButton( 'Popup', 'new', JText::_('NEW_WIDGET'), hikashop_completeLink('dashboard&task=add',true), 480,380 );
				}else{
					$bar->appendButton( 'Popup', 'new', JText::_('NEW_WIDGET'), 'index.php?option=com_hikashop&ctrl=dashboard&task=add&tmpl=component', 480,380 );
				}
				JHTML::_('behavior.modal');
			}
		}
	}
	function csv(){
		if(hikashop_level(2)){
			$widget_id = hikashop_getCID('widget_id');
			if($widget_id){
				$widgetClass = hikashop_get('class.widget');
				$widget = $widgetClass->get($widget_id);
				$this->data($widget,true);
				$encodingClass = hikashop_get('helper.encoding');
		 		@ob_clean();
				header("Pragma: public");
				header("Expires: 0"); // set expiration time
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");
				header("Content-Disposition: attachment; filename=hikashopexport.csv;");
				header("Content-Transfer-Encoding: binary");
				$eol= "\r\n";
				$config =& hikashop_config();
				$separator = $config->get('csv_separator',";");
				echo implode($separator,$widget->exportFields).$eol;
				$missing = array();
				$convert_date = $config->get('convert_date',DATE_RFC822);
				foreach($widget->elements as $el){
					$line = array();
					foreach($widget->exportFields as $field){
						if(!isset($missing[$field])){
							if(isset($el->$field)){
								if($convert_date && in_array($field,array('user_created','order_created','order_modified'))) $el->$field=hikashop_getDate($el->$field,$convert_date);
								$line[]=str_replace(array("\r","\n"),array('\r','\n'),$el->$field);
							}else{
								$missing[$field]=$field;
							}
						}
					}
					if(empty($missing)){
						echo $encodingClass->change(implode($separator,$line),'UTF-8',$widget->widget_params->format).$eol;
					}
				}
				if(!empty($missing)){
					@ob_clean();
					$fieldsLeft = array();
					foreach($widget->exportFields as $field){
						if(!isset($missing[$field])){
							$fieldsLeft[]=$field;
						}
					}
					echo implode($separator,$fieldsLeft).$eol;
					foreach($widget->elements as $el){
						$line = array();
						foreach($fieldsLeft as $field){
							if($convert_date && in_array($field,array('user_created','order_created','order_modified'))) $el->$field=hikashop_getDate($el->$field,DATE_RFC822);
							$line[]=$el->$field;
						}
						echo $encodingClass->change(implode($separator,$line),'UTF-8',$widget->widget_params->format).$eol;
					}
				}
				exit;
			}else{
				$app =& JFactory::getApplication();
				$app->enqueueMessage();
				$this->listing();
			}
		}
	}
	function data(&$widget,$csv=false){
		$filters = array();
		$leftjoin = array();
		$groupby_add='';
		$select='SELECT ';
		$pageInfo=null;
		if(!hikashop_level(2)){
			if($widget->widget_params->content=='partners' || $widget->widget_params->display=='map') return false;
			if(!hikashop_level(1) && in_array($widget->widget_params->display,array('gauge','pie'))) return false;
		}
		switch($widget->widget_params->content){
			case 'orders':
			case 'sales':
			case 'taxes':
				$date_field = 'a.order_'.@$widget->widget_params->date_type;
				if(!empty($widget->widget_params->status)){
					$filters['status']='a.order_status IN (\''.implode('\',\'',$widget->widget_params->status).'\')';
				}
				if($widget->widget_params->display=='listing'){
					$leftjoin[] = ' LEFT JOIN '.hikashop_table('user').' AS b ON a.order_user_id=b.user_id ';
					$select.='b.*,';
				}
				if($widget->widget_params->content=='orders'){
					$pie = 'COUNT(a.order_id) AS total';
				}elseif($widget->widget_params->content=='taxes'){
					$leftjoin[] = ' LEFT JOIN '.hikashop_table('order_product').' AS c ON a.order_id=c.order_id AND c.order_product_tax > 0 ';
					$pie = 'SUM(c.order_product_tax) AS total,a.order_currency_id AS currency_id';
					$groupby_add=', currency_id';
				}else{
					$pie = 'SUM(a.order_full_price) AS total,a.order_currency_id AS currency_id';
					$groupby_add=', currency_id';
				}
				$widget->widget_params->content_view = 'order';
				$sum = $pie;
				$pie .=',a.order_status AS name';
				$table = 'order';
				$id = 'order_id';
				break;
			case 'partners':
			case 'customers':
				$widget->filter_partner = 1;
				if($widget->widget_params->content=='customers'){
					$widget->filter_partner = 0;
					$filters[]='a.user_partner_activated=0';
				}else{
					$filters[]='a.user_partner_activated=1';
				}
				if($widget->widget_params->display=='listing'){
					$leftjoin[] = ' LEFT JOIN '.hikashop_table('users',false).' AS b ON a.user_cms_id=b.id ';
					$select.='b.*,';
				}
				$table = 'user';
				$date_field = 'a.user_created';
				$sum = 'COUNT(a.user_id) AS total';
				$widget->widget_params->content_view = 'user';
				$id = 'user_id';
				break;
		}
		$limit='';
		switch($widget->widget_params->display){
			case 'gauge':
			case 'graph':

				$config =& JFactory::getConfig();
				$timeoffset = $config->getValue('config.offset');
				$group_string = '';
				switch($widget->widget_params->date_group){
					case '%j %Y':
						$group_string = '%Y %j';
						break;
					case '%u %Y':
						$group_string = '%Y %u';
						break;
					case '%m %Y':
						$group_string = '%Y %m';
						break;
					default:
						$group_string = $widget->widget_params->date_group;
						break;
				}
				$timeoffset = (int)($timeoffset*60*60)-(int)@$widget->widget_params->offset;
				if($timeoffset>=0){
					$timeoffset = '+'.$timeoffset;
				}
				$group = 'DATE_FORMAT(FROM_UNIXTIME(CAST('.$date_field.' AS SIGNED )'.$timeoffset.'),\''.$group_string.'\')';
				$select .=$group.' AS calculated_date, '.$sum;
				$limit.=' GROUP BY calculated_date'.$groupby_add;
				$limit.=' ORDER BY calculated_date DESC';
				break;
			case 'listing':
				if(!empty($id)){
					$limit.=' ORDER BY a.'.$id.' DESC';
				}
				if(!empty($widget->widget_params->limit) && !$csv){
					$limit.=' LIMIT '.(int)$widget->widget_params->limit;
				}
				$select.='a.*';
				break;
			case 'pie':
				$select.=$pie;
				$limit.=' GROUP BY a.order_status'.$groupby_add;
				break;
			case 'map':
				$leftjoin[] = ' LEFT JOIN '.hikashop_table('geolocation').' AS b ON a.'.$id.'=b.geolocation_ref_id AND b.geolocation_type=\''.$table.'\' AND b.geolocation_country_code != \'RD\'';
				$select.='b.geolocation_country_code AS code, b.geolocation_country AS name, '.$sum;
				$limit.=' GROUP BY b.geolocation_country_code'.$groupby_add;
				break;
			default:
				return false;
				break;
		}
		$end=time();
		if(!empty($widget->widget_params->start)){
			$filters['start']=$date_field.' > '.$widget->widget_params->start;
		}
		if(!empty($widget->widget_params->end)){
			$filters['end']=$date_field.' < '.$widget->widget_params->end;
			$end = $widget->widget_params->end;
		}
		if((empty($filters['start']) || empty($filters['end'])) && !empty($widget->widget_params->period)){
			if(!empty($filters['start'])){
				$filters['end']=$date_field.' < '.($widget->widget_params->start+$widget->widget_params->period);
			}else{
				$filters['start']=$date_field.' > '.($end-$widget->widget_params->period);
			}
		}
		$filters = (empty($filters)? ' ':' WHERE ').implode(' AND ',$filters);
		$leftjoin = implode(' ',$leftjoin);
		$query=$select.' FROM '.hikashop_table($table).' AS a '.$leftjoin.$filters.$limit;
		$db =& JFactory::getDBO();
		$db->setQuery($query);
		$elements = $db->loadObjectList();
		if(!empty($elements)){
			$first = reset($elements);
			if($widget->widget_params->content=='sales' && isset($first->currency_id)){
				$currencyClass=hikashop_get('class.currency');
				$currencyClass->convertStats($elements);
				if($widget->widget_params->display=='pie'){
					$group = 'name';
				}elseif($widget->widget_params->display=='map'){
					$group = 'code';
				}else{
					$group = 'calculated_date';
				}
				$newElements = array();
				foreach($elements as $k => $element){
					if(!isset($newElements[$element->$group])){
						$newElements[$element->$group]=$element;
					}else{
						$newElements[$element->$group]->total += $element->total;
					}
				}
				$elements = $newElements;
			}
		}
		switch($widget->widget_params->display){
			case 'gauge':
				if(empty($widget->widget_params->end)){
					$widget->widget_params->end=time();
				}
				$current = $this->_mysqlDate($widget->widget_params->date_group,$widget->widget_params->end);
				$total = 0.0;
				$main=0.0;
				$average = 0.0;
				$same = array();
				$i = 0;
				if(!empty($elements)){
					foreach($elements as $k => $period){
						if($period->calculated_date==$current){
							$main = $period->total;
						}else{
							$total+=$period->total;
							if(!isset($same[$period->calculated_date])){
								$i++;
								$same[$period->calculated_date]=$period->calculated_date;
							}
						}
					}
				}
				if($i){
					$average = $total/$i;
				}
				$widget->average = $average;
				$widget->total = $total;
				$widget->main = $main;
				$widget->exportFields = array('calculated_date','total');
				break;
			case 'map':
				$widget->exportFields = array('code','name','total');
				if(!empty($elements)){
					$newElements = array();
					foreach($elements as $k => $element){
						if(!empty($element->code)){
							$newElements[$element->code]=$element;
						}
					}
					$elements = $newElements;
				}
				break;
			case 'graph':
				$dates = array();
				$minimum = 0;
				if(!empty($elements)){
					foreach($elements as $k => $element){
						$this->_jsDate($widget->widget_params->date_group,$element);
						if(empty($minimum) || $minimum>$element->timestamp){
							$minimum = $element->timestamp;
						}
						$dates[$element->calculated_date] = $element;
					}
				}
				if(empty($widget->widget_params->end)){
					$widget->widget_params->end=time();
				}
				if(empty($widget->widget_params->start)){
					if(!empty($widget->widget_params->period)){
						$widget->widget_params->start = $widget->widget_params->end - $widget->widget_params->period;
					}else{
						if($minimum==0){
							$minimum = time();
						}
						$widget->widget_params->start = $minimum;
					}
				}
				$end = $widget->widget_params->end;
				$obj = null;
				$obj->timestamp = $end;
				$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
				if(!isset($dates[$obj->calculated_date])){
					$this->_jsDate($widget->widget_params->date_group,$obj);
					$obj->total = 0;
					$dates[$obj->calculated_date] = $obj;
				}
				switch($widget->widget_params->date_group){
					case '%j %Y':
						$period = 3600*24;
						break;
					case '%u %Y':
						$period = 7*3600*24;
						break;
					case '%m %Y':
						$period = 30*3600*24;
						break;
					case '%Y':
						$period = 365*3600*24;
						break;
					default:
						$period = 365*3600*24;
						break;
				}
				while($widget->widget_params->start<$end){
					$end = $end-$period;
					$obj = null;
					$obj->timestamp = $end;
					$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
					if(!isset($dates[$obj->calculated_date])){
						$this->_jsDate($widget->widget_params->date_group,$obj);
						$obj->total = 0;
						$dates[$obj->calculated_date]=$obj;
					}
				}
				$obj = null;
				$obj->timestamp = $widget->widget_params->start;
				$obj->calculated_date = $this->_mysqlDate($widget->widget_params->date_group,$obj->timestamp);
				if(!isset($dates[$obj->calculated_date])){
					$this->_jsDate($widget->widget_params->date_group,$obj);
					$obj->total = 0;
					$dates[$obj->calculated_date]=$obj;
				}
				$elements = array();
				foreach($dates as $date){
					$elements[$date->timestamp]=$date;
				}
				ksort($elements);
				$widget->exportFields = array('calculated_date','total');
				break;
			case 'pie':
				$name = 'name';
				$widget->exportFields = array('name','total');
			case 'listing':
				if($widget->widget_params->display=='listing'){
					$name = 'order_status';
					if(!empty($elements)){
						$first = reset($elements);
						unset($first->user_params);
						$widget->exportFields = array_keys(get_object_vars($first));
					}else{
						$widget->exportFields=array();
					}
				}
				$class = hikashop_get('class.category');
				$trans = $class->loadAllWithTrans('status');
				if(!empty($elements)){
					foreach($elements as $k => $element){
						if(!empty($element->$name)){
							$found = false;
							if(!empty($trans)){
								foreach($trans as $t){
									if($t->category_name == $element->$name && isset($t->translation)){
										$elements[$k]->$name = $t->translation;
										$found = true;
									}
								}
							}
							if(!$found){
								$fileTrans = JText::_(strtoupper($element->$name));
								if($fileTrans != strtoupper($element->$name)){
									$elements[$k]->$name = $fileTrans;
								}
							}
						}
					}
				}
				break;
			default:
				break;
		}
		$widget->elements =& $elements;
		return true;
	}
	function _mysqlDate($group,$date){
		$current_year=date('Y',$date);
		switch($group){
			case '%j %Y':
				$current_day = sprintf('%03d',date('z',$date))+1;
				$current = $current_year.' '.$current_day;
				break;
			case '%u %Y':
				$current_week = sprintf('%02d',date('W',$date));
				$current = $current_year.' '.$current_week;
				break;
			case '%m %Y':
				$current_month = date('m',$date);
				$current=$current_year.' '.$current_month;
				break;
			case '%Y':
				$current=$current_year;
				break;
			default:
				$current='';
				break;
		}
		return $current;
	}
	function _jsDate($group,&$element){
		if(!isset($element->timestamp)){
			switch($group){
				case '%j %Y'://day
					$parts = explode(' ',$element->calculated_date);
					$element->timestamp = mktime(0, 0, 0, 1, $parts[1], $parts[0]);
					break;
				case '%u %Y'://week
					$parts = explode(' ',$element->calculated_date);
					$element->timestamp = mktime(0, 0, 0, 1, $parts[1]*7, $parts[0]);
					break;
				case '%m %Y'://month
					$parts = explode(' ',$element->calculated_date);
					$element->timestamp = mktime(0, 0, 0, $parts[1], 1, $parts[0]);
					break;
				case '%Y'://year
					$element->timestamp = mktime(0, 0, 0, 1, 1, $element->calculated_date);
					break;
			}
		}
		$element->year = date('Y',$element->timestamp);
		$element->month = date('m',$element->timestamp)-1;
		$element->day = date('d',$element->timestamp);
	}
	function links(){
		$buttons = array();
		$desc = array();
		$desc['product'] = '<ul><li>'.JText::_('PRODUCTS_DESC_CREATE').'</li><li>'.JText::_('PRODUCTS_DESC_MANAGE').'</li><li>'.JText::_('CHATACTERISTICS_DESC_MANAGE').'</li></ul>';
		$desc['category'] = '<ul><li>'.JText::_('CATEGORIES_DESC_CREATE').'</li></ul>';
		$desc['user'] = '<ul><li>'.JText::_('CUSTOMERS_DESC_CREATE').'</li><li>'.JText::_('CUSTOMERS_DESC_MANAGE').'</li></ul>';
		$desc['order'] = '<ul><li>'.JText::_('ORDERS_DESC').'</li><li>'.JText::_('ORDERS_DESC_STATUS').'</li></ul>';
		$desc['banner'] = '<ul><li>'.JText::_('AFFILIATES_DESC').'</li>';
		$desc['banner'] .= '<li>'.JText::_('AFFILIATES_DESC_BANNERS').'</li>';
		$desc['banner'] .= '<li>'.JText::_('AFFILIATES_DESC_SALES').'</li></ul>';
		if(!hikashop_level(2)){
			$desc['banner'] .= ' <small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
		}
		$desc['zone'] = '<ul><li>'.JText::_('ZONE_DESC').'</li><li>'.JText::_('ZONE_DESC_TAXES').'</li></ul>';
		$desc['discount'] = '<ul><li>'.JText::_('DISCOUNT_DESC').'</li><li>'.JText::_('DISCOUNT_DESC_LIMITS');
		if(!hikashop_level(1)){
			$desc['discount'] .= ' <small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
		}
		$desc['discount'] .= '</li></ul>';
		 $desc['currency'] = '<ul><li>'.JText::_('CURRENCY_DESC').'</li><li>'.JText::_('CURRENCY_DESC_RATES');
		if(!hikashop_level(1)){
			$desc['currency'] .= ' <small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
		}
		$desc['currency'] .= '</li></ul>';
		$desc['plugins'] = '<ul><li>'.JText::_('PLUGINS_DESC_PAYMENT').'</li><li>'.JText::_('PLUGINS_DESC_SHIPPING').'</li></ul>';
		$desc['view'] = '<ul><li>'.JText::_('DISPLAY_DESC_VIEW').'</li><li>'.JText::_('DISPLAY_DESC_CONTENT').'</li><li>'.JText::_('DISPLAY_DESC_FIELDS').'</li></ul>';
		$desc['config'] = '<ul><li>'.JText::_('CONFIG_DESC_CONFIG').'</li><li>'.JText::_('CONFIG_DESC_MODIFY').'</li><li>'.JText::_('CONFIG_DESC_EMAIL');
		if(!hikashop_level(1)){
			$desc['config'] .= ' <small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
		}
		$config =& hikashop_config();
		if(hikashop_isAllowed($config->get('acl_config_view','all'))) $desc['config'] .= '</li><li>'.JText::_('CONFIG_DESC_PLUGIN').'</li></ul>';
		$desc['documentation'] = '<ul><li>'.JText::_('HELP_DESC').'</li><li>'.JText::_('UPDATE_DESC').'</li><li>'.JText::_('FORUM_DESC').'</li></ul>';
		if(hikashop_isAllowed($config->get('acl_product_view','all'))) $buttons[] = array('link'=>'product','level'=>0,'image'=>'generic','text'=>JText::_('PRODUCTS'));
		if(hikashop_isAllowed($config->get('acl_category_view','all'))) $buttons[] = array('link'=>'category','level'=>0,'image'=>'categories','text'=>JText::_('HIKA_CATEGORIES'));
		if(hikashop_isAllowed($config->get('acl_user_view','all'))) $buttons[] = array('link'=>'user','level'=>0,'image'=>'user','text'=>JText::_('CUSTOMERS'));
		if(hikashop_isAllowed($config->get('acl_order_view','all'))) $buttons[] = array('link'=>'order','level'=>0,'image'=>'order','text'=>JText::_('ORDERS'));
		if(hikashop_isAllowed($config->get('acl_banner_view','all'))) $buttons[] = array('link'=>'banner','level'=>2,'image'=>'affiliate','text'=>JText::_('AFFILIATES'));
		if(hikashop_isAllowed($config->get('acl_zone_view','all'))) $buttons[] = array('link'=>'zone','level'=>0,'image'=>'langmanager','text'=>JText::_('ZONES'));
		if(hikashop_isAllowed($config->get('acl_discount_view','all'))) $buttons[] = array('link'=>'discount','level'=>0,'image'=>'discount','text'=>JText::_('DISCOUNTS'));
		if(hikashop_isAllowed($config->get('acl_currency_view','all'))) $buttons[] = array('link'=>'currency','level'=>0,'image'=>'currency','text'=>JText::_('CURRENCIES'));
		if(hikashop_isAllowed($config->get('acl_plugins_view','all'))) $buttons[] = array('link'=>'plugins','level'=>0,'image'=>'plugin','text'=>JText::_('PLUGINS'));
		if(hikashop_isAllowed($config->get('acl_view_view','all'))) $buttons[] = array('link'=>'view','level'=>0,'image'=>'menumgr','text'=>JText::_('DISPLAY'));
		if(hikashop_isAllowed($config->get('acl_config_view','all'))) $buttons[] = array('link'=>'config','level'=>0,'image'=>'config','text'=>JText::_('HIKA_CONFIGURATION'));
		$buttons[] = array('link'=>'documentation','level'=>0,'image'=>'install','text'=>JText::_('UPDATE_ABOUT'));
		$htmlbuttons = array();
		foreach($buttons as $oneButton){
			$htmlbuttons[] = $this->_quickiconButton($oneButton['link'],$oneButton['image'],$oneButton['text'],$desc[$oneButton['link']],$oneButton['level']);
		}
		$this->assignRef('buttons',$htmlbuttons);
	}
	function _quickiconButton( $link, $image, $text,$description,$level){
		$url = hikashop_level($level) ? 'onclick="document.location.href=\''.hikashop_completeLink($link).'\';"' : '';
		$html = '<div style="float:left;width: 100%;" '.$url.' class="icon"><a href="';
		$html .= hikashop_level($level) ? hikashop_completeLink($link) : '#';
		$html .= '"><table width="100%"><tr><td style="text-align: center;" width="120px">';
		$html .= '<span class="icon-48-'.$image.'" style="background-repeat:no-repeat;background-position:center;height:48px" title="'.$text.'"> </span>';
		$html .= '<span>'.$text.'</span></td><td>'.$description.'</td></tr></table></a>';
		$html .= '</div>';
		return $html;
	}
	function form(){
		if(hikashop_level(1)){
			$cid = hikashop_getCID('widget_id');
			if(empty($cid)){
				$element = null;
				$task='add';
			}else{
				$widgetClass = hikashop_get('class.widget');
				$element = $widgetClass->get($cid);
				$task='edit';
			}
			if(empty($element->widget_params->format)){
				$element->widget_params->format = 'UTF-8';
			}
			if(empty($element->widget_params->limit)){
				$element->widget_params->limit = '7';
			}
			$this->assignRef('element',$element);
			$widgetContent = hikashop_get('type.widgetcontent');
			$this->assignRef('widgetContent',$widgetContent);
			$widgetDisplay = hikashop_get('type.widgetdisplay');
			$this->assignRef('widgetDisplay',$widgetDisplay);
			$dateGroup = hikashop_get('type.dategroup');
			$this->assignRef('dateGroup',$dateGroup);
			$dateType = hikashop_get('type.datetype');
			$this->assignRef('dateType',$dateType);
			$delay = hikashop_get('type.delay');
			$this->assignRef('delay',$delay);
			$status = hikashop_get('type.categorysub');
			$status->type='status';
			$this->assignRef('status',$status);
			if(hikashop_level(2)){
				$region = hikashop_get('type.region');
				$this->assignRef('region',$region);
				$encoding = hikashop_get('type.charset');
				$this->assignRef('encoding',$encoding);
			}
		}
	}
}
