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
<div id="page-modules">
	<table width="100%">
		<tr>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'GENERAL_DISPLAY_OPTIONS' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('BUTTON_STYLE'); ?>
							</td>
							<td>
								<?php echo $this->button->display('config[button_style]',$this->config->get('button_style')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('MENU_STYLE'); ?>
							</td>
							<td>
								<?php echo $this->menu_style->display('config[menu_style]',$this->config->get('menu_style')); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('HIKA_PAGINATION'); ?>
							</td>
							<td>
								<?php echo $this->paginationType->display('config[pagination]',$this->config->get('pagination','bottom'));?>
							</td>
						</tr>
						<?php
						$no_checked='';
						$jcomments_checked='';
						$jomcomment_checked='';
						switch($this->config->get('comments_feature')){
							default:
								$no_checked = ' checked="checked"';
								break;
							case 'jcomments':
								$jcomments_checked = ' checked="checked"';
								break;
							case 'jomcomment':
								$jomcomment_checked = ' checked="checked"';
								break;
						}
						$jcomments_disable='';
						if(!file_exists(HIKASHOP_ROOT.'components'.DS.'com_jcomments'.DS.'jcomments.php')){
							$jcomments_disable=' DISABLED';
						}
						$jom_comment_disable='';
						if(!file_exists(HIKASHOP_ROOT.'plugins'.DS.'content'.DS.'jom_comment_bot.php')){
							$jom_comment_disable=' DISABLED';
						}	 ?>
						<tr>
							<td class="key" >
							<?php echo JText::_('COMMENTS_ENABLED_ON_PRODUCTS'); ?>
							</td>
							<td>
								<input name="config[comments_feature]" id="config[comments_feature]" value=""<?php echo $no_checked;?> size="1" type="radio">
								<label for="config[comments_feature]"><?php echo JText::_('HIKASHOP_NO');?></label>
								<input name="config[comments_feature]" id="config[comments_feature]jcomments" value="jcomments"<?php echo $jcomments_checked;?> size="1" type="radio"<?php echo $jcomments_disable;?>>
								<label for="config[comments_feature]jcomments">jComments</label>
								<input name="config[comments_feature]" id="config[comments_feature]jomcomment" value="jomcomment"<?php echo $jomcomment_checked;?> size="1" type="radio"<?php echo $jom_comment_disable;?>>
								<label for="config[comments_feature]jomcomment">jomComment</label>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('PRODUCT_SHOW_MODULES'); ?>
							</td>
							<td>
								<input type="hidden" name="config[product_show_modules]'; ?>" id="menumodules"  value="<?php echo $this->config->get('product_show_modules'); ?>" />
								<a id="linkmenumodules" title="<?php echo JText::_('SELECT_MODULES'); ?>"  href="" rel="{handler: 'iframe', size: {x: 650, y: 375}}" onclick="this.href='<?php echo hikashop_completeLink('modules&task=selectmodules&control=menu&name=modules',true); ?>&modules='+document.getElementById('menumodules').value;SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
									<button onclick="return false"><?php echo JText::_('SELECT'); ?></button>
								</a><br/>
								<?php
										$modules = explode(',',$this->config->get('product_show_modules'));
										$modulesClass = hikashop_get('class.modules');
										foreach($modules as $module){
											$element = $modulesClass->get($module);
											if(!empty($element->title)){
												echo '<a href="'.hikashop_completeLink('modules&task=edit&cid[]='.@$element->id).'">'.JText::sprintf('OPTIONS_FOR_X',@$element->title).'</a><br/>';
											}
										}
								?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('CHARACTERISTICS_DISPLAY'); ?>
							</td>
							<td>
								<?php echo $this->characteristicdisplayType->display('config[characteristic_display]',$this->config->get('characteristic_display'));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('CHARACTERISTICS_VALUES_ORDER'); ?>
							</td>
							<td>
								<?php echo $this->characteristicorderType->display('config[characteristics_values_sorting]',$this->config->get('characteristics_values_sorting'));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('APPEND_CHARACTERISTICS_VALUE_TO_PRODUCT_NAME'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[append_characteristic_values_to_product_name]','',$this->config->get('append_characteristic_values_to_product_name',1));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('CHARACTERISTICS_DISPLAY_TEXT'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[characteristic_display_text]','',$this->config->get('characteristic_display_text'));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('DIMENSIONS_DISPLAY'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[dimensions_display]','',$this->config->get('dimensions_display',0));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('CATALOGUE_MODE'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[catalogue]','onchange="if(this.value==1) alert(\''.JText::_('CATALOGUE_MODE_WARNING',true).'\');"',$this->config->get('catalogue'));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('COMPARE_MODE'); ?>
							</td>
							<td>
								<?php if(hikashop_level(2)){
									echo $this->compare->display('config[show_compare]',$this->config->get('show_compare'));
								}else{
									echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
								} ?>
							</td>
						</tr>
						<?php if(hikashop_level(2)){ ?>
						<tr>
							<td class="key">
								<?php echo JText::_('COMPARE_LIMIT'); ?>
							</td>
							<td>
								<input type="text" name="config[compare_limit]" value="<?php echo $this->config->get('compare_limit','5'); ?>"/>
							</td>
						</tr>
						<?php } ?>
						<tr>
						<td class="key">
								<?php echo JText::_('DISPLAY_ADD_TO_CART_BUTTON_FOR_FREE_PRODUCT'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[display_add_to_cart_for_free_products]','',$this->config->get('display_add_to_cart_for_free_products'));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('QUANTITY_FIELD'); ?>
							</td>
							<td>
								<?php echo $this->quantity->display('config[show_quantity_field]',$this->config->get('show_quantity_field'));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('DISPLAY_CONTACT_BUTTON'); ?>
							</td>
							<td>
								<?php if(hikashop_level(1)){
									echo $this->contact->display('config[product_contact]',$this->config->get('product_contact',0));
								}else{
									echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
								} ?>
							</td>
						</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('PRINT_INVOICE_FRONTEND'); ?>
								</td>
								<td>
									<?php if(hikashop_level(1)){
										echo JHTML::_('select.booleanlist', 'config[print_invoice_frontend]','',$this->config->get('print_invoice_frontend'));
									}else{
										echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
									} ?>
								</td>
							</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('CATEGORY_EXPLORER'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[category_explorer]','',$this->config->get('category_explorer'));?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ACTIVATE_WAITLIST'); ?>
							</td>
							<td>
								<?php if(hikashop_level(1)){
									echo $this->waitlist->display('config[product_waitlist]',$this->config->get('product_waitlist',0));
								}else{
									echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
								} ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('WAITLIST_SUBSCRIBE_LIMIT'); ?>
							</td>
							<td>
								<?php if(hikashop_level(1)){
									?><input type="text" name="config[product_waitlist_sub_limit]" value="<?php echo $this->config->get('product_waitlist_sub_limit','20'); ?>"/><?php
								}else{
									echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
								} ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('WAITLIST_SEND_LIMIT'); ?>
							</td>
							<td>
								<?php if(hikashop_level(1)){
									?><input type="text" name="config[product_waitlist_send_limit]" value="<?php echo $this->config->get('product_waitlist_send_limit','5'); ?>"/><?php
								}else{
									echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
								} ?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo 'CSS' ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key" >
							<?php echo JText::_('CSS_FRONTEND'); ?>
							</td>
							<td>
								<?php echo $this->elements->css_frontend;?>
							</td>
						</tr>
						<tr>
							<td class="key" >
							<?php echo JText::_('CSS_BACKEND'); ?>
							</td>
							<td>
								<?php echo $this->elements->css_backend;?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'MODULES_MAIN_DEFAULT_OPTIONS' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('TYPE_OF_CONTENT');?>
							</td>
							<td>
								<?php echo $this->contentType->display('config[default_params][content_type]',$this->default_params['content_type'],$this->js,false); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('TYPE_OF_LAYOUT');?>
							</td>
							<td>
								<?php echo $this->layoutType->display('config[default_params][layout_type]',$this->default_params['layout_type'],$this->js,false);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('NUMBER_OF_COLUMNS');?>
							</td>
							<td>
								<input name="config[default_params][columns]" type="text" value="<?php echo $this->default_params['columns'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('NUMBER_OF_ITEMS');?>
							</td>
							<td>
								<input name="config[default_params][limit]" type="text" value="<?php echo $this->default_params['limit'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ORDERING_DIRECTION');?>
							</td>
							<td>
								<?php echo $this->orderdirType->display('config[default_params][order_dir]',$this->default_params['order_dir']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SUB_ELEMENTS_FILTER');?>
							</td>
							<td>
								<?php echo $this->childdisplayType->display('config[default_params][filter_type]',@$this->default_params['filter_type']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('CATEGORY_PARENT');?>
							</td>
							<td>
								<?php $link = hikashop_completeLink('category&task=selectparentlisting&filter_id=product&values='.$this->default_params['selectparentlisting'].'&control=config_default_params_',true); ?>
								<span id="changeParent">
									<?php echo $this->element->category_id.' '.htmlspecialchars($this->element->category_name, ENT_COMPAT, 'UTF-8');?>
								</span>
								<input class="inputbox" id="config_default_params_selectparentlisting" name="config[default_params][selectparentlisting]" type="hidden" size="20" value="<?php echo $this->default_params['selectparentlisting'];?>">
								<a id="linkconfig_default_params_selectparentlisting" title="<?php echo JText::_('SELECT_A_CATEGORY')?>"  href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
									<button onclick="return false"><?php echo JText::_('SELECT'); ?></button>
								</a>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('MODULE_CLASS_SUFFIX');?>
							</td>
							<td>
								<input name="config[default_params][moduleclass_sfx]" type="text" value="<?php echo @$this->default_params['moduleclass_sfx'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('MODULES_TO_DISPLAY_UNDER_MAIN_ZONE');?>
							</td>
							<td>
								<input type="hidden" name="config[default_params][modules]" id="modules_display"  value="<?php echo @$this->default_params['modules']; ?>" />
								<?php $link = hikashop_completeLink('modules&task=selectmodules',true); ?>
								<a id="linkmodules_display" title="<?php echo JText::_('SELECT_MODULES')?>"  href="" rel="{handler: 'iframe', size: {x: 650, y: 375}}" onclick="this.href='<?php echo $link;?>&modules='+document.getElementById('modules_display').value;SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
									<button onclick="return false"><?php echo JText::_('SELECT'); ?></button>
								</a>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SYNCHRO_WITH_ITEM');?>
							</td>
							<td>
								<?php
									echo JHTML::_('select.booleanlist', 'config[default_params][content_synchronize]' , '',$this->default_params['content_synchronize']);
								?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('USE_NAME_INSTEAD_TITLE');?>
							</td>
							<td>
								<?php
									echo JHTML::_('select.booleanlist', 'config[default_params][use_module_name]' , '',@$this->default_params['use_module_name']);
								?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'FILTER' ); ?></legend>
					<?php if(hikashop_level(2)){ ?>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('NUMBER_OF_COLUMNS');?>
							</td>
							<td>
								<input name="config[filter_column_number]" type="text" value="<?php echo $this->config->get('filter_column_number',2)?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('LIMIT');?>
							</td>
							<td>
								<input name="config[filter_limit]" type="text" value="<?php echo $this->config->get('filter_limit')?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('HEIGHT');?>
							</td>
							<td>
								<input name="config[filter_height]" type="text" value="<?php echo $this->config->get('filter_height',100)?>" />
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('SHOW_FILTER_BUTTON'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[show_filter_button]' , '',@$this->config->get('show_filter_button',1)); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('DISPLAY_FIELDSET'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[display_fieldset]' , '',@$this->config->get('display_fieldset',1)); ?>
							</td>
						</tr>
						<tr>
							<td class="key" >
								<?php echo JText::_('FILTER_BUTTON_POSITION'); ?>
							</td>
							<td>
								<?php echo $this->filterButtonType->display('config[filter_button_position]',$this->config->get('filter_button_position'));?>
							</td>
						</tr>
					</table>
					<?php }else{
						echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
					} ?>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_('ALL_FEED'); ?></legend>
					<table><tr><td>
						<table class="admintable" cellspacing="1">
							<tr>
								<td class="key">
									<?php echo JText::_('HIKA_TYPE'); ?>
								</td>
								<td>
									<?php echo $this->elements->hikarss_format; ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('HIKA_NAME'); ?>
								</td>
								<td>
									<input type="text" size="40" name="config[hikarss_name]" value="<?php echo $this->config->get('hikarss_name',''); ?>"/>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('HIKA_DESCRIPTION'); ?>
								</td>
								<td>
									<textarea cols="32" rows="5" name="config[hikarss_description]" ><?php echo $this->config->get('hikarss_description',''); ?></textarea>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('NUMBER_OF_ITEMS'); ?>
								</td>
								<td>
									<input type="text" size="40" name="config[hikarss_element]" value="<?php echo $this->config->get('hikarss_element','10'); ?>"/>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('ORDERING_FIELD'); ?>
								</td>
								<td>
									<?php echo $this->elements->hikarss_order; ?>
								</td>
							</tr>
							<tr>
								<td class="key">
									<?php echo JText::_('SHOW_SUB_CATEGORIES');?>
								</td>
								<td>
									<?php echo $this->elements->hikarss_child; ?>
								</td>
							</tr>
						</table>
					</td>
					</tr>
				</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'DEFAULT_PARAMS_FOR_PRODUCTS' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('ORDERING_FIELD');?>
							</td>
							<td>
								<?php echo $this->orderType->display('config[default_params][product_order]',$this->default_params['product_order'],'product');?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('RANDOM_ITEMS');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[default_params][random]' , '',$this->default_params['random']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ADD_TO_CART_BUTTON');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[default_params][add_to_cart]' , '',@$this->default_params['add_to_cart']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('LINK_TO_PRODUCT_PAGE');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[default_params][link_to_product_page]' , '',@$this->default_params['link_to_product_page']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('CONTENT_ON_PRODUCT_PAGE');?>
							</td>
							<td>
								<?php echo $this->productSyncType->display('config[default_params][product_synchronize]' , $this->default_params['product_synchronize']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('DISPLAY_PRICE');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[default_params][show_price]' , '',$this->default_params['show_price']); ?>
							</td>
						</tr>
						<tr id="price_with_tax_line">
							<td class="key">
								<?php echo JText::_('SHOW_TAXED_PRICES');?>
							</td>
							<td>
								<?php echo $this->pricetaxType->display('config[default_params][price_with_tax]' , $this->default_params['price_with_tax']); ?>
							</td>
						</tr>
						<tr id="show_original_price_line">
							<td class="key">
								<?php echo JText::_('ORIGINAL_CURRENCY_PRICE');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[default_params][show_original_price]' , '',$this->default_params['show_original_price']); ?>
							</td>
						</tr>
						<tr id="show_discount_line">
							<td class="key">
								<?php echo JText::_('SHOW_DISCOUNTED_PRICE');?>
							</td>
							<td>
								<?php echo $this->discountDisplayType->display('config[default_params][show_discount]' ,$this->default_params['show_discount']); ?>
							</td>
						</tr>
						<tr id="price_display_type_line">
							<td class="key">
								<?php echo JText::_('PRICE_DISPLAY_METHOD');?>
							</td>
							<td>
								<?php echo $this->priceDisplayType->display( 'config[default_params][price_display_type]',$this->default_params['price_display_type']); ?>
							</td>
						</tr>
						<tr id="show_price_weight_line">
							<td class="key">
								<?php echo JText::_('WEIGHT_UNIT_PRICE');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[show_price_weight]' , '',$this->config->get('show_price_weight')); ?>
							</td>
						</tr>
						<tr id="price_stock_display_line">
							<td class="key">
								<?php echo JText::_('DISPLAY_OUT_OF_STOCK_PRODUCTS');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[show_out_of_stock]', '',$this->config->get('show_out_of_stock',1)); ?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'DEFAULT_PARAMS_FOR_CATEGORIES' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('ORDERING_FIELD');?>
							</td>
							<td>
								<?php echo $this->orderType->display('config[default_params][category_order]',$this->default_params['category_order'],'category');?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SHOW_SUB_CATEGORIES');?>
							</td>
							<td>
								<?php echo $this->listType->display('config[default_params][child_display_type]',$this->default_params['child_display_type']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('NUMBER_OF_SUB_CATEGORIES');?>
							</td>
							<td>
								<input name="config[default_params][child_limit]" type="text" value="<?php echo @$this->default_params['child_limit'];?>" />
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'DEFAULT_PARAMS_FOR_DIV' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('TYPE_OF_ITEM_LAYOUT');?>
							</td>
							<td>
								<?php echo $this->itemType->display('config[default_params][div_item_layout_type]',$this->default_params['div_item_layout_type'],$this->js);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ITEM_BOX_COLOR');?>
							</td>
							<td>
								<?php echo $this->colorType->displayAll('','config[default_params][background_color]',@$this->default_params['background_color']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ITEM_BOX_MARGIN');?>
							</td>
							<td>
								<input name="config[default_params][margin]" type="text" value="<?php echo @$this->default_params['margin'];?>" />px
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ITEM_BOX_ROUND_CORNER');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[default_params][rounded_corners]' , '',@$this->default_params['rounded_corners']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('TEXT_CENTERED');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', 'config[default_params][text_center]' , '',@$this->default_params['text_center']); ?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'FOOTER' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key" >
							<?php echo JText::_('SHOW_FOOTER'); ?>
							</td>
							<td>
								<?php echo $this->elements->show_footer; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('AFFILIATE');?>
							</td>
							<td>
								<input name="config[partner_id]" type="text" value="<?php echo $this->config->get('partner_id')?>" />
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
</div>