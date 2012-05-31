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
class hikashopCurrencyClass extends hikashopClass{
	var $tables = array('currency');
	var $pkeys = array('currency_id');
	var $namekeys = array('');
	var $toggle = array('currency_published'=>'currency_id','currency_displayed'=>'currency_id');
	function get($element){
		$data = parent::get($element);
		$this->checkLocale($data);
		return $data;
	}
	function getTaxedPrice($price,$zone_id,$tax_category_id,$round=2){
		if(empty($tax_category_id)) return round($price,$round);
		$tax = (float)$this->getTax($zone_id,$tax_category_id);
		$price=(float)$price;
		$taxedPrice=round($price+$price*$tax,$round);
		return $taxedPrice;
	}
	function getUntaxedPrice($price,$zone_id,$tax_category_id,$round=2){
		if(empty($tax_category_id)) return round($price,$round);
		$tax = (float)$this->getTax($zone_id,$tax_category_id);
		$price=(float)$price;
		$untaxedPrice = round($price/(1.00000+$tax),$round);
		return $untaxedPrice;
	}
	function getTaxType(){
		static $taxType = '';
		if(empty($taxType)){
			$config =& hikashop_config();
			$type = $config->get('default_type','individual');
			$app =& JFactory::getApplication();
			$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.'.$config->get('tax_zone_type','shipping').'_address',0);
			if(!empty($shipping_address)){
				$addressClass = hikashop_get('class.address');
				$address = $addressClass->get($shipping_address);
				if(!empty($address->address_company)){
					$type = 'company_without_vat_number';
				}
				if(!empty($address->address_vat)){
					$vat = hikashop_get('helper.vat');
					if($vat->isValid($address)) $type = 'company_with_vat_number';
				}
			}
			$taxType=$type;
		}
		return $taxType;
	}
	function getTax($zone_id,$tax_category_id,$type=''){
		static $calculated = array();
		if(empty($zone_id)){
			$zone_id=$this->mainTaxZone();
		}
		if(empty($type)){
			$type=$this->getTaxType();
		}
		$key = $zone_id.'_'.$tax_category_id.'_'.$type;
		if(!isset($calculated[$key])){
			$filter = '';
			switch($type){
				default:
					$filter = 'taxation_type = '.$this->database->Quote($type);
				case '':
					$typeFilter = 'taxation_type = \'\'';
					if(!empty($filter)){
						$typeFilter = '( '.$typeFilter.' OR '.$filter.' )';
					}
					break;
			}
			$filters = array('a.category_id = '.(int)$tax_category_id,'b.taxation_published=1',$typeFilter);
			hikashop_addACLFilters($filters,'taxation_access','b');
			$query = 'SELECT b.*,c.* FROM '.hikashop_table('category'). ' AS a LEFT JOIN '.hikashop_table('taxation').' AS b ON a.category_namekey=b.category_namekey LEFT JOIN '.hikashop_table('tax').' AS c ON b.tax_namekey=c.tax_namekey WHERE '.implode(' AND ',$filters);
			$this->database->setQuery($query);
			$taxPlans = $this->database->loadObjectList('taxation_id');
			$query = 'SELECT * FROM '.hikashop_table('zone').' WHERE zone_id = '.(int)$zone_id;
			$this->database->setQuery($query);
			$zone = $this->database->loadObject();
			$quotedTaxNamekeys = array();
			$tax = 0;
			if(!empty($taxPlans) && !empty($zone)){
				$matches = array();
				$already = array($zone->zone_id=>$zone);
				foreach($taxPlans as $taxPlan){
					$quotedTaxNamekeys[]=$this->database->Quote($taxPlan->zone_namekey);
					if($taxPlan->zone_namekey==$zone->zone_namekey){
						$taxPlan->zone_type = $zone->zone_type;
						$matches[$taxPlan->taxation_id]=$taxPlan;
					}
				}
				if(count($matches)==0){
					$childs = array($this->database->Quote($zone->zone_namekey));
					$this->_getParents($childs,$matches,$already,$quotedTaxNamekeys,$taxPlans);
				}
				if(count($matches)!=0){
					$type = 'state';
					$types=array('country','tax');
					$found=false;
					while(!$found){
						foreach($matches as $match){
							if($match->zone_type==$type){
								$tax = floatval(@$match->tax_rate);
								$found = true;
								break;
							}
						}
						if(!$found){
							if(empty($types)){
								$found = true;
								break;
							}
							$type = array_shift($types);
						}
					}
				}else{
					foreach($taxPlans as $taxPlan){
						if(empty($taxPlan->zone_namekey)){
							$tax = floatval(@$taxPlan->tax_rate);
						}
					}
				}
			}
			$calculated[$key]=$tax;
		}
		return $calculated[$key];
	}
	function mainTaxZone(){
		static $main_tax_zone = 0;
		if(!$main_tax_zone){
			$config =& hikashop_config();
			$main_tax_zone = explode(',',$config->get('main_tax_zone',''));
			if(count($main_tax_zone)){
				$main_tax_zone = array_shift($main_tax_zone);
			}
		}
		return $main_tax_zone;
	}
	function displayPrices($prices,$value_field='price_value',$currency_id_field='price_currency_id'){
		$html = '';
		if(!empty($prices)){
			$start=true;
			foreach($prices as $price){
				if(@$price->price_min_quantity)continue;
				if($start) $start=false;
				else $html.= ' / ';
				$html.= $this->format($price->$value_field,$price->$currency_id_field);
			}
		}
		return $html;
	}
	function _getParents(&$childs,&$matches,&$already,&$quotedTaxNamekeys,&$taxPlans){
		$namekeys = array();
		foreach($already as $zone){
			$namekeys[]=$this->database->Quote($zone->zone_namekey);
		}
		$query = 'SELECT b.* FROM '.hikashop_table('zone_link').' AS a LEFT JOIN '.hikashop_table('zone').' AS b ON a.zone_parent_namekey=b.zone_namekey WHERE a.zone_child_namekey IN ('.implode(',',$childs).') AND a.zone_parent_namekey NOT IN ('.implode(',',$namekeys).') AND (b.zone_type IN(\'state\',\'country\') OR ( b.zone_type=\'tax\' AND b.zone_namekey IN ('.implode(',',$quotedTaxNamekeys).') ))';
		$this->database->setQuery($query);
		$parents = $this->database->loadObjectList('zone_id');
		$childs = array();
		$already = array_merge($already,$parents);
		foreach($parents as $parent){
			$found = false;
			foreach($taxPlans as $taxPlan){
				if($parent->zone_namekey==$taxPlan->zone_namekey){
					if(!isset($matches[$taxPlan->taxation_id])){
						$taxPlan->zone_type = $parent->zone_type;
						$matches[$taxPlan->taxation_id]=$taxPlan;
					}
					$found = true;
				}
			}
			if(!$found){
				$childs[]=$this->database->Quote($parent->zone_namekey);
			}
		}
		if(!empty($childs)){
			$this->_getParents($childs,$matches,$already,$quotedTaxNamekeys,$taxPlans);
		}
	}
	function saveForm(){
		$currency = null;
		$currency->currency_id = hikashop_getCID('currency_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['currency'] as $column => $value){
			hikashop_secureField($column);
			if($column=='currency_symbol'){
				jimport('joomla.filter.filterinput');
				$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
				$currency->$column = $safeHtmlFilter->clean($value, 'string');
			}elseif($column=='currency_locale'){
				$tmp = array();
				foreach($value as $key => $val){
					$key = hikashop_secureField($key);
					if($key=='mon_grouping'){
						$tmp[$key] = preg_replace('#[^0-9,]#','',$val);
					}elseif(!in_array($key,array('mon_thousands_sep','mon_decimal_point','negative_sign','positive_sign'))){
						$tmp[$key] = intval($val);
					}else{
						$tmp[$key] = (!empty($val)? $val[0]:'');
					}
				}
				$currency->$column = serialize($tmp);
			}elseif(in_array($column,array('currency_rate','currency_flat_fee','currency_percent_fee'))){
				$currency->$column = hikashop_toFloat($value);
			}else{
				$currency->$column = strip_tags($value);
			}
		}
		$status = $this->save($currency);
		if(!$status){
			$currency=null;
			foreach($formData['currency'] as $column => $value){
				$currency->$column = $value;
			}
			JRequest::setVar( 'fail', $currency  );
		}
		return $status;
	}
	function getNamekey($element){
		return false;
	}
	function mainCurrency(){
		$config =& hikashop_config();
		return $config->get('main_currency',1);
	}
	function publishedCurrencies(){
		static $list = null;
		if(!isset($list)){
			$config =& hikashop_config();
			$query ='SELECT currency_id FROM '.hikashop_table('currency').' WHERE currency_published=1 OR currency_id = '.(int) $config->get('main_currency',1);
			$this->database->setQuery($query);
			$list = $this->database->loadResultArray();
		}
		return $list;
	}
	function getListingPrices(&$rows,$zone_id,$currency_id,$price_display_type=''){
		$ids = array();
		foreach($rows as $key => $row){
			$ids[]=$row->product_id;
		}
		$filters=array(
			'discount_type=\'discount\'',
			'discount_published=1',
			'( discount_quota>discount_used_times OR discount_quota=0 )',
			'discount_start < '.time(),
			'( discount_end > '.time().' OR discount_end = 0 )',
			'( discount_product_id IN ('.implode(',',$ids).') OR discount_product_id=0 )',
			'( discount_flat_amount!=0 OR discount_percent_amount!=0 )'
		);
		$app =& JFactory::getApplication();
		if(!$app->isAdmin()){
			hikashop_addACLFilters($filters,'discount_access');
		}
		$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE '.implode(' AND ',$filters);
		$this->database->setQuery($query);
		$discounts = $this->database->loadObjectList();
		$filters=array('a.price_product_id IN ('.implode(',',$ids).')','a.price_currency_id IN ('.implode(',',$this->publishedCurrencies()).')');
		if(!$app->isAdmin()){
			hikashop_addACLFilters($filters,'price_access','a');
		}
		$query = 'SELECT a.* FROM '.hikashop_table('price').' AS a WHERE '.implode(' AND ',$filters). ' ORDER BY a.price_value DESC';
		$this->database->setQuery($query);
		$prices = $this->database->loadObjectList();
		$variantSearch = array();
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$discount_before_tax = (int)$config->get('discount_before_tax',0);
		foreach($rows as $k => $element){
			$pricefound = false;
			if(!empty($prices)){
				$currentRowPrices = array();
				$matches=array();
				foreach($prices as $price){
					if($price->price_product_id==$element->product_id){
						if($price->price_currency_id==$currency_id){
							$matches[$price->price_min_quantity]=$price;
						}
						$currentRowPrices[]=$price;
					}
				}
				if(empty($matches)&&!empty($currentRowPrices)){
					foreach($currentRowPrices as $price){
						if($price->price_currency_id==$main_currency){
							$matches[$price->price_min_quantity]=$price;
						}
					}
					if(empty($matches)){
						$match = array_pop($currentRowPrices);
						if(!empty($currentRowPrices)){
							foreach($currentRowPrices as $price){
								if($price->price_currency_id==$match->price_currency_id){
									$matches[$price->price_min_quantity]=$price;
								}
							}
						}
						$matches[]=$match;
					}
				}
				if(!empty($matches)){
					switch($price_display_type){
						case 'all':
							foreach($matches as $j => $match){
								$matches[$j]->price_value_with_tax = $this->getTaxedPrice($match->price_value,$zone_id,$element->product_tax_id);
							}
							$rows[$k]->prices = $matches;
							break;
						case 'cheapest':
							$min=0;
							$minVal=0;
							foreach($matches as $match){
								if($match->price_value<$minVal || $minVal==0){
									$min = $match;
									$minVal = $match->price_value;
								}
							}
							$pricefound=true;
							$min->price_value_with_tax = $this->getTaxedPrice($min->price_value,$zone_id,$element->product_tax_id);
							$rows[$k]->prices = array($min);
							break;
						case 'unit':
							if(isset($matches[0])){
								$pricefound=true;
								$matches[0]->price_value_with_tax = $this->getTaxedPrice($matches[0]->price_value,$zone_id,$element->product_tax_id);
								$rows[$k]->prices = array($matches[0]);
							}else{
								$rows[$k]->prices = array(reset($matches));
							}
							break;
						case 'range':
							$min=0;
							$minVal=0;
							$max=0;
							$maxVal=0;
							foreach($matches as $match){
								if($match->price_value>$maxVal || $maxVal==0){
									$max = $match;
									$maxVal = $match->price_value;
								}
								if($match->price_value<$minVal || $minVal==0){
									$min = $match;
									$minVal = $match->price_value;
								}
							}
							$min->price_value_with_tax = $this->getTaxedPrice($min->price_value,$zone_id,$element->product_tax_id);
							$max->price_value_with_tax = $this->getTaxedPrice($max->price_value,$zone_id,$element->product_tax_id);
							$pricefound=true;
							if($min->price_value_with_tax==$max->price_value_with_tax){
								$rows[$k]->prices = array($min);
							}else{
								$rows[$k]->prices = array($min,$max);
							}
							break;
						default:
							$rows = array();
							return;
					}
				}
			}
			if(!$pricefound){
				$variantSearch[]=$element->product_id;
			}
		}
		if(!empty($variantSearch)){
			$filters=array('a.product_parent_id IN ('.implode(',',$variantSearch).')','a.product_published=1','b.price_currency_id IN ('.implode(',',$this->publishedCurrencies()).')');
			if(!$app->isAdmin()){
				hikashop_addACLFilters($filters,'price_access','b');
			}
			$query = 'SELECT a.*,b.* FROM '.hikashop_table('product').' AS a LEFT JOIN '.hikashop_table('price').' AS b ON a.product_id=b.price_product_id WHERE '.implode(' AND ',$filters);
			$this->database->setQuery($query);
			$prices = $this->database->loadObjectList();
			if(!empty($prices)){
				$unset=array();
				foreach($prices as $k => $price){
					if(empty($price->price_id)){
						$unset[]=$k;
					}
				}
				if(!empty($unset)){
					foreach($unset as $u){
						unset($prices[$u]);
					}
				}
			}
			if(!empty($prices)){
				foreach($rows as $k => $element){
					if(!empty($element->prices))continue;
					$currentRowPrices = array();
					$matches=array();
					foreach($prices as $price){
						if($price->product_parent_id==$element->product_id){
							if($price->price_currency_id==$currency_id){
								$matches[]=$price;
							}
							$currentRowPrices[]=$price;
						}
					}
					if(empty($matches)&&!empty($currentRowPrices)){
						foreach($currentRowPrices as $price){
							if($price->price_currency_id==$main_currency){
								$matches[]=$price;
							}
						}
						if(empty($matches)){
							$match = array_pop($currentRowPrices);
							if(!empty($currentRowPrices)){
								foreach($currentRowPrices as $price){
									if($price->price_currency_id==$match->price_currency_id){
										$matches[]=$price;
									}
								}
							}
							$matches[]=$match;
						}
					}
					if(!empty($matches)){
						switch($price_display_type){
							case 'all':
								$found = array();
								foreach($matches as $j => $match){
									if(in_array($match->price_value,$found)) continue;
									$found[]=$match->price_value;
									$matches[$j]->price_value_with_tax = $this->getTaxedPrice($match->price_value,$zone_id,$element->product_tax_id);
								}
								$rows[$k]->prices = $matches;
								break;
							case 'cheapest':
								$min=0;
								$minVal=0;
								foreach($matches as $match){
									if($match->price_value<$minVal || $minVal==0){
										$min = $match;
										$minVal = $match->price_value;
									}
								}
								$min->price_value_with_tax = $this->getTaxedPrice($min->price_value,$zone_id,$element->product_tax_id);
								$rows[$k]->prices = array($min);
								break;
							case 'unit':
								$found = false;
								foreach($matches as $j => $match){
									if(empty($match->price_min_quantity)){
										$matches[0]->price_value_with_tax = $this->getTaxedPrice($matches[0]->price_value,$zone_id,$element->product_tax_id);
										$rows[$k]->prices = array($matches[0]);
										$found = true;
										break;
									}
								}
								if(!$found){
									$rows[$k]->prices = array(reset($matches));
								}
								break;
							case 'range':
								$min=0;
								$minVal=0;
								$max=0;
								$maxVal=0;
								foreach($matches as $match){
									if($match->price_value>$maxVal || $maxVal==0){
										$max = $match;
										$maxVal = $match->price_value;
									}
									if($match->price_value<$minVal || $minVal==0){
										$min = $match;
										$minVal = $match->price_value;
									}
								}
								$min->price_value_with_tax = $this->getTaxedPrice($min->price_value,$zone_id,$element->product_tax_id);
								$max->price_value_with_tax = $this->getTaxedPrice($max->price_value,$zone_id,$element->product_tax_id);
								if($min->price_value_with_tax==$max->price_value_with_tax){
									$rows[$k]->prices = array($min);
								}else{
									$rows[$k]->prices = array($min,$max);
								}
								break;
						}
					}
				}
			}
		}

		$cids = array();
		if(!empty($rows)){
			foreach($rows as $k => $row){
				if(!empty($row->prices)){





					if(!empty($rows[$k]->prices)){
						foreach($rows[$k]->prices as $k2 => $price){
							if($price->price_currency_id!=$currency_id){
								$cids[$price->price_currency_id]=$price->price_currency_id;
							}
						}
					}
				}
			}
		}
		if(!empty($discounts)){
			foreach($discounts as $discount){
				$cids[$discount->discount_currency_id]=$discount->discount_currency_id;
			}
		}
		if(!empty($cids)){
			if(empty($cids[$currency_id])) $cids[$currency_id]=$currency_id;
			if(empty($cids[$main_currency]))$cids[$main_currency]=$main_currency;
			$query = 'SELECT * FROM '.hikashop_table('currency').' WHERE currency_id IN ('.implode(',',$cids).')';
			$this->database->setQuery($query);
			$currencies = $this->database->loadObjectList('currency_id');
			foreach($rows as $k => $row){
				if(!empty($row->prices)){
					$this->convertPrices($row->prices,$currencies,$currency_id,$main_currency);
				}
			}
			if(!empty($discounts)){
				$this->convertDiscounts($discounts,$currencies,$currency_id,$main_currency);
				$this->cartDiscountsLeft=array();
				$this->addDiscountToPrices($rows,$discounts,$discount_before_tax,$zone_id);
			}
		}
	}
	function convertUniquePrice($price,$srcCurrency_id, $dstCurrency_id){
		$config =& hikashop_config();
		$main_currency_id = $config->get('main_currency',1);
		$currencies=array();
		$ids=array();
		$ids[$main_currency_id]=$main_currency_id;
		$ids[$srcCurrency_id]=$srcCurrency_id;
		$ids[$dstCurrency_id]=$dstCurrency_id;
		$currencies=$this->getCurrencies($ids,$currencies);
		$srcCurrency = $currencies[$srcCurrency_id];
		$dstCurrency = $currencies[$dstCurrency_id];
		$mainCurrency = $currencies[$main_currency_id];
		if($srcCurrency_id!=$main_currency_id){
			$price=floatval($price)/floatval($srcCurrency->currency_rate);
		}
		if($dstCurrency_id!=$main_currency_id){
			$price=floatval($price)*floatval($dstCurrency->currency_rate);
		}
		$price=round($price,3);
		return $price;
	}
	function convertPrices(&$prices,$currencies,$currency_id,$main_currency){
		$unset = array();
		foreach($prices as $k2 => $price){
			if($price->price_currency_id!=$currency_id){
				if(isset($currencies[$price->price_currency_id]) && isset($currencies[$currency_id]) && isset($currencies[$main_currency])){
					$prices[$k2]->price_orig_value = $price->price_value;
					$prices[$k2]->price_orig_value_with_tax = $price->price_value_with_tax;
					$prices[$k2]->price_orig_currency_id = $price->price_currency_id;
					$prices[$k2]->price_currency_id = $currency_id;
					$srcCurrency = $currencies[$prices[$k2]->price_orig_currency_id];
					$dstCurrency = $currencies[$currency_id];
					$mainCurrency =  $currencies[$main_currency];
					$prices[$k2]->price_currency_id = $currency_id;
					$prices[$k2]->price_value=floatval($prices[$k2]->price_value);
					$prices[$k2]->price_value_with_tax=floatval($prices[$k2]->price_value_with_tax);
					if($srcCurrency->currency_id!=$mainCurrency->currency_id){
						if(bccomp($srcCurrency->currency_percent_fee,0,2)){
							$prices[$k2]->price_value+=$prices[$k2]->price_value*floatval($srcCurrency->currency_percent_fee)/100.0;
							$prices[$k2]->price_value_with_tax+=$prices[$k2]->price_value_with_tax*floatval($srcCurrency->currency_percent_fee)/100.0;
						}
						$prices[$k2]->price_value=$prices[$k2]->price_value/floatval($srcCurrency->currency_rate);
						$prices[$k2]->price_value_with_tax=$prices[$k2]->price_value_with_tax/floatval($srcCurrency->currency_rate);
					}
					if($dstCurrency->currency_id!=$mainCurrency->currency_id){
						$prices[$k2]->price_value=floatval($prices[$k2]->price_value)*floatval($dstCurrency->currency_rate);
						$prices[$k2]->price_value_with_tax=floatval($prices[$k2]->price_value_with_tax)*floatval($dstCurrency->currency_rate);
						if(bccomp($dstCurrency->currency_percent_fee,0,2)){
							$prices[$k2]->price_value+=$prices[$k2]->price_value*floatval($dstCurrency->currency_percent_fee)/100.0;
							$prices[$k2]->price_value_with_tax+=$prices[$k2]->price_value_with_tax*floatval($dstCurrency->currency_percent_fee)/100.0;
						}
					}
				}else{
					$unset[]=$k2;
				}
			}
		}
		if(!empty($unset)){
			foreach($unset as $u){
				unset($prices[$u]);
			}
		}
	}
	function selectDiscount(&$product,&$discounts,$zone_id){
		$discountsSelected= array();
		$discountSkippedBecauseOverQuota = false;
		$id = $product->product_id;
		if(!empty($product->product_parent_id)){
			$id = $product->product_parent_id;
		}
		static $zones = array();
		$zoneClass = hikashop_get('class.zone');
		if(empty($zones[$zone_id])){
			foreach($discounts as $discount){
				if($discount->discount_zone_id){
					$zones[$zone_id] = $zoneClass->getZoneParents($zone_id);
					break;
				}
			}
		}
		foreach($discounts as $discount){
			$value = (int)$discount->discount_flat_amount.'_'.$discount->discount_percent_amount;
			if($discount->discount_zone_id){
				$zone = $zoneClass->get($discount->discount_zone_id);
				if($zone && !in_array($zone->zone_namekey,$zones[$zone_id])){
					continue;
				}
			}
			if(!empty($product->cart_product_quantity) && empty($product->discount)){
				if(!isset($this->cartDiscountsLeft[$discount->discount_code])){
					$this->cartDiscountsLeft[$discount->discount_code] = $discount->discount_quota-$discount->discount_used_times;
				}
				if(!empty($discount->discount_quota) && $this->cartDiscountsLeft[$discount->discount_code]<$product->cart_product_quantity){
					$discountSkippedBecauseOverQuota = true;
					continue;
				}
				$this->cartDiscountsLeft[$discount->discount_code]-=$product->cart_product_quantity;
			}
			if(!empty($discount->discount_product_id) && $product->product_id==$discount->discount_product_id){
				$discountsSelected[0][$value]=$discount;
				continue;
			}
			if(!empty($discount->discount_product_id) && !empty($product->product_parent_id) && $product->product_parent_id==$discount->discount_product_id){
				$discountsSelected[5][$value]=$discount;
				continue;
			}
			if(empty($discount->discount_product_id) && !empty($discount->discount_category_id)){
				$categories = $this->_getCategories($id,$discount->discount_category_childs);
				if(!empty($categories)){
					foreach($categories as $val){
						if($val->category_id==$discount->discount_category_id){
							$discountsSelected[10][$val->category_depth][$value]=$discount;
							continue;
						}
					}
				}
			}
			if(empty($discount->discount_product_id) && empty($discount->discount_category_id)){
				$discountsSelected[20][$value]=$discount;
			}
		}
		if(!empty($discountsSelected)){
			ksort($discountsSelected);
			$discount = array_shift($discountsSelected);
			if(is_array($discount)){
				krsort($discount);
				$discount = array_shift($discount);
				if(is_array($discount)){
					krsort($discount);
					$discount = array_shift($discount);
				}
			}
			$product->discount = $discount;
		}elseif($discountSkippedBecauseOverQuota){
		}
	}
	function convertDiscounts(&$discounts,&$currencies,$currency_id,$main_currency){
		$unset = array();
		foreach($discounts as $k => $discount){
			if($discount->discount_currency_id!=$currency_id){
				if(bccomp($discounts[$k]->discount_flat_amount,0,5)){
					if(isset($currencies[$discount->discount_currency_id]) && isset($currencies[$currency_id]) && isset($currencies[$main_currency])){
						$discounts[$k]->discount_flat_amount_orig = round($discounts[$k]->discount_flat_amount,(int)$currencies[$discounts[$k]->discount_currency_id]->currency_locale['int_frac_digits']);
						$discounts[$k]->discount_currency_id_orig = $discounts[$k]->discount_currency_id;
						$discounts[$k]->discount_currency_id = $currency_id;
						$srcCurrency = $currencies[$discount->discount_currency_id_orig];
						$dstCurrency = $currencies[$currency_id];
						$mainCurrency =  $currencies[$main_currency];
						if($srcCurrency->currency_id!=$mainCurrency->currency_id){
							if(bccomp($srcCurrency->currency_percent_fee,0,2)){
								$discounts[$k]->discount_flat_amount+=$discounts[$k]->discount_flat_amount*floatval($srcCurrency->currency_percent_fee)/100.0;
							}
							$discounts[$k]->discount_flat_amount=floatval($discounts[$k]->discount_flat_amount)/floatval($srcCurrency->currency_rate);
						}
						if($dstCurrency->currency_id!=$mainCurrency->currency_id){
							$discounts[$k]->discount_flat_amount=floatval($discounts[$k]->discount_flat_amount)*floatval($dstCurrency->currency_rate);
							if(bccomp($dstCurrency->currency_percent_fee,0,2)){
								$discounts[$k]->discount_flat_amount+=$discounts[$k]->discount_flat_amount*floatval($dstCurrency->currency_percent_fee)/100.0;
							}
						}
						$discounts[$k]->discount_flat_amount=round($discounts[$k]->discount_flat_amount,(int)$currencies[$discounts[$k]->discount_currency_id]->currency_locale['int_frac_digits']);
					}else{
						$unset[]=$k;
					}
				}else{
					$discounts[$k]->discount_flat_amount=0;
				}
			}
		}
		if(!empty($unset)){
			foreach($unset as $u){
				unset($discounts[$u]);
			}
		}
	}
	function convertStats(&$orders){
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = hikashop_getCurrency();
		$currencies = array();
		foreach($orders as $k => $order){
			if($order->currency_id!=$currency_id && bccomp($order->total,0,5)){
				$currencies[$order->currency_id]=$order->currency_id;
			}
		}
		if(!empty($currencies)){
			$currencies[$currency_id]=$currency_id;
			$currencies[$main_currency]=$main_currency;
			$null=null;
			$currencies = $this->getCurrencies($currencies,$null);
			$unset = array();
			foreach($orders as $k => $order){
				if($order->currency_id!=$currency_id){
					if(isset($currencies[$order->currency_id]) && isset($currencies[$currency_id]) && isset($currencies[$main_currency])){
						$orders[$k]->total_orig = round($orders[$k]->total,(int)$currencies[$orders[$k]->currency_id]->currency_locale['int_frac_digits']);
						$orders[$k]->currency_id_orig = $orders[$k]->currency_id;
						$orders[$k]->currency_id = $currency_id;
						$srcCurrency = $currencies[$order->currency_id_orig];
						$dstCurrency = $currencies[$currency_id];
						$mainCurrency =  $currencies[$main_currency];
						if($srcCurrency->currency_id!=$mainCurrency->currency_id){
							if(bccomp($srcCurrency->currency_percent_fee,0,2)){
								$orders[$k]->total+=$orders[$k]->total*floatval($srcCurrency->currency_percent_fee)/100.0;
							}
							$orders[$k]->total=(floatval($orders[$k]->total)/floatval($srcCurrency->currency_rate));
						}
						if($dstCurrency->currency_id!=$mainCurrency->currency_id){
							$orders[$k]->total=floatval($orders[$k]->total)*floatval($dstCurrency->currency_rate);
							if(bccomp($dstCurrency->currency_percent_fee,0,2)){
								$orders[$k]->total+=$orders[$k]->total*floatval($dstCurrency->currency_percent_fee)/100.0;
							}
						}
						$orders[$k]->total=round($orders[$k]->total,(int)$currencies[$orders[$k]->currency_id]->currency_locale['int_frac_digits']);
					}else{
						$unset[]=$k;
					}
				}else{
					$orders[$k]->total=round($orders[$k]->total,(int)$currencies[$orders[$k]->currency_id]->currency_locale['int_frac_digits']);
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($orders[$u]);
				}
			}
		}
	}
	function convertShippings(&$shippings){
		$config =& hikashop_config();
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = hikashop_getCurrency();
		if(!in_array($currency_id,$this->publishedCurrencies())){
			$currency_id = $main_currency;
		}
		$currencies = array();
		foreach($shippings as $k => $shipping){
			if($shipping->shipping_currency_id!=$currency_id && bccomp($shipping->shipping_price,0,5)){
				$currencies[$shipping->shipping_currency_id]=$shipping->shipping_currency_id;
			}
		}
		if(!empty($currencies)){
			$currencies[$currency_id]=$currency_id;
			$currencies[$main_currency]=$main_currency;
			$null=null;
			$currencies = $this->getCurrencies($currencies,$null);
			$unset = array();
			foreach($shippings as $k => $shipping){
				if(empty($shipping->shipping_currency_id)){
					continue;
				}
				if($shipping->shipping_currency_id!=$currency_id){
					if(isset($currencies[$shipping->shipping_currency_id]) && isset($currencies[$currency_id]) && isset($currencies[$main_currency])){
						if(!isset($shippings[$k]->shipping_params->shipping_min_price)){
							$shippings[$k]->shipping_params->shipping_min_price=0.0;
						}
						$shippings[$k]->shipping_price_orig = round($shippings[$k]->shipping_price,2);
						$shippings[$k]->shipping_params->shipping_min_price_orig = round($shippings[$k]->shipping_params->shipping_min_price,(int)$currencies[$shippings[$k]->shipping_currency_id]->currency_locale['int_frac_digits']);
						$shippings[$k]->shipping_currency_id_orig = $shippings[$k]->shipping_currency_id;
						$shippings[$k]->shipping_currency_id = $currency_id;
						$srcCurrency = $currencies[$shipping->shipping_currency_id_orig];
						$dstCurrency = $currencies[$currency_id];
						$mainCurrency =  $currencies[$main_currency];
						if($srcCurrency->currency_id!=$mainCurrency->currency_id){
							if(bccomp($srcCurrency->currency_percent_fee,0,2)){
								$shippings[$k]->shipping_price+=$shippings[$k]->shipping_price*floatval($srcCurrency->currency_percent_fee)/100.0;
								$shippings[$k]->shipping_params->shipping_min_price+=$shippings[$k]->shipping_params->shipping_min_price*floatval($srcCurrency->currency_percent_fee)/100.0;
							}
							$shippings[$k]->shipping_price=(floatval($shippings[$k]->shipping_price)/floatval($srcCurrency->currency_rate));
							$shippings[$k]->shipping_params->shipping_min_price=(floatval($shippings[$k]->shipping_params->shipping_min_price)/floatval($srcCurrency->currency_rate));
						}
						if($dstCurrency->currency_id!=$mainCurrency->currency_id){
							$shippings[$k]->shipping_price=floatval($shippings[$k]->shipping_price)*floatval($dstCurrency->currency_rate);
							$shippings[$k]->shipping_params->shipping_min_price=floatval($shippings[$k]->shipping_params->shipping_min_price)*floatval($dstCurrency->currency_rate);
							if(bccomp($dstCurrency->currency_percent_fee,0,2)){
								$shippings[$k]->shipping_price+=$shippings[$k]->shipping_price*floatval($dstCurrency->currency_percent_fee)/100.0;
								$shippings[$k]->shipping_params->shipping_min_price+=$shippings[$k]->shipping_params->shipping_min_price*floatval($dstCurrency->currency_percent_fee)/100.0;
							}
						}
						$shippings[$k]->shipping_price=round($shippings[$k]->shipping_price,2);
						$shippings[$k]->shipping_params->shipping_min_price=round($shippings[$k]->shipping_params->shipping_min_price,(int)$currencies[$shippings[$k]->shipping_currency_id]->currency_locale['int_frac_digits']);
					}else{
						$unset[]=$k;
					}
				}else{
					$shippings[$k]->shipping_price=round($shippings[$k]->shipping_price,2);
					$shippings[$k]->shipping_params->shipping_min_price=round($shippings[$k]->shipping_params->shipping_min_price,(int)$currencies[$shippings[$k]->shipping_currency_id]->currency_locale['int_frac_digits']);
				}
			}
			if(!empty($unset)){
				foreach($unset as $u){
					unset($shippings[$u]);
				}
			}
		}
	}
	function _getCategories($id,$farAwayParent=false){
		static $result=array();
		$key = $id.'_'.(int)$farAwayParent;
		if(!isset($result[$key])){
			$app =& JFactory::getApplication();
			if(!$farAwayParent){
				$filters = array('a.product_id = '.(int)$id);
				if(!$app->isAdmin()){
					hikashop_addACLFilters($filters,'category_access','b');
				}
				$query = 'SELECT DISTINCT b.category_id, b.category_depth FROM '.hikashop_table('product_category').' AS a LEFT JOIN '.hikashop_table('category').' AS b ON a.category_id=b.category_id WHERE '.implode(' AND ',$filters);
			}else{
				$filters = array('b.category_right >= a.category_right','c.product_id = '.(int)$id);
				if(!$app->isAdmin()){
					hikashop_addACLFilters($filters,'category_access','b');
				}
				$query = 'SELECT DISTINCT b.category_id, b.category_depth FROM '.hikashop_table('product_category').' AS c LEFT JOIN '.
				hikashop_table('category').' AS a ON c.category_id=a.category_id LEFT JOIN '.
				hikashop_table('category').' AS b ON a.category_left >= b.category_left WHERE '.implode(' AND ',$filters);
			}
			$this->database->setQuery($query);
			$array= $this->database->loadObjectList();
			if(!is_array($array)){
				$array = array();
			}
			$result[$key]=$array;
		}
		return $result[$key];
	}
	function getPrices(&$element,&$ids,$currency_id,$main_currency,$zone_id,$discount_before_tax){
		$currency_ids = array($currency_id=>$currency_id,$main_currency=>$main_currency);
		$filters=array('price_currency_id IN ('.implode(',',$this->publishedCurrencies()).')');
		if(!empty($ids)){
			$ids_string = '';
			foreach($ids as $id){
				if(!empty($id)) $ids_string.= (int)$id.',';
			}
			$ids_string=rtrim($ids_string,',');
			if(empty($ids_string)){
				if(!empty($element->product_id)){
					$ids_string = $element->product_id;
					$ids = array($ids_string);
				}else{
					return false;
				}
			}
			$filters[]= 'price_product_id IN ('.$ids_string.')';
		}
		$app =& JFactory::getApplication();
		if(!$app->isAdmin()){
			hikashop_addACLFilters($filters,'price_access');
		}
		$query = 'SELECT * FROM '.hikashop_table('price').' WHERE '.implode(' AND ',$filters). ' ORDER BY price_value DESC';
		$this->database->setQuery($query);
		$prices = $this->database->loadObjectList();
		if(!empty($prices)){
			if(is_array($element)){
				foreach($element as $k => $el){
					$this->removeAndAddPrices($element[$k],$prices,$currency_ids,$currency_id,$main_currency,$zone_id);
				}
			}else{
				$this->removeAndAddPrices($element,$prices,$currency_ids,$currency_id,$main_currency,$zone_id);
			}
			$uneeded = array();
			foreach($prices as $k => $price){
				if(empty($price->needed)) $uneeded[]=$k;
			}
			if(!empty($uneeded)){
				foreach($uneeded as $k){
					unset($prices[$k]);
				}
			}
		}
		$filters=array(
			'discount_type=\'discount\'',
			'discount_published=1',
			'( discount_quota>discount_used_times OR discount_quota=0 )',
			'discount_start < '.time(),
			'( discount_end > '.time().' OR discount_end = 0 )',
			'( discount_product_id IN ('.$ids_string.') OR discount_product_id=0 )',
			'( discount_flat_amount!=0 OR discount_percent_amount!=0 )'
		);
		if(!$app->isAdmin()){
			hikashop_addACLFilters($filters,'discount_access');
		}
		$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE '.implode(' AND ',$filters);
		$this->database->setQuery($query);
		$discounts = $this->database->loadObjectList();
		if(!empty($discounts)){
			foreach($discounts as $discount){
				if(!empty($discount->discount_currency_id)){
					$currency_ids[$discount->discount_currency_id]=$discount->discount_currency_id;
				}
			}
		}
		$null=null;
		$currencies = $this->getCurrencies($currency_ids,$null);
		$this->convertPrice($element,$currencies,$currency_id,$main_currency);
		if(!empty($discounts)){
			$this->cartDiscountsLeft=array();
			$this->productsDone=array();
			$this->convertDiscounts($discounts,$currencies,$currency_id,$main_currency);
			$this->addDiscountToPrices($element,$discounts,$discount_before_tax,$zone_id);
			if(!empty($element->options)){
				$this->addDiscountToPrices($element->options,$discounts,$discount_before_tax,$zone_id);
			}
		}
	}
	function removeAndAddPrices(&$element,&$prices,&$currency_ids,$currency_id,$main_currency,$zone_id){
		$this->removeUneededPrices($element,$prices,$currency_id,$main_currency);
		$this->addTax($prices,$element,$currency_ids,$zone_id,$element->product_tax_id);
		if(!empty($element->variants)){
			foreach($element->variants as $k2 => $variant){
				$this->removeUneededPrices($element->variants[$k2],$prices,$currency_id,$main_currency);
				$this->addTax($prices,$element->variants[$k2],$currency_ids,$zone_id,$element->product_tax_id);
			}
		}
		if(!empty($element->options)){
			foreach($element->options as $k2 => $optionElement){
				$this->removeAndAddPrices($element->options[$k2],$prices,$currency_ids,$currency_id,$main_currency,$zone_id);
			}
		}
	}
	function removeUneededPrices(&$element,&$prices,$currency_id,$main_currency){
		$elementPrices = array();
		foreach($prices as $k => $price){
			if($price->price_product_id==$element->product_id){
				$elementPrices[$price->price_currency_id][$price->price_min_quantity][]=$k;
			}
		}
		if(empty($elementPrices)){
			return true;
		}




		if(empty($elementPrices[$currency_id])){
			if(isset($elementPrices[$main_currency])){
				$this->_removePrices($elementPrices,$prices,$main_currency);
			}else{
				reset($elementPrices);
				$found=key($elementPrices);
				foreach($elementPrices as $currency => $price){
					if(isset($price[0])){
						$found = $currency;
					}
				}
				$this->_removePrices($elementPrices,$prices,$found);
			}
		}else{
			$this->_removePrices($elementPrices,$prices,$currency_id);
		}
		if(!empty($element->cart_product_quantity)){
			if(empty($element->cart_product_total_quantity)){
				$element->cart_product_total_quantity = $element->cart_product_quantity;
			}
			$elementPrices=array();
			foreach($prices as $k => $price){
				if($price->price_product_id==$element->product_id){
					$price->k=$k;
					$elementPrices[$price->price_min_quantity] = $price;
				}
			}
			krsort($elementPrices);
			$found = false;
			foreach($elementPrices as $qty => $price){
				if($qty>$element->cart_product_total_quantity || $found){
				}else{
					$prices[$price->k]->needed = true;
					$found = true;
				}
			}
		}
		return true;
	}
	function _removePrices(&$elementPrices,&$prices,$main_currency){
		foreach($elementPrices as $currency => $currencyPrices){
			if($currency!=$main_currency){
				foreach($currencyPrices as $quantityPrices){
					foreach($quantityPrices as $k){
						unset($prices[$k]);
					}
				}
			}
		}
	}
	function convertCoupon(&$coupon,$currency_id){
		if($coupon->discount_currency_id==$currency_id){
			return true;
		}
		$config =& hikashop_config();
		$main_currency = $config->get('main_currency',1);
		$currencies = array($coupon->discount_currency_id,$currency_id);
		if($coupon->discount_currency_id!=$main_currency){
			$currencies[]=$main_currency;
		}
		$null = null;
		$currenciesData=$this->getCurrencies($currencies,$null);
		$coupon->discount_currency_id_orig = $coupon->discount_currency_id;
		$srcCurrency = $currenciesData[$coupon->discount_currency_id];
		$dstCurrency = $currenciesData[$currency_id];
		$mainCurrency =  $currenciesData[$main_currency];
		$coupon->discount_currency_id = $currency_id;
		if(bccomp($coupon->discount_flat_amount,0,5)){
			$coupon->discount_flat_amount_orig = round($coupon->discount_flat_amount,(int)$currenciesData[$coupon->discount_currency_id_orig]->currency_locale['int_frac_digits']);
			if($srcCurrency->currency_id!=$mainCurrency->currency_id){
				if(bccomp($srcCurrency->currency_percent_fee,0,2)){
					$coupon->discount_flat_amount+=$coupon->discount_flat_amount*floatval($srcCurrency->currency_percent_fee)/100.0;
				}
				$coupon->discount_flat_amount=(floatval($coupon->discount_flat_amount)/floatval($srcCurrency->currency_rate));
			}
			if($dstCurrency->currency_id!=$mainCurrency->currency_id){
				$coupon->discount_flat_amount=floatval($coupon->discount_flat_amount)*floatval($dstCurrency->currency_rate);
				if(bccomp($dstCurrency->currency_percent_fee,0,2)){
					$coupon->discount_flat_amount+=$coupon->discount_flat_amount*floatval($dstCurrency->currency_percent_fee)/100.0;
				}
			}
			$coupon->discount_flat_amount=round($coupon->discount_flat_amount,(int)$currenciesData[$coupon->discount_currency_id]->currency_locale['int_frac_digits']);
		}else{
			$coupon->discount_flat_amount=0;
		}
		if(bccomp($coupon->discount_minimum_order,0,5)){
			$coupon->discount_minimum_order_orig = round($coupon->discount_minimum_order,(int)$currenciesData[$coupon->discount_currency_id_orig]->currency_locale['int_frac_digits']);
			if($srcCurrency->currency_id!=$mainCurrency->currency_id){
				if(bccomp($srcCurrency->currency_percent_fee,0,2)){
					$coupon->discount_minimum_order+=$coupon->discount_minimum_order*floatval($srcCurrency->currency_percent_fee)/100.0;
				}
				$coupon->discount_minimum_order=(floatval($coupon->discount_minimum_order)/floatval($srcCurrency->currency_rate));
			}
			if($dstCurrency->currency_id!=$mainCurrency->currency_id){
				$coupon->discount_minimum_order=floatval($coupon->discount_minimum_order)*floatval($dstCurrency->currency_rate);
				if(bccomp($dstCurrency->currency_percent_fee,0,2)){
					$coupon->discount_minimum_order+=$coupon->discount_minimum_order*floatval($dstCurrency->currency_percent_fee)/100.0;
				}
			}
			$coupon->discount_minimum_order=round($coupon->discount_minimum_order,(int)$currenciesData[$coupon->discount_currency_id]->currency_locale['int_frac_digits']);
		}else{
			$coupon->discount_minimum_order=0;
		}
		return true;
	}
	function getCurrencies($ids,&$currencies){
		static $cachedCurrencies=array();
		if(!empty($currencies)){
			foreach($currencies as $currency){
				$this->checkLocale($currency);
				$cachedCurrencies[(int)$currency->currency_id]=$currency;
			}
		}
		if(!is_null($ids)){
			if(!is_array($ids)){
				$ids = array($ids);
			}
			$need = array();
			foreach($ids as $id){
				if(!isset($cachedCurrencies[(int)$id])){
					$need[]=(int)$id;
				}
			}
			if(!empty($need)){
				$query = 'SELECT * FROM '.hikashop_table('currency').' WHERE currency_id IN ('.implode(',',$need).')';
				$this->database->setQuery($query);
				$results = $this->database->loadObjectList();
				foreach($results as $k => $v){
					$this->checkLocale($results[$k]);
				}
				$this->getCurrencies(null,$results);
			}
			$found = array();
			foreach($ids as $id){
				if(isset($cachedCurrencies[(int)$id])) $found[(int)$id]=$cachedCurrencies[(int)$id];
			}
			return $found;
		}
		return true;
	}
	function calculateTotal(&$rows,&$order,$currency_id){
		$total=null;
		$total->price_value=0.0;
		$total->price_value_with_tax=0.0;
		$total->price_currency_id=$currency_id;
		foreach($rows as $k => $row){
			if(!empty($row->prices)&&$row->cart_product_quantity>0){
				$price = reset($row->prices);
				foreach(get_object_vars($total) as $key => $value){
					if(!in_array($key,array('price_currency_id','price_orig_currency_id','price_value_without_discount_with_tax','price_value_without_discount')) && isset($price->$key)){
						$total->$key = $total->$key + $price->$key;
					}
				}
			}
		}
		$order->prices = array($total);
	}
	function pricesSelection(&$prices,$quantity){
		$matches=array();
		$otherCurrencies=array();
		if(!empty($prices)){
			foreach($prices as $k2 => $price){
				if($price->price_min_quantity>$quantity) continue;
				if(empty( $price->price_orig_currency_id)){
					$matches[]=$price;
				}else{
					$otherCurrencies[]=$price;
				}
			}
		}
		if(empty($matches) && !empty($otherCurrencies)){
			$config =& hikashop_config();
			$main_currency = (int)$config->get('main_currency',1);
			foreach($otherCurrencies as $price){
				if($price->price_orig_currency_id==$main_currency){
					$matches[]=$price;
				}
			}
			if(empty($matches)){
				$matches = $otherCurrencies;
			}
		}
		$prices = $matches;
	}
	function calculateProductPriceForQuantity(&$product){
		if(function_exists('hikashop_product_price_for_quantity_in_cart')){
			hikashop_product_price_for_quantity_in_cart($product);
		}else{
			$this->quantityPrices($product->prices,@$product->cart_product_quantity,$product->cart_product_total_quantity);
		}
	}
	function quantityPrices(&$prices,$quantity,$total_quantity){
		$this->pricesSelection($prices,$total_quantity);
		$unitPrice = null;
		if(!empty($prices)){
			$unitPrice = reset($prices);
			if(count($prices)>1){
				$cheapest_value=$unitPrice->price_value;
				foreach($prices as $price){
					if($cheapest_value>$price->price_value){
						$unitPrice = $price;
						$cheapest_value = $price->price_value;
					}
				}
			}
			$this->quantityPrice($unitPrice,$quantity);
			$prices = array($unitPrice);
		}
	}
	function quantityPrice(&$price,$quantity){
		if($quantity>0){
			$price->unit_price->price_currency_id = $price->price_currency_id;
			$rounding = $this->getRounding($price->price_currency_id);
			if(isset($price->price_orig_currency_id)){
				$price->unit_price->price_orig_currency_id = $price->price_orig_currency_id;
			}
			if(isset($price->price_value_without_discount)){
				$price->unit_price->price_value_without_discount=round($price->price_value_without_discount,$rounding);
				$price->price_value_without_discount=round($price->unit_price->price_value_without_discount*$quantity,$rounding);
			}
			if(isset($price->price_value)){
				$price->unit_price->price_value=round($price->price_value,$rounding);
				$price->price_value=round($price->unit_price->price_value*$quantity,$rounding);
			}
			if(isset($price->price_orig_value)){
				$price->unit_price->price_orig_value=round($price->price_orig_value,$rounding);
				$price->price_orig_value=round($price->unit_price->price_orig_value*$quantity,$rounding);
			}
			if(isset($price->price_orig_value_with_tax)){
				$price->unit_price->price_orig_value_with_tax=round($price->price_orig_value_with_tax,$rounding);
				$price->price_orig_value_with_tax=round($price->unit_price->price_orig_value_with_tax*$quantity,$rounding);
			}
			if(isset($price->price_orig_value_without_discount)){
				$price->unit_price->price_orig_value_without_discount=round($price->price_orig_value_without_discount,$rounding);
				$price->price_orig_value_without_discount=round($price->unit_price->price_orig_value_without_discount*$quantity,$rounding);
			}
			if(isset($price->price_value_without_discount_with_tax)){
				$price->unit_price->price_value_without_discount_with_tax=round($price->price_value_without_discount_with_tax,$rounding);
				$price->price_value_without_discount_with_tax=round($price->unit_price->price_value_without_discount_with_tax*$quantity,$rounding);
			}
			if(isset($price->price_value_with_tax)){
				$price->unit_price->price_value_with_tax=round($price->price_value_with_tax,$rounding);
				$price->price_value_with_tax=round($price->unit_price->price_value_with_tax*$quantity,$rounding);
			}
		}
	}
	function addDiscountToPrices(&$element,&$discounts,$discount_before_tax,$zone_id){
		if(is_array($element)){
			foreach($element as $k => $el){
				$this->addDiscountToPrices($element[$k],$discounts,$discount_before_tax,$zone_id);
			}
		}
		else{
			if(empty($element->discount) && !empty($element->prices)){
				$this->selectDiscount($element,$discounts,$zone_id);
				if(!empty($element->discount)){
					foreach($element->prices as $k=>$price){
						$this->addDiscount($element->prices[$k],$element->discount,$discount_before_tax,$zone_id,$element->product_tax_id);
					}
				}
			}
			if(!empty($element->variants)){
				foreach($element->variants as $k => $row){
					if(empty($row->discount) && !empty($row->prices)){
						$this->selectDiscount($element->variants[$k],$discounts,$zone_id);
						if(!empty($element->variants[$k]->discount)){
							foreach($row->prices as $k2=>$price){
								$this->addDiscount($element->variants[$k]->prices[$k2],$element->variants[$k]->discount,$discount_before_tax,$zone_id,$element->product_tax_id);
							}
						}
					}
				}
			}
		}
	}
	function updateRatesWithNewMainCurrency($old_currency,$new_currency){
		if($old_currency==$new_currency) return true;
		$ids = array($old_currency,$new_currency);
		$null=null;
		$currencies = $this->getCurrencies($ids,$null);
		if(empty($currencies[$old_currency])||empty($currencies[$new_currency])) return true;
		$main_currency = 1/$currencies[$new_currency]->currency_rate;
		$query = 'UPDATE '.hikashop_table('currency').' SET currency_rate=currency_rate*'.$main_currency;
		$this->database->setQuery($query);
		return $this->database->query();
	}
	function save(&$element){
		$element->currency_modified = time();
		return parent::save($element);
	}
	function addDiscount(&$price,&$discount,$discount_before_tax,$zone_id,$product_tax_id){
		$price->price_value_without_discount=$price->price_value;
		if($discount_before_tax){
			if(bccomp($discount->discount_flat_amount,0,5)!==0){
				$price->price_value=$price->price_value-floatval($discount->discount_flat_amount);
			}else{
				$round = $this->getRounding(@$price->price_currency_id);
				$price->price_value=round(($price->price_value *(100.0-floatval($discount->discount_percent_amount)))/100.0,$round);
				if(isset($price->price_orig_value)){
					$price->price_orig_value_without_discount=$price->price_orig_value;
					$price->price_orig_value=round(($price->price_orig_value *(100.0-floatval($discount->discount_percent_amount)))/100.0,$round);
				}
			}
			$price->price_value_without_discount_with_tax = $this->getTaxedPrice($price->price_value_without_discount,$zone_id,$product_tax_id);
			$price->price_value_with_tax = $this->getTaxedPrice($price->price_value,$zone_id,$product_tax_id);
			if(isset($price->price_orig_value)){
				$price->price_orig_value_with_tax = $this->getTaxedPrice($price->price_orig_value,$zone_id,$product_tax_id);
			}
		}else{
			$price->price_value_without_discount_with_tax = $price->price_value_with_tax;
			if(bccomp($discount->discount_flat_amount,0,5)!==0){
				$price->price_value_with_tax=$price->price_value_with_tax-floatval($discount->discount_flat_amount);
			}else{
				$round = $this->getRounding(@$price->price_currency_id);
				$price->price_value_with_tax=round(($price->price_value_with_tax *(100.0-floatval($discount->discount_percent_amount)))/100.0,$round);
				if(isset($price->price_orig_value_with_tax)){
					$price->price_orig_value_without_discount_with_tax=$price->price_orig_value_with_tax;
					$price->price_orig_value_with_tax=round(($price->price_orig_value_with_tax *(100.0-floatval($discount->discount_percent_amount)))/100.0,$round);
				}
			}
			$price->price_value_without_discount = $this->getUntaxedPrice($price->price_value_without_discount_with_tax,$zone_id,$product_tax_id);
			$price->price_value = $this->getUntaxedPrice($price->price_value_with_tax,$zone_id,$product_tax_id);
			if(isset($price->price_orig_value_with_tax)){
				$price->price_orig_value = $this->getUntaxedPrice($price->price_orig_value_with_tax,$zone_id,$product_tax_id);
			}
		}
	}
	function getRounding($currency_id){
		if(empty($currency_id)){
			$round = 2;
		}else{
			$array = null;
			$currencies = $this->getCurrencies($currency_id,$array);
			$currency = $currencies[$currency_id];
			$round = (int)$currency->currency_locale['int_frac_digits'];
		}
		return $round;
	}
	function addCoupon(&$prices,&$discount){
		$config =& hikashop_config();
		$discount_before_tax = $config->get('discount_before_tax');
		if(isset($discount->discount_value)){
			$discountClass = hikashop_get('class.discount');
			$total =& $discount->total;
			$discount = $discountClass->get($discount->discount_id);
			$discount->total =& $total;
		}
		foreach($prices->prices as $k => $price){
			if(isset($prices->prices[$k]->price_value_without_discount_with_tax) && $prices->prices[$k]->price_value_without_discount_with_tax>0) continue;
			$prices->prices[$k]->price_value_without_discount_with_tax = $price->price_value_with_tax;
				$round = $this->getRounding(@$prices->prices[$k]->price_currency_id);
				$zone_id = hikashop_getZone();
				if(bccomp($discount->discount_flat_amount,0,5)!==0){
					$discount->discount_value_without_tax = $discount->discount_flat_amount_without_tax = $discount->discount_flat_amount;
					if($discount_before_tax){
						$discount->discount_flat_amount = $this->getTaxedPrice($discount->discount_flat_amount,$zone_id,$discount->discount_tax_id,$round);
					}
					$prices->prices[$k]->price_value_with_tax=$price->price_value_with_tax-floatval($discount->discount_flat_amount);
				}else{
					if($discount_before_tax){
						$discount->discount_value_without_tax = $discount->discount_percent_amount_calculated_without_tax = $discount->discount_percent_amount_calculated = round($price->price_value*floatval($discount->discount_percent_amount)/100.0,$round);
						$discount->discount_percent_amount_calculated = $price->price_value_with_tax*$discount->discount_percent_amount_calculated_without_tax/$price->price_value;
					}else{
						$discount->discount_value_without_tax = $discount->discount_percent_amount_calculated_without_tax = $discount->discount_percent_amount_calculated = round($price->price_value_with_tax*floatval($discount->discount_percent_amount)/100.0,$round);
					}
					$discount->discount_percent_amount_calculated = $this->getTaxedPrice($discount->discount_percent_amount_calculated,$zone_id,$discount->discount_tax_id,$round);
					$prices->prices[$k]->price_value_with_tax=$price->price_value_with_tax-$discount->discount_percent_amount_calculated;
					if(isset($price->price_orig_value_with_tax)){
						$prices->prices[$k]->price_orig_value_without_discount_with_tax=$price->price_orig_value_with_tax;
						$discount->discount_orig_percent_amount_calculated_without_tax = $discount->discount_orig_percent_amount_calculated = round($price->price_orig_value_with_tax*floatval($discount->discount_percent_amount)/100.0,$round);
						$discount->discount_orig_percent_amount_calculated = $this->getTaxedPrice($discount->discount_orig_percent_amount_calculated,$zone_id,$discount->discount_tax_id,$round);
						$prices->prices[$k]->price_orig_value_with_tax=$price->price_orig_value_with_tax-$discount->discount_orig_percent_amount_calculated;
					}
				}
				$discount->discount_value = $prices->prices[$k]->price_value_without_discount_with_tax-$prices->prices[$k]->price_value_with_tax;
			$prices->prices[$k]->price_value_without_discount=$price->price_value;
			$prices->prices[$k]->price_value=$price->price_value-$discount->discount_value_without_tax;
		}
	}
	function addShipping(&$shipping,&$total){
		if(PHP_VERSION < 5){
			$shipping->total = $total;
		}else{
			$shipping->total->prices = array(clone(reset($total->prices)));
		}
		foreach($shipping->total->prices as $k => $price){
			$shipping->total->prices[$k]->price_value_without_shipping_with_tax = $price->price_value_with_tax;
			$shipping->total->prices[$k]->price_value_without_shipping=$price->price_value;
			if(bccomp(@$shipping->shipping_price_with_tax,0,5)!==0){
				$shipping->total->prices[$k]->price_value_with_tax=$price->price_value_with_tax+floatval($shipping->shipping_price_with_tax);
				$shipping->total->prices[$k]->price_value=$price->price_value+$shipping->shipping_price;
			}
		}
	}
	function processShippings(&$usable_rates){
		if(!empty($usable_rates)){
			$this->convertShippings($usable_rates);
			$zone_id = hikashop_getZone();
			foreach($usable_rates as $k => $rate){
				$round = $this->getRounding(@$rate->shipping_currency_id);
				if(!empty($rate->shipping_tax_id) && bccomp($rate->shipping_price,0,5)){
					$usable_rates[$k]->shipping_price_with_tax = $this->getTaxedPrice($rate->shipping_price,$zone_id,$rate->shipping_tax_id,$round);
					if(isset($rate->shipping_price_orig) && bccomp($rate->shipping_price_orig,0,5)){
						$usable_rates[$k]->shipping_price_orig_with_tax = $this->getTaxedPrice($rate->shipping_price_orig,$zone_id,$rate->shipping_tax_id,$round);
					}else{
						$usable_rates[$k]->shipping_price_orig = 0.0;
						$usable_rates[$k]->shipping_price_orig_with_tax = 0.0;
					}
				}else{
					$usable_rates[$k]->shipping_price_with_tax = round($rate->shipping_price,$round);
					$usable_rates[$k]->shipping_price_orig_with_tax = round(@$usable_rates[$k]->shipping_price_orig,$round);
				}
			}
		}
	}
	function addTax(&$prices,&$element,&$currency_ids,$zone_id,$product_tax_id){
		foreach($prices as $price){
			$currency_ids[$price->price_currency_id]=$price->price_currency_id;
			if($price->price_product_id==$element->product_id){
				$round = $this->getRounding($price->price_currency_id);
				$price->price_value_with_tax = $this->getTaxedPrice($price->price_value,$zone_id,$product_tax_id,$round);
				$element->prices[]=(PHP_VERSION < 5) ? $price : clone($price);
			}
		}
	}
	function convertPrice(&$element,&$currencies,$currency_id,$main_currency){
		if(is_array($element)){
			foreach($element as $k => $row){
				$this->convertPrice($element[$k],$currencies,$currency_id,$main_currency);
			}
		}else{
			if(!empty($element->prices)){
				$this->convertPrices($element->prices,$currencies,$currency_id,$main_currency);
			}
			if(!empty($element->variants)){
				$this->convertPrice($element->variants,$currencies,$currency_id,$main_currency);
			}
			if(!empty($element->options)){
				$this->convertPrice($element->options,$currencies,$currency_id,$main_currency);
			}
		}
	}
	function format($number,$currency_id=0,$format_override='') {
		if(!$currency_id){
			$currency_id = $this->mainCurrency();
		}
		$null = null;
		$currencies = $this->getCurrencies($currency_id,$null);
		$data=$currencies[$currency_id];
		if(empty($format_override)){
			$format = $data->currency_format;
		}else{
			$format = $format_override;
		}
		$locale = $data->currency_locale;
		preg_match_all('/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?(?:#([0-9]+))?(?:\.([0-9]+))?([in%][in]?)/', $format, $matches, PREG_SET_ORDER);
		foreach ($matches as $fmatch) {
			$value = (float)$number;
			$flags = array(
				'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ? $match[1] : ' ',
				'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
				'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ? $match[0] : '+',
				'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
				'isleft'	=> preg_match('/\-/', $fmatch[1]) > 0
			);
			$width	  = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
			$left	   = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
			$conversion = $fmatch[5];
			$right	  = trim($fmatch[4]) ? (int)$fmatch[4] : $locale[($conversion[0] == 'i' ? 'int_' : '').'frac_digits'];
			$positive = true;
			if ($value < 0) {
				$positive = false;
				$value  *= -1;
			}
			$letter = $positive ? 'p' : 'n';
			$prefix = $suffix = $cprefix = $csuffix = $signal = '';
			$signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
			switch (true) {
				case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
					$prefix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
					$suffix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
					$cprefix = $signal;
					break;
				case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
					$csuffix = $signal;
					break;
				case $flags['usesignal'] == '(':
				case $locale["{$letter}_sign_posn"] == 0:
					$prefix = '(';
					$suffix = ')';
					break;
			}
			if (!$flags['nosimbol']) {
				$currency = $cprefix .
							($conversion[0] == 'i' ? $data->currency_code : $data->currency_symbol) .
							( isset($conversion[1]) ? ' '.( $conversion[1] == 'i' ? $data->currency_code : $data->currency_symbol) : '') .
							$csuffix;
			} else {
				$currency = '';
			}
			$space  = $locale["{$letter}_sep_by_space"] ? ' ' : '';
			$value = $this->numberFormat($value, $right, $locale['mon_decimal_point'],
					 $flags['nogroup'] ? '' : $locale['mon_thousands_sep'],$locale['mon_grouping']);
			$value = @explode($locale['mon_decimal_point'], $value);
			$n = strlen($prefix) + strlen($currency) + strlen($value[0]);
			if ($left > 0 && $left > $n) {
				$value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
			}
			$value = implode($locale['mon_decimal_point'], $value);
			if ($locale["{$letter}_cs_precedes"]) {
				$value = $prefix . $currency . $space . $value . $suffix;
			} else {
				$value = $prefix . $value . $space . $currency . $suffix;
			}
			if ($width > 0) {
				$value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
						 STR_PAD_RIGHT : STR_PAD_LEFT);
			}
			$format = str_replace($fmatch[0], $value, $format);
		}
		return $format;
	}
	function numberFormat  ($number  , $decimals = 2 , $dec_point = '.' , $sep = ',', $grouping=3   ){
	    $num = sprintf("%0.{$decimals}f",$number);
	    $num = explode('.',$num);
	    if(!is_array($grouping)){
	    	$grouping = array($grouping);
	    }
		$size = strlen($num[0]);
		$currentGroup = 0;
		$groups = array();
		$loop_override=0;
	    while ($size && $loop_override<5){
	    	$loop_override++;
	    	if(empty($grouping[$currentGroup])) $grouping[$currentGroup] = 3;
	    	if($size > $grouping[$currentGroup]){
	    		$groups[] = substr($num[0],-$grouping[$currentGroup]);
	    		$num[0] = substr($num[0],0,$size-($grouping[$currentGroup]));
	    		$size = strlen($num[0]);
	    		if(!empty($grouping[$currentGroup+1])) $currentGroup++;
	    	}else{
	    		$groups[] = $num[0];
	    		$size=0;
	    	}
	    }
	    $num[0] = implode($sep[0],array_reverse($groups));
	    $num[0] = trim($num[0]);
	    $num = implode($dec_point[0],$num);
	    return $num;
	}
	function checkLocale(&$element){
		if(empty($element->currency_locale)){
			$element->currency_locale =	array(
					'mon_decimal_point' => ',',
					'mon_thousands_sep' => ' ',
					'positive_sign' => '',
					'negative_sign' => '-',
					'int_frac_digits' => 2,
					'frac_digits' => 2,
					'p_cs_precedes' => 0,
					'p_sep_by_space' => 1,
					'n_cs_precedes' => 0,
					'n_sep_by_space' => 1,
					'p_sign_posn' => 1,
					'n_sign_posn' => 1,
					'mon_grouping' => array('3')
				);
		}elseif(is_string($element->currency_locale)){
			$element->currency_locale = unserialize($element->currency_locale);
			if(!empty($element->currency_locale['mon_grouping'])){
				$element->currency_locale['mon_grouping'] = explode(',',$element->currency_locale['mon_grouping']);
			}
		}
	}
}