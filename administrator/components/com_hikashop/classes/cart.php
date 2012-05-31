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
class hikashopCartClass extends hikashopClass{
	var $tables = array('cart_product','cart');
	var $pkeys = array('cart_id','cart_id');
	var $options = array();
	var $new_quantities = array();
	var $insertedIds = array();
	function hasCart($cart_id=0){
		$this->loadCart($cart_id);
		if(!empty($this->cart->cart_id)){
			return true;
		}
		return false;
	}
	function loadCart($cart_id=0,$reset=false){
		static $carts = array();
		if($reset){
			$carts = array();
			$this->cart_id = 0;
			$this->cart = null;
			return true;
		}
		$this->filters = array();
		$app =& JFactory::getApplication();
		if(empty($cart_id)){
			$this->cart_id = $app->getUserStateFromRequest( HIKASHOP_COMPONENT.'.cart_id', 'cart_id', 0, 'int' );
		}else{
			$this->cart_id=$cart_id;
		}
		if(!empty($this->cart_id)){
			$this->filters[]='a.cart_id = '.(int)$this->cart_id;
		}else{
			$user = JFactory::getUser();
			if(!empty($user->id)){
				$this->filters[]='a.user_id = '.(int)$user->id;
			}
			$session = JFactory::getSession();
			if($session->getId()){
				$this->filters[]='a.session_id = '.$this->database->Quote($session->getId());
			}
		}
		$filter = implode(' OR ',$this->filters);
		if(!empty($carts[$filter])){
			$this->cart =& $carts[$filter];
		}else{
			if(!empty($filter)){
				$query='SELECT a.* FROM '.hikashop_table('cart').' AS a WHERE '.$filter.' ORDER BY a.cart_modified DESC LIMIT 1';
				$this->database->setQuery($query);
				$this->cart = $this->database->loadObject();
			}else{
				$this->cart = null;
			}
			$carts[$filter] =& $this->cart;
		}
	}
	function & get($cart_id=0,$keepEmptyCart=false){
		$result = false;
		if($this->hasCart($cart_id)){
			$filters=array('b.cart_id = '.$this->cart->cart_id,'b.product_id > 0');
			hikashop_addACLFilters($filters,'product_access','c');
			$query='SELECT b.*,c.* FROM '.hikashop_table('cart_product').' AS b LEFT JOIN '.hikashop_table('product').' AS c ON b.product_id=c.product_id WHERE '.implode(' AND ',$filters).' ORDER BY c.product_parent_id ASC,b.cart_product_modified ASC';
			$this->database->setQuery($query);
			$products = $this->database->loadObjectList('cart_product_id');
			if(empty($products) && !$keepEmptyCart){
				$this->delete($this->cart->cart_id);
				$app =& JFactory::getApplication();
				$app->setUserState(HIKASHOP_COMPONENT.'.cart_id', 0);
				$this->cart = null;
			}
			return $products;
		}
		return $result;
	}
	function addToCartFromFields(&$entriesData,&$fields){
		$this->resetCart(false);
		$app =& JFactory::getApplication();
		$productsToAdd = array();
		$coupons = array();
		foreach($entriesData as $entryData){
			foreach(get_object_vars($entryData) as $namekey=>$value){
				foreach($fields as $field){
					if($field->field_namekey==$namekey){
						$ok = false;
						if(!empty($field->field_options) && !is_array($field->field_options)) $field->field_options = unserialize($field->field_options);
						if(!empty($field->field_options['product_id'])){
							if(is_numeric($value) && is_numeric($field->field_options['product_value'])){
								if( $value === $field->field_options['product_value'] ){
									$ok = true;
								}
							}elseif($value == $field->field_options['product_value']){
								$ok = true;
							}
							if($ok){
								$id = $field->field_options['product_id'];
								if(empty($productsToAdd[$id])){
									$productsToAdd[$id]=1;
								}else{
									$productsToAdd[$id]++;
								}
							}
						}
						if($field->field_type=='coupon' && !empty($field->coupon[$value])){
							$coupons[] = $field->coupon[$value];
						}
						break;
					}
				}
			}
		}
		if(!empty($productsToAdd)){
			$array = array();
			foreach($productsToAdd as $id => $qty){
				$this->updateEntry($qty,$array,$id,0,false);
			}
		}
		if(count($coupons)>1){
			$total = 0.0;
			$currency = hikashop_getCurrency();
			$currencyClass = hikashop_get('class.currency');
			$discountClass = hikashop_get('class.discount');
			foreach($coupons as $item){
				$currencyClass->convertCoupon($item,$currency);
				$total = $total + $item->discount_flat_amount;
				$this->database->setQuery('UPDATE '.hikashop_table('discount').' SET discount_used_times=discount_used_times+1 WHERE discount_id = '.$item->discount_id);
				$this->database->query();
			}
			$newCoupon = null;
			$newCoupon->discount_type='coupon';
			$newCoupon->discount_currency_id = $currency;
			$newCoupon->discount_flat_amount = $total;
			$newCoupon->discount_quota = 1;
			jimport('joomla.user.helper');
			$newCoupon->discount_code = JUserHelper::genRandomPassword(30);
			$newCoupon->discount_published = 1;
			$discountClass->save($newCoupon);
			$coupon = $newCoupon;
		}elseif(count($coupons)==1){
			$coupon = reset($coupons);
		}
		if(!empty($coupon)){
			$this->update($coupon->discount_code,1,0,'coupon',false);
		}
		$this->loadCart(0,true);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',0);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',0);
	}
	function initCart(){
		$cart = null;
		if(!empty($this->cart->cart_id)){
			$cart->cart_id = $this->cart->cart_id;
		}
		$user = JFactory::getUser();
		if(!empty($user->id)){
			$cart->user_id = $user->id;
		}
		$session = JFactory::getSession();
		if($session->getId()){
			$cart->session_id = $session->getId();
		}
		$cart->cart_modified=time();
		$this->cart->cart_id=(int)$this->save($cart);
		return $cart;
	}
	function resetCart($reset=true){
		$cartContent =& $this->get();
		$cart = $this->initCart();
		$app =& JFactory::getApplication();
		$app->setUserState( HIKASHOP_COMPONENT.'.entries_fields',null);
		if(!empty($cartContent)){
			$query = 'DELETE FROM '.hikashop_table('cart_product').' WHERE cart_id = '.$cart->cart_id;
			$this->database->setQuery($query);
			$this->database->query();
		}
		if(!empty($this->cart->cart_coupon)){
			$app->setUserState( HIKASHOP_COMPONENT.'.coupon_code',$this->cart->cart_coupon);
			$this->update('',0,0,'coupon',false);
			$app->setUserState( HIKASHOP_COMPONENT.'.coupon_code','');
		}
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',0);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0);
		$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',0);
		if($reset){
			$this->loadCart(0,true);
		}
	}
	function update($product_id,$quantity=1,$add=0,$type='product',$resetCartWhenUpdate=true,$force=false){
		if($type=='product' && empty($product_id))return false;
		$cartContent =& $this->get();
		$app =& JFactory::getApplication();
		$entries = $app->getUserState(HIKASHOP_COMPONENT.'.entries_fields');
		if(!empty($entries)){
			return false;
		}
		$cart = $this->initCart();
		if($this->cart->cart_id){
			$app->setUserState(HIKASHOP_COMPONENT.'.cart_id', $this->cart->cart_id);
		}
		if(in_array($type,array('product','item'))){
			if(!is_array($product_id)){
				$pid =$product_id;
				$this->mainProduct = $product_id;
				$product_id=array($product_id=>$quantity);
				$options = JRequest::getVar( 'hikashop_product_option', array(), '', 'array' );
				if(!empty($options)&& is_array($options)){
					foreach($options as $optionElement){
						$this->options[$optionElement]=$pid;
						$product_id[$optionElement]=$quantity;
					}
				}
			}
			$updated = false;
			foreach($product_id as $id => $infos){
				$res = $this->updateEntry($infos,$cartContent,(int)$id,$add,false,$type,$force);
				if(is_numeric($id) && $res){
					$updated = true;
				}
			}
			if($updated && $resetCartWhenUpdate){
				$this->loadCart(0,true);
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',0);
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0);
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',0);
			}
			return $updated;
		}else{
			if($quantity){
				$new_coupon=$product_id;
			}else{
				$new_coupon='';
			}
			$old_coupon = $app->getUserState( HIKASHOP_COMPONENT.'.coupon_code','');
			if($old_coupon == $new_coupon){
				return false;
			}
			$cart->cart_coupon = $new_coupon;
			$this->cart->cart_coupon = $new_coupon;
			if($this->save($cart)){
				if(!$quantity){
					if(!empty($product_id)){
						$message = JText::_('COUPON_REMOVED');
						$app->enqueueMessage( $message );
					}
				}
				if($resetCartWhenUpdate){
					$this->loadCart(0,true);
					$app->setUserState( HIKASHOP_COMPONENT.'.coupon_code',$new_coupon);
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',0);
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0);
					$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',0);
				}
				return true;
			}
		}
		return false;
	}
	function updateEntry($quantity,&$cartContent,$product_id,$add,$resetCartWhenUpdate=true,$type='product',$force=false){
		if(empty($product_id)) return false;
		if($type=='product'){
			$id = 0;
			if(!empty($cartContent)){
				$do = true;
				static $already_done = false;
				if((!$already_done || $force) && hikashop_level(2)){
					$already_done = true;
					$formData = JRequest::getVar( 'data', array(), '', 'array' );
					if(!empty($formData['item']) || !empty($_FILES)){
						$fieldClass = hikashop_get('class.field');
						$element = null;
						$element->product_id = $product_id;
						$data = $fieldClass->getInput('item',$element,true,'data',$force);
						if($data===false){
							$this->errors = true;
							return false;
						}
						if(!empty($data)){
							$do = false;
							foreach($cartContent as $cart_product_id => $prod){
								if($prod->product_id == $product_id ){
									$same = true;
									foreach(get_object_vars($data) as $field => $var){
										if($prod->$field!=$var){
											$same = false;
										}
									}
									if($same){
										$do = true;
									}
								}
							}
						}
					}
				}
				if($do){
					foreach($cartContent as $cart_product_id => $prod){
						if($prod->product_id==$product_id){
							if(@$this->mainProduct==$product_id || @$this->mainProduct==@$cartContent[@$prod->cart_product_option_parent_id]->product_id){
								$already = array();
								foreach($cartContent as $optionElement){
									if($this->mainProduct==$product_id && $optionElement->cart_product_option_parent_id==$cart_product_id){
										$already[]=$optionElement->product_id;
										continue;
									}
									if(@$this->mainProduct==@$cartContent[@$prod->cart_product_option_parent_id]->product_id && $optionElement->cart_product_option_parent_id==@$prod->cart_product_option_parent_id){
										$already[]=$optionElement->product_id;
										continue;
									}
								}
								$ok = true;
								if(!empty($already)){
									foreach($already as $a){
										if(!isset($this->options[$a])){
											$ok = false;
										}
									}
									foreach($this->options as $o=>$a){
										if(!in_array($o,$already)){
											$ok = false;
										}
									}
								}
								if($ok) $id = $cart_product_id;
								break;
							}
						}
					}
				}
			}
			$quantity=(int)$quantity;
		}else{
			$id = $product_id;
			$product_id = (int)@$cartContent[$id]->product_id;
			if(is_array($quantity)){
				$quantity=(int)@$quantity['cart_product_quantity'];
			}else{
				$quantity=(int)@$quantity;
			}
		}
		if($quantity){
			if(!empty($cartContent) && in_array($id,array_keys($cartContent))){
				if($add){
					$quantity+=$cartContent[$id]->cart_product_quantity;
					$add=0;
				}elseif($quantity==$cartContent[$id]->cart_product_quantity){
					return false;
				}
				$this->_checkQuantity($cartContent[$id],$quantity,$cartContent);
				if($quantity){
					$query = 'UPDATE '.hikashop_table('cart_product').' SET cart_product_modified=\''.time().'\',cart_product_quantity='.(int)$quantity.' WHERE cart_product_id='.$cartContent[$id]->cart_product_id;
					$this->database->setQuery($query);
					$this->database->query();
					if($resetCartWhenUpdate){
						$this->loadCart(0,true);
						$app =& JFactory::getApplication();
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',0);
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0);
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',0);
					}
					return true;
				}
			}elseif($this->cart->cart_id){
				$class = hikashop_get('class.product');
				$product = $class->get($product_id);
				$parent = 0;
				$this->_checkQuantity($product,$quantity,$cartContent);
				if($quantity){
					if($product->product_type=='variant'){
						$query = 'INSERT INTO '.hikashop_table('cart_product').' (cart_id,cart_product_modified,product_id,cart_product_parent_id,cart_product_quantity) VALUES ( '.$this->cart->cart_id.','.time().','.(int)$product->product_parent_id.',0,0)';
						$this->database->setQuery($query);
						$this->database->query();
						$parent = (int)$this->database->insertid();
						$this->insertedIds[(int)$product->product_parent_id]=$parent;
					}
					$optionElement=0;
					if(!empty($this->insertedIds[(int)@$this->mainProduct])){
						$optionElement = (int)$this->insertedIds[$this->mainProduct];
					}
					$fields = array('cart_id','cart_product_modified','product_id','cart_product_parent_id','cart_product_quantity','cart_product_option_parent_id');
					$values = array($this->cart->cart_id,time(),(int)$product_id,$parent,(int)$quantity,$optionElement);
					static $already_done2 = false;
					if((!$already_done2 || $force) && hikashop_level(2)){
						$already_done2 = true;
						$formData = JRequest::getVar( 'data', array(), '', 'array' );
						if(!empty($formData['item']) || !empty($_FILES)){
							if(empty($data)){
								$fieldClass = hikashop_get('class.field');
								$element = null;
								$element->product_id = $product_id;
								$data = $fieldClass->getInput('item',$element,true,'data',$force);
								if($data===false){
									$this->errors = true;
									return false;
								}
							}
							if(!empty($data)){
								foreach(get_object_vars($data) as $field => $var){
									$fields[]=$field;
									$values[]=$this->database->Quote($var);
								}
							}
						}
					}
					$query = 'INSERT INTO '.hikashop_table('cart_product').' ('.implode(',',$fields).') VALUES ('.implode(',',$values).')';
					$this->database->setQuery($query);
					$this->database->query();
					$cartId = (int)$this->database->insertid();
					$this->insertedIds[(int)$product_id]=$cartId;
					if($resetCartWhenUpdate){
						$this->loadCart(0,true);
						$app =& JFactory::getApplication();
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',0);
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0);
						$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',0);
					}
					return true;
				}
			}
		}
		if(!$add && !empty($cartContent) && in_array($id,array_keys($cartContent))){
			$query = 'DELETE FROM '.hikashop_table('cart_product').' WHERE cart_product_id = '.$cartContent[$id]->cart_product_id. ' OR cart_product_parent_id = '.$id.' OR cart_product_id = '.$cartContent[$id]->cart_product_parent_id;
			$this->database->setQuery($query);
			$this->database->query();
			if($resetCartWhenUpdate){
				$this->loadCart(0,true);
				$app =& JFactory::getApplication();
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_method',0);
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_id',0);
				$app->setUserState( HIKASHOP_COMPONENT.'.shipping_data',0);
			}
			return true;
		}
		return false;
	}
	function _checkQuantity(&$product,&$quantity,&$cartContent){
		if($quantity<0){
			$quantity = 0;
		}
		$wantedQuantity = $quantity;
		if(!empty($this->options[$product->product_id])){
			$parent = $this->options[$product->product_id];
			if(!empty($this->new_quantities[$parent]) && $quantity>$this->new_quantities[$parent]){
				$quantity = $this->new_quantities[$parent];
			}
		}
		if(hikashop_level(1)){
			$config =& hikashop_config();
			$item_limit = $config->get('cart_item_limit',0);
			if(!empty($item_limit)){
				$current_items = 0;
				if(!empty($cartContent)){
					foreach($cartContent as $element){
						if($element->product_id!=$product->product_id) $current_items+=(int)$element->cart_product_quantity;
					}
				}
				$possible_quantity = $item_limit - $current_items;
				if($quantity > $possible_quantity){
					if($possible_quantity<0){
						$possible_quantity=0;
					}
					$quantity=$possible_quantity;
				}
			}
		}
		if(hikashop_level(1)) {
			$database =& JFactory::getDBO();
			$productIds = array($product->product_id);
			if( $product->product_parent_id > 0 ) {
				$productIds[] = $product->product_parent_id;
			}
			$productCartIds = array((int)$product->product_id);
			if( is_array($cartContent) ) {
				foreach($cartContent as $cart_product_id => $prod){
					if( !in_array($prod->product_id, $productCartIds) ) {
						$productCartIds[] = (int)$prod->product_id;
					}
					if( $prod->product_parent_id > 0 && !in_array($prod->product_parent_id, $productCartIds) ) {
						$productCartIds[] = (int)$prod->product_parent_id;
					}
				}
			}
			$database->setQuery('SELECT category_id, product_id FROM '.hikashop_table('product_category').' WHERE product_id IN ('.implode(',',$productCartIds).');');
			$categoryIds = array();
			$cartCategoryLink = array();
			$ret = $database->loadObjectList();
			foreach($ret as $c) {
				$categoryIds[] = $c->category_id;
				if(!isset($cartCategoryLink[$c->product_id])) {
					$cartCategoryLink[$c->product_id] = array($c->category_id);
				} else {
					$cartCategoryLink[$c->product_id][] = $c->category_id;
				}
			}
			unset($c);
			unset($ret);
			$filters = array();
			hikashop_addACLFilters($filters,'limit_access','a');
			$query = ' FROM '.hikashop_table('limit').' AS a WHERE (a.limit_published = 1 AND (limit_start = 0 OR limit_start >= '.time().') AND (limit_end = 0 OR limit_end <= '.time().') AND (a.limit_product_id IN ('.implode(',',$productIds).') ';
			$filters = implode(' AND ', $filters);
			if( count($categoryIds) > 0 ) {
				$query .= 'OR limit_category_id IN ('.implode(',',$categoryIds).')';
			}
			if( !empty($filters) ) {
				$query .= ') AND ' . $filters . ');';
			} else {
				$query .= '))';
			}
			$database->setQuery('SELECT count(*)'.$query );
			$limiters = $database->loadResult();
			if( $limiters > 0 ) {
				$database->setQuery('SELECT a.*'.$query);
				$limiters = $database->loadObjectList();
				$periodicity = array(
					'forever' => 0,
					'yearly' => 1,
					'quaterly' => 2,
					'monthly' => 3,
					'weekly' => 4,
					'daily' => 5,
					'cart' => 6
				);
				$limiterTypes = array('price' => false, 'quantity' => false, 'weight' => false );
				$dateLimiter = 0;
				$categoryIds = array();
				$limit_statuses = array();
				foreach($limiters as $limiter) {
					if( $limiter->limit_category_id > 0 ) {
						$categoryIds[] = $limiter->limit_category_id;
					}
					$limiterTypes[ $limiter->limit_type ] = true;
					$dateLimiter = ($dateLimiter > 0 && $dateLimiter < $periodicity[$limiter->limit_periodicity])?$dateLimiter:$periodicity[$limiter->limit_periodicity];
					$statuses = explode(',',$limiter->limit_status);
					foreach($statuses as $s) {
						$limit_statuses[$s] = $s;
					}
					unset($s);
					unset($statuses);
				}
				$d = getdate();
				$baseDates = array(
					0 => 0,
					1 => mktime(0,0,0,1,1,$d['year']),
					2 => mktime(0,0,0,$d['mon']-(($d['mon']-1)%4),1,$d['year']),
					3 => mktime(0,0,0,$d['mon'],1,$d['year']),
					4 => mktime(0,0,0,$d['mon'],$d['mday']-$d['wday'],$d['year']),
					5 => mktime(0,0,0,$d['mon'],$d['mday'],$d['year']),
					6 => -1
				);
				$user = JFactory::getUser();
				if(!empty($user->id) && $baseDates[$dateLimiter] >= 0) {
					$query = 'SELECT a.order_product_id, a.product_id, a.order_product_quantity, a.order_product_price, a.order_product_tax, b.order_currency_id, b.order_created, b.order_status, c.product_parent_id, d.category_id FROM ';
					$query .= hikashop_table('order_product').' AS a';
					$query .= ' INNER JOIN '.hikashop_table('order').' AS b ON a.order_id = b.order_id ';
					if( count($limit_statuses) > 0 ) {
						$query .= "AND b.order_status IN ('". implode("','",$limit_statuses) ."')";
					}
					$query .= ' AND b.order_user_id = ' . (int)hikashop_loadUser();
					$query .= ' AND b.order_created >= ' . $baseDates[$dateLimiter];
					$query .= ' INNER JOIN '.hikashop_table('product').' AS c ON a.product_id = c.product_id';
					$query .= ' INNER JOIN '.hikashop_table('product_category').' AS d ON (c.product_parent_id = 0 AND c.product_id = d.product_id) OR (c.product_parent_id = d.product_id) ';
					$query .= ' WHERE a.product_id IN ('.implode(',',$productIds).')';
					if( count($categoryIds) > 0 )
						$query .= 'OR category_id IN ('.implode(',',$categoryIds).')';
					$query .= ';';
					$database->setQuery($query);
					$rows = $database->loadObjectList('product_id');
				} else {
					$rows = array();
				}
				$productIds = array_keys($rows);
				if( $limiterTypes['weight'] || $limiterTypes['price'] ) {
					$productClass = hikashop_get('class.product');
					$productClass->getProducts( $productIds );
					$fullcart = $this->loadFullCart(false, true);
				}
				foreach($limiters as $limiter) {
					$baseDate = $baseDates[ $periodicity[ $limiter->limit_periodicity ] ];
					$value = 0;
					foreach($rows as $r) {
						if( $baseDate >= 0 && $r->order_created >= $baseDate && strpos(','.$limiter->limit_status.',', ','.$r->order_status.',') !== false ) {
							if(
								($limiter->limit_product_id > 0 && ($limiter->limit_product_id == $r->product_id) || $limiter->limit_product_id == $r->product_parent_id)
									||
								($limiter->limit_category_id > 0 && ($limiter->limit_category_id == $r->category_id))
							) {
								switch($limiter->limit_type) {
									case 'quantity':
										$value += $r->order_product_quantity;
										break;
									case 'price':
										if(function_exists('hikashop_product_price_for_quantity_in_order')){
											hikashop_product_price_for_quantity_in_order($r);
										}else{
											$r->order_product_total_price_no_vat = $r->order_product_price*$r->order_product_quantity;
											$r->order_product_total_price = ($r->order_product_price+$r->order_product_tax)*$r->order_product_quantity;
										}
										$value += $r->order_product_total_price;
									case 'weight':
										$id = ($r->product_parent_id == 0)?$r->product_id:$r->product_parent_id;
										if(!empty($productClass->products[$id])){
											$p =& $productClass->products[$id];
											if(empty($p->product_weight)&& $r->product_parent_id != 0 && !empty($productClass->products[$r->product_parent_id])){
												$p =& $productClass->products[$r->product_parent_id];
											}
											if( $p->product_weight_unit == $limiter->limit_unit ) {
												$value += $p->product_weight * $r->order_product_quantity;
											}
											unset($p);
										}
										break;
								}
							}
						}
					}
					if( isset($fullcart) ) {
						foreach($fullcart->products as $cc ) {
							if( $cc->product_id == $product->product_id )
								continue;
							$id = ($cc->product_parent_id == 0)?$cc->product_id:$cc->product_parent_id;
							if( ($limiter->limit_product_id > 0 && $limiter->limit_product_id == $id) || ($limiter->limit_category_id > 0 && in_array($limiter->limit_category_id, $cartCategoryLink[$id])) ) {
								switch($limiter->limit_type) {
									case 'quantity':
										$value += $cc->cart_product_quantity;
										break;
									case 'price':
										$value += $cc->prices[0]->price_value_with_tax;
										break;
									case 'weight':
										if( $cc->product_weight_unit == $limiter->limit_unit ) {
											$value += $cc->product_weight * $cc->cart_product_total_quantity;
										}
										break;
								}
							}
						}
					} else {
						foreach($cartContent as $cc ) {
							if( $cc->product_id == $product->product_id )
								continue;
							$id = ($cc->product_parent_id == 0)?$cc->product_id:$cc->product_parent_id;
							if( ($limiter->limit_product_id > 0 && $limiter->limit_product_id == $id) || ($limiter->limit_category_id > 0 && in_array($limiter->limit_category_id, $cartCategoryLink[$id])) ) {
								if($limiter->limit_type == 'quantity') {
									$value += $cc->cart_product_quantity;
								}
							}
						}
					}
					switch($limiter->limit_type) {
						case 'quantity':
							if( $value + $quantity > $limiter->limit_value ) {
								$quantity = $limiter->limit_value - $value;
							}
							break;
						case 'price':
							$currencyClass = hikashop_get('class.currency');
							$config =& hikashop_config();
							$main_currency = (int)$config->get('main_currency',1);
							$currency_id = hikashop_getCurrency();
							if(!in_array($currency_id,$currencyClass->publishedCurrencies())){
								$currency_id = $main_currency;
							}
							$zone_id = hikashop_getZone('shipping');
							if($config->get('tax_zone_type','shipping')=='billing'){
								$tax_zone_id=hikashop_getZone('billing');
							}else{
								$tax_zone_id=$zone_id;
							}
							$discount_before_tax = (int)$config->get('discount_before_tax',0);
							$oldQuantity = $product->cart_product_quantity;
							$product->cart_product_quantity = $quantity;
							$product->cart_product_total_quantity = $quantity;
							$ids = array($product->product_id);
							$currencyClass->getPrices($product,$ids,$currency_id,$main_currency,$tax_zone_id,$discount_before_tax);
							$currencyClass->calculateProductPriceForQuantity($product);
							if( $value + $product->prices[0]->price_value_with_tax > $limiter->limit_value ) {
								while( $product->cart_product_quantity > 0 && ($value + $product->prices[0]->price_value_with_tax > $limiter->limit_value) ) {
									$product->cart_product_quantity--;
									$currencyClass->getPrices($product,$ids,$currency_id,$main_currency,$tax_zone_id,$discount_before_tax);
									$currencyClass->calculateProductPriceForQuantity($product);
								}
								$quantity = $product->cart_product_quantity;
							}
							$product->cart_product_quantity = $oldQuantity;
							$product->cart_product_total_quantity = $oldQuantity;
							$currencyClass->getPrices($product,$ids,$currency_id,$main_currency,$tax_zone_id,$discount_before_tax);
							$currencyClass->calculateProductPriceForQuantity($product);
							break;
						case 'weight':
							if( $product->product_weight > 0 && $product->product_weight_unit == $limiter->limit_unit && ($value + ($quantity * $product->product_weight) > $limiter->limit_value) ) {
								$quantity = floor(($limiter->limit_value - $value) / $product->product_weight);
							}
							break;
					}
					if( $quantity < 0 ) {
						$quantity = 0;
					}
				}
			}
		}
		if($product->product_type=='variant'){
			$class = hikashop_get('class.product');
			$parentProduct = $class->get($product->product_parent_id);
			if($product->product_quantity==-1 && $parentProduct->product_quantity!=-1){
				$product->product_quantity = $parentProduct->product_quantity;
			}
			if(empty($product->product_max_per_order) && !empty($parentProduct->product_max_per_order)){
				$product->product_max_per_order = $parentProduct->product_max_per_order;
			}
			if(empty($product->product_min_per_order) && !empty($parentProduct->product_min_per_order)){
				$product->product_min_per_order = $parentProduct->product_min_per_order;
			}
		}
		if($product->product_quantity>=0 && $product->product_quantity<$quantity) $quantity = $product->product_quantity;
		if($product->product_min_per_order>0 && $product->product_min_per_order>$quantity){
			$quantity = $product->product_min_per_order;
			if($product->product_quantity>=0 && $product->product_quantity<$quantity){
				$quantity = 0;
			}
		}
		if($product->product_max_per_order>0 && $product->product_max_per_order<$quantity) $quantity = $product->product_max_per_order;
		if( $wantedQuantity > $quantity ) {
			$app =& JFactory::getApplication();
			if( $quantity == 0 ) {
				$app->enqueueMessage( JText::sprintf( 'LIMIT_REACHED_REMOVED', $product->product_name));
			} else {
				$app->enqueueMessage( JText::sprintf( 'LIMIT_REACHED', $product->product_name));
			}
		}
		$this->new_quantities[$product->product_id] = $quantity;
	}
	function &loadFullCart($addtionalInfos=false,$keepEmptyCart=false){
		$app =& JFactory::getApplication();
		$database	=& JFactory::getDBO();
		$config =& hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$productClass = hikashop_get('class.product');
		$main_currency = (int)$config->get('main_currency',1);
		$currency_id = hikashop_getCurrency();
		if(!in_array($currency_id,$currencyClass->publishedCurrencies())){
			$currency_id = $main_currency;
		}
		$zone_id = hikashop_getZone('shipping');
		if($config->get('tax_zone_type','shipping')=='billing'){
			$tax_zone_id=hikashop_getZone('billing');
		}else{
			$tax_zone_id=$zone_id;
		}
		$discount_before_tax = (int)$config->get('discount_before_tax',0);
		$cart = null;
		$cart->products =& $this->get(@$this->cart->cart_id, $keepEmptyCart);
		$cart->cart_id = @$this->cart->cart_id;
		$cart->coupon = null;
		$cart->shipping = null;
		$cart->total = null;
		if(!empty($cart->products)){
			$ids = array();
			foreach($cart->products as $product){
				$ids[]=$product->product_id;
			}
			JArrayHelper::toInteger($ids);
			$query = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type = \'product\'';
			$database->setQuery($query);
			$images = $database->loadObjectList();
			if(!empty($images)){
				foreach($cart->products as $k => $row){
					$productClass->addFiles($cart->products[$k],$images);
				}
			}
			foreach($cart->products as $k => $row){
				if($row->product_type=='variant'){
					foreach($cart->products as $k2 => $row2){
						if($row->product_parent_id==$row2->product_id){
							$cart->products[$k2]->variants[]=&$cart->products[$k];
							break;
						}
					}
				}
			}
			$query = 'SELECT a.*,b.* FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id IN ('.implode(',',$ids).') ORDER BY a.ordering,b.characteristic_value';
			$database->setQuery($query);
			$characteristics = $database->loadObjectList();
			if(!empty($characteristics)){
				foreach($cart->products as $key => $product){
					if($product->product_type!='variant'){
						$element =& $cart->products[$key];
						$product_id=$product->product_id;
						$mainCharacteristics = array();
						foreach($characteristics as $characteristic){
							if($product_id==$characteristic->variant_product_id){
								$mainCharacteristics[$product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
							}
							if(!empty($element->options)){
								foreach($element->options as $k => $optionElement){
									if($optionElement->product_id==$characteristic->variant_product_id){
										$mainCharacteristics[$optionElement->product_id][$characteristic->characteristic_parent_id][$characteristic->characteristic_id]=$characteristic;
									}
								}
							}
						}
						if(!empty($element->variants)){
							$this->addCharacteristics($element,$mainCharacteristics,$characteristics);
						}
						if(!empty($element->options)){
							foreach($element->options as $k => $optionElement){
								if(!empty($optionElement->variants)){
									$this->addCharacteristics($element->options[$k],$mainCharacteristics,$characteristics);
								}
							}
						}
					}
				}
			}
			$product_quantities = array();
			foreach($cart->products as $row){
				if(empty($product_quantities[$row->product_id])){
					$product_quantities[$row->product_id] = (int)@$row->cart_product_quantity;
				}else{
					$product_quantities[$row->product_id]+=(int)@$row->cart_product_quantity;
				}
			}
			foreach($cart->products as $k => $row){
				$cart->products[$k]->cart_product_total_quantity = $product_quantities[$row->product_id];
			}
			$currencyClass->getPrices($cart->products,$ids,$currency_id,$main_currency,$tax_zone_id,$discount_before_tax);
			if($addtionalInfos){
				$queryImage = 'SELECT * FROM '.hikashop_table('file').' WHERE file_ref_id IN ('.implode(',',$ids).') AND file_type=\'product\' ORDER BY file_id ASC';
				$database->setQuery($queryImage);
				$images = $database->loadObjectList();
				foreach($cart->products as $k=>$row){
					if(!empty($images)){
						foreach($images as $image){
							if($row->product_id==$image->file_ref_id){
								if(!isset($row->file_ref_id)){
									foreach(get_object_vars($image) as $key => $name){
										$cart->products[$k]->$key = $name;
									}
								}
								break;
							}
						}
					}
					if(!isset($cart->products[$k]->file_name)){
						$cart->products[$k]->file_name = $row->product_name;
					}
				}
			}
			foreach($cart->products as $k => $row){
				if(!empty($row->variants)){
					foreach($row->variants as $k2 => $variant){
						$productClass->checkVariant($cart->products[$k]->variants[$k2],$row);
					}
				}
			}
			$notUsable = array();
			foreach($cart->products as $k => $product){
				if(empty($product->product_id)){
					continue;
				}
				if(!empty($product->cart_product_quantity)){
					if(empty($product->product_published)){
						$notUsable[$product->cart_product_id]=0;
						$app->enqueueMessage(JText::sprintf('PRODUCT_NOT_AVAILABLE',$product->product_name),'notice');
						continue;
					}
					if($product->product_quantity!=-1 && $product->product_quantity < $product->cart_product_quantity){
						$notUsable[$product->cart_product_id]=0;
						$app->enqueueMessage(JText::sprintf('NOT_ENOUGH_STOCK_FOR_PRODUCT',$product->product_name),'notice');
						continue;
					}
					if($product->product_sale_start>time()){
						$notUsable[$product->cart_product_id]=0;
						$app->enqueueMessage(JText::sprintf('PRODUCT_NOT_YET_ON_SALE',$product->product_name),'notice');
						continue;
					}
					if(!empty($product->product_sale_end) && $product->product_sale_end<time()){
						$notUsable[$product->cart_product_id]=0;
						$app->enqueueMessage(JText::sprintf('PRODUCT_NOT_SOLD_ANYMORE',$product->product_name),'notice');
						continue;
					}
				}
			}
			if(!empty($notUsable)){
				$this->update($notUsable,1,0,'item');
				return $this->loadFullCart($addtionalInfos);
			}
			foreach($cart->products as $k => $row){
				$currencyClass->calculateProductPriceForQuantity($cart->products[$k]);
			}
			$currencyClass->calculateTotal($cart->products,$cart->total,$currency_id);
			$cart->full_total=&$cart->total;
			if(!empty($this->cart->cart_coupon)){
				$discount=hikashop_get('class.discount');
				$discountData = $discount->load($this->cart->cart_coupon);
				if(@$discountData->discount_auto_load){
					$current_auto_coupon_key = sha1($zone_id.'_'.serialize($cart->products));
					$previous_auto_coupon_key = $app->getUserState( HIKASHOP_COMPONENT.'.auto_coupon_key');
					if($current_auto_coupon_key!=$previous_auto_coupon_key){
						$this->cart->cart_coupon='';
					}
				}
			}
			if(hikashop_level(1) && empty($this->cart->cart_coupon)){
				$filters = array('discount_type=\'coupon\'','discount_published=1','discount_auto_load=1');
				hikashop_addACLFilters($filters,'discount_access');
				$query = 'SELECT * FROM '.hikashop_table('discount').' WHERE '.implode(' AND ',$filters).' ORDER BY discount_minimum_order DESC, discount_minimum_products DESC';
				$this->database->setQuery($query);
				$coupons = $this->database->loadObjectList();
				if(!empty($coupons)){
					$discount = hikashop_get('class.discount');
					$zoneClass = hikashop_get('class.zone');
					$zones = $zoneClass->getZoneParents($zone_id);
					foreach($coupons as $coupon){
						$result = $discount->check($coupon,$cart->total,$zones,$cart->products,false);
						if($result){
							$auto_coupon_key = sha1($zone_id.'_'.serialize($cart->products));
							$app->setUserState( HIKASHOP_COMPONENT.'.auto_coupon_key',$auto_coupon_key);
							$app->setUserState( HIKASHOP_COMPONENT.'.coupon_code','');
							$this->update($coupon->discount_code,1,0,'coupon',true);
							return $this->loadFullCart($addtionalInfos);
						}
					}
				}
			}
			if(!empty($this->cart->cart_coupon)){
				$zoneClass = hikashop_get('class.zone');
				$zones = $zoneClass->getZoneParents($zone_id);
				$cart->coupon = $discount->loadAndCheck($this->cart->cart_coupon,$cart->total,$zones,$cart->products,true);
				if(empty($cart->coupon)){
					$this->cart->cart_coupon='';
				}else{
					$cart->full_total=&$cart->coupon->total;
				}
			}
			if(bccomp($cart->full_total->prices[0]->price_value_with_tax,0,5)<=0){
				$cart->full_total->prices[0]->price_value_with_tax = 0;
				$cart->full_total->prices[0]->price_value = 0;
			}
			$shipping_id = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_id');
			if(!empty($shipping_id)){
				$cart->shipping = $app->getUserState( HIKASHOP_COMPONENT.'.shipping_data');
				if(!empty($cart->shipping)){
					$currencyClass = hikashop_get('class.currency');
					$shipping =& $cart->shipping;
					$shippings = array(&$shipping);
					$currencyClass->processShippings($shippings);
					$currencyClass->addShipping($cart->shipping,$cart->full_total);
					$cart->full_total=&$cart->shipping->total;
				}
			}
			if(bccomp($cart->full_total->prices[0]->price_value_with_tax,0,5)<=0){
				$cart->full_total->prices[0]->price_value_with_tax = 0;
				$cart->full_total->prices[0]->price_value = 0;
			}
		}
		if($addtionalInfos){
			$app =& JFactory::getApplication();
			$shipping_address=$app->getUserState( HIKASHOP_COMPONENT.'.shipping_address',0);
			if(!empty($shipping_address)){
				$this->loadAddress($cart,$shipping_address);
			}
			$billing_address=$app->getUserState( HIKASHOP_COMPONENT.'.billing_address',0);
			if($billing_address==$shipping_address){
				$cart->billing_address =& $cart->shipping_address;
			}else{
				if(!empty($billing_address)){
					$this->loadAddress($cart,$billing_address,'parent','billing');
				}
			}
			$this->calculateWeightAndVolume($cart);
		}
		return $cart;
	}
	function addCharacteristics(&$element,&$mainCharacteristics,&$characteristics){
		$element->characteristics = $mainCharacteristics[$element->product_id][0];
		if(is_array($element->characteristics) && count($element->characteristics)){
			foreach($element->characteristics as $k => $characteristic){
				if(!empty($mainCharacteristics[$element->product_id][$k])){
					$element->characteristics[$k]->default=end($mainCharacteristics[$element->product_id][$k]);
				}
			}
		}
		if(!empty($element->variants)){
			foreach($characteristics as $characteristic){
				foreach($element->variants as $k => $variant){
					if($variant->product_id==$characteristic->variant_product_id){
						$element->variants[$k]->characteristics[$characteristic->characteristic_parent_id]=$characteristic;
						$element->characteristics[$characteristic->characteristic_parent_id]->values[$characteristic->characteristic_id]=$characteristic;
					}
				}
			}
			foreach($element->variants as $j => $variant){
				$chars = array();
				if(!empty($variant->characteristics)){
					foreach($variant->characteristics as $k => $val){
						$i = 0;
						$ordering = @$element->characteristics[$val->characteristic_parent_id]->ordering;
						while(isset($chars[$ordering])&& $i < 30){
							$i++;
							$ordering++;
						}
						$chars[$ordering] = $val;
					}
				}
				ksort($chars);
				$element->variants[$j]->characteristics=$chars;
			}
		}
	}
	function loadAddress(&$order,$address,$loading_type='parent',$address_type='shipping'){
		$addressClass=hikashop_get('class.address');
		$name = $address_type.'_address';
		$order->$name=$addressClass->get($address);
		if(!empty($order->$name)){
			$array = array(&$order->$name);
			$addressClass->loadZone($array,$loading_type);
			if(!empty($addressClass->fields)){
				$order->fields =& $addressClass->fields;
			}
		}
	}
	function calculateWeightAndVolume(&$order){
		$order->volume = 0;
		$order->weight = 0;
		if(!empty($order->products)){
			$volumeClass=hikashop_get('helper.volume');
			$weightClass=hikashop_get('helper.weight');
			foreach($order->products as $k => $product){
				if(!empty($order->products[$k]->cart_product_quantity) && bccomp($product->product_length,0,5) && bccomp($product->product_width,0,5)&& bccomp($product->product_height,0,5)){
					$order->products[$k]->product_volume=$product->product_length*$product->product_width*$product->product_height;
					$order->products[$k]->product_total_volume=$order->products[$k]->product_volume*$order->products[$k]->cart_product_quantity;
					$order->products[$k]->product_total_volume_orig = $order->products[$k]->product_total_volume;
					$order->products[$k]->product_total_volume = $volumeClass->convert($order->products[$k]->product_total_volume,$product->product_dimension_unit);
					$order->volume+=$order->products[$k]->product_total_volume;
				}
			}
			foreach($order->products as $k => $product){
				if(!empty($order->products[$k]->cart_product_quantity) && bccomp($product->product_weight,0,5)){
					$order->products[$k]->product_weight_orig = $product->product_weight;
					$order->products[$k]->product_weight=$weightClass->convert($product->product_weight,$product->product_weight_unit);
					$order->weight+=$order->products[$k]->product_weight*$order->products[$k]->cart_product_quantity;
				}
			}
		}
	}
	function delete($id){
		$result = parent::delete($id);
		if($result){
			$app =& JFactory::getApplication();
			$app->setUserState( HIKASHOP_COMPONENT.'.cart_id', 0);
			$this->loadCart(0,true);
		}
		return $result;
	}
}
