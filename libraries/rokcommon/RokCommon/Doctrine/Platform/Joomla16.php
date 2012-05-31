<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */


class RokCommon_Doctrine_Platform_Joomla16 implements RokCommon_Doctrine_Platform
{
    protected $config;

    public function __construct()
    {
        $this->config =new JConfig();
    }

    /**
     * @param string $tablename
     * @return string
     */
    public function setTableName($tablename)
    {
        return $this->config->dbprefix . $tablename;
    }

    /**
     * @return string a doctrine safe tablename format
     */
    public function getTableNameFormat()
    {
        return $this->config->dbprefix .'_%s';
    }

    /**
     * @return string a doctrine safe connection URL
     */
    public function getConnectionUrl()
    {
        $host = $this->config->host != '' ? $this->config->host : 'localhost';

        $url = 'mysql';
        $url .= '://';
        $url .= urlencode($this->config->user);
        $url .= ':';
        $url .= urlencode($this->config->password);
        $url .= '@';
        $url .= $host;
        $url .= '/';
        $url .= $this->config->db;
        return $url;
    }

    /**
     * @return string the schema name for the platform
     */
    public function getSchema()
    {
        return $this->config->db;
    }

    /**
     * @return string the database username for the platform
     */
    public function getUsername()
    {
        return $this->config->user;
    }

    /**
     * @return string the database password for the platform
     */
    public function getPassword()
    {
        return $this->config->password;
    }

    /**
     * @return string the database hostname for the platform
     */
    public function getHost()
    {
        return$this->config->host;
    }
}
