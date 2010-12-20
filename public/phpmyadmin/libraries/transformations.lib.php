<?php
/* $Id: transformations.lib.php 9616 2006-10-26 14:56:57Z nijel $ */
// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Set of functions used with the relation and pdf feature
 */

function PMA_transformation_getOptions($string) {
    $transform_options = array();

    /* Parse options */
    for ($nextToken = strtok($string, ','); $nextToken !== false; $nextToken = strtok(',')) {
        $trimmed = trim($nextToken);
        if ($trimmed{0} == '\'' && $trimmed{strlen($trimmed) - 1} == '\'') {
            $transform_options[] = substr($trimmed, 1, -1);
        } else {
            if ($trimmed{0} == '\'') {
                $trimmed= ltrim($nextToken);
                while ($nextToken !== false) {
                    $nextToken = strtok(',');
                    $trimmed .= $nextToken;
                    $rtrimmed = rtrim($trimmed);
                    if ($rtrimmed{strlen($rtrimmed) - 1} == '\'') break;
                }
                $transform_options[] = substr($rtrimmed, 1, -1);
            } else {
                $transform_options[] = $nextToken;
            }
        }
    }

    // strip possible slashes to behave like documentation says
    $result = array();
    foreach ($transform_options as $val) {
        $result[] = stripslashes($val);
    }
    return $result;
}

/**
 * Gets all available MIME-types
 *
 * @return  array    array[mimetype], array[transformation]
 *
 * @access  public
 *
 * @author  Garvin Hicking <me@supergarv.de>
 */
function PMA_getAvailableMIMEtypes() {
    $handle = opendir('./libraries/transformations');

    $stack = array();
    $filestack = array();

    while (($file = readdir($handle)) != false) {
        $filestack[$file] = $file;
    }

    closedir($handle);

    if (is_array($filestack)) {
        @ksort($filestack);
        foreach ($filestack AS $key => $file) {

            if (preg_match('|^.*__.*\.inc\.php$|', trim($file))) {
                // File contains transformation functions.
                $base = explode('__', str_replace('.inc.php', '', $file));
                $mimetype = str_replace('_', '/', $base[0]);
                $stack['mimetype'][$mimetype] = $mimetype;

                $stack['transformation'][] = $mimetype . ': ' . $base[1];
                $stack['transformation_file'][] = $file;

            } elseif (preg_match('|^.*\.inc\.php$|', trim($file))) {
                // File is a plain mimetype, no functions.
                $base = str_replace('.inc.php', '', $file);

                if ($base != 'global') {
                    $mimetype = str_replace('_', '/', $base);
                    $stack['mimetype'][$mimetype] = $mimetype;
                    $stack['empty_mimetype'][$mimetype] = $mimetype;
                }
            }

        }
    }

    return $stack;
}

/**
 * Gets the mimetypes for all rows of a table
 *
 * @param   string   the name of the db to check for
 * @param   string   the name of the table to check for
 * @param   string   whether to include only results having a mimetype set
 *
 * @return  array    [field_name][field_key] = field_value
 *
 * @global  array    the list of relations settings
 *
 * @access  public
 *
 * @author  Mike Beck <mikebeck@users.sourceforge.net> / Garvin Hicking <me@supergarv.de>
 */
function PMA_getMIME($db, $table, $strict = false) {
    global $cfgRelation;

    $com_qry  = 'SELECT column_name, mimetype, transformation, transformation_options FROM ' . PMA_backquote($GLOBALS['cfgRelation']['db']) . '.' . PMA_backquote($cfgRelation['column_info'])
              . ' WHERE db_name = \'' . PMA_sqlAddslashes($db) . '\''
              . ' AND table_name = \'' . PMA_sqlAddslashes($table) . '\''
              . ' AND (mimetype != \'\'' . (!$strict ? ' OR transformation != \'\' OR transformation_options != \'\'' : '') . ')';
    $com_rs   = PMA_query_as_cu($com_qry);

    while ($row = @PMA_DBI_fetch_assoc($com_rs)) {
        $col                                    = $row['column_name'];
        $mime[$col]['mimetype']                 = $row['mimetype'];
        $mime[$col]['transformation']           = $row['transformation'];
        $mime[$col]['transformation_options']   = $row['transformation_options'];
    } // end while
    PMA_DBI_free_result($com_rs);
    unset($com_rs);

    if (isset($mime) && is_array($mime)) {
        return $mime;
     } else {
        return FALSE;
     }
 } // end of the 'PMA_getMIME()' function

