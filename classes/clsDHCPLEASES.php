<?php

	class clsDHCPLEASES {
		private $lease_table;
		private $mysql_table;
		private $mysql_conf;
		private $mysql_host;
		private $mysql_subnet;
		private $mysql_networks;
		private $conf;
		private $file_info;

		private $dhcpd_lease_file;
		private $dhcpd_conf_file;
		private $loop_stop_file_name;

		private $sql_host;
		private $sql_user;
		private $sql_password;
		private $sql_database;

		private $sql_table_lease;
		private $sql_table_conf;
		private $sql_table_host;
		private $sql_table_subnet;
		private $sql_table_networks;

		private $sql_connection_id;
		private $sql_select_base;
		private $sql_select_base_conf;
		private $sql_select_base_host;
		private $sql_select_base_subnet;
		private $sql_update_base;
		private $sql_update_base_conf;
		private $sql_update_base_host;
		private $sql_update_base_subnet;
		private $sql_insert_base;
		private $sql_insert_base_conf;
		private $sql_insert_base_host;
		private $sql_insert_base_subnet;
		private $sql_delete_base;
		private $sql_delete_base_conf;
		private $sql_delete_base_host;
		private $sql_delete_base_subnet;

		private $log_file;
		private $log_handle;
		private $error_message;
		private $stop;
		private $sleep_time;

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		public function __construct() {
			$this->lease_table = array();
			$this->mysql_table = array();
			$this->file_info = array();

			$this->conf = array();
			$this->conf_host = array();
			$this->conf_subnet = array();

			$this->mysql_conf = array();
			$this->mysql_host = array();
			$this->mysql_subnet = array();
			$this->mysql_networks = array();

			$this->dhcpd_lease_file = "/iyte/run/dhcpd/dhcpd.leases";
			$this->dhcpd_conf_file = "/iyte/run/dhcpd/dhcpd.conf";
			$this->loop_stop_file_name = "/iyte/loop/stop.stp";

			$this->sql_host = "mysql sunucu adresi";
			$this->sql_user = "mysql kullanýcý adý";
			$this->sql_password = "mysql kullanýcý þifresi";
			$this->sql_database = "dhcp";
			$this->sql_table_lease = "lease";
			$this->sql_table_conf = "conf";
			$this->sql_table_host = "host";
			$this->sql_table_subnet = "subnet";
			$this->sql_table_networks = "networks";

			$this->log_file = "/iyte/log/dhcpd/leases-" . date( "Y-m-d-H" ) . ".log";
			$this->error_message = "";
			$this->stop = false;
			$this->sleep_time = 10;

			$this->log_handle = fopen( $this->log_file , "a+" );
			$this->write_log_header();

			// SELECT BASE
			$this->sql_select_base = "SELECT * FROM " . $this->sql_table_lease . " WHERE lease_ip_address='%s' AND lease_index=%d";
			$this->sql_select_base_conf = "SELECT * FROM " . $this->sql_table_conf . " WHERE parameter='%s'";
			$this->sql_select_base_host = "SELECT * FROM " . $this->sql_table_host . " WHERE host='%s' AND subnet='%s'";
			$this->sql_select_base_subnet = "SELECT * FROM " . $this->sql_table_subnet . " WHERE subnet='%s'";

			// UPDATE BASE
			$this->sql_update_base = "UPDATE " . $this->sql_table_lease . " SET ";
			$this->sql_update_base .= "starts_date='%s' , "; // starts_date
			$this->sql_update_base .= "starts_time='%s' , "; // starts_time
			$this->sql_update_base .= "ends_date='%s' , "; // ends_date
			$this->sql_update_base .= "ends_time='%s' , "; // ends_time
			$this->sql_update_base .= "tstp_date='%s' , "; // tstp_date
			$this->sql_insert_base .= "tstp_time='%s' , "; // tstp_time
			$this->sql_update_base .= "binding_state=%d , "; // binding_state
			$this->sql_update_base .= "next_binding_state=%d , "; // next_binding_state
			$this->sql_update_base .= "hardware_ethernet='%s' , "; // hardware_ethernet
			$this->sql_update_base .= "uid='%s' , "; // uid
			$this->sql_update_base .= "client_host_name='%s' , "; // client_host_name
			$this->sql_update_base .= "ddns_txt='%s' , "; // ddns_txt
			$this->sql_update_base .= "ddns_fwd_name='%s'"; // ddns_fwd_name
			$this->sql_update_base .= " WHERE lease_ip_address='%s' AND lease_index=%d"; //

			$this->sql_update_base_conf = "UPDATE " . $this->sql_table_conf . " SET ";
			$this->sql_update_base_conf .= "value='%s' WHERE parameter='%s'"; //

			$this->sql_update_base_host = "UPDATE " . $this->sql_table_host . " SET ";
			$this->sql_update_base_host .= "subnet_name='%s' , ";
			$this->sql_update_base_host .= "fixed_address='%s' , ";
			$this->sql_update_base_host .= "hardware_ethernet='%s' , ";
			$this->sql_update_base_host .= "status='%s' ";
			$this->sql_update_base_host .= " WHERE host='%s' AND subnet='%s'";

			$this->sql_update_base_subnet = "UPDATE " . $this->sql_table_subnet . " SET ";
			$this->sql_update_base_subnet .= "netmask='%s' , ";
			$this->sql_update_base_subnet .= "network='%s' , ";
			$this->sql_update_base_subnet .= "range_start='%s' , ";
			$this->sql_update_base_subnet .= "range_end='%s' , ";
			$this->sql_update_base_subnet .= "ddns_updates='%s' , ";
			$this->sql_update_base_subnet .= "ddns_domain_name='%s' , ";
			$this->sql_update_base_subnet .= "option_domain_name_servers='%s' , ";
			$this->sql_update_base_subnet .= "option_routers='%s' , ";
			$this->sql_update_base_subnet .= "option_broadcast_address='%s' ";
			$this->sql_update_base_subnet .= " WHERE subnet='%s'";

			//INSERT BASE
			$this->sql_insert_base = "INSERT INTO " . $this->sql_table_lease . " VALUES ( ";
			$this->sql_insert_base .= "'%s' , "; // lease_ip_address
			$this->sql_insert_base .= "%d , "; // lease_index
			$this->sql_insert_base .= "'%s' , "; // starts_date
			$this->sql_insert_base .= "'%s' , "; // starts_time
			$this->sql_insert_base .= "'%s' , "; // ends_date
			$this->sql_insert_base .= "'%s' , "; // ends_time
			$this->sql_insert_base .= "'%s' , "; // tstp_date
			$this->sql_insert_base .= "'%s' , "; // tstp_time
			$this->sql_insert_base .= "%d , "; // binding_state
			$this->sql_insert_base .= "%d , "; // next_binding_state
			$this->sql_insert_base .= "'%s' , "; // hardware_ethernet
			$this->sql_insert_base .= "'%s' , "; // uid
			$this->sql_insert_base .= "'%s' , "; // client_host_name
			$this->sql_insert_base .= "'%s' , "; // ddns_txt
			$this->sql_insert_base .= "'%s'"; // ddns_fwd_name
			$this->sql_insert_base .= " )"; //

			$this->sql_insert_base_conf = "INSERT INTO " . $this->sql_table_conf . " VALUES ( ";
			$this->sql_insert_base_conf .= "'%s',";
			$this->sql_insert_base_conf .= "'%s' )";

			$this->sql_insert_base_host = "INSERT INTO " . $this->sql_table_host . " VALUES ( ";
			$this->sql_insert_base_host .= "'%s',";
			$this->sql_insert_base_host .= "'%s',";
			$this->sql_insert_base_host .= "'%s',";
			$this->sql_insert_base_host .= "'%s',";
			$this->sql_insert_base_host .= "'%s',";
			$this->sql_insert_base_host .= "'%s' )";

			$this->sql_insert_base_subnet = "INSERT INTO " . $this->sql_table_subnet . " VALUES ( ";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s',";
			$this->sql_insert_base_subnet .= "'%s' )";

			// DELETE BASE
			$this->sql_delete_base = "DELETE FROM " . $this->sql_table_lease . " WHERE lease_ip_address='%s' AND lease_index=%d";
			$this->sql_delete_base_conf = "DELETE FROM " . $this->sql_table_conf . " WHERE parameter='%s'";
			$this->sql_delete_base_host = "DELETE FROM " . $this->sql_table_host . " WHERE host='%s' AND subnet='%s'";
			$this->sql_delete_base_subnet = "DELETE FROM " . $this->sql_table_subnet . " WHERE subnet='%s'";

		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		public function __destruct() {
			$this->log_close();
			if( $this->sql_connection_id ) mysql_close( $this->sql_connection_id );
			$this->sql_connection_id = null;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function re_init_log_file() {
			$this->log_close();
			$this->log_file = "/iyte/log/dhcpd/leases-" . date( "Y-m-d-H" ) . ".log";
			$this->log_handle = fopen( $this->log_file , "a+" );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function reset_arrays() {
			$this->conf = array();
			$this->lease_table = array();
			$this->lease_lines = array();
			$this->mysql_conf = array();
			$this->mysql_host = array();
			$this->mysql_table = array();
			$this->mysql_networks = array();
			$this->mysql_subnet = array();
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function get_file_info( $this_file , $this_file_name ) {
			$this->file_info[ $this_file ]["name"] = $this_file_name;
			$this->file_info[ $this_file ]["last_accessed"] = 0;
			$this->file_info[ $this_file ]["accessed"] = 0;
			$this->file_info[ $this_file ]["last_changed"] = 0;
			$this->file_info[ $this_file ]["changed"] = 0;
			$this->file_info[ $this_file ]["last_modified"] = 0;
			$this->file_info[ $this_file ]["modified"] = 0;
			$this->file_info[ $this_file ]["last_size"] = 0;
			$this->file_info[ $this_file ]["size"] = 0;
			$this->file_info[ $this_file ]["last_read_pos"] = 0;
			$this->file_info[ $this_file ]["exists"] = ( file_exists( $this_file_name ) ) ? true : false;

			if( $this->file_info[ $this_file ]["exists"] ) {
				$this->file_info[ $this_file ]["last_accessed"] = $this->file_info[ $this_file ]["accessed"] = fileatime( $this_file_name );
				$this->file_info[ $this_file ]["last_changed"] = $this->file_info[ $this_file ]["changed"] = filectime( $this_file_name );
				$this->file_info[ $this_file ]["last_modified" ] = $this->file_info[ $this_file ]["modified"] = filemtime( $this_file_name );
				$this->file_info[ $this_file ]["last_size"] = $this->file_info[ $this_file ]["size"] = filesize( $this_file_name );
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function update_file_info( $this_file ) {
			$fname = $this->file_info[ $this_file ]["name"];
			$this->file_info[ $this_file ]["accessed"] = fileatime( $fname );
			$this->file_info[ $this_file ]["changed"] = filectime( $fname );
			$this->file_info[ $this_file ]["modified"] = filemtime( $fname );
			$this->file_info[ $this_file ]["size"] = filesize( $fname );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function reload_me( $this_file ) {
			$this->update_file_info( $this_file );
			if( $this->file_info[ $this_file ]["modified"] != $this->file_info[ $this_file ]["last_modified"] ) {
				$this->file_info[ $this_file ]["last_accessed"] = $this->file_info[ $this_file ]["accessed"];
				$this->file_info[ $this_file ]["last_changed"] = $this->file_info[ $this_file ]["changed"];
				$this->file_info[ $this_file ]["last_modified"] = $this->file_info[ $this_file ]["modified"];
				$this->file_info[ $this_file ]["last_size"] = $this->file_info[ $this_file ]["size"];

				return true;
			}

			return false;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function read_lease_file() {
			$read_size = $this->file_info[ "lease" ]["size"] - $this->file_info[ "lease" ]["last_read_pos"];
			if( $read_size == 0 ) {
				$this->error_message = "read size for the file < " . $this->dhcpd_lease_file . " > is 0. No read done !!!";
				return null;
			}

			$handle = @fopen( $this->file_info[ "lease" ]["name"] , "r" );
			if( !$handle ) {
				$this->error_message = "could not read the file < " . $this->dhcpd_lease_file . " >. please check the file exists and in the path you specified !!!";
				return null;
			}
			fseek( $handle , $this->file_info[ "lease" ]["last_read_pos"] );
			$content = @fread( $handle , $read_size );
			fclose( $handle );
			$this->file_info[ "lease" ]["last_read_pos"] = $this->file_info[ "lease" ]["last_size"];

			return $content;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function stop_me() {
			$stop_content = @file_get_contents( $this->loop_stop_file_name );
			if( !$stop_content ) return true;
			if( $stop_content[0] == "1" ) return true;

			return false;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function get_used_memory() {
			$out_str = "used memory => ";
			$out_str .= ( (int)( memory_get_usage() / ( 1024 * 1024 ) ) );
			$out_str .= " MByte(s)";

			return $out_str;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function reset_array_item( &$this_array ) {
			$this_array["starts_date"] = "";
			$this_array["starts_time"] = "";
			$this_array["ends_date"] = "";
			$this_array["ends_time"] = "";
			$this_array["tstp_date"] = "";
			$this_array["tstp_time"] = "";
			$this_array["binding_state"] = "";
			$this_array["next_binding_state"] = "";
			$this_array["hardware_ethernet"] = "";
			$this_array["uid"] = "";
			$this_array["client_host_name"] = "";
			$this_array["ddns_txt"] = "";
			$this_array["ddns_fwd_name"] = "";
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function clear_value( $from_this ) {
			$str = trim( $from_this );
			$str = str_replace( "\"" , "" , $str );
			$str = str_replace( "\\" , "/" , $str );
			$str = str_replace( ";" , "" , $str );
			$str = str_replace( "\t" , "" , $str );
			$str = str_replace( "'" , "?" , $str );

			return $str;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function reformat_hardware_ethernet( $hw_eth ) {
			$arr = explode( ":" , $hw_eth , 6 );
			$out = $arr[0].$arr[1] . "." . $arr[2].$arr[3] . "." . $arr[4].$arr[5];
			return $out;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function equals( $arr_1 , $arr_2 ) {
			if( count( $arr_1 ) != count( $arr_2 ) ) return false;

			foreach( $arr_1 as $key => $value ) {
				$ret = ( $value == $arr_2[ $key ] ) ? true : false;
				if (!$ret ) return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function assign( &$arr_1 , $arr_2 ) {
			foreach( $arr_2 as $key => $value ) {
				$arr_1[ $key ] = $arr_2[ $key ];
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function open_database_connection() {
			$this->sql_connection_id = null;
			$this->sql_connection_id = mysql_connect( $this->sql_host , $this->sql_user , $this->sql_password );
			if( $this->sql_connection_id ) {
				if( mysql_select_db( $this->sql_database ) )
					return true;
				else {
					$this->log_write( "database can not be selected : " . mysql_error() );
					$this->close_database_connection();
					return false;
				}
			}
			else $this->log_write( "database connection could not be made !!!" );

			return false;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function close_database_connection() {
			if( $this->sql_connection_id != null )
				mysql_close( $this->sql_connection_id );
			$this->sql_connection_id = null;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function write_log_header() {
			if( $this->log_handle ) {
				fputs( $this->log_handle , "--------------------------------------------------------------------------------\r\n" );
				fputs( $this->log_handle , "------------------------------LOG STARTED---------------------------------------\r\n" );
				fputs( $this->log_handle , "--------------------------------------------------------------------------------\r\n" );
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function log_write( $this_log_str ) {
			if( $this->log_handle ) {
				fputs( $this->log_handle , $this_log_str . "\r\n" );
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function log_close() {
			if( $this->log_handle ) {
				fputs( $this->log_handle , "--------------------------------------------------------------------------------\r\n" );
				fputs( $this->log_handle , "------------------------------LOG STOPPED---------------------------------------\r\n" );
				fputs( $this->log_handle , "--------------------------------------------------------------------------------\r\n" );
				fclose( $this->log_handle );
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		public function dump() {
			print "lease fields \r\n";
			print_r( $this->lease_table );
			print "\r\nlease fields - DONE \r\n";
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function read_from_mysql_lease() {
			if( !$this->sql_connection_id ) return false;

			$sql_string = "SELECT * FROM " . $this->sql_table_lease;
			$select_result = mysql_query( $sql_string , $this->sql_connection_id );
			if( !$select_result ) return false;

			while ($row = mysql_fetch_assoc( $select_result ) ) {
				$lease_ip = $row["lease_ip_address"];
				$lease_index = $row["lease_index"];
				$this->mysql_table[$lease_ip][$lease_index]["starts_date"] = $row["starts_date"];
				$this->mysql_table[$lease_ip][$lease_index]["starts_time"] = $row["starts_time"];
				$this->mysql_table[$lease_ip][$lease_index]["ends_date"] = $row["ends_date"];
				$this->mysql_table[$lease_ip][$lease_index]["ends_time"] = $row["ends_time"];
				$this->mysql_table[$lease_ip][$lease_index]["tstp_date"] = $row["tstp_date"];
				$this->mysql_table[$lease_ip][$lease_index]["tstp_time"] = $row["tstp_time"];
				$this->mysql_table[$lease_ip][$lease_index]["binding_state"] = $row["binding_state"];
				$this->mysql_table[$lease_ip][$lease_index]["next_binding_state"] = $row["next_binding_state"];
				$this->mysql_table[$lease_ip][$lease_index]["hardware_ethernet"] = $row["hardware_ethernet"];
				$this->mysql_table[$lease_ip][$lease_index]["uid"] = $row["uid"];
				$this->mysql_table[$lease_ip][$lease_index]["client_host_name"] = $row["client_host_name"];
				$this->mysql_table[$lease_ip][$lease_index]["ddns_txt"] = $row["ddns_txt"];
				$this->mysql_table[$lease_ip][$lease_index]["ddns_fwd_name"] = $row["ddns_fwd_name"];
			}
			mysql_free_result( $select_result );

			$this->log_write( "mysql table count : " . count( $this->mysql_table ) );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function read_from_mysql_conf() {
			if( !$this->sql_connection_id ) return false;

			$sql_string = "SELECT * FROM " . $this->sql_table_conf;
			$select_result = mysql_query( $sql_string , $this->sql_connection_id );
			if( !$select_result ) return false;

			while ($row = mysql_fetch_assoc( $select_result ) ) {
				$this->mysql_conf[ $row["parameter"] ] = $row["value"];
			}
			mysql_free_result( $select_result );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function read_from_mysql_host() {
			if( !$this->sql_connection_id ) return false;

			$sql_string = "SELECT * FROM " . $this->sql_table_host;
			$select_result = mysql_query( $sql_string , $this->sql_connection_id );
			if( !$select_result ) return false;

			while ($row = mysql_fetch_assoc( $select_result ) ) {
				$host = $row["host"];
				$this->mysql_host[$host] = array();
				$this->mysql_host[$host]["subnet"] = $row["subnet"];
				$this->mysql_host[$host]["subnet_name"] = $row["subnet_name"];
				$this->mysql_host[$host]["fixed_address"] = $row["fixed_address"];
				$this->mysql_host[$host]["hardware_ethernet"] = $row["hardware_ethernet"];
				$this->mysql_host[$host]["status"] = $row["status"];
			}
			mysql_free_result( $select_result );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function read_from_mysql_subnet() {
			if( !$this->sql_connection_id ) return false;

			$sql_string = "SELECT * FROM " . $this->sql_table_subnet;
			$select_result = mysql_query( $sql_string , $this->sql_connection_id );
			if( !$select_result ) return false;

			while ($row = mysql_fetch_assoc( $select_result ) ) {
				$subnet = $row["subnet"];
				$this->mysql_subnet[$subnet] = array();
				$this->mysql_subnet[$subnet]["netmask"] = $row["netmask"];
				$this->mysql_subnet[$subnet]["network"] = $row["network"];
				$this->mysql_subnet[$subnet]["range_start"] = $row["range_start"];
				$this->mysql_subnet[$subnet]["range_end"] = $row["range_end"];
				$this->mysql_subnet[$subnet]["ddns_updates"] = $row["ddns_updates"];
				$this->mysql_subnet[$subnet]["ddns_domain_name"] = $row["ddns_domain_name"];
				$this->mysql_subnet[$subnet]["option_domain_name_servers"] = $row["option_domain_name_servers"];
				$this->mysql_subnet[$subnet]["option_routers"] = $row["option_routers"];
				$this->mysql_subnet[$subnet]["option_broadcast_address"] = $row["option_broadcast_address"];
			}
			mysql_free_result( $select_result );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function read_from_mysql_networks() {
			$this->mysql_networks = array();
			if( !$this->sql_connection_id ) return false;

			$sql_string = "SELECT * FROM " . $this->sql_table_networks;
			$select_result = mysql_query( $sql_string , $this->sql_connection_id );
			if( !$select_result ) return false;

			while ($row = mysql_fetch_assoc( $select_result ) ) {
				$this->mysql_networks[ $row["network"] ]["network_id"] = $row["network_id"];
				$this->mysql_networks[ $row["network"] ]["network_name"] = $row["network_name"];
			}
			mysql_free_result( $select_result );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function add( $this_lines , $start_index ) {
			$line_count = count( $this_lines );
			$arr = explode( " " , $this_lines[0] );
			if( ( count( $arr ) != 3 ) || ( $arr[0] != "lease" ) || ( $arr[2] != "{" ) ) {
				$this->log_write( "parse error at line < " . $start_index . " >." );
				return false;
			}

			if( $this_lines[ $line_count - 1 ] != "}" ) {
				$this->log_write( "parse error at line < " . ( $start_index + $line_count ) . " >." );
				return false;
			}

			$index = 0;
			$lease_ip_address = $arr[1];
			if( array_key_exists( $lease_ip_address , $this->lease_table ) ) {
				$index = count( $this->lease_table[$lease_ip_address] );
			}

			$this->reset_array_item( $this->lease_table[ $lease_ip_address ][$index] );

			for( $i = 1; $i < $line_count - 1; $i ++ ) {
				$arr = explode( " " , $this_lines[ $i ] );

				if( $arr[0] == "starts" ) {
					$this->lease_table[ $lease_ip_address ][$index]["starts_date"] = $arr[2];
					$this->lease_table[ $lease_ip_address ][$index]["starts_time"] = $this->clear_value( $arr[3] );
				}
				else if( $arr[0] == "ends" ) {
					$this->lease_table[ $lease_ip_address ][$index]["ends_date"] = $arr[2];
					$this->lease_table[ $lease_ip_address ][$index]["ends_time"] = $this->clear_value( $arr[3] );
				}
				else if( $arr[0] == "tstp" ) {
					$this->lease_table[ $lease_ip_address ][$index]["tstp_date"] = $arr[2];
					$this->lease_table[ $lease_ip_address ][$index]["tstp_time"] = $this->clear_value( $arr[3] );
				}
				else if( $arr[0] == "binding" ) {
					$this->lease_table[ $lease_ip_address ][$index]["binding_state"] = $this->clear_value( $arr[2] );
				}
				else if( $arr[0] == "next" ) {
					$this->lease_table[ $lease_ip_address ][$index]["next_binding_state"] = $this->clear_value( $arr[3] );
				}
				else if( $arr[0] == "hardware" ) {
					$this->lease_table[ $lease_ip_address ][$index]["hardware_ethernet"] = $this->reformat_hardware_ethernet( $this->clear_value( $arr[2] ) );
				}
				else if( $arr[0] == "uid" ) {
					$this->lease_table[ $lease_ip_address ][$index]["uid"] = $this->clear_value( $arr[1] );
				}
				else if( ( $arr[0] == "set" ) && ( $arr[1] == "ddns-txt" ) ) {
					$this->lease_table[ $lease_ip_address ][$index]["ddns_txt"] = $this->clear_value( $arr[3] );
				}
				else if( ( $arr[0] == "set" ) && ( $arr[1] == "ddns-fwd-name" ) ) {
					$this->lease_table[ $lease_ip_address ][$index]["ddns_fwd_name"] = $this->clear_value( $arr[3] );
				}
				else if( $arr[0] == "client-hostname" ) {
					$this->lease_table[ $lease_ip_address ][$index]["client_host_name"] = $this->clear_value( $arr[1] );
				}
			}

		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function parse_host( $subnet , $subnet_name , &$start_index ) {
			$arr = explode( " " , $this->lines[ $start_index ++ ] );
			$host = $arr[ 1 ];

			if( $arr[ 2 ] != "{" ) {
				$this->error_message = "parse error occured at line " . ( $start_index - 1 );
				return false;
			}

			$this->conf_host[$host] = array();
			$this->conf_host[$host]["subnet"] = $subnet;
			$this->conf_host[$host]["subnet_name"] = $subnet_name;

			//print "subnet_name = " . $subnet_name . "\r\n";
			//print "this->conf_host[$host][subnet_name]" . $this->conf_host[$host]["subnet_name"] . "\r\n";

			while( $this->lines[ $start_index ] != "}" ) {
				$empty = ( $this->lines[ $start_index ] == "" ) ? true : false;
				$comment = ( substr( $this->lines[ $start_index ] , 0 , 1 ) == "#" ) ? true : false;
				if( (!$comment) && ( !$empty) ) {
					$arr = explode( " " , $this->lines[ $start_index ] );
					switch( $arr[ 0 ] ) {
						case "hardware" : $this->conf_host[$host]["hardware_ethernet"] = $this->reformat_hardware_ethernet( $arr[2] ); break;
						case "fixed-address" : $this->conf_host[$host]["fixed_address"] = $arr[1]; break;
					}
				}

				$start_index ++;
			}
			$h1 =( array_key_exists( "hardware_ethernet" , $this->conf_host[$host] ) ) ? true : false;
			$h2 =( array_key_exists( "fixed_address" , $this->conf_host[$host] ) ) ? true : false;

			if( ( !$h1 ) && ( !$h2 ) ) {
				$this->conf_host[$host]["status"] = "disabled";
				$this->conf_host[$host]["hardware_ethernet"] = "0000.0000.0000";
				$this->conf_host[$host]["fixed_address"] = "0.0.0.0";
			}
			else if( ( !$h1 ) && ( $h2 ) ) {
				$this->conf_host[$host]["status"] = "error";
				$this->conf_host[$host]["hardware_ethernet"] = "0000.0000.0000";
			}
			else if( ( $h1 ) && ( !$h2 ) ) {
				$this->conf_host[$host]["status"] = "error";
				$this->conf_host[$host]["fixed_address"] = "0.0.0.0";
			}
			else $this->conf_host[$host]["status"] = "active";

		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function parse_subnet( &$start_index ) {
			$arr = explode( " " , $this->lines[ $start_index ++ ] );
			$subnet = $arr[ 1 ];
			$netmask = $arr[ 3 ];
			$subnet_name = "undefined";

			if( $arr[ 4 ] != "{" ) {
				$this->error_message = "parse error occured at line " . ( $start_index - 1 );
				return false;
			}

			$this->conf_subnet[$subnet] = array();
			$this->conf_subnet[$subnet]["network"] = $subnet_name;
			if( array_key_exists( $subnet , $this->mysql_networks ) ) {
				$this->conf_subnet[$subnet]["network"] = $this->mysql_networks[ $subnet ]["network_name"];
				$subnet_name = $this->mysql_networks[ $subnet ]["network_name"];
			}
			//else {
			//	print "------------------------\r\n";
			//	print $subnet . " does not exists in array this->sql_table_networks\r\n";
			//	print_r( $this->mysql_networks );
			//	print "------------------------\r\n";
			//}

			$this->conf_subnet[$subnet]["netmask"] = $netmask;
			while( $this->lines[ $start_index ] != "}" ) {
				$empty = ( $this->lines[ $start_index ] == "" ) ? true : false;
				$comment = ( substr( $this->lines[ $start_index ] , 0 , 1 ) == "#" ) ? true : false;
				if( (!$comment) && ( !$empty) ) {
					$arr = explode( " " , $this->lines[ $start_index ] );
					switch( $arr[ 0 ] ) {
						case "range" : $this->conf_subnet[$subnet]["range_start"] = $arr[1]; $this->conf_subnet[$subnet]["range_end"] = $arr[2]; break;
							case "ddns-updates" : $this->conf_subnet[$subnet]["ddns_updates"] = $arr[1]; break;
						case "ddns-domainname" : $this->conf_subnet[$subnet]["ddns_domain_name"] = $arr[1]; break;
						case "option" :
							switch( $arr[1] ) {
								case "domain-name-servers" : $this->conf_subnet[$subnet]["option_domain_name_servers"] = "";
									for( $xi = 2; $xi < count( $arr ); $xi ++ ) {
										$this->conf_subnet[$subnet]["option_domain_name_servers"] .= $arr[ $xi ];
									} break;
								case "routers" : $this->conf_subnet[$subnet]["option_routers"] = $arr[2]; break;
								case "broadcast-address" : $this->conf_subnet[$subnet]["option_broadcast_address"] = $arr[2]; break;
							} break;
						case "host" : $this->parse_host( $subnet , $subnet_name , $start_index ); break;
					}
				}

				$start_index ++;
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function read_from_conf_file() {
			$parse_stop = false;

			$file_contents = file_get_contents( $this->dhcpd_conf_file );
			if( !$file_contents ) {
				$this->error_message = "could not read the file < " . $this->dhcpd_conf_file . " >. please check the file exists and in the path you specified !!!";
				$this->log_write( $this->error_message );
				return false;
			}

			$this->lines = explode( "\n" , $file_contents );
			$file_contents = "";

			for( $i = 0; $i < count( $this->lines ); $i ++ ) {
				$this->lines[ $i ] = trim( $this->lines[ $i ] );
				$this->lines[ $i ] = $this->clear_value( $this->lines[ $i ] );
			}

			$i = 0;
			$counter = 0;
			while( $i < count( $this->lines ) ) {
				$empty = ( $this->lines[ $i ] == "" ) ? true : false;
				$comment = ( substr( $this->lines[ $i ] , 0 , 1 ) == "#" ) ? true : false;

				if( (!$comment) && ( !$empty) ) {
					$arr = explode( " " , $this->lines[ $i ] );
					switch( $arr[ 0 ] ) {
						case "authoritative" : $this->conf["authoritative"] = "yes"; break;
						case "ddns-updates" : $this->conf["ddns_updates"] = $arr[ 1 ]; break;
						case "ddns-update-style" : $this->conf["ddns_update_style"] = $arr[ 1 ]; break;
						case "update-static-leases" : $this->conf["update_static_leases"] = $arr[ 1 ]; break;
						case "default-lease-time" : $this->conf["default_lease_time"] = $arr[ 1 ]; break;
						case "max-lease-time" : $this->conf["max_lease_time"] = $arr[ 1 ]; break;
						case "one-lease-per-client" : $this->conf["one_lease_per_client"] = $arr[ 1 ]; break;
						case "ddns-ttl" : $this->conf["ddns_ttl"] = $arr[ 1 ]; break;
						case "log-facility" : $this->conf["log_facility"] = $arr[ 1 ]; break;
						case "option" : $this->conf["option_" . $arr[ 1 ] ] = $arr[ 2 ]; break;
						case "subnet" : $this->parse_subnet( $i ); break;
						//case "" : $this->parse_subnet( $i ); break;
					}
				}

				$i ++;
			}

			//print_r( $this->conf );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function read_from_lease_file() {
			$parse_stop = false;

			//$file_contents = file_get_contents( $this->dhcpd_lease_file );
			$file_contents = $this->read_lease_file();
			if( !$file_contents ) {
				$this->error_message = "could not read the file < " . $this->dhcpd_lease_file . " >. please check the file exists and in the path you specified !!!";
				$this->log_write( $this->error_message );
				return false;
			}

			$this->lines = explode( "\n" , $file_contents );
			$file_contents = "";

			for( $i = 0; $i < count( $this->lines ); $i ++ ) {
				$this->lines[ $i ] = trim( $this->lines[ $i ] );
			}

			$i = 0;
			$counter = 0;
			$line_count = count( $this->lines );
			while( $i < $line_count ) {
				$empty = ( $this->lines[ $i ] == "" ) ? true : false;
				$comment = ( strpos( $this->lines[ $i ] , "#" ) === false ) ? false : true;

				if( (!$comment) && ( !$empty) ) {
					$lease = ( strpos( $this->lines[ $i ] , "lease" ) === false ) ? false : true;

					if( $lease ) {
						$lease_lines = array();
						$j = $i;
						while( ( $this->lines[ $j ] != "}" ) && ( !$parse_stop ) ) {
							$lease_lines[] = $this->lines[ $j ];
							$j ++;
							if( $j == $line_count ) {
								$parse_stop = true;
								return true;
							}
						}
						$lease_lines[] = $this->lines[ $j ];
						$j ++;
						$counter ++;
						$this->add( $lease_lines , $i );
						$i = $j;

					}
					else $i ++;
				}
				else $i ++;
			}

			$this->log_write( "lease table count : " . count( $this->lease_table ) );
			$this->log_write( "lease counter : " . $counter );
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++   LEASE DATABASE FUNCTIONS   ++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function select_lease_database( $lease_ip , $lease_index ) {
			$this_record = array();
			$sql_select_str = sprintf( $this->sql_select_base , $lease_ip , $lease_index );
			$select_result = mysql_query( $sql_select_str , $this->sql_connection_id );
			if( !$select_result ) return null;
			$rows_affected = mysql_affected_rows();
			if( $rows_affected != 1 ) return null;

			$row = mysql_fetch_assoc( $select_result );
			$this_record["starts_date"] = $row["starts_date"];
			$this_record["starts_time"] = $row["starts_time"];
			$this_record["ends_date"] = $row["ends_date"];
			$this_record["ends_time"] = $row["ends_time"];
			$this_record["tstp_date"] = $row["tstp_date"];
			$this_record["tstp_time"] = $row["tstp_time"];
			$this_record["binding_state"] = $row["binding_state"];
			$this_record["next_binding_state"] = $row["next_binding_state"];
			$this_record["hardware_ethernet"] = $row["hardware_ethernet"];
			$this_record["uid"] = $row["uid"];
			$this_record["client_host_name"] = $row["client_host_name"];
			$this_record["ddns_txt"] = $row["ddns_txt"];
			$this_record["ddns_fwd_name"] = $row["ddns_fwd_name"];

			mysql_free_result( $select_result );

			return $this_record;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function update_lease_database( $this_record , $lease_ip , $lease_index ) {
			$sql_update_str = sprintf( $this->sql_update_base ,
				$this_record["starts_date"] ,
				$this_record["starts_time"] ,
				$this_record["ends_date"] ,
				$this_record["ends_time"] ,
				$this_record["tstp_date"] ,
				$this_record["tstp_time"] ,
				$this_record["binding_state"] ,
				$this_record["next_binding_state"] ,
				$this_record["hardware_ethernet"] ,
				$this_record["uid"] ,
				$this_record["client_host_name"] ,
				$this_record["ddns_txt"] ,
				$this_record["ddns_fwd_name"] ,
				$lease_ip , $lease_index );

			$update_result = mysql_query( $sql_update_str , $this->sql_connection_id );
			if( !$update_result ) {
				$this->log_write( $sql_update_str );
				$this->log_write( "Lease UPDATE Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function insert_lease_database( $this_record , $lease_ip , $lease_index ) {
			$sql_insert_str = sprintf( $this->sql_insert_base ,
					$lease_ip ,	$lease_index ,
					$this_record["starts_date"] ,
					$this_record["starts_time"] ,
					$this_record["ends_date"] ,
					$this_record["ends_time"] ,
					$this_record["tstp_date"] ,
					$this_record["tstp_time"] ,
					$this_record["binding_state"] ,
					$this_record["next_binding_state"] ,
					$this_record["hardware_ethernet"] ,
					$this_record["uid"] ,
					$this_record["client_host_name"] ,
					$this_record["ddns_txt"] ,
					$this_record["ddns_fwd_name"] );

			$insert_result = mysql_query( $sql_insert_str , $this->sql_connection_id );
			if( !$insert_result ) {
				$this->log_write( $sql_insert_str );
				$this->log_write( "Lease INSERT Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function delete_lease_database( $lease_ip , $lease_index ) {
			$sql_delete_str = sprintf( $this->sql_delete_base , $lease_ip , $lease_index );
			$delete_result = mysql_query( $sql_delete_str , $this->sql_connection_id );
			if( !$delete_result ) {
				$this->log_write( $sql_delete_str );
				$this->log_write( "Lease DELETE Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++++              END          +++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++   CONF DATABASE FUNCTIONS   +++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function select_conf_database( $param ) {
			$sql_select_str = sprintf( $this->sql_select_base_conf , $param );
			$select_result = mysql_query( $sql_select_str , $this->sql_connection_id );
			if( !$select_result ) return null;
			$rows_affected = mysql_affected_rows();
			if( $rows_affected != 1 ) return null;

			$row = mysql_fetch_assoc( $select_result );
			$value = $row["value"];
			mysql_free_result( $select_result );

			return $value;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function update_conf_database( $param , $value ) {
			$sql_update_str = sprintf( $this->sql_update_base_conf , $value , $param );
			$update_result = mysql_query( $sql_update_str , $this->sql_connection_id );
			if( !$update_result ) {
				$this->log_write( $sql_update_str );
				$this->log_write( "Conf UPDATE Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function insert_conf_database( $param , $value ) {
			$sql_insert_str = sprintf( $this->sql_insert_base_conf , $param , $value );
			$insert_result = mysql_query( $sql_insert_str , $this->sql_connection_id );
			if( !$insert_result ) {
				$this->log_write( $sql_insert_str );
				$this->log_write( "Conf INSERT Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function delete_conf_database( $param ) {
			$sql_delete_str = sprintf( $this->sql_delete_base_conf , $param );
			$delete_result = mysql_query( $sql_delete_str , $this->sql_connection_id );
			if( !$delete_result ) {
				$this->log_write( $sql_delete_str );
				$this->log_write( "Conf DELETE Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++++              END          +++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//+++++++++++++++   SUBNET DATABASE FUNCTIONS   ++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function select_subnet_database( $subnet ) {
			$this_record = array();
			$sql_select_str = sprintf( $this->sql_select_base_subnet , $subnet );
			$select_result = mysql_query( $sql_select_str , $this->sql_connection_id );
			if( !$select_result ) return null;
			$rows_affected = mysql_affected_rows();
			if( $rows_affected != 1 ) return null;

			$row = mysql_fetch_assoc( $select_result );
			$this_record["subnet"] = $row["subnet"];
			$this_record["netmask"] = $row["netmask"];
			$this_record["network"] = $row["network"];
			$this_record["range_start"] = $row["range_start"];
			$this_record["range_end"] = $row["range_end"];
			$this_record["ddns_updates"] = $row["ddns_updates"];
			$this_record["ddns_domain_name"] = $row["ddns_domain_name"];
			$this_record["option_domain_name_servers"] = $row["option_domain_name_servers"];
			$this_record["option_routers"] = $row["option_routers"];
			$this_record["option_broadcast_address"] = $row["option_broadcast_address"];

			mysql_free_result( $select_result );

			return $this_record;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function update_subnet_database( $this_record , $subnet ) {
			$sql_update_str = sprintf( $this->sql_update_base_subnet ,
				$this_record["netmask"] ,
				$this_record["network"] ,
				$this_record["range_start"] ,
				$this_record["range_end"] ,
				$this_record["ddns_updates"] ,
				$this_record["ddns_domain_name"],
				$this_record["option_domain_name_servers"] ,
				$this_record["option_routers"] ,
				$this_record["option_broadcast_address"] ,
				$subnet );

			$update_result = mysql_query( $sql_update_str , $this->sql_connection_id );
			if( !$update_result ) {
				$this->log_write( $sql_update_str );
				$this->log_write( "Subnet UPDATE Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function insert_subnet_database( $this_record , $subnet ) {
			$sql_insert_str = sprintf( $this->sql_insert_base_subnet ,
					$subnet ,
					$this_record["netmask"] ,
					$this_record["network"] ,
					$this_record["range_start"] ,
					$this_record["range_end"] ,
					$this_record["ddns_updates"] ,
					$this_record["ddns_domain_name"],
					$this_record["option_domain_name_servers"] ,
					$this_record["option_routers"] ,
					$this_record["option_broadcast_address"] );

			$insert_result = mysql_query( $sql_insert_str , $this->sql_connection_id );
			if( !$insert_result ) {
				$this->log_write( $sql_insert_str );
				$this->log_write( "Subnet INSERT Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function delete_subnet_database( $subnet ) {
			$sql_delete_str = sprintf( $this->sql_delete_base_subnet , $subnet );
			$delete_result = mysql_query( $sql_delete_str , $this->sql_connection_id );
			if( !$delete_result ) {
				$this->log_write( $sql_delete_str );
				$this->log_write( "Subnet DELETE Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++++              END          +++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++   HOST DATABASE FUNCTIONS   +++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function select_host_database( $host , $subnet ) {
			$this_record = array();
			$sql_select_str = sprintf( $this->sql_select_base_host , $host , $subnet );
			$select_result = mysql_query( $sql_select_str , $this->sql_connection_id );
			if( !$select_result ) return null;
			$rows_affected = mysql_affected_rows();
			if( $rows_affected != 1 ) return null;

			$row = mysql_fetch_assoc( $select_result );
			$this_record["host"] = $row["host"];
			$this_record["subnet"] = $row["subnet"];
			$this_record["fixed_address"] = $row["fixed_address"];
			$this_record["hardware_ethernet"] = $row["hardware_ethernet"];
			$this_record["status"] = $row["status"];

			mysql_free_result( $select_result );

			return $this_record;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function update_host_database( $this_record , $host , $subnet ) {
			$sql_update_str = sprintf( $this->sql_update_base_host ,
				$this_record["subnet_name"] ,
				$this_record["fixed_address"] ,
				$this_record["hardware_ethernet"] ,
				$this_record["status"] ,
				$host , $subnet );

			$update_result = mysql_query( $sql_update_str , $this->sql_connection_id );
			if( !$update_result ) {
				$this->log_write( $sql_update_str );
				$this->log_write( "Host UPDATE Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function insert_host_database( $this_record , $host ) {
			$sql_insert_str = sprintf( $this->sql_insert_base_host ,
					$host ,
					$this_record["subnet"] ,
					$this_record["subnet_name"] ,
					$this_record["fixed_address"] ,
					$this_record["hardware_ethernet"] ,
					$this_record["status"] );

			$insert_result = mysql_query( $sql_insert_str , $this->sql_connection_id );
			if( !$insert_result ) {
				$this->log_write( $sql_insert_str );
				$this->log_write( "Host INSERT Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function delete_host_database( $host , $subnet ) {
			$sql_delete_str = sprintf( $this->sql_delete_base_host , $host , $subnet );
			$delete_result = mysql_query( $sql_delete_str , $this->sql_connection_id );
			if( !$delete_result ) {
				$this->log_write( $sql_delete_str );
				$this->log_write( "Host DELETE Error => " . mysql_error() );
				return false;
			}

			return true;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//++++++++++++++++++              END          +++++++++++++++++++++++++++++++++
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function sync_mysql_conf_file() {
			foreach( $this->mysql_conf as $param => $value ) {
				if( array_key_exists( $param , $this->conf ) ) {
					if( $this->mysql_conf[ $param ] == $this->conf[ $param ] ) {
						unset( $this->conf[ $param ] );
					}
					else {
						$this->mysql_conf[ $param ] = $this->conf[ $param ];
						$this->update_conf_database( $param , $this->mysql_conf[ $param ] );
						unset( $this->conf[ $param ] );
					}
				}
				else {
					$this->delete_conf_database( $param );
					unset( $this->mysql_conf[ $param ] );
				}
			}

			foreach( $this->conf as $param => $value ) {
				$this->mysql_conf[ $param ] = $this->conf[ $param ];
				$this->insert_conf_database( $param , $value );
				unset( $this->conf[ $param ] );
			}

		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function sync_mysql_host_file() {
			foreach( $this->mysql_host as $host => $value ) {
				if( array_key_exists( $host , $this->conf_host ) ) {
					if( $this->equals( $this->mysql_host[ $host ] , $this->conf_host[ $host ] ) ) {
						unset( $this->conf_host[ $host ] );
					}
					else {
						$this->assign( $this->mysql_host[ $host ] , $this->conf_host[ $host ] );
						$this->update_host_database( $this->mysql_host[ $host ] , $host , $this->conf_host[$host]["subnet"] );
						unset( $this->conf_host[ $host ] );
					}
				}
				else {
					$this->delete_host_database( $this->mysql_host[ $host ] , $host , $this->conf_host[$host]["subnet"] );
					unset( $this->mysql_host[ $host ] );
				}
			}

			foreach( $this->conf_host as $host => $value ) {
				$this->mysql_host[ $host ] = array();
				$this->assign( $this->mysql_host[ $host ] , $this->conf_host[ $host ] );
				$this->insert_host_database( $this->mysql_host[ $host ] , $host );
				//print_r( $this->mysql_host[$host] );
				unset( $this->conf_host[ $host ] );
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function sync_mysql_subnet_file() {
			foreach( $this->mysql_subnet as $subnet => $value ) {
				if( array_key_exists( $subnet , $this->conf_subnet ) ) {
					if( $this->equals( $this->mysql_subnet[ $subnet ] , $this->conf_subnet[ $subnet ] ) ) {
						unset( $this->conf_subnet[ $subnet ] );
					}
					else {
						$this->assign( $this->mysql_subnet[ $subnet ] , $this->conf_subnet[ $subnet ] );
						$this->update_subnet_database( $this->mysql_subnet[ $subnet ] , $subnet );
						unset( $this->conf_subnet[ $subnet ] );
					}
				}
				else {
					$this->delete_subnet_database( $this->mysql_subnet[ $subnet ] , $subnet );
					unset( $this->mysql_subnet[ $subnet ] );
				}
			}

			foreach( $this->conf_subnet as $subnet => $value ) {
				$this->mysql_subnet[ $subnet ] = array();
				$this->assign( $this->mysql_subnet[ $subnet ] , $this->conf_subnet[ $subnet ] );
				$this->insert_subnet_database( $this->mysql_subnet[ $subnet ] , $subnet );
				unset( $this->conf_subnet[ $subnet ] );
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function sync_mysql_lease_file() {
			foreach( $this->lease_table as $lease_ip => $value ) {
				if( array_key_exists( $lease_ip , $this->mysql_table ) ) {
					foreach( $this->lease_table[ $lease_ip ] as $lease_index => $valuex ) {
						if( !array_key_exists( $lease_index , $this->mysql_table[ $lease_ip ] ) ) {
							$this->assign( $this->mysql_table[ $lease_ip ][ $lease_index ] , $this->lease_table[ $lease_ip ][ $lease_index ] );
							$this->insert_lease_database( $this->mysql_table[ $lease_ip ][ $lease_index ] , $lease_ip , $lease_index );
							//unset( $this->lease_table[ $lease_ip ][ $lease_index ] );
						}
					}
				}
				else {
					foreach( $this->lease_table as $lease_ip => $value ) {
						foreach( $this->lease_table[ $lease_ip ] as $lease_index => $valuex ) {
							$this->mysql_table[ $lease_ip ][ $lease_index ] = array();
							$this->assign( $this->mysql_table[ $lease_ip ][ $lease_index ] , $this->lease_table[ $lease_ip ][ $lease_index ] );
							$this->insert_lease_database( $this->mysql_table[ $lease_ip ][ $lease_index ] , $lease_ip , $lease_index );
							//unset( $this->lease_table[ $lease_ip ][ $lease_index ] );
						}
					}
				}
			}
			$this->lease_table = array();
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		private function sync_mysql_lease_file_Old() {
			foreach( $this->mysql_table as $lease_ip => $value ) {
				if( array_key_exists( $lease_ip , $this->lease_table ) ) {
					foreach( $this->mysql_table[ $lease_ip ] as $lease_index => $valuex ) {
						if( array_key_exists( $lease_index , $this->lease_table[ $lease_ip ] ) ) {
							if( $this->equals( $this->mysql_table[ $lease_ip ][ $lease_index ] , $this->lease_table[ $lease_ip ][ $lease_index ] ) ) {
								unset( $this->lease_table[ $lease_ip ][ $lease_index ] );
							}
							else {
								$this->assign( $this->mysql_table[ $lease_ip ][ $lease_index ] , $this->lease_table[ $lease_ip ][ $lease_index ] );
								$this->update_lease_database( $this->mysql_table[ $lease_ip ][ $lease_index ] , $lease_ip , $lease_index );
								unset( $this->lease_table[ $lease_ip ][ $lease_index ] );
							}
						}
						else {
							$this->delete_lease_database( $this->mysql_table[ $lease_ip ][ $lease_index ] , $lease_ip , $lease_index );
							unset( $this->mysql_table[ $lease_ip ][ $lease_index ] );
						}
					}
				}
				else {
					foreach( $this->mysql_table[ $lease_ip ] as $lease_index => $valuex ) {
						$this->delete_lease_database( $this->mysql_table[ $lease_ip ][ $lease_index ] , $lease_ip , $lease_index );
						unset( $this->mysql_table[ $lease_ip ][ $lease_index ] );
					}
				}
			}

			foreach( $this->lease_table as $lease_ip => $value ) {
				foreach( $this->lease_table[ $lease_ip ] as $lease_index => $valuex ) {
					$this->mysql_table[ $lease_ip ][ $lease_index ] = array();
					$this->assign( $this->mysql_table[ $lease_ip ][ $lease_index ] , $this->lease_table[ $lease_ip ][ $lease_index ] );
					$this->insert_lease_database( $this->mysql_table[ $lease_ip ][ $lease_index ] , $lease_ip , $lease_index );
					unset( $this->lease_table[ $lease_ip ][ $lease_index ] );
				}
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		public function startup() {
			$this->get_file_info( "conf" , $this->dhcpd_conf_file );
			$this->get_file_info( "lease" , $this->dhcpd_lease_file );

			if( $this->open_database_connection() )  {
				$this->read_from_mysql_networks();
				$this->read_from_mysql_conf();
				$this->read_from_mysql_host();
				$this->read_from_mysql_subnet();
				$this->read_from_conf_file();
				$this->sync_mysql_conf_file();
				$this->sync_mysql_host_file();
				$this->sync_mysql_subnet_file();

				$this->read_from_mysql_lease();
				$this->read_from_lease_file();
				$this->sync_mysql_lease_file();
				$this->close_database_connection();
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		public function loop() {
			while( true ) {
				if( $this->stop_me() ) return;
				$this->reset_arrays();
				$this->re_init_log_file();

				if( $this->reload_me( "conf" ) ) {
					if( $this->open_database_connection() )  {
						$this->read_from_mysql_networks();
						$this->read_from_mysql_conf();
						$this->read_from_mysql_host();
						$this->read_from_mysql_subnet();
						$this->read_from_conf_file();
						$this->sync_mysql_conf_file();
						$this->sync_mysql_host_file();
						$this->sync_mysql_subnet_file();
						$this->close_database_connection();
					}
				}

				if( $this->reload_me( "lease" ) ) {
					if( $this->open_database_connection() )  {
						$this->read_from_mysql_lease();
						$this->read_from_lease_file();
						$this->sync_mysql_lease_file();
						$this->close_database_connection();
					}
				}

				sleep( $this->sleep_time );
			}
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		/*public function loop() {
			while( true ) {
				if( $this->stop_me() ) return;

				if( $this->reload_me( "conf" ) ) {
					if( $this->open_database_connection() )  {
						$this->read_from_conf_file();
						$this->read_from_mysql_host();
						$this->read_from_mysql_subnet();
						$this->sync_mysql_conf_file();
						$this->sync_mysql_host_file();
						$this->sync_mysql_subnet_file();
						$this->close_database_connection();
					}
				}

				if( $this->reload_me( "lease" ) ) {
					if( $this->open_database_connection() )  {
						$this->read_from_lease_file();
						$this->sync_mysql_lease_file();
						$this->close_database_connection();
					}
				}

				sleep( $this->sleep_time );
			}
		}*/
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\
		public function set_sleep_time( $time_sec ) {
			if( !is_null( $time_sec ) ) $this->sleep_time = $time_sec;
		}
		//\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\/\

	}

?>