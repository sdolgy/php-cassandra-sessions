<?php

// Sasha Dolgy <sasha@dolgy.com>
// Twitter: @sdolgy
// using phpcassa:  https://github.com/thobbs/phpcassa

$ROOT = '/path/to/phpcassa';

require_once($GLOBALS['ROOT'] . '/include/connection.php');
require_once($GLOBALS['ROOT'] . '/include/columnfamily.php');

function getConn() { 
	// return a cassandra connection
}

// helper function
function add_record($cf, $key, $cols, $expiry=null) {

	if ($cf != null && $key != null && isset($cols)) {
		$target_cf = new ColumnFamily(getConn(), $cf);
		if ($expiry == null) { $target_cf->insert($key, $cols, null); }
		else { $target_cf->insert($key, $cols, null, $ttl=$expiry); }
	} else {
		echo "null information for add_record";
	}
}



function open($session_name)
{
  return(true);
}

function close()
{
  return(true);
}

function read($id) {
	try {
		$target_cf = new ColumnFamily(getConn(), $GLOBALS['cf_sessions']);
		$sess_data = $target_cf->get($GLOBALS['site_name'],$columns=array($id));
		return $sess_data[$id];
	} catch (Exception $e) {
		return(false);
	}
}

// $session_expiration is in seconds.  if the row isn't touched after the elapsed number of seconds,
// it disappears and invalidates the session.  makes gc($maxlifetime) redundant.
function write($id, $sess_data) {
    try {
		add_record($GLOBALS['cf_sessions'], $GLOBALS['site_name'], array($id => $sess_data), $GLOBALS['session_expiration']);
		return(true);
	} catch (Exception $e) {
		return(false);
	}
}

function destroy($id) {
	try {
		$target_cf = new ColumnFamily(getConn(), $GLOBALS['cf_sessions']);
		$target_cf->remove($GLOBALS['site_name'], array($id));
		return(true);
	} catch (Exception $e) {
		return(false);
	}
}

function gc($maxlifetime) {
	// handled by col expiry
  	return true;
}

session_set_save_handler("open", "close", "read", "write", "destroy", "gc");

?>
