<?php
/**
 * @package   gantry
 * @subpackage html.layouts
 * @version   1.5.2 November 11, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('JPATH_BASE') or die();

gantry_import('core.gantrylayout');

/**
 *
 * @package gantry
 * @subpackage html.layouts
 */
class GantryLayoutBody_MainBody extends GantryLayout {
    var $render_params = array(
        'schema'        =>  null,
        'pushPull'      =>  null,
        'classKey'      =>  null,
        'sidebars'      =>  '',
        'contentTop'    =>  null,
        'contentBottom' =>  null
    );
    function render($params = array()){
        global $gantry;

        $fparams = $this-> _getParams($params);

        // logic to determine if the component should be displayed
		$display_mainbody = !($gantry->get("mainbody-enabled",true)==false && JRequest::getVar('view') == 'frontpage');
        $display_component = !($gantry->get("component-enabled",true)==false && (JRequest::getVar('option') == 'com_content' && JRequest::getVar('view') == 'frontpage'));
        ob_start();

		$mbClasses = trim("rt-grid-" . trim($fparams->schema['mb'] . " " . $fparams->pushPull[0]));
		$mbClasses = preg_replace('/\s\s+/', ' ', $mbClasses);
// XHTML LAYOUT
?>          
<?php if ($display_mainbody) : ?>
<div id="rt-main" class="<?php echo $fparams->classKey; ?>">
	<div class="rt-container">
		<div class="<?php echo $mbClasses; ?>">
			<?php if (isset($fparams->contentTop)) : ?>
			<?php if (controlsDisplay('content-top')): ?><div class="scroller-enabled"><?php endif; ?>
			<div id="rt-content-top">
				<?php echo $fparams->contentTop; ?>
				<div class="clear"></div>
			</div>
			<?php if (controlsDisplay('content-top')): ?></div><?php endif; ?>
			<?php endif; ?>
			<?php if ($display_component) : ?>
				<?php if ($gantry->get('articlestyle')!='') : ?>
				<div class="<?php echo $gantry->get('articlestyle'); ?>">
				<?php endif; ?>
					<div class="rt-block component-block">
						<div id="rt-mainbody">
							<div class="component-content rt-joomla">
								<jdoc:include type="component" />
							</div>
						</div>
						<div class="clear"></div>
					</div>
				<?php if ($gantry->get('articlestyle')!='') : ?>
				</div>
				<?php endif; ?>
			<?php endif; ?>
			<?php if (isset($fparams->contentBottom)) : ?>
			<?php if (controlsDisplay('content-bottom')): ?><div class="scroller-enabled"><?php endif; ?>
			<div id="rt-content-bottom">
				<?php echo $fparams->contentBottom; ?>
				<div class="clear"></div>
			</div>
			<?php if (controlsDisplay('content-bottom')): ?></div><?php endif; ?>
			<?php endif; ?>
		</div>
		<?php echo $fparams->sidebars; ?>
		<div class="clear"></div>
	</div>
</div>
<?php endif; ?>
<?php
        return ob_get_clean();
    }
}