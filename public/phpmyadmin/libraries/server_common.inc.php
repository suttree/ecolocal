<?php
/* $Id: server_common.inc.php 9057 2006-05-18 16:53:40Z lem9 $ */
// vim: expandtab sw=4 ts=4 sts=4:

/**
 * Gets some core libraries
 */
require_once('./libraries/common.lib.php');

/**
 * Handles some variables that may have been sent by the calling script
 * Note: this can be called also from the db panel to get the privileges of
 *       a db, in which case we want to keep displaying the tabs of
 *       the Database panel
 */
if (empty($viewing_mode)) {
    unset($db, $table);
}

/**
 * Set parameters for links
 */
$url_query = PMA_generate_common_url((isset($db) ? $db : ''));

/**
 * Defines the urls to return to in case of error in a sql statement
 */
$err_url = 'main.php' . $url_query;

/**
 * Displays the headers
 */
require_once('./libraries/header.inc.php');

/**
 * Checks for superuser privileges
 */

$is_superuser  = PMA_isSuperuser();

// now, select the mysql db
if ($is_superuser) {
    PMA_DBI_select_db('mysql', $userlink);
}

$has_binlogs = FALSE;
$binlogs = PMA_DBI_try_query('SHOW MASTER LOGS', null, PMA_DBI_QUERY_STORE);
if ($binlogs) {
    if (PMA_DBI_num_rows($binlogs) > 0) {
        $binary_logs = array();
        while ($row = PMA_DBI_fetch_array($binlogs)) {
            $binary_logs[] = $row[0];
        }
        $has_binlogs = TRUE;
    }
    PMA_DBI_free_result($binlogs);
}
unset($binlogs);
?>
