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
defined('_JEXEC') or die('Restricted access'); ?>
<ul class="mostread <?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) : ?>
	<li class="mostread">
		<a href="<?php echo $item->link; ?>" class="mostread">
			<?php echo $item->text; ?></a>
	</li>
<?php endforeach; ?>
</ul>