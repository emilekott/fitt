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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=category" method="post" name="adminForm" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td width="70%" valign="top">
				<fieldset class="adminform" id="htmlfieldset_info">
					<legend><?php echo JText::_( 'MAIN_INFORMATION' ); ?></legend>
					<?php
						$this->category_name_input = "data[category][category_name]";
						$this->category_meta_description_input = "data[category][category_meta_description]";
						$this->category_keywords_input = "data[category][category_keywords]";
						if($this->translation){
							$this->setLayout('translation');
						}else{
							$this->setLayout('normal');
						}
						echo $this->loadTemplate();
					?>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset class="adminform" id="htmlfieldset_additional">
					<legend><?php echo JText::_( 'CATEGORY_ADDITIONAL_INFORMATION' ); ?></legend>
					<table class="admintable" style="">
						<tr>
							<td class="key">
								<label for="category_published">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "data[category][category_published]" , '',@$this->element->category_published	); ?>
							</td>
						</tr>
						<?php
						if(!empty($this->fields)){?>
							<?php foreach($this->fields as $fieldName => $oneExtraField){
								if(!$oneExtraField->field_backend){ ?>
								<tr><td><input type="hidden" name="data[category][<?php echo $fieldName; ?>]" value="<?php echo $this->element->$fieldName; ?>" /></td></tr>
								<?php }else{ ?>
								<tr id='hikashop_category_<?php echo $fieldName; ?>'>
									<td class="key">
										<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
									</td>
									<td>
										<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick'; ?>
										<?php echo $this->fieldsClass->display($oneExtraField,$this->element->$fieldName,'data[category]['.$fieldName.']',false,' '.$onWhat.'="hikashopToggleFields(this.value,\''.$fieldName.'\',\'category\',0);"'); ?>
									</td>
								</tr>
								<?php }
							} ?>
						<?php }
						?>
					</table>
					<?php if(isset($this->element->category_namekey) && in_array($this->element->category_namekey,array('root','product','tax','status','created','confirmed','cancelled','refunded','shipped','manufacturer'))){?>
						<input type="hidden" name="data[category][category_parent_id]" value="<?php echo @$this->element->category_parent_id; ?>" />
					<?php }else{?>
						<table class="admintable" id="category_parent">
							<tr>
								<td class="key">
									<label for="category_parent">
										<?php echo JText::_( 'CATEGORY_PARENT' ); ?>
									</label>
								</td>
								<td>
									<span id="changeParent">
										<?php echo @$this->element->category_parent_id.' '.@$this->element->category_parent_name; ?>
										<input type="hidden" name="data[category][category_parent_id]" value="<?php echo @$this->element->category_parent_id; ?>" />
									</span>
									<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("category&task=selectparentlisting&filter_id=".@$this->element->category_parent_id,true ); ?>">
										<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
									</a>
								</td>
							</tr>
						</table>
					<?php }
					?>
				</fieldset>
				<?php if($this->category_image){ ?>
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'HIKA_IMAGE' ); ?></legend>
					<span id="category_image-<?php echo @$this->element->file_id;?>">
						<?php echo $this->image->display(@$this->element->file_path); ?>
					<span class="spanloading"><?php if(!empty($this->element->file_path)) echo $this->toggle->delete("category_image-".$this->element->file_id,'category-'.$this->element->category_id,'file',true); ?></span><br/></span>
					<input type="file" name="files[]" size="30" />
					<?php echo JText::sprintf('MAX_UPLOAD',(hikashop_bytes(ini_get('upload_max_filesize')) > hikashop_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize')); ?>
				</fieldset>
				<?php } ?>
				<fieldset>
				<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
				<?php
				if(hikashop_level(2)){
					$acltype = hikashop_get('type.acl');
					echo $acltype->display('category_access',@$this->element->category_access,'category');
				}else{
					echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
				} ?>
				</fieldset>
			</td>
		</tr>
  	</table>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->category_id; ?>" />
	<input type="hidden" name="data[category][category_id]" value="<?php echo @$this->element->category_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="category" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>