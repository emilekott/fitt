<?php
/**
 * @version   1.7 October 12, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

require_once(dirname(__FILE__) . '/default/theme.php');
RokNavMenu::registerTheme(dirname(__FILE__).'/default','default', 'Default', 'RokNavMenuDefaultTheme');

require_once(dirname(__FILE__) . '/fusion/theme.php');
RokNavMenu::registerTheme(dirname(__FILE__).'/fusion','fusion', 'Default Fusion', 'RokNavMenuFusionTheme');