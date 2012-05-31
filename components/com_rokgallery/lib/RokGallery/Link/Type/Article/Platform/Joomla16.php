<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokGallery_Link_Type_Article_Platform_Joomla16 implements RokGallery_Link_Type_Article_Platform {
    public function &getArticleInfo($id)
    {
        $article_info = new RokGallery_Link_Type_Article_Info();
        $article_info->setId($id);
        $article_info->setLink('index.php?option=com_content&view=article&id='.$id);
        
        /** @var $db JDatabase */
		$db = JFactory::getDbo();

        /** @var $query JDatabaseQuery */
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->from('#__content AS a');
        $query->select('a.title');
        $query->where('a.id = ' . (int) $id);
        $db->setQuery((string) $query);
        if (!$db->query()) {
            JError::raiseError(500, $db->getErrorMsg());
        }
        $article_info->title = $db->loadResult();
        return $article_info;
    }
}
