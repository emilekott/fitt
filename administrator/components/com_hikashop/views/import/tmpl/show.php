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
	<fieldset class="adminform">
	<legend><?php echo JText::_( 'IMPORT' ); ?></legend>
		<?php echo JHTML::_('select.radiolist',   $this->importValues, 'importfrom', 'class="inputbox" size="1" onclick="updateImport(this.value);"', 'value', 'text','file'); ?>
	</fieldset>
	<div>
	<?php foreach($this->importData as $data){
		echo '<div id="'.$data->key.'"';
		if($data->key != 'file') echo ' style="display:none"';
		echo '>';
		echo '<fieldset class="adminform">';
		echo '<legend>'.$data->text.'</legend>';
		if($data->key=='folder' && !hikashop_level(2)){
			echo '<small style="color:red">'.JText::_('ONLY_FROM_BUSINESS').'</small>';
		}elseif($data->key=='vm' && !$this->vm){
			echo '<small style="color:red">VirtueMart has not been found in the database</small>';
		}else{
			if(in_array($data->key,array('file','textarea','folder','vm'))) include(dirname(__FILE__).DS.$data->key.'.php');
			else echo $data->data;
		}
		echo '</fieldset>';
		echo '</div>';
		}?>
	</div>
	<?php if(hikashop_level(2)){
			include(dirname(__FILE__).DS.'template.php');
		} ?>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
