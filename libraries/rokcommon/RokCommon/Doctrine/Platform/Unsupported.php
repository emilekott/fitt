<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokCommon_Doctrine_Platform_Unsupported implements RokCommon_Doctrine_Platform
{
    /**
     * @return string a doctrine safe tablename format
     */
    public function getTableNameFormat()
    {
        // TODO: Implement getTableNameFormat() method.
    }

    /**
     * @return string a doctrine safe connection URL
     */
    public function getConnectionUrl()
    {
        $host = 'localhost';

        $url = 'mysql';
        $url .= '://';
        $url .='dev';
        $url .= ':';
        $url .= 'dev';
        $url .= '@';
        $url .= $host;
        $url .= '/';
        $url .= 'migration';
        return $url;
    }

    /**
     * @param string $tablename
     * @return string
     */
    public function setTableName($tablename)
    {
        return $tablename;
    }


}
