<?php
 /**
 * @version   $Id$
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

abstract class RokGallery_Link_AbstractType implements RokGallery_Link_Type
{
    /**
     * @param array $vars
     */
    public function __construct(array $vars)
    {
        foreach ($vars as $varname => $varvalue)
        {
            $this->$varname = $varvalue;
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
