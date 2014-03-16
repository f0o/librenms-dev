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
		"obj"    => NULL,  //Can be Int, String like 'd123 or p123 or s123' or Array( [d|p|s]=>123 )
		"type"   => NULL,  //Can be Int or String, see alert-handler's documenation.
		"state"  => NULL,  //Property to handle alert for.
		"extra"  => NULL,  //Extra information for those handlers that require more input.
	);
	private $data = array( );
	public function __construct( $raw=false ) {
		$this->data["timestamp"] = time();
		if( is_array($raw) ) {
			$this->raw['obj']   =   $raw['obj'];
			$this->raw['type']  =  $raw['type'];
			$this->raw['state'] = $raw['state'];
			$this->raw['extra'] = $raw['extra'];
			if( $raw['issue'] || $config['alert']['autoissue'] ) {
				return $this->issue();
			}
		}
	}
	
	public function __destruct( ) {
 	global $config;
		if( $config['alert']['autoissue'] ) {
		 return $this->issue();
		}
	}
	
	public function resolve( ) {
		global $config;
		if( !$this->raw["obj"] || !$this->raw["type"] || $this->raw["state"] === NULL ) {
			return false;
		} else {
			if( !$config['alert'][$this->raw["type"]] && !$config['alert']['*']) {
				return false;
			}
			if( is_array($this->raw["obj"]) ) {
				if( @$this->raw["obj"]["d"] !== NULL ) {
					if( !$this->getDevice($this->raw["obj"]["d"]) ) {
						return false;
					}
				}
				if( @$this->raw["obj"]["p"] !== NULL ) {
					if( !$this->getPort($this->raw["obj"]["p"]) ) {
						return false;
					}
				}
				if( @$this->raw["obj"]["s"] !== NULL ) {
					return false;
				}
			} else {
				if( $this->raw["obj"][0] == "p" ) {
					if( !$this->getPort(substr($this->raw["obj"],1)) ) {
						return false;
					}
				} elseif( $this->raw["obj"][0] == "d" ) {
					if( !$this->getDevice(substr($this->raw["obj"],1)) ) {
						return false;
					}
				} else {
					if( !$this->getDevice($this->raw["obj"]) ) {
						return false;
					}
				}
			}
			if( !$this->data["type"] ) {
				$this->data["type"] = $this->raw["type"];
			}
			if( !$this->parse ) {
				if( !$this->parse( $this->data["type"] ) ) {
					return false;
				}
			}
			if( !$this->data[$this->data["type"]] ) {
				$this->data[$this->data["type"]] = $this->callType( $this->data["type"] );
			}
			if( !$this->data['Format'] ) {
				if( !$this->getFormat( $this->data["type"] ) ) {
					return false;
				}
			}
			if( !$this->data['recv'] ) {
				if( !$this->getContacts( ) ) {
					return false;
				}
			}
			return true;
		}
	}
	
	private function chkissue( $deep=false ) {
		if( $deep === false ) {
			var_dump('$config[\'alert\'][\'fine\'][\'example.net\'] = false');
			if( $config['alert']['fine'][$this->data['device']['hostname']] === false ) {
				return false;
			}
			var_dump('$config[\'alert\'][\'fine\'][\'example.net\'][\'sensors\'] = false');
			if( $config['alert']['fine'][$this->data['device']['hostname']][$this->raw['type']] === false ) {
				return false;
			}
			var_dump('$config[\'alert\'][\'fine\'][\'example.net\'][\'eth0\'] = false');
			if( $config['alert']['fine'][$this->data['device']['hostname']][$this->data['port']['ifName']] === false ) {
				return false;
			}
			var_dump('$config[\'alert\'][\'fine\'][\'example.net\'][\'eth0\'][\'bgp\'] = false');
			if( $config['alert']['fine'][$this->data['device']['hostname']][$this->data['port']['ifName']][$this->raw['type']] === false ) {
				return false;
			}
			return true;
		} else {
			var_dump('$config[\'alert\'][\'fine\'][\'example.net\'][\'sensors\'][\'email\'] = false');
		 if( $config['alert']['fine'][$this->data['device']['hostname']][$this->raw['type']][$deep] === false ) {
		 	return false;
		 }
		 var_dump('$config[\'alert\'][\'fine\'][\''.$this->data['device']['hostname'].'\'][\''.$this->data['port']['ifName'].'\'][\''.$this->raw['type'].'\'][\''.$deep.'\'] = false');
		 if( $config['alert']['fine'][$this->data['device']['hostname']][$this->data['port']['ifName']][$this->raw['type']][$deep] === false ) {
		 	return false;
		 }
		 return true;
		}
	}
	
	public function issue( $mixed=false ) {
		global $config;
		if( !$this->resolve() || !$this->parse || !$this->chkissue() ) {
			return false;
		}
		foreach( $config['alert']['issue'] as $type ) {
			if( !file_exists($config['install_dir']."/includes/alerts/transport.".$type.".php") || !$this->chkissue($type) ) {
				continue;
			}
			var_dump($type);
			eval('$tmp = function( $state ){ global $config; $extra = $this->raw["extra"]; '.file_get_contents($config['install_dir']."/includes/alerts/transport.".$type.".php").' };');
			$tmp = $tmp($mixed);
			var_dump($tmp);
			unset($tmp);
		}
		return true;
	}
	
	private function parse( $mixed ) {
		global $config;
		if( !file_exists($config['install_dir']."/includes/alerts/".$this->raw["type"].".inc.php") ) {
			return false;
		}
		$tmp = array();
		$parse = array( "Format"=>"", "Subject"=>"", "Require"=>"" );
		if( is_array($config['alert']['formats']) ) {
			foreach( $config['alert']['formats'] as $format ) {
				$parse[$format] = "";
			}
		}
		foreach( file($config['install_dir']."/includes/alerts/".$this->raw["type"].".inc.php") as $line ) {
			foreach( $parse as $k => $v ) {
				if( preg_match('/^\s?+(\/\/|\*|\/\*)\s?+'.$k.'(-'.$this->raw['state'].')?:\s/',$line,$match) == 1 ) {
					if( sizeof($match) == 3 && !$tmp[$k] ) {
						$parse[$k] = "";
						$tmp[$k] = true;
					}
					if( !$tmp[$k] || sizeof($match) == 3 ) {
						$parse[$k] .= trim(preg_replace('/^\s?+(\/\/|\*|\/\*)\s?+'.$k.'(-'.$this->raw['state'].')?:\s/','',$line))." ";
					}
				}
			}
		}
		foreach( $parse as $v ) {
			if( strlen($v) == 0 ) {
				return false;
			}
		}
		$this->parse = $parse;
		return true;
	}
	
	private function getContacts( ) {
		global $config;
		if( !$this->parse ) {
			return false;
		}
		$contacts = array();
		$uids = array();
		$tmpa = array();
		$tmp  = NULL;
		if( is_numeric($this->data["port"]["port_id"]) ) {
			$tmpa = dbFetchRows("SELECT user_id FROM ports_perms WHERE access_level >= 0 AND port_id = ".$this->data["port"]["port_id"]);
			foreach( $tmpa as $tmp ) {
				$uids[$tmp['user_id']] = $tmp['user_id'];
			}
		}
		if( is_numeric($this->data["device"]["device_id"]) ) {
			$contacts[$this->data["device"]["sysContact"]] = "NOC";
			$tmpa = dbFetchRows("SELECT user_id FROM devices_perms WHERE access_level >= 0 AND device_id = ".$this->data["device"]["device_id"]);
			foreach( $tmpa as $tmp ) {
				$uids[$tmp['user_id']] = $tmp['user_id'];
			}
		}
		if( $config["alert"]["globals"] ) {
			$tmpa = dbFetchRows("SELECT realname,email FROM users WHERE level >= 5 AND level < 10");
			foreach( $tmpa as $tmp ) {
				$contacts[$tmp['email']] = $tmp['realname'];
			}
		}
		if( $config["alert"]["admins"] ) {
			$tmpa = dbFetchRows("SELECT realname,email FROM users WHERE level = 10");
			foreach( $tmpa as $tmp ) {
				$contacts[$tmp['email']] = $tmp['realname'];
			}
		}
		if( is_array($uids) ) {
			foreach( $uids as $uid ) {
				$tmp = dbFetchRow("SELECT realname,email FROM users WHERE user_id = ".$uid);
				$contacts[$tmp['email']] = $tmp['realname'];
			}
		}
		$this->data["recv"] = $contacts;
		if( sizeof($contacts) > 0 ) {
			return true;
		} else {
			return false;
		}
	}
	
	private function getFormat( $mixed ) {
		global $config;
		if( !$this->parse ) {
			return false;
		}
		$alert = $this->data;
		foreach( $this->parse as $type => $value ) {
			preg_match_all('/(\{([a-z]+\()?+\$.+(\))?+\})/Ui', $value, $matches);
			$matches = $matches[0];
			foreach( $matches as $mod ) {
				unset($tmp);
				eval('$tmp = '.substr($mod,1,strlen($mod)-2).';');
				$value = str_replace(array($mod,'\n'), array($tmp,"\n"), $value);
			}
			$this->data[$type] = $value;
			unset($tmp);
		}
		return true;
	}
	
	private function callType( $mixed ) {
		global $config;
		if( !$this->parse ) {
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
			$this->data["device"] = dbFetchRow("SELECT * FROM devices WHERE $w = ?", array($mixed));
			return $this->data["device"];
		}
	}
	
	private function getPort( $mixed ) {
		if( $mixed === NULL ) {
			return false;
		} else {
			$w = is_numeric($mixed) ? "port_id" : "ifDescr";
			$this->data["port"] = dbFetchRow("SELECT * FROM ports WHERE $w = ?", array($mixed));
			$this->data["device"] = $this->getDevice($this->data["port"]["device_id"]);
			unset($this->data["port"]["device_id"]);
			return $this->data["port"];
		}
	}
	
	public function mail( ) {
		if( $this->resolve() ) {
			return array( 'emails' => $this->data['recv'], 'subject' => $this->data['Subject'], 'body' => $this->data['Format'] );
		} else {
			return false;
		}
	}
	
	public function txt( ) {
		if( $this->resolve() ) {
			return $this->data['Format'];
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
