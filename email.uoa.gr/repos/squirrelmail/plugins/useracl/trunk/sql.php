<?php
/**
 * sql.php
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @package plugins
 * @subpackage useracl
 * @copyright 2003-2008 Alexandros Vellis <avel@noc.uoa.gr>
 * @version $Id$
 * @author This file was contributed by "Mike Battersby" <mib at
 *   unimelb.edu.au>
 *
 * SQL search function.
 */

/** Includes */
require_once 'DB.php';

/**
 * Search database for user
 * @param string $user Username to search for
 * @return int 1 if found, 0 if not.
 */
function sql_search($user) {

    global $useracl_sql_dsn, $useracl_sql_table, $useracl_sql_username_field;

    $db = DB::connect($useracl_sql_dsn, TRUE);

    if (DB::isError($db)) {
        print "Could not connect to useracl search database.";
        return 0;
    }

    $res = $db->query("SELECT count(*) FROM $useracl_sql_table WHERE $useracl_sql_username_field = '$user'");

    if (DB::isError($res)) {
        print "Could not query useracl search database.";
        $db->disconnect();
        return 0;
    }

    $row = $res->fetchRow();
    $count = $row[0];
    $db->disconnect();
    return ($count == 1);
}

