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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=characteristic" method="post" name="adminForm" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td width="50%" valign="top">
				<?php echo $this->loadTemplate('item');?>
			</td>
			<td valign="top">
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'VALUES' ); ?></legend>
					<?php 
						$this->setLayout('form_value');
						echo $this->loadTemplate();
					?>
				</fieldset>
			</td>
		</tr>
  	</table>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->characteristic_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="characteristic" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>