<?php
// $_SERVER['DOCUMENT_ROOT'] is now set - you can use it as usual...
/**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Composite_Context
{
    protected $_context = '';

    /**
     * @var string
     */
    protected $_paths;

    /**
     * @param $context
     * @param array $paths
     * @return RokCommon_Composite_Context
     *
     */
    public function __construct($context, array &$paths = array())
    {
        $this->_context = $context;
        $this->_paths = $paths;
    }

    /**
     * Perform an include with passed variables set for the passed in found file on the paths of the
     * context in the context path
     *
     * @param $file
     * @param $vars
     * @return string|bool
     */
    public function load($file, $vars, $hierarchical = true)
    {
        $_internal_loading_final_file = false;
        if (!$hierarchical) {
            $_internal_loading_final_file = self::_findFile($file, $this->_context, $this->_paths);
        }
        else {
            $found_paths = self::_findSet($file, $this->_context, $this->_paths);
            if (!empty($found_paths)) {
                $_internal_loading_final_file = $found_paths[0];
            }
        }

        if ($_internal_loading_final_file === false) return false;
        if (!file_exists($_internal_loading_final_file)) return false;
        extract($vars, EXTR_REFS | EXTR_SKIP);
        ob_start();
        include($_internal_loading_final_file);
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Perform an include with passed variables set for the passed in found file on the paths of the
     * context in the context path
     *
     * @param $file
     * @param $vars
     * @return string|bool
     */
    public function loadAll($file, $vars)
    {

        $found_paths = self::_findSet($file, $this->_context, $this->_paths);
        if (!empty($found_paths)) {
            $_internal_loading_final_file = $found_paths[0];
        }

        if ($found_paths === false && !empty($found_paths)) return false;

        extract($vars, EXTR_REFS | EXTR_SKIP);
        ob_start();
        foreach ($found_paths as $found_path) {
            if (!file_exists($_internal_loading_final_file)) continue;
            include($found_path);
        }
        $output = ob_get_clean();
        return $output;
    }


    /**
     * Get the path of the highest priority package file with the context in the context paths;
     *
     * @param $file
     * @return bool|string
     */
    public function get($file)
    {
        return self::_findFile($file, $this->_context, $this->_paths);
    }

    /**
     * @param $file
     * @return #Fstr_replace|?
     */
    public function getUrl($file, $hierarchical = true)
    {
        $file_path = false;
        if (!$hierarchical) {
            $file_path = self::_findFile($file, $this->_context, $this->_paths);
        }
        else {
            $found_paths = self::_findSet($file, $this->_context, $this->_paths);
            if (!empty($found_paths)) {
                $file_path = $found_paths[0];
            }
        }
        if ($file_path == false) return '';
        return RokCommon_Composite::getPlatform()->getUrlForPath($file_path);
    }

    /**
     * Get the hierarchical set of the highest priority files with the filename along the context path
     * @param $file
     * @return array
     */
    public function getSet($file)
    {
        return self::_findSet($file, $this->_context, $this->_paths);
    }


    /**
     * @param $file
     * @param $context
     * @param $basepaths
     * @return bool|string
     */
    protected static function _findFile($file, $context, $basepaths)
    {
        $hunt_path = str_replace('.', DS, $context);
        foreach ($basepaths as $priority => $paths)
        {
            foreach ($paths as $path)
            {
                $find_path = $path . DS . $hunt_path . DS . $file;
                if (file_exists($find_path) && is_file($find_path)) {
                    return $find_path;
                }
            }
        }
        return false;
    }

    /**
     * @param $file
     * @param $context
     * @param $basepaths
     * @return array
     */
    protected static function _findSet($file, $context, $basepaths)
    {
        $ret = array();
        $context_parts = explode('.', $context);
        while (count($context_parts))
        {
            $context_path = implode('.', $context_parts);
            $filepath = self::_findFile($file, $context_path, $basepaths);
            if ($filepath !== false) {
                $ret[] = $filepath;
            }
            array_pop($context_parts);
        }
        $filepath = self::_findFile($file, '', $basepaths);
        if ($filepath !== false) {
            $ret[] = $filepath;
        }

        return $ret;
    }
}
