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
<?php
	$type = $this->type;
	foreach($this->extraFields[$type] as $fieldName => $oneExtraField) {
	?>
		<tr class="hikashop_registration_<?php echo $fieldName;?>_line">
			<td class="key">
				<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
			</td>
			<td>
				<?php echo $this->fieldsClass->display($oneExtraField,$this->$type->$fieldName,'data['.$type.']['.$fieldName.']'); ?>
			</td>
		</tr>
	<?php }	?>