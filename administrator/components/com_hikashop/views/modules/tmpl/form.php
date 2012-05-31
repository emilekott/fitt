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
						<tr>
							<td class="key">
								<label for="title">
									<?php echo JText::_( 'HIKA_TITLE' ); ?>
								</label>
							</td>
							<td>
								<input class="text_area" type="text" name="module[title]" id="title" size="35" value="<?php echo $this->escape(@$this->element->title); ?>" />
							</td>
						</tr>
						<tr>
							<td width="100" class="key">
								<?php echo JText::_( 'SHOW_TITLE' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist',  'module[showtitle]', 'class="inputbox"', @$this->element->showtitle ); ?>
							</td>
						</tr>
						<tr>
							<td valign="top" class="key">
								<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist',  'module[published]', 'class="inputbox"', @$this->element->published ); ?>
							</td>
						</tr>
						<?php if($this->element->module=='mod_hikashop'){ ?>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TYPE_OF_CONTENT');?>
							</td>
							<td>
								<?php
									$html = $this->contentType->display($this->control.'[content_type]',@$this->element->hikashop_params['content_type'],$this->js);
									if($this->include_module){
										echo @$this->element->hikashop_params['content_type'];
										?><input name="<?php echo $this->control; ?>[content_type]" type="hidden" value="<?php echo @$this->element->hikashop_params['content_type'];?>" /><?php
									}else{
										echo $html;
									}
								?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('TYPE_OF_LAYOUT');?>
							</td>
							<td>
								<?php echo $this->layoutType->display($this->control.'[layout_type]',@$this->element->hikashop_params['layout_type'],$this->js);?>
							</td>
						</tr>
						<tr id="number_of_columns">
							<td class="key" valign="top">
								<?php echo JText::_('NUMBER_OF_COLUMNS');?>
							</td>
							<td>
								<input name="<?php echo $this->control; ?>[columns]" type="text" value="<?php echo @$this->element->hikashop_params['columns'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('NUMBER_OF_ITEMS');?>
							</td>
							<td>
								<input name="<?php echo $this->control; ?>[limit]" type="text" value="<?php echo @$this->element->hikashop_params['limit'];?>" />
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ORDERING_DIRECTION');?>
							</td>
							<td>
								<?php echo $this->orderdirType->display($this->control.'[order_dir]',@$this->element->hikashop_params['order_dir']);?>
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
								<?php $link = hikashop_completeLink('category&task=selectparentlisting&values='.@$this->element->hikashop_params['selectparentlisting'].'&control=params',true); ?>
								<span id="changeParent">
									<?php echo @$this->element->category->category_id.' '.htmlspecialchars(@$this->element->category->category_name, ENT_COMPAT, 'UTF-8');?>
								</span>
								<input class="inputbox" id="paramsselectparentlisting" name="<?php echo $this->control;?>[selectparentlisting]" type="hidden" size="20" value="<?php echo @$this->element->hikashop_params['selectparentlisting'];?>">
								<a id="linkparamsselectparentlisting" title="<?php echo JText::_('SELECT_A_CATEGORY')?>"  href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}" onclick="SqueezeBox.fromElement(this,{parse: 'rel'});return false;">
									<button onclick="return false"><?php echo JText::_('SELECT'); ?></button>
								</a>
								<a href="#" onclick="document.getElementById('changeParent').innerHTML='';document.getElementById('paramsselectparentlisting').value=0;return false;">
									<img src="<?php echo HIKASHOP_IMAGES?>delete.png"/>
								</a>
							</td>
						</tr>
						<?php }else{
						$document=& JFactory::getDocument();
						$js = "window.addEvent('domready', function() { hikashopToggleCart(".(int)@$this->element->hikashop_params['small_cart'].");});";
						$document->addScriptDeclaration($js);
							?>
						<tr>
							<td class="key">
								<?php echo JText::_('MINI_CART'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[small_cart]','onchange="hikashopToggleCart(this.value);"',@$this->element->hikashop_params['small_cart']);?>
							</td>
						</tr>
						<tr id="cart_price">
							<td class="key">
								<?php echo JText::_('SHOW_CART_PRICE'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[show_cart_price]','',@$this->element->hikashop_params['show_cart_price']);?>
							</td>
						</tr>
						<tr id="cart_proceed">
							<td class="key">
								<?php echo JText::_('SHOW_CART_PROCEED'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[show_cart_proceed]','',@$this->element->hikashop_params['show_cart_proceed']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SHOW_CART_QUANTITY'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[show_cart_quantity]','',@$this->element->hikashop_params['show_cart_quantity']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('SHOW_CART_DELETE'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[show_cart_delete]','',@$this->element->hikashop_params['show_cart_delete']);?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('EMPTY_CART_MESSAGE_OVERRIDE'); ?>
							</td>
							<td>
								<input name="<?php echo $this->control;?>[msg]" type="text" value="<?php echo @$this->element->hikashop_params['msg'];?>" />
							</td>
						</tr>
						<?php }
							  if($this->element->module=='mod_hikashop'){ ?>
						 <tr>
							<td class="key" valign="top">
								<?php echo JText::_('SYNCHRO_WITH_ITEM');?>
							</td>
							<td>
								<?php
									echo JHTML::_('select.booleanlist', $this->control.'[content_synchronize]' , '',@$this->element->hikashop_params['content_synchronize']);
								?>
							</td>
						</tr>
						<tr>
							<td class="key" >
							<?php echo JText::_('MENU'); ?>
							</td>
							<td>
								<?php echo JHTML::_('select.genericlist', $this->hikashop_menu, $this->control.'[itemid]' , 'size="1"', 'value', 'text', @$this->element->hikashop_params['itemid']); ?>
							</td>
						</tr>
						<?php } ?>
					</table>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset id="content_product">
					<legend><?php echo JText::_('PARAMS_FOR_PRODUCTS'); ?></legend>
					<table class="admintable" cellspacing="1" width="100%">
						<?php if($this->element->module=='mod_hikashop'){ ?>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('ORDERING_FIELD');?>
							</td>
							<td>
								<?php echo $this->orderType->display($this->control.'[product_order]',@$this->element->hikashop_params['product_order'],'product');?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('CONTENT_ON_PRODUCT_PAGE');?>
							</td>
							<td>
								<?php echo $this->productSyncType->display($this->control.'[product_synchronize]' , @$this->element->hikashop_params['product_synchronize']); ?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('RANDOM_ITEMS');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[random]' , '',@$this->element->hikashop_params['random']); ?>
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
						<?php } ?>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('DISPLAY_PRICE');?>
							</td>
							<td>
								<?php
									echo JHTML::_('select.booleanlist', $this->control.'[show_price]' , 'onchange="switchDisplay(this.value,\'price_with_tax_line\',\'1\');switchDisplay(this.value,\'show_original_price_line\',\'1\');switchDisplay(this.value,\'show_discount_line\',\'1\');switchDisplay(this.value,\'price_display_type_line\',\'1\');"',$this->element->hikashop_params['show_price']);
									if(!@$this->element->hikashop_params['show_price']) $this->js .='switchDisplay(\'0\',\'price_display_type_line\',\'1\');switchDisplay(\'0\',\'price_with_tax_line\',\'1\');switchDisplay(\'0\',\'show_original_price_line\',\'1\');switchDisplay(\'0\',\'show_discount_line\',\'1\');';
								?>
							</td>
						</tr>
						<tr id="price_with_tax_line">
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_TAXED_PRICES');?>
							</td>
							<td>
								<?php echo $this->pricetaxType->display($this->control.'[price_with_tax]' , @$this->element->hikashop_params['price_with_tax']); ?>
							</td>
						</tr>
						<tr id="show_original_price_line">
							<td class="key" valign="top">
								<?php echo JText::_('ORIGINAL_CURRENCY_PRICE');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[show_original_price]' , '',@$this->element->hikashop_params['show_original_price']); ?>
							</td>
						</tr>
						<tr id="show_discount_line">
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_DISCOUNTED_PRICE');?>
							</td>
							<td>
								<?php echo $this->discountDisplayType->display( $this->control.'[show_discount]' ,@$this->element->hikashop_params['show_discount']); ?>
							</td>
						</tr>
						<tr id="price_display_type_line">
							<td class="key">
								<?php echo JText::_('PRICE_DISPLAY_METHOD');?>
							</td>
							<td>
								<?php echo $this->priceDisplayType->display( $this->control.'[price_display_type]',@$this->element->hikashop_params['price_display_type']); ?>
							</td>
						</tr>
						<?php if($this->element->module=='mod_hikashop'){ ?>
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
								<?php echo $this->orderType->display($this->control.'[category_order]',@$this->element->hikashop_params['category_order'],'category');?>
							</td>
						</tr>
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('SHOW_SUB_CATEGORIES');?>
							</td>
							<td>
								<?php echo $this->listType->display($this->control.'[child_display_type]',@$this->element->hikashop_params['child_display_type']);?>
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
						<tr>
							<td class="key" valign="top">
								<?php echo JText::_('LINK_ON_MAIN_CATEGORIES');?>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', $this->control.'[links_on_main_categories]' , '',@$this->element->hikashop_params['links_on_main_categories']); ?>
							</td>
						</tr>
					</table>
				</fieldset>
				<fieldset id="layout_div">
					<legend><?php echo JText::_('PARAMS_FOR_DIV'); ?></legend>
					<?php
						$displayCarouselType="";
						$slideDirection="";
						$transitionEffect="";
						$carouselEffectDuration="";
						$productBySlide="";
						$slideOneByOne="";
						$autoSlide="";
						$autoSlideDuration="";
						$slidePagination="";
						$paginationWidth="";
						$paginationHeight="";
						$paginationPosition="";
						$displayButton="";
						$productEffect="";
						$productEffectDuration="";
						$paneHeight="";
						if(@!$this->element->hikashop_params['enable_carousel']){
							$displayCarouselType='style="display:none"';
							$slideDirection='style="display:none"';
							$transitionEffect='style="display:none"';
							$carouselEffectDuration='style="display:none"';
							$productBySlide='style="display:none"';
							$slideOneByOne='style="display:none"';
							$autoSlide='style="display:none"';
							$autoSlideDuration='style="display:none"';
							$slidePagination='style="display:none"';
							$paginationWidth='style="display:none"';
							$paginationHeight='style="display:none"';
							$paginationPosition='style="display:none"';
							$displayButton='style="display:none"';
						}
						if(@$this->element->hikashop_params['carousel_effect']=="fade"){
							$transitionEffect='style="display:none"';
							$slideOneByOne='style="display:none"';
						}
						if(@$this->element->hikashop_params['div_item_layout_type']=='fade'){
							$productEffect='style="display:none"';
						}else if(@$this->element->hikashop_params['div_item_layout_type']=='img_pane'){
							$productEffect='style="display:none"';
							$productEffectDuration='style="display:none"';
						}else if(@$this->element->hikashop_params['div_item_layout_type']!='slider_horizontal' && @$this->element->hikashop_params['div_item_layout_type']!='slider_vertical'){
							$productEffect='style="display:none"';
							$productEffectDuration='style="display:none"';
						}
						if(!@$this->element->hikashop_params['auto_slide']){
							$autoSlideDuration='style="display:none"';
						}
						if(@$this->element->hikashop_params['pagination_type']=="no_pagination"){
							$paginationWidth='style="display:none"';
							$paginationHeight='style="display:none"';
							$paginationPosition='style="display:none"';
						}else{
							if(@$this->element->hikashop_params['pagination_type']!="thumbnails" ){
								$paginationHeight='style="display:none"';
								$paginationWidth='style="display:none"';
							}
						}
					?>
					<table class="admintable" cellspacing="1" width="100%">
						<?php if(hikashop_level(2)){ ?>
							<tr>
								<td class="key" valign="top">
									<?php echo JText::_('ENABLE_CAROUSEL');?>
								</td>
								<td>
									<?php echo JHTML::_('select.booleanlist', $this->control.'[enable_carousel]' , 'onclick="setVisible(this.value);"',@$this->element->hikashop_params['enable_carousel']); ?>
								</td>
							</tr>
							<tr id="carousel_type" <?php echo $displayCarouselType; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('TYPE_OF_CAROUSEL_EFFECT');?>
								</td>
								<td>
									<?php echo $this->effectType->display($this->control.'[carousel_effect]',@$this->element->hikashop_params['carousel_effect'] , 'onchange="setVisibleEffect(this.value);"');?>
								</td>
							</tr>
							<tr id="slide_direction" <?php echo $slideDirection; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('SLIDE_DIRECTION');?>
								</td>
								<td>
									<?php echo $this->directionType->display($this->control.'[slide_direction]',@$this->element->hikashop_params['slide_direction']);?>
								</td>
							</tr>
							<tr id="transition_effect" <?php echo $transitionEffect; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('TRANSITION_EFFECT');?>
								</td>
								<td>
									<?php echo $this->transition_effectType->display($this->control.'[transition_effect]',@$this->element->hikashop_params['transition_effect']);?>
								</td>
							</tr>
							<tr id="carousel_effect_duration" <?php echo $carouselEffectDuration; ?>>
								<td class="key">
									<?php echo JText::_('CAROUSEL_EFFECT_DURATION');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[carousel_effect_duration]" type="text" value="<?php echo @$this->element->hikashop_params['carousel_effect_duration'];?>" /> ms
								</td>
							</tr>
							<tr id="product_by_slide" <?php echo $productBySlide; ?>>
								<td class="key">
									<?php echo JText::_('PRODUCTS_BY_SLIDE');?>
								</td>
								<td>
									<input size=9 name="<?php echo $this->control;?>[item_by_slide]" type="text" value="<?php echo @$this->element->hikashop_params['item_by_slide'];?>" />
								</td>
							</tr>
							<tr id="slide_one_by_one" <?php echo $slideOneByOne; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('SLIDE_ONE_BY_ONE');?>
								</td>
								<td>
									<?php echo JHTML::_('select.booleanlist', $this->control.'[one_by_one]' , '',@$this->element->hikashop_params['one_by_one']); ?>
								</td>
							</tr>
							<tr id="auto_slide" <?php echo $autoSlide; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('AUTO_SLIDE');?>
								</td>
								<td>
									<?php echo JHTML::_('select.booleanlist', $this->control.'[auto_slide]' , 'onclick="setVisibleAutoSlide(this.value);"',@$this->element->hikashop_params['auto_slide']); ?>
								</td>
							</tr>
							<tr id="auto_slide_duration" <?php echo $autoSlideDuration; ?>>
								<td class="key">
									<?php echo JText::_('AUTO_SLIDE_DURATION');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[auto_slide_duration]" type="text" value="<?php echo @$this->element->hikashop_params['auto_slide_duration'];?>" /> ms
								</td>
							</tr>
							<tr id="slide_pagination" <?php echo $slidePagination; ?>>
								<td class="key">
									<?php echo JText::_('SLIDE_PAGINATION_TYPE');?>
								</td>
								<td>
									<?php echo $this->slide_paginationType->display($this->control.'[pagination_type]',@$this->element->hikashop_params['pagination_type'], 'onchange="setVisiblePagination(this.value);"');?>
								</td>
							</tr>
							<tr id="pagination_width" <?php echo $paginationWidth; ?>>
								<td class="key">
									<?php echo JText::_('PAGINATION_IMAGE_WIDTH');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[pagination_image_width]" type="text" value="<?php echo @$this->element->hikashop_params['pagination_image_width'];?>" /> px
								</td>
							</tr>
							<tr id="pagination_height" <?php echo $paginationHeight; ?>>
								<td class="key">
									<?php echo JText::_('PAGINATION_IMAGE_HEIGHT');?>
								</td>
								<td>
									<input size=12 name="<?php echo $this->control;?>[pagination_image_height]" type="text" value="<?php echo @$this->element->hikashop_params['pagination_image_height'];?>" /> px
								</td>
							</tr>
							<tr id="pagination_position" <?php echo $paginationPosition; ?>>
								<td class="key">
									<?php echo JText::_('HIKA_PAGINATION');?>
								</td>
								<td>
									<?php echo $this->positionType->display($this->control.'[pagination_position]',@$this->element->hikashop_params['pagination_position']);?>
								</td>
							</tr>
							<tr id="display_button" <?php echo $displayButton; ?>>
								<td class="key" valign="top">
									<?php echo JText::_('DISPLAY_BUTTONS');?>
								</td>
								<td>
									<?php echo JHTML::_('select.booleanlist', $this->control.'[display_button]' , '',@$this->element->hikashop_params['display_button']); ?>
								</td>
							</tr>
						<?php }else{ ?>
							<tr>
								<td class="key" valign="top">
									<?php echo JText::_('ENABLE_CAROUSEL');?>
								</td>
								<td>
									<?php echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>'; ?>
								</td>
							</tr>
						<?php } ?>
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
								<?php echo $this->itemType->display($this->control.'[div_item_layout_type]',@$this->element->hikashop_params['div_item_layout_type'],$this->js, 'onchange="setVisibleLayoutEffect(this.value);"');?>
							</td>
						</tr>
						<?php if(hikashop_level(2)){ ?>
							<tr id="product_effect" <?php echo $productEffect; ?>>
								<td class="key" valign="top">
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
						<tr id="pane_height" <?php echo $paneHeight; ?>>
							<td class="key">
								<?php echo JText::_('PANE_HEIGHT');?>
							</td>
							<td>
								<input size=12 name="<?php echo $this->control;?>[pane_height]" type="text" value="<?php echo @$this->element->hikashop_params['pane_height'];?>" />px
							</td>
						</tr>
					<?php } ?>
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
								<input name="<?php echo $this->control;?>[margin]" type="text" value="<?php echo @$this->element->hikashop_params['margin'];?>" />px
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
				<?php if($this->element->module=='mod_hikashop'){ ?>
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
				<?php } ?>
			</td>
		</tr>
	</table>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="module[id]" value="<?php echo (int)@$this->element->id; ?>" />
	<input type="hidden" name="module[module]" value="<?php echo $this->element->module; ?>" />
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