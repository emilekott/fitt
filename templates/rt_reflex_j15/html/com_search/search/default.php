<?php
/**
 * @package   Reflex Template - RocketTheme
 * @version   1.5.2 November 11, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Rockettheme Reflex Template uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('_JEXEC') or die('Restricted access');
?>

<?php if($this->params->get('show_page_title',1)) : ?>
<h2 class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')) ?>">
	<?php echo $this->escape($this->params->get('page_title')) ?>
</h2>
<?php endif; ?>

<div id="page">
<?php if (!$this->error) :
	echo $this->loadTemplate('results');
else :
	echo $this->loadTemplate('error');
endif; ?>
<?php echo $this->loadTemplate('form'); ?>
</div>