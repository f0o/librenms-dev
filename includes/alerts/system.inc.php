/*
 * Short-Desc: Alert handler for system-typed events
 * Require: -
 *
 * Subject: Device {$alert['device']['hostname']} {$alert['system']['state']}
 * Format: Device {$alert['device']['hostname']} {$alert['system']['state']} after {formatUptime($alert['device']['uptime'])}.
 *
 * Subject-up: Device {$alert['device']['hostname']} {$alert['system']['state']}
 * Format-up: Device {$alert['device']['hostname']} back up
 */

if( $state == "reboot" ) {
	$ret['state'] = "rebooted";
} elseif( $state == "up" || $state == "down" ) {
	$ret['state'] = $state;
} else {
	return false;
}
return $ret;