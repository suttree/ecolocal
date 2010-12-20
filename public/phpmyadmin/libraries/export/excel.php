<?php
/* $Id: excel.php 9533 2006-10-10 14:20:58Z nijel $ */
// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Set of functions used to build CSV dumps of tables
 */

if (isset($plugin_list)) {
    $plugin_list['excel'] = array(
        'text' => 'strStrucExcelCSV',
        'extension' => 'csv',
        'mime_type' => 'text/comma-separated-values',
        'options' => array(
            array('type' => 'text', 'name' => 'null', 'text' => 'strReplaceNULLBy'),
            array('type' => 'bool', 'name' => 'columns', 'text' => 'strPutColNames'),
            array('type' => 'select', 'name' => 'edition', 'values' => array('win' => 'Windows', 'mac' => 'Excel 2003 / Macintosh'), 'text' => 'strExcelEdition'),
            array('type' => 'hidden', 'name' => 'data'),
            ),
        'options_text' => 'strExcelOptions',
        );
} else {
    /* Everything rest is coded in csv plugin */
    require('./libraries/export/csv.php');
}
?>
