#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage alerts
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 *
 */

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.php");
include("html/includes/functions.inc.php");
include("includes/alert.inc.php");

// Check all of our interface RRD files for errors

if ($argv[1]) { $where = "AND `port_id` = ?"; $params = array($argv[1]); }

$i = 0;
$errored = array();

foreach (dbFetchRows("SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id $where", $params) as $interface)
{
  $errors = $interface['ifInErrors_delta'] + $interface['ifOutErrors_delta'];
  if ($errors > 1)
  {
    $errored[] = array($interface['port_id'], $interface);
  }
  $i++;
}

echo("Checked $i interfaces\n");

if (is_array($errored))
{ // If there are errored ports
  $i = 0;
  $msg = "Interfaces with errors : \n\n";
  echo $interface['hostname'].'/'.$interface['ifDescr']."\n";
  $alert = new Alert( array( 'obj' => 'p'.$errored[0], 'type' => 'link', 'state' => 'error', 'extra' => $errored[1], 'issue' => true ) );
}

echo("$errored interfaces with errors over the past 5 minutes.\n");

?>
