/*
 * Short-Desc: Alert handler for system-typed events
 * Require: -
 *
 * Subject-reboot: Device {$alert['device']['hostname']} rebooted
 * Format-reboot: Device {$alert['device']['hostname']} rebooted at {$alert['timestamp']}
 *
 */

if( $state == "reboot" ) {
	$ret = true;
} else {
	return false;
}
return $ret;