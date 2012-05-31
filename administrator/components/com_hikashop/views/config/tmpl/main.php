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
<div id="page-main">
	<fieldset class="adminform">
		<table width="100%">
			<tr>
				<td valign="top">
			<table class="admintable" cellspacing="1">
				<tr>
					<td class="key">
						<?php echo JText::_('PUT_STORE_OFFLINE'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "config[store_offline]",'',$this->config->get('store_offline',0)); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('STORE_ADDRESS'); ?>
					</td>
					<td>
						<textarea class="inputbox" name="config_store_address" cols="30" rows="5"><?php echo $this->config->get('store_address'); ?></textarea>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('ZONE_TAX_ADDRESS_TYPE'); ?>
					</td>
					<td>
						<?php echo $this->tax_zone->display('config[tax_zone_type]',$this->config->get('tax_zone_type')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('APPLY_DISCOUNTS'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "config[discount_before_tax]",'',$this->config->get('discount_before_tax'),JTEXT::_('BEFORE_TAXES'),JTEXT::_('AFTER_TAXES')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('DEFAULT_ADDRESS_TYPE'); ?>
					</td>
					<td>
						<?php echo $this->tax->display('config[default_type]',$this->config->get('default_type')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('MAIN_TAX_ZONE'); ?>
					</td>
					<td>
						<span id="zone_id" >
							<?php echo (int)@$this->zone->zone_id.' '.@$this->zone->zone_name_english; ?>
							<input type="hidden" name="config[main_tax_zone]" value="<?php echo @$this->zone->zone_id; ?>" />
						</span>
						<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("zone&task=selectchildlisting&type=config",true ); ?>">
							<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
						</a>
						<a href="#" onclick="document.getElementById('zone_id').innerHTML='0 <?php echo $this->escape(JText::_('ZONE_NOT_FOUND'));?>';return false;" >
							<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
						</a>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('VAT_CHECK'); ?>
					</td>
					<td>
						<?php echo $this->vat->display('config[vat_check]',$this->config->get('vat_check')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('MAIN_CURRENCY'); ?>
					</td>
					<td>
						<?php echo $this->currency->display('config[main_currency]',$this->config->get('main_currency')); ?>
						<a href="<?php echo hikashop_completeLink('currency');?>">
							<img src="<?php echo HIKASHOP_IMAGES.'go.png';?>" title="Go to the currencies management" alt="Go to the currencies management"/>
						</a>
					</td>
				</tr>
				<?php if($this->rates_active){?>
				<tr>
					<td class="key" >
						<?php echo JText::_('RATES_REFRESH_FREQUENCY'); ?>
					</td>
					<td>
						<?php echo $this->delayTypeRates->display('params[hikashop][rates][frequency]',@$this->rates_params['frequency'],3); ?>
					</td>
				</tr>
				<?php }?>
				<tr>
					<td class="key" >
						<?php echo JText::_('ORDER_NUMBER_FORMAT'); ?>
					</td>
					<td>
						<?php
						if(hikashop_level(1)){ ?>
							<input class="inputbox" type="text" name="config[order_number_format]" value="<?php echo $this->escape($this->config->get('order_number_format','{automatic_code}')); ?>">
						<?php }else{
							echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
						}?>
					</td>
				</tr>
			</table>
				</td>
				<td valign="top">
			<table class="admintable" cellspacing="1">
				<tr>
					<td class="key">
							<?php echo JText::_('DEFAULT_ORDER_STATUS'); ?>
					</td>
					<td>
						<?php echo $this->order_status->display('config[order_created_status]',$this->config->get('order_created_status')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
							<?php echo JText::_('CONFIRMED_ORDER_STATUS'); ?>
					</td>
					<td>
						<?php echo $this->order_status->display('config[order_confirmed_status]',$this->config->get('order_confirmed_status')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('CANCELLED_ORDER_STATUS'); ?>
					</td>
					<td>
						<input id="cancelled_order_status" name="config[cancelled_order_status]" value="<?php echo @$this->config->get('cancelled_order_status'); ?>" />
						<a id="link_cancelled_order_status" class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("category&task=selectstatus&control=cancelled_order_status&values=".$this->config->get('cancelled_order_status'),true ); ?>">
							<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
						</a>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('CANCELLABLE_ORDER_STATUS'); ?>
					</td>
					<td>
						<input id="cancellable_order_status" name="config[cancellable_order_status]" value="<?php echo @$this->config->get('cancellable_order_status'); ?>" />
						<a id="link_cancellable_order_status" class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("category&task=selectstatus&control=cancellable_order_status&values=".$this->config->get('cancellable_order_status'),true ); ?>">
							<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
						</a>
					</td>
				</tr>
				<tr>
					<td class="key" >
						<?php echo JText::_('CART_RETAINING_PERIOD'); ?>
					</td>
					<td>
						<?php echo $this->delayTypeRetaining->display('config[cart_retaining_period]',$this->config->get('cart_retaining_period',2592000)); ?>
					</td>
				</tr>
				<tr>
					<td class="key" >
						<?php echo JText::_('CART_RETAINING_PERIOD_CHECK_FREQUENCY'); ?>
					</td>
					<td>
						<?php echo $this->delayTypeCarts->display('config[cart_retaining_period_check_frequency]',$this->config->get('cart_retaining_period_check_frequency',86400));?><br/>
						<?php echo JText::sprintf('LAST_CHECK',hikashop_getDate($this->config->get('cart_retaining_period_checked')));?>
					</td>
				</tr>
				<tr>
					<td class="key" >
						<?php echo JText::_('HIKA_EDITOR'); ?>
					</td>
					<td>
						<?php echo $this->elements->editor;?>
					</td>
				</tr>
				<tr>
					<td class="key" >
						<?php echo JText::_('READ_MORE'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "config[readmore]",'',$this->config->get('readmore')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('DIMENSION_SYMBOLS'); ?>
					</td>
					<td>
						<input class="inputbox" type="text" name="config[volume_symbols]" value="<?php echo $this->config->get('volume_symbols'); ?>">
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('WEIGHT_SYMBOLS'); ?>
					</td>
					<td>
						<input class="inputbox" type="text" name="config[weight_symbols]" value="<?php echo $this->config->get('weight_symbols'); ?>">
					</td>
				</tr>
				<tr>
					<td class="key" >
					<?php echo JText::_('DEFAULT_VARIANT_PUBLISH'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "config[variant_default_publish]" , '',$this->config->get('variant_default_publish',1) );?>
					</td>
				</tr>
			</table>
				<table>
					<tr>
						<td>
							<fieldset class="adminform">
								<legend><?php echo JText::_( 'SEF_URL_OPTIONS' ); ?></legend>
								<?php
									$sefOptions='';
									if($this->config->get('activate_sef',1)==0){
										$sefOptions='style="display:none"';
									}
								?>
								<table class="admintable" cellspacing="1" width="100%">
									<tr>
										<td class="key">
											<?php echo JText::_('ACTIVATE_SMALLER_URL'); ?>
										</td>
										<td>
											<?php echo JHTML::_('select.booleanlist', "config[activate_sef]",'onclick="setVisible(this.value);"',$this->config->get('activate_sef',1)); ?>
										</td>
									</tr>
									<tr id="sef_cat_name" <?php echo $sefOptions; ?>>
										<td class="key">
											<?php echo JText::_('CATEGORY_LISTING_SEF_NAME'); ?>
										</td>
										<td>
											<input class="inputbox" type="text" name="config[category_sef_name]" value="<?php echo $this->config->get('category_sef_name', 'category'); ?>">
										</td>
									</tr>
									<tr id="sef_prod_name" <?php echo $sefOptions; ?>>
										<td class="key">
											<?php echo JText::_('PRODUCT_SHOW_SEF_NAME'); ?>
										</td>
										<td>
											<input class="inputbox" type="text" name="config[product_sef_name]" value="<?php echo $this->config->get('product_sef_name', 'product'); ?>">
										</td>
									</tr>
								</table>
							</fieldset>
						</td>
					</tr>
					<tr></tr>
				</table>
				</td>
			</tr>
		</table>
	</fieldset>
</div>