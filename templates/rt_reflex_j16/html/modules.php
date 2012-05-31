<?php
/**
 * @package   Reflex Template - RocketTheme
 * @version   1.6.4 December 12, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Rockettheme Reflex Template uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * This is a file to add template specific chrome to module rendering.  To use it you would
 * set the style attribute for the given module(s) include in your template to use the style
 * for each given modChrome function.
 *
 * eg.  To render a module mod_test in the sliders style, you would use the following include:
 * <jdoc:include type="module" name="test" style="slider" />
 *
 * This gives template designers ultimate control over how modules are rendered.
 *
 * NOTICE: All chrome wrapping methods should be named: modChrome_{STYLE} and take the same
 * two arguments.
 */

function modChrome_submenu($module, &$params, &$attribs)
{
	global $Itemid;
	
	$start	= intval($params->get('submenu-startLevel'));

	$tabmenu = &JSite::getMenu();
	$item = $tabmenu->getActive();
	
	$level = (isset($item) && isset($item->tree)) ? sizeof($item->tree) : 0;

	if (isset($item) && $start <= $level) {
        $menu_title = "";

        if ($start == 0) $start = 1;

        $menu_title_item_id = $item->tree[$start-1];
        $menu_title_item = $tabmenu->getItem($menu_title_item_id);
	
		if (!empty ($module->content) && $module->content != '') : ?>
	        <?php if ($params->get('submenu-module_sfx')!='') : ?>
	        <div class="<?php echo $params->get('submenu-module_sfx'); ?>">
	        <?php endif; ?>
	            <div class="rt-block">
					<div class="module-title"><h2 class="title"><?php echo $menu_title_item->title.' '.JText::_('Menu'); ?></h2></div>
					<div class="module-content">
                		<?php echo $module->content; ?>
						<div class="clear"></div>
					</div>
	            </div>
	        <?php if ($params->get('submenu-module_sfx')!='') : ?>
	        </div>
			<?php endif; ?>
		<?php endif;
	}
}

function modChrome_basic($module, &$params, &$attribs)
{
    	
	if (!empty ($module->content)) : ?>
		<?php echo $module->content; ?>
	<?php endif;
}

function modChrome_limited($module, &$params, &$attribs)
{
 	if (!empty ($module->content)) : ?>
    <div class="rt-block">
		<?php if ($module->showtitle != 0) : ?>
		<div class="module-title"><h2 class="title"><?php echo $module->title; ?></h2></div>
        <?php endif; ?>
    	<?php echo $module->content; ?>
		<div class="clear"></div>
    </div>
	<?php endif;
}

function modChrome_menu($module, &$params, &$attribs)
{
    	
	if (!empty ($module->content)) : ?>
	<div class="rt-block menu-block">
		<?php echo $module->content; ?>
	</div>
	<?php endif;
}

function modChrome_fusion($module, &$params, &$attribs)
{
    if (!empty ($module->content)) : ?>
    <div class="fusion-module <?php echo $params->get('moduleclass_sfx'); ?>">
		<?php if ($module->showtitle != 0) : ?>
		<div class="module-title"><h2 class="title"><?php echo $module->title; ?></h2></div>
        <?php endif; ?>
        <?php echo $module->content; ?>
    </div>
	<?php endif;
}

function modChrome_breadcrumbs($module, &$params, &$attribs)
{
    	
	if (!empty ($module->content)) : ?>
	<div class="rt-breadcrumb-surround">
		<div class="rt-block">
			<?php echo $module->content; ?>
		</div>
	</div>
	<?php endif;
}

function modChrome_standard($module, &$params, &$attribs)
{
 	if (!empty ($module->content)) : ?>
        <?php if ($params->get('moduleclass_sfx')!='') : ?>
        <div class="<?php echo $params->get('moduleclass_sfx'); ?>">
        <?php endif; ?>
            <div class="rt-block">
				<?php if ($module->showtitle != 0) : ?>
				<div class="module-title"><h2 class="title"><?php echo $module->title; ?></h2></div>
                <?php endif; ?>
				<div class="module-content">
                	<?php echo $module->content; ?>
					<div class="clear"></div>
				</div>
            </div>
        <?php if ($params->get('moduleclass_sfx')!='') : ?>
        </div>
		<?php endif; ?>
	<?php endif;
}

function modChrome_scroller($module, &$params, &$attribs)
{
 	if (!empty ($module->content)) : ?>
        <?php if ($params->get('moduleclass_sfx')!='') : ?>
        <div class="<?php echo $params->get('moduleclass_sfx'); ?> rt-block-scroller">
        <?php endif; ?>
            <div class="rt-block <?php echo ($params->get('moduleclass_sfx')!='') ? '' : 'rt-block-scroller'; ?>">
            	<div class="rt-module-surround">
					<?php if ($module->showtitle != 0) : ?>
					<div class="module-title"><h2 class="title"><?php echo $module->title; ?></h2></div>
	                <?php endif; ?>
					<div class="module-content">
	                	<?php echo $module->content; ?>
						<div class="clear"></div>
					</div>
				</div>
            </div>
        <?php if ($params->get('moduleclass_sfx')!='') : ?>
        </div>
	<?php endif; ?>
	<?php endif;
}

function modChrome_popup($module, &$params, &$attribs)
{
 	if (!empty ($module->content)) : ?>
	<div class="rt-block">
		<div class="module-content">
			<?php if ($module->showtitle != 0) : ?>
			<h2 class="title"><?php echo $module->title; ?></h2>
			<?php endif; ?>
			<div class="module-inner">
               	<?php echo $module->content; ?>
			</div>
		</div>
	</div>
	<?php endif;
}

?>

