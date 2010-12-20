<?php
/* $Id: db_sql.php 9602 2006-10-25 12:25:01Z nijel $ */
// vim: expandtab sw=4 ts=4 sts=4:

require_once('./libraries/common.lib.php');

/**
 * Runs common work
 */
require('./libraries/db_common.inc.php');
require_once './libraries/sql_query_form.lib.php';

/**
 * Gets informations about the database and, if it is empty, move to the
 * "db_structure.php" script where table can be created
 */
require('./libraries/db_info.inc.php');
if ( $num_tables == 0 && empty( $db_query_force ) ) {
    $sub_part   = '';
    $is_info    = TRUE;
    require './db_structure.php';
    exit();
}

/**
 * Query box, bookmark, insert data from textfile
 */
PMA_sqlQueryForm();

/**
 * Displays the footer
 */
require_once './libraries/footer.inc.php';
?>
