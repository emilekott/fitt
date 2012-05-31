<?php
 /**
 * @version   $Id: Helper.php 39497 2011-07-05 08:21:28Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

class RokGallery_Helper
{
    public static $imageExtensions = array(
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png'
    );

    public static function getImageInfo($file)
    {
        if (!is_file($file))
        {
            throw new RokGallery_Exception_NoMatchingSlice('File not found ' . $file);
        }
        $info = getimagesize($file);
        switch ($info[2])
        {
            case IMAGETYPE_GIF:
            case IMAGETYPE_JPEG:
            case IMAGETYPE_PNG:

            default:
                throw new RokGallery_Exception_UnsupportedImageType('Unsupported Image Format');
        }
    }

    /**
     * @param string $guid guid to generate path from
     * @param string $seperator the directory seperator to use
     * @return string
     */
    public static function getPathFromGUID($guid, $seperator = '/')
    {
        $path = substr($guid, 0, 1);
        $path .= $seperator;
        $path .= $guid;
        return $path;
    }

    /**
     * @static
     * @return string
     */
    public static function createUUID()
    {
        return RokCommon_UUID::generate();
    }


    /**
     * @param $tmp_path the folder to delete
     * @return bool true if sucesss false if fail
     */
    public static function delete_folder($tmp_path)
    {
        if (!is_writeable($tmp_path) && is_dir($tmp_path)) {
            @chmod($tmp_path, 0777);
        }
        $handle = opendir($tmp_path);
        while ($tmp = readdir($handle))
        {
            if ($tmp != '..' && $tmp != '.' && $tmp != '') {
                if (is_writeable($tmp_path . DS . $tmp) && is_file($tmp_path . DS . $tmp)) {
                    @unlink($tmp_path . DS . $tmp);
                } elseif (!is_writeable($tmp_path . DS . $tmp) && is_file($tmp_path . DS . $tmp))
                {
                    @chmod($tmp_path . DS . $tmp, 0666);
                    @unlink($tmp_path . DS . $tmp);
                }

                if (is_writeable($tmp_path . DS . $tmp) && is_dir($tmp_path . DS . $tmp)) {
                    self::delete_folder($tmp_path . DS . $tmp);
                } elseif (!is_writeable($tmp_path . DS . $tmp) && is_dir($tmp_path . DS . $tmp))
                {
                    @chmod($tmp_path . DS . $tmp, 0777);
                    self::delete_folder($tmp_path . DS . $tmp);
                }
            }
        }
        closedir($handle);
        rmdir($tmp_path);
        if (!is_dir($tmp_path)) {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param $bytes
     * @return string human readable filesize
     */
    public static function decodeSize($bytes)
    {
        $types = array('B', 'KB', 'MB', 'GB', 'TB');
        for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++) ;
        return (round($bytes, 2) . " " . $types[$i]);
    }

    /**
     * @param $filesize
     * @return int
     */
    public static function getFilesizeAsInt($filesize)
    {
        //remove spaces
        $filesize = str_replace(' ', '', trim($filesize));

        //get last to chars
        $matches = array();
        if (preg_match('/^(\d+\.?\d*)\w*([kmgt])b*/', strtolower($filesize), $matches)) {
            $size = $matches[1];
            switch ($matches[2])
            {
                case 't':
                    $size = $size * 1024;
                case 'g':
                    $size = $size * 1024;
                case 'm':
                    $size = $size * 1024;
                case 'k':
                    $size = $size * 1024;
                default:
                    $filesize = $size;
            }
        }
        return (int)$filesize;
    }

    /**
     * @param $color
     * @return array|bool|null
     */
    public static function html2rgb($color)
    {
        if (empty($color) || $color == 'transparent')
            return null;
        if ($color[0] == '#')
            $color = substr($color, 1);

        if (strlen($color) == 6)
            list($r, $g, $b) = array($color[0] . $color[1],
                                     $color[2] . $color[3],
                                     $color[4] . $color[5]);
        elseif (strlen($color) == 3)
            list($r, $g, $b) = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        else
            return false;

        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);

        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }

    public static function getImageQuality($type)
    {
        $quality = null;
        switch (strtolower($type)) {
            case 'jpeg':
            case 'jpg':
                $quality = RokGallery_Config::getOption(RokGallery_Config::OPTION_JPEG_QUALITY, 80);
                break;
            case 'png':
                $quality = RokGallery_Config::getOption(RokGallery_Config::OPTION_PNG_COMPRESSION, 0);
                break;
            default:
                $quality = null;
                break;
        }
        return $quality;
    }

    public static function getJSVersion()
    {
        if (version_compare(JVERSION, '1.5', '>=') && version_compare(JVERSION, '1.6', '<')) {
            if (JPluginHelper::isEnabled('system', 'mtupgrade')) {
                return "-mt1.2";
            } else
            {
                return "";
            }
        } else
        {
            return "";
        }
    }
}
