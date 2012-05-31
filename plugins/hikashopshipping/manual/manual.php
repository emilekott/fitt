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
class plgHikashopshippingManual extends JPlugin{
	var $multiple_entries = true;
    function onShippingDisplay(&$order,&$dbrates,&$usable_rates,&$messages){
    	$config =& hikashop_config();
    	if(!$config->get('force_shipping') && bccomp(@$order->weight,0,5)<=0) return true;
    	if(empty($dbrates)){
    		$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
    	}else{
    		$rates = array();
    		foreach($dbrates as $k => $rate){
    			if($rate->shipping_type=='manual' && !empty($rate->shipping_published)){
    				$rates[]=$rate;
    			}
    		}
    		if(empty($rates)){
    			$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
    		}else{
    			if(!isset($rate->shipping_params->shipping_virtual_included) || $rate->shipping_params->shipping_virtual_included){
    				$price = $order->total->prices[0]->price_value_with_tax;
    			}else{
    				$price = 0.0;
    				if(!empty($order->products)){
    					$copy = null;
    					$copy->products = array();
	    				foreach($order->products as $k => $row){
	    					if($row->product_weight > 0){
	    						$copy->products[] = $row;
	    					}
						}
						$currencyClass = hikashop_get('class.currency');
						$currencyClass->calculateTotal($copy->products,$copy->total,hikashop_getCurrency());
						$price = $copy->total->prices[0]->price_value_with_tax;
    				}
    			}
				$notUsable = array();
				foreach($rates as $k => $rate){
					if(@$rate->shipping_params->shipping_min_price>$price){
						$notUsable[]=$k;
						continue;
					}
					if(bccomp($price,0,5)){
						if(!empty($rate->shipping_params->shipping_max_price) && bccomp($rate->shipping_params->shipping_max_price,0,5) && @$rate->shipping_params->shipping_max_price<$price){
							$notUsable[]=$k;
							continue;
						}
						if(isset($rate->shipping_params->shipping_percentage) && bccomp($rate->shipping_params->shipping_percentage,0,3)){
							$currencyClass = hikashop_get('class.currency');
							$rates[$k]->shipping_price = round($rate->shipping_price + $price*$rate->shipping_params->shipping_percentage/100,$currencyClass->getRounding($rate->shipping_currency_id));
						}
					}
				}
				foreach($notUsable as $item){
					unset($rates[$item]);
				}
				if(empty($rates)){
					$messages['order_total_too_low'] = JText::_('ORDER_TOTAL_TOO_LOW_FOR_SHIPPING_METHODS');
				}else{
	    			$notUsable = array();
	    			$zoneClass=hikashop_get('class.zone');
	    			$zones = $zoneClass->getOrderZones($order);
	    			foreach($rates as $k => $rate){
						if(!empty($rate->shipping_zone_namekey)){
							if(!in_array($rate->shipping_zone_namekey,$zones)){
								$notUsable[]=$k;
								continue;
							}
						}
						if(!empty($rate->shipping_params->shipping_zip_prefix) || !empty($rate->shipping_params->shipping_min_zip) || !empty($rate->shipping_params->shipping_max_zip) || !empty($rate->shipping_params->shipping_zip_suffix)){
							$checkDone = false;
							if(!empty($order->shipping_address) && !empty($order->shipping_address->address_post_code)){
								if(preg_match('#([a-z]*)([0-9]+)(.*)#i',preg_replace('#[^a-z0-9]#i','',$order->shipping_address->address_post_code),$match)){
									$checkDone = true;
									$prefix = $match[1];
									$main = $match[2];
									$suffix = $match[3];
									if(!empty($rate->shipping_params->shipping_zip_prefix) && $rate->shipping_params->shipping_zip_prefix!=$prefix){
										$notUsable[]=$k;
										continue;
									}
									if(!empty($rate->shipping_params->shipping_min_zip) && $rate->shipping_params->shipping_min_zip>$main){
										$notUsable[]=$k;
										continue;
									}
				    				if(!empty($rate->shipping_params->shipping_max_zip) && $rate->shipping_params->shipping_max_zip<$main){
										$notUsable[]=$k;
									}
									if(!empty($rate->shipping_params->shipping_zip_suffix) && $rate->shipping_params->shipping_zip_suffix!=$suffix){
										$notUsable[]=$k;
										continue;
									}
								}
							}
							if(!$checkDone){
								$notUsable[]=$k;
								continue;
							}
						}
					}
		    		foreach($notUsable as $item){
						unset($rates[$item]);
					}
					if(empty($rates)){
						$messages['no_shipping_to_your_zone'] = JText::_('NO_SHIPPING_TO_YOUR_ZONE');
					}else{
						$volumeClass=hikashop_get('helper.volume');
						$notUsable = array();
						foreach($rates as $k => $rate){
							if(bccomp($rate->shipping_params->shipping_max_volume,0,3)){
								$rates[$k]->shipping_params->shipping_max_volume_orig = $rates[$k]->shipping_params->shipping_max_volume;
								$rates[$k]->shipping_params->shipping_max_volume=$volumeClass->convert($rate->shipping_params->shipping_max_volume,@$rate->shipping_params->shipping_size_unit);
								if($rates[$k]->shipping_params->shipping_max_volume<$order->volume){
									$notUsable[]=$k;
								}
							}
							if(bccomp((float)@$rate->shipping_params->shipping_min_volume,0,3)){
								$rates[$k]->shipping_params->shipping_min_volume_orig = $rates[$k]->shipping_params->shipping_min_volume;
								$rates[$k]->shipping_params->shipping_min_volume=$volumeClass->convert($rate->shipping_params->shipping_min_volume,@$rate->shipping_params->shipping_size_unit);
								if($rates[$k]->shipping_params->shipping_min_volume>$order->volume){
									$notUsable[]=$k;
								}
							}
						}
						foreach($notUsable as $item){
							unset($rates[$item]);
						}
						if(empty($rates)){
							$messages['items_volume_over_limit'] = JText::_('ITEMS_VOLUME_TOO_BIG_FOR_SHIPPING_METHODS');
						}elseif(isset($order->weight)){
							$notUsable = array();
							$weightClass=hikashop_get('helper.weight');
				    		foreach($rates as $k => $rate){
				    			if(!empty($rate->shipping_params->shipping_max_weight) && bccomp($rate->shipping_params->shipping_max_weight,0,3)){
									$rates[$k]->shipping_params->shipping_max_weight_orig = $rates[$k]->shipping_params->shipping_max_weight;
									$rates[$k]->shipping_params->shipping_max_weight=$weightClass->convert($rate->shipping_params->shipping_max_weight,@$rate->shipping_params->shipping_weight_unit);
									if($rates[$k]->shipping_params->shipping_max_weight<$order->weight){
										$notUsable[]=$k;
									}
				    			}
				    			if(!empty($rate->shipping_params->shipping_min_weight) && bccomp((float)@$rate->shipping_params->shipping_min_weight,0,3)){
									$rates[$k]->shipping_params->shipping_min_weight_orig = $rates[$k]->shipping_params->shipping_min_weight;
									$rates[$k]->shipping_params->shipping_min_weight=$weightClass->convert($rate->shipping_params->shipping_min_weight,@$rate->shipping_params->shipping_weight_unit);
									if($rates[$k]->shipping_params->shipping_min_weight>$order->weight){
										$notUsable[]=$k;
									}
				    			}
							}
				    		foreach($notUsable as $item){
								unset($rates[$item]);
							}
							if(empty($rates)){
								$messages['items_weight_over_limit'] = JText::_('ITEMS_WEIGHT_TOO_BIG_FOR_SHIPPING_METHODS');
							}else{
								foreach($rates as $rate){
									$usable_rates[]=$rate;
								}
							}
						}
					}
				}
    		}
    	}
    	return true;
    }
    function onShippingConfiguration(&$elements){
    	$this->manual = JRequest::getCmd('name','manual');
		$subtask = JRequest::getCmd('subtask','');
    	if($subtask=='shipping_edit'){
    		$this->view = 'edit';
			$this->currency = hikashop_get('type.currency');
			$this->weight = hikashop_get('type.weight');
			$this->volume = hikashop_get('type.volume');
			$this->categoryType = hikashop_get('type.categorysub');
			$this->categoryType->type = 'tax';
			$this->categoryType->field = 'category_id';
			$bar = & JToolBar::getInstance('toolbar');
			JToolBarHelper::save();
			JToolBarHelper::apply();
			$bar->appendButton( 'Link', 'cancel', JText::_('HIKA_CANCEL'), hikashop_completeLink('plugins&plugin_type=shipping&task=edit&name='.$this->manual) );
			JToolBarHelper::divider();
			$bar->appendButton( 'Pophelp','shipping-manual-form');
			hikashop_setTitle(JText::_('HIKASHOP_SHIPPING_METHOD'),'plugin','plugins&plugin_type=shipping&task=edit&name='.$this->manual.'&subtask=shipping_edit&shipping_id='.JRequest::getInt('shipping_id',0));
    	}else{
    		if($subtask=='copy'){
    			$task = JRequest::getVar('task');
    			if(!in_array($task,array('orderup','orderdown','saveorder'))){
	    			$shippings = JRequest::getVar( 'cid', array(), '', 'array' );
	    			JArrayHelper::toInteger($shippings);
					$result = true;
					if(!empty($shippings)){
						$db =& JFactory::getDBO();
						$db->setQuery('SELECT * FROM '.hikashop_table('shipping').' WHERE shipping_id IN ('.implode(',',$shippings).')');
						$elements = $db->loadObjectList();
						$helper = hikashop_get('class.shipping');
						foreach($elements as $element){
							unset($element->shipping_id);
							if(!$helper->save($element)){
								$result=false;
							}
						}
					}
					if($result){
						$app =& JFactory::getApplication();
						$app->enqueueMessage(JText::_( 'HIKASHOP_SUCC_SAVED' ), 'message');
						$app->redirect(hikashop_completeLink('plugins&plugin_type=shipping&task=edit&name='.$this->manual,false,true));
					}
    			}
    		}
	    	$this->dbrates =& $elements;
	    	$this->noForm=true;
	    	if(!empty($this->dbrates)){
	    		$db = JFactory::getDBO();
	    		$zones = array();
	    		foreach($this->dbrates as $rate){
	    			if(!empty($rate->shipping_zone_namekey)){
	    				$zones[$rate->shipping_zone_namekey]=$db->Quote($rate->shipping_zone_namekey);
	    			}
	    		}
	    		if(!empty($zones)){
	    			$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_namekey IN ('.implode(',',$zones).');';
	    			$db->setQuery($query);
	    			$zones = $db->loadObjectList();
	    			if(!empty($zones)){
		    			foreach($this->dbrates as $k => $rate){
		    				if(!empty($rate->shipping_zone_namekey)){
		    					foreach($zones as $zone){
		    						if($zone->zone_namekey==$rate->shipping_zone_namekey){
		    							foreach(get_object_vars($zone) as $key => $val){
		    								$this->dbrates[$k]->$key=$val;
		    							}
		    							break;
		    						}
		    					}
		    				}
		    			}
	    			}
	    		}
	    	}
	    	$bar = & JToolBar::getInstance('toolbar');
	    	$bar->appendButton( 'Standard', 'copy',JText::_('HIKA_COPY'), 'edit', true, false );
			$bar->appendButton( 'Link','new',JText::_('HIKA_NEW'),hikashop_completeLink('plugins&plugin_type=shipping&task=edit&name='.$this->manual.'&subtask=shipping_edit'));
			JToolBarHelper::cancel();
			JToolBarHelper::divider();
			$bar->appendButton( 'Pophelp','shipping-manual-listing');
			hikashop_setTitle(JText::_('HIKASHOP_SHIPPING_METHOD'),'plugin','plugins&plugin_type=shipping&task=edit&name='.$this->manual);
			$this->toggleClass = hikashop_get('helper.toggle');
			$this->currencyHelper = hikashop_get('class.currency');
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination( count($this->dbrates), 0, false );
			$this->order = null;
			$this->order->ordering = true;
			$this->order->orderUp = 'orderup';
			$this->order->orderDown = 'orderdown';
			$this->order->reverse = false;
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.shipping_plugin_type', $this->manual);
    	}
    }
    function onShippingConfigurationSave(&$elements){
    	return true;
    }
	function onShippingSave(&$cart,&$methods,&$shipping_id){
    	$usable_mehtods = array();
    	$errors = array();
    	$this->onShippingDisplay($cart,$methods,$usable_mehtods,$errors);
    	$shipping_id = (int) $shipping_id;
    	foreach($usable_mehtods as $k => $usable_method){
    		if($usable_method->shipping_id==$shipping_id){
    			return $usable_method;
    		}
    	}
    	return false;
    }
	function onAfterOrderConfirm(&$order,&$methods,$method_id){
    	return true;
    }
}