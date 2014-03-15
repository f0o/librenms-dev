/*
 * Require: device
 * Subject: BGP Session {$alert['bgp']['state']}: {$alert['bgp']['peer']['bgpPeerIdentifier']}
 * Subject: (AS {$alert['bgp']['peer']['bgpPeerRemoteAs']} - {$alert['bgp']['peer']['astext']})
 *
 * Format: BGP Session {$alert['bgp']['state']} since {$alert['bgp']['FsmEstablishedTime']}\n
 * Format: Hostname  : {$alert['device']['hostname']}\n
 * Format: Peer IP   : {$alert['bgp']['peer']['bgpPeerIdentifier']}\n
 * Format: Remote AS : {$alert['bgp']['peer']['bgpPeerRemoteAs']} ({$alert['bgp']['peer']['astext']})
 */
if( $state == "flap" || $state == "up" || $state == "down") {
	$ret["state"] = $state;
	$ret["peer"] = $extra["bgpPeer"];
	$ret["FsmEstablishedTime"] = $extra["FsmEstablishedTime"];
} else {
	return false;
}
return $ret;