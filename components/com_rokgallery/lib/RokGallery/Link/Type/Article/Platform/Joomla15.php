<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokGallery_Link_Type_Article_Platform_Joomla15 implements RokGallery_Link_Type_Article_Platform {
    public function &getArticleInfo($id)
    {
        $article_info = new RokGallery_Link_Type_Article_Info();
        $article_info->setId($id);
        $article_info->setLink('index.php?option=com_content&view=article&id='.$id);
        //get the article info from joomla
        $db =& JFactory::getDBO();
        // Get the articles
		$query = 'SELECT c.title'.
				' FROM #__content AS c' .
                ' WHERE c.id = '. $id;
		$db->setQuery($query);
		$article_info->setTitle($db->loadResult());
        return $article_info;
    }
}
