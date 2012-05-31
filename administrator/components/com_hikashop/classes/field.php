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
class hikashopFieldClass extends hikashopClass{
	var $tables = array('field');
	var $pkeys = array('field_id');
	var $namekeys = array();
	var $errors = array();
	var $prefix = '';
	var $suffix = '';
	var $excludeValue = array();
	var $toggle = array('field_required'=>'field_id','field_published'=>'field_id','field_backend'=>'field_id','field_backend_listing'=>'field_id','field_frontcomp'=>'field_id','field_core'=>'field_id');
	var $where = array();
	var $skipAddressName=false;
	var $report = true;
	function & getData($area,$type,$notcoreonly=false, $categories=null){
		static $data = array();
		$key = $area.'_'.$type.'_'.$notcoreonly;
		if(!isset($data[$key])){
			$this->where = array();
			$this->where[] = 'a.`field_published` = 1';
			if($area == 'backend'){
				$this->where[] = 'a.`field_backend` = 1';
			}elseif($area == 'frontcomp'){
				$this->where[] = 'a.`field_frontcomp` = 1';
			}elseif($area=='backend_listing'){
				$this->where[] = 'a.`field_backend_listing` = 1';
			}
			if($notcoreonly){
				$this->where[] = 'a.`field_core` = 0';
			}
			if($this->skipAddressName){
				$this->where[]='a.field_namekey!=\'address_name\'';
			}
			$this->where[]='a.field_table='.$this->database->Quote($type);
			$filters='';
			if(!empty($categories)){
				$categories_filter=array('AND ((field_with_sub_categories=0 AND (field_categories="all"');
				if(!empty($categories['originals'])){
					foreach($categories['originals'] as $cat){
						$categories_filter[]='field_categories LIKE \'%,'.$cat.',%\'';
					}
				}
				$filters=implode(' OR ',$categories_filter).'))';
				$categories_filter=array('OR (field_with_sub_categories=1 AND (field_categories="all"');
				if(!empty($categories['parents'])){
					foreach($categories['parents'] as $cat){
						$categories_filter[]='field_categories LIKE \'%,'.$cat.',%\'';
					}
				}
				$filters.=implode(' OR ',$categories_filter).')))';
			}
			hikashop_addACLFilters($this->where,'field_access','a');
			$this->database->setQuery('SELECT * FROM '.hikashop_table('field').' as a WHERE '.implode(' AND ',$this->where).' '.$filters.' ORDER BY a.`field_ordering` ASC');
			$data[$key] = $this->database->loadObjectList('field_namekey');
		}
		return $data[$key];
	}
	function getField($fieldid,$type=''){
		if(is_numeric($fieldid)){
			$element = parent::get($fieldid);
		}else{
			$this->database->setQuery('SELECT * FROM '.hikashop_table('field').' WHERE field_table='.$this->database->Quote($type).' AND field_namekey='.$this->database->Quote($fieldid));
			$element = $this->database->loadObject();
		}
		$fields = array($element);
		$data = null;
		$this->prepareFields($fields,$data,$fields[0]->field_type,'',true);
		return $fields[0];
	}
	function getFields($area,&$data,$type='user',$url='checkout&task=state'){
		$allCat=$this->getCategories($type, $data);
		$fields = $this->getData($area,$type, false, $allCat);
		$this->prepareFields($fields,$data,$type,$url);
		return $fields;
	}
	function getCategories($type, &$data){
		$allCat=null;
		if(!empty($data)){
			if($type=='product' || $type=='item'){
				if(!empty($data->product_id)){
					static $categories=array();
					if(!isset($categories[$data->product_id])){
						$categories[$data->product_id]['originals']=array();
						$categories[$data->product_id]['parents']=array();
						$categoryClass = hikashop_get('class.category');
						if(!empty($data->categories)){
							foreach($data->categories as $category){
								$categories[$data->product_id]['originals'][$category->category_id]=$category->category_id;
							}
							$parents = $categoryClass->getParents($data->categories);
						}else{
							$productClass = hikashop_get('class.product');
							$loadedCategories=$productClass->getCategories($data->product_id);
							if(!empty($loadedCategories)){
								foreach($loadedCategories as $cat){
									$categories[$data->product_id]['originals'][$cat]=$cat;
								}
							}
							$parents = $categoryClass->getParents($loadedCategories);
						}
						foreach($parents as $parent){
							$categories[$data->product_id]['parents'][$parent->category_id]=$parent->category_id;
						}
					}
					$allCat =& $categories[$data->product_id];
				}
			}
			if($type=='category' && !empty($data->category_id)){
				static $categories2=array();
				if(!isset($categories2[$data->category_id])){
					$categories2[$data->category_id]['originals'][$data->category_id]=$data->category_id;
					$categoryClass = hikashop_get('class.category');
					$parents = $categoryClass->getParents($data->category_id);
					if(!empty($parents)){
						foreach($parents as $parent){
							$categories2[$data->category_id]['parents'][$parent->category_id]=$parent->category_id;
						}
					}
				}
				$allCat =& $categories2[$data->category_id];
			}
		}
		return $allCat;
	}
	function chart($table,$field,$order_status='',$width=0,$height=0){
		static $a = false;
		$doc =& JFactory::getDocument();
		if(!$a){
			$a = true;
			$doc->addScript(((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on" ) ? 'http://' : 'https://')."www.google.com/jsapi");
		}
		$namekey = hikashop_secureField($field->field_namekey);
		if(empty($order_status)){
			if($table=='item') $table ='order_product';
			$this->database->setQuery('SELECT COUNT(`'.$namekey.'`) as total,`'.$namekey.'` as name FROM '.hikashop_table($table).' WHERE `'.$namekey.'` IS NOT NULL AND `'.$namekey.'` != \'\' GROUP BY `'.$namekey.'` ORDER BY total DESC LIMIT 20');
		}elseif($table=='entry'){
			$this->database->setQuery('SELECT COUNT(a.`'.$namekey.'`) as total,a.`'.$namekey.'` as name FROM '.hikashop_table($table).' AS a LEFT JOIN '.hikashop_table('order').' AS b ON a.order_id=b.order_id WHERE b.order_status='.$this->database->Quote($order_status).' AND a.`'.$namekey.'` IS NOT NULL AND a.`'.$namekey.'` != \'\' GROUP BY a.`'.$namekey.'` ORDER BY total DESC LIMIT 20');
		}
		if(empty($width)){
			$width=600;
		}
		if(empty($height)){
			$height=400;
		}
		$results = $this->database->loadObjectList();?>
		<script language="JavaScript" type="text/javascript">
		 function drawChart<?php echo $namekey; ?>() {
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn('string');
        	dataTable.addColumn('number');
			dataTable.addRows(<?php echo count($results); ?>);
			<?php
			foreach($results as $i => $oneResult){
				$name = isset($field->field_value[$oneResult->name]) ? $this->trans(@$field->field_value[$oneResult->name]->value) : $oneResult->name; ?>
				dataTable.setValue(<?php echo $i ?>, 0, '<?php echo addslashes($name).' ('.$oneResult->total.')'; ?>');
				dataTable.setValue(<?php echo $i ?>, 1, <?php echo intval($oneResult->total); ?>);
			<?php } ?>
			var vis = new google.visualization.PieChart(document.getElementById('fieldchart<?php echo $namekey;?>'));
	        var options = {
	    	  title: '<?php echo addslashes($field->field_realname);?>',
	          width: <?php echo $width;?>,
	          height: <?php echo $height;?>,
	          is3D:true,
	          legendTextStyle: {color:'#333333'}
	        };
	        vis.draw(dataTable, options);
      }
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart<?php echo $namekey; ?>);
      </script>
		<div class="hikachart" style="width:<?php echo $width;?>px;height:<?php echo $height;?>px;" id="fieldchart<?php echo $namekey;?>"></div>
<?php
	}
	function prepareFields(&$fields,&$data,$type='user',$url='checkout&task=state',$test=false){
		if(!empty($fields)){
			$id = $type.'_id';
			foreach($fields as $namekey => $field){
				if(!empty($fields[$namekey]->field_options) && is_string($fields[$namekey]->field_options)){
					$fields[$namekey]->field_options = unserialize($fields[$namekey]->field_options);
				}
				if(!empty($field->field_value) && is_string($fields[$namekey]->field_value)){
					$fields[$namekey]->field_value = $this->explodeValues($fields[$namekey]->field_value);
				}
				if(empty($data->$id) && empty($data->$namekey)){
					$data->$namekey = $field->field_default;
				}
				if(!empty($fields[$namekey]->field_options['zone_type']) && $fields[$namekey]->field_options['zone_type']=='country'){
					$baseUrl = hikashop_completeLink($url,true,true);
					if(strpos($baseUrl,'?')!==false){
						$baseUrl.='&';
					}else{
						$baseUrl.='?';
					}
					$fields[$namekey]->field_url=$baseUrl;
				}
			}
			$this->handleZone($fields,$test);
		}
	}
	function handleZone(&$fields,$test=false){
		$types = array();
		foreach($fields as $k => $field){
			if($field->field_type=='zone' && !empty($field->field_options['zone_type'])){
				if($field->field_options['zone_type']!='state'){
					$types[$field->field_options['zone_type']]=$field->field_options['zone_type'];
				}elseif($test){
					$allFields = $this->getData('',$field->field_table,false);
					foreach($allFields as $i => $oneField){
						if(!empty($oneField->field_options)){
							$oneField->field_options = unserialize($oneField->field_options);
						}
						if($oneField->field_type=='zone' && !empty($oneField->field_options['zone_type']) && $oneField->field_options['zone_type']=='country'){
							$zoneClass = hikashop_get('class.zone');
							$zone = $zoneClass->get($oneField->field_default);
							if(empty($zone) || !$zone->zone_published){
								$config =& hikashop_config();
								$zone_id = explode(',',$config->get('main_tax_zone',$zone_id));
								if(count($zone_id)){
									$zone_id = array_shift($zone_id);
								}
								$ok =false;
								if($zone->zone_id!=$zone_id){
									$newZone = $zoneClass->get($zone_id);
									if($newZone->zone_pbulished){
										$allFields[$i]->field_default = $newZone-->zone_namekey;
										$oneField->field_default = $newZone-->zone_namekey;
										$oneField->field_options = serialize($oneField->field_options);
										$this->save($oneField);
										$ok = true;
									}
								}
								if(!$ok){
									$app =& JFactory::getApplication();
									if(empty($zone)){
										$app->enqueueMessage('In your custom zone field "'.$oneField->field_namekey.'", you have the zone "'.$oneField->field_default. '". However, that zone does not exist. Please change your custom field accordingly.','error');
									}else{
										$app->enqueueMessage('In your custom zone field "'.$oneField->field_namekey.'", you have the zone "'.$oneField->field_default. '". However, that zone is unpublished. Please change your custom field accordingly.','error');
									}
								}
							}
							$zoneType = hikashop_get('type.country');
							$zoneType->type = 'state';
							$zoneType->published = true;
							$zoneType->country_name = $oneField->field_default;
							$zones = $zoneType->load();
							$this->setValues($zones,$fields,$k,$field);
							break;
						}
					}
				}
			}
		}
		if(!empty($types)){
			$zoneType = hikashop_get('type.country');
			$zoneType->type = $types;
			$zoneType->published = true;
			$zones = $zoneType->load();
			if(!empty($zones)){
				foreach($fields as $k => $field){
					$this->setValues($zones,$fields,$k,$field);
				}
			}
		}
	}
	function handleZoneListing(&$fields,&$rows){
		$values = array();
		foreach($fields as $k => $field){
			if($field->field_type=='zone'){
				$field_namekey = $field->field_namekey;
				foreach($rows as $row){
					if(!empty($row->$field_namekey)){
						$values[$row->$field_namekey]=$this->database->Quote($row->$field_namekey);
					}
				}
			}
		}
		if(!empty($values)){
			$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey IN ('.implode(',',$values).')';
			$this->database->setQuery($query);
			$zones = $this->database->loadObjectList('zone_namekey');
			foreach($fields as $k => $field){
				if($field->field_type=='zone'){
					$field_namekey = $field->field_namekey;
					foreach($rows as $k => $row){
						if(!empty($row->$field_namekey)){
							foreach($zones as $zone){
								if($zone->zone_namekey==$row->$field_namekey){
									$title = $zone->zone_name_english;
									if($zone->zone_name_english != $zone->zone_name){
										$title.=' ('.$zone->zone_name.')';
									}
									$rows[$k]->$field_namekey=$title;
									break;
								}
							}
						}
					}
				}
			}
		}
	}
	function setValues(&$zones,&$fields,$k,&$field){
		foreach($zones as $zone){
			if($field->field_type=='zone' && !empty($field->field_options['zone_type']) && $field->field_options['zone_type']==$zone->zone_type){
				$title = $zone->zone_name_english;
				if($zone->zone_name_english != $zone->zone_name){
					$title.=' ('.$zone->zone_name.')';
				}
				$obj = null;
				$obj->value = $title;
				$obj->disabled = '0';
				$fields[$k]->field_value[$zone->zone_namekey]=$obj;
			}
		}
	}
	function getInput($type,&$oldData,$report=true,$varname='data',$force=false){
		$this->report = $report;
		$data = null;
		static $formData = null;
		if($force || !isset($formData)){
			$formData = JRequest::getVar( $varname, array(), '', 'array' );
		}
		if(empty($formData[$type])){
			$formData[$type]=array();
		}
		$app =& JFactory::getApplication();
		if($app->isAdmin()){
			$area = 'backend';
		}else{
			$area = 'frontcomp';
		}
		$allCat=$this->getCategories($type, $oldData);
		$fields =& $this->getData($area,$type, true, $allCat);
		if(!empty($fields)){
			foreach($fields as $namekey => $field){
				if(!empty($fields[$namekey]->field_options) && is_string($fields[$namekey]->field_options)){
					$fields[$namekey]->field_options = unserialize($fields[$namekey]->field_options);
				}
			}
		}
		if($type=='entry' && $area=='frontcomp'){
			$ok = true;
			$data=array();
			foreach($formData[$type] as $key => $form){
				$obj=null;
				$data[$key]=$obj;
				if(!isset($formData[$type][$key])){
					$formData[$type][$key]='';
				}
				if(!$this->_checkOneInput($fields,$formData[$type][$key],$data[$key],$type,$oldData)){
					$ok = false;
				}
			}
		}else{
			if(!isset($formData[$type])){
				$formData[$type]='';
			}
			$ok = $this->_checkOneInput($fields,$formData[$type],$data,$type,$oldData);
		}
		$_SESSION['hikashop_'.$type.'_data']=$data;
		if(!$ok){
			return $ok;
		}
		return $data;
	}
	function _checkOneInput(&$fields,&$formData,&$data,$type,&$oldData){
		$ok = true;
		if(!empty($fields)){
			foreach($fields as $k => $field){
				$namekey = $field->field_namekey;
				if($field->field_type == "customtext"){
					if(isset($formData[$field->field_namekey])) unset($formData[$field->field_namekey]);
					continue;
				}
				if(!empty($field->field_options['limit_to_parent'])){
					$parent = $field->field_options['limit_to_parent'];
					if(!isset($field->field_options['parent_value'])){
						$field->field_options['parent_value']='';
					}
					$skip = false;
					foreach($fields as $otherField){
						if($otherField->field_namekey==$parent){
							if(!isset($formData[$parent]) || $field->field_options['parent_value']!=$formData[$parent]){
								if(isset($formData[$namekey])){
									unset($formData[$namekey]);
								}
								$skip=true;
							}
							break;
						}
					}
					if($skip && $field->field_required){
						continue;
					}
				}
				$classType = 'hikashop'.ucfirst($field->field_type);
				$class = new $classType($this);
				$val = @$formData[$namekey];
				if(!$class->check($fields[$k],$val,@$oldData->$namekey)){
					$ok = false;
				}
				$formData[$namekey] = $val;
			}
		}
		$this->checkFields($formData,$data,$type,$fields);
		return $ok;
	}
	function checkFields(&$data,&$object,$type,&$fields){
		$app =& JFactory::getApplication();
		static $safeHtmlFilter= null;
		if($app->isAdmin()){
			if (is_null($safeHtmlFilter)) {
				jimport('joomla.filter.filterinput');
				$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
			}
		}
		$noFilter = array();
		foreach($fields as $field){
			if(isset($field->field_options['filtering']) && !$field->field_options['filtering']){
				$noFilter[]=$field->field_namekey;
			}
		}
		if(!empty($data) && is_array($data)){
			foreach($data as $column => $value){
				$column = trim(strtolower($column));
				if($this->allowed($column,$type)){
					hikashop_secureField($column);
					if(is_array($value)){
						if($type=='user' && $column=='user_params' || $type=='order' && $app->isAdmin() && in_array($column,array('history','mail','product'))){
							$object->$column = null;
							foreach($value as $c => $v){
								$c = trim(strtolower($c));
								if($this->allowed($c,$type)){
									hikashop_secureField($c);
									$object->$column->$c = in_array($c,$noFilter) ? $v : strip_tags($v);
								}
							}
						}else{
							$value = implode(',',$value);
							$object->$column = in_array($column,$noFilter) ? $value : strip_tags($value);
						}
					}elseif(is_null($safeHtmlFilter)){
						$object->$column = in_array($column,$noFilter) ? $value : strip_tags($value);
					}else{
						$object->$column = in_array($column,$noFilter) ? $value : $safeHtmlFilter->clean($value, 'string');
					}
				}
			}
		}
	}
	function checkFieldsForJS(&$extraFields,&$requiredFields,&$validMessages,&$values){
		foreach($extraFields as $type => $oneType){
			foreach($oneType as $k => $oneField){
				if(empty($oneField->field_js_added)){
					$classType = 'hikashop'.ucfirst($oneField->field_type);
					$class = new $classType($this);
					$class->JSCheck($oneField,$requiredFields[$type],$validMessages[$type],$values[$type]);
				}
				$extraFields[$type][$k]->field_js_added = true;
			}
		}
	}
	function addJS(&$requiredFields,&$validMessages,$types=array()){
		static $done = false;
		$doc =& JFactory::getDocument();
		if(!$done){
			$js="var hikashop=Array();
			hikashop['reqFieldsComp']=Array();
			hikashop['validFieldsComp']=Array();";
			$doc->addScriptDeclaration( "<!--\n".$js."\n//-->" );
			$done = true;
		}
		$js='';
		if(!empty($types)){
			foreach($types as $type){
				if(!empty($requiredFields[$type])){
					$js .= "
					hikashop['reqFieldsComp']['".$type."'] = Array('".implode("','",$requiredFields[$type])."');
					hikashop['validFieldsComp']['".$type."'] = Array('".implode("','",$validMessages[$type])."');";
				}
				if($type=='register'){
					$js.="
					hikashop['password_different'] = '".JText::_('PASSWORDS_DO_NOT_MATCH')."';
					hikashop['valid_email'] = '".JText::_('VALID_EMAIL')."';";
				}elseif($type=='address'){
					$js.="
					hikashop['valid_phone'] = '".JText::_('VALID_PHONE')."';";
				}
			}
		}
		if(!empty($js)){
			$doc->addScriptDeclaration( "<!--\n".$js."\n//-->" );
		}
	}
	function jsToggle(&$fields,$data,$id=1){
		$doc =& JFactory::getDocument();
		$parents = $this->getParents($fields);
		if(empty($parents)){
			return false;
		}
		$first = reset($parents);
		$type = $first->type;
		$js ="hikashop['".$type."']=Array();";
		foreach($parents as $namekey => $parent){
			$js.="
			hikashop['".$type."']['".$namekey."']=Array();";
			foreach($parent->childs as $value => $childs){
				$js.="
			hikashop['".$type."']['".$namekey."']['".$value."']=Array();";
				foreach($childs as $field){
					$js.="
			hikashop['".$type."']['".$namekey."']['".$value."']['".$field->field_namekey."']='".$field->field_namekey."';";
				}
			}
		}
		static $done = false;
		if(!$done){
			$js.="
			function hikashopToggleFields(new_value,namekey,field_type,id){
				var arr = new Array();
				var checked = 0;
				arr = document.getElementsByName('data['+field_type+']['+namekey+'][]');
				if(typeof arr[0] != 'undefined' && typeof arr[0].length != 'undefined'){
					var size = arr[0].length;
				}else{
					var size = arr.length;
				}
				for(var c = 0; c < size; c++){
					if(typeof arr[0] != 'undefined' && typeof arr[0].length != 'undefined'){
						var obj = document.getElementsByName('data['+field_type+']['+namekey+'][]').item(0).item(c);
					}else{
						var obj = document.getElementsByName('data['+field_type+']['+namekey+'][]').item(c);
					}
					if((typeof obj.checked != 'undefined' && obj.checked) || (typeof obj.selected != 'undefined' && obj.selected)){
						checked++;
					}
					if((typeof obj.type != 'undefined' && obj.type=='checkbox')){
						var specialField = true;
					}
				}
				var checkedGood = 0;
				var count = 0;
				if(typeof hikashop != 'undefined' && typeof hikashop[field_type] != 'undefined'){
					for(var k in hikashop[field_type][namekey]) {
						if(typeof hikashop[field_type][namekey][k] == 'object'){
							for(var l in hikashop[field_type][namekey][k]){
								if(typeof hikashop[field_type][namekey][k][l] == 'string'){
									count++;
									newEl = document.getElementById(namekey+'_'+k);
									if(newEl && ((typeof newEl.checked != 'undefined' && newEl.checked) || (typeof newEl.selected != 'undefined' && newEl.selected))){
										checkedGood++;
									}
								}
							}
						}
					}
				}
				if(typeof arr[0] != 'undefined' && typeof arr[0].length != 'undefined' && count>1){
					var specialField = true;
				}
				if(typeof hikashop != 'undefined' && typeof hikashop[field_type] != 'undefined'){
					for(var j in hikashop[field_type][namekey]) {
						if(typeof hikashop[field_type][namekey][j] == 'object'){
							for(var i in hikashop[field_type][namekey][j]){
								if(typeof hikashop[field_type][namekey][j][i] == 'string'){
									var elementName = 'hikashop_'+field_type+'_'+hikashop[field_type][namekey][j][i];
									if(id){
										elementName = elementName + '_' + id;
									}
									el = document.getElementById(elementName);
									if(specialField){
										if(el){
											if(checkedGood==count && checkedGood==checked && new_value!=''){
												el.style.display='';
												hikashopToggleFields(el.value,hikashop[field_type][namekey][j][i],field_type,id);
											}else{
												el.style.display='none';
												hikashopToggleFields('',hikashop[field_type][namekey][j][i],field_type,id);
											}
										}
									}else{
										if(el){
											if(j==new_value){
												el.style.display='';
												hikashopToggleFields(el.value,hikashop[field_type][namekey][j][i],field_type,id);
											}else{
												el.style.display='none';
												hikashopToggleFields('',hikashop[field_type][namekey][j][i],field_type,id);
											}
										}
									}
								}
							}
						}
					}
				}
			}";
			$done = true;
		}
		$js .= $this->getLoadJSForToggle($parents,$data,$id);
		$doc->addScriptDeclaration( "<!--\n".$js."\n//-->" );
	}
	function getLoadJSForToggle(&$parents,&$data,$id=1){
		$js="
		window.addEvent('domready', function(){";
		$js.=$this->initJSToggle($parents,$data,$id);
		$js.="});";
		return $js;
	}
	function initJSToggle(&$parents,&$data,$id=1){
		$first = reset($parents);
		$type = $first->type;
		$js = '';
		foreach($parents as $namekey => $parent){
			$js.="
			hikashopToggleFields('".@$data->$namekey."','".$namekey ."','".$type."',".$id.");";
		}
		return $js;
	}
	function getParents(&$fields){
		$parents = array();
		if(empty($fields)){
			return false;
		}
		foreach($fields as $k => $field){
			if(!empty($field->field_options['limit_to_parent'])){
				$parent = $field->field_options['limit_to_parent'];
				if(!isset($parents[$parent])){
					$obj=null;
					$obj->type = $field->field_table;
					$obj->childs = array();
					$parents[$parent]=$obj;
				}
				$parent_value = @$field->field_options['parent_value'];
				if(is_array($parent_value)){
					foreach($parent_value as $value){
						if(!isset($parents[$parent]->childs[$value])){
							$parents[$parent]->childs[$value]=array();
						}
						$parents[$parent]->childs[$value][$field->field_namekey]=$field;
					}
				}else{
					if(!isset($parents[$parent]->childs[$parent_value])){
						$parents[$parent]->childs[$parent_value]=array();
					}
					$parents[$parent]->childs[$parent_value][$field->field_namekey]=$field;
				}
			}
		}
		return $parents;
	}
	function allowed($column,$type='user'){
		$restricted=array('user'=>array('user_partner_price'=>1,'user_partner_paid'=>1,'user_created_ip'=>1,'user_partner_id'=>1,'user_partner_lead_fee'=>1,'user_partner_click_fee'=>1,'user_partner_percent_fee'=>1,'user_partner_flat_fee'=>1),
						  'order'=>array('order_id'=>1,'order_billing_address_id'=>1,'order_shipping_address_id'=>1,'order_user_id'=>1,'order_status'=>1,'order_discount_code'=>1,'order_created'=>1,'order_ip'=>1,'order_currency_id'=>1,'order_status'=>1,'order_shipping_price'=>1,'order_discount_price'=>1,'order_shipping_id'=>1,'order_shipping_method'=>1,'order_payment_id'=>1,'order_payment_method'=>1,'order_full_price'=>1,'order_modified'=>1,'order_partner_id'=>1,'order_partner_price'=>1,'order_partner_paid'=>1,'order_type'=>1,'order_partner_currency_id'=>1));
		if(isset($restricted[$type][$column])){
			$app =& JFactory::getApplication();
			if(!$app->isAdmin()){
				return false;
			}
		}
		return true;
	}
	function explodeValues($values){
		$allValues = explode("\n",$values);
		$returnedValues = array();
		foreach($allValues as $id => $oneVal){
			$line = explode('::',trim($oneVal));
			$var = $line[0];
			$val = $line[1];
			if(count($line)==2){
				$disable = '0';
			}else{
				$disable = $line[2];
			}
			if(strlen($val)>0){
				$obj = null;
				$obj->value = $val;
				$obj->disabled = $disable;
				$returnedValues[$var] = $obj;
			}
		}
		return $returnedValues;
	}
	function getFieldName($field){
		return '<label for="'.$this->prefix.$field->field_namekey.$this->suffix.'">'.$this->trans($field->field_realname).'</label>';
	}
	function trans($name){
		$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($name));
		$trans = JText::_($val);
		if($val==$trans){
			$trans = $name;
		}
		return $trans;

	}
	function get($field_id){
		$query = 'SELECT a.* FROM '.hikashop_table('field').' as a WHERE a.`field_id` = '.intval($field_id).' LIMIT 1';
		$this->database->setQuery($query);
		$field = $this->database->loadObject();
		if(!empty($field->field_options)){
			$field->field_options = unserialize($field->field_options);
		}
		if(!empty($field->field_value)){
			$field->field_value = $this->explodeValues($field->field_value);
		}
		return $field;
	}
function saveForm(){
    $field = null;
    $field->field_id = hikashop_getCID('field_id');
    $formData = JRequest::getVar( 'data', array(), '', 'array' );
    foreach($formData['field'] as $column => $value){
      hikashop_secureField($column);
      if(is_array($value)) $value = implode(',',$value);
      $field->$column = strip_tags($value);
    }
	$fieldOptions = JRequest::getVar( 'field_options', array(), '', 'array' );
    foreach($fieldOptions as $column => $value){
    	if(is_array($value)){
			foreach($value as $id => $val){
				hikashop_secureField($val);
				$fieldOptions[$column][$id] = strip_tags($val);
			}
    	}else{
    		$fieldOptions[$column] = strip_tags($value);
    	}
    }
    if($field->field_type == "customtext"){
		 $fieldOptions['customtext'] = JRequest::getVar('fieldcustomtext','','','string',JREQUEST_ALLOWRAW);
		 if(empty($field->field_id)) $field->field_namekey = 'customtext_'.date('z_G_i_s');
	}
    $field->field_options = serialize($fieldOptions);
    $fieldValues = JRequest::getVar('field_values', array(), '', 'array' );
    if(!empty($fieldValues)){
    	$field->field_value = array();
    	foreach($fieldValues['title'] as $i => $title){
    		if(strlen($title)<1 AND strlen($fieldValues['value'][$i])<1) continue;
    		$value = strlen($fieldValues['value'][$i])<1 ? $title : $fieldValues['value'][$i];
    		$disabled = strlen($fieldValues['disabled'][$i])<1 ? '0' : $fieldValues['disabled'][$i];
    		$field->field_value[] = strip_tags($title).'::'.strip_tags($value).'::'.strip_tags($disabled);
    	}
    	$field->field_value = implode("\n",$field->field_value);
    }
	if(empty($field->field_id) && $field->field_type != 'customtext'){
		if(empty($field->field_namekey)) $field->field_namekey = $field->field_realname;
		$field->field_namekey = preg_replace('#[^a-z0-9_]#i', '',strtolower($field->field_namekey));
		if(empty($field->field_namekey)){
			$this->errors[] = 'Please specify a namekey';
			return false;
		}
		$tables = array($field->field_table);
		if($field->field_table=='item'){
			$tables = array('cart_product','order_product');
		}
		foreach($tables as $table_name){
			$columnsTable = $this->database->getTableFields(hikashop_table($table_name));
			$columns = reset($columnsTable);
			if(isset($columns[$field->field_namekey])){
				$this->errors[] = 'The field "'.$field->field_namekey.'" already exists in the table "'.$table_name.'"';
				return false;
			}
		}
		foreach($tables as $table_name){
			$query = 'ALTER TABLE '.hikashop_table($table_name).' ADD `'.$field->field_namekey.'` TEXT NULL';
			$this->database->setQuery($query);
			$this->database->query();
		}
	}
	$categories = JRequest::getVar( 'category', array(), '', 'array' );
	JArrayHelper::toInteger($categories);
	$cat=',';
	foreach($categories as $category){
		$cat.=$category.',';
	}
	if($cat==','){
		$cat='all';
	}
	$field->field_categories = $cat;
    $field_id = $this->save($field);
    if(!$field_id) return false;
    if(empty($field->field_id)){
		$orderClass = hikashop_get('helper.order');
		$orderClass->pkey = 'field_id';
		$orderClass->table = 'field';
		$orderClass->groupMap = 'field_table';
		$orderClass->groupVal = $field->field_table;
		$orderClass->orderingMap = 'field_ordering';
		$orderClass->reOrder();
    }
    JRequest::setVar( 'field_id', $field_id);
    return true;
  }
	function delete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}
		foreach($elements as $key => $val){
			$elements[$key] = $this->database->getEscaped($val);
		}
		if(empty($elements)) return false;
		$this->database->setQuery('SELECT `field_namekey`,`field_id`,`field_table`,`field_type`  FROM '.hikashop_table('field').'  WHERE `field_core` = 0 AND `field_id` IN ('.implode(',',$elements).')');
		$fieldsToDelete = $this->database->loadObjectList('field_id');
		if(empty($fieldsToDelete)){
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('CORE_FIELD_DELETE_ERROR'));
			return false;
		}
		$namekeys = array();
		foreach($fieldsToDelete as $oneField){
			if($oneField->field_type!='customtext'){
				if($oneField->field_table=='item'){
					$namekeys['cart_product'][] = $oneField->field_namekey;
					$namekeys['order_product'][] = $oneField->field_namekey;
				}else{
					$namekeys[$oneField->field_table][] = $oneField->field_namekey;
				}
			}
		}
		foreach($namekeys as $table => $fields){
			$this->database->setQuery('ALTER TABLE '.hikashop_table($table).' DROP `'.implode('`, DROP `',$fields).'`');
			$this->database->query();
		}
		$this->database->setQuery('DELETE FROM '.hikashop_table('field').' WHERE `field_id` IN ('.implode(',',array_keys($fieldsToDelete)).')');
		$result = $this->database->query();
		if(!$result) return false;
		$affectedRows = $this->database->getAffectedRows();
		foreach($namekeys as $table => $fields){
			$orderClass = hikashop_get('helper.order');
			$orderClass->pkey = 'field_id';
			$orderClass->table = 'field';
			$orderClass->groupMap = 'field_table';
			$orderClass->groupVal = $table;
			$orderClass->orderingMap = 'field_ordering';
			$orderClass->reOrder();
		}
		return $affectedRows;
	}
	function display(&$field,$value,$map,$inside = false,$options='',$test=false){
		$classType = 'hikashop'.ucfirst($field->field_type);
		$class = new $classType($this);
		$html = $class->display($field,htmlspecialchars($value, ENT_COMPAT,'UTF-8'),$map,$inside,$options,$test);
		if(!empty($field->field_required)){
			$html .=' <span class="hikashop_field_required">*</span>';
		}
		return $html;
	}
	function show(&$field,$value){
		$classType = 'hikashop'.ucfirst($field->field_type);
		$class = new $classType($this);
		$html = $class->show($field,$value);
		return $html;
	}
}
class hikashopItem{
	function hikashopItem(&$obj){
		$this->prefix = $obj->prefix;
		$this->suffix = $obj->suffix;
		$this->excludeValue =& $obj->excludeValue;
		$this->report = @$obj->report;
		$this->parent =& $obj;
	}
	function getFieldName($field){
		return '<label for="'.$this->prefix.$field->field_namekey.$this->suffix.'">'.$this->trans($field->field_realname).'</label>';
	}
	function trans($name){
		$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($name));
		$trans = JText::_($val);
		if($val==$trans){
			$trans = $name;
		}
		return $trans;
	}
	function show(&$field,$value){
		return $this->trans($value);
	}
	function JSCheck(&$oneField,&$requiredFields,&$validMessages,&$values){
		if(!empty($oneField->field_required)){
			$requiredFields[] = $oneField->field_namekey;
			if(!empty($oneField->field_options['errormessage'])){
				$validMessages[] = addslashes($this->trans($oneField->field_options['errormessage']));
			}else{
				$validMessages[] = addslashes(JText::sprintf('FIELD_VALID',$this->trans($oneField->field_realname)));
			}
		}
	}
	function check(&$field,$value,$oldvalue){
		if(!$field->field_required || is_array($value) || strlen($value) || strlen($oldvalue)){
			return true;
		}
		if($this->report){
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('PLEASE_FILL_THE_FIELD',$this->trans($field->field_realname)));
		}
		return false;
	}
}
class hikashopCustomtext extends hikashopItem{
	function display($field,$value,$map,$inside,$options='',$test=false,$type='text',$class='inputbox'){
		return @$field->field_options['customtext'];
	}
}
class hikashopText extends hikashopItem{
	function display($field,$value,$map,$inside,$options='',$test=false,$type='text',$class='inputbox'){
		$size = empty($field->field_options['size']) ? '' : 'size="'.intval($field->field_options['size']).'"';
		$size .= empty($field->field_options['maxlength']) ? '' : ' maxlength="'.intval($field->field_options['maxlength']).'"';
		$js = '';
		if($inside AND strlen($value) < 1){
			$value = addslashes($this->trans($field->field_realname));
			$this->excludeValue[$field->field_namekey] = $value;
			$js = 'onfocus="if(this.value == \''.$value.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$value.'\';"';
		}
		return '<input class="'.$class.'" id="'.$this->prefix.@$field->field_namekey.$this->suffix.'" '.$size.' '.$js.' '.$options.' type="'.$type.'" name="'.$map.'" value="'.$value.'" />';
	}
}
class hikashopFile extends hikashopText{
	function display($field,$value,$map,$inside,$options='',$test=false){
		$html='';
		if(!empty($value)){
			$html.=$this->show($field,$value,'hikashop_custom_file_upload_link');
		}
		$map = $field->field_table.'_'.$field->field_namekey;
		$html.= parent::display($field,$value,$map,$inside,$options,$test,'file','inputbox hikashop_custom_file_upload_field');
		$html.= '<span class="hikashop_custom_file_upload_message">'.JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')).'</span>';
		return $html;
	}
	function JSCheck(&$oneField,&$requiredFields,&$validMessages,&$values){
		$namekey = $oneField->field_namekey;
		if(empty($values->$namekey)){
			return parent::JSCheck($oneField,$requiredFields,$validMessages,$values);
		}
		return true;
	}
	function show(&$field,$value,$class='hikashop_custom_file_link'){
		return '<a target="_blank" class="'.$class.'" href="'.hikashop_completeLink('order&task=download&field_table='.$field->field_table.'&field_namekey='.urlencode(base64_encode($field->field_namekey)).'&name='.urlencode(base64_encode($value))).'">'.$value.'</a>';
	}
	function check(&$field,&$value,$oldvalue){
		$class = hikashop_get('class.file');
		$map = $field->field_table.'_'.$field->field_namekey;
		if(empty($field->field_options['file_type'])){
			$field->field_options['file_type']='file';
		}
		$file = $class->saveFile($map,$field->field_options['file_type']);
		if(!empty($file)){
			$value = $file;
		}else{
			if(!empty($oldvalue)){
				$value = $oldvalue;
			}else{
				$value = '';
			}
		}
		return parent::check($field,$value,$oldvalue);
	}
}
class hikashopCoupon extends hikashopText{
	function check(&$field,$value){
		$status = parent::check($field,$value);
		if($status){
			if($field->field_required && empty($value)){
				return true;
			}
			$zone_id = hikashop_getZone('shipping');
			$discount=hikashop_get('class.discount');
			$zoneClass = hikashop_get('class.zone');
			$zones = $zoneClass->getZoneParents($zone_id);
			$total = null;
			$price = null;
			$price->price_value_with_tax = 0;
			$price->price_value = 0;
			$price->price_currency_id = hikashop_getCurrency();
			$total->prices = array($price);
			if(empty($field->coupon)){
				$field->coupon=array();
			}
			$products = array();
			$field->coupon[$value] = $discount->loadAndCheck($value,$total,$zones,$products,true);
			if(empty($field->coupon[$value])){
				$app =& JFactory::getApplication();
				$app->enqueueMessage(JRequest::getVar('coupon_error_message'),'notice');
				$status = false;
			}
			static $validCoupons = array();
			if(!isset($validCoupons[$value])){
				$validCoupons[$value] = 1;
			}else{
				$validCoupons[$value]++;
			}
			if($field->coupon[$value]->discount_quota!=-1){
				$left = ($field->coupon[$value]->discount_quota - $field->coupon[$value]->discount_used_times);
				if($left<$validCoupons[$value]){
					if($left>0){
						$app =& JFactory::getApplication();
						$app->enqueueMessage('You cannot use the coupon '.$value.' more than '.$left.' times !');
					}
					$status = false;
				}
			}
		}
		return $status;
	}
}
class hikashopTextarea extends hikashopItem{
	function display($field,$value,$map,$inside,$options='',$test=false){
		$js = '';
		if($inside AND strlen($value) < 1){
			$value = addslashes($this->trans($field->field_realname));
			$this->excludeValue[$field->field_namekey] = $value;
			$js = 'onfocus="if(this.value == \''.$value.'\') this.value = \'\';" onblur="if(this.value==\'\') this.value=\''.$value.'\';"';
		}
		$cols = empty($field->field_options['cols']) ? '' : 'cols="'.intval($field->field_options['cols']).'"';
		$rows = empty($field->field_options['rows']) ? '' : 'rows="'.intval($field->field_options['rows']).'"';
		return '<textarea class="inputbox" id="'.$this->prefix.@$field->field_namekey.$this->suffix.'" name="'.$map.'" '.$cols.' '.$rows.' '.$js.' '.$options.'>'.$value.'</textarea>';
	}
}
class hikashopDropdown extends hikashopItem{
	function show(&$field,$value){
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(isset($field->field_value[$value])) $value = $field->field_value[$value]->value;
		return parent::show($field,$value);
	}
	function display($field,$value,$map,$type,$inside,$options='',$test=false){
		$string = '';
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(empty($field->field_value) || !count($field->field_value)){
			return '<input type="hidden" name="'.$map.'" value="" />';
		}
		if($type == "multiple"){
			$string.= '<input type="hidden" name="'.$map.'" value=" " />';
			$map.='[]';
			$arg = 'multiple="multiple"';
			if(!empty($field->field_options['size'])) $arg .= ' size="'.intval($field->field_options['size']).'"';
		}else{
			$arg = 'size="1"';
			if(is_string($value)&& empty($value) && !empty($field->field_value)){
				$found = false;
				$first = false;
				foreach($field->field_value as $oneValue => $title){
					if($first===false){
						$first=$oneValue;
					}
					if($oneValue==$value){
						$found = true;
						break;
					}
				}
				if(!$found){
					$value = $first;
				}
			}
		}
		$string .= '<select id="'.$this->prefix.$field->field_namekey.$this->suffix.'" name="'.$map.'" '.$arg.$options.'>';
		if(empty($field->field_value)) return $string;
		$app =& JFactory::getApplication();
		$admin = $app->isAdmin();
		foreach($field->field_value as $oneValue => $title){
			$selected = ((int)$title->disabled && !$admin) ? 'disabled="disabled" ' : '';
			$selected .= ((is_numeric($value) AND is_numeric($oneValue) AND $oneValue == $value) OR (is_string($value) AND $oneValue === $value) OR is_array($value) AND in_array($oneValue,$value)) ? 'selected="selected" ' : '';
			$id = $this->prefix.$field->field_namekey.$this->suffix.'_'.$oneValue;
			$string .= '<option value="'.$oneValue.'" id="'.$id.'" '.$selected.'>'.$this->trans($title->value).'</option>';
		}
		$string .= '</select>';
		return $string;
	}
}
class hikashopSingledropdown extends hikashopDropdown{
	function display($field,$value,$map,$inside,$options='',$test=false){
		return parent::display($field,$value,$map,'single',$inside,$options,$test);
	}
}
class hikashopZone extends hikashopSingledropdown{
	function display($field,$value,$map,$inside,$options='',$test=false){
		static $namekey = null;
		if($field->field_options['zone_type']=='country'){
			static $done = false;
			if(empty($done)){
				$done = true;
				if(!empty($options)){
					if(stripos($options,'onchange="')!==false){
						$options=preg_replace('#onchange="#i','onchange="changeState(this.value);',$options);
					}else{
						$options .= ' onchange="changeState(this.value);"';
					}
				}else{
					$options = ' onchange="changeState(this.value);"';
				}
				if(!empty($namekey)){
					$namekey='&field_namekey='.$namekey;
				}
				$js = '
				function optionValueIndexOf(options,value) {
					for (var i=0;i<options.length;i++) {
						if (options[i].value == value) {
							return i;
						}
					}
					return -1;
				}
				function changeState(newvalue){
					var defaultValInput = document.getElementById(\'state_default_value\');
					var defaultVal = \'\';
					var namekey = false;
					if(defaultValInput){
						defaultVal = defaultValInput.value;
						namekey = document.getElementById(\'state_namekey\').value;
					}
					if(namekey){
						try{
							new Ajax(\''.$field->field_url.'field_type='.$field->field_table.$namekey.'&namekey=\'+newvalue, { method: \'get\', onComplete: function(result) { old = window.document.getElementById(\'state_dropdown\'); if(old){ old.innerHTML = result;if(namekey) {var stateSelect = document.getElementById(namekey); if(stateSelect && optionValueIndexOf(stateSelect.options, defaultVal) >= 0) stateSelect.value=defaultVal;}}}}).request();
						}catch(err){
							new Request({url:\''.$field->field_url.'field_type='.$field->field_table.$namekey.'&namekey=\'+newvalue, method: \'get\', onComplete: function(result) { old = window.document.getElementById(\'state_dropdown\'); if(old){ old.innerHTML = result;if(namekey) {var stateSelect = document.getElementById(namekey); if(stateSelect && optionValueIndexOf(stateSelect.options, defaultVal) >= 0) stateSelect.value=defaultVal;}}}}).send();
						}
					}
				}
				window.addEvent(\'domready\', function(){ changeState(document.getElementById(\''.$this->prefix.$field->field_namekey.$this->suffix.'\').value); });
				';
				$doc =& JFactory::getDocument();
				$doc->addScriptDeclaration( "<!--\n".$js."\n//-->" );
			}
		}elseif($field->field_options['zone_type']=='state' && !$test){
			$namekey=$field->field_namekey;
			return '<span id="state_dropdown"></span><input type="hidden" id="state_namekey" name="state_namekey" value="'.$this->prefix.$field->field_namekey.$this->suffix.'"/><input type="hidden" id="state_default_value" name="state_default_value" value="'.$value.'"/>';
		}
		return parent::display($field,$value,$map,$inside,$options,$test);
	}
	function check(&$field,$value,$oldvalue){
		return true;
	}
	function JSCheck(&$oneField,&$requiredFields,&$validMessages,&$values){
	}
}
class hikashopMultipledropdown extends hikashopDropdown{
	function display($field,$value,$map,$inside,$options='',$test=false){
		$value = explode(',',$value);
		return parent::display($field,$value,$map,'multiple',$inside,$options,$test=false);
	}
	function show(&$field,$value){
		if(!is_array($value)){
			$value = explode(',',$value);
		}
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		$results = array();
		foreach($value as $val){
			if(isset($field->field_value[$val])) $val = $field->field_value[$val]->value;
			$results[]= parent::show($field,$val);
		}
		return implode(',',$results);
	}
}
class hikashopRadioCheck extends hikashopItem{
	function show(&$field,$value){
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		if(isset($field->field_value[$value])) $value = $field->field_value[$value]->value;
		return parent::show($field,$value);
	}
	function display($field,$value,$map,$type,$inside,$options='',$test=false){
		$string = '';
		if($inside) $string = $this->trans($field->field_realname).' ';
		if($type == 'checkbox'){
			$string.= '<input type="hidden" name="'.$map.'" value=" "/>';
			$map.='[]';
		}
		if(empty($field->field_value)) return $string;
		$app =& JFactory::getApplication();
		$admin = $app->isAdmin();
		foreach($field->field_value as $oneValue => $title){
			$checked = ((int)$title->disabled && !$admin) ? 'disabled="disabled" ' : '';
			$checked .= ((is_string($value) AND $oneValue == $value) OR is_array($value) AND in_array($oneValue,$value)) ? 'checked="checked" ' : '';
			$id = $this->prefix.$field->field_namekey.$this->suffix.'_'.$oneValue;
			$string .= '<input type="'.$type.'" name="'.$map.'" value="'.$oneValue.'" id="'.$id.'" '.$checked.' '.$options.' /><label for="'.$id.'">'.$this->trans($title->value).'</label>';
		}
		return $string;
	}
}
class hikashopRadio extends hikashopRadioCheck{
	function display($field,$value,$map,$inside,$options='',$test=false){
		return parent::display($field,$value,$map,'radio',$inside,$options,$test);
	}
}
class hikashopDate extends hikashopItem{
	function display($field,$value,$map,$inside,$options='',$test=false){
		if(empty($field->field_options['format'])) $field->field_options['format'] = "%Y-%m-%d";
		$size = $options . empty($field->field_options['size']) ? '' : ' size="'.$field->field_options['size'].'"';
		if(!empty($field->field_options['allow'])){
			JHTML::_('behavior.mootools');
			$processing='';
			switch($field->field_options['allow']){
				case 'future':
					$check = 'today>selectedDate';
					$message = JText::_('SELECT_DATE_IN_FUTURE');
					break;
				case 'past':
					$check = 'today<selectedDate';
					$message = JText::_('SELECT_DATE_IN_PAST');
					break;
			}
			$js = 'function '.$this->prefix.$field->field_namekey.$this->suffix.'_checkDate()
			{
				var selObj = document.getElementById(\''.$this->prefix.$field->field_namekey.$this->suffix.'\');
					var selectedDate = new Date(selObj.value);
					var today=new Date();
					'.$processing.'
					if('.$check.'){
						selObj.value=\'\';
						alert(\''.$message.'\');
					}else{
						this.hide();
					}
			}';
			$document = & JFactory::getDocument();
			$document->addScriptDeclaration($js);
			$field->field_options['format'].='", onClose       :    '.$this->prefix.$field->field_namekey.$this->suffix.'_checkDate, //';
		}
		return JHTML::_('calendar', $value, $map,$this->prefix.$field->field_namekey.$this->suffix,$field->field_options['format'],$size);
	}
}
class hikashopCheckbox extends hikashopRadioCheck{
	function display($field,$value,$map,$inside,$options='',$test=false){
		$value = explode(',',$value);
		return parent::display($field,$value,$map,'checkbox',$inside,$options,$test);
	}
	function show(&$field,$value){
		if(!is_array($value)){
			$value = explode(',',$value);
		}
		if(!empty($field->field_value) && !is_array($field->field_value)){
			$field->field_value = $this->parent->explodeValues($field->field_value);
		}
		$results = array();
		foreach($value as $val){
			if(isset($field->field_value[$val])) $val = $field->field_value[$val]->value;
			$results[]= parent::show($field,$val);
		}
		return implode(',',$results);
	}
}
