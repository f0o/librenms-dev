/*
 * Short-Desc: Alert handler for Link-Typed events
 * Require: device, port
 *
 * Subject: Port state change of {$alert['port']['ifDescr']}/{$alert['device']['hostname']}
 * Format: {$alert['port']['ifDescr']} of {$alert['device']['hostname']} went
 * Format: from {$alert['link']['old']} to {$alert['link']['state']}
 *
 * Subject-threshold: Port saturation threshold reached on {$alert['device']['hostname']}/{$alert['port']['ifDescr']}
 * Format-threshold: Port saturation threshold alarm: {$alert['device']['hostname']} on {$alert['port']['ifDescr']}\n
 * Format-threshold: Rates   : {$alert['link']['ifInBits_rate']}/{$alert['link']['ifOutBits_rate']}\n
 * Format-threshold: ifSpeed : {$alert['port']['ifSpeed']}
 *
 * Subject-error: Port errors detected on {$alert['device']['hostname']}/{$alert['port']['ifDescr']}
 * Format-error: Port       : {$alert['port']['ifDescr']}\n
 * Format-error: ifSpeed    : {$alert['port']['ifSpeed']}\n
 * Format-error: In-Errors  : {$alert['link']['ifInErrors_delta']}\n
 * Format-error: Out-Errors : {$alert['link']['ifOutErrors_delta']}\n
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
} elseif( $state == "error" ) {
	$ret['ifInErrors_delta'] = $extra['ifInErrors_delta'];
	$ret['ifOutErrors_delta'] = $extra['ifOutErrors_delta'];
} else {
	return false;
}
return $ret;