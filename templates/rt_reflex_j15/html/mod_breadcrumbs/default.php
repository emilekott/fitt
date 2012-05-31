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
<span class="breadcrumbs pathway">
<?php for ($i = 0; $i < $count; $i ++) :
	if ($i < $count -1) {
		if(!empty($list[$i]->link)) {
			echo '<a href="'.$list[$i]->link.'" class="pathway">'.$list[$i]->name.'</a>';
		} else {
			echo '<span class="no-link">'.$list[$i]->name.'</span>';
		}
		echo ' '.$separator.' ';
	}  else if ($params->get('showLast', 1)) {
	    echo '<span class="no-link">'.$list[$i]->name.'</span>';
	}
endfor; ?>
</span>
