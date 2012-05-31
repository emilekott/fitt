<?php
 /**
  * @version   $Id: Memory.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
/**
 *
 */
class RokGallery_Memory_Exception extends Exception {}

/**
 *
 */
class RokGallery_Memory extends RokCommon_Memory
{

    /**
     * @static
     * @param $x
     * @param $y
     * @param int $rgb
     * @throws RokGallery_Memory_Exception
     */
    public static function adjustLimitForImage($x, $y, $rgb = 3)
    {
        try {
            $needed = $x * $y * $rgb * 2;

            if ($needed > RokCommon_Memory::getFreeSpace()) {
                $mem_bump_amount = (int)($needed - RokCommon_Memory::getFreeSpace()) * 1.25;
                self::setLimit((int)$mem_bump_amount + RokCommon_Memory::getLimit());
            }
        }
        catch (RokCommon_Memory_Exception $me)
        {
            throw new RokGallery_Memory_Exception($me->getMessage(), null, $me);
        }
        if ($needed > RokCommon_Memory::getFreeSpace()) {
            throw new RokGallery_Memory_Exception("Not enough memory available.  Please adjust the memory_limit in the php.ini");
        }
    }
}



