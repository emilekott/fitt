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
<div style="float:right">
	<a class="modal" rel="{handler: 'iframe', size: {x: 760, y: 480}}" href="<?php echo hikashop_completeLink("zone&task=selectchildlisting&main_id=".$this->main_id."&main_namekey=".$this->main_namekey,true ); ?>">
		<button type="button" onclick="return false">
			<img src="<?php echo HIKASHOP_IMAGES; ?>add.png"/><?php echo JText::_('ADD');?>
		</button>
	</a>
</div>
<table class="adminlist" cellpadding="1" width="100%">
	<thead>
		<tr>
			<th class="title">
				<?php echo JText::_('ZONE_NAME_ENGLISH'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('HIKA_NAME'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ZONE_CODE_2'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ZONE_CODE_3'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ZONE_TYPE'); ?>
			</th>
			<th class="title titletoggle">
				<?php echo JText::_('HIKA_DELETE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
		</tr>
	</thead>
	<tbody id="list_0_data">
		<?php
			$this->k = 0;
			$this->setLayout('child');
			for($i = 0,$a = count($this->list);$i<$a;$i++){
				$this->row =& $this->list[$i];
				echo $this->loadTemplate();
				$this->k = 1-$this->k; 
			}	
		?>
	</tbody>
</table>
