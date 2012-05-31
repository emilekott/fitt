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
<?php $i = (int)(count($this->buttons)/2); if(count($this->buttons)%2)$i++;?>
<table class="adminform">
	<tr>
		<td width="50%" valign="top">
			<div id="hikashopcpanel">
				<?php
					foreach($this->buttons as $k => $oneButton){
						if($k == $i){
							echo '</div></td><td valign="top"><div id="hikashopcpanel">';
						}
						echo $oneButton;
					}
					?>
			</div>
		</td>
	</tr>
</table>
