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
<form action="<?php echo hikashop_completeLink('taxation');?>" method="post" name="adminForm">
	<center>
	<table class="admintable">
		<tr>
			<td class="key">
				<label for="data[taxation][taxation_category]">
					<?php echo JText::_( 'TAXATION_CATEGORY' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->category->display( "data[taxation][category_namekey]" , @$this->element->category_namekey ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[taxation][tax_namekey]">
					<?php echo JText::_( 'RATE' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->ratesType->display( "data[taxation][tax_namekey]" , @$this->element->tax_namekey ); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[taxation][zone_namekey]">
					<?php echo JText::_( 'ZONE' ); ?>
				</label>
			</td>
			<td>
				<span id="zone_id" >
					<?php echo (int)@$this->element->zone_id.' '.@$this->element->zone_name_english; ?>
					<input type="hidden" name="data[taxation][zone_namekey]" value="<?php echo @$this->element->zone_namekey; ?>" />
				</span>
				<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("zone&task=selectchildlisting&type=tax",true ); ?>">
					<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
				</a>
				<a href="#" onclick="document.getElementById('zone_id').innerHTML='0 <?php echo $this->escape(JText::_('ZONE_NOT_FOUND'));?>';return false;" >
					<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="delete"/>
				</a>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[taxation][taxation_type]">
					<?php echo JText::_( 'CUSTOMER_TYPE' ); ?>
				</label>
			</td>
			<td>
				<?php echo $this->taxType->display( "data[taxation][taxation_type]" , @$this->element->taxation_type ); ?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<fieldset>
					<legend><?php echo JText::_('ACCESS_LEVEL'); ?></legend>
					<?php
					if(hikashop_level(2)){
						$acltype = hikashop_get('type.acl');
						echo $acltype->display('taxation_access',@$this->element->taxation_access,'taxation');
					}else{
						echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
					} ?>
				</fieldset>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="data[taxation][taxation_published]">
					<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
				</label>
			</td>
			<td>
				<?php echo JHTML::_('select.booleanlist', "data[taxation][taxation_published]" , '',@$this->element->taxation_published	); ?>
			</td>
		</tr>
	</table>
	</center>
	<div class="clr"></div>
	<input type="hidden" name="taxation_id" value="<?php echo @$this->element->taxation_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT;?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getString('ctrl');?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>