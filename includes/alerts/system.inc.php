/*
 * Short-Desc: Alert handler for system-typed events
 * Require: -
 *
 * Subject: Device {$alert['device']['hostname']} {$alert['system']['state']}
 * Format: Device {$alert['device']['hostname']} {$alert['system']['state']} at {$alert['timestamp']}.
 *
 */

if( $state == "reboot" ) {
	$ret['state'] = "rebooted";
} elseif( $state == "up" || $state == "down" ) {
	$ret['state'] = $state;
} else {
	return false;
}
return $ret;