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
<form action="index.php?option=com_hikashop&amp;ctrl=field" method="post" name="adminForm" >
	<table cellspacing="1" width="100%">
		<tr>
		<td width="50%" valign="top">
			<table class="paramlist admintable">
				<tr>
					<td class="key">
					<label for="data[field][field_realname]">
						<?php echo JText::_( 'FIELD_LABEL' ); ?>
					</label>
					</td>
					<td>
						<input type="text" name="data[field][field_realname]" id="name" class="inputbox" size="40" value="<?php echo $this->escape(@$this->field->field_realname); ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
					<label for="data[field][field_table]">
						<?php echo JText::_( 'FIELD_TABLE' ); ?>
					</label>
					</td>
					<td>
						<?php
						if(hikashop_level(1) && empty($this->field->field_id)){
							echo $this->tabletype->display('data[field][field_table]',$this->field->field_table,true, 'onchange="setVisible(this.value);"');
						}else{
							echo $this->field->field_table.'<input type="hidden" name="data[field][field_table]" value="'.$this->field->field_table.'" />';
						} ?>
					</td>
				</tr>
				<tr class="columnname">
					<td class="key">
					<label for="data[field][field_namekey]">
						<?php echo JText::_( 'FIELD_COLUMN' ); ?>
					</label>
					</td>
					<td>
					<?php if(empty($this->field->field_id)){?>
						<input type="text" name="data[field][field_namekey]" id="namekey" class="inputbox" size="40" value="" />
					<?php }else { echo $this->field->field_namekey; } ?>
					</td>
				</tr>
				<tr>
					<td class="key">
					<label for="data[field][field_type]">
						<?php echo JText::_( 'FIELD_TYPE' ); ?>
					</label>
					</td>
					<td>
						<?php
						if(!empty($this->field->field_type) && $this->field->field_type=='customtext'){
							$this->fieldtype->addJS();
							echo $this->field->field_type.'<input type="hidden" id="fieldtype" name="data[field][field_type]" value="'.$this->field->field_type.'" />';
						}else{
							echo $this->fieldtype->display('data[field][field_type]',@$this->field->field_type,@$this->field->field_table);
						}						 ?>
					</td>
				</tr>
				<tr class="required">
					<td class="key">
						<label for="data[field][field_required]">
							<?php echo JText::_( 'REQUIRED' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[field][field_required]" , '',@$this->field->field_required); ?>
					</td>
				</tr>
				<tr class="required">
					<td class="key">
						<label for="field_options[errormessage]">
							<?php echo JText::_( 'FIELD_ERROR' ); ?>
						</label>
					</td>
					<td>
						<input type="text" id="errormessage" size="80" name="field_options[errormessage]" value="<?php echo $this->escape(@$this->field->field_options['errormessage']); ?>"/>
					</td>
				</tr>
				<tr class="default">
					<td class="key">
					<label for="data[field][field_default]">
						<?php echo JText::_( 'FIELD_DEFAULT' ); ?>
					</label>
					</td>
					<td>
						<?php echo $this->fieldsClass->display($this->field,@$this->field->field_default,'data[field][field_default]',false,'',true); ?>
					</td>
				</tr>
				<tr class="multivalues">
					<td class="key" valign="top">
					<label for="value">
						<?php echo JText::_( 'FIELD_VALUES' ); ?>
					</label>
					</td>
					<td>
						<table>
						<tbody  id="tablevalues">
						<tr><td><?php echo JText::_('FIELD_VALUE')?></td><td><?php echo JText::_('FIELD_TITLE'); ?></td><td><?php echo JText::_('FIELD_DISABLED'); ?></td></tr>
						<?php if(!empty($this->field->field_value) AND is_array($this->field->field_value) AND $this->field->field_type!='zone'){
							foreach($this->field->field_value as $title => $value){
								$no_selected = 'selected="selected"';
								$yes_selected = '';
								if((int)$value->disabled){
									$no_selected = '';
									$yes_selected = 'selected="selected"';
								}
							?>
								<tr><td><input type="text" name="field_values[title][]" value="<?php echo $this->escape($title); ?>" /></td>
								<td><input type="text" name="field_values[value][]" value="<?php echo $this->escape($value->value); ?>" /></td>
								<td><select name="field_values[disabled][]" class="inputbox">
				<option <?php echo $no_selected; ?> value="0"><?php echo JText::_('HIKASHOP_NO'); ?></option>
				<option <?php echo $yes_selected; ?> value="1"><?php echo JText::_('HIKASHOP_YES'); ?></option>
			</select></td></tr>
						<?php } }?>
						<tr><td><input type="text" name="field_values[title][]" value="" /></td>
						<td><input type="text" name="field_values[value][]" value="" /></td>
						<td>
			<select name="field_values[disabled][]" class="inputbox">
				<option selected="selected" value="0"><?php echo JText::_('HIKASHOP_NO'); ?></option>
				<option value="1"><?php echo JText::_('HIKASHOP_YES'); ?></option>
			</select>
						</td></tr></tbody></table>
						<a onclick="addLine();return false;" href='#' title="<?php echo $this->escape(JText::_('FIELD_ADDVALUE')); ?>"><?php echo JText::_('FIELD_ADDVALUE'); ?></a>
					</td>
				</tr>
				<tr class="cols">
					<td class="key">
					<label for="field_options[cols]">
						<?php echo JText::_( 'FIELD_COLUMNS' ); ?>
					</label>
					</td>
					<td>
						<input type="text"  size="10" name="field_options[cols]" id="cols" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['cols']); ?>"/>
					</td>
				</tr>
				<tr class="filtering">
					<td class="key">
					<label for="field_options[filtering]">
						<?php echo JText::_( 'INPUT_FILTERING' ); ?>
					</label>
					</td>
					<td>
						<?php
						if(!isset($this->field->field_options['filtering'])) $this->field->field_options['filtering'] = 1;
						echo JHTML::_('select.booleanlist', "field_options[filtering]" , '',$this->field->field_options['filtering']); ?>
					</td>
				</tr>
				<tr class="maxlength">
					<td class="key">
					<label for="field_options[maxlength]">
						<?php echo JText::_( 'MAXLENGTH' ); ?>
					</label>
					</td>
					<td>
						<input type="text"  size="10" name="field_options[maxlength]" id="cols" class="inputbox" value="<?php echo (int)@$this->field->field_options['maxlength']; ?>"/>
					</td>
				</tr>
				<tr class="rows">
					<td class="key">
					<label for="field_options[rows]">
						<?php echo JText::_( 'FIELD_ROWS' ); ?>
					</label>
					</td>
					<td>
						<input type="text"  size="10" name="field_options[rows]" id="rows" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['rows']); ?>"/>
					</td>
				</tr>
				<tr class="zone">
					<td class="key">
					<label for="field_options[zone_type]">
						<?php echo JText::_( 'FIELD_ZONE' ); ?>
					</label>
					</td>
					<td>
						<?php echo $this->zoneType->display("field_options[zone_type]",@$this->field->field_options['zone_type'],true);?>
					</td>
				</tr>
				<tr class="size">
					<td class="key">
						<label for="field_options[size]">
							<?php echo JText::_( 'FIELD_SIZE' ); ?>
						</label>
					</td>
					<td>
						<input type="text" id="size" size="10" name="field_options[size]" value="<?php echo $this->escape(@$this->field->field_options['size']); ?>"/>
					</td>
				</tr>
				<tr class="format">
					<td class="key">
						<label for="field_options[format]">
							<?php echo JText::_( 'FORMAT' ); ?>
						</label>
					</td>
					<td>
						<input type="text" id="format" name="field_options[format]" value="<?php echo $this->escape(@$this->field->field_options['format']); ?>"/>
					</td>
				</tr>
				<tr class="customtext">
					<td class="key">
						<label for="size">
							<?php echo JText::_( 'CUSTOM_TEXT' ); ?>
						</label>
					</td>
					<td>
						<textarea cols="50" rows="10" name="fieldcustomtext"><?php echo @$this->field->field_options['customtext']; ?></textarea>
					</td>
				</tr>
				<tr class="allow">
					<td class="key">
						<label for="field_options[allow]">
							<?php echo JText::_( 'ALLOW' ); ?>
						</label>
					</td>
					<td>
						<?php echo $this->allowType->display("field_options[allow]",@$this->field->field_options['allow']);?>
					</td>
				</tr>
			</table>
		</td>
		<td valign="top">
			<table class="paramlist admintable">
				<tr>
					<td class="key">
						<label for="data[field][field_published]">
							<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[field][field_published]" , '',@$this->field->field_published); ?>
					</td>
				</tr>
				<tr class="limit_to">
					<td class="key">
						<label for="field_options[limit_to_parent]">
							<?php echo JText::_( 'DISPLAY_LIMITED_TO' ); ?>
						</label>
					</td>
					<td>
						<?php
						if(hikashop_level(2)){
							if(empty($this->field->field_table)){
								echo JText::_( 'SAVE_THE_FIELD_FIRST_BEFORE' );
							}else{
								echo $this->limitParent->display("field_options[limit_to_parent]",@$this->field->field_options['limit_to_parent'],$this->field->field_table,@$this->field->field_options['parent_value']);
							}
						}else{
							echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
						}
						?>
						<span id="parent_value"></span>
					</td>
				</tr>
				<?php if(hikashop_level(2) && $this->field->field_table=='entry'){ ?>
				<tr class="product_link">
					<td class="key">
						<label for="field_options[product_id]">
							<?php echo JText::_( 'CORRESPOND_TO_PRODUCT' ); ?>
						</label>
					</td>
					<td>
						<span id="product_id" >
							<?php echo (int)@$this->field->field_options['product_id'].' '.@$this->element->product_name; ?>
							<input type="hidden" name="field_options[product_id]" value="<?php echo @$this->field->field_options['product_id']; ?>" />
						</span>
						<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectrelated&select_type=field",true ); ?>">
							<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
						</a>
						<a href="#" onclick="document.getElementById('product_id').innerHTML='<input type=\'hidden\' name=\'field_options[product_id]\' value=\'0\' />';return false;" >
							<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
						</a>
						<br/>
						<label for="field_options[product_value]"><?php echo JText::_( 'FOR_THE_VALUE' ).' '; ?></label>
						<?php echo $this->fieldsClass->display($this->field,@$this->field->field_options['product_value'],'field_options[product_value]',false,'',true); ?>
					</td>
				</tr>
				<?php }?>
				<tr>
					<td class="key">
						<label for="data[field][field_frontcomp]">
							<?php echo JText::_( 'DISPLAY_FRONTCOMP' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[field][field_frontcomp]" , '',@$this->field->field_frontcomp); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[field][field_backend]">
							<?php echo JText::_( 'DISPLAY_BACKEND_FORM' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[field][field_backend]" , '',@$this->field->field_backend); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="data[field][field_backend_listing]">
							<?php echo JText::_( 'DISPLAY_BACKEND_LISTING' ); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('select.booleanlist', "data[field][field_backend_listing]" , '',@$this->field->field_backend_listing); ?>
					</td>
				</tr>
			</table>
			<?php
				if($this->field->field_table=="product" || $this->field->field_table=="item" || $this->field->field_table=="category"){
					$fieldsetDisplay="";
				}else{
					$fieldsetDisplay='style="display:none"';
				}
				?>
			<fieldset <?php echo $fieldsetDisplay; ?> style="width:50%;" id="category_field">
				<legend><?php echo JText::_( 'HIKA_CATEGORIES' ); ?></legend>
				<div style="text-align:right;">
					<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("product&task=selectcategory",true ); ?>">
						<button type="button" onclick="return false">
							<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
						</button>
					</a>
				</div>
				<br/>
				<table class="adminlist" cellpadding="1" width="100%">
					<thead>
						<tr>
							<th class="title">
								<?php echo JText::_('HIKA_NAME'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('HIKA_DELETE'); ?>
							</th>
							<th class="title">
								<?php echo JText::_('ID'); ?>
							</th>
						</tr>
					</thead>
					<tbody id="category_listing">
						<?php
							if(!empty($this->categories)){
								$k = 0;
								for($i = 1,$a = count($this->categories)+1;$i<$a;$i++){
									$row =& $this->categories[$i];
									if(!empty($row->category_id)){
									?>
										<tr id="category_<?php echo $row->category_id;?>">
											<td>
												<div id="category_<?php echo $row->category_id; ?>_id">
												<a href="<?php echo hikashop_completeLink('category&task=edit&cid='.$row->category_id); ?>"><?php echo $row->category_name; ?></a>
											</td>
											<td align="center">
												<a href="#" onclick="return deleteRow('category_div_<?php echo $row->category_id;?>','category[<?php echo $row->category_id;?>]','category_<?php echo $row->category_id; ?>');">
													<img src="../media/com_hikashop/images/delete.png"/>
												</a>
											</td>
											<td width="1%" align="center">
												<?php echo $row->category_id; ?>
												<div id="category_div_<?php echo $row->category_id;?>">
													<input style="width: 50px; background-color:#e8f9db;" type	="hidden" name="category[<?php echo $row->category_id;?>]" id="category[<?php echo $row->category_id;?>]" value="<?php echo $row->category_id;?>"/>
												</div>
											</td>
										</tr>
									<?php
									}
									$k = 1-$k;
								}
							}
						?>
					</tbody>
				</table>
				<br/>
				<table class="paramlist admintable">
					<tr>
						<td class="key">
							<label for="data[field][field_with_sub_categories]">
								<?php echo JText::_( 'INCLUDING_SUB_CATEGORIES' ); ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('select.booleanlist', "data[field][field_with_sub_categories]" , '',@$this->field->field_with_sub_categories); ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
				<?php
				if(hikashop_level(2)){
					$acltype = hikashop_get('type.acl');
					echo $acltype->display('field_access',@$this->field->field_access,'field');
				}else{
					echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
				} ?>
			</fieldset>
			<?php if(!empty($this->field->field_id)){ ?>
			<br/><br/>
			<fieldset>
			<legend><?php echo JText::_('PREVIEW'); ?></legend>
			<table class="admintable"><tr><td class="key"><?php echo $this->fieldsClass->getFieldName($this->field); ?></td><td><?php echo $this->fieldsClass->display($this->field,$this->field->field_default,'data['.$this->field->field_table.']['.$this->field->field_namekey.']',false,'',true); ?></td></tr></table>
			</fieldset>
			<?php } ?>
		</td>
		</tr>
	</table>
	<?php
	if(hikashop_level(2) && !empty($this->field->field_id) && in_array($this->field->field_type,array('radio','singledropdown','zone'))){
		$this->fieldsClass->chart($this->field->field_table,$this->field);
	}?>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->field->field_id; ?>" />
	<input type="hidden" name="option" value="com_hikashop" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="field" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
