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
<table class="admintable" cellspacing="1" width="100%">
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Help');?>
		</td>
		<td>
			<?php 
				$config =& hikashop_config();
				$level = $config->get('level');
				$link = HIKASHOP_HELPURL.'help&level='.$level;
				echo '<a class="modal" title="'.JText::_('HELP',true).'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><button onclick="SqueezeBox.fromElement(this);return false;">'.JText::_('HELP').'</button></a>';
			?>
		</td>
	</tr>
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Type of content');?>
		</td>
		<td>
			<?php 
				$html = $this->contentType->display($this->control.'[content_type]',$this->content_type,$this->js);
				if($this->include_module){
					echo $this->content_type;
					?><input name="<?php echo $this->control; ?>[content_type]" type="hidden" value="<?php echo $this->content_type;?>" /><?php
				}else{
					echo $html;
				}
			?>
		</td>
	</tr>
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Type of layout');?>
		</td>
		<td>
			<?php echo $this->layoutType->display($this->control.'[layout_type]',$this->layout_type,$this->js);?>
		</td>
	</tr>
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Number of columns');?>
		</td>
		<td>
			<input name="<?php echo $this->control; ?>[columns]" type="text" value="<?php echo $this->columns;?>" />
		</td>
	</tr>
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Limit the number of items');?>
		</td>
		<td>
			<input name="<?php echo $this->control; ?>[limit]" type="text" value="<?php echo $this->limit;?>" />
		</td>
	</tr>
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Ordering direction');?>
		</td>
		<td>
			<?php echo $this->orderdirType->display($this->control.'[order_dir]',$this->order_dir);?>
		</td>
	</tr>
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Sub elements filter');?>
		</td>
		<td>
			<?php echo $this->childdisplayType->display($this->control.'[filter_type]',@$this->filter_type);?>
		</td>
	</tr>
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Parent category');?>
		</td>
		<td>
			<?php $link = hikashop_completeLink('category&task=selectparentlisting&values='.$this->selectparentlisting.'&control='.$this->control,true); ?>
			<span id="changeParent">
				<?php echo $this->element->category_id.' '.htmlspecialchars($this->element->category_name, ENT_COMPAT, 'UTF-8');?>
			</span>
			<input class="inputbox" id="<?php echo $this->control;?>'selectparentlisting" name="<?php echo $this->control;?>[selectparentlisting]" type="hidden" size="20" value="<?php echo $this->selectparentlisting;?>">
			<a id="link<?php echo $this->control;?>selectparentlisting" title="<?php echo JText::_('Select a category')?>"  href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}" onclick="SqueezeBox.fromElement(this);return false;">
				<button onclick="return false"><?php echo JText::_('Select'); ?></button>
			</a>
		</td>
	</tr>
	<tr>
		<td class="key" valign="top">
			<?php echo JText::_('Module class suffix');?>
		</td>
		<td>
			<input name="<?php echo $this->control; ?>[moduleclass_sfx]" type="text" value="<?php echo @$this->moduleclass_sfx;?>" />
		</td>
	</tr>
	<?php if($this->include_module){?>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Modules display');?>
			</td>
			<td>
				<input type="hidden" name="<?php echo $this->control;?>[modules]" id="modules_display"  value="<?php echo @$this->modules; ?>" />
				<?php $link = hikashop_completeLink('modules&task=selectmodules&control='.$this->control,true); ?>
				<a id="link<?php echo $this->control;?>modules" title="<?php echo JText::_('Select a module')?>"  href="" rel="{handler: 'iframe', size: {x: 650, y: 375}}" onclick="this.href='<?php echo $link;?>&modules='+document.getElementById('modules_display').value;SqueezeBox.fromElement(this);return false;">
					<button onclick="return false"><?php echo JText::_('Select'); ?></button>
				</a>
			</td>
		</tr>
	<?php }else{ 
		 ?>
		 <tr>
			<td class="key" valign="top">
				<?php echo JText::_('Synchronize with currently displayed item when possible');?>
			</td>
			<td>
				<?php 
					echo JHTML::_('select.booleanlist', $this->control.'[content_synchronize]' , '',$this->content_synchronize); 
				?>
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Use module name instead of category name for the title');?>
			</td>
			<td>
				<?php 
					echo JHTML::_('select.booleanlist', $this->control.'[use_module_name]' , '',@$this->use_module_name); 
				?>
			</td>
		</tr>
	<?php }?>
