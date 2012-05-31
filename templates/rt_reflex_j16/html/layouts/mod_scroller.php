<?php
/**
 * @package   gantry
 * @subpackage html.layouts
 * @version   1.6.4 December 12, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 * Gantry uses the Joomla Framework (http://www.joomla.org), a GNU/GPLv2 content management system
 *
 */
defined('GANTRY_VERSION') or die();

gantry_import('core.gantrylayout');

/**
 *
 * @package gantry
 * @subpackage html.layouts
 */
class GantryLayoutMod_Scroller extends GantryLayout {
    var $render_params = array(
        'contents'      =>  null,
        'gridCount'     =>  null,
        'prefixCount'   =>  0,
        'extraClass'      =>  ''
    );
    function render($params = array()){
        global $gantry;

        $rparams = $this->_getParams($params);

        $prefixClass = '';

        if ($rparams->prefixCount !=0) {
            $prefixClass = " rt-prefix-".$rparams->prefixCount;
        }
		
		$contents = $params['contents'];
		preg_match_all('#([^\s=]+)\s*=\s*"(\'[^<\']*\'|[^<"]*)"#', $contents, $position);
		
		$keyPosition = array_search('name', $position[1]);
		$value = $position[2][$keyPosition];
		$value = preg_replace("/-[a-f]$/", '', $value);
		
        ob_start();
        // XHTML LAYOUT
?>
<?php if (controlsDisplay($value)): ?>
<div class="controls">
	<span class="down"><?php echo JText::_('SCROLLER_MORE'); ?></span>
	<span class="up"><?php echo JText::_('SCROLLER_MORE'); ?></span>
</div>
<?php endif; ?>
<div class="rt-grid-<?php echo $rparams->gridCount.$prefixClass.$rparams->extraClass; ?>">
	<?php if (controlsDisplay($value)): ?><div class="scroller-enabled"><?php endif; ?>
    <?php echo $rparams->contents;  ?>
    <?php if (controlsDisplay($value)): ?></div><?php endif; ?>
</div>
<?php

        return ob_get_clean();
    }
}

function controlsDisplay($positionStub){
	global $gantry;
	
	$published = array();	
	$showControls = false;
	$positions = $gantry->getPositions($positionStub);
	
	foreach($positions as $position){
		if ($gantry->countModules($position)) array_push($published, $position);
	}
	
	foreach($published as $position){
		if (!$showControls && $gantry->get('scrolling'.$positionStub.'-enabled') && $gantry->countSubPositionModules($position) > 1) $showControls = true;
	}
	
	return $showControls;
	
}