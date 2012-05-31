<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

interface RokCommon_Ajax_Model {

    /**
     * @abstract
     * @param  $params
     * @return RokCommon_Ajax_Result
     * @throws RokCommon_Ajax_Model
     */
    public function run($action, $params);
}
