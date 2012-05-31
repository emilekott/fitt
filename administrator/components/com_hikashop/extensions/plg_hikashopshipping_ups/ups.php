<?php
defined('_JEXEC') or die('Restricted access');
?>
<?php
class plgHikashopshippingUPS extends JPlugin
{
	var $ups_methods = array(
		array('code' => '01', 'name' => 'UPS Next Day Air', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172') , 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('code' => '02', 'name' => 'UPS Second Day Air', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('code' => '03', 'name' => 'UPS Ground', 'countries' => 'USA, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('code' => '07', 'name' => 'UPS Worldwide Express', 'countries' => 'USA, PUERTO RICO, CANADA', 'zones' => array('country_United_States_of_America_223', 'country_Puerto_Rico_172', 'country_Canada_38'), 'destinations' => array('country_United_States_of_America_223', 'country_Puerto_Rico_172', 'country_Canada_38', 'international')),
		array('code' => '08', 'name' => 'UPS Worldwide Expedited', 'countries' => 'USA, PUERTO RICO, CANADA' , 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172', 'country_Canada_38'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172', 'country_Canada_38', 'international')),
		array('code' => '11', 'name' => 'UPS Standard', 'countries' => 'USA, CANADA, POLAND, EUROPEAN UNION, OTHER', 'zones' => array('country_United_States_of_America_223', 'country_Canada_38', 'country_Poland_170', 'tax_europe_9728', 'other'), 'destinations' => array('country_United_States_of_America_223', 'country_Canada_38', 'country_Poland_170', 'tax_europe_9728', 'other')),
		array('code' => '12', 'name' => 'UPS Three-Day Select', 'countries' => 'USA, CANADA', 'zones' => array('country_United_States_of_America_223', 'country_Canada_38'), 'destinations' => array('country_United_States_of_America_223', 'country_Canada_38')),
		array('code' => '13', 'name' => 'UPS Next Day Air Saver', 'countries' => 'USA', 'zones' => array('country_United_States_of_America_223'), 'destinations' => array('country_United_States_of_America_223')),
		array('code' => '14', 'name' => 'UPS Next Day Air Early A.M.', 'countries' => 'USA, PUERTO RICO' , 'zones' => array('country_United_States_of_America_223','country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Puerto_Rico_172')),
		array('code' => '54', 'name' => 'UPS Worldwide Express Plus', 'countries' => 'USA, CANADA, POLAND, EUROPEAN UNION, OTHER, PUERTO RICO', 'zones' => array('country_United_States_of_America_223','country_Canada_38', 'country_Poland_170', 'tax_europe_9728', 'other', 'country_Puerto_Rico_172'), 'destinations' => array('country_United_States_of_America_223','country_Canada_38', 'country_Poland_170', 'tax_europe_9728', 'other', 'country_Puerto_Rico_172', 'international')),
		array('code' => '59', 'name' => 'UPS Second Day Air A.M.', 'countries' => 'USA', 'zones' => array('country_United_States_of_America_223'), 'destinations' => array('country_United_States_of_America_223')),
		array('code' => '65', 'name' => 'UPS Saver', 'countries' => 'USA, PUERTO RICO, CANADA, MEXICO, POLAND, EUROPEAN UNION, OTHER', 'zones' => array('country_United_States_of_America_223', 'country_Puerto_Rico_172', 'country_Canada_38', 'country_Mexico_138', 'country_Poland_170', 'tax_europe_9728', 'other'), 'destinations' => array('country_United_States_of_America_223', 'country_Puerto_Rico_172', 'country_Canada_38', 'country_Mexico_138', 'country_Poland_170', 'tax_europe_9728', 'other')),
		array('code' => '01', 'double' => true, 'name' => 'UPS Express CA', 'countries' => 'CANADA', 'zones' => array('country_Canada_38'), 'destinations' => array('country_Canada_38')),
		array('code' => '02', 'double' => true, 'name' => 'UPS Expedited CA', 'countries' => 'CANADA', 'zones' => array('country_Canada_38'), 'destinations' => array('country_Canada_38')),
		array('code' => '13', 'double' => true, 'name' => 'UPS Saver CA', 'countries' => 'CANADA', 'zones' => array('country_Canada_38'), 'destinations' => array('country_Canada_38')),
		array('code' => '14', 'double' => true, 'name' => 'UPS Express Early A.M', 'countries' => 'CANADA', 'zones' => array('country_Canada_38'), 'destinations' => array('country_Canada_38')),
		array('code' => '07', 'name' => 'UPS Express', 'countries' => 'MEXICO, POLAND, EUROPEAN UNION, OTHER', 'zones' => array('country_Mexico_138', 'country_Poland_170','tax_europe_9728', 'other'), 'destinations' => array('country_Mexico_138', 'country_Poland_170','tax_europe_9728', 'other')),
		array('code' => '08', 'name' => 'UPS Expedited', 'countries' => 'MEXICO, POLAND, EUROPEAN UNION, OTHER', 'zones' => array('country_Mexico_138', 'country_Poland_170','tax_europe_9728', 'other'), 'destinations' => array('country_Mexico_138', 'country_Poland_170','tax_europe_9728', 'other')),
		array('code' => '54', 'name' => 'UPS Express Plus', 'countries' => 'MEXICO', 'zones' => array('country_Mexico_138'), 'destinations' => array('country_Mexico_138')),
		array('code' => '82', 'name' => 'UPS Today Standard', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('code' => '83', 'name' => 'UPS Today Dedicated Courrier', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('code' => '84', 'name' => 'UPS Today Intercity', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('code' => '85', 'name' => 'UPS Today Express', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('code' => '86', 'name' => 'UPS Today Express Saver', 'countries' => 'POLAND', 'zones' => array('country_Poland_170'), 'destinations' => array('country_Poland_170')),
		array('code' => 'TDCB', 'name' => 'Trade Direct Cross Border', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('code' => 'TDA', 'name' => 'Trade Direct Air', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('code' => 'TDO', 'name' => 'Trade Direct Ocean', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('code' => '308', 'name' => 'UPS Freight LTL', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('code' => '309', 'name' => 'UPS Freight LTL Guaranteed', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
		array('code' => '310', 'name' => 'UPS Freight LTL Urgent', 'countries' => 'ALL', 'zones' => array('all'), 'destinations' => array('international')),
	  );
	  var $convertUnit=array(
		'kg' => 'KGS',
		'lb' => 'LBS',
		'cm' => 'CM',
		'in' => 'IN',
		'kg2' => 'kg',
		'lb2' => 'lb',
		'cm2' => 'cm',
		'in2' => 'in',
	  );
	function shippingMethods(&$main){
    	$methods = array();
    	if(!empty($main->shipping_params->methodsList)){
			$main->shipping_params->methods=unserialize($main->shipping_params->methodsList);
		}
    	if(!empty($main->shipping_params->methods)){
			foreach($main->shipping_params->methods as $method){
				$selected = null;
				foreach($this->ups_methods as $ups){
					if($ups['code']==$method) $selected = $ups;
				}
				if($selected){
					$methods[$method]=$selected['name'];
				}
			}
		}
    	return $methods;
    }
	function onShippingDisplay(&$order,&$dbrates,&$usable_rates,&$messages){
		if(empty($dbrates)){
    		$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
    	}else{
    		$rates = array();
    		foreach($dbrates as $k => $rate){
	    		if($rate->shipping_type=='ups')
	    			$rates[]=$rate;
    		}
    		if(empty($rates)){
				$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
				return true;
			}
			$found = true;
    		if(bccomp($order->total->prices[0]->price_value,0,5)){
				$usableWarehouses = array();
    			$zoneClass=hikashop_get('class.zone');
    			$zones = $zoneClass->getOrderZones($order);
    			foreach($rates as $k => $rate){
    				if(!empty($rate->shipping_params->warehousesList)){
	    				$rate->shipping_params->warehouses=unserialize($rate->shipping_params->warehousesList);
    				}
    				else{
    					$messages['no_wharehouse_configured'] = 'No wharehouse configured in the UPS shipping plugin options';
    					return true;
    				}
					foreach($rate->shipping_params->warehouses as $warehouse){
						if(empty($warehouse->zone) || in_array($warehouse->zone,$zones)){
							$usableWarehouses[]=$warehouse;
						}
					}
					if(empty($usableWarehouses)){
						$messages['no_wharehouse_configured'] = 'No available wharehouse found for your location';
						return true;
					}
					if(!empty($rate->shipping_params->methodsList)){
	    				$rate->shipping_params->methods=unserialize($rate->shipping_params->methodsList);
    				}
    				else{
    					$messages['no_shipping_methods_configured'] = 'No shipping methods configured in the UPS shipping plugin options';
    					return true;
    				}
    				if($order->weight<=0 || $order->volume<=0){
    					return true;
    				}
    			}
    			$this->freight=false;
    			$this->classicMethod=false;
    			$heavyProduct=false;
    			$weightTotal=0;
    			if(!empty($rate->shipping_params->methods)){
	    			foreach($rate->shipping_params->methods as $method){
						if($method=='TDCB' || $method=='TDA' || $method=='TDO' || $method=='308' || $method=='309' || $method=='310'){
							$this->freight=true;
						}
						else{
							$this->classicMethod=true;
						}
					}
    			}
				$data=null;
				if(empty($order->shipping_address)){
					return true;
				}
				$this->shipping_currency_id=$currency= hikashop_getCurrency();
				$db = &JFactory::getDBO();
				$query='SELECT currency_code FROM '.hikashop_table('currency').' WHERE currency_id IN ('.$this->shipping_currency_id.')';
				$db->setQuery($query);
				$this->shipping_currency_code = $db->loadResult();
				$cart = hikashop_get('class.cart');
				$null = null;
				$cart->loadAddress($null,$order->shipping_address->address_id,'object', 'shipping');
				$currency = hikashop_get('class.currency');
				$config =& hikashop_config();
				$this->main_currency = $config->get('main_currency',1);
				if(empty($rate->shipping_params->handling_fees)){
					$rate->shipping_params->handling_fees=0;
				}
				if($this->shipping_currency_id==$this->main_currency){
					$price=$order->total->prices[0]->price_value_with_tax;
					$handlingFees=$rate->shipping_params->handling_fees;
				}else{
					$currencyClass = hikashop_get('class.currency');
					$price=$currencyClass->convertUniquePrice($order->total->prices[0]->price_value_with_tax,$this->shipping_currency_id, $this->main_currency);
					$handlingFees=$currencyClass->convertUniquePrice($rate->shipping_params->handling_fees, $this->main_currency, $this->shipping_currency_id);
				}
				if(empty($rate->shipping_params->shipping_min_price)){
					$rate->shipping_params->shipping_min_price=0;
				}else{
					if($price<$rate->shipping_params->shipping_min_price){
						$messages['order_total_too_low'] = JText::_('ORDER_TOTAL_TOO_LOW_FOR_SHIPPING_METHODS');
						return true;
					}
				}
				if(empty($rate->shipping_params->shipping_max_price)){
					$rate->shipping_params->shipping_max_price=0;
				}else{
					if($price>$rate->shipping_params->shipping_max_price){
						$messages['order_total_too_higth'] = JText::_('ORDER_TOTAL_TOO_HIGH_FOR_SHIPPING_METHODS');
						return true;
					}
				}
				foreach($rates as $rate){
					$receivedMethods=$this->_getBestMethods($rate, $order, $usableWarehouses, $heavyProduct, $null);
				}
				if(empty($receivedMethods)){
					$messages['no_rates'] = JText::_('NO_SHIPPING_METHOD_FOUND');
					return true;
				}
				$i=0;
				$rate=(PHP_VERSION < 5) ? $rates[0] : clone($rates[0]);
				foreach($receivedMethods as $method){
					$usableMethods[]=$method;
					$rates[$i]=(PHP_VERSION < 5) ? $rate : clone($rate);
					$rates[$i]->shipping_price=0.0;
					if(!empty($rate->shipping_params->handling_fees_percent)){
						$rates[$i]->shipping_price+=$order->total->prices[0]->price_value_with_tax*($rate->shipping_params->handling_fees_percent/100);
					}
					$rates[$i]->shipping_price+=$method['value']+$handlingFees;
					foreach($this->ups_methods as $ups_method){
						if($method['old_currency_code']=='CAD'){
							if($ups_method['code']== $method['code']){
								$name= $ups_method['name'];
							}
						}else{
							if($ups_method['code']== $method['code'] && !isset($ups_method['double'])){
								$name= $ups_method['name'];
							}
						}
					}
					$rates[$i]->shipping_name=$name;
					$rates[$i]->shipping_id=$name;
					if($method['delivery_day']!=-1){
						$rates[$i]->shipping_description.=' '.JText::sprintf( 'ESTIMATED_TIME_AFTER_SEND', $method['delivery_day']);
					}else{
						$rates[$i]->shipping_description.=' '.JText::_( 'NO_ESTIMATED_TIME_AFTER_SEND');
					}
					if($method['delivery_time']!=-1){
						$rates[$i]->shipping_description.='<br/>'.JText::sprintf( 'DELIVERY_HOUR', $method['delivery_time']);
					}else{
						$rates[$i]->shipping_description.='<br/>'.JText::_( 'NO_DELIVERY_HOUR');
					}
					$i++;
				}
				foreach($rates as $i => $rate){
					$usable_rates[]=$rate;
				}
			}
		}
	 }
	function onShippingConfiguration(&$elements){
		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currencyClass = hikashop_get('class.currency');
		$currency = hikashop_getCurrency();
		$this->ups = JRequest::getCmd('name','ups');
		$this->currency = hikashop_get('type.currency');
		$this->categoryType = hikashop_get('type.categorysub');
		$this->categoryType->type = 'tax';
		$this->categoryType->field = 'category_id';
		$bar = & JToolBar::getInstance('toolbar');
    	if(empty($elements)){
			$element = null;
    		$element->shipping_name='UPS';
    		$element->shipping_description='';
    		$element->group_package=0;
    		$element->shipping_images='ups';
    		$element->shipping_type=$this->ups;
    		$element->shipping_params=null;
			$element->shipping_params->post_code='';
			$element->shipping_currency_id = $this->main_currency;
			$element->shipping_params->pickup_type='01';
			$element->shipping_params->destination_type='auto';
    		$elements = array($element);
    	}
		JToolBarHelper::save();
		JToolBarHelper::apply();
		$bar->appendButton( 'Link', 'cancel', JText::_('HIKA_CANCEL'), hikashop_completeLink('plugins&plugin_type=shipping') );
		JToolBarHelper::divider();
		$bar->appendButton( 'Pophelp','shipping-'.$this->ups.'-form');
		hikashop_setTitle(JText::_('HIKASHOP_SHIPPING_METHOD'),'plugin','plugins&plugin_type=shipping&task=edit&name='.$this->ups);
		$config =& hikashop_config();
	 	$this->main_currency = $config->get('main_currency',1);
	 	$currency = hikashop_get('class.currency');
	 	$this->currency = $currency->get($this->main_currency);
		$key = key($elements);
		if(!empty($elements[$key]->shipping_params->warehousesList)){
			$elements[$key]->shipping_params->warehouse = unserialize($elements[$key]->shipping_params->warehousesList);
		}
		if(!empty($elements[$key]->shipping_params->methodsList)){
			$elements[$key]->shipping_params->methods = unserialize($elements[$key]->shipping_params->methodsList);
		}
		$js = '
		function deleteRow(divName,inputName,rowName){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display=\'none\';
			}
			return false;
		}
		function deleteZone(zoneName){
			var d = document.getElementById(zoneName);
			if(d){
				d.innerHTML="";
			}
			return false;
		}
		';
		$doc =& JFactory::getDocument();
	 	$doc->addScriptDeclaration($js);
	}
	function onShippingConfigurationSave(&$elements){
		$warehouses = JRequest::getVar( 'warehouse', array(), '', 'array' );
		$cats = array();
		$methods=array();
		$db = &JFactory::getDBO();
		$zone_keys='';
		if(isset($_REQUEST['data']['shipping_methods'])){
			foreach($_REQUEST['data']['shipping_methods'] as $method){
				foreach($this->ups_methods as $upsMethod){
					$name=strtolower($upsMethod['name']);
					$name=str_replace(' ','_', $name);
					if($name==$method['name']){
						$obj = null;
						$methods[strip_tags($method['name'])]=strip_tags($upsMethod['code']);
					}
				}
			}
		}
		$elements->shipping_params->methodsList = serialize($methods);
		if(!empty($warehouses)){
			foreach($warehouses as $id => $warehouse){
				if(!empty($warehouse['zone']))
					$zone_keys.='zone_namekey='.$db->Quote($warehouse['zone']).' OR ';
			}
			$zone_keys=substr($zone_keys,0,-4);
			if(!empty($zone_keys)){
				$query=' SELECT zone_namekey, zone_id, zone_name_english FROM '.hikashop_table('zone').' WHERE '.$zone_keys;
				$db->setQuery($query);
				$zones = $db->loadObjectList();
			}
			foreach($warehouses as $id => $warehouse){
				$warehouse['zone_name']='';
				if(!empty($zones)){
					foreach($zones as $zone){
						if($zone->zone_namekey==$warehouse['zone'])
							$warehouse['zone_name']=$zone->zone_id.' '.$zone->zone_name_english;
					}
				}
				if(empty($_REQUEST['warehouse'][$id]['zip'])){
					$_REQUEST['warehouse'][$id]['zip']='-';
				}
				if(@$_REQUEST['warehouse'][$id]['zip']!='-'){
					$obj = null;
					$obj->name = strip_tags($_REQUEST['warehouse'][$id]['name']);
					$obj->zip = strip_tags($_REQUEST['warehouse'][$id]['zip']);
					$obj->country = strip_tags($_REQUEST['warehouse'][$id]['country']);
					$obj->zone = strip_tags($_REQUEST['warehouse'][$id]['zone']);
					$obj->zone_name = $warehouse['zone_name'];
					$obj->units = strip_tags($_REQUEST['warehouse'][$id]['units']);
					$obj->currency = strip_tags($_REQUEST['warehouse'][$id]['currency']);
					$cats[]=$obj;
				}
			}
			$elements->shipping_params->warehousesList = serialize($cats);
		}
		if(empty($cats)){
			$obj->name = '-';
			$obj->zip = '-';
			$obj->country = '-';
			$obj->zone = '-';
			$void[]=$obj;
			$elements->shipping_params->warehousesList = serialize($void);
		}
    	return true;
    }
  	function _getBestMethods(&$rate, &$order, &$usableWarehouses, $heavyProduct, $null){
  		$db = &JFactory::getDBO();
		$usableMethods=array();
		$zone_code='';
		$freight=false;
		$classicMethod=false;
		foreach($rate->shipping_params->methods as $method){
			if($method=='TDCB' || $method=='TDA' || $method=='TDO' || $method=='308' || $method=='309' || $method=='310'){
				$this->freight=true;
			}
			else{
				$this->classicMethod=true;
			}
		}
		$currencies=array();
		foreach($usableWarehouses as $warehouse){
			$zone_code.=$warehouse->country.',';
			$currencies[$warehouse->currency]=(int)$warehouse->currency;
		}
		$zone_code=substr($zone_code,0,-1);
		$query='SELECT zone_id, zone_code_2 FROM '.hikashop_table('zone').' WHERE zone_id IN ('.$zone_code.')';
		$db->setQuery($query);
		$warehouses_namekey = $db->loadObjectList();
		foreach($usableWarehouses as $warehouse){
			foreach($warehouses_namekey as $zone){
				if($zone->zone_id==$warehouse->country){
					$warehouse->country_ID=$zone->zone_code_2;
				}
			}
		}
		$query='SELECT currency_code, currency_id FROM '.hikashop_table('currency').' WHERE currency_id IN ('.implode(',',$currencies).')';
		$db->setQuery($query);
		$warehouses_currency_code = $db->loadObjectList();
		foreach($usableWarehouses as $k => $warehouse){
			foreach($warehouses_currency_code as $currency_code){
				if($warehouse->currency==$currency_code->currency_id){
					$usableWarehouses[$k]->currency_code=$currency_code->currency_code;
				}
			}
		}
		foreach($usableWarehouses as $k => $warehouse){
			$usableWarehouses[$k]->methods=$this->_getShippingMethods($rate, $order, $warehouse, $heavyProduct, $null);
		}
		if(empty($usableWarehouses)){
			return false;
		}
		foreach($usableWarehouses as $k => $warehouse){
			if(!empty($warehouse->methods)){
				foreach($warehouse->methods as $i => $method){
					if(!in_array($method['code'], $rate->shipping_params->methods)){
						unset($usableWarehouses[$k]->methods[$i]);
					}
				}
			}
		}
		$bestPrice=99999999;
		foreach($usableWarehouses as $id => $warehouse){
			if(!empty($warehouse->methods)){
				foreach($warehouse->methods as $method){
					if($method['value']<$bestPrice){
						$bestPrice=$method['value'];
						$bestWarehouse=$id;
					}
				}
			}
		}
		if(isset($bestWarehouse)){
			return $usableWarehouses[$bestWarehouse]->methods;
		}else{
			return false;
		}
  	}
	function _getShippingMethods(&$rate, &$order, &$warehouse, $heavyProduct, $null){
		$data['userId']=$rate->shipping_params->user_id;
		$data['accessLicenseNumber']=$rate->shipping_params->access_code;
		$data['password']=$rate->shipping_params->password;
		$data['destZip']=$null->shipping_address->address_post_code;
		$data['destCountry']=$null->shipping_address->address_country->zone_code_2;
		$data['zip']=$warehouse->zip;
		$data['country']=$warehouse->country_ID;
		$data['units']=$warehouse->units;
		$data['currency']=$warehouse->currency;
		$data['currency_code']=$warehouse->currency_code;
		$data['old_currency']=$warehouse->currency;
		$data['old_currency_code']=$warehouse->currency_code;
		$data['shipperNumber']=$rate->shipping_params->shipper_number;
		$data['XMLpackage']='';
		$data['destType']='';
		if($rate->shipping_params->destination_type=='res'){
			$data['destType']='<ResidentialAddressIndicator/>';
		}
		if($rate->shipping_params->destination_type=='auto' && !isset($order->shipping_address->address_company)){
			$data['destType']='<ResidentialAddressIndicator/>';
		}
		$data['pickup_type']=$rate->shipping_params->pickup_type;
		$totalPrice=0;
		if(($this->freight==true && $this->classicMethod==false) || ($heavyProduct==true && $this->freight==true)){
			$data['weight']=0;
			$data['height']=0;
			$data['length']=0;
			$data['width']=0;
			$data['price']=0;
			foreach($order->products as $product){
				if($product->product_parent_id==0){
					if(isset($product->variants)){
						foreach($product->variants as $variant){
							$caracs=$this->_convertCharacteristics($variant, $data);
							$data['weight_unit']=$caracs['weight_unit'];
							$data['dimension_unit']=$caracs['dimension_unit'];
							$data['weight']+=round($caracs['weight'],2)*$variant->cart_product_quantity;
							$data['height']+=round($caracs['height'],2)*$variant->cart_product_quantity;
							$data['length']+=round($caracs['length'],2)*$variant->cart_product_quantity;
							$data['width']+=round($caracs['width'],2)*$variant->cart_product_quantity;
							$data['price']+=$variant->prices[0]->unit_price->price_value_with_tax*$variant->cart_product_quantity;
						}
					}
					else{
						$caracs=$this->_convertCharacteristics($product,$data);
						$data['weight_unit']=$caracs['weight_unit'];
						$data['dimension_unit']=$caracs['dimension_unit'];
						$data['weight']+=round($caracs['weight'],2)*$product->cart_product_quantity;
						$data['height']+=round($caracs['height'],2)*$product->cart_product_quantity;
						$data['length']+=round($caracs['length'],2)*$product->cart_product_quantity;
						$data['width']+=round($caracs['width'],2)*$product->cart_product_quantity;
						$data['price']+=$product->prices[0]->unit_price->price_value_with_tax*$product->cart_product_quantity;
					}
				}
			}
			$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order );
			$usableMethods=$this->_UPSrequestMethods($data);
			return $usableMethods;
		}
		if($rate->shipping_params->group_package){
			$data['weight']=0;
			$data['height']=0;
			$data['length']=0;
			$data['width']=0;
			$data['price']=0;
			foreach($order->products as $product){
				if($product->product_parent_id==0){
					if(isset($product->variants)){
						foreach($product->variants as $variant){
							for($i=0;$i<$variant->cart_product_quantity;$i++){
								$caracs=$this->_convertCharacteristics($variant, $data);
								$data['weight_unit']=$caracs['weight_unit'];
								$data['dimension_unit']=$caracs['dimension_unit'];
								$tmpHeight=$data['height']+round($caracs['height'],2);
								$tmpLength=$data['length']+round($caracs['length'],2);
								$tmpWidth=$data['width']+round($caracs['width'],2);
								$dim=$tmpLength+2*$tmpWidth+2*$tmpHeight;
								if($data['weight']+round($caracs['weight'],2)>150 || $dim>165){
									$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order );
									$data['weight']=round($caracs['weight'],2);
									$data['height']=round($caracs['height'],2);
									$data['length']=round($caracs['length'],2);
									$data['width']=round($caracs['width'],2);
									$data['price']=$variant->prices[0]->unit_price->price_value_with_tax;
								}
								else{
									$data['weight']+=round($caracs['weight'],2);
									$data['height']+=round($caracs['height'],2);
									$data['length']+=round($caracs['length'],2);
									$data['width']+=round($caracs['width'],2);
									$data['price']+=$variant->prices[0]->unit_price->price_value_with_tax;
								}
							}
						}
					}
					else{
						for($i=0;$i<$product->cart_product_quantity;$i++){
							$caracs=$this->_convertCharacteristics($product, $data);
							$data['weight_unit']=$caracs['weight_unit'];
							$data['dimension_unit']=$caracs['dimension_unit'];
							$tmpHeight=$data['height']+round($caracs['height'],2);
							$tmpLength=$data['length']+round($caracs['length'],2);
							$tmpWidth=$data['width']+round($caracs['width'],2);
							$dim=$tmpLength+2*$tmpWidth+2*$tmpHeight;
							if($data['weight']+round($caracs['weight'],2)>150 || $dim>165){
								$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order );
								$data['weight']=round($caracs['weight'],2);
								$data['height']=round($caracs['height'],2);
								$data['length']=round($caracs['length'],2);
								$data['width']=round($caracs['width'],2);
								$data['price']=$product->prices[0]->unit_price->price_value_with_tax;
							}
							else{
								$data['weight']+=round($caracs['weight'],2);
								$data['height']+=round($caracs['height'],2);
								$data['length']+=round($caracs['length'],2);
								$data['width']+=round($caracs['width'],2);
								$data['price']+=$product->prices[0]->unit_price->price_value_with_tax;
							}
						}
					}
				}
			}
			$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order);
			$usableMethods=$this->_UPSrequestMethods($data);
		}
		else{
			foreach($order->products as $product){
				if($product->product_parent_id==0){
					if(isset($product->variants)){
						foreach($product->variants as $variant){
							for($i=0;$i<$variant->cart_product_quantity;$i++){
								$data['XMLpackage'].=$this->_createPackage($data, $variant, $rate, $order, true);
							}
						}
					}
					else{
						for($i=0;$i<$product->cart_product_quantity;$i++){
							$data['XMLpackage'].=$this->_createPackage($data, $product, $rate, $order, true );
						}
					}
				}
			}
			$usableMethods=$this->_UPSrequestMethods($data);
		}
		if(empty($usableMethods)){
			return false;
		}
		$currencies=array();
		foreach($usableMethods as $method){
			$currencies[$method['currency_code']]='"'.$method['currency_code'].'"';
		}
		$db = &JFactory::getDBO();
		$query='SELECT currency_code, currency_id FROM '.hikashop_table('currency').' WHERE currency_code IN ('.implode(',',$currencies).')';
		$db->setQuery($query);
		$currencyList = $db->loadObjectList();
		$currencyList=reset($currencyList);
		foreach($usableMethods as $i => $method){
			$usableMethods[$i]['currency_id']=$currencyList->currency_id;
		}
		$usableMethods=$this->_currencyConversion($usableMethods, $order);
		return $usableMethods;
  	}
  	function _createPackage(&$data, &$product, &$rate, &$order, $includeDimension=false){
		if(empty($data['weight'])){
			$caracs=$this->_convertCharacteristics($product, $data);
			$data['weight_unit']=$caracs['weight_unit'];
			$data['dimension_unit']=$caracs['dimension_unit'];
			$data['weight']=round($caracs['weight'],2);
			$data['height']=round($caracs['height'],2);
			$data['length']=round($caracs['length'],2);
			$data['width']=round($caracs['width'],2);
		}
		$currencyClass=hikashop_get('class.currency');
		$config =& hikashop_config();
		$this->main_currency = $config->get('main_currency',1);
		$currency = hikashop_getCurrency();
		if(isset($data['price'])){
			$price=$data['price'];
		}
		else{
			$price=$product->prices[0]->unit_price->price_value;
		}
		if($this->shipping_currency_id!=$data['currency']){
			$price=$currencyClass->convertUniquePrice($price, $this->shipping_currency_id,$data['currency']);
		}
		if(!empty($rate->shipping_params->weight_approximation)){
			$data['weight']=$data['weight']+$data['weight']*$rate->shipping_params->weight_approximation/100;
		}
		if($data['weight']<1){
			$data['weight']=1;
		}
		if(!empty($rate->shipping_params->dim_approximation)){
			$data['height']=$data['height']+$data['height']*$rate->shipping_params->dim_approximation/100;
			$data['length']=$data['length']+$data['length']*$rate->shipping_params->dim_approximation/100;
			$data['width']=$data['width']+$data['width']*$rate->shipping_params->dim_approximation/100;
		}
		$options='';
		$dimension='';
		if($rate->shipping_params->include_price){
			$options='<PackageServiceOptions>
						<InsuredValue>
							<CurrencyCode>'.$data['currency_code'].'</CurrencyCode>
							<MonetaryValue>'.$price.'</MonetaryValue>
						</InsuredValue>
					</PackageServiceOptions>';
		}
		if($includeDimension){
			$dimension='<Dimensions>
		            		<UnitOfMeasurement>
		                		<Code>'.$data['dimension_unit'].'</Code>
		            		</UnitOfMeasurement>
		            		<Length>'.$data['length'].'</Length>
		            		<Width>'.$data['width'].'</Width>
		            		<Height>'.$data['height'].'</Height>
		        		</Dimensions>';
		}
		$xml='<Package>
				<PackagingType>
					<Code>02</Code>
				</PackagingType>
				<Description>Shop</Description>
				'.$dimension.'
				<PackageWeight>
					<UnitOfMeasurement>
						<Code>'.$data['weight_unit'].'</Code>
					</UnitOfMeasurement>
					<Weight>'.$data['weight'].'</Weight>
				</PackageWeight>
				'.$options.'
			</Package>';
		return $xml;
  	}
	function _convertCharacteristics(&$product, $data, $forceUnit=false){
		$weightClass=hikashop_get('helper.weight');
		$volumeClass=hikashop_get('helper.volume');
		if($forceUnit){
			$carac['weight']=$weightClass->convert($product->product_weight_orig, $product->product_weight_unit, 'lb');
			$carac['weight_unit']='LBS';
			$carac['height']=$volumeClass->convert($product->product_height, $product->product_dimension_unit, 'in' , 'dimension');
			$carac['length']=$volumeClass->convert($product->product_length, $product->product_dimension_unit, 'in', 'dimension' );
			$carac['width']=$volumeClass->convert($product->product_width, $product->product_dimension_unit, 'in', 'dimension' );
			$carac['dimension_unit']='IN';
			return $carac;
		}
		if($data['units']=='kg'){
			if($product->product_weight_unit=='kg'){
				$carac['weight']=$product->product_weight_orig;
				$carac['weight_unit']=$this->convertUnit[$product->product_weight_unit];
			}else{
				$carac['weight']=$weightClass->convert($product->product_weight_orig, $product->product_weight_unit, 'kg');
				$carac['weight_unit']='KGS';
			}
			if($product->product_dimension_unit=='cm'){
				$carac['height']=$product->product_height;
				$carac['length']=$product->product_length;
				$carac['width']=$product->product_width;
				$carac['dimension_unit']=$this->convertUnit[$product->product_dimension_unit];
			}else{
				$carac['height']=$volumeClass->convert($product->product_height, $product->product_dimension_unit, 'cm' , 'dimension');
				$carac['length']=$volumeClass->convert($product->product_length, $product->product_dimension_unit, 'cm', 'dimension' );
				$carac['width']=$volumeClass->convert($product->product_width, $product->product_dimension_unit, 'cm', 'dimension' );
				$carac['dimension_unit']='CM';
			}
		}else{
			if($product->product_weight_unit=='lb'){
				$carac['weight']=$product->product_weight_orig;
				$carac['weight_unit']=$this->convertUnit[$product->product_weight_unit];
			}else{
				$carac['weight']=$weightClass->convert($product->product_weight_orig, $product->product_weight_unit, 'lb');
				$carac['weight_unit']='LBS';
			}
			if($product->product_dimension_unit=='in'){
				$carac['height']=$product->product_height;
				$carac['length']=$product->product_length;
				$carac['width']=$product->product_width;
				$carac['dimension_unit']=$this->convertUnit[$product->product_dimension_unit];
			}else{
				$carac['height']=$volumeClass->convert($product->product_height, $product->product_dimension_unit, 'in' , 'dimension');
				$carac['length']=$volumeClass->convert($product->product_length, $product->product_dimension_unit, 'in', 'dimension' );
				$carac['width']=$volumeClass->convert($product->product_width, $product->product_dimension_unit, 'in', 'dimension' );
				$carac['dimension_unit']='IN';
			}
		}
		return $carac;
	}
	function _UPSrequestMethods($data){
	        $xml='<?xml version="1.0" ?>
				<AccessRequest xml:lang=\'en-US\'>
					<AccessLicenseNumber>'.$data['accessLicenseNumber'].'</AccessLicenseNumber>
					<UserId>'.$data['userId'].'</UserId>
					<Password>'.$data['password'].'</Password>
				</AccessRequest>
				<?xml version="1.0" ?>
				<RatingServiceSelectionRequest>
					<Request>
						<TransactionReference>
							<CustomerContext>Rating and Service</CustomerContext>
							<XpciVersion>1.0</XpciVersion>
						</TransactionReference>
						<RequestAction>Rate</RequestAction>
						<RequestOption>shop</RequestOption>
					</Request>
					<PickupType>
						<Code>'.$data['pickup_type'].'</Code>
						<Description>Daily Pickup</Description>
					</PickupType>
					<Shipment>
						<Description>Rate Shopping - Domestic</Description>
						<Shipper>
							<ShipperNumber>'.$data['shipperNumber'].'</ShipperNumber>
							<Address>
								<City>Maiden</City>
								<PostalCode>'.$data['zip'].'</PostalCode>
								<CountryCode>'.$data['country'].'</CountryCode>
							</Address>
						</Shipper>
						<ShipTo>
							<Address>
								<City>MECHANICSVILLE</City>
								<PostalCode>'.$data['destZip'].'</PostalCode>
								<CountryCode>'.$data['destCountry'].'</CountryCode>
								'.$data['destType'].'
							</Address>
						</ShipTo>
					<ShipFrom>
						<Address>
							<City>Maiden</City>
							<PostalCode>'.$data['zip'].'</PostalCode>
							<CountryCode>'.$data['country'].'</CountryCode>
						</Address>
					</ShipFrom>
					'.$data['XMLpackage'].'
					<ShipmentServiceOptions />
				</Shipment>
			</RatingServiceSelectionRequest>';
        $ch = curl_init("https://www.ups.com/ups.app/xml/Rate");
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_TIMEOUT, 60);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);
        $result=curl_exec ($ch);
        $xml = strstr($result, '<?');
        $xml_parser = xml_parser_create();
        $vals =  null;
        $index = null;
        xml_parse_into_struct($xml_parser, $xml, $vals, $index);
        xml_parser_free($xml_parser);
        $params = array();
        $level = array();
        $shipmentNumber=0; $errorNumber=0;
        foreach ($vals as $xml_elem) {
			if ($xml_elem['type'] == 'open') {
				if (array_key_exists('attributes',$xml_elem)) {
					 list($level[$xml_elem['level']],$extra) = array_values($xml_elem['attributes']);
				} else {
					 if($xml_elem['tag']=='ERROR'){
					 	$errorNumber++;
					 }
					 if($xml_elem['tag']=='RATEDSHIPMENT'){
					 	$shipmentNumber++;
					 	$xml_elem['tag']= $xml_elem['tag'].'_'.$shipmentNumber;
					 	$level[$xml_elem['level']] = $xml_elem['tag'];
					 }
					 $level[$xml_elem['level']] = $xml_elem['tag'];
				}
			}
			if ($xml_elem['type'] == 'complete') {
				if(empty($xml_elem['value'])){
					$xml_elem['value']=-1;
				}
				$start_level = 1;
				$php_stmt = '$params';
				while($start_level < $xml_elem['level']) {
					 $php_stmt .= '[$level['.$start_level.']]';
					 $start_level++;
				}
				$php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
				eval($php_stmt);
			}
        }
        curl_close($ch);
        $shipment= array();
        for($i=1;$i<=$shipmentNumber;$i++){
        	$shipment[$i]['value']=$params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT_'.$i]['TOTALCHARGES']['MONETARYVALUE'];
        	$shipment[$i]['code']=$params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT_'.$i]['SERVICE']['CODE'];
        	$shipment[$i]['delivery_day']=$params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT_'.$i]['GUARANTEEDDAYSTODELIVERY'];
        	$shipment[$i]['delivery_time']=$params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT_'.$i]['SCHEDULEDDELIVERYTIME'];
        	$shipment[$i]['currency_code']=$params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT_'.$i]['TOTALCHARGES']['CURRENCYCODE'];
        	$shipment[$i]['old_currency_code']=$params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT_'.$i]['TOTALCHARGES']['CURRENCYCODE'];
        }
        $error=false;
        for($i=0;$i<$errorNumber;$i++){
        	$error=true;
        	$shipment[$i]['return']=$params['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['RESPONSESTATUSCODE'];
        	if($shipment[$i]['return']=="-1"){
        		$app =& JFactory::getApplication();
        		$shipment[$i]['err_message']=$params['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['ERROR']['ERRORDESCRIPTION'];
        		$shipment[$i]['err_code']=$params['RATINGSERVICESELECTIONRESPONSE']['RESPONSE']['ERROR']['ERRORCODE'];
        		if($shipment[$i]['err_code']<=111056 && $shipment[$i]['err_code']>=111050){
					$messages['items_volume_over_limit'] = JText::_('ITEMS_VOLUME_TOO_BIG_FOR_SHIPPING_METHODS');
        		}else{
					$app->enqueueMessage( 'Error while sending XML to UPS. Error code: '.$shipment[$i]['err_code'].'. Message: '.$shipment[$i]['err_message'].'', 'error');
        		}
        	}
        }
        if($error){
        	return false;
        }
        return $shipment;
	}
	function _currencyConversion(&$usableMethods, &$order){
		$currency= $this->shipping_currency_id;
		$currencyClass = hikashop_get('class.currency');
		foreach($usableMethods as $i => $method){
			if($method['currency_id']!=$currency){
				$usableMethods[$i]['value']=$currencyClass->convertUniquePrice($method['value'],$method['currency_id'], $currency);
				$usableMethods[$i]['old_currency_id']=$usableMethods[$i]['currency_id'];
				$usableMethods[$i]['old_currency_code']=$usableMethods[$i]['currency_code'];
				$usableMethods[$i]['currency_id']=$currency;
				$usableMethods[$i]['currency_code']=$this->shipping_currency_code;
			}
		}
		return $usableMethods;
	}
	function onShippingSave(&$cart,&$methods,&$shipping_id){
		$usable_mehtods = array();
    	$errors = array();
    	$this->onShippingDisplay($cart,$methods,$usable_mehtods,$errors);
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