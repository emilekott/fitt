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
<?php $js ='
function hikashopRemoveCustom(id){
	if(confirm(\''.JText::_('HIKA_VALIDDELETEITEMS',true).'\')){
		document.getElementById(\'view_id\').value = id;
		submitform(\'remove\');
	}
	return false;
}';
$doc =& JFactory::getDocument();
$doc->addScriptDeclaration($js);
?>
<div class="iframedoc" id="iframedoc"></div>
<form action="<?php echo hikashop_completeLink('view'); ?>" method="post" name="adminForm">
	<?php if($this->ftp){ ?>
	<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>">
		<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>
		<?php echo JText::_('DESCFTP'); ?>
		<?php if(JError::isError($this->ftp)){ ?>
			<p><?php echo JText::_($this->ftp->message); ?></p>
		<?php } ?>
		<table class="adminform nospace">
		<tbody>
		<tr>
			<td width="120">
				<label for="username"><?php echo JText::_('HIKA_USERNAME'); ?>:</label>
			</td>
			<td>
				<input type="text" id="username" name="username" class="input_box" size="70" value="" />
			</td>
		</tr>
		<tr>
			<td width="120">
				<label for="password"><?php echo JText::_('HIKA_PASSWORD'); ?>:</label>
			</td>
			<td>
				<input type="password" id="password" name="password" class="input_box" size="70" value="" />
			</td>
		</tr>
		</tbody>
		</table>
	</fieldset>
	<?php } ?>
	<table>
		<tr>
			<td width="100%">
				<?php echo JText::_( 'FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->escape($this->pageInfo->search);?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'RESET' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<?php echo $this->templateType->display("template",$this->pageInfo->filter->template,$this->templateValues);?>
				<?php echo $this->viewType->display("client_id",$this->pageInfo->filter->client_id);?>
			</td>
		</tr>
	</table>
	<table class="adminlist" cellpadding="1">
		<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_( 'HIKA_NUM' );?>
				</th>
				<th class="title">
					<?php echo JText::_('CLIENT'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_TEMPLATE'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_VIEW'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('HIKA_FILE'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('REMOVE_CUSTOMIZATION'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
				$k = 0;
				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
			?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
					<?php echo $this->pagination->getRowOffset($i);
					?>
					</td>
					<td>
						<?php
						if($row->client_id){
							echo JText::_('BACK_END');
						}else{
							echo JText::_('FRONT_END');
						}
						?>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_templates&task=edit&cid[]='.strip_tags($row->template).'&client='.$row->client_id); ?>">
							<?php echo $row->template; ?>
						</a>
					</td>
					<td>
						<?php echo $row->view; ?>
					</td>
					<td>
						<?php if($this->manage){ ?>
							<a href="<?php echo hikashop_completeLink('view&task=edit&id='.strip_tags($row->id));?>">
						<?php } ?>
								<?php echo $row->file; ?>
						<?php if($this->manage){ ?>
							</a>
						<?php } ?>
					</td>
					<td align="center">
					<?php if($row->overriden){ ?>
						<?php if($this->delete){ ?>
							<a href="<?php echo hikashop_completeLink('view&task=remove&cid='.$row->id); ?>" onclick="return hikashopRemoveCustom('<?php echo $row->id?>');">
								<img src="<?php echo HIKASHOP_IMAGES; ?>delete.png" />
							</a>
						<?php } ?>
					<?php } ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
	<input type="hidden" id="view_id" name="cid[]" value="" />
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>