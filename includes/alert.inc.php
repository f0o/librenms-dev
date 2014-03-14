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
		$this->data["timestamp"] = time();
	}
	
	public function resolve( ) {
		if( $this->raw["obj"] === NULL || $this->raw["type"] === NULL || $this->raw["state"] === NULL ) {
			return false;
		} else {
			if( is_array($this->raw["obj"]) ) {
				if( $this->raw["obj"]["d"] !== NULL ) {
					$this->getDevice($this->raw["obj"]["d"]);
				}
				if( $this->raw["obj"]["p"] !== NULL ) {
					$this->getPort($this->raw["obj"]["p"]);
				}
				if( $this->raw["obj"]["s"] !== NULL ) {
					return false;
				}
			} else {
				$this->getDevice($this->raw["obj"]);
			}
			$this->data["type"] = $this->raw["type"];
			$this->parse( $this->data["type"] );
			$this->data[$this->data["type"]] = $this->callType( $this->data["type"] );
			$this->data["msg"] = $this->getFormat( $this->data["type"] );
			return true;
		}
	}
	
	private function parse( $mixed ) {
		if( !file_exists($config['install_dir']."/includes/alerts/".$this->raw["type"].".inc.php") ) {
			return false;
		}
		foreach( file($config['install_dir']."/includes/alerts/".$this->raw["type"].".inc.php") as $line ) {
			if( preg_match('/^\s?+(\/\/|\*|\/\*)\s?+Format(-'.$this->raw['state'].')?:\s/',$line,$match) == 1 ) {
				if( sizeof($match) == 3 && !$f ) {
						$format = "";
						$f = true;
				}
				if( !$f || $format == "" ) {
					$format .= trim(preg_replace('/^\s?+(\/\/|\*|\/\*)\s?+Format(-'.$this->raw['state'].')?:\s/','',$line))." ";
				}
			} elseif( preg_match('/^\s?+(\/\/|\*|\/\*)\s?+Subject(-'.$this->raw['state'].')?:\s/',$line,$match) == 1 ) {
				if( sizeof($match) == 3 && !$s ) {
					$subject = "";
					$s = true;
				}
				if( !$s || $format == "" ) {
					$format .= trim(preg_replace('/^\s?+(\/\/|\*|\/\*)\s?+Subject(-'.$this->raw['state'].')?:\s/','',$line))." ";
				}
			}
		}
		$this->format = $format;
		$this->subject = $subject;
	}
	
	private function getFormat( $mixed ) {
		global $config;
		if( !$this->format ) {
			return false;
		}
		$alert = $this->data;
		eval('$msg = "'.$this->format.'";');
		return $msg;
	}
	
	private function callType( $mixed ) {
		global $config;
		if( !$this->format ) {
			return false;
		}
		eval('$tmp = function( $state ){ global $config; $extra = $this->raw["extra"]; '.file_get_contents($config['install_dir']."/includes/alerts/".$this->raw["type"].".inc.php").' };');
		$tmp = $tmp($this->raw["state"]);
		return $tmp;
	}
	
	private function getDevice( $mixed ) {
		if( $mixed === NULL ) {
			return false;
		} else {
			$w = is_numeric($mixed) ? "device_id" : "hostname";
			$this->data["device"] = dbFetchRow("SELECT device_id,hostname,sysName,sysContact FROM devices WHERE $w = ?", array($mixed));
			return $this->data["device"];
		}
	}
	
	private function getPort( $mixed ) {
		if( $mixed === NULL ) {
			return false;
		} else {
			$w = is_numeric($mixed) ? "port_id" : "ifName";
			$this->data["port"] = dbFetchRow("SELECT port_id,device_id,ifName,ifDescr,ifPhysAddress,ifSpeed FROM ports WHERE $w = ?", array($mixed));
			$this->data["device"] = $this->getDevice($this->data["port"]["device_id"]);
			unset($this->data["port"]["device_id"]);
			return $this->data["port"];
		}
	}
	
	public function html( ) {
		if( $this->resolve() ) {
			return array( 'emails' => $this->data['recv'], 'subject' => $this->data['subj'], 'body' => $this->data['msg'] );
		} else {
			return false;
		}
	}
	
	public function txt( ) {
		if( $this->resolve() ) {
			return $this->data["msg"];
		} else {
			return false;
		}
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