</table>
<fieldset id="content_product">
	<legend><?php echo JText::_('PARAMS_FOR_PRODUCTS'); ?></legend>
	<table class="admintable" cellspacing="1" width="100%">
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Order field');?>
			</td>
			<td>
				<?php echo $this->orderType->display($this->control.'[product_order]',$this->product_order,'product');?>
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Display price');?>
			</td>
			<td>
				<?php 
					echo JHTML::_('select.booleanlist', $this->control.'[show_price]' , 'onchange="switchDisplay(this.value,\'price_with_tax_line\',\'1\');switchDisplay(this.value,\'show_original_price_line\',\'1\');switchDisplay(this.value,\'show_discount_line\',\'1\');"',$this->show_price); 
					if(!$this->show_price) $this->js .='switchDisplay(\'0\',\'price_with_tax_line\',\'1\');switchDisplay(\'0\',\'show_original_price_line\',\'1\');switchDisplay(\'0\',\'show_discount_line\',\'1\');';
				?>
			</td>
		</tr>
		<tr id="price_with_tax_line">
			<td class="key" valign="top">
				<?php echo JText::_('Apply taxes to the prices');?>
			</td>
			<td>
				<?php echo $this->pricetaxType->display($this->control.'[price_with_tax]' , $this->price_with_tax); ?>
			</td>
		</tr>
		<tr id="show_original_price_line">
			<td class="key" valign="top">
				<?php echo JText::_('Display price in original currency');?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', $this->control.'[show_original_price]' , '',$this->show_original_price); ?>
			</td>
		</tr>
		<tr id="show_discount_line">
			<td class="key" valign="top">
				<?php echo JText::_('Display discounted price');?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', $this->control.'[show_discount]' , '',$this->show_discount); ?>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset id="content_category">
	<legend><?php echo JText::_('PARAMS_FOR_CATEGORIES'); ?></legend>
	<table class="admintable" cellspacing="1" width="100%">
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Order field');?>
			</td>
			<td>
				<?php echo $this->orderType->display($this->control.'[category_order]',$this->category_order,'category');?>
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Child categories display');?>
			</td>
			<td>
				<?php echo $this->listType->display($this->control.'[child_display_type]',$this->child_display_type);?>
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Child limit');?>
			</td>
			<td>
				<input name="<?php echo $this->control; ?>[child_limit]" type="text" value="<?php echo @$this->child_limit;?>" />
			</td>
		</tr>
	</table>
</fieldset>
<fieldset id="layout_div">
	<legend><?php echo JText::_('PARAMS_FOR_DIV'); ?></legend>
	<table class="admintable" cellspacing="1" width="100%">
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Type of item layout');?>
			</td>
			<td>
				<?php echo $this->itemType->display($this->control.'[div_item_layout_type]',$this->div_item_layout_type,$this->js);?>
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Item box height');?>
			</td>
			<td>
				<input name="<?php echo $this->control; ?>[height]" type="text" value="<?php echo @$this->height;?>" />
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Item box background color');?>
			</td>
			<td>
				<?php echo $this->colorType->displayAll('',$this->control.'[background_color]',@$this->background_color); ?>
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Item box rounded corners');?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', $this->control.'[rounded_corners]' , '',@$this->rounded_corners); ?>
			</td>
		</tr>
		<tr>
			<td class="key" valign="top">
				<?php echo JText::_('Text centered');?>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', $this->control.'[text_center]' , '',@$this->text_center); ?>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset id="layout_list">
	<legend><?php echo JText::_('PARAMS_FOR_LIST'); ?></legend>
	<table class="admintable" cellspacing="1" width="100%">
	</table>
</fieldset>