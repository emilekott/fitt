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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=currency" method="post" name="adminForm" enctype="multipart/form-data">
	<table width="100%">
		<tr>
			<td width="50%" valign="top">
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'CURRENCY_INFORMATION' ); ?></legend>
					<table class="admintable" width="280px" style="margin:auto">
						<tr>
							<td class="key">
								<label for="currency_name">
									<?php echo JText::_( 'HIKA_NAME' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_name]" value="<?php echo $this->escape(@$this->element->currency_name); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="currency_code">
									<?php echo JText::_( 'CURRENCY_CODE' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_code]" value="<?php echo @$this->element->currency_code; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="currency_symbol">
									<?php echo JText::_( 'CURRENCY_SYMBOL' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_symbol]" value="<?php echo @$this->element->currency_symbol; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="currency_rate">
									<?php echo JText::_( 'RATE' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_rate]" value="<?php echo @$this->element->currency_rate; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<?php echo JText::_( 'HIKA_LAST_MODIFIED' ); ?>
							</td>
							<td>
								<?php echo hikashop_getDate(@$this->element->currency_modified); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="currency_percent_fee">
									<?php echo JText::_( 'CURRENCY_PERCENT_FEE' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_percent_fee]" value="<?php echo @$this->element->currency_percent_fee; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="currency_published">
									<?php echo JText::_( 'HIKA_PUBLISHED' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "data[currency][currency_published]" , '',@$this->element->currency_published	); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="currency_displayed">
									<?php echo JText::_( 'CURRENCY_DISPLAYED' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "data[currency][currency_displayed]" , '',@$this->element->currency_displayed	); ?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
			<td valign="top">
				<fieldset class="adminform" id="htmlfieldset">
					<legend><?php echo JText::_( 'LOCALE_INFORMATION' ); ?></legend>
					<table class="admintable" width="280px" style="margin:auto">
						<tr>
							<td class="key">
								<label for="currency_format">
									<?php echo JText::_( 'CURRENCY_FORMAT' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_format]" value="<?php echo $this->escape(@$this->element->currency_format); ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="mon_decimal_point">
									<?php echo JText::_( 'MON_DECIMAL_POINT' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_locale][mon_decimal_point]" value="<?php echo @$this->element->currency_locale['mon_decimal_point']; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="mon_thousands_sep">
									<?php echo JText::_( 'MON_THOUSANDS_SEP' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_locale][mon_thousands_sep]" value="<?php echo @$this->element->currency_locale['mon_thousands_sep']; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="mon_grouping">
									<?php echo JText::_( 'MON_GROUPING' ); ?>
								</label>
							</td>
							<td>
								<?php if(isset($this->element->currency_locale['mon_grouping']) && is_array($this->element->currency_locale['mon_grouping'])) $this->element->currency_locale['mon_grouping'] = implode(',',$this->element->currency_locale['mon_grouping']); ?>
								<input type="text" name="data[currency][currency_locale][mon_grouping]" value="<?php echo @$this->element->currency_locale['mon_grouping']; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="positive_sign">
									<?php echo JText::_( 'POSITIVE_SIGN' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_locale][positive_sign]" value="<?php echo @$this->element->currency_locale['positive_sign']; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="negative_sign">
									<?php echo JText::_( 'NEGATIVE_SIGN' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_locale][negative_sign]" value="<?php echo @$this->element->currency_locale['negative_sign']; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="int_frac_digits">
									<?php echo JText::_( 'INT_FRAC_DIGITS' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_locale][int_frac_digits]" value="<?php echo @$this->element->currency_locale['int_frac_digits']; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="frac_digits">
									<?php echo JText::_( 'FRAC_DIGITS' ); ?>
								</label>
							</td>
							<td>
								<input type="text" name="data[currency][currency_locale][frac_digits]" value="<?php echo @$this->element->currency_locale['frac_digits']; ?>" />
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="p_cs_precedes">
									<?php echo JText::_( 'P_CS_PRECEDES' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "data[currency][currency_locale][p_cs_precedes]" , '',@$this->element->currency_locale['p_cs_precedes']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="p_sep_by_space">
									<?php echo JText::_( 'P_SEP_BY_SPACE' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "data[currency][currency_locale][p_sep_by_space]" , '',@$this->element->currency_locale['p_sep_by_space']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="n_cs_precedes">
									<?php echo JText::_( 'N_CS_PRECEDES' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "data[currency][currency_locale][n_cs_precedes]" , '',@$this->element->currency_locale['n_cs_precedes']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="n_sep_by_space">
									<?php echo JText::_( 'N_SEP_BY_SPACE' ); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('select.booleanlist', "data[currency][currency_locale][n_sep_by_space]" , '',@$this->element->currency_locale['n_sep_by_space']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="p_sign_posn">
									<?php echo JText::_( 'P_SIGN_POSN' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->signpos->display('data[currency][currency_locale][p_sign_posn]',@$this->element->currency_locale['p_sign_posn']); ?>
							</td>
						</tr>
						<tr>
							<td class="key">
								<label for="n_sign_posn">
									<?php echo JText::_( 'N_SIGN_POSN' ); ?>
								</label>
							</td>
							<td>
								<?php echo $this->signpos->display('data[currency][currency_locale][n_sign_posn]',@$this->element->currency_locale['n_sign_posn']); ?>
							</td>
						</tr>
					</table>
				</fieldset>
			</td>
		</tr>
  	</table>
	<div class="clr"></div>
	<input type="hidden" name="cid[]" value="<?php echo @$this->element->currency_id; ?>" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="currency" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>