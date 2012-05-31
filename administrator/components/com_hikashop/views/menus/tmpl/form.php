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
<div class="iframedoc" id="iframedoc"></div>
<form action="index.php" method="post" name="adminForm">
	<table width="100%">
		<tr>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'HIKA_DETAILS' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<?php if(version_compare(JVERSION,'1.6','<')){ ?>
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_NAME' ); ?>
							</td>
							<td>
								<input class="text_area" type="text" name="menu[name]" id="title" size="35" value="<?php echo $this->escape(@$this->element->name); ?>" />
							</td>
						</tr>
						<?php }else{ ?>
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_TITLE' ); ?>
							</td>
							<td>
								<input class="text_area" type="text" name="menu[title]" id="title" size="35" value="<?php echo $this->escape(@$this->element->title); ?>" />
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_ALIAS' ); ?>
							</td>
							<td>
								<input class="text_area" type="text" name="menu[alias]" id="title" size="35" value="<?php echo $this->escape(@$this->element->alias); ?>" />
							</td>
						</tr>
						<tr>
							<td valign="top" class="key">
								<?php echo JText::_( 'SHOW_IMAGE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist',  $this->control.'[show_image]', 'class="inputbox"', @$this->element->hikashop_params['show_image'] ); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" class="key">
								<?php echo JText::_( 'SHOW_DESCRIPTION' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist',  $this->control.'[show_description]', 'class="inputbox"', @$this->element->hikashop_params['show_description'] ); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" class="key">
								<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist',  'menu[published]', 'class="inputbox"', @$this->element->published ); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TYPE_OF_CONTENT');?>
							</td>
							<td>
								<?php echo $this->contentType->display('menu[content_type]',@$this->element->content_type,$this->js); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TYPE_OF_LAYOUT');?>
							</td>
							<td>
								<?php echo $this->layoutType->display($this->control.'[layout_type]',$this->element->hikashop_params['layout_type'],$this->js);?>
							</td>
						</tr>
						<tr id="number_of_columns">
							<td class="key" valign="top">
								<?php echo JText::_('NUMBER_OF_COLUMNS');?>
							</td>
							<td>
								<input name="<?php echo $this->control; ?>[columns]" type="text" value="<?php echo $this->element->hikashop_params['columns'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('NUMBER_OF_ITEMS');?>
							</td>
							<td>
								<input name="<?php echo $this->control; ?>[limit]" type="text" value="<?php echo $this->element->hikashop_params['limit'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ORDERING_DIRECTION');?>
							</td>
							<td>
								<?php echo $this->orderdirType->display($this->control.'[order_dir]',$this->element->hikashop_params['order_dir']);?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('SUB_ELEMENTS_FILTER');?>
							</td>
							<td>
								<?php echo $this->childdisplayType->display($this->control.'[filter_type]',@$this->element->hikashop_params['filter_type']);?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('CATEGORY_PARENT');?>
							</td>
							<td>
								<?php $link = hikashop_completeLink('category&task=selectparentlisting&values='.$this->element->hikashop_params['selectparentlisting'].'&control=params',true); ?>
								<span id="changeParent">
									<?php echo @$this->element->category->category_id.' '.htmlspecialchars(@$this->element->category->category_name, ENT_COMPAT, 'UTF-8');?>
								</span>
								<input class="inputbox" id="paramsselectparentlisting" name="<?php echo $this->control;?>[selectparentlisting]" type="hidden" size="20" value="<?php echo $this->element->hikashop_params['selectparentlisting'];?>">
								<a id="linkparamsselectparentlisting" title="<?php echo JText::_('SELECT_A_CATEGORY')?>"  href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
									<button onclick="return false"><?php echo JText::_('SELECT'); ?></button>
								</a>
								<a href="#" onclick="document.getElementById('changeParent').innerHTML='';document.getElementById('paramsselectparentlisting').value=0;return false;">
									<img src="<?php echo HIKASHOP_IMAGES?>delete.png"/>
								</a>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ASSOCIATED_MODULES');?>
							</td>
							<td>
								<input type="hidden" name="<?php echo $this->control; ?>[modules]" id="menumodules"  value="<?php echo @$this->element->hikashop_params['modules']; ?>" />
								<a id="linkmenumodules" title="<?php echo JText::_('SELECT_MODULES'); ?>"  href="" rel="{handler: 'iframe', size: {x: 650, y: 375}}" onclick="this.href='<?php echo hikashop_completeLink('modules&task=selectmodules&control=menu&name=modules',true); ?>&modules='+document.getElementById('menumodules').value;SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
									<button onclick="return false"><?php echo JText::_('SELECT'); ?></button>
								</a>
								<br/>
								<?php
									if(!empty($this->element->id) && !empty($this->element->hikashop_params['modules'])){
										$modules = explode(',',$this->element->hikashop_params['modules']);
										$modulesClass = hikashop_get('class.modules');
										$ok = false;
										foreach($modules as $module){
											$element = $modulesClass->get($module);
											if(!empty($element->title)){
												$ok = true;
												echo '<a href="'.hikashop_completeLink('modules&task=edit&cid[]='.@$element->id).'">'.JText::sprintf('OPTIONS_FOR_X',@$element->title).'</a><br/>';
											}
										}
										if(!$ok){
											$menuClass = hikashop_get('class.menus');
											$menuClass->displayErrors((int)$this->element->id);
										}
									}elseif(!empty($this->element->content_type) && $this->element->content_type=='category'){
										$menuClass = hikashop_get('class.menus');
										$menuClass->displayErrors((int)$this->element->id);
									}
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('USE_NAME_INSTEAD_TITLE');?>
							</td>
							<td>
								<?php
									echo JHTML::_('select.booleanlist', $this->control.'[use_module_name]' , '',@$this->element->hikashop_params['use_module_name']);
								?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset id="content_product">
					<legend><?php echo JText::_('PARAMS_FOR_PRODUCTS'); ?></legend>
					<table class="admintable" cellspacing="1" width="100%">
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ORDERING_FIELD');?>
							</td>
							<td>
								<?php echo $this->orderType->display($this->control.'[product_order]',$this->element->hikashop_params['product_order'],'product');?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('RANDOM_ITEMS');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[random]' , '',$this->element->hikashop_params['random']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ADD_TO_CART_BUTTON');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[add_to_cart]' , '',@$this->element->hikashop_params['add_to_cart']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('LINK_TO_PRODUCT_PAGE');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[link_to_product_page]' , '',@$this->element->hikashop_params['link_to_product_page']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('DISPLAY_PRICE');?>
							</td>
							<td>
								<?php
									echo JHTML::_('select.booleanlist', $this->control.'[show_price]' , 'onchange="switchDisplay(this.value,\'price_with_tax_line\',\'1\');switchDisplay(this.value,\'show_original_price_line\',\'1\');switchDisplay(this.value,\'show_discount_line\',\'1\');switchDisplay(this.value,\'price_display_type_line\',\'1\');"',$this->element->hikashop_params['show_price']);
									if(!$this->element->hikashop_params['show_price']) $this->js .='switchDisplay(\'0\',\'price_display_type_line\',\'1\');switchDisplay(\'0\',\'price_with_tax_line\',\'1\');switchDisplay(\'0\',\'show_original_price_line\',\'1\');switchDisplay(\'0\',\'show_discount_line\',\'1\');';
								?>
							</td>
						</tr>
						<tr id="price_with_tax_line">
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_TAXED_PRICES');?>
							</td>
							<td>
								<?php echo $this->pricetaxType->display($this->control.'[price_with_tax]' , $this->element->hikashop_params['price_with_tax']); ?>
							</td>
						</tr>
						<tr id="show_original_price_line">
							<td class="key" valign="top">
								<?php echo JText::_('ORIGINAL_CURRENCY_PRICE');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[show_original_price]' , '',$this->element->hikashop_params['show_original_price']); ?>
							</td>
						</tr>
						<tr id="show_discount_line">
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_DISCOUNTED_PRICE');?>
							</td>
							<td>
								<?php echo $this->discountDisplayType->display( $this->control.'[show_discount]' ,$this->element->hikashop_params['show_discount']); ?>
							</td>
						</tr>
						<tr id="price_display_type_line">
							<td class="key">
								<?php echo JText::_('PRICE_DISPLAY_METHOD');?>
							</td>
							<td>
								<?php echo $this->priceDisplayType->display( $this->control.'[price_display_type]',$this->element->hikashop_params['price_display_type']); ?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset id="content_category">
					<legend><?php echo JText::_('PARAMS_FOR_CATEGORIES'); ?></legend>
					<table class="admintable" cellspacing="1" width="100%">
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ORDERING_FIELD');?>
							</td>
							<td>
								<?php echo $this->orderType->display($this->control.'[category_order]',$this->element->hikashop_params['category_order'],'category');?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_SUB_CATEGORIES');?>
							</td>
							<td>
								<?php echo $this->listType->display($this->control.'[child_display_type]',$this->element->hikashop_params['child_display_type']);?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('NUMBER_OF_SUB_CATEGORIES');?>
							</td>
							<td>
								<input name="<?php echo $this->control; ?>[child_limit]" type="text" value="<?php echo @$this->element->hikashop_params['child_limit'];?>" />
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset id="layout_div">
					<legend><?php echo JText::_('PARAMS_FOR_DIV'); ?></legend>
					<?php
						if(hikashop_level(2)){
							$productEffect="";
							$productEffectDuration="";
							$paneHeight="";
							if($this->element->hikashop_params['div_item_layout_type']=='fade'){
								$productEffect='style="display:none"';
							}else if($this->element->hikashop_params['div_item_layout_type']=='img_pane'){
								$productEffect='style="display:none"';
								$productEffectDuration='style="display:none"';
							}else if($this->element->hikashop_params['div_item_layout_type']!='slider_horizontal' && $this->element->hikashop_params['div_item_layout_type']!='slider_vertical'){
								$productEffect='style="display:none"';
								$productEffectDuration='style="display:none"';
							}
						}
					?>
					<table class="admintable" cellspacing="1" width="100%">
						<tr>
							<td class="key">
								<?php echo JText::_('IMAGE_X');?>
							</td>
							<td>
								<input size=12 name="<?php echo $this->control;?>[image_width]" type="text" value="<?php echo @$this->element->hikashop_params['image_width'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('IMAGE_Y');?>
							</td>
							<td>
								<input size=12 name="<?php echo $this->control;?>[image_height]" type="text" value="<?php echo @$this->element->hikashop_params['image_height'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TYPE_OF_ITEM_LAYOUT');?>
							</td>
							<td>
								<?php echo $this->itemType->display($this->control.'[div_item_layout_type]',$this->element->hikashop_params['div_item_layout_type'],$this->js, 'onchange="setVisibleLayoutEffect(this.value);"');?>
							</td>
						</tr>
						<?php if(hikashop_level(2)){ ?>
							<tr id="product_effect" <?php echo $productEffect; ?>>
								<td class="key">
									<?php echo JText::_('PRODUCT_TRANSITION_EFFECT');?>
								</td>
								<td>
									<?php echo $this->transition_effectType->display($this->control.'[product_transition_effect]',@$this->element->hikashop_params['product_transition_effect']);?>
								</td>
							</tr>
							<tr id="product_effect_duration" <?php echo $productEffectDuration; ?>>
								<td class="key">
									<?php echo JText::_('PRODUCT_EFFECT_DURATION');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[product_effect_duration]" type="text" value="<?php echo @$this->element->hikashop_params['product_effect_duration'];?>" /> ms
								</td>
							</tr>
						<?php } ?>
						<tr>
							<td class="key">
								<?php echo JText::_('PANE_HEIGHT');?>
							</td>
							<td>
								<input size=12 name="<?php echo $this->control;?>[pane_height]" type="text" value="<?php echo @$this->element->hikashop_params['pane_height'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ITEM_BOX_COLOR');?>
							</td>
							<td>
								<?php echo $this->colorType->displayAll('',$this->control.'[background_color]',@$this->element->hikashop_params['background_color']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ITEM_BOX_MARGIN');?>
							</td>
							<td>
								<input name="<?php echo $this->control;?>[margin]" type="text" value="<?php echo @$this->element->hikashop_params['margin'];?>" /> px
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ITEM_BOX_BORDER');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[border_visible]' , '',@$this->element->hikashop_params['border_visible']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ITEM_BOX_ROUND_CORNER');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[rounded_corners]' , '',@$this->element->hikashop_params['rounded_corners']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TEXT_CENTERED');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[text_center]' , '',@$this->element->hikashop_params['text_center']); ?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset id="layout_list">
					<legend><?php echo JText::_('PARAMS_FOR_LIST'); ?></legend>
					<table class="admintable" cellspacing="1" width="100%">
						<tr>
							<td class="key">
								<?php echo JText::_('UL_CLASS_NAME');?>
							</td>
							<td>
								<input name="<?php echo $this->control;?>[ul_class_name]" type="text" value="<?php echo @$this->element->hikashop_params['ul_class_name'];?>" />
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="menu[id]" value="<?php echo (int)@$this->element->id; ?>" />
	<?php if(version_compare(JVERSION,'1.6','<')){ ?>
		<input type="hidden" name="menu[componentid]" value="<?php echo @$this->element->conponentid; ?>" />
	<?php }else{ ?>
		<input type="hidden" name="menu[component_id]" value="<?php echo @$this->element->conponent_id; ?>" />
	<?php } ?>
	<input type="hidden" name="menu[type]" value="component" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getVar('ctrl');?>" />
	<input type="hidden" name="return" value="<?php echo JRequest::getString('return');?>" />
	<input type="hidden" name="client" value="0" />
	<?php echo JHTML::_( 'form.token' );?>
</form>
<?php
$this->js = "window.addEvent('domready', function() {
		".$this->js."
});";
$doc =& JFactory::getDocument();
$doc->addScriptDeclaration($this->js);?>