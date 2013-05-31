<?php

	include_once( $application_paths[ "class_path" ] . "clsLOG.php" );

	class clsLDAPBASE {
		private $LDAP_OPT_DIAGNOSTIC_MESSAGE;

		private $server;
		private $admin;
		private $password;
		public $base_dn;
		public $base_dc;

		public $connection;
		private $bind;
		private $result;
		public $entry_count;
		public $connected;

		public $log;
		public $log_file_name;
		public $log_enabled;

		public $last_error_no;
		public $last_error_message;


		public $error_message;
		public $target_dn;
		public $current_group;


		public $entries_add;
		public $entries_read;
		public $entries_update;

		public $add_unique_id;
		public $modify_unique_id;

		public $available_fields;


		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function __construct( $server_addr = null , $b_dc, $b_dn , $logdir, $log_id ) {

			$this->LDAP_OPT_DIAGNOSTIC_MESSAGE = 0x0032;

			$this->base_dc = $b_dc;
			$this->base_dn = $b_dn;
			$this->server = "ldap sunucu adresi";
			$this->admin = "cn=ldap admin kullanýcý adý," . $this->base_dn;
			$this->password = "ldap admin kullanýcý þifresi";

			$this->connection = null;
			$this->bind = false;
			$this->result = null;
			$this->entry_count = 0;
			$this->connected = FALSE;

			$this->log = NULL;
			$this->log_file_name = "";
			$this->log_enabled = TRUE;

			$this->last_error_no = 0;
			$this->last_error_message = "";

			$this->error_message = "";
			$this->target_dn = "";

			$this->entries_add = array();
			$this->entries_read = array();
			$this->entries_update = array();

			$this->add_unique_id = "";
			$this->modify_unique_id = "";

			$this->available_fields = array();

			if( $server_addr == null ) $this->server = "127.0.0.1";
			else $this->server = $server_addr;

			$this->log = new clsLOG( $logdir , $log_id );
			$this->connect();

		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function __destruct() {
			if( $this->connection != null )
				$this->disconnect();
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx


		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		private function get_error() {
			$this->last_error_no = ldap_errno( $this->connection );
			$this->last_error_message = ldap_error( $this->connection );
			$extended_error = "";
			$this->last_error_extended = "nothing";

			if( ldap_get_option( $this->connection, $this->LDAP_OPT_DIAGNOSTIC_MESSAGE , $extended_error ) )
				$this->last_error_extended = $extended_error;

			$outmsg = 	"Detailed info --> " .
						"Code = ( " . $this->last_error_no . " ) " .
						"Msg = ( " . $this->last_error_message . " ) " .
						"Detail = ( " . $this->last_error_extended . " ).";

			return $outmsg;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		private function dump_error( $dump_type , $dump_array ) {
			if( $this->log_enabled ) {
				$this->log->write( $dump_type . " failed." );
				$this->log->write( $this->get_error() );

				if( ( $dump_array != NULL ) && ( is_array( $dump_array ) ) ) {
					if( $this->last_error_no != 68 ) {
						$this->log->write( "Dumping " . $dump_type . " fields contents ...." );

						foreach( $dump_array as $key => $value ) {
							if( is_array( $value ) ) {
								foreach( $value as $key_sub => $value_sub ) {
									$this->log->write( "entries[ " . $key . " ][" . $key_sub . "] = " . $value_sub );
								}
							}
							else {
								$this->log->write( "entries[ " . $key . " ] = " . $value );
							}
						}
						$this->log->write( "Dump finished." );
					}
				}
			}

			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function connect() {
			$this->connection = ldap_connect( $this->server );
			if( $this->connection ) {
				ldap_set_option( $this->connection, LDAP_OPT_PROTOCOL_VERSION, 3 );
				$this->bind = ldap_bind( $this->connection , $this->admin , $this->password );
				if( !$this->bind ) {
					ldap_close( $this->connection );
					$this->error_message = "can not connect to the server " . $this->server . ".";
					$this->connected = FALSE;
					$this->connection = NULL;
					return FALSE;
				}
			}
			else {
				$this->error_message = "invalid credientials.";
				$this->connected = FALSE;
				$this->connection = NULL;
				return FALSE;
			}

			$this->connected = TRUE;
			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function disconnect() {
			ldap_close( $this->connection );
			$this->connection = NULL;
			$this->connected = FALSE;

			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function reconnect() {
			disconnect();
			connect();
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function login( $uname , $upassword ) {
			$lconnection = NULL;
			$lbind = FALSE;
			$urdn = "cn=" . $uname . ",ou=iyteusers," . $this->base_dn;

			$lconnection = @ldap_connect( $this->server );
			if( $lconnection ) {
				@ldap_set_option( $lconnection, LDAP_OPT_PROTOCOL_VERSION, 3 );
				$bind = @ldap_bind( $lconnection , $urdn , $upassword );
				if( !$bind ) {
					@ldap_close( $lconnection );
					$this->error_message = "can not bind to the server " . $this->server . " with rdn => " . $urdn;
					$this->log->write( $this->error_message );
					return FALSE;
				}
			}
			else {
				$this->error_message = "invalid credientials.";
				$this->log->write( $this->error_message );
				return FALSE;
			}

			@ldap_close( $lconnection );

			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function add( $target , $new_entry ) {
			if( $this->log_enabled )
				$this->log->write( "ldapadd --> dn:" . $target );

			$this->result = ldap_add( $this->connection , $target , $new_entry );
			if( !$this->result ) {
				$this->dump_error( "ldap_add" , $new_entry );
				return FALSE;
			}
			return TRUE;

		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function remove( $target ) {
			if( $this->log_enabled )
				$this->log->write( "ldap_delete --> " . $target );

			$this->result = ldap_delete( $this->connection , $target );
			if( !$this->result ) {
				$this->dump_error( "ldap_delete" , NULL );
				return FALSE;
			}
			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// Done
		public function add_member( $target_user_dn , $target_entries ) {
			if( $this->log_enabled )
				$this->log->write( "ldap_mod_add --> " . $target_user_dn . " -> " . $target_entries );

			$this->result = ldap_mod_add( $this->connection  , $target_user_dn , $target_entries  );

			if( !$this->result ) {
				$this->dump_error( "ldap_mod_add" , $modify_entries );
				return FALSE;
			}

			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// Done
		public function remove_member( $target_user_dn , $target_entries ) {
			if( $this->log_enabled )
				$this->log->write( "ldap_mod_del --> " . $target_user_dn . " -> " . $target_entries );

			$this->result = ldap_mod_del( $this->connection  , $target_user_dn , $target_entries  );

			if( !$this->result ) {
				$this->dump_error( "ldap_mod_del" , $modify_entries );
				return FALSE;
			}

			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function modify( $target , $modify_entries ) {
			if( $this->log_enabled )
				$this->log->write( "ldap_modify --> dn:$target" );

			$result = ldap_modify( $this->connection , $target , $modify_entries );
			if( !$result ) {
				if( $this->log_enabled )
					$this->dump_error( "ldap_modify" , $modify_entries );

				return FALSE;
			}

			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function search( $search_filter , $search_dn , $these_fields = null ) {
			$this->entry_count = 0;
			if( $this->log_enabled ) {
				$this->log->write( "ldap command => ldap_search" );
				$this->log->write( "search filter => " . $search_filter );
				$this->log->write( "search dn => " . $search_dn );
			}

			if( $these_fields == null )
				$sr = ldap_search( $this->connection , $search_dn , $search_filter );
			else
				$sr = ldap_search( $this->connection , $search_dn , $search_filter , $these_fields );

			if( !$sr ) {
				$this->dump_error( "ldap_search" );
				return $this->entry_count;
			}

			$this->entry_count = ldap_count_entries( $this->connection , $sr );
			if( $this->entry_count == 0 ) {
				$this->log->write( "no record found" );
				return $this->entry_count;
			}

			$this->entries_read = array();
			$this->entries_read = ldap_get_entries( $this->connection , $sr );

			$sr = NULL;
			return $this->entry_count;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		public function read( $u_id , $u_grp , $u_class ) {
			$sr = ldap_read( $this->connection , "cn=" . $u_id . ",ou=" . $u_grp . ",dc=iyte,dc=edu,dc=tr", "(objectclass=" . $u_class . ")" );
			$entry = ldap_get_entries( $this->connection , $sr );
			$new_entry = array();

			$entry_count = ldap_count_entries( $this->connection , $sr );
			if( $entry_count == 0 ) return $new_entry;

			for( $i = 0; $i< $entry[0]["count"]; $i ++ ) {
				$key_index = $entry[0][$i];
				if( $key_index != "objectclass" ) {
					$new_entry[ $key_index ] = $entry[0][$key_index];
				}
			}
			return $new_entry;
		}
	}

?>