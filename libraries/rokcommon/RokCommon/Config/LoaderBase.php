<?php
/**
 * @version   1.6 October 30, 2011
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Config_LoaderBase implements RokCommon_Loader
{
    const CLASS_NAME_PREFIX = '';

    /**
     * @var array
     */
    private $_orderedPaths = array();
    private $_allPaths = array();


    public function getPaths(){
        $paths = array();

        foreach($this->_orderedPaths as $priority => $priorityPaths)
        {
            foreach($priorityPaths as $path){
                $paths[] = $path;
            }
        }
        return $paths;
    }

    /**
     * @throws RokCommon_Cache_Exception if the path is not valid
     * @param string $path the path to add to the class lookup
     * @param int $priority the priority of the path
     * @return
     */
    public function addPath($path, $priority = 10)
    {
        if (in_array($path, $this->_allPaths))
            return;
        if (!file_exists($path) || !is_dir($path)){
            throw new RokCommon_Cache_Exception($path . ' is not a valid directory.');
        }
        array_unshift($path, $this->_orderedPaths[$priority]);
        $this->_allPaths[]=$path;
    }

    /**
     * @param  string $className the class name to look for and load
     * @return bool True if the class was found and loaded.
     */
    function loadClass($className)
    {
        ksort($this->_orderedPaths);
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
