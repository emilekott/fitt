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
// no direct access
defined('_JEXEC') or die('Restricted access');
?>

<div class="user">
	<?php if ( $this->params->get( 'show_page_title' ) ) : ?>
	<h1 class="rt-pagetitle">
		<?php echo $this->escape($this->params->get('page_title')); ?>
	</h1>
	<?php endif; ?>
	<p>
		<?php echo nl2br($this->escape($this->params->get('welcome_desc', JText::_( 'WELCOME_DESC' )))); ?>
	</p>
</div>