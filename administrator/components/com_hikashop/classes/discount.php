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
class hikashopDiscountClass extends hikashopClass{
	var $tables = array('discount');
	var $pkeys = array('discount_id');
	var $namekeys = array('');
	var $toggle = array('discount_published'=>'discount_id');
	function saveForm(){
		$discount = null;
		$discount->discount_id = hikashop_getCID('discount_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		foreach($formData['discount'] as $column => $value){
			hikashop_secureField($column);
			$discount->$column = strip_tags($value);
		}
		if(!empty($discount->discount_start)){
			$discount->discount_start=hikashop_getTime($discount->discount_start);
		}
		if(!empty($discount->discount_end)){
			$discount->discount_end=hikashop_getTime($discount->discount_end);
		}
		$status = $this->save($discount);
		return $status;
	}
	function save(&$discount){
		if(empty($discount->discount_id)){
			if(empty($discount->discount_type) || ($discount->discount_type=='coupon' && empty($discount->discount_code))){
				return false;
			}
		}
		$status = parent::save($discount);
		return $status;
	}
	function load($coupon){
		static $coupons = array();
		if(!isset($coupons[$coupon])){
			$filters = array('discount_code='.$this->database->Quote($coupon),'discount_type=\'coupon\'','discount_published=1');
			$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE '.implode(' AND ',$filters);
			$this->database->setQuery($query);
			$coupons[$coupon] = $this->database->loadObject();
		}
		return $coupons[$coupon];
	}
	function loadAndCheck($coupon_code,&$total,$zones,&$products,$display_error=true){
		$coupon = $this->load($coupon_code);
		return $this->check($coupon,$total,$zones,$products,$display_error);
	}
	function check(&$coupon,&$total,$zones,&$products,$display_error=true){
		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher =& JDispatcher::getInstance();
		$error_message = '';
		$do=true;
		$dispatcher->trigger( 'onBeforeCouponCheck', array( &$coupon,&$total,&$zones,&$products,&$display_error, &$error_message, & $do) );
		if($do){
			$user = hikashop_get('class.user');
			$currency = hikashop_get('class.currency');
			if(empty($coupon)){
				$error_message = JText::_('COUPON_NOT_VALID');
			}elseif($coupon->discount_start>time()){
				$error_message = JText::_('COUPON_NOT_YET_USABLE');
			}elseif($coupon->discount_end && $coupon->discount_end<time()){
				$error_message = JText::_('COUPON_EXPIRED');
			}else{
				if(hikashop_level(2)){
					if(!empty($coupon->discount_access)){
						$my =& JFactory::getUser();
						if($coupon->discount_access != 'all' AND ($coupon->discount_access == 'none' OR empty($my->id) OR !hikashop_isAllowed($coupon->discount_access))){
				    		$error_message = JText::_('COUPON_NOT_FOR_YOU');
				    	}
					}
				}
				if(empty($error_message) && hikashop_level(1)){
					if(!empty($coupon->discount_quota) && $coupon->discount_quota<=$coupon->discount_used_times){
						$error_message = JText::_('QUOTA_REACHED_FOR_COUPON');
					}else{
						if(!empty($coupon->discount_quota_per_user)){
							$user_id = hikashop_loadUser();
							if($user_id){
								$db =& JFactory::getDBO();
								$config =& hikashop_config();
								$cancelled_order_status = explode(',',$config->get('cancelled_order_status'));
								$cancelled_order_status = "'".implode("','",$cancelled_order_status)."'";
								$query = 'SELECT COUNT(order_id) AS already_used FROM '.hikashop_table('order').' WHERE order_user_id='.(int)$user_id.' AND order_status NOT IN ('.$cancelled_order_status.') AND order_discount_code='.$db->Quote($coupon->discount_code).' GROUP BY order_id';
								$db->setQuery($query);
								$already_used = $db->loadResult();
								if($coupon->discount_quota_per_user<=$already_used){
									$error_message = JText::_('QUOTA_REACHED_FOR_COUPON');
								}
							}
						}
						if(empty($error_message)){
							if($coupon->discount_zone_id){
								$class = hikashop_get('class.zone');
								$zone = $class->get($coupon->discount_zone_id);
								if(!in_array($zone->zone_namekey,$zones)){
									$error_message = JText::_('COUPON_NOT_AVAILABLE_IN_YOUR_ZONE');
								}
							}
						}
						$ids = array();
						$qty = 0;
						foreach($products as $prod){
							$qty+=$prod->cart_product_quantity;
							$ids[$prod->product_id]=$prod->product_id;
							if(!empty($prod->product_parent_id)) $ids[$prod->product_parent_id]=$prod->product_parent_id;
						}
						if(empty($error_message) && $coupon->discount_product_id){
							if(!in_array($coupon->discount_product_id,$ids)){
								$error_message = JText::_('COUPON_NOT_FOR_THOSE_PRODUCTS');
							}
						}
						if(empty($error_message) && $coupon->discount_category_id){
							$database =& JFactory::getDBO();
							if($coupon->discount_category_childs){
								$categoryClass = hikashop_get('class.category');
								$category = $categoryClass->get((int)$coupon->discount_category_id);
								$filters = array('b.category_left >= '.$category->category_left ,'b.category_right <= '.$category->category_right,'b.category_published=1','b.category_type=\'product\'','a.product_id IN ('.implode(',',$ids).')');
								hikashop_addACLFilters($filters,'category_access','b');
								$select = 'SELECT a.product_id FROM '.hikashop_table('category').' AS b LEFT JOIN '.hikashop_table('product_category').' AS a ON b.category_id=a.category_id WHERE '.implode(' AND ',$filters);
							}else{
								$filters = array('b.category_id = '.(int)$coupon->discount_category_id ,'a.product_id IN ('.implode(',',$ids).')');
								hikashop_addACLFilters($filters,'category_access','b');
								$select = 'SELECT a.product_id FROM '.hikashop_table('category').' AS b LEFT JOIN '.hikashop_table('product_category').' AS a ON b.category_id=a.category_id WHERE '.implode(' AND ',$filters);
							}
							$database->setQuery($select);

							$id = $database->loadRowList();
							if (empty($id)) {
								$error_message = JText::_('COUPON_NOT_FOR_PRODUCTS_IN_THOSE_CATEGORIES');
							}
						}
						if(empty($error_message) && bccomp($coupon->discount_minimum_order,0,5)){
							$currency->convertCoupon($coupon,$total->prices[0]->price_currency_id);
							$config =& hikashop_config();
							$discount_before_tax = $config->get('discount_before_tax');
							$var = 'price_value_with_tax';
							if(!$discount_before_tax){
								$var = 'price_value';
							}
							if($coupon->discount_minimum_order>$total->prices[0]->$var){
								$error_message = JText::sprintf('ORDER_NOT_EXPENSIVE_ENOUGH_FOR_COUPON',$currency->format($coupon->discount_minimum_order,$coupon->discount_currency_id));
							}
						}
						if(empty($error_message) && $coupon->discount_minimum_products){
							if($coupon->discount_minimum_products>$qty){
								$error_message = JText::sprintf('NOT_ENOUGH_PRODUCTS_FOR_COUPON',$coupon->discount_minimum_products);
							}
						}
					}
				}
			}
		}
		$dispatcher->trigger( 'onAfterCouponCheck', array( &$coupon,&$total,&$zones,&$products,&$display_error, &$error_message, & $do));
		if(!empty($error_message)){
			$class = hikashop_get('class.cart');
			$class->update('',0,0,'coupon');
			if($display_error){
				JRequest::setVar('coupon_error_message',$error_message);
			}
			return null;
		}
		JRequest::setVar('coupon_error_message','');
		if($do){
			$currency->convertCoupon($coupon,$total->prices[0]->price_currency_id);
			if(PHP_VERSION < 5){
				$coupon->total = $total;
			}else{
				$coupon->total->prices = array(clone(reset($total->prices)));
			}

			if (!empty($coupon->discount_coupon_product_only) && bccomp($coupon->discount_percent_amount, 0, 5)) {
				$coupon->discount_flat_amount = 0;
				if (!empty($coupon->discount_product_id)) {
					foreach ($products as $product) {
						if ($coupon->discount_product_id == $product->product_id) {
							$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->price_value) / 100;
							break;
						}
					}
				}
				else if(!empty($id)) {
					foreach ($products as $product) {
						foreach ($id as $productid) {
							if ($product->product_id == $productid[0]) {
								$coupon->discount_flat_amount += ($coupon->discount_percent_amount * $product->prices[0]->price_value) / 100;
								break;
							}
						}
					}
				}
				if (bccomp($coupon->discount_flat_amount, 0, 5)) {
					$coupon->discount_percent_amount = 0;
					$coupon->discount_coupon_nodoubling = null;
				}
			}
			switch (@$coupon->discount_coupon_nodoubling) {
				case 1:


					$coupon = $this->addCoupon($coupon, $products, $currency, 1);
					break;
				case 2:


					$coupon = $this->addCoupon($coupon, $products, $currency, 2);
					break;
				default:


					$currency->addCoupon($coupon->total,$coupon, $currency, 0);
					break;
			}
		}
		return $coupon;
	}
	function addCoupon(&$coupon1, &$products, &$currency, $discountmode) {
		$totaldiscount=0.0;
		$totaldiscount_with_tax=0.0;
		$totalprice=0.0;
		$totalprice_with_tax=0.0;
		$totalnondiscount=0.0;
		$totalnondiscount_with_tax=0.0;
		foreach($products as $k => $product){
			if(!empty($product->prices)&&$product->cart_product_quantity>0){
				$price = reset($product->prices);
				if (isset($price->price_value)) {
					$totalprice += $price->price_value;
					if (isset($price->price_value_without_discount)){
						$totaldiscount += $price->price_value_without_discount - $price->price_value;
					}
					else {
						$totalnondiscount += $price->price_value;
					}
				}
				if (isset($price->price_value_with_tax)) {
					$totalprice_with_tax += $price->price_value_with_tax;
					if (isset($price->price_value_without_discount_with_tax)){
						$totaldiscount_with_tax += $price->price_value_without_discount_with_tax - $price->price_value_with_tax;
					}
					else {
						$totalnondiscount_with_tax += $price->price_value_with_tax;
					}
				}
			}
		}
		if (!bccomp($totaldiscount_with_tax, 0, 5) || !bccomp($totaldiscount, 0, 5)) {
			$currency->addCoupon($coupon1->total,$coupon1);
			return $coupon1;
		}
		if (bccomp($coupon1->discount_flat_amount, 0, 5) && $totalnondiscount >= $coupon1->discount_flat_amount) {
			$currency->addCoupon($coupon1->total,$coupon1);
			return $coupon1;
		}
		$totalprice += $totaldiscount;
		$totalprice_with_tax += $totaldiscount_with_tax;
		$coupon2 = clone $coupon1;
		$coupon2->total = clone $coupon1->total;
		$coupon2->total->prices = $this->copyStandardPrices($coupon1->total->prices);
		switch ($discountmode) {
			case 2:
				$coupon2->total->prices[0]->price_value_with_tax = $totalprice_with_tax;
				$coupon2->total->prices[0]->price_value = $totalprice;
				$currency->addCoupon($coupon2->total,$coupon2);
				$coupon2->total->prices[0]->price_value_without_discount_with_tax -= $totaldiscount_with_tax;
				$coupon2->total->prices[0]->price_value_without_discount -= $totaldiscount;
				$coupon2->discount_percent_amount_calculated_without_tax -= $totaldiscount;
				$coupon2->discount_value_without_tax -= $totaldiscount;
				$coupon2->discount_percent_amount_calculated -= $totaldiscount;
				$coupon2->discount_value -= $totaldiscount;
				$coupon2->discount_flat_amount = $coupon2->discount_value;
				break;
			default:
				$coupon2->total->prices[0]->price_value_with_tax = $totalnondiscount_with_tax;
				$coupon2->total->prices[0]->price_value = $totalnondiscount;
				 $currency->addCoupon($coupon2->total,$coupon2);
				break;
		}
		if (isset($coupon2->discount_percent_amount_calculated) && $discountmode < 2) {
			$price_diff = $coupon2->discount_percent_amount_calculated - $totaldiscount_with_tax;


		}
		elseif(isset($coupon2->discount_percent_amount_calculated) && $discountmode == 2){
			$price_diff = $coupon2->discount_value;
		}else {
			$price_diff = $coupon2->discount_percent_amount_calculated - $totaldiscount_with_tax;
			$price_diff += $totaldiscount;
		}
		if ($price_diff <= 0) {
			JRequest::setVar('coupon_error_message','Coupon has no value when used with current product discount(s).');
			return $coupon1;
		}
		if(!(isset($coupon2->discount_percent_amount_calculated) && $discountmode == 2)){
			$coupon2->discount_percent_amount_calculated_without_tax = $price_diff + $totaldiscount;
			$coupon2->discount_value_without_tax = $price_diff + $totaldiscount;
			$coupon2->discount_percent_amount_calculated = $price_diff + $totaldiscount_with_tax;
			$coupon2->discount_value = $price_diff + $totaldiscount_with_tax;
		}
		if ($discountmode == 1) {
			$coupon2->total->prices[0]->price_value_without_discount_with_tax += ($totalprice_with_tax - $totalnondiscount_with_tax);
			$coupon2->total->prices[0]->price_value_without_discount += ($totalprice - $totalnondiscount);
			$coupon2->total->prices[0]->price_value_with_tax += ($totalprice_with_tax - $totalnondiscount_with_tax);
			$coupon2->total->prices[0]->price_value += ($totalprice - $totalnondiscount);
			$coupon2->total->prices[0]->price_value_with_tax -= $totaldiscount_with_tax;
			$coupon2->total->prices[0]->price_value -= $totaldiscount;
		}
		if ($coupon2->discount_flat_amount != $coupon2->discount_value_without_tax) {
			JRequest::setVar('coupon_error_message','Coupon has limited value when used with current product discount(s).');
		}
		return $coupon2;
	}
	function copyStandardPrices($prices) {
		$copiedPrices = array();
		for ($i=0; $i<count($prices); $i++) $copiedPrices[$i] = clone $prices{$i};
		return $copiedPrices;
	}
}