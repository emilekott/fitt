<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokCommon_Composite_Package
{
    /** @var string */
    protected $_name;

    /** @var string[] */
    protected $_paths = array();

    protected $_contexts = array();


    /**
     *
     */
    public function __construct($name)
    {
        $this->_name = strtolower($name);
    }

    /**
     * @param $path
     * @param int $priority
     */
    public function addPath($path, $priority = RokCommon_Composite::DEFAULT_PRIORITY)
    {
        if (realpath($path))
        {
            $this->_paths[$priority][$path] = $path;
            krsort($this->_paths, SORT_NUMERIC);
        }
    }


    /**
     * @param $context_path
     * @return \RokCommon_Composite_Context
     */
    public function &getContext($context_path)
    {
        if(!array_key_exists($context_path,$this->_contexts))
            $this->_contexts[$context_path] = new RokCommon_Composite_Context($context_path, $this->_paths);

        return $this->_contexts[$context_path];
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
}
