<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokCommon_Cache_DriverLoader implements RokCommon_Loader
{
    const CLASS_NAME_PREFIX = 'RTCacheDriver';

    /**
     * @var array
     */
    private $_orderedPaths = array();
    private $_allPaths = array();

    public function addDriverPath($path, $priority = 10)
    {
        if (in_array($path, $this->_allPaths))
            return;
        if (!file_exists($path) || !is_dir($path)){
            throw new RokCommon_Cache_Exception($path . ' is not a valid directory.');
        }
        $this->_orderedPaths[$priority][$path] = $path;
        $this->_allPaths[]=$path;
    }

    /**
     * @param  string $className the class name to look for and load
     * @return bool True if the class was found and loaded.
     */
    function loadClass($className)
    {
        $fileName = strtolower(str_replace(self::CLASS_NAME_PREFIX, '', $className) . self::FILE_EXTENSION);
        foreach($this->_orderedPaths as $priority => $priorityPaths)
        {
            foreach($priorityPaths as $path){
                $full_file_path = $path . DIRECTORY_SEPARATOR . $fileName;
                if (file_exists($full_file_path) && is_readable($full_file_path)){
                    require($full_file_path);
                    return true;
                }

            }
        }
        return false;
    }

}
