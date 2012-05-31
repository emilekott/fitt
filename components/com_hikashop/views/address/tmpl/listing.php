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
<div id="hikashop_address_listing">
<?php if($this->user_id){ ?>
<fieldset>
	<div class="header hikashop_header_title"><h1><?php echo JText::_('ADDRESSES');?></h1></div>
	<div class="toolbar hikashop_header_buttons" id="toolbar" >
		<table>
			<tr>
				<td>
					<a  class="modal" rel="{handler: 'iframe', size: {x: 450, y: 480}}" href="<?php echo hikashop_completeLink('address&task=add',true);?>">
						<span class="icon-32-new" title="<?php echo JText::_('HIKA_NEW'); ?>">
						</span>
						<?php echo JText::_('HIKA_NEW'); ?>
					</a>
				</td>
				<td>
					<a href="<?php echo hikashop_completeLink('user');?>" >
						<span class="icon-32-cancel" title="<?php echo JText::_('HIKA_CANCEL'); ?>">
						</span>
						<?php echo JText::_('HIKA_CANCEL'); ?>
					</a>
				</td>
			</tr>
		</table>
	</div>
</fieldset>
<div class="iframedoc" id="iframedoc"></div>
<?php
	if(!empty($this->addresses)){
?>
<div class="hikashop_address_listing_div">
<table class="hikashop_address_listing_table">
<?php
		foreach($this->addresses as $address){
			$this->address =& $address;
	?>
	<tr class="hikashop_address_listing_item">
		<td class="hikashop_address_listing_item_details">
			<span>
	<?php
			$this->setLayout('address_template');
			$html = $this->loadTemplate();
			foreach($this->fields as $field){
				$fieldname = $field->field_namekey;
				$html=str_replace('{'.$fieldname.'}',$this->fieldsClass->show($field,$address->$fieldname),$html);
			}
			echo str_replace("\n","<br/>\n",str_replace("\n\n","\n",preg_replace('#{(?:(?!}).)*}#i','',$html)));
	?>
			</span>
		</td>
		<td class="hikashop_address_listing_item_actions">
			<?php global $Itemid; ?>
			<a href="<?php echo hikashop_completeLink('address&task=delete&address_id='.$address->address_id.'&'.JUtility::getToken().'=1&Itemid='.$Itemid);?>"><img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" alt="<?php echo JText::_('HIKA_DELETE'); ?>" /></a>
			<a class="modal" rel="{handler: 'iframe', size: {x: 450, y: 480}}" href="<?php echo hikashop_completeLink('address&task=edit&address_id='.$address->address_id.'&Itemid='.$Itemid,true);?>"><img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="<?php echo JText::_('HIKA_EDIT'); ?>" /></a>
		</td>
	</tr>
	<?php
		}
?>
</table>
</div>
<?php
	}
}
?>
</div>
<div class="clear_both"></div>