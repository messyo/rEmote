<?php

//============================================
//====== START CONNECTION PARAMETERS =========
//============================================

// DATABASE CONNECTION INFOS
//
// Possible Types are:
// "mysql"     for usage of MySQL
// "sqlite"    for usage of SQLite
// "pgsql"     for usage of PostreSQL
// "mysql_old" for usage of MySQL in PHP-Versions < 5
// "oci"       for usage of Oracle
//
// NOTE: All Databases, exept for mysql_old, require an PHP-Version >= 5
//
// ==============================
// Conntect params:
// user     is Username
// password is Userpassword
// host     ss Database-Host (in default-case 'localhost'
// database is Database-Name for rEmote (must exist)
//
// When using SQLite, the Database-File has to be given
// in the database-Variable. Other Variables (exept for type)
// may be left empty.
//
// ==============================
// Example-Configuration for MySQL
//
// $sql = array(
// 	'type'      => 'mysql',
// 	'user'      => 'my_remote_user',
// 	'password'  => 'secret1234',
// 	'host'      => 'localhost',
// 	'database'  => 'remote'
// );
// ==============================
$sql = array(
	'type'      => '',
	'user'      => '',
	'password'  => '',
	'host'      => '',
	'database'  => ''
);

// RPC connect params
// Use http://server/RPCPath
// e.g.: http://localhost/myrcp
// 
// If you have User-Authentication via WWW-Authenticate
// You can use the syntax http://user:password@localhost/RPCPath
// e.g.: http://myuser:topsecret123@localhost/myrpc
$rpc_connect="http://localhost/RPC2";


//============================================
//======  END CONNECTION PARAMETERS  =========
//============================================




//============================================
//====== OTHER SETTINGS ======================
//============================================


// Time between giving Torrent-Temp-Path to rTorrent and killing the Torrent.
// In Microseconds
// (Used as fallback only... usually 1 usec/byte is calculated
// (by default 500000)
define('SLEEP_AFTER_TORRENT_LOAD', 500000);

// Sets Propability to call Session-Garbage-Collector
// Propability is 1/[VALUE]
// (by default 20)
define('SESSION_GC_DIVISOR', 20);

// SEMAPHORE-KEY
// Random Number
// Only neccessary to change if multiple-rEmote installations run on one Server
define('SEM_KEY', 865432);
?>
