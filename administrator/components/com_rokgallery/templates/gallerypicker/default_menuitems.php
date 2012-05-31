<?php
/**
  * @version   $Id: default_file.php 39412 2011-07-03 18:34:26Z djamil $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

?>

<table id="gallerypicker-menulist" class="adminlist">
    <thead>
        <tr>
            <th class="title">Menu Item</th>
            <th width="15%">Published</th>
            <th width="15%">Access</th>
            <th width="15%">Menu</th>
            <th width="15%">Language</th>
            <th width="1%" class="nowrap">Id</th>
        </tr>
    </thead>
    <tbody>
<?php
$menuitems = $that->menuitems;
$count = 0;
foreach($menuitems as $menuitem):
?>
        <tr class="row<?php echo ($count % 2); ?>">
            <td class="menuitem">
                <a data-opentag="<a href='<?php echo $menuitem->menu_link;?>'>" data-closetag="</a>" data-display="<?php echo $menuitem->link_name;?>" class="menu-item" href="<?php echo $menuitem->menu_link;?>"><?php echo $menuitem->link_name;?></a>
            </td>
            <td class="menuitemid center">
                <?php if($menuitem->published): ?>
                    <span class="published"><span class="text">Published</span></span>
                <?php else: ?>
                    <span class="unpublished"><span class="text">Unpublished</span></span>
                <?php endif; ?>
            </td>
            <td class="menutype center">
                <span><?php echo $menuitem->access_group; ?></span>
            </td>
            <td class="menutype center">
                <span><?php echo ($menuitem->menu_name != '') ? $menuitem->menu_name : 'none'; ?></span>
            </td>
            <td class="menutype center">
                <?php if(($menuitem->language_title == '*')||($menuitem->language_title == '')): ?>
                    <span class="text">default</span>
                <?php else: ?>
                    span><?php echo $menuitem->language_title; ?></span>
                <?php endif; ?>
            </td>
            <td class="menuitemid center">
                <span><?php echo $menuitem->id; ?></span>
            </td>
        </tr>
<?php $count++; endforeach; ?>
    </tbody>
</table>
