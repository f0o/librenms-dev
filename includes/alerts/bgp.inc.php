/*
 * Subject: BGP Session {$alert['bgp']['state']}: {$alert['bgp']['bgpPeerIdentifier']}
 * Subject: (AS {$alert['bgp']['bgpPeerRemoteAs']} - {$alert['bgp']['astext']})
 *
 * Format: BGP Session {$alert['bgp']['state']} since {$alert['bgp']['bgpPeerFsmEstablishedTime']}\n
 * Format: Hostname  : {$alert['device']['hostname']}\n
 * Format: Peer IP   : {$alert['bgp']['bgpPeerIdentifier']}\n
 * Format: Remote AS : {$alert['bgp']['bgpPeerRemoteAs']} ({$alert['bgp']['astext']})
 */
if( $state == "flap" || $state == "up" || $state == "down") {
	$ret["state"] = $state;
	$ret = array_merge( $ret, $extra );
} else {
	return false;
}
return $ret;