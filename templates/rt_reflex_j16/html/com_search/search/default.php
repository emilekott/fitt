<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Site
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<div class="search-results">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h2 class="componentheading">
		<?php if ($this->escape($this->params->get('page_heading'))) :?>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		<?php else : ?>
			<?php echo $this->escape($this->params->get('page_title')); ?>
		<?php endif; ?>
	</h2>
	<?php endif; ?>

	<div id="page">
			<?php echo $this->loadTemplate('form'); ?>
			<?php if ($this->error==null && count($this->results) > 0) :
				echo $this->loadTemplate('results');
			else :
				echo $this->loadTemplate('error');
			endif; ?>
		</div>
	</div>
