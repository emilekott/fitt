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
if(bccomp($this->orderInfos->full_total->prices[0]->price_value_with_tax,0,5)!=0){
	if(!empty($this->methods)){ ?>
<div id="hikashop_payment_methods" class="hikashop_payment_methods">
	<fieldset>
		<legend><?php echo JText::_('HIKASHOP_PAYMENT_METHOD');?></legend>
		<table class="hikashop_payment_methods_table">
			<?php
			$done = false;
			$row_index=0;
			$auto_select_default = $this->config->get('auto_select_default',2);
			if($auto_select_default==1 && count($this->methods)>1) $auto_select_default=0;
			$odd = 0;
			foreach($this->methods as $method){
				$checked = '';
				if(($this->payment_method==$method->payment_type && $this->payment_id==$method->payment_id)|| ($auto_select_default && empty($this->payment_id)&&!$done)){
					$checked = 'checked="checked"';
					$done = true;
				}
				if($this->config->get('auto_submit_methods',1) && empty($method->ask_cc) && empty($method->custom_html) && empty($checked)){
					$checked.=' onclick="this.form.submit(); return false;"';
				}
			?>
			<tr class="row<?php echo $odd; ?>">
				<td>
					<input class="hikashop_checkout_payment_radio" id="radio_<?php echo $method->payment_type.'_'.$method->payment_id;?>" type="radio" name="hikashop_payment" value="<?php echo $method->payment_type.'_'.$method->payment_id;?>" <?php echo $checked; ?> />
				</td>
				<td><label for="radio_<?php echo $method->payment_type.'_'.$method->payment_id;?>" style="cursor:pointer;">
					<span class="hikashop_checkout_payment_image">
					<?php
						if(!empty($method->payment_images)){
							$images = explode(',',$method->payment_images);
							if(!empty($images)){
								foreach($images as $image){
									if(!empty($this->images_payment[$image])){
										?>
										<img src="<?php echo HIKASHOP_IMAGES .'payment/'. $this->images_payment[$image];?>" alt=""/>
										<?php
									}
								}
							}
						}
					?>
					</span>
					</label>
				</td>
				<td><label for="radio_<?php echo $method->payment_type.'_'.$method->payment_id;?>" style="cursor:pointer;">
					<span class="hikashop_checkout_payment_name"><?php echo $method->payment_name;?></span></label>
					<?php if(!empty($method->payment_description)){ ?>
					<br/>
					<div class="hikashop_checkout_payment_description"><?php echo $method->payment_description;?></div>
					<?php }?>
				</td>
			</tr>
			<tr>
				<td colspan="3">
					<?php
						$this->method =& $method;
						$this->setLayout('ccinfo');
						echo $this->loadTemplate();
					?>
				</td>
			</tr>
			<?php $row_index++;
				$odd = 1-$odd;
			}
			 ?>
		</table>
	</fieldset>
</div>
<?php }
}