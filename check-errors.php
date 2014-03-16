#!/usr/bin/env php
<?php
/* Copyright (C) 2014  <f0o@devilcode.org>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

chdir(dirname($argv[0]));
include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.php");
include("html/includes/functions.inc.php");
include("includes/alert.inc.php");

$params = NULL;
if( is_number($argv[1]) ) {
	$where = "AND `port_id` = ?";
	$params = array($argv[1]);
}

$errored = array();
foreach( dbFetchRows("SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id $where", $params) as $interface ) {
	if( ($interface['ifInErrors_delta']+$interface['ifOutErrors_delta']) > 0 ) {
		$errored[] = $interface;
	}
}

echo "Checked $i interfaces\n";
if( sizeof($errored) > 0 ) {
	foreach( $errored as $interface ) {
		echo $interface['hostname'].'/'.$interface['ifDescr']."\n";
		$alert = new Alert( array( 'obj' => 'p'.$interface['port_id'], 'type' => 'link', 'state' => 'error', 'extra' => $interface, 'issue' => true ) );
	}
}
echo sizeof($errored)." interfaces with errors over the past 5 minutes.\n";
?>
