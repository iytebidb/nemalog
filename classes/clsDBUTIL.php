<?php

	class clsDBUTIL {
		private $connection_id;
		private $mysql_database;
		private $mysql_host;
		private $mysql_user;
		private $mysql_password;
		private $log_handle;
		public $error_message;
		private $ip_address;
		private $ip_address_long;
		private $device_name;
		private $rdate;
		private $rtime;


		public function __construct() {
			$this->connection_id = null;
			$this->mysql_database = "network";

			$this->mysql_host = "mysql sunucu adresi";
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

		public function _logWrite( $str ) {
			$new_str = $str . "\r\n";
			if( $this->log_handle )
				fputs( $this->log_handle , $new_str );
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
			if( !is_null( $this->connection_id ) ) mysql_select_db( $this->mysql_database );
		}

		public function setLogHandle( $hnd ) {
			$this->log_handle = $hnd;
		}

		public function connect() {
			if( !is_null( $this->connection_id ) ) $this->disconnect();

			$this->connection_id = @mysql_connect( $this->mysql_host , $this->mysql_user , $this->mysql_password );
			if( !$this->connection_id ) {
				$this->error_message = @mysql_error();
				return false;
			}

			if( !@mysql_select_db( $this->mysql_database ) ) {
				$this->error_message = @mysql_error( $this->connection_id );
				@mysql_close( $this->connection_id );
				$this->connection_id = null;
				return false;
			}
			return true;
		}

		public function disconnect() {
			if( $this->connection_id ) mysql_close( $this->connection_id );
			$this->connection_id = null;
		}


		public function write_eduroam_guest_user_info( $user_id , $pwd , $this_data , $created_by , $info_array ) {
			$insert_base_str = "INSERT INTO guest_info VALUES('%s','%s','%s','%s','%s','%s')";
			$insert_str = sprintf( $insert_base_str , $user_id , $info_array["name"] , $info_array["surname"] , $this_data , $created_by , $pwd );
			$result = mysql_query( $insert_str , $this->connection_id );
			if( !$result ) return false;
			return true;
		}

		// find functions
		public function query_eduroam_log_mac( $user_mac_address ) {
			$log_query_result = array();
			$counter = 0;
			//if( !$this->connection_id ) return $log_query_result;

			$query_string = "SELECT * FROM accept WHERE user_mac_address='" . $user_mac_address . "' ORDER BY auth_date DESC LIMIT 1";
			//print $query_string . "<br>" . PHP_EOL;
			$result = mysql_query( $query_string , $this->connection_id );
			//print_r( $result );
			if( !$result ) return $log_query_result;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $log_query_result;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["user_mac_address"];

				$log_query_result[ $key ]["ap_ip_address"] = $row["ap_ip_address"];
				$log_query_result[ $key ]["mail_address"] = $row["user_name"];
				$log_query_result[ $key ]["user_mac_address"] = $row["user_mac_address"];
				$log_query_result[ $key ]["user_ip_address"] = $row["dhcp_ip_address"];

				$log_query_result[ $key ]["user_host_name"] = "";
				$log_query_result[ $key ]["switch_ip_address"] = "";
				$log_query_result[ $key ]["switch_name"] = "";
				$log_query_result[ $key ]["switch_port_vlan"] = "";
				$log_query_result[ $key ]["switch_port"] = "";

			}
			mysql_free_result( $result );
			return $log_query_result;
		}

		public function query_eduroam_log_ip( $user_ip_address ) {
			$log_query_result = array();
			$counter = 0;

			$query_string = "SELECT * FROM accept WHERE dhcp_ip_address='" . $user_ip_address . "' ORDER BY auth_date DESC LIMIT 1";
			$result = mysql_query( $query_string , $this->connection_id );
			if( !$result ) return $log_query_result;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $log_query_result;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = "";
				$key = $row["dhcp_ip_address"];

				$log_query_result[ $key ]["ap_ip_address"] = $row["ap_ip_address"];
				$log_query_result[ $key ]["mail_address"] = $row["user_name"];
				$log_query_result[ $key ]["user_mac_address"] = $row["user_mac_address"];
				$log_query_result[ $key ]["user_ip_address"] = $row["dhcp_ip_address"];
			}
			mysql_free_result( $result );
			return $log_query_result;
		}

		public function query_dhcp_lease_log_mac( $user_mac_address ) {
			$log_query_result = array();
			$counter = 0;

			$query_string = "SELECT * from lease WHERE hardware_ethernet='" . $user_mac_address . "' ORDER BY starts_date DESC, starts_time DESC LIMIT 1";
			$result = mysql_query( $query_string , $this->connection_id );
			if( !$result ) return $log_query_result;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $log_query_result;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = "";
				$key = $row["hardware_ethernet"];

				$log_query_result[ $key ]["user_ip_address"] = $row["lease_ip_address"];
				$log_query_result[ $key ]["user_mac_address"] = $row["hardware_ethernet"];
				$log_query_result[ $key ]["user_host_name"] = $row["client_host_name"];
			}
			mysql_free_result( $result );
			return $log_query_result;
		}

		public function query_dhcp_lease_log_ip( $user_ip_address ) {
			$log_query_result = array();
			$counter = 0;

			$query_string = "SELECT * from lease WHERE lease_ip_address='" . $user_ip_address . "' ORDER BY starts_date DESC, starts_time DESC LIMIT 1";
			$result = mysql_query( $query_string , $this->connection_id );
			if( !$result ) return $log_query_result;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $log_query_result;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = "";
				$key = $row["lease_ip_address"];

				$log_query_result[ $key ]["user_ip_address"] = $row["lease_ip_address"];
				$log_query_result[ $key ]["user_mac_address"] = $row["hardware_ethernet"];
				$log_query_result[ $key ]["user_host_name"] = $row["client_host_name"];
			}
			mysql_free_result( $result );
			return $log_query_result;
		}

		public function query_dhcp_host_log_mac( $user_mac_address ) {
			$log_query_result = array();
			$counter = 0;

			$query_string = "SELECT * from host WHERE hardware_ethernet='" . $user_mac_address . "' AND ( status='active' OR status='error' ) ";
			$result = mysql_query( $query_string , $this->connection_id );
			if( !$result ) return $log_query_result;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $log_query_result;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = "";
				$key = $row["hardware_ethernet"];

				$log_query_result[ $key ]["user_ip_address"] = $row["fixed_address"];
				$log_query_result[ $key ]["user_mac_address"] = $row["hardware_ethernet"];
				$log_query_result[ $key ]["user_host_name"] = $row["host"];
			}
			mysql_free_result( $result );
			return $log_query_result;
		}

		public function query_dhcp_host_log_ip( $user_ip_address ) {
			$log_query_result = array();
			$counter = 0;

			$query_string = "SELECT * from host WHERE fixed_address='" . $user_ip_address . "' AND ( status='active' OR status='error' ) ";
			$result = mysql_query( $query_string , $this->connection_id );
			if( !$result ) return $log_query_result;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $log_query_result;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = "";
				$key = $row["fixed_address"];

				$log_query_result[ $key ]["user_ip_address"] = $row["fixed_address"];
				$log_query_result[ $key ]["user_mac_address"] = $row["hardware_ethernet"];
				$log_query_result[ $key ]["user_host_name"] = $row["host"];
			}
			mysql_free_result( $result );
			return $log_query_result;
		}

		public function query_switch_connected_port_mac( $user_mac_address ) {
			$log_query_result = array();
			$counter = 0;

			$query_string = "SELECT * from ip_mac WHERE mac_address='" . $user_mac_address . "'";
			$result = mysql_query( $query_string , $this->connection_id );
			if( !$result ) return $log_query_result;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $log_query_result;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = "";
				$key = $row["mac_address"];

				$log_query_result[ $key ]["switch_ip_address"] = $row["ip"];
				$log_query_result[ $key ]["switch_name"] = $row["name"];
				$log_query_result[ $key ]["switch_port_vlan"] = $row["vlan"];
				$log_query_result[ $key ]["switch_port"] = $row["interface"];
				$log_query_result[ $key ]["user_mac_address"] = $row["mac_address"];
				$log_query_result[ $key ]["user_ip_address"] = $row["ip_address"];
			}
			mysql_free_result( $result );
			return $log_query_result;
		}

		public function query_switch_connected_port_ip( $user_ip_address ) {
			$log_query_result = array();
			$counter = 0;

			$query_string = "SELECT * from ip_mac WHERE ip_address='" . $user_ip_address . "'";
			$result = mysql_query( $query_string , $this->connection_id );
			if( !$result ) return $log_query_result;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $log_query_result;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = "";
				$key = $row["ip_address"];

				$log_query_result[ $key ]["switch_ip_address"] = $row["ip"];
				$log_query_result[ $key ]["switch_name"] = $row["name"];
				$log_query_result[ $key ]["switch_port_vlan"] = $row["vlan"];
				$log_query_result[ $key ]["switch_port"] = $row["interface"];
				$log_query_result[ $key ]["user_mac_address"] = $row["mac_address"];
				$log_query_result[ $key ]["user_ip_address"] = $row["ip_address"];
			}
			mysql_free_result( $result );
			return $log_query_result;
		}


		// Switch Funtions
		public function query_switch_info( $sw_ip ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from switch_info WHERE ip_address='" . $sw_ip . "'";
			//print $query_str;
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$row = mysql_fetch_assoc( $result );
			$return_array["host_name"] = $row["host_name"];
			$return_array["description"] = $row["description"];
			$return_array["interface_count"] = $row["interface_count"];
			$return_array["default_gateway"] = $row["default_gateway"];
			$return_array["image_name"] = $row["image_name"];
			$return_array["up_time"] = $row["up_time"];
			$return_array["contact"] = $row["contact"];
			$return_array["location"] = $row["location"];
			$return_array["last_change"] = $row["last_change"];

			mysql_free_result( $result );
			return $return_array;
		}

		public function query_switch_info_all() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * FROM switch_info ORDER BY long_ip_address";
			//print $query_str;
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["ip_address"];
				$return_array[$key]["host_name"] = $row["host_name"];
				$return_array[$key]["description"] = $row["description"];
				$return_array[$key]["interface_count"] = $row["interface_count"];
				$return_array[$key]["default_gateway"] = $row["default_gateway"];
				$return_array[$key]["image_name"] = $row["image_name"];
				$return_array[$key]["up_time"] = $row["up_time"];
				$return_array[$key]["contact"] = $row["contact"];
				$return_array[$key]["location"] = $row["location"];
				$return_array[$key]["last_change"] = $row["last_change"];
			}

			mysql_free_result( $result );
			return $return_array;
		}

		public function query_switch_interface_list( $sw_ip ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from interfaces WHERE ip='" . $sw_ip . "'";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key_x = $row["name"];
				$key_y = $row["interface"];
				$return_array[$key_x][$key_y]["vlan"] = $row["vlan"];
				$return_array[$key_x][$key_y]["trunk"] = ( $row["trunk"] == 1 ) ? "yes" : "no";
				$return_array[$key_x][$key_y]["admin_status"] = ( $row["admin_status"] == 1 ) ? "up":"down";
				$return_array[$key_x][$key_y]["oper_status"] = ( $row["oper_status"] == 1 ) ? "up":"down";
				$return_array[$key_x][$key_y]["additional_oper_status"] = $row["additional_oper_status"];
			}

			mysql_free_result( $result );
			return $return_array;
		}

		public function query_switch_interface_list_all() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from interfaces";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["ip"];
				$key_x = $row["name"];
				$key_y = $row["interface"];
				$return_array[$key][$key_x][$key_y]["vlan"] = $row["vlan"];
				$return_array[$key][$key_x][$key_y]["trunk"] = ( $row["trunk"] == 1 ) ? "yes" : "no";
				$return_array[$key][$key_x][$key_y]["admin_status"] = ( $row["admin_status"] == 1 ) ? "up":"down";
				$return_array[$key][$key_x][$key_y]["oper_status"] = ( $row["oper_status"] == 1 ) ? "up":"down";
				$return_array[$key][$key_x][$key_y]["additional_oper_status"] = $row["additional_oper_status"];
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_switch_cdp_list( $sw_ip ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from cdp WHERE ip='" . $sw_ip . "'";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["name"];
				$key_x = $row["interface"];
				$return_array[$key][$key_x]["remote_ip"] = $row["remote_ip"];
				$return_array[$key][$key_x]["remote_name"] = $row["remote_name"];
				$return_array[$key][$key_x]["remote_interface"] = $row["remote_interface"];
				$return_array[$key][$key_x]["remote_platform"] = $row["remote_platform"];
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_switch_cdp_list_all() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from cdp";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["ip"];
				$key_x = $row["name"];
				$key_y = $row["interface"];
				$return_array[$key][$key_x][$key_y]["remote_ip"] = $row["remote_ip"];
				$return_array[$key][$key_x][$key_y]["remote_name"] = $row["remote_name"];
				$return_array[$key][$key_x][$key_y]["remote_interface"] = $row["remote_interface"];
				$return_array[$key][$key_x][$key_y]["remote_platform"] = $row["remote_platform"];
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_switch_ip_mac_list( $sw_ip ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from ip_mac WHERE ip='" . $sw_ip . "'";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["name"];
				$key_x = $row["interface"];
				$key_y = $row["mac_address"];
				$return_array[$key][$key_x][$key_y]["vlan"] = $row["vlan"];
				$return_array[$key][$key_x][$key_y]["ip_address"] = $row["ip_address"];
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_switch_ip_mac_list_all() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from ip_mac";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["ip"];
				$key_x = $row["name"];
				$key_y = $row["interface"];
				$key_z = $row["mac_address"];
				$return_array[$key][$key_x][$key_y][$key_z]["vlan"] = $row["vlan"];
				$return_array[$key][$key_x][$key_y][$key_z]["ip_address"] = $row["ip_address"];
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_switch_list() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from devices WHERE type=1 AND status=1";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["ip"];
				$return_array[$key]["name"] = $row["name"];
				$return_array[$key]["status"] = $row["status"];
			}
			mysql_free_result( $result );
			return $return_array;
		}


		// AP Functions
		public function query_ap_info( $ap_ip ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from ap_info WHERE ip_address='" . $ap_ip . "'";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$row = mysql_fetch_assoc( $result );
			$return_array["host_name"] = $row["host_name"];
			$return_array["description"] = $row["description"];
			$return_array["interface_count"] = $row["interface_count"];
			$return_array["default_gateway"] = $row["default_gateway"];
			$return_array["image_name"] = $row["image_name"];
			$return_array["up_time"] = $row["up_time"];
			$return_array["contact"] = $row["contact"];
			$return_array["location"] = $row["location"];
			$return_array["last_change"] = $row["last_change"];

			mysql_free_result( $result );
			return $return_array;
		}

		public function query_ap_info_all() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from ap_info order by long_ip_address";
			//print $query_str;
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["ip_address"];
				$return_array[$key]["host_name"] = $row["host_name"];
				$return_array[$key]["description"] = $row["description"];
				$return_array[$key]["interface_count"] = $row["interface_count"];
				$return_array[$key]["default_gateway"] = $row["default_gateway"];
				$return_array[$key]["image_name"] = $row["image_name"];
				$return_array[$key]["up_time"] = $row["up_time"];
				$return_array[$key]["contact"] = $row["contact"];
				$return_array[$key]["location"] = $row["location"];
				$return_array[$key]["last_change"] = $row["last_change"];
			}

			mysql_free_result( $result );
			return $return_array;
		}

		public function query_ap_list() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * FROM devices WHERE type=2 AND status=1";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["ip"];
				$return_array[$key]["name"] = $row["name"];
				$return_array[$key]["status"] = $row["status"];
			}
			mysql_free_result( $result );
			return $return_array;
		}


		// eduroam functions
		public function query_eduroam_accept( $filter , $limit ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * FROM accept WHERE user_name like '%" . $filter . "%' OR user_mac_address like '%" . $filter . "%' ORDER BY auth_date DESC LIMIT " . $limit;
			$result = mysql_query( $query_str , $this->connection_id );
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$return_array[$key]["auth_date"] = $row["auth_date"];
				$return_array[$key]["ap_ip_address"] = $row["ap_ip_address"];
				$return_array[$key]["user_name"] = $row["user_name"];
				$return_array[$key]["user_mac_address"] = $row["user_mac_address"];
				$return_array[$key]["user_ip_address"] = $row["dhcp_ip_address"];
				$key ++;
			}
			mysql_free_result( $result );
			return $return_array;

		}

		public function query_eduroam_accept_all( $limit ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from accept ORDER BY auth_date DESC LIMIT " . $limit;
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$return_array[$key]["auth_date"] = $row["auth_date"];
				$return_array[$key]["ap_ip_address"] = $row["ap_ip_address"];
				$return_array[$key]["user_name"] = $row["user_name"];
				$return_array[$key]["user_mac_address"] = $row["user_mac_address"];
				$return_array[$key]["user_ip_address"] = $row["dhcp_ip_address"];
				$key ++;
			}
			mysql_free_result( $result );
			return $return_array;

		}

		public function query_eduroam_reject( $filter , $limit ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from reject WHERE user_name like '" . $filter . "%' OR user_mac_address like '" . $filter . "%' ORDER BY auth_date DESC LIMIT " . $limit;
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$return_array[$key]["auth_date"] = $row["auth_date"];
				$return_array[$key]["ap_ip_address"] = $row["ap_ip_address"];
				$return_array[$key]["user_name"] = $row["user_name"];
				$return_array[$key]["user_mac_address"] = $row["user_mac_address"];
				$return_array[$key]["reject_reason"] = $row["reject_reason"];
				$key ++;
			}
			mysql_free_result( $result );
			return $return_array;

		}

		public function query_eduroam_reject_all( $limit ) {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from reject ORDER BY auth_date DESC LIMIT " . $limit;
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$return_array[$key]["auth_date"] = $row["auth_date"];
				$return_array[$key]["ap_ip_address"] = $row["ap_ip_address"];
				$return_array[$key]["user_name"] = $row["user_name"];
				$return_array[$key]["user_mac_address"] = $row["user_mac_address"];
				$return_array[$key]["reject_reason"] = $row["reject_reason"];
				$key ++;
			}
			mysql_free_result( $result );
			return $return_array;

		}

		public function query_eduroam_reject_ldap_error() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from reject WHERE reject_reason like '%ldap%' ORDER BY auth_date DESC LIMIT 1000";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$return_array[$key]["auth_date"] = $row["auth_date"];
				$return_array[$key]["ap_ip_address"] = $row["ap_ip_address"];
				$return_array[$key]["user_name"] = $row["user_name"];
				$return_array[$key]["user_mac_address"] = $row["user_mac_address"];
				$key ++;
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_eduroam_reject_certificate_error() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from reject WHERE reject_reason like 'TLS%' ORDER BY auth_date DESC LIMIT 1000";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$return_array[$key]["auth_date"] = $row["auth_date"];
				$return_array[$key]["ap_ip_address"] = $row["ap_ip_address"];
				$return_array[$key]["user_name"] = $row["user_name"];
				$return_array[$key]["user_mac_address"] = $row["user_mac_address"];
				$key ++;
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_eduroam_reject_domain_error() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from reject WHERE user_name NOT LIKE '%@iyte.edu.tr' AND user_name NOT LIKE '%@std.iyte.edu.tr' AND user_name NOT LIKE '%@guest.iyte.edu.tr' ORDER BY auth_date DESC LIMIT 1000";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$return_array[$key]["auth_date"] = $row["auth_date"];
				$return_array[$key]["ap_ip_address"] = $row["ap_ip_address"];
				$return_array[$key]["user_name"] = $row["user_name"];
				$return_array[$key]["user_mac_address"] = $row["user_mac_address"];
				$key ++;
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_eduroam_reject_unspecified_error() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from reject WHERE reject_reason='' ORDER BY auth_date DESC LIMIT 1000";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$return_array[$key]["auth_date"] = $row["auth_date"];
				$return_array[$key]["ap_ip_address"] = $row["ap_ip_address"];
				$return_array[$key]["user_name"] = $row["user_name"];
				$return_array[$key]["user_mac_address"] = $row["user_mac_address"];
				$key ++;
			}
			mysql_free_result( $result );
			return $return_array;
		}


		// Dhcp Lease Functions
		public function query_dhcp_conf() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from conf";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["parameter"];
				$value = $row["value"];
				$return_array[$key] = $value;
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_dhcp_host() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from host";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["host"];
				$return_array[$key]["subnet"] = $row["subnet"];
				$return_array[$key]["subnet_name"] = $row["subnet_name"];
				$return_array[$key]["fixed_address"] = $row["fixed_address"];
				$return_array[$key]["hardware_ethernet"] = $row["hardware_ethernet"];
				$return_array[$key]["status"] = $row["status"];
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_dhcp_network() {
			$query_str = "";
			$return_array = array();

			$query_str = "SELECT * from subnet";
			$result = mysql_query( $query_str , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["subnet"];
				$return_array[$key]["netmask"] = $row["netmask"];
				$return_array[$key]["network"] = $row["network"];
				$return_array[$key]["range_start"] = $row["range_start"];
				$return_array[$key]["range_end"] = $row["range_end"];
				$return_array[$key]["ddns_updates"] = $row["ddns_updates"];
				$return_array[$key]["ddns_domain_name"] = $row["ddns_domain_name"];
				$return_array[$key]["option_domain_name_servers"] = $row["option_domain_name_servers"];
				$return_array[$key]["option_routers"] = $row["option_routers"];
				$return_array[$key]["option_broadcast_address"] = $row["option_broadcast_address"];
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_dhcp_lease( $query_string ) {
			$return_array = array();

			$result = mysql_query( $query_string , $this->connection_id );
			if( !$result ) return $return_array;
			if( mysql_affected_rows( $this->connection_id ) == 0 ) return $return_array;

			$key = 0;
			while( $row = mysql_fetch_assoc( $result ) ) {
				$key = $row["lease_ip_address"];
				$key_x = $row["lease_index"];
				$return_array[$key][$key_x]["starts_date"] = $row["starts_date"];
				$return_array[$key][$key_x]["starts_time"] = $row["starts_time"];
				$return_array[$key][$key_x]["ends_date"] = $row["ends_date"];
				$return_array[$key][$key_x]["ends_time"] = $row["ends_time"];
				$return_array[$key][$key_x]["tstp_date"] = $row["tstp_date"];
				$return_array[$key][$key_x]["tstp_time"] = $row["tstp_time"];
				$return_array[$key][$key_x]["binding_state"] = $row["binding_state"];
				$return_array[$key][$key_x]["next_binding_state"] = $row["next_binding_state"];
				$return_array[$key][$key_x]["hardware_ethernet"] = $row["hardware_ethernet"];
				$return_array[$key][$key_x]["uid"] = $row["uid"];
				$return_array[$key][$key_x]["user_host_name"] = $row["client_host_name"];
				$return_array[$key][$key_x]["ddns_txt"] = $row["ddns_txt"];
				$return_array[$key][$key_x]["ddns_fwd_name"] = $row["ddns_fwd_name"];
			}
			mysql_free_result( $result );
			return $return_array;
		}

		public function query_dhcp_lease_ip( $ip ) {
			$query_str = "SELECT * FROM lease WHERE lease_ip_address='" . $ip . "' ORDER BY starts_date,starts_time DESC";
			return $this->query_dhcp_lease( $query_str );
		}

		public function query_dhcp_lease_mac( $mac ) {
			$query_str = "SELECT * FROM lease WHERE hardware_ethernet='" . $mac . "' ORDER BY starts_date,starts_time DESC";
			return $this->query_dhcp_lease( $query_str );
		}

		public function query_dhcp_lease_all( $limit ) {
			$query_str = "SELECT * FROM lease ORDER BY starts_date,starts_time DESC LIMIT " . $limit;
			return $this->query_dhcp_lease( $query_str );
		}

	}
?>