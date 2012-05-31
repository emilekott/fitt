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
<form action="index.php?option=<?php echo HIKASHOP_COMPONENT ?>&amp;ctrl=config" method="post" name="adminForm" enctype="multipart/form-data">
	<?php
		echo $this->tabs->startPane( 'config_tab');
		echo $this->tabs->startPanel( JText::_( 'MAIN' ), 'config_main');
		$this->setLayout('main');
		echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'CHECKOUT' ), 'config_checkout');
		$this->setLayout('checkout');
		echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'HIKA_FILES' ), 'config_files');
		$this->setLayout('files');
		echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'HIKA_EMAIL' ), 'config_mail');
		$this->setLayout('mail');
		echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'DISPLAY' ), 'config_display');
		$this->setLayout('display');
		echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'PLUGINS' ), 'config_plugins');
		$this->setLayout('plugins');
		echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'LANGUAGES' ), 'config_languages');
		$this->setLayout('languages');
		echo $this->loadTemplate();
		echo $this->tabs->endPanel();
		if(hikashop_level(2)){
			echo $this->tabs->startPanel( JText::_( 'ACCESS_LEVEL' ), 'config_acl');
			$this->setLayout('acl');
			echo $this->loadTemplate();
			echo $this->tabs->endPanel();
		}
		if(hikashop_level(1)){
			echo $this->tabs->startPanel( JText::_( 'CRON' ), 'config_cron');
			$this->setLayout('cron');
			echo $this->loadTemplate();
			echo $this->tabs->endPanel();
			echo $this->tabs->startPanel( JText::_( 'AFFILIATE' ), 'config_affiliate');
			$this->setLayout('affiliate');
			echo $this->loadTemplate();
			echo $this->tabs->endPanel();
		}
		echo $this->tabs->endPane();
	?>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo HIKASHOP_COMPONENT; ?>" />
	<input type="hidden" name="task" id="config_form_task" value="" />
	<input type="hidden" name="ctrl" value="config" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>