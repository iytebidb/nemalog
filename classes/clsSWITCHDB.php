<?php

	class clsSWITCHDB {
		private $connection_id;
		private $mysql_database;
		private $mysql_host;
		private $mysql_user;
		private $mysql_password;
		private $log_handle;
		private $error_message;
		private $ip_address;
		private $ip_address_long;
		private $device_name;
		private $rdate;
		private $rtime;


		public function __construct() {
			$this->connection_id = null;
			$this->mysql_database = "network";
			$this->mysql_host = "mysql sunucu ip adresi";
			$this->mysql_user = "mysql kullanýcý adý";
			$this->mysql_password = "mysql kullanýcý þifresi";
			$this->log_handle = null;
			$this->error_message = "";
			$this->ip_address = "0.0.0.0";
			$this->ip_address_long = "000.000.000.000";
			$this->host = "";
			$this->rdate = "";
			$this->rtime = "";
		}

		public function __destruct() {
			$this->disconnect();
		}

		public function _getIpLongAddress( $inIp ) {
			$out = "";
			$arr = explode( "." , $inIp );

			if( strlen( $arr[0] ) == 1 ) $out .= "00" . $arr[0] . ".";
			else if( strlen( $arr[0] ) == 2 ) $out .= "0" . $arr[0] . ".";
			else $out .= $arr[0] . ".";

			if( strlen( $arr[1] ) == 1 ) $out .= "00" . $arr[1] . ".";
			else if( strlen( $arr[1] ) == 2 ) $out .= "0" . $arr[1] . ".";
			else $out .= $arr[1] . ".";

			if( strlen( $arr[2] ) == 1 ) $out .= "00" . $arr[2] . ".";
			else if( strlen( $arr[2] ) == 2 ) $out .= "0" . $arr[2] . ".";
			else $out .= $arr[2] . ".";

			if( strlen( $arr[3] ) == 1 ) $out .= "00" . $arr[3];
			else if( strlen( $arr[3] ) == 2 ) $out .= "0" . $arr[3];
			else $out .= $arr[3];

			return $out;
		}

		public function _logWrite( $str ) {
			$new_str = $str . "\r\n";
			if( $this->log_handle )
				fputs( $this->log_handle , $new_str );
		}

		public function query_device_list( $ip , $typ , $stat ) {
			$switch_list = array();
			$q_str = "SELECT * FROM devices WHERE type=" . $typ . " AND status=" . $stat;
			if( !is_null( $ip ) ) $q_str .= " AND ip='" . $ip . "'";
			$result = mysql_query( $q_str , $this->connection_id );
			while( $row = mysql_fetch_assoc( $result ) ) {
				$ip = $row["ip"];
				$host = $row["name"];
				$switch_list[ $ip ] = $host;
			}
			mysql_free_result( $result );
			return $switch_list;
		}

		public function setHost( $hst ) {
			$this->mysql_host = $hst;
		}

		public function setUser( $usr ) {
			$this->mysql_user = $usr;
		}

		public function setPassword( $pwd ) {
			$this->mysql_password = $pwd;
		}

		public function setDatabase( $actdb ) {
			$this->mysql_database = $actdb;
		}

		public function setLogHandle( $hnd ) {
			$this->log_handle = $hnd;
		}

		public function setIpAddress( $ip ) {
			$this->ip_address = $ip;
			$this->ip_address_long = $this->_getIpLongAddress( $ip );
		}

		public function setSwitchHost( $host ) {
			$this->device_name = $host;
		}

		public function setStartDatetime( $rdate , $rtime ) {
			$this->rdate = $rdate;
			$this->rtime = $rtime;
		}

		public function connect() {
			if( !is_null( $this->connection_id ) ) $this->disconnect();
			$this->connection_id = mysql_connect( $this->mysql_host , $this->mysql_user , $this->mysql_password );
			if( !$this->connection_id ) return false;
			mysql_select_db( $this->mysql_database );
			return true;
		}

		public function disconnect() {
			if( $this->connection_id ) mysql_close( $this->connection_id );
			$this->connection_id = null;
		}

		public function write_switch_info( $hTable ) {
			$insert_base_str = "INSERT INTO switch_info VALUES ('%s','%s','%s','%s',%d,'%s','%s','%s','%s','%s','%s')";
			$insert_str = sprintf( $insert_base_str ,
									$this->ip_address_long ,
									$this->ip_address ,
									$hTable["host_name"] ,
									$hTable["description"] ,
									$hTable["interface_count"] ,
									$hTable["default_gateway"] ,
									$hTable["image_name"] ,
									$hTable["up_time"] ,
									$hTable["contact"] ,
									$hTable["location"] ,
									$hTable["last_change"]
									);

			$delete_base_str = "DELETE FROM switch_info WHERE ip='%s'";
			$delete_str = sprintf( $delete_base_str , $this->ip_address );
			$result_delete = @mysql_query( $delete_str );
			$result_insert = @mysql_query( $insert_str );
			if( !$result_insert ) {
				$this->error_message = @mysql_error();
				$this->_logWrite( $insert_str );
				$this->_logWrite( $this->error_message );
				return false;
			}
			return true;
		}

		public function write_ap_info( $hTable ) {
			$insert_base_str = "INSERT INTO ap_info VALUES ('%s','%s','%s','%s',%d,'%s','%s','%s','%s','%s','%s')";
			$insert_str = sprintf( $insert_base_str ,
									$this->ip_address_long ,
									$this->ip_address ,
									$hTable["host_name"] ,
									$hTable["description"] ,
									$hTable["interface_count"] ,
									$hTable["default_gateway"] ,
									$hTable["image_name"] ,
									$hTable["up_time"] ,
									$hTable["contact"] ,
									$hTable["location"] ,
									$hTable["last_change"]
									);

			$delete_base_str = "DELETE FROM ap_info WHERE ip='%s'";
			$delete_str = sprintf( $delete_base_str , $this->ip_address );
			$result_delete = @mysql_query( $delete_str );
			$result_insert = @mysql_query( $insert_str );
			if( !$result_insert ) {
				$this->error_message = @mysql_error();
				$this->_logWrite( $insert_str );
				$this->_logWrite( $this->error_message );
				return false;
			}
			return true;
		}

		public function write_vlans( $hTable ) {
			$insert_base_str = "INSERT INTO vlan_list VALUES ( %d,'%s','%s')";
			$delete_str = "DELETE FROM vlan_list";
			$result_delete = @mysql_query( $delete_str );
			foreach( $hTable as $key => $value ) {
				$insert_str = sprintf( $insert_base_str , $key , $value , "-" );
				$result_insert = @mysql_query( $insert_str );
				if( !$result_insert ) {
					$this->error_message = @mysql_error();
					$this->_logWrite( $insert_str );
					$this->_logWrite( $this->error_message );
					return false;
				}
			}
			return true;
		}

		public function write_interfaces( $iTable ) {
			// device ip long
			// device ip
			// device name
			// device interface
			// trunk ?
			// admin status
			// oper status


			$base_delete_str = "DELETE FROM interfaces WHERE ip='%s'";
			//$base_select_str = "SELECT * FROM interfaces WHERE ip_long='%s' AND ip='%s' AND name='%s' AND interface='%s'";
			//$base_update_str = "UPDATE interfaces SET trunk=%d, admin_status=%d, oper_status=%d WHERE ip_long='%s' AND ip='%s' AND name='%s' AND interface='%s'";
			$base_insert_str = "INSERT INTO interfaces VALUES ( '%s' , '%s' , '%s' , '%s', %d , %d , %d, %d , '%s' )";

			$delete_str = sprintf( $base_delete_str , $this->ip_address );
			$result_delete = @mysql_query( $delete_str );

			foreach( $iTable as $key => $value ) {
				$device_interface = $iTable[ $key ]["description"];
				$trunk = $iTable[ $key ]["trunk"];
				$vlan = $iTable[ $key ]["vlan_id"];
				$admin_status = $iTable[ $key ]["admin_state"];
				$oper_status = $iTable[ $key ]["current_state"];
				$additional_oper_status = $iTable[ $key ]["additional_state"];

				$insert_str = sprintf( $base_insert_str , $this->ip_address_long, $this->ip_address, $this->device_name, $device_interface, $vlan, $trunk, $admin_status, $oper_status , $additional_oper_status );
				$res = @mysql_query( $insert_str );
				if( !$res )	{
					$this->error_message = @mysql_error();
					$this->_logWrite( $insert_str );
					$this->_logWrite( $this->error_message );
				}
			}

			return true;
		}

		public function write_cdp_table( $cTable ) {
			// local ip long
			// local ip
			// local name
			// local interface
			// local platform
			// remote ip
			// remote name
			// remote interface
			// remote platform

			$base_select_str = "SELECT * FROM cdp WHERE ip_long='%s' AND ip='%s' AND name='%s' AND interface='%s'";
			$base_update_str = "UPDATE cdp SET remote_ip='%s', remote_name='%s', remote_interface='%s', remote_platform='%s' WHERE ip_long='%s' AND ip='%s' AND name='%s' AND interface='%s'";
			$base_insert_str = "INSERT INTO cdp VALUES ( '%s' , '%s' , '%s' , '%s', '%s' , '%s' , '%s' , '%s' )";

			foreach( $cTable as $key => $value ) {
				$local_ip_long = $cTable[ $key ]["local_ip_long"];
				$local_ip = $cTable[ $key ]["local_ip"];
				$local_name = $cTable[ $key ]["local_name"];
				$local_interface = $cTable[ $key ]["local_interface"];
				$remote_ip = $cTable[ $key ]["remote_ip"];
				$remote_name = $cTable[ $key ]["remote_name"];
				$remote_interface = $cTable[ $key ]["remote_interface"];
				$remote_platform = $cTable[ $key ]["remote_platform"];

				$select_str = sprintf( $base_select_str , $local_ip_long, $local_ip, $local_name, $local_interface );
				$update_str = sprintf( $base_update_str , $remote_ip, $remote_name, $remote_interface, $remote_platform , $local_ip_long, $local_ip, $local_name, $local_interface );
				$insert_str = sprintf( $base_insert_str , $local_ip_long, $local_ip, $local_name, $local_interface, $remote_ip, $remote_name, $remote_interface, $remote_platform );

				$result_select = @mysql_query( $select_str );
				if( $result_select ) {
					if( mysql_num_rows($result_select) > 0 ) {
						$res = @mysql_query( $update_str );
						if( !$res )	{
							$this->error_message = @mysql_error();
							$this->_logWrite( $update_str );
							$this->_logWrite( $this->error_message );
						}
					}
					else {
						$res = @mysql_query( $insert_str );
						if( !$res )	{
							$this->error_message = @mysql_error();
							$this->_logWrite( $insert_str );
							$this->_logWrite( $this->error_message );
						}
					}

					mysql_free_result( $result_select );
				}
				else {
					$this->error_message = @mysql_error();
					$this->_logWrite( $select_str );
					$this->_logWrite( $this->error_message );
				}
			}

			return true;
		}

		public function write_mac_table( $mTable ) {
			// ip long - ip - name - vlan - interface - mac address - ip address

			$base_delete_str = "DELETE FROM ip_mac WHERE ip_long='%s'";
			$base_insert_str = "INSERT INTO ip_mac VALUES ( '%s' , '%s' , '%s' , %d , '%s', '%s' , '%s' )";
			$mobil_base_insert_str = "INSERT INTO ip_mac_mobil VALUES ( '%s' , '%s' , '%s' , '%s' , '%s' , %d , '%s', '%s' , '%s' )";

			$delete_str = sprintf( $base_delete_str , $this->ip_address_long );
			$res = @mysql_query( $delete_str );
			if( !$res )	{
				$this->error_message = @mysql_error();
				$this->_logWrite( $delete_str );
				$this->_logWrite( $this->error_message );
			}

			foreach( $mTable as $key => $value ) {
				$vlan = $mTable[$key]["vlan"];
				$interface = $mTable[$key]["interface"];
				$mac_address = $key;
				//$arr = explode( ":" , $mac_address );
				//$mac_address = $arr[0] . $arr[1] . "." . $arr[2] . $arr[3] . "." . $arr[4] . $arr[5];
				$ip_address = $mTable[$key]["ip_address"];

				$insert_str = sprintf( $base_insert_str , $this->ip_address_long, $this->ip_address, $this->device_name, $vlan , $interface, $mac_address, $ip_address );
				$mobil_insert_str = sprintf( $mobil_base_insert_str , $this->rdate , $this->rtime , $this->ip_address_long, $this->ip_address, $this->device_name, $vlan , $interface, $mac_address, $ip_address );

				$res = mysql_query( $insert_str );
				if( !$res )	{
					$this->error_message = @mysql_error();
					$this->_logWrite( $insert_str );
					$this->_logWrite( $this->error_message );
				}

				$res = mysql_query( $mobil_insert_str );
				if( !$res )	{
					$this->error_message = @mysql_error();
					$this->_logWrite( $mobil_insert_str );
					$this->_logWrite( $this->error_message );
				}
			}
			return true;
		}
	}
?>