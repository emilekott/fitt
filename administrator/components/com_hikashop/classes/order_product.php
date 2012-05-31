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
class hikashopOrder_productClass extends hikashopClass{
	var $tables = array('order_product');
	var $pkeys = array('order_product_id');
	function save(&$products){
		if(!empty($products)){
			$items = array();
			$updates = array();
			$discounts = array();
			$fields = array('order_id,product_id','order_product_quantity','order_product_name','order_product_code','order_product_price','order_product_tax','order_product_options','order_product_option_parent_id');
			if(hikashop_level(2)){
				$element=null;
				$fieldsClass = hikashop_get('class.field');
				$itemFields = $fieldsClass->getFields('frontcomp',$element,'item');
				if(!empty($itemFields)){
					foreach($itemFields as $field){
						if($field->field_type=='customtext') continue;
						$fields[]=$field->field_namekey;
					}
				}
			}
			$order_id = 0;
			$class = hikashop_get('class.product');
			foreach($products as $product){
				$order_id = (int)$product->order_id;
				$line = array($order_id,(int)$product->product_id,(int)$product->order_product_quantity,$this->database->Quote($product->order_product_name),$this->database->Quote($product->order_product_code),$this->database->Quote($product->order_product_price),$this->database->Quote($product->order_product_tax),$this->database->Quote($product->order_product_options),(int)$product->cart_product_id);
				if(!empty($itemFields)){
					foreach($itemFields as $field){
						$namekey=$field->field_namekey;
						if($field->field_type=='customtext') continue;
						$line[]=$this->database->Quote(@$product->$namekey);
					}
				}
				$items[] = '('.implode(',',$line).')';
				$updates[$product->order_product_quantity][] = (int)$product->product_id;
				$prod=$class->get((int)$product->product_id);
				if($prod->product_type=='variant' && !empty($prod->product_parent_id)) $updates[$product->order_product_quantity][] = (int)$prod->product_parent_id;
				if(!empty($product->discount)){
					if(empty($discounts[$product->discount->discount_code])){
						$discounts[$product->discount->discount_code] = 0;
					}
					$discounts[$product->discount->discount_code] += (int)$product->order_product_quantity;
				}
			}
			foreach($updates as $k => $update){
				$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = product_quantity - '.(int)$k.' WHERE product_id IN ('.implode(',',$update).') AND product_quantity > -1';
				$this->database->setQuery($query);
				$this->database->query();
				$query = 'UPDATE '.hikashop_table('product').' SET product_sales = product_sales + '.(int)$k.' WHERE product_id IN ('.implode(',',$update).')';
				$this->database->setQuery($query);
				$this->database->query();
			}
			$query='INSERT IGNORE INTO '.hikashop_table('order_product').' ('.implode(',',$fields).') VALUES '.implode(',',$items);
			$this->database->setQuery($query);
			$this->database->query();
			$this->database->setQuery('SELECT * FROM '.hikashop_table('order_product').' WHERE order_id='.$order_id);
			$newProducts = $this->database->loadObjectList('order_product_option_parent_id');
			$mainProducts = array();
			foreach($products as $product){
				if(!empty($product->cart_product_option_parent_id)){
					$mainProducts[$product->cart_product_option_parent_id][]=$product->cart_product_id;
				}
			}
			$keep = array();
			if(!empty($mainProducts)){
				foreach($mainProducts as $k => $v){
					$keep[]=(int)@$newProducts[$k]->order_product_id;
					$this->database->setQuery('UPDATE '.hikashop_table('order_product').' SET order_product_option_parent_id='.(int)@$newProducts[$k]->order_product_id.' WHERE order_product_option_parent_id IN ('.implode(',',$v).') AND order_id='.$order_id);
					$this->database->query();
				}
			}
			if(!empty($keep)){
				$keep = ' AND order_product_option_parent_id NOT IN ('.implode('',$keep).')';
			}else{
				$keep = '';
			}
			$this->database->setQuery('UPDATE '.hikashop_table('order_product').' SET order_product_option_parent_id=0 WHERE order_id='.$order_id.$keep);
			$this->database->query();
			if(!empty($discounts)){
				$discountUpdates = array();
				foreach($discounts as $code => $qty){
					$discountUpdates[$qty][]=$this->database->Quote($code);
				}
				foreach($discountUpdates as $k => $update){
					$query = 'UPDATE '.hikashop_table('discount').' SET discount_used_times = discount_used_times + '.(int)$k.' WHERE discount_code IN ('.implode(',',$update).')';
					$this->database->setQuery($query);
					$this->database->query();
				}
			}
		}
		return true;
	}
	function cancelProductReservation($order_id){
		$query='SELECT * FROM '.hikashop_table('order_product').' WHERE order_id='.(int)$order_id;
		$this->database->setQuery($query);
		$items = $this->database->loadObjectList();
		if(!empty($items)){
			$updates = array();
			$class = hikashop_get('class.product');
			foreach($items as $item){
				$updates[$item->order_product_quantity][] = (int)$item->product_id;
				$prod=$class->get((int)$item->product_id);
				if($prod->product_type=='variant' && !empty($prod->product_parent_id)) $updates[$item->order_product_quantity][] = (int)$prod->product_parent_id;
			}
			foreach($updates as $k => $update){
				$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = product_quantity + '.(int)$k.' WHERE product_id IN ('.implode(',',$update).') AND product_quantity > -1';
				$this->database->setQuery($query);
				$this->database->query();
				$query = 'UPDATE '.hikashop_table('product').' SET product_sales = product_sales - '.(int)$k.' WHERE product_id IN ('.implode(',',$update).') AND product_sales>0';
				$this->database->setQuery($query);
				$this->database->query();
			}
		}
	}
	function update(&$product){
		$old = $this->get($product->order_product_id);
		if(!empty($old->product_id) && $old->order_product_quantity!=$product->order_product_quantity){
			$k = $product->order_product_quantity-$old->order_product_quantity;
			$filters = array('product_id='.(int)$old->product_id);
			$class = hikashop_get('class.product');
			$prod=$class->get($product->product_id);
			if($prod->product_type=='variant' && !empty($prod->product_parent_id)){
				$filters[]='product_id='.(int)$prod->product_parent_id;
			}
			$query = 'UPDATE '.hikashop_table('product').' SET product_quantity = product_quantity - '.$k.' WHERE '.implode(' AND ',$filters).' AND product_quantity > -1';
			$this->database->setQuery($query);
			$this->database->query();
			$query = 'UPDATE '.hikashop_table('product').' SET product_sales = product_sales + '.$k.' WHERE '.implode(' AND ',$filters);
			$this->database->setQuery($query);
			$this->database->query();
		}
		if(empty($product->order_product_quantity)){
			return $this->delete($product->order_product_id);
		}
		$product->order_product_id = parent::save($product);
		return $product->order_product_id;
	}
}