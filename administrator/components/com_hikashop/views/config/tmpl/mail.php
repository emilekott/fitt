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
<div id="page-mail">
	<table width="100%">
		<tr>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'SENDER_INFORMATIONS' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td width="185" class="key">
								<?php echo JText::_('FROM_NAME'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[from_name]" size="40" value="<?php echo $this->escape($this->config->get('from_name')); ?>">
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('FROM_ADDRESS'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[from_email]" size="40" value="<?php echo $this->escape($this->config->get('from_email')); ?>">
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('REPLYTO_NAME'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[reply_name]" size="40" value="<?php echo $this->escape($this->config->get('reply_name')); ?>">
							</td>
						</tr>
						<tr>
							<td class="key">
							<?php echo JText::_('REPLYTO_ADDRESS'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[reply_email]" size="40" value="<?php echo $this->escape($this->config->get('reply_email')); ?>">
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('BOUNCE_ADDRESS'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[bounce_email]" size="40" value="<?php echo $this->escape($this->config->get('bounce_email')); ?>">
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('PAYMENTS_NOTIFICATIONS_EMAIL_ADDRESS'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[payment_notification_email]" size="40" value="<?php echo $this->escape($this->config->get('payment_notification_email')); ?>">
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ORDER_CREATION_NOTIFICATION_EMAIL_ADDRESS'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[order_creation_notification_email]" size="40" value="<?php echo $this->escape($this->config->get('order_creation_notification_email')); ?>">
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset class="adminform">
					<legend><?php echo JText::_( 'EMAILS_OPTIONS' ); ?></legend>
					<table class="admintable" cellspacing="1">
						<tr>
							<td class="key">
								<?php echo JText::_('ADD_NAMES'); ?>
							</td>
							<td>
								<?php echo $this->elements->add_names; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('ENCODING_FORMAT'); ?>
							</td>
							<td>
								<?php echo $this->elements->encoding_format; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('CHARSET'); ?>
							</td>
							<td>
								<?php echo $this->elements->charset; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('WORD_WRAPPING'); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[word_wrapping]" size="10" value="<?php echo $this->config->get('word_wrapping',0) ?>">
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('EMBED_IMAGES'); ?>
							</td>
							<td>
								<?php echo $this->elements->embed_images; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('EMBED_ATTACHMENTS'); ?>
							</td>
							<td>
								<?php echo $this->elements->embed_files; ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_('MULTIPLE_PART'); ?>
							</td>
							<td>
								<?php echo $this->elements->multiple_part; ?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
	</table>
</div>