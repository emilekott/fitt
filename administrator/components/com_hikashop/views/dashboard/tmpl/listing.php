<?php
/**
 * @package		HikaShop for Joomla!
 * @version		1.5.5
 * @author		hikashop.com
 * @copyright	(C) 2010-2011 HIKARI SOFTWARE. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?>
<div class="iframedoc" id="iframedoc"></div>
<?php $itemOnARow =0; ?>
<table class="adminform" style="border-collapse:separate;border-spacing:5px">
		<?php
			foreach($this->widgets as $widget){
				if(empty($widget->widget_params->display)) continue;
				if(!hikashop_level(2)){
					if($widget->widget_params->content=='partners' || $widget->widget_params->display=='map') continue;
					if(!hikashop_level(1) && in_array($widget->widget_params->display,array('gauge','pie'))) continue;
				}
				if($itemOnARow==0){
					echo '<tr>';
				}
				$val = preg_replace('#[^a-z0-9]#i','_',strtoupper($widget->widget_name));
				$trans = JText::_($val);
				if($val!=$trans){
					$widget->widget_name = $trans;
				}
				if(hikashop_level(1)){
					if($this->manage){
						$widget->widget_name.= '
						<a class="modal" rel="{handler: \'iframe\', size: {x: 480, y: 380}}" href="'.hikashop_completeLink('dashboard&task=edit&widget_id='.$widget->widget_id,true).'">
							<img src="'.HIKASHOP_IMAGES.'edit.png" alt="edit"/>
						</a>';
					}
					if(hikashop_level(2)){
						$widget->widget_name.= '
						<a href="'.hikashop_completeLink('dashboard&task=csv&cid[]='.$widget->widget_id).'&'.JUtility::getToken().'=1">
							<img src="'.HIKASHOP_IMAGES.'go.png" alt="Download CSV"/>
						</a>';
					}
					if($this->delete){
						$widget->widget_name.= '
						<a onclick="return confirm(\''.JText::_('REMOVE_WIDGET',true).'\')" href="'.hikashop_completeLink('dashboard&task=remove&cid[]='.$widget->widget_id).'&'.JUtility::getToken().'=1">
							<img src="'.HIKASHOP_IMAGES.'delete.png" alt="delete"/>
						</a>
						';
					}
				}
				echo '<td valign="top" style="border: 1px solid #CCCCCC"><fieldset style="border:0px"><legend>'.$widget->widget_name.'</legend>';
				$this->widget =& $widget;
				if($widget->widget_params->display=='listing'){
					$this->setLayout($widget->widget_params->content_view);
				}else{
					$this->setLayout($widget->widget_params->display);
				}
				echo $this->loadTemplate();
				echo '</fieldset></td>';
				$itemOnARow++;
				if($itemOnARow==3){
					echo '</tr>';
					$itemOnARow=0;
				}
			}
			?>
</table>
<?php
$this->setLayout('cpanel');
echo $this->loadTemplate();