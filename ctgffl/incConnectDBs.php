<?php
require_once 'DB.php';

// Connect to CFFL and CTGFFL database.
//
$user = 'ctgffld';
$pass = 'c7d237d';
$dbname1='ctgffld_cffldb';
$dbname2='ctgffld_xoopsdb';
$host='localhost';
$dsn1 = "mysql://$user:$pass@$host/$dbname1";
$cffldb = DB::connect($dsn1, false);

if (DB::isError($cffldb)) {

    echo 'Standard Message: ' . $cffldb->getMessage() . "\n";
    echo 'Standard Code: ' . $cffldb->getCode() . "\n";
    echo 'DBMS/User Message: ' . $cffldb->getUserInfo() . "\n";
    echo 'DBMS/Debug Message: ' . $cffldb->getDebugInfo() . "\n";

    die ($cffldb->getMessage().' on cffldb');
}

$cffldb->setFetchMode(DB_FETCHMODE_ASSOC);

$dsn2 = "mysql://$user:$pass@$host/$dbname2";
$ctgffldb = DB::connect($dsn2, false);

if (DB::isError($ctgffldb)) {
    echo 'Standard Message: ' . $ctgffldb->getMessage() . "\n";
    echo 'Standard Code: ' . $ctgffldb->getCode() . "\n";
    echo 'DBMS/User Message: ' . $ctgffldb->getUserInfo() . "\n";
    echo 'DBMS/Debug Message: ' . $ctgffldb->getDebugInfo() . "\n";

    die ($ctgffldb->getMessage(). ' on ctgffldb');
}

$ctgffldb->setFetchMode(DB_FETCHMODE_ASSOC);
?>