/**
* Set a single mimetype to a certain value.
*
* @param   string   the name of the db
* @param   string   the name of the table
* @param   string   the name of the column
* @param   string   the mimetype of the column
* @param   string   the transformation of the column
* @param   string   the transformation options of the column
* @param   string   (optional) force delete, will erase any existing comments for this column
*
* @return  boolean  true, if comment-query was made.
*
* @global  array    the list of relations settings
*
* @access  public
*/
function PMA_setMIME($db, $table, $key, $mimetype, $transformation, $transformation_options, $forcedelete = false) {
    global $cfgRelation;

    $test_qry  = 'SELECT mimetype, ' . PMA_backquote('comment') . ' FROM ' . PMA_backquote($GLOBALS['cfgRelation']['db']) . '.' . PMA_backquote($cfgRelation['column_info'])
                . ' WHERE db_name = \'' . PMA_sqlAddslashes($db) . '\''
                . ' AND table_name = \'' . PMA_sqlAddslashes($table) . '\''
                . ' AND column_name = \'' . PMA_sqlAddslashes($key) . '\'';
    $test_rs   = PMA_query_as_cu($test_qry, TRUE, PMA_DBI_QUERY_STORE);

    if ($test_rs && PMA_DBI_num_rows($test_rs) > 0) {
        $row = @PMA_DBI_fetch_assoc($test_rs);
        PMA_DBI_free_result($test_rs);
        unset($test_rs);

        if (!$forcedelete && (strlen($mimetype) > 0 || strlen($transformation) > 0 || strlen($transformation_options) > 0 || strlen($row['comment']) > 0)) {
            $upd_query = 'UPDATE ' . PMA_backquote($GLOBALS['cfgRelation']['db']) . '.' . PMA_backquote($cfgRelation['column_info'])
                   . ' SET mimetype = \'' . PMA_sqlAddslashes($mimetype) . '\','
                   . '     transformation = \'' . PMA_sqlAddslashes($transformation) . '\','
                   . '     transformation_options = \'' . PMA_sqlAddslashes($transformation_options) . '\''
                   . ' WHERE db_name  = \'' . PMA_sqlAddslashes($db) . '\''
                   . ' AND table_name = \'' . PMA_sqlAddslashes($table) . '\''
                   . ' AND column_name = \'' . PMA_sqlAddslashes($key) . '\'';
        } else {
            $upd_query = 'DELETE FROM ' . PMA_backquote($GLOBALS['cfgRelation']['db']) . '.' . PMA_backquote($cfgRelation['column_info'])
                   . ' WHERE db_name  = \'' . PMA_sqlAddslashes($db) . '\''
                   . ' AND table_name = \'' . PMA_sqlAddslashes($table) . '\''
                   . ' AND column_name = \'' . PMA_sqlAddslashes($key) . '\'';
        }
    } elseif (strlen($mimetype) > 0 || strlen($transformation) > 0 || strlen($transformation_options) > 0) {
        $upd_query = 'INSERT INTO ' . PMA_backquote($GLOBALS['cfgRelation']['db']) . '.' . PMA_backquote($cfgRelation['column_info'])
                   . ' (db_name, table_name, column_name, mimetype, transformation, transformation_options) '
                   . ' VALUES('
                   . '\'' . PMA_sqlAddslashes($db) . '\','
                   . '\'' . PMA_sqlAddslashes($table) . '\','
                   . '\'' . PMA_sqlAddslashes($key) . '\','
                   . '\'' . PMA_sqlAddslashes($mimetype) . '\','
                   . '\'' . PMA_sqlAddslashes($transformation) . '\','
                   . '\'' . PMA_sqlAddslashes($transformation_options) . '\')';
    }

    if (isset($upd_query)){
        $upd_rs    = PMA_query_as_cu($upd_query);
        PMA_DBI_free_result($upd_rs);
        unset($upd_rs);
        return true;
    } else {
        return false;
    }
} // end of 'PMA_setMIME()' function

/**
* Returns the real filename of a configured transformation
*
* @param   string   the current filename
*
* @return  string   the new filename
*
* @access  public
*/
function PMA_sanitizeTransformationFile(&$filename) {
    // garvin: for security, never allow to break out from transformations directory

    $include_file = PMA_securePath($filename);

    // This value can also contain a 'php3' value, in which case we map this filename to our new 'php' variant
    $testfile = preg_replace('@\.inc\.php3$@', '.inc.php', $include_file);
    if ($include_file{strlen($include_file)-1} == '3' && file_exists('./libraries/transformations/' . $testfile)) {
        $include_file = $testfile;
        $filename     = $testfile; // Corrects the referenced variable for further actions on the filename;
    }

    return $include_file;
} // end of 'PMA_sanitizeTransformationFile()' function
?>
