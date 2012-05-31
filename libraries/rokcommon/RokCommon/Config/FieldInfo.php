<?php
/**
 * Created by JetBrains PhpStorm.
 * User: brian
 * Date: 12/10/10
 * Time: 2:27 PM
 * To change this template use File | Settings | File Templates.
 */

interface RokCommon_Config_FieldInfo {
    public function get_field_id($fieldId, $group = null);
    public function get_field_name($fieldName, $group = null);
}
