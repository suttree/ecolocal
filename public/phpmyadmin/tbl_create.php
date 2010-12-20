<?php
/* $Id: tbl_create.php 10144 2007-03-20 11:22:31Z cybot_tm $ */
// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Get some core libraries
 */
require_once './libraries/common.lib.php';
require_once './libraries/Table.class.php';

$js_to_run = 'functions.js';

if (isset($table)) {
    $table = PMA_sanitize($table);
}

require_once './libraries/header.inc.php';

// Check parameters
PMA_checkParameters(array('db', 'table'));

/**
 * Defines the url to return to in case of error in a sql statement
 */
$err_url = $cfg['DefaultTabTable'] . '?' . PMA_generate_common_url($db, $table);


/**
 * Selects the database to work with
 */
PMA_DBI_select_db($db);


/**
 * The form used to define the structure of the table has been submitted
 */
$abort = false;
if (isset($submit_num_fields)) {
    $regenerate = true;
    $num_fields = $orig_num_fields + $added_fields;
} elseif (isset($do_save_data)) {
    $sql_query = $query_cpy = '';

    // Transforms the radio button field_key into 3 arrays
    $field_cnt = count($field_name);
    for ($i = 0; $i < $field_cnt; ++$i) {
        if (isset(${'field_key_' . $i})) {
            if (${'field_key_' . $i} == 'primary_' . $i) {
                $field_primary[] = $i;
            }
            if (${'field_key_' . $i} == 'index_' . $i) {
                $field_index[]   = $i;
            }
            if (${'field_key_' . $i} == 'unique_' . $i) {
                $field_unique[]  = $i;
            }
        } // end if
    } // end for
    // Builds the fields creation statements
    for ($i = 0; $i < $field_cnt; $i++) {
        // '0' is also empty for php :-(
        if (empty($field_name[$i]) && $field_name[$i] != '0') {
            continue;
        }

        $query = PMA_Table::generateFieldSpec($field_name[$i], $field_type[$i], $field_length[$i], $field_attribute[$i], isset($field_collation[$i]) ? $field_collation[$i] : '', $field_null[$i], $field_default[$i], isset($field_default_current_timestamp[$i]), $field_extra[$i], isset($field_comments[$i]) ? $field_comments[$i] : '', $field_primary, $i);

        $query .= ', ';
        $sql_query .= $query;
        $query_cpy .= "\n" . '  ' . $query;
    } // end for
    unset($field_cnt);
    unset($query);
    $sql_query = preg_replace('@, $@', '', $sql_query);
    $query_cpy = preg_replace('@, $@', '', $query_cpy);

    // Builds the primary keys statements
    $primary     = '';
    $primary_cnt = (isset($field_primary) ? count($field_primary) : 0);
    for ($i = 0; $i < $primary_cnt; $i++) {
        $j = $field_primary[$i];
        if (isset($field_name[$j]) && strlen($field_name[$j])) {
            $primary .= PMA_backquote($field_name[$j]) . ', ';
        }
    } // end for
    unset($primary_cnt);
    $primary = preg_replace('@, $@', '', $primary);
    if (strlen($primary)) {
        $sql_query .= ', PRIMARY KEY (' . $primary . ')';
        $query_cpy .= ',' . "\n" . '  PRIMARY KEY (' . $primary . ')';
    }
    unset($primary);

    // Builds the indexes statements
    $index     = '';
    $index_cnt = (isset($field_index) ? count($field_index) : 0);
    for ($i = 0;$i < $index_cnt; $i++) {
        $j = $field_index[$i];
        if (isset($field_name[$j]) && strlen($field_name[$j])) {
            $index .= PMA_backquote($field_name[$j]) . ', ';
        }
    } // end for
    unset($index_cnt);
    $index = preg_replace('@, $@', '', $index);
    if (strlen($index)) {
        $sql_query .= ', INDEX (' . $index . ')';
        $query_cpy .= ',' . "\n" . '  INDEX (' . $index . ')';
    }
    unset($index);

    // Builds the uniques statements
    $unique     = '';
    $unique_cnt = (isset($field_unique) ? count($field_unique) : 0);
    for ($i = 0; $i < $unique_cnt; $i++) {
        $j = $field_unique[$i];
        if (isset($field_name[$j]) && strlen($field_name[$j])) {
           $unique .= PMA_backquote($field_name[$j]) . ', ';
        }
    } // end for
    unset($unique_cnt);
    $unique = preg_replace('@, $@', '', $unique);
    if (strlen($unique)) {
        $sql_query .= ', UNIQUE (' . $unique . ')';
        $query_cpy .= ',' . "\n" . '  UNIQUE (' . $unique . ')';
    }
    unset($unique);

    // Builds the FULLTEXT statements
    $fulltext     = '';
    $fulltext_cnt = (isset($field_fulltext) ? count($field_fulltext) : 0);
    for ($i = 0; $i < $fulltext_cnt; $i++) {
        $j = $field_fulltext[$i];
        if (isset($field_name[$j]) && strlen($field_name[$j])) {
           $fulltext .= PMA_backquote($field_name[$j]) . ', ';
        }
    } // end for

    $fulltext = preg_replace('@, $@', '', $fulltext);
    if (strlen($fulltext)) {
        $sql_query .= ', FULLTEXT (' . $fulltext . ')';
        $query_cpy .= ',' . "\n" . '  FULLTEXT (' . $fulltext . ')';
    }
    unset($fulltext);

    // Builds the 'create table' statement
    $sql_query      = 'CREATE TABLE ' . PMA_backquote($table) . ' (' . $sql_query . ')';
    $query_cpy      = 'CREATE TABLE ' . PMA_backquote($table) . ' (' . $query_cpy . "\n" . ')';

    // Adds table type, character set and comments
    if (!empty($tbl_type) && ($tbl_type != 'Default')) {
        $sql_query .= ' ' . PMA_ENGINE_KEYWORD  . ' = ' . $tbl_type;
        $query_cpy .= "\n" . PMA_ENGINE_KEYWORD . ' = ' . $tbl_type;
    }
    if (PMA_MYSQL_INT_VERSION >= 40100 && !empty($tbl_collation)) {
        $sql_query .= PMA_generateCharsetQueryPart($tbl_collation);
        $query_cpy .= "\n" . PMA_generateCharsetQueryPart($tbl_collation);
    }

    if (!empty($comment)) {
        $sql_query .= ' COMMENT = \'' . PMA_sqlAddslashes($comment) . '\'';
        $query_cpy .= "\n" . 'COMMENT = \'' . PMA_sqlAddslashes($comment) . '\'';
    }

    // Executes the query
    $error_create = false;
    $result    = PMA_DBI_try_query($sql_query) or $error_create = true;

    if ($error_create == false) {
        $sql_query = $query_cpy . ';';
        unset($query_cpy);
        $message   = $strTable . ' ' . htmlspecialchars($table) . ' ' . $strHasBeenCreated;

        // garvin: If comments were sent, enable relation stuff
        require_once './libraries/relation.lib.php';
        require_once './libraries/transformations.lib.php';

        $cfgRelation = PMA_getRelationsParam();

        // garvin: Update comment table, if a comment was set.
        if (isset($field_comments) && is_array($field_comments) && $cfgRelation['commwork'] && PMA_MYSQL_INT_VERSION < 40100) {
            foreach ($field_comments AS $fieldindex => $fieldcomment) {
                if (isset($field_name[$fieldindex]) && strlen($field_name[$fieldindex])) {
                    PMA_setComment($db, $table, $field_name[$fieldindex], $fieldcomment, '', 'pmadb');
                }
            }
        }

        // garvin: Update comment table for mime types [MIME]
        if (isset($field_mimetype) && is_array($field_mimetype) && $cfgRelation['commwork'] && $cfgRelation['mimework'] && $cfg['BrowseMIME']) {
            foreach ($field_mimetype AS $fieldindex => $mimetype) {
                if (isset($field_name[$fieldindex]) && strlen($field_name[$fieldindex])) {
                    PMA_setMIME($db, $table, $field_name[$fieldindex], $mimetype, $field_transformation[$fieldindex], $field_transformation_options[$fieldindex]);
                }
            }
        }

        $message = $strTable . ' '
         . htmlspecialchars(PMA_backquote($db) . '.' . PMA_backquote($table))
         . ' ' . $strHasBeenCreated;
        $display_query = $sql_query;
        unset($sql_query);

        // do not switch to sql.php - as there is no row to be displayed on a new table
        if ($cfg['DefaultTabTable'] === 'sql.php') {
            require './tbl_structure.php';
        } else {
            require './' . $cfg['DefaultTabTable'];
        }
        exit;
    } else {
        PMA_mysqlDie('', '', '', $err_url, false);
        // garvin: An error happened while inserting/updating a table definition.
        // to prevent total loss of that data, we embed the form once again.
        // The variable $regenerate will be used to restore data in libraries/tbl_properties.inc.php
        $num_fields = $orig_num_fields;
        $regenerate = true;
    }
} // end do create table

/**
 * Displays the form used to define the structure of the table
 */
if ($abort == false) {
    if (isset($num_fields)) {
        $num_fields = intval($num_fields);
    }
    // No table name
    if (!isset($table) || trim($table) == '') {
        PMA_mysqlDie($strTableEmpty, '', '', $err_url);
    }
    // No valid number of fields
    elseif (empty($num_fields) || !is_int($num_fields)) {
        PMA_mysqlDie($strFieldsEmpty, '', '', $err_url);
    }
    // Does table exist?
    elseif (!(PMA_DBI_get_fields($db, $table) === false)) {
        PMA_mysqlDie(sprintf($strTableAlreadyExists, htmlspecialchars($table)), '', '', $err_url);
    }
    // Table name and number of fields are valid -> show the form
    else {
        $action = 'tbl_create.php';
        require './libraries/tbl_properties.inc.php';
        // Displays the footer
        require_once './libraries/footer.inc.php';
   }
}

?>
