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

class Alert implements arrayaccess {
	private $raw = array(
							"obj"    => NULL,  //Can be Int, String or Array( Prop=>Val )
							"type"   => NULL,  //Can be Int or String
							"state"  => NULL,  //New state
						);
	private $data = array( );
	public function __construct( ) {
		
	}
	
	public function resolve( ) {
		if( $this->raw["obj"] === NULL || $this->raw["type"] === NULL || $this->raw["state"] === NULL ) {
			return false;
		} else {
			if( is_array($this->raw["obj"]) ) {
				if( $this->raw["obj"]["d"] !== NULL ) {
					$this->data["device"] = $this->getDevice(is_array($this->raw["obj"]["d"]));
				}
				if( $this->raw["obj"]["p"] !== NULL ) {
					$this->data["port"] = $this->getPort(is_array($this->raw["obj"]["p"]));
				}
				if( $this->raw["obj"]["s"] !== NULL ) {
					return false;
				}
			} else {
				$this->data["device"] = $this->getDevice(is_array($this->raw["obj"]));
			}
			
			return true;
		}
	}
	
	private function getDevice( $mixed ) {
		if( $mixed === NULL ) {
			return false;
		} else {
			$w = is_numeric($mixed) ? "device_id" : "hostname";
			$this->device = dbFetchRow("SELECT device_id,hostname,sysName,sysContact FROM devices WHERE $w = ?", array($this->raw["obj"]["d"]));
			return $this->device;
		}
	}
	
	private function getPort( $mixed ) {
		if( $mixed === NULL ) {
			return false;
		} else {
			$w = is_numeric($mixed) ? "port_id" : "ifName";
			$this->port = dbFetchRow("SELECT port_id,device_id,ifName,ifPhysAddress,ifSpeed FROM ports WHERE $w = ?", array($this->raw["obj"]["p"]));
			return $this->port;
		}
	}
	
	public function txt( ) {
		return false;
	}
	
	public function raw( ) {
		if( $this->resolve() ) {
			return $this->data;
		} else {
			return false;
		}
	}
	
	public function json( ) {
		if( $this->resolve() ) {
			return json_encode($this->data);
		} else {
			return false;
		}
	}
	
	public function serialize( ) {
		if( $this->resolve() ) {
			return serialize($this->data);
		} else {
			return false;
		}
	}
	
	public function csv( ) {
		return false;
	}
	
	public function offsetExists( $offset ) {
		if( $this->raw[$offset] !== NULL ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function offsetGet( $offset ) {
		if( $this->offsetExists( $offset ) ) {
			return $this->raw[$offset];
		} else {
			return false;
		}
	}
	
	public function offsetSet( $offset, $value ) {
		if( !$this->offsetExists( $offset ) ) {
			$this->raw[$offset] = $value;
			return true;
		} else {
			return false;
		}
	}
	
	public function offsetUnset( $offset ) {
		return false;
	}
	
}
?>