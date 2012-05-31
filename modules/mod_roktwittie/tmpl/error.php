<?php
/**
 * RokTwittie Module
 *
 * @package RocketTheme
 * @subpackage roktwittie.tmpl
 * @version   1.5 October 6, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

defined('_JEXEC') or die('Restricted access');

$error = (isset($status) && is_string($status)) ? $status : (isset($friends) ? $friends : '');
$error = (is_string($error)) ? $error : '';
?>

<div id="roktwittie" class="roktwittie<?php echo $params->get('moduleclass_sfx'); ?>">
	<div class="error">
		<?php echo $error; ?>
	</div>
</div>