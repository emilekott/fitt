<?php
/**
 * RokNewsPager Module
 *
 * @package rockettheme
 * @subpackage roknewspager.lib
 * @version   1.5 November 11, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPath::clean(JPATH_SITE.'/components/com_content/models/articles.php'));
require_once (JPath::clean(JPATH_SITE.'/components/com_content/helpers/route.php'));
require_once (JPath::clean(JPATH_SITE.'/libraries/joomla/html/html/content.php'));

/**
 * @package RocketTheme
 * @subpackage roknewspager.lib
 */
class modRokNewsPagerHelper
{
	
	function loadScripts()
	{
		JHTML::_('behavior.mootools');
		
		if (!defined('ROKNEWSPAGER_JS')) {
			$doc =& JFactory::getDocument();
			$doc->addScript(JURI::Root(true).'/modules/mod_roknewspager/tmpl/js/roknewspager'.self::_getJSVersion().'.js');
		}
	}

    function getRowCount(&$params)
    {
		$db			    =& JFactory::getDBO();
		$user		    =& JFactory::getUser();
		$userId		    = (int) $user->get('id');

		$count		    = $params->get('article_count',4);
		$catid		    = $params->get('catid', '0');
		$show_front	    = $params->get('show_front', 1);
		$aid		    = $user->get('aid', 0);
		$content_type   = $params->get('content_type','joomla');
		$ordering       = $params->get('itemsOrdering');
		$cid            = $params->get('category_id', NULL);
		$user_id        = $params->get('user_id');
        $text_length    = intval($params->get( 'preview_count', 75) );
        $thumb_size      = $params->get('thumb_width',90);
        $image_size      = $params->get('itemImgSize','M');
		
		$offset     = JRequest::getInt('offset',0);
		// content specific stuff
        if ($content_type=='joomla') {
            // start Joomla specific
			jimport('joomla.application.component.model');
			
            // Get an instance of the generic articles model
            $model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

            // Set application parameters in model
            $appParams = JFactory::getApplication()->getParams();
            $model->setState('params', $appParams);

            // Set the filters based on the module params
            $model->setState('list.start', $offset);
            $model->setState('list.limit', $count);
            $model->setState('filter.published', 1);

            // Access filter
            $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
            $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
            $model->setState('filter.access', $access);

            // Category filter
            $model->setState('filter.category_id', $catid);

            // User filter
            $userId = JFactory::getUser()->get('id');
            switch ($user_id)
            {
                case 'by_me':
                    $model->setState('filter.author_id', (int) $userId);
                    break;
                case 'not_me':
                    $model->setState('filter.author_id', $userId);
                    $model->setState('filter.author_id.include', false);
                    break;

                case 0:
                    break;

                default:
                    $model->setState('filter.author_id', $user_id);
                    break;
            }

            //  Featured switch
       		
			switch ($show_front)
            {
                case 1:
                    $model->setState('filter.featured', 'show');
                    break;
                case 0:
                    $model->setState('filter.featured', 'hide');
                    break;
                default:
                    $model->setState('filter.featured', 'only');
                    break;
            }

       		return $model->getTotal();
    		// end Joomla specific
	    } else {
		    // start K2 specific
		    require_once (JPath::clean( JPATH_SITE.'/components/com_k2/helpers/route.php'));

            $nullDate	    = $db->getNullDate();

            $date =& JFactory::getDate();
            $now = $date->toMySQL();
            $where = '';

            // User Filter
            switch ($user_id)
            {
                case 'by_me':
                    $where .= ' AND (a.created_by = ' . (int) $userId . ' OR a.modified_by = ' . (int) $userId . ')';
                    break;
                case 'not_me':
                    $where .= ' AND (a.created_by <> ' . (int) $userId . ' AND a.modified_by <> ' . (int) $userId . ')';
                    break;
            }

            // ensure should be published
            $where .= " AND ( a.publish_up = ".$db->Quote($nullDate)." OR a.publish_up <= ".$db->Quote($now)." )";
            $where .= " AND ( a.publish_down = ".$db->Quote($nullDate)." OR a.publish_down >= ".$db->Quote($now)." )";
		    
    		$query = "SELECT count(a.id)".
    		" FROM #__k2_items as a".
    		" LEFT JOIN #__k2_categories c ON c.id = a.catid";
	
    		$query .= " WHERE a.published = 1"
    		." AND a.access <= {$aid}"
    		." AND a.trash = 0"
    		." AND c.published = 1"
    		." AND c.access <= {$aid}"
    		." AND c.trash = 0"
    		;
	
   		    if ($params->get('catfilter')){
    			if (!is_null($cid)) {
    				if (is_array($cid)) {
    					$query .= " AND a.catid IN(".implode(',', $cid).")";
    				}
    				else {
    					$query .= " AND a.catid={$cid}";
    				}
    			}
    		}

    		if ($params->get('FeaturedItems')=='0')
    			$query.= " AND a.featured != 1";
	
    		if ($params->get('FeaturedItems')=='2')
    			$query.= " AND a.featured = 1";
    			
    		$query .= $where;

            $db->setQuery($query);
            return $db->loadresult();
    		// end K2 specific
		}
    }
    
