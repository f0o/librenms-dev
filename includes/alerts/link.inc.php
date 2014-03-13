/*
 * Format: $alert["device"]["hostname"]/$alert["port"]["ifName"] went
 * Format: from $alert["link"]["old"] to $alert["link"]["state"] at $alert["timestamp"]
 */
if( $state == "down" ) {
	$ret["state"] = "down";
	$ret["old"] = "up";
} elseif( $state == "up" ) {
	$ret["state"] = "up";
	$ret["old"] = "down";
} else {
	$ret["state"] = "unknown/$state";
	$ret["old"] = "-";
}
return $ret;