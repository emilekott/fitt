<?php
/**
  * @version   $Id: controller.php 39359 2011-07-02 19:02:14Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');


/**
 * rokgallery Controller
 *
 * @package Joomla
 * @subpackage rokgallery
 */
class RokgalleryController extends JController
{
    /**
     * Constructor
     * @access private
     * @subpackage rokgallery
     */
    function __construct()
    {
        //Get View
        if (JRequest::getCmd('view') == '')
        {
            JRequest::setVar('view', 'default');
        }
        $this->item_type = 'Default';
        parent::__construct();
    }

    public function ajax()
    {
        try
        {
            RokCommon_Ajax::addModelPath(JPATH_SITE . '/components/com_rokgallery/lib/RokGallery/Admin/Ajax/Model', 'RokGalleryAdminAjaxModel');
            $model = JRequest::getString('model');
            $action = JRequest::getString('action');
            if (isset($_REQUEST['params']))
            {
                $params = $this->smartstripslashes($_REQUEST['params']);
            }
            echo RokCommon_Ajax::run($model, $action, $params);
        }
        catch (Exception $e)
        {
            $result = new RokCommon_Ajax_Result();
            $result->setAsError();
            $result->setMessage($e->getMessage());
            echo json_encode($result);
        }
    }

    protected function smartstripslashes($str) {
      $cd1 = substr_count($str, "\"");
      $cd2 = substr_count($str, "\\\"");
      $cs1 = substr_count($str, "'");
      $cs2 = substr_count($str, "\\'");
      $tmp = strtr($str, array("\\\"" => "", "\\'" => ""));
      $cb1 = substr_count($tmp, "\\");
      $cb2 = substr_count($tmp, "\\\\");
      if ($cd1 == $cd2 && $cs1 == $cs2 && $cb1 == 2 * $cb2) {
        return strtr($str, array("\\\"" => "\"", "\\'" => "'", "\\\\" => "\\"));
      }
      return $str;
    }
}