	function getList($params)
	{
		$db			    =& JFactory::getDBO();
		$user		    =& JFactory::getUser();
		$userId		    = (int) $user->get('id');

		$count		    = $params->get('article_count',4);
		$catid		    = $params->get('catid', '0');
		$show_front	    = $params->get('show_front', 1);
		$aid		    = $user->get('aid', 0);
		$content_type   = $params->get('content_type','joomla');
		$ordering       = $params->get('itemsOrdering');
		$cid            = $params->get('category_id', NULL);
		$user_id        = $params->get('user_id');
        $text_length    = intval($params->get( 'preview_count', 75) );
        $thumb_size      = $params->get('thumb_width',90);
        $image_size      = $params->get('itemImgSize','M');

	    // ordering
		switch ($ordering) {
			case 'date' :
				$orderby = 'a.created ASC';
				break;
			case 'rdate' :
				$orderby = 'a.created DESC';
				break;
			case 'alpha' :
				$orderby = 'a.title';
				break;
			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;
			case 'order' :
				$orderby = 'a.ordering';
				break;
			default :
				$orderby = 'a.id DESC';
				break;
		}
		
		$offset     = JRequest::getInt('offset',0);

		// content specific stuff
        if ($content_type=='joomla') {
            // start Joomla specific
            // Get an instance of the generic articles model
            $model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			
            // Set application parameters in model
            $appParams = JFactory::getApplication()->getParams();
            $model->setState('params', $appParams);

            // Set the filters based on the module params
            $model->setState('list.start', $offset);
            $model->setState('list.limit', $count);
            $model->setState('filter.published', 1);

            // Access filter
            $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
            $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
            $model->setState('filter.access', $access);

            // Category filter
            $model->setState('filter.category_id', $catid);

            // User filter
            $userId = JFactory::getUser()->get('id');
            switch ($user_id)
            {
                case 'by_me':
                    $model->setState('filter.author_id', (int) $userId);
                    break;
                case 'not_me':
                    $model->setState('filter.author_id', $userId);
                    $model->setState('filter.author_id.include', false);
                    break;

                case 0:
                    break;

                default:
                    $model->setState('filter.author_id', $user_id);
                    break;
            }

            //  Featured switch
            switch ($show_front)
            {
                case 1:
                    $model->setState('filter.featured', 'show');
                    break;
                case 0:
                    $model->setState('filter.featured', 'hide');
                    break;
                default:
                    $model->setState('filter.featured', 'only');
                    break;
            }

            $ordering = explode(' ', $orderby);
            $model->setState('list.ordering', $ordering[0]);
            $model->setState('list.direction', isset($ordering[1]) ? $ordering[1] : null);

            $rows = $model->getItems();

    		// end Joomla specific
	    } else {
		    // start K2 specific
		    require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');

            $nullDate	    = $db->getNullDate();

            $date =& JFactory::getDate();
            $now = $date->toMySQL();
            $where = '';

            // User Filter
            switch ($user_id)
            {
                case 'by_me':
                    $where .= ' AND (a.created_by = ' . (int) $userId . ' OR a.modified_by = ' . (int) $userId . ')';
                    break;
                case 'not_me':
                    $where .= ' AND (a.created_by <> ' . (int) $userId . ' AND a.modified_by <> ' . (int) $userId . ')';
                    break;
            }

            // ensure should be published
            $where .= " AND ( a.publish_up = ".$db->Quote($nullDate)." OR a.publish_up <= ".$db->Quote($now)." )";
            $where .= " AND ( a.publish_down = ".$db->Quote($nullDate)." OR a.publish_down >= ".$db->Quote($now)." )";

    		$query = "SELECT a.*, c.name as categoryname,c.id as categoryid, c.alias as categoryalias, c.params as categoryparams".
    		" FROM #__k2_items as a".
    		" LEFT JOIN #__k2_categories c ON c.id = a.catid";

    		$query .= " WHERE a.published = 1"
    		." AND a.access <= {$aid}"
    		." AND a.trash = 0"
    		." AND c.published = 1"
    		." AND c.access <= {$aid}"
    		." AND c.trash = 0"
    		;

   		    if ($params->get('catfilter')){
    			if (!is_null($cid)) {
    				if (is_array($cid)) {
    					$query .= " AND a.catid IN(".implode(',', $cid).")";
    				}
    				else {
    					$query .= " AND a.catid={$cid}";
    				}
    			}
    		}

    		if ($params->get('FeaturedItems')=='0')
    			$query.= " AND a.featured != 1";

    		if ($params->get('FeaturedItems')=='2')
    			$query.= " AND a.featured = 1";

    		$query .= $where . ' ORDER BY ' . $orderby;

            $db->setQuery($query, 0, $count);

            $rows = $db->loadObjectList();
    		// end K2 specific
        }

		$i		= 0;
		$lists	= array();
		foreach ( $rows as $row )
		{
            //process content plugins
		    $text = JHTML::_('content.prepare',$row->introtext);

            $lists[$i]->id = $row->id;

            $lists[$i]->comment_count = null;

			if ($content_type=='joomla') {
				$lists[$i]->link = JRoute::_(ContentHelperRoute::getArticleRoute($row->id.'-'.$row->alias, $row->catid));
                $images = self::getImages($row->introtext,$thumb_size);
                $lists[$i]->comment_count = self::getCommentCount($row);

			} else {
                $lists[$i]->link = JRoute::_(K2HelperRoute::getItemRoute($row->id.':'.$row->alias, $row->catid.':'.$row->category_alias));
                $images = self::getK2Images($row->id,$image_size,$thumb_size);
				// commentcount can be null, cast to int needed to make sure it's a numeric value
                $lists[$i]->comment_count = (int) $row->commentcount;
			}
			$lists[$i]->title = htmlspecialchars( $row->title );
			$lists[$i]->introtext = self::prepareContent( $text, $text_length, $params);

            $lists[$i]->rating = (is_numeric($row->rating))?floatval($row->rating / 5 * 100):null;
            if($images){
            	$lists[$i]->image = $images->image;
    			$lists[$i]->thumb = $images->thumb;
            } else {
            	$lists[$i]->image = '';
    			$lists[$i]->thumb = '';
            }

            // set the author
            $author	=& JFactory::getUser($row->created_by);
            $lists[$i]->author = $author->name;

            //set the pub date
            jimport('joomla.utilities.date');
            $created_date = new JDate($row->created);
            $publish_date = new JDate($row->publish_up);
            $lists[$i]->published_date = ($created_date->toUnix() > $publish_date->toUnix())?$row->created:$row->publish_up;

			if (isset($images->size)) $lists[$i]->thumb_size = $images->size;
			$i++;
		}

		return $lists;
	}
	
