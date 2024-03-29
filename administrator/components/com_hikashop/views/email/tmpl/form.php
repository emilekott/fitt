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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>" method="post" name="adminForm" enctype="multipart/form-data">
	<?php if(JRequest::getString('tmpl')=='component'){ ?>
		<fieldset>
			<div class="toolbar" id="toolbar" style="float: right;">
				<button type="button" onclick="javascript:submitbutton('apply'); return false;"><img src="<?php echo HIKASHOP_IMAGES; ?>save.png"/><?php echo JText::_('HIKA_SAVE',true); ?></button>
			</div>
		</fieldset>
	<?php }
	echo $this->loadTemplate('param'); ?>
		<br/>
		<fieldset class="adminform" id="htmlfieldset">
			<legend><?php echo JText::_( 'HTML_VERSION' ); ?></legend>
			<?php echo $this->editor->displayCode('data[mail][body]',@$this->mail->body); ?>
		</fieldset>
		<fieldset class="adminform" >
			<legend><?php echo JText::_( 'TEXT_VERSION' ); ?></legend>
			<textarea style="width:100%" rows="20" name="data[mail][altbody]" id="altbody" ><?php echo @$this->mail->altbody; ?></textarea>
		</fieldset>
	<div class="clr"></div>
	<input type="hidden" name="mail_name" value="<?php echo @$this->mail_name; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="tmpl" value="<?php echo JRequest::getCmd('tmpl', 'index'); ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="email" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>