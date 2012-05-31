<?php
/**
 * RokNewsFlash Module
 *
 * @package RocketTheme
 * @subpackage roknewsflash
 * @version   1.1 March 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

defined('_JEXEC') or die('Restricted access');

require_once (JPath::clean(JPATH_SITE.'/components/com_content/models/articles.php'));
require_once (JPath::clean(JPATH_SITE.'/components/com_content/helpers/route.php'));
require_once (JPath::clean(JPATH_SITE.'/libraries/joomla/html/html/content.php'));
jimport('joomla.utilities.date');

/**
 * @package RocketTheme
 * @subpackage roknewsflash
 */
class modRokNewsflashHelper
{
	
	function loadScripts(&$params)
	{
		JHTML::_('behavior.mootools');
		$doc = &JFactory::getDocument();
		$doc->addScript(JURI::Root(true).'/modules/mod_roknewsflash/tmpl/js/roknewsflash'.self::_getJSVersion().'.js');
		
		$jsinit = "window.addEvent('domready', function() {
				var x = new RokNewsFlash('newsflash', {
					controls: ".(($params->get('controls') == 1) ? "1" : "0").",
					delay: ".$params->get('delay').",
					duration: ".$params->get('duration')."
				});
			});";
		
		$doc->addScriptDeclaration($jsinit);
	}
	
	function prepareRokContent( $text, $length=200 ) {
		// strips tags won't remove the actual jscript
		$text = preg_replace( "'<script[^>]*>.*?</script>'si", "", $text );
		$text = preg_replace( '/{.+?}/', '', $text);
		// replace line breaking tags with whitespace
		$text = preg_replace( "'<(br[^/>]*?/|hr[^/>]*?/|/(div|h[1-6]|li|p|td))>'si", ' ', $text );
		$text = substr(strip_tags( $text ), 0, $length) ;
		return $text;
	}	
	
	function getList($params)
	{
		$db			    =& JFactory::getDBO();
		$user		    =& JFactory::getUser();
		$userId		    = (int) $user->get('id');

		$count		    = $params->get('article_count',4);
		$catid		    = trim( $params->get('catid') );
		$show_front	    = $params->get('show_front', 1);
		$aid		    = $user->get('aid', 0);
		$content_type   = $params->get('content_type','joomla');
		$ordering       = $params->get('itemsOrdering');
		$cid            = $params->get('category_id', NULL);
		$user_id        = $params->get('user_id');
        $text_length    = intval($params->get( 'preview_count', 75) );

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
            $model->setState('list.start', 0);
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
            $lists[$i]->id = $row->id;
			$lists[$i]->title = htmlspecialchars( $row->title );
			$lists[$i]->introtext = self::prepareRokContent( $row->introtext, $text_length);
			$lists[$i]->date = new JDate( $row->created );
			if ($content_type=='joomla') {
			    $lists[$i]->link = JRoute::_(ContentHelperRoute::getArticleRoute($row->id, $row->catid));
			} else {
                $lists[$i]->link = JRoute::_(K2HelperRoute::getItemRoute($row->id.':'.$row->alias, $row->catid.':'.$row->category_alias));
			}
			$i++;
		}

		return $lists;
	}

	function getBrowser() 
	{
		$agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : false;
		$ie_version = false;
				
		if (preg_match("#msie#i", $agent) && !preg_match("#opera#i", $agent)){
            $val = explode(" ",stristr($agent, "msie"));
            $ver = explode(".", $val[1]);
			$ie_version = $ver[0];
			$ie_version = preg_replace("#[^0-9,.,a-z,A-Z]#", "", $ie_version);
		}
		
		return $ie_version;
	}
	
	public static function _getJSVersion() {

        return "";
    }
}
