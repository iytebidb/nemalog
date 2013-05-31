<?php

	$path = "d:/sourcecodes/";
	include_once( $path . "classes/clsSWITCHBASE.php");

	class clsSWITCH {
		private $switchbase = null;
		private $host_name = "";
		private $ip_address = "";
		private $ip_address_long = "";
		private $community = "snmp communuty string";
		private $log_base = "log dizini";
		private $log_extension = ".log";
		private $log_file = "";
		private $log_handle = null;
		private $oids = array();
		public $info = array();
		public $interfaces = array();
		public $mac_table = array();
		public $cdp_table = array();
		public $ip_mac_table = array();
		private $vlans = array();
		private $vlan_interface = array();
		public $vlan_names = array();


		public function __construct() {
			$this->switchbase = new clsSWITCHBASE();
			$this->switchbase->setSnmpCommunity( $this->community );
		}

		public function setLogHandle( $hndl ) {
			$this->log_handle = $hndl;
			$this->switchbase->setLogHandle( $this->log_handle );
		}

		public function shutdown_port( $port_name , $reason ) {
			$arx_name = $this->switchbase->_ifName();
			$arx_alias = $this->switchbase->_ifAlias();
			foreach( $arx_name as $key => $value ) {
				if( $value == $port_name ) {
					$this->switchbase->_setoid( "ifAdminStatus" , "i" , 2 , $key );
					$this->switchbase->_setoid( "ifAlias" , "s" , $reason , $key );
				}
			}
		}

		public function no_shutdown_port( $port_name , $reason ) {
			$arx_name = $this->switchbase->_ifName();
			foreach( $arx_name as $key => $value ) {
				if( $value == $port_name ) {
					$this->switchbase->_setoid( "ifAdminStatus" , "i" , 1 , $key );
					$this->switchbase->_setoid( "ifAlias" , "s" , $reason , $key );
				}
			}
		}

		public function getInfo() {
			$this->info = array();
			$this->vlans = array();
			$this->vlan_interface = array();
			$this->vlan_names = array();

			$this->info["description"] = $this->switchbase->_sysDescr();
			$this->info["host_name"] = $this->switchbase->_sysName();
			$this->info["host_name"] = str_replace( ".iyte.edu.tr" , "" , $this->info["host_name"] );
			$this->info["interface_count"] = $this->switchbase->_ifNumber();
			$this->info["default_gateway"] = $this->switchbase->_netDefaultGateway();
			$this->info["image_name"] = $this->switchbase->_probeDownloadFile();
			$this->info["up_time"] = $this->switchbase->_sysUpTime();
			$this->info["contact"] = $this->switchbase->_sysContact();
			$this->info["location"] = $this->switchbase->_sysLocation();
			$this->info["last_change"] = $this->switchbase->_sysORLastChange();

			//$arx = $this->switchbase->_walkOid( null , "vmVlan" );
			$arx = $this->switchbase->_vmVlan();

			if( $arx ) {
				foreach( $arx as $key => $value ) {
					if( $value != 0 )
						$this->vlans[ $value ] = $value;
						$this->vlan_interface[ $key ] = $value;
				}
			}

			$arx = $this->switchbase->_vtpVlanName();
			if( $arx ) {
				foreach( $arx as $key => $value ) {
					$tmp = str_replace( "vtpVlanName." , "" , $key );
					$tmp = str_replace( "1." , "" , $tmp );
					$this->vlan_names[$tmp] = $value;
				}
			}
			//print_r( $this->vlan_names );

			$this->base_ports = $this->switchbase->_dot1dBasePortIfIndex( $this->vlans );
			//$arx_base_port = $this->switchbase->_dot1dBasePort( $this->vlans );
		}

		public function getInterfaces() {
			$this->interfaces = array();

			$arx_index = $this->switchbase->_ifIndex();
			$arx_type = $this->switchbase->_ifType();
			$arx_description = $this->switchbase->_ifDescr();
			$arx = $this->switchbase->_ifDescr();
			$arx_admin_status = $this->switchbase->_ifAdminStatus();
			$arx_oper_status = $this->switchbase->_ifOperStatus();
			$arx_name = $this->switchbase->_ifName();
			$arx_alias = $this->switchbase->_ifAlias();
			$arx_trunk = $this->switchbase->_vlanTrunkPortDynamicStatus();
			$arx_additional_oper_status = $this->switchbase->_portAdditionalOperStatus();
			print_r( $arx_additional_oper_status );
			//$arx_last_change = $this->switchbase->_ifLastChange();
			//$arx_in_byte = $this->switchbase->_ifInOctets();
			//$arx_in_packet = $this->switchbase->_ifInUcastPkts();
			//$arx_in_error = $this->switchbase->_ifInErrors();
			//$arx_out_byte = $this->switchbase->_ifOutOctets();
			//$arx_out_packet = $this->switchbase->_ifOutUcastPkts();
			//$arx_out_error = $this->switchbase->_ifOutErrors();
			//$arx_mtu = $this->switchbase->_ifMtu();
			//$arx_speed = $this->switchbase->_ifSpeed();
			//$arx_physical_address = $this->switchbase->_ifPhysAddress();
			//$arx_high_speed = $this->switchbase->_ifHighSpeed();

			if( $arx_index ) {
				foreach( $arx_index as $key => $value ) {
					$type_value = $arx_type[ $key ];

					// if this port is an ethernet port then add it to ports table
					if( $type_value == "ethernetCsmacd" ) {
						$this->interfaces[ $key ] = array();

						$this->interfaces[ $key ]["type"] = $type_value;
						$this->interfaces[ $key ]["description"] = ( array_key_exists( $key , $arx_description ) ) ? $arx_description[ $key ] : "";
						$this->interfaces[ $key ]["admin_state"] = ( array_key_exists( $key , $arx_admin_status ) ) ? $arx_admin_status[ $key ] : 0;
						$this->interfaces[ $key ]["current_state"] = ( array_key_exists( $key , $arx_oper_status ) ) ? $arx_oper_status[ $key ] : 0;
						$this->interfaces[ $key ]["additional_state"] = ( array_key_exists( $key , $arx_additional_oper_status ) ) ? $arx_additional_oper_status[ $key ] : 0;
						$this->interfaces[ $key ]["additional_state"] = $this->switchbase->_getPortAdditionalOperationStatus( $this->interfaces[ $key ]["additional_state"] );
						$this->interfaces[ $key ]["interface_name"] = ( array_key_exists( $key , $arx_name ) ) ? $arx_name[ $key ] : "";
						$this->interfaces[ $key ]["alias"] = ( array_key_exists( $key , $arx_alias ) ) ? $arx_alias[ $key ] : "";
						$this->interfaces[ $key ]["trunk"] = ( array_key_exists( $key , $arx_trunk ) ) ? $arx_trunk[ $key ] : 0;
						//if( $this->interfaces[ $key ]["trunk"] != 1 ) $this->interfaces[ $key ]["trunk"] = 0;

						if( is_array( $this->vlan_interface ) ) {
							if( array_key_exists( $key , $this->vlan_interface ) )
								$this->interfaces[ $key ]["vlan_id"] = $this->vlan_interface[$key];
							else $this->interfaces[ $key ]["vlan_id"] = 1;
						}
						else {
							$this->interfaces[ $key ]["vlan_id"] = 0;
							print "ip = " . $this->ip_address . PHP_EOL;
						}

						//$this->interfaces[ $key ]["mtu"] = $this->switchbase->_readValue( "ifMtu" , $arx_mtu[ "ifMtu." . $real_port ] );
						//$this->interfaces[ $key ]["speed"] = $this->switchbase->_readValue( "ifSpeed" , $arx_speed[ "ifSpeed." . $real_port ] );
						//$this->interfaces[ $key ]["physical_address"] = $this->switchbase->_readValue( "ifPhysAddress" , $arx_physical_address[ "ifPhysAddress." . $real_port ] );
						//$this->interfaces[ $key ]["last_change"] = $this->switchbase->_readValue( "ifLastChange" , $arx_last_change[ "ifLastChange." . $real_port ] );
						//$this->interfaces[ $key ]["input_bytes"] = $this->switchbase->_readValue( "ifInOctets" , $arx_in_byte[ "ifInOctets." . $real_port ] );
						//$this->interfaces[ $key ]["input_unicast_packet_count"] = $this->switchbase->_readValue( "ifInUcastPkts" , $arx_in_packet[ "ifInUcastPkts." . $real_port ] );
						//$this->interfaces[ $key ]["input_error_count"] = $this->switchbase->_readValue( "ifInErrors" , $arx_in_error[ "ifInErrors." . $real_port ] );
						//$this->interfaces[ $key ]["output_byte_count"] = $this->switchbase->_readValue( "ifOutOctets" , $arx_out_byte[ "ifOutOctets." . $real_port ] );
						//$this->interfaces[ $key ]["output_unicast_packet_count"] = $this->switchbase->_readValue( "ifOutUcastPkts" , $arx_out_packet[ "ifOutUcastPkts." . $real_port ] );
						//$this->interfaces[ $key ]["output_error_count"] = $this->switchbase->_readValue( "ifOutErrors" , $arx_out_error[ "ifOutErrors." . $real_port ] );
						//$this->interfaces[ $key ]["high_speed"] = $this->switchbase->_readValue( "ifHighSpeed" , $arx_high_speed[ "ifHighSpeed." . $real_port ] );
					}
				}
			}
		}

		public function isTrunk( $prt ) {

		}

		public function getMacTable( $ipmactable ) {
			$this->mac_table = array();
			$vlan_interface = array();
			$vlans = array();
			$arx = $this->switchbase->_vmVlan();
			foreach( $arx as $key => $value ) {
				if( $value != 0 ) $vlans[ $value ] = $value;
				$vlan_interface[ $key ] = $value;
			}
			$arx = array();

			$arx_ports = $this->switchbase->_dot1dTpFdbPort( $vlans );
			$arx_mac = $this->switchbase->_dot1dTpFdbAddress( $vlans );
			$arx_learned = $this->switchbase->_dot1dTpFdbStatus( $vlans );
			$base_ports = $this->switchbase->_dot1dBasePortIfIndex( $vlans );
			$arx_description = $this->switchbase->_ifDescr();
			$arx_trunk = $this->switchbase->_vlanTrunkPortDynamicStatus();

			foreach( $arx_ports as $key => $value ) {
				$tmp = $this->switchbase->_readValue( "dot1dTpFdbPort" , $value );
				if( array_key_exists( $key , $arx_learned ) ) {
					$learned = $this->switchbase->_readValue( "dot1dTpFdbStatus" , $arx_learned[ $key ] );
				}
				else { $learned = 0; }
				if( array_key_exists( $key , $arx_mac ) ) {
					$mac = $this->switchbase->_readValue( "dot1dTpFdbAddress" , $arx_mac[ $key ] );
				}
				else { $mac = "0000.0000.0000"; }

				if( array_key_exists( $tmp , $base_ports ) ) {
					$interface_index = $base_ports[$tmp];
					if( ( array_key_exists( $interface_index , $arx_trunk ) ) && ( $arx_trunk[$interface_index] == 2 ) ) {
						if( ( $learned == 3 ) && ( $mac != "0000.0000.0000" ) ) {
							if( ( !is_null( $ipmactable ) ) && ( array_key_exists( $mac , $ipmactable ) ) ) {
								$this->mac_table[$mac]["vlan"] = $vlan_interface[$interface_index]; //$key_x;
								$this->mac_table[$mac]["interface"] = ( array_key_exists( $interface_index , $arx_description ) ) ? $arx_description[$interface_index] : "";
								$this->mac_table[$mac]["ip_address"] = $ipmactable[$mac];
							}
							else {
								$this->mac_table[$mac]["vlan"] = $vlan_interface[$interface_index]; //$key_x;
								$this->mac_table[$mac]["interface"] = ( array_key_exists( $interface_index , $this->interfaces ) ) ? $arx_description[$interface_index] : "";
								$this->mac_table[$mac]["ip_address"] = "0.0.0.0";
							}
						}
					}
				}
			}
		}


		/*public function getMacTable( $ipmactable ) {
			$this->mac_table = array();

			//foreach( $this->vlans as $key_x => $value_x ) {
			$arx_ports = $this->switchbase->_dot1dTpFdbPort( $this->vlans );
			$arx_mac = $this->switchbase->_dot1dTpFdbAddress( $this->vlans );
			$arx_learned = $this->switchbase->_dot1dTpFdbStatus( $this->vlans );

			$arx_description = $this->switchbase->_ifDescr();
			$arx_trunk = $this->switchbase->_vlanTrunkPortDynamicStatus();

			//$arx_ports = $this->switchbase->_walkOid( $key_x , "dot1dTpFdbPort" );
			//$arx_mac = $this->switchbase->_walkOid( $key_x , "dot1dTpFdbAddress" );
			//$arx_learned = $this->switchbase->_walkOid( $key_x , "dot1dTpFdbStatus" );

			if( $arx_ports ) {
				foreach( $arx_ports as $key => $value ) {
					$tmp = $this->switchbase->_readValue( "dot1dTpFdbPort" , $value );
					if( array_key_exists( $key , $arx_learned ) ) {
						$learned = $this->switchbase->_readValue( "dot1dTpFdbStatus" , $arx_learned[ $key ] );
					}
					else { $learned = 0; }
					if( array_key_exists( $key , $arx_mac ) ) {
						$mac = $this->switchbase->_readValue( "dot1dTpFdbAddress" , $arx_mac[ $key ] );
					}
					else { $mac = "00:00:00:00:00:00"; }

					if( array_key_exists( $tmp , $this->base_ports ) ) {
						$interface_index = $this->base_ports[$tmp];
						if( ( array_key_exists( $interface_index , $this->interfaces ) ) && ( $this->interfaces[$interface_index]["trunk"] == 2 ) ) {
							if( ( $learned == 3 ) && ( $mac != "00:00:00:00:00:00" ) ) {
								if( ( !is_null( $ipmactable ) ) && ( array_key_exists( $mac , $ipmactable ) ) ) {
									$this->mac_table[$mac]["vlan"] = $this->interfaces[$interface_index]["vlan_id"]; //$key_x;
									$this->mac_table[$mac]["interface"] = ( array_key_exists( $interface_index , $this->interfaces ) ) ? $this->interfaces[$interface_index]["description"] : "";
									$this->mac_table[$mac]["ip_address"] = $ipmactable[$mac];
								}
								else {
									$this->mac_table[$mac]["vlan"] = $this->interfaces[$interface_index]["vlan_id"]; //$key_x;
									$this->mac_table[$mac]["interface"] = ( array_key_exists( $interface_index , $this->interfaces ) ) ? $this->interfaces[$interface_index]["description"] : "";
									$this->mac_table[$mac]["ip_address"] = "0.0.0.0";
								}
							}
						}
					}
				}
			}
			//}
		}*/

		public function print_array( $this_array , $this_text ) {
			print $this_text . PHP_EOL;
			print_r( $this_array );
			print PHP_EOL;
		}

		public function getCdpTable() {
			$arx_address = $this->switchbase->_cdpCacheAddress();
			$arx_port = $this->switchbase->_cdpCacheDevicePort();
			$arx_platform = $this->switchbase->_cdpChachePlatform();
			$arx_id = $this->switchbase->_cdpCacheDeviceId();
			$arx_description = $this->switchbase->_ifDescr();
			$host_name = $this->switchbase->_sysName();
			$this->cdp_table = array();
			$counter = 0;

			if( $arx_address ) {
				foreach( $arx_address as $key => $value ) {
					$arr = explode( "." , $key );
					$address_index = $arr[0];
					$this->cdp_table[$counter]["local_ip_long"] = $this->switchbase->_getIpLongAddress( $this->ip_address );
					$this->cdp_table[$counter]["local_ip"] = $this->ip_address;
					$this->cdp_table[$counter]["local_name"] = $host_name;
					//$this->cdp_table[$counter]["local_interface"] = $this->interfaces[$address_index][ "description" ];
					$this->cdp_table[$counter]["local_interface"] = $arx_description[$address_index];

					$this->cdp_table[$counter]["remote_ip"] = $value;
					$arr = explode( "." , $arx_id[ $key ] );
					$this->cdp_table[$counter]["remote_name"] = $arr[0];
					$this->cdp_table[$counter]["remote_interface"] = $arx_port[ $key ];
					$this->cdp_table[$counter]["remote_platform"] = str_replace( ".iyte.edu.tr" , "" , $arx_platform[ $key ] );
					$this->cdp_table[$counter]["remote_platform"] = str_replace( "cisco " , "" , $this->cdp_table[$counter]["remote_platform"] );
					$counter ++;
				}
			}
		}

		public function getIpMacTable() {
			$this->ip_mac_table = array();
			$vlans = array();
			$arx = $this->switchbase->_vmVlan();

			foreach( $arx as $key => $value ) {
				if( $value != 0 ) $vlans[ $value ] = $value;
			}

			foreach( $vlans as $key_x => $value_x ) {
				$arx = $this->switchbase->_walkOid( $key_x , "ipNetToMediaPhysAddress" );
				if( $arx ) {
					foreach( $arx as $key => $value ) {
						$tmp = $this->switchbase->_getIpKey( $key );
						$mac = $this->switchbase->_readValue( "ipNetToMediaPhysAddress" , $value );
						$this->ip_mac_table[$mac] = $tmp;
					}
				}
			}
		}


		public function setIp( $ip ) {
			$this->ip_address = $ip;
			$this->switchbase->setIp( $ip );
			$this->ip_address_long = $this->switchbase->_getIpLongAddress( $ip );
		}

		public function setSnmpCommunity( $cmnyt ) {
			$this->community = $cmnyt;
			$this->switchbase->setSnmpCommunity( $cmnyt );
		}

		public function ping($host, $timeout = 500000 ) {
			// ICMP ping packet with a pre-calculated checksum
			$package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
			$socket  = @socket_create(AF_INET, SOCK_RAW, 1);
			//@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
			$time_out = array('sec' => 0, 'usec' => $timeout );
			//print_r( $time_out );
			@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, $time_out );
			@socket_connect($socket, $host, null);

			//$ts = microtime(true);
			//$result = microtime(true) - $ts;
			@socket_send($socket, $package, strLen($package), 0);
			if( @socket_read( $socket, 255 ) ) $result = true;
			else $result = false;

			@socket_close($socket);

			return $result;
		}

		public function isUp( $this_host )	{
			$p_array = array();

			if( $this->ping( $this_host ) ) $p_array[] = 1;
			if( $this->ping( $this_host ) ) $p_array[] = 1;
			//if( Ping( $this_host ) ) $p_array[] = 1;

			if( count( $p_array ) > 0 ) return true;

			return false;
		}
	}
?>