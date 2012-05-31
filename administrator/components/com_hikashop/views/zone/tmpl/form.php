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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=zone" method="post" name="adminForm" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td width="350" valign="top">
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'ZONE_INFORMATION' ); ?></legend>
					<?php 
					$this->setLayout('information');
					echo $this->loadTemplate();
					?>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'SUBZONES' ); ?></legend>
					<?php if(empty($this->element->zone_namekey)){
						echo JText::_( 'SUBZONES_CHOOSER_DISABLED' );
					}else{
						$this->setLayout('childlisting');
						echo $this->loadTemplate();
					} ?>
				</fieldset>
			</td>
		</tr>
  	</table>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->zone_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="zone" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>