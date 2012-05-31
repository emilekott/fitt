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
<div id="config_languages">
	<fieldset>
		<table class="admintable" cellspacing="1">
			<tr>
				<td class="key" >
					<?php echo JText::_('MULTI_LANGUAGE_EDIT'); ?>
				</td>
				<td>
					<?php
					if(hikashop_level(1)){
						$translationHelper = hikashop_get('helper.translation');
						if($translationHelper->isMulti(true)){
							echo JHTML::_('select.booleanlist', "config[multi_language_edit]" , '', $this->config->get('multi_language_edit'));
						}else{
							echo JText::_('INSTALL_JOOMFISH');
						}
					}else{
						echo JHTML::_('select.booleanlist', "config[multi_language_edit]" , ' DISABLED', 0).' <small style="color:red">'.JText::_('ONLY_COMMERCIAL').'</small>';
					}
					?>
				</td>
			</tr>
			<?php if(hikashop_level(1)){ ?>
			<tr>
				<td class="key" >
				<?php echo JText::_('DEFAULT_TRANSLATION_PUBLISH'); ?>
				</td>
				<td>
					<?php echo JHTML::_('select.booleanlist', "config[default_translation_publish]" , '',$this->config->get('default_translation_publish',0) );?>
				</td>
			</tr>
			<?php if(hikashop_level(9)){ ?>
				<tr>
					<td class="key" >
					<?php echo JText::_('MUTLILANGUAGE_INTERFACE_DISPLAY'); ?>
					</td>
					<td>
						<?php echo $this->multilang->display("config[multilang_display]" , $this->config->get('multilang_display','tabs') );?>
					</td>
				</tr>
			<?php }
			} ?>
		</table>
	</fieldset>
	<fieldset>
		<legend><?php echo JText::_('LANGUAGES') ?></legend>
		<table class="adminlist" cellpadding="1">
			<thead>
				<tr>
					<th class="title titlenum">
						<?php echo JText::_( 'HIKA_NUM' );?>
					</th>
					<th class="title titletoggle">
						<?php echo JText::_('HIKA_EDIT'); ?>
					</th>
					<th class="title">
						<?php echo JText::_('HIKA_NAME'); ?>
					</th>
					<th class="title titletoggle">
						<?php echo JText::_('ID'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$k = 0;
					for($i = 0,$a = count($this->languages);$i<$a;$i++){
						$row =& $this->languages[$i];
				?>
					<tr class="<?php echo "row$k"; ?>">
						<td align="center">
						<?php echo $i+1; ?>
						</td>
						<td  align="center">
							<?php if($this->manage) echo $row->edit; ?>
						</td>
						<td align="center">
							<?php echo $row->name; ?>
						</td>
						<td align="center">
							<?php echo $row->language; ?>
						</td>
					</tr>
				<?php
						$k = 1-$k;
					}
				?>
			</tbody>
		</table>
	</fieldset>
</div>