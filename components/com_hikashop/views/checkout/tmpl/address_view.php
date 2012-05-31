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
global $Itemid;
$url_itemid='';
if(!empty($Itemid)){
	$url_itemid='&Itemid='.$Itemid;
}
$same_address = false;
if($this->shipping_address==$this->billing_address){
	$same_address = true;
}
if(!empty($this->addresses)){
?>
<table>
<?php
		$varname = $this->type.'_address';
		if(empty($this->$varname) || !isset($this->addresses[$this->$varname])){
			$first = reset($this->addresses);
			$this->$varname = $first->address_id;
		}
		$done = false;
		$nb_addresses = count($this->addresses);
		$k=0;
		foreach($this->addresses as $address){
			$this->address =& $address;
			$checked = '';
			if(($this->$varname==$address->address_id)||(empty($address->address_id) && !$done)){
				$checked = 'checked="checked"';
				$done = true;
			}
			if($this->config->get('auto_submit_methods',1)){
				$checked.=' onclick="this.form.submit(); return false;"';
			}
	?>
	<tr class="<?php echo "row$k"; ?>">
		<td>
			<input id="hikashop_checkout_<?php echo $this->type;?>_address_radio_<?php echo $address->address_id;?>" class="hikashop_checkout_<?php echo $this->type;?>_address_radio" type="radio" name="hikashop_address_<?php echo $this->type;?>" value="<?php echo $address->address_id;?>" <?php echo $checked; ?> />
		</td>
		<td><label for="hikashop_checkout_<?php echo $this->type;?>_address_radio_<?php echo $address->address_id;?>" style="cursor:pointer;">
			<span class="hikashop_checkout_<?php echo $this->type;?>_address_info">
	<?php
			$params = null;
			$js = '';
			$html = hikashop_getLayout('address','address_template',$params,$js);
			foreach($this->fields as $field){
				$fieldname = $field->field_namekey;
				$html=str_replace('{'.$fieldname.'}',$this->fieldsClass->show($field,$address->$fieldname),$html);
			}
			echo str_replace("\n","<br/>\n",str_replace("\n\n","\n",preg_replace('#{(?:(?!}).)*}#i','',$html)));
	?>
			</span>
			</label>
		</td>
		<td>
			<span class="hikashop_checkout_<?php echo $this->type;?>_address_buttons">
				<?php if($nb_addresses>1){ ?>
					<a title="<?php echo JText::_('HIKA_DELETE'); ?>" class="hikashop_checkout_<?php echo $this->type;?>_address_delete" href="<?php echo hikashop_completeLink('checkout&step='.$this->step.'&redirect=checkout&task=deleteaddress&address_id='.$address->address_id.'&'.JUtility::getToken().'=1'.$url_itemid);?>"><img alt="<?php echo JText::_('HIKA_DELETE'); ?>" src="<?php echo HIKASHOP_IMAGES; ?>delete.png" border="0" /></a>
				<?php }
				?>
				<a title="<?php echo JText::_('HIKA_EDIT'); ?>" class="modal hikashop_checkout_<?php echo $this->type;?>_address_edit" rel="{handler: 'iframe', size: {x: 450, y: 480}}" href="<?php echo hikashop_completeLink('address&task=edit&redirect=checkout&address_id='.$address->address_id.'&step='.$this->step.'&type='.$this->type.$url_itemid,true);?>" onclick="return hikashopEditAddress(this,<?php echo (int)$same_address?>,false);"><img alt="<?php echo JText::_('HIKA_EDIT'); ?>" src="<?php echo HIKASHOP_IMAGES; ?>edit.png" border="0" /></a>
			</span>
		</td>
	</tr>
	<?php
			$k = 1-$k;
		}
?>
</table>
<?php
}
?>
<span id="hikashop_checkout_<?php echo $this->type;?>_address_new" class="hikashop_checkout_<?php echo $this->type;?>_address_new">
	<?php $html = $this->cart->displayButton(JText::_('HIKA_NEW'),'new',$this->params,'','var link = document.getElementById(\'hikashop_checkout_'. $this->type.'_address_new_link\'); if(link) return hikashopEditAddress(link,'.(int)$same_address.',true); return false;');
	if(strpos($html,'<a')!==false) echo $html; ?>
	<a id="hikashop_checkout_<?php echo $this->type;?>_address_new_link" rel="{handler: 'iframe', size: {x: 450, y: 480}}" href="<?php echo hikashop_completeLink('address&redirect=checkout&task=add&step='.$this->step.'&type='.$this->type.$url_itemid,true);?>" onclick="return hikashopEditAddress(this,<?php echo (int)$same_address; ?>,true);">
		<?php if(strpos($html,'<a')===false) echo $html; ?>
	</a>
</span>