	function prepareContent($text,  $length=200, &$params) {
		$tags_option = $params->get('strip_tags', 'a,br');
		
		$tags = explode(",", $tags_option);
		$strip_tags = array();
		for($i = 0; $i < count($tags); $i++) {
			$strip_tags[$i] = '<'.trim($tags[$i]).'>';
		}
		$tags = implode(',', $strip_tags);
		
		// strips tags won't remove the actual jscript
		$text = preg_replace( "'<script[^>]*>.*?</script>'si", "", $text );
		$text = preg_replace( '/{.+?}/', '', $text);
		//$text = preg_replace( "'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text );
		$text = strip_tags($text, $tags);

		if (strlen($text) > $length) {
			$text = substr($text, 0, strpos($text, ' ', $length)) . "..." ;
		} 
		
		return $text;
	}

	function getBrowser() 
	{
		$agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : false;
		$ie_version = false;
				
		if (preg_match("/msie/", $agent) && !preg_match("/opera/", $agent)){
            $val = explode(" ",stristr($agent, "msie"));
            $ver = explode(".", $val[1]);
			$ie_version = $ver[0];
			$ie_version = preg_replace("/[^0-9,.,a-z,A-Z]/", "", $ie_version);
		}
		
		return $ie_version;
	}


    function getK2Images($id, $image_size, $thumb_size=70) {

		$images = new stdClass();
		$images->image = false;
		$images->thumb = false;
		$current_size = intval($thumb_size);


		if (file_exists(JPath::clean(JPATH_SITE.'/media/k2/items/cache/'.md5("Image".$id).'_'.$image_size.'.jpg'))) {
		    $image_path = 'media/k2/items/cache/'.md5("Image".$id).'_'.$image_size.'.jpg';
		    $images->image = JURI::Root(true).'/'.$image_path;

			// create a thumb filename
			$file_div = strrpos($image_path,'.');
			$thumb_ext = substr($image_path, $file_div);
			$thumb_prev = substr($image_path, 0, $file_div);
			$thumb_path = $thumb_prev . "_thumb" . $thumb_ext;

			// check to see if this file exists, if so we don't need to create it
			if (function_exists("gd_info")) {
				// file doens't exist, so create it and save it
				if (!class_exists("Thumbnail")) include_once('thumbnail.inc.php');

				if (file_exists($thumb_path)) {
				    $existing_thumb = new Thumbnail($thumb_path);
				 	$images->size = $existing_thumb->currentDimensions;
				    $current_size = $existing_thumb->getCurrentWidth();
				}

                if (!file_exists($thumb_path) || $current_size!=$thumb_size) {

				    $thumb = new Thumbnail($image_path);
    				if ($thumb->error) {
    					echo "ROKSTORIES ERROR: " . $thumb->errmsg . ": " . $image_path;
    					return false;
    				}
    				$thumb->resize($thumb_size);
    				if (!is_writable(dirname($thumb_path))) {
    					$thumb->destruct();
    					return false;
    				}
					$images->size = $thumb->currentDimensions;
    				$thumb->save($thumb_path);
    				$thumb->destruct();
    			}
			}
			$images->thumb = $thumb_path;
		}
		return $images;
	}

