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
<div id="page-checkout">
	<fieldset class="adminform">
		<table width="100%">
			<tr>
				<td valign="top">
			<table class="admintable" cellspacing="1">
				<tr>
					<td class="key">
						<?php echo JText::_('CHECKOUT_FLOW'); ?>
					</td>
					<td>
<?php
	$checkoutlist = array('login','address','shipping','payment','coupon','cart','status','fields','terms');
?>
<style>
#checkoutTrash { float: right; padding: 2px; border: 1px solid black; width: 80px; height: 50px; }
.checkoutElem { float: left; cursor: move; position: relative; width: 70px; margin: 4px; border: 1px solid #555; background-color: #EEE; text-align: center; }
#workflow_zones { padding-top: 2px; }
.workflow_zone { border: 1px solid #000; margin-bottom: 5px; }
.workflow_zone .drop { width: 100%; height: 20px; clear: both; }
.workflow_pos { width: 12px; }
</style>
						<textarea class="inputbox" name="config[checkout]" id="TextCheckoutWorkFlow" cols="30" rows="5"><?php echo $this->config->get('checkout'); ?></textarea>
						<div id="CheckoutWorkflow" style="display:none;">
							<div id="checkoutelements">
								<div id="checkoutTrash"></div>
							<?php
							foreach($checkoutlist as $c) {
								echo '<div class="checkoutElem" rel="'.$c.'">'.JText::_('HIKASHOP_CHECKOUT_'.strtoupper($c)).'</div>';
							}
							?>
								<div style="clear: both;"></div>
							</div>
							<div id="workflow_zones">
									<div id="workflow_zone_tpl" class="workflow_zone" style="display:none;">
									<div class="drop">
									</div>
									<div style="clear: left;"></div>
								</div>
<?php
	$workflow = explode(',', $this->config->get('checkout'));
	$i = 0;
	$initSortableJS = array('#checkoutTrash');
	foreach($workflow as $flow) {
		if( $flow != 'end') {
			echo '<div class="workflow_zone"><div class="drop" id="workflow_drop_'.$i.'">';
			$initSortableJS[] = '#workflow_drop_'.$i;
			$i++;
			$flow = explode('_', $flow);
			foreach($flow as $f) {
				if( in_array($f, $checkoutlist) ) {
					echo '<div class="checkoutElem" rel="'.$f.'">'. JText::_('HIKASHOP_CHECKOUT_'.strtoupper($f)).'</div>';
				}
			}
			echo '</div><div style="clear: left;"></div></div>';
		}
	}
?>
							</div>
							<a href="#" onclick="return checkoutAddStep();"><?php echo JText::_('HIKASHOP_CHECKOUT_ADD_STEP'); ?></a>
							<?php if($this->config->get('checkout_workflow_edition',1)){ ?>
<script type="text/javascript">
var sortables;
if( MooTools.version != '1.12') {
	window.addEvent('domready', function(){
		$$('#checkoutelements .checkoutElem').addEvent('mousedown', function(event){
			event.stop();
			var step = this;
			var clone = step.clone().setStyles(step.getCoordinates()).setStyles({
				opacity: 0.7,
				position: 'absolute'
			}).inject(document.body);
			var drag = new Drag.Move(clone, {
				droppables: $$('#workflow_zones .workflow_zone'),
				onDrop : function(dragging, zone) {
					dragging.destroy();
					if( zone != null ) {
						var dropZone = zone.getChildren('.drop');
						var insertedElem = step.clone();
						dropZone[0].adopt( insertedElem );
						sortables.addItems( insertedElem );
						zone.highlight('#7590BB', '#ffffff');
						setTimeout( function() { checkoutExport(); }, 100);
					}
				},
				onEnter: function(dragging, zone) {
				},
				onLeave: function(dragging, zone) {
				},
				onCancel: function(dragging) {
					dragging.destroy();
				}
			});
			drag.start(event);
		});
		$('TextCheckoutWorkFlow').setStyle('display','none');
		$('CheckoutWorkflow').setStyle('display','');
		sortables = new Sortables("<?php echo implode(',', $initSortableJS); ?>", {
			clone: true,
			opacity: 0.7,
			revert: true,
			onStart: function(element,clone) {
			},
			onComplete: function(element) {
				var trashElements = $('checkoutTrash').getChildren();
				sortables.removeItems(trashElements).destroy();
				setTimeout( function() { checkoutExport(); }, 800);
			}
		});
	});
	function checkoutAddStep() {
		var clone = $('workflow_zone_tpl').clone().setStyle('display','').inject( $('workflow_zones') );
		var sortZone = clone.getChildren('.drop');
		sortables.addLists( sortZone );
		return false;
	}
	function checkoutExport() {
		var data = sortables.serialize(false, function(element, index) {
			var rel = element.getProperty('rel');
			if( rel )
				return rel;
			return '';
		}).join(';');
		data = data.replace(/,/g,'_');
		data = data.replace(/;/g,',');
		while( data.substring(0,1) == ',' )
			data = data.substring(1);
		data += '_confirm,end';
		while(data.indexOf(',,') >= 0)
			data = data.replace(/,,/g,',');
		data = data.replace(/,_confirm/g,'_confirm');
		$('TextCheckoutWorkFlow').set('value', data);
	}
}
</script>
						<?php } ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="key">
                        <?php echo JText::_('CHECKOUT_WORKFLOW_EDITION'); ?>
                    </td>
                    <td>
                        <?php echo JHTML::_('select.booleanlist', "config[checkout_workflow_edition]",'onclick="task = document.getElementById(\'config_form_task\');if(task) task.value=\'apply\'; this.form.submit();"',$this->config->get('checkout_workflow_edition',1)); ?>
                    </td>
                </tr>
				<tr>
					<td class="key">
						<?php echo JText::_('CHECKOUT_FORCE_SSL'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "config[force_ssl]",'',$this->config->get('force_ssl',0)); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
							<?php echo JText::_('DISPLAY_CHECKOUT_BAR'); ?>
					</td>
					<td>
						<?php echo $this->checkout->display('config[display_checkout_bar]',$this->config->get('display_checkout_bar')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('CHECKOUT_SHOW_CART_DELETE'); ?>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', 'config[checkout_cart_delete]','',$this->config->get('checkout_cart_delete'));?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('FORCE_MENU_ON_CHECKOUT'); ?>
					</td>
					<td>
						<?php echo $this->elements->hikashop_menu;?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('HIKA_LOGIN'); ?>
					</td>
					<td>
						<?php 	echo JHTML::_('select.booleanlist', 'config[display_login]','',$this->config->get('display_login',1)); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('HIKA_REGISTRATION'); ?>
					</td>
					<td>
						<?php if(hikashop_level(1)){
							echo $this->registration->display( 'config[simplified_registration]',$this->config->get('simplified_registration'));
						}else{
							echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
						}	 ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('AUTO_SELECT_DEFAULT_SHIPPING_AND_PAYMENT'); ?>
					</td>
					<td>
						<?php echo $this->auto_select->display('config[auto_select_default]',$this->config->get('auto_select_default',2)); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('AUTO_SUBMIT_SHIPPING_AND_PAYMENT_SELECTION'); ?>
					</td>
					<td>
						<?php 	echo JHTML::_('select.booleanlist', 'config[auto_submit_methods]','',$this->config->get('auto_submit_methods',1)); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('FORCE_SHIPPING_REGARDLESS_OF_WEIGHT'); ?>
					</td>
					<td>
						<?php 	echo JHTML::_('select.booleanlist', 'config[force_shipping]','',$this->config->get('force_shipping',0)); ?>
					</td>
				</tr>
				<tr>
					<td class="key" >
						<?php echo JText::_('HIKASHOP_CHECKOUT_TERMS'); ?>
					</td>
					<td>
						<input class="inputbox" id="checkout_terms" name="config[checkout_terms]" type="text" size="20" value="<?php echo $this->config->get('checkout_terms'); ?>">
						<?php
						if(version_compare(JVERSION,'1.6','<')){
							$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object=checkout';
							$js = "
							function jSelectArticle(id, title, object) {
								document.getElementById(object+'_terms').value = id;
								try{	window.top.document.getElementById('sbox-window').close(); }catch(err){ window.top.SqueezeBox.close(); }
							}";
							$doc =& JFactory::getDocument();
							$doc->addScriptDeclaration($js);
						}else{
							$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;object=content&amp;function=jSelectArticle_checkout';
							$js = "
							function jSelectArticle_checkout(id, title, catid, object) {
								document.getElementById('checkout_terms').value = id;
								SqueezeBox.close();
							}";
							$doc =& JFactory::getDocument();
							$doc->addScriptDeclaration($js);
						}
						?>
						<a class="modal" id="checkout_terms_link" title="<?php echo JText::_('Select one article which will be displayed for the Terms & Conditions'); ?>"  href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}"><button onclick="return false"><?php echo JText::_('Select'); ?></button></a>
					</td>
				</tr>
			</table>
		</td>
		<td valign="top">
			<table class="admintable" cellspacing="1">
				<tr>
					<td class="key">
						<?php echo JText::_('CONTINUE_SHOPPING_BUTTON_URL');?>
					</td>
					<td>
						<input name="config[continue_shopping]" type="text" value="<?php echo $this->config->get('continue_shopping');?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('AFTER_ADD_TO_CART'); ?>
					</td>
					<td>
						<?php echo $this->cart_redirect->display('config[redirect_url_after_add_cart]',$this->config->get('redirect_url_after_add_cart'));?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('USE_AJAX_WHEN_POSSIBLE_FOR_ADD_TO_CART'); ?>
					</td>
					<td>
						<?php 	echo JHTML::_('select.booleanlist', 'config[ajax_add_to_cart]','',$this->config->get('ajax_add_to_cart',0)); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('NOTICE_POPUP_DISPLAY_TIME'); ?>
					</td>
					<td>
						<input size="10" name="config[popup_display_time]" value="<?php echo (int)$this->config->get('popup_display_time',2000);?>"/>ms
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('WHEN_CART_IS_EMPTY'); ?>
					</td>
					<td>
						<input name="config[redirect_url_when_cart_is_empty]" value="<?php echo $this->escape($this->config->get('redirect_url_when_cart_is_empty'));?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('LIMIT_NUMBER_OF_ITEMS_IN_CART'); ?>
					</td>
					<td>
						<?php
						if(hikashop_level(1)){
							$item_limit = $this->config->get('cart_item_limit',0);
							if(empty($item_limit)){
								$item_limit = JText::_('UNLIMITED');
							}
							?>
							<input name="config[cart_item_limit]" type="text" value="<?php echo $item_limit;?>" onfocus="if(this.value=='<?php echo JText::_('UNLIMITED',true); ?>') this.value='';" />
						<?php }else{
							echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
						} ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('ALLOW_USERS_TO_PRINT_CART'); ?>
					</td>
					<td>
						<?php 	echo JHTML::_('select.booleanlist', 'config[print_cart]','',$this->config->get('print_cart',0)); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('GROUP_OPTIONS_WITH_PRODUCT'); ?>
					</td>
					<td>
						<?php 	echo JHTML::_('select.booleanlist', 'config[group_options]','',$this->config->get('group_options',0)); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('SHOW_IMAGE'); ?>
					</td>
					<td>
						<?php 	echo JHTML::_('select.booleanlist', 'config[show_cart_image]','',$this->config->get('show_cart_image')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('CLEAN_CART_WHEN_ORDER_IS'); ?>
					</td>
					<td>
						<?php
						$values = array();
						$values[] = JHTML::_('select.option', 'order_created',JText::_('CREATED'));
						$values[] = JHTML::_('select.option', 'order_confirmed',JText::_('CONFIRMED'));
						echo JHTML::_('select.genericlist',   $values, 'config[clean_cart]', 'class="inputbox" size="1"', 'value', 'text', $this->config->get('clean_cart','order_created') );  ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('ALLOW_CUSTOMERS_TO_PAY_ORDERS_AFTERWARD'); ?>
					</td>
					<td>
						<?php if(hikashop_level(1)){
							echo JHTML::_('select.booleanlist', 'config[allow_payment_button]','',$this->config->get('allow_payment_button'));
						}else{
							echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
						} ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('ALLOW_CUSTOMERS_TO_CHANGE_THEIR_PAYMENT_METHOD_AFTER_CHECKOUT'); ?>
					</td>
					<td>
						<?php if(hikashop_level(1)){
							echo JHTML::_('select.booleanlist', 'config[allow_payment_change]','',$this->config->get('allow_payment_change',1));
						}else{
							echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
						} ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('BUSINESS_HOURS'); ?>
					</td>
					<td>
						<?php
						if(hikashop_level(1)){
							$hours = array();
							for($i=0;$i<24;$i++) $hours[]=JHTML::_('select.option', $i,$i);
							$minutes = array();
							for($i=0;$i<60;$i++) $minutes[]=JHTML::_('select.option', $i,$i);
							echo JText::_('OPENS_AT').JHTML::_('select.genericlist',   $hours, "config[store_open_hour]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('store_open_hour',0) ); ?><?php echo JText::_('HOURS');
							echo JHTML::_('select.genericlist',   $minutes, "config[store_open_minute]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('store_open_minute',0) ); ?><?php echo JText::_('HIKA_MINUTES');
							echo '<br/>'.JText::_('CLOSES_AT').JHTML::_('select.genericlist',   $hours, "config[store_close_hour]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('store_close_hour',0) ); ?><?php echo JText::_('HOURS');
							echo JHTML::_('select.genericlist',   $minutes, "config[store_close_minute]", 'class="inputbox" size="1"', 'value', 'text', $this->config->get('store_close_minute',0) ); ?><?php echo JText::_('HIKA_MINUTES');
						}else{
							echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
						} ?>
					</td>
				</tr>
				</table>
				</td>
			</tr>
		</table>
	</fieldset>
</div>