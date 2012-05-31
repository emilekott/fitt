<?php
 /**
  * @version   $Id$
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokCommon_Doctrine_Filter extends Doctrine_Record_Filter
{
    public function filterGet(Doctrine_Record $record, $name)
    {
        $method = '_get'.ucfirst($name);
        if (method_exists($this,$method)){
            if (!$record->hasMappedValue($name))
            {
                $record->mapValue($name, $this->$method($record));
            }
            return $record->get($name);
        }
        throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property "%s" on "%s"', $name, get_class($record)));
    }

    public function filterSet(Doctrine_Record $record, $name, $value)
    {
        $method = '_set'.ucfirst($name);
        if (method_exists($this,$method)){
            $record->mapValue($name, $this->$method($record, $value));
            return $record->get($name);
        }
        throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property "%s" on "%s"', $name, get_class($record)));
    }
}