    function getCommentCount(&$item){
        $jcommment_enabled = false;
        $comments = JPATH_SITE . '/components/com_jcomments/jcomments.php';
        $enabled = JPluginHelper::isEnabled('content', 'jcomments');
        if (file_exists($comments) and $enabled) {
            require_once($comments);
            $jcommment_enabled = true;
        }
        
        if ($jcommment_enabled && JCommentsContentPluginHelper::checkCategory($item->catid) && (JCommentsContentPluginHelper::isEnabled($item, false) || !JCommentsContentPluginHelper::isDisabled($item, false))){
            $count = JComments::getCommentsCount($item->id, 'com_content');
            if ($count == null) $count = 0;
            return $count;
        }
        return false;
    }

	function getImages($text, $thumb_size=70) {

        $matches = array();

		preg_match("/\<img.+?src=\"(.+?)\".+?\/>/", $text, $matches);

		$images = new stdClass();
		$images->image = false;
		$images->thumb = false;

		$paths = array();

		if (isset($matches[1])) {

			$image_path = $matches[1];

			//joomla 1.5 only
			$full_url = JURI::base();

			//remove any protocol/site info from the image path
			$parsed_url = parse_url($full_url);

			$paths[] = $full_url;
			if (isset($parsed_url['path']) && $parsed_url['path'] != "/") $paths[] = $parsed_url['path'];


			foreach ($paths as $path) {
				if (strpos($image_path,$path) !== false) {
					$image_path = substr($image_path,strpos($image_path, $path)+strlen($path));
				}
			}

			// remove any / that begins the path
			if (substr($image_path, 0 , 1) == '/') $image_path = substr($image_path, 1);

			//if after removing the uri, still has protocol then the image
			//is remote and we don't support thumbs for external images
			if (strpos($image_path,'http://') !== false ||
				strpos($image_path,'https://') !== false) {
				return false;
			}

			$images->image = JURI::Root(True)."/".$image_path;

			// create a thumb filename
			$file_div = strrpos($image_path,'.');
			$thumb_ext = substr($image_path, $file_div);
			$thumb_prev = substr($image_path, 0, $file_div);
			$thumb_path = $thumb_prev . "_thumb" . $thumb_ext;

			// check to see if this file exists, if so we don't need to create it
			if (function_exists("gd_info")) {
				// file doens't exist, so create it and save it
				if (!class_exists("Thumbnail")) include_once('thumbnail.inc.php');

				if (file_exists($thumb_path)) {
				    $existing_thumb = new Thumbnail($thumb_path);
				 	$images->size = $existing_thumb->currentDimensions;
				    $current_size = $existing_thumb->getCurrentWidth();
				}

                if (!file_exists($thumb_path) || $current_size!=$thumb_size) {

				    $thumb = new Thumbnail($image_path);

    				if ($thumb->error) {
    					echo "ROKNEWSPAGER ERROR: " . $thumb->errmsg . ": " . $image_path;
    					return false;
    				}
    				$thumb->resize($thumb_size);
    				if (!is_writable(dirname($thumb_path))) {
    					$thumb->destruct();
    					return false;
    				}
					$images->size = $thumb->currentDimensions;
    				$thumb->save($thumb_path);
    				$thumb->destruct();
    			}
			}
			$images->thumb = $thumb_path;
		}
		return $images;
	}
	
	public static function _getJSVersion() {

        return "";
    }
}
