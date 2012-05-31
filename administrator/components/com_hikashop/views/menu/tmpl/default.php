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
<div style="line-height:normal;font-weight:bold;">
	<?php echo $this->title;?>
	<ul id="qm0" class="qmmc">
		<?php
		$first=true;
		$config =& hikashop_config();
		$ok = hikashop_level(2);
		foreach($this->menus as $menu){
			$html='';
			if(!empty($menu->children)){
				ob_start();
				foreach($menu->children as $child){
					$task = 'view';
					if(!empty($child->task)) $task = $child->task;
					if(!$ok || empty($child->acl) || hikashop_isAllowed($config->get('acl_'.$child->acl.'_'.$task,'all'))){
						?>
						<li>
							<a href="<?php echo $child->url; ?>"<?php if(!empty($child->active)) echo ' style="color:#CCC;"';?> <?php echo @$child->options;?>><?php echo $child->name; ?></a>
						</li>
						<?php
					}
				}
				$html = ob_get_clean();
				if(!empty($html)){
					$html = '<ul>'.$html.'</ul>';
				}
			}
			$task = 'view';
			if(!empty($menu->task)) $task = $menu->task;
			if($ok  && !empty($menu->acl) && !hikashop_isAllowed($config->get('acl_'.$menu->acl.'_'.$task,'all'))){
				if(empty($html)){
					continue;
				}else{
					$menu->url='#';
				}
			}
			if($first){
				$first = false;
			}else{
				?>
			<li>
				<span class="qmdivider qmdividery" ></span>
			</li>
				<?php
			}
			?>
			<li>
				<a class="qmparent" href="<?php echo $menu->url; ?>"<?php if(!empty($menu->active)) echo ' style="color:#CCC;"';?> <?php echo @$menu->options;?>><?php echo $menu->name; ?></a>
				<?php echo $html; ?>
			</li>
			<?php
		}
		?>
		<li class="qmclear">&nbsp;</li>
	</ul>
</div>
<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click (\'all\' or \'lev2\'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->
<script type="text/javascript">qm_create(0,false,0,250,false,false,false,false,false);</script>