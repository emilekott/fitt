<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

interface RokCommon_Doctrine_Platform
{

    /**
     * @abstract
     * @param string $tablename
     * @return string
     */
    public function setTableName($tablename);

    /**
     * @abstract
     * @return string a doctrine safe connection URL
     */
    public function getConnectionUrl();

    /**
     * @abstract
     * @return string a doctrine safe tablename format
     */
    public function getTableNameFormat();


    /**
     * @abstract
     * @return string the schema name for the platform
     */
    public function getSchema();

    /**
     * @abstract
     * @return string the database username for the platform
     */
    public function getUsername();

    /**
     * @abstract
     * @return string the database password for the platform
     */
    public function getPassword();

    /**
     * @abstract
     * @return string the database hostname for the platform
     */
    public function getHost();


}
