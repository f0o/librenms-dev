/*
 * Short-Desc: Alert handler for Link-Typed events
 * Require: device, port
 *
 * Subject: Port state change of {$alert['port']['ifDescr']} on {$alert['device']['hostname']}
 * Format: {$alert['device']['hostname']}/{$alert['port']['ifDesc']} went
 * Format: from {$alert['link']['old']} to {$alert['link']['state']}
 *
 * Subject-threshold: Port saturation threshold reached on {$alert['device']['hostname']}/{$alert['port']['ifDescr']}
 * Format-threshold: Port saturation threshold alarm: {$alert['device']['hostname']} on {$alert['port']['ifDescr']}\n
 * Format-threshold: Rates   : {$alert['link']['ifInBits_rate']}/{$alert['link']['ifOutBits_rate']}\n
 * Format-threshold: ifSpeed : {$alert['port']['ifSpeed']}
 */

if( $state == "threshold" ) {
	$ret['ifInBits_rate'] = $extra['ifInBits_rate'];
	$ret['ifOutBits_rate'] = $extra['ifOutBits_rate'];
} elseif( $state == "down" ) {
	$ret['state'] = "down";
	$ret['old'] = "up";
} elseif( $state == "up" ) {
	$ret['state'] = "up";
	$ret['old'] = "down";
} else {
	return false;
}
return $ret;