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
<div id="page-affiliate">
	<fieldset class="adminform">
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('PARTNER_KEY'); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="params[system][hikashopaffiliate][partner_key_name]" value="<?php echo $this->escape($this->affiliate_params['partner_key_name']); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('PARTNER_CLICK_FEE'); ?>
				</td>
				<td>
					<input class="inputbox" size="5" type="text" name="config[partner_click_fee]" value="<?php echo $this->config->get('partner_click_fee'); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('PARTNER_LEAD_FEE'); ?>
				</td>
				<td>
					<input class="inputbox" size="5" type="text" name="config[partner_lead_fee]" value="<?php echo $this->config->get('partner_lead_fee'); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('PARTNER_ORDER_PERCENT_FEE'); ?>
				</td>
				<td>
					<input class="inputbox" size="5" type="text" name="config[partner_percent_fee]" value="<?php echo $this->config->get('partner_percent_fee'); ?>" />%
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('PARTNER_ORDER_FLAT_FEE'); ?>
				</td>
				<td>
					<input class="inputbox" size="5" type="text" name="config[partner_flat_fee]" value="<?php echo $this->config->get('partner_flat_fee'); ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('VALID_ORDER_STATUS'); ?>
				</td>
				<td>
					<input id="partner_valid_status" name="config[partner_valid_status]" value="<?php echo @$this->config->get('partner_valid_status'); ?>" />
					<a id="link_partner_valid_status" class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("category&task=selectstatus&control=partner_valid_status&values=".$this->config->get('partner_valid_status'),true ); ?>">
						<img src="<?php echo HIKASHOP_IMAGES; ?>edit.png" alt="edit"/>
					</a>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('VALIDITY_PERIOD'); ?>
				</td>
				<td>
					<?php echo $this->delayTypeAffiliate->display('config[click_validity_period]', $this->config->get('click_validity_period',2592000),3); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('CLICK_MINIMUM_DELAY'); ?>
				</td>
				<td>
					<?php echo $this->delayTypeClick->display('config[click_min_delay]', $this->config->get('click_min_delay',86400)); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('AFFILIATE_PAYMENT_DELAY'); ?>
				</td>
				<td>
					<?php echo $this->delayTypeOrder->display('config[affiliate_payment_delay]', $this->config->get('affiliate_payment_delay',0),3); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('AFFILIATE_TERMS'); ?>
				</td>
				<td>
					<input class="inputbox" id="affiliate_terms" name="config[affiliate_terms]" type="text" size="20" value="<?php echo $this->config->get('affiliate_terms'); ?>">
					<?php
					if(version_compare(JVERSION,'1.6','<')){
						$link = 'index.php?option=com_content&amp;task=element&amp;tmpl=component&amp;object=affiliate';
					}else{
						$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;object=content&amp;function=jSelectArticle_terms';
						$js = "
						function jSelectArticle_terms(id, title, catid, object) {
							document.getElementById('affiliate_terms').value = id;
							SqueezeBox.close();
						}";
						$doc =& JFactory::getDocument();
						$doc->addScriptDeclaration($js);
					}
					?>
					<a class="modal" id="affiliate_terms_link" title="<?php echo JText::_('Select one article which will be displayed for the affiliate program Terms & Conditions'); ?>"  href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 650, y: 375}}"><button onclick="return false"><?php echo JText::_('Select'); ?></button></a>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('BECOME_PARTNER_QUESTION_REGISTRATION'); ?>
				</td>
				<td>
					<?php echo JHTML::_('select.booleanlist', "config[affiliate_registration]" , '', $this->config->get('affiliate_registration',0)); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('QUESTION_REGISTRATION_DEFAULT'); ?>
				</td>
				<td>
					<?php echo JHTML::_('select.booleanlist', "config[affiliate_registration_default]" , '', $this->config->get('affiliate_registration_default',0)); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('PARTNER_CURRENCY'); ?>
				</td>
				<td>
					<?php echo $this->currency->display('config[partner_currency]', $this->config->get('partner_currency')); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('ALLOW_CURRENCY_SELECTION'); ?>
				</td>
				<td>
					<?php echo JHTML::_('select.booleanlist', "config[allow_currency_selection]" , '', $this->config->get('allow_currency_selection')); ?>
				</td>
			</tr>
			<tr>
				<td class="key" >
					<?php echo JText::_('AFFILIATE_ADVANCED_STATS'); ?>
				</td>
				<td>
					<?php echo JHTML::_('select.booleanlist', "config[affiliate_advanced_stats]" , '', $this->config->get('affiliate_advanced_stats')); ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>