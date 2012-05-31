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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=product" method="post" name="adminForm" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td width="70%" valign="top">
				<?php
					$this->product_name_input = "data[product][product_name]";
					$this->product_url_input = "data[product][product_url]";
					$this->product_meta_description_input = "data[product][product_meta_description]";
					$this->product_keywords_input = "data[product][product_keywords]";
					if($this->translation){
						$this->setLayout('translation');
						echo $this->loadTemplate();
					}else{
						?>
						<fieldset class="adminform" id="htmlfieldset">
							<legend><?php echo JText::_( 'MAIN_INFORMATION' ); ?></legend>
							<?php
								$this->setLayout('normal');
								echo $this->loadTemplate();
							?>
						</fieldset>
						<?php
					}
				?>
				<?php
					if($this->element->product_type=='main'){
						$this->setLayout('info');
						echo $this->loadTemplate();
					}else{
						$this->setLayout('infovariant');
						echo $this->loadTemplate();
					}
					if(!empty($this->fields)){?>
						<table class="admintable" width="100%">
						<?php foreach($this->fields as $fieldName => $oneExtraField){
							if(!$oneExtraField->field_backend){ 
								if($oneExtraField->field_type != "customtext"){?>
							<tr><td><input type="hidden" name="data[product][<?php echo $fieldName; ?>]" value="<?php echo $this->element->$fieldName; ?>" /></td></tr>
							<?php }
							}else{ ?>
							<tr id="hikashop_product_<?php echo $fieldName; ?>">
								<td class="key">
									<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
								</td>
								<td>
									<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick'; ?>
									<?php echo $this->fieldsClass->display($oneExtraField,$this->element->$fieldName,'data[product]['.$fieldName.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'product\',0);"'); ?>
								</td>
							</tr>
							<?php }
						} ?>
						</table>
					<?php }?>
			</td>
			<td valign="top">
				<?php if($this->element->product_type=='main'){?>
					<fieldset class="adminform hikashop_product_categories" id="htmlfieldset">
						<legend><?php echo JText::_( 'HIKA_CATEGORIES' ); ?></legend>
					<?php
							$this->setLayout('category');
							echo $this->loadTemplate();
					?>
					</fieldset>
					<fieldset class="adminform hikashop_product_related" id="htmlfieldset">
						<legend><?php echo JText::_( 'RELATED_PRODUCTS' ); ?></legend>
					<?php
							$this->type='related';
							$this->setLayout('related');
							echo $this->loadTemplate();
					?>
					</fieldset>
					<fieldset class="adminform hikashop_product_options" id="htmlfieldset">
						<legend><?php echo JText::_( 'OPTIONS' ); ?></legend>
					<?php
					if(hikashop_level(1)){
						$this->type='options';
						$this->setLayout('related');
						echo $this->loadTemplate();
					}else{
						echo '<small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
					}
					?>
					</fieldset>
					<fieldset class="adminform hikashop_product_characteristics" id="htmlfieldset">
						<legend><?php echo JText::_('CHARACTERISTICS');?></legend>
						<?php
							$this->setLayout('characteristic');
							echo $this->loadTemplate();
						?>
					</fieldset>
				<?php }?>
				<fieldset class="adminform hikashop_product_prices" id="htmlfieldset">
					<legend><?php echo JText::_('PRICES');?></legend>
					<?php
						$this->setLayout('price');
						echo $this->loadTemplate();
					?>
				</fieldset>
				<fieldset class="adminform hikashop_product_images" id="htmlfieldset">
					<legend><?php echo JText::_('HIKA_IMAGES');?></legend>
					<?php
						$this->setLayout('image');
						echo $this->loadTemplate();
					?>
				</fieldset>
				<fieldset class="adminform hikashop_product_files" id="htmlfieldset">
					<legend><?php echo JText::_('HIKA_FILES');?></legend>
					<?php
						$this->setLayout('file');
						echo $this->loadTemplate();
					?>
				</fieldset>
			</td>
		</tr>
  	</table>
	<div class="clr"></div>
	<input type="hidden" name="data[product][product_type]" value="<?php echo @$this->element->product_type; ?>" />
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->product_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="product" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>