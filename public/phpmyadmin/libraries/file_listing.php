<?php
/* $Id: file_listing.php 8301 2006-01-17 17:03:02Z cybot_tm $ */
// vim: expandtab sw=4 ts=4 sts=4:
// Functions for listing directories

/**
 * Returns array of filtered file names
 *
 * @param   string  directory to list
 * @param   string  regullar expression to match files
 * @returns array   sorted file list on success, FALSE on failure
 */
function PMA_getDirContent($dir, $expression = '')
{
    if ($handle = @opendir($dir)) {
        $result = array();
        if (substr($dir, -1) != '/') {
            $dir .= '/';
        }
        while ($file = @readdir($handle)) {
            if (is_file($dir . $file) && ($expression == '' || preg_match($expression, $file))) {
                $result[] = $file;
            }
        }
        @closedir($handle);
        asort($result);
        return $result;
    } else {
        return FALSE;
    }
}

/**
 * Returns options of filtered file names
 *
 * @param   string  directory to list
 * @param   string  regullar expression to match files
 * @param   string  currently active choice
 * @returns array   sorted file list on success, FALSE on failure
 */
function PMA_getFileSelectOptions($dir, $extensions = '', $active = '')
{
    $list = PMA_getDirContent($dir, $extensions);
    if ($list === FALSE) {
        return FALSE;
    }
    $result = '';
    foreach ($list as $key => $val) {
        $result .= '<option value="'. htmlspecialchars($val) . '"';
        if ($val == $active) {
            $result .= ' selected="selected"';
        }
        $result .= '>' . htmlspecialchars($val) . '</option>' . "\n";
    }
    return $result;
}

/**
 * Get currently supported decompressions.
 *
 * @returns string | separated list of extensions usable in PMA_getDirContent
 */
function PMA_supportedDecompressions()
{
    global $cfg;
    
    $compressions = '';
    
    if ($cfg['GZipDump'] && @function_exists('gzopen')) {
        if (!empty($compressions)) {
            $compressions .= '|';
        }
        $compressions .= 'gz';
    }
    if ($cfg['BZipDump'] && @function_exists('bzopen')) {
        if (!empty($compressions)) {
            $compressions .= '|';
        }
        $compressions .= 'bz2';
    }
    if ($cfg['ZipDump'] && @function_exists('gzinflate')) {
        if (!empty($compressions)) {
            $compressions .= '|';
        }
        $compressions .= 'zip';
    }

    return $compressions;
}
