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
<?php $link = hikashop_completeLink('category&task=listing&cid='.$this->row->category_id.'&name='.$this->row->alias.$this->menu_id);?>
<span class="hikashop_category_name">
	<a href="<?php echo $link;?>">
		<?php 
		echo $this->row->category_name;
		?>
	</a>
</span>