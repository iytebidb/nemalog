<?php

	class clsIYTELDAP{

		private $THIS_TYPE_UNICODE = 1;
		private $THIS_TYPE_ARRAY_UNICODE = 2;
		private $THIS_TYPE_INT = 3;
		private $THIS_TYPE_IPADDR = 4;
		private $THIS_TYPE_DATE = 5;
		private $THIS_TYPE_PHONE = 6;
		private $THIS_TYPE_STRING = 7;
		private $LDAP_OPT_DIAGNOSTIC_MESSAGE = 0x0032;

		private $ldap_server;
		private $ldap_admin;
		private $ldap_password;
		public $base_dn;
		public $base_dc;

		public $connection;
		private $bind;
		private $result;
		public $entry_count;
		public $connected;

		public $log_handle;
		public $log_enabled;

		public $last_error_no;
		public $last_error_message;
		public $error_message;

		public $add_record_field;

		public $entries_add;
		public $entries_read;
		public $entries_update;

		public $add_unique_id;
		public $modify_unique_id;

		public $available_fields;


		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function __construct() {

			$this->LDAP_OPT_DIAGNOSTIC_MESSAGE = 0x0032;

			$this->base_dc = "dc=iyte";
			$this->base_dn = "dc=iyte,dc=edu,dc=tr";
			$this->ldap_server = "ldap sunucu adresi";
			$this->ldap_admin = "cn=ldap admin kullanýcý adý," . $this->base_dn;
			$this->ldap_password = "ldap kullanýcý þifresi";

			$this->connection = null;
			$this->bind = false;
			$this->result = null;
			$this->entry_count = 0;
			$this->connected = FALSE;

			$this->log_handle = NULL;
			$this->log_enabled = TRUE;

			$this->last_error_no = 0;
			$this->last_error_message = "";

			$this->error_message = "";
			$this->target_dn = "";

			$this->add_record_field = array();
			$this->entries_add = array();
			$this->entries_read = array();
			$this->entries_update = array();

			$this->add_unique_id = "";
			$this->modify_unique_id = "";

			$this->available_fields = array();

			$this->init_settings();

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
				$this->log_write( $dump_type . " failed." );
				$this->log_write( $this->get_error() );

				if( ( $dump_array != NULL ) && ( is_array( $dump_array ) ) ) {
					if( $this->last_error_no != 68 ) {
						$this->log_write( "Dumping " . $dump_type . " fields contents ...." );

						foreach( $dump_array as $key => $value ) {
							if( is_array( $value ) ) {
								foreach( $value as $key_sub => $value_sub ) {
									$this->log_write( "entries[ " . $key . " ][" . $key_sub . "] = " . $value_sub );
								}
							}
							else {
								$this->log_write( "entries[ " . $key . " ] = " . $value );
							}
						}
						$this->log_write( "Dump finished." );
					}
				}
			}

			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function connect() {
			$this->connection = ldap_connect( $this->ldap_server );
			if( $this->connection ) {
				ldap_set_option( $this->connection, LDAP_OPT_PROTOCOL_VERSION, 3 );
				$this->bind = ldap_bind( $this->connection , $this->ldap_admin , $this->ldap_password );
				if( !$this->bind ) {
					ldap_close( $this->connection );
					$this->error_message = "can not connect to the server " . $this->ldap_server . ".";
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

			$lconnection = @ldap_connect( $this->ldap_server );
			if( $lconnection ) {
				@ldap_set_option( $lconnection, LDAP_OPT_PROTOCOL_VERSION, 3 );
				$bind = @ldap_bind( $lconnection , $urdn , $upassword );
				if( !$bind ) {
					@ldap_close( $lconnection );
					$this->error_message = "can not bind to the server " . $this->ldap_server . " with rdn => " . $urdn;
					$this->log_write( $this->error_message );
					return FALSE;
				}
			}
			else {
				$this->error_message = "invalid credientials.";
				$this->log_write( $this->error_message );
				return FALSE;
			}

			@ldap_close( $lconnection );

			return TRUE;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		private function init_settings() {
/*			$this->$available_fields = array(
				"cn" 								=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"givenname" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"sn" 								=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"userpassword" 						=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"ntpassword" 						=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"uid" 								=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"mail" 								=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"description" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"displayname" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"tc-kimlik" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"uidNumber" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 100 ) ,
				"gidNumber" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 200 ) ,
				"homeDirectory" 					=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "/home/" ) ,
				"loginShell" 						=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "/usr/bin/sh/" ) ,
				"gecos" 							=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"guestaccount" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 0 ) ,
				"eduroamenabled" 					=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 1 ) ,
				"isrehberuser" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 1 ) ,
				"ismailuser" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 0 ) ,
				"isspcluser" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 0 ) ,

				"sicil-no" 							=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => 0 ) ,
				"group-id" 							=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "Akademik" ) ,
				"r-unvan-kadro" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-unvan-gorev" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-unvan-kisa" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-birim-kadro" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-birim-gorev" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-oda-no" 							=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-telefon-index" 					=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@iyte" , "default" => array() ) ,
				"r-telefon-no" 						=> array( "type" => $this->ATTR_TYPE_PHONE 			, "size" => 128 , "filter" => "@iyte" , "default" => array() ) ,
				"r-faks-index" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@iyte" , "default" => array() ) ,
				"r-faks-no" 						=> array( "type" => $this->ATTR_TYPE_PHONE 			, "size" => 128 , "filter" => "@iyte" , "default" => array() ) ,
				"r-birim-telefon" 					=> array( "type" => $this->ATTR_TYPE_PHONE 			, "size" => 128 , "filter" => "@iyte" , "default" => "7506000" ) ,
				"r-web-sayfa" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "http://" ) ,
				"r-son-giris-tarih" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => "@iyte" , "default" => ";" ) ,
				"r-son-giris-ip-adres" 				=> array( "type" => $this->ATTR_TYPE_IPADDR 		, "size" => 128 , "filter" => "@iyte" , "default" => "0.0.0.0" ) ,
				"r-olusturan-kisi" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "firatkocak" ) ,
				"r-guncelleyen-kisi" 				=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-ilisik-kesen-kisi" 				=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-olusturma-tarih" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => "@iyte" , "default" => ";" ) ,
				"r-guncelleme-tarih" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => "@iyte" , "default" => ";" ) ,

				"mail-kotasi" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => 0 ) ,
				"web-kotasi" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => 0 ) ,
				"acct-creation-date" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"last-login-date" 					=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"acct-aktif" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => 1 ) ,
				"passwd-last-update-date" 			=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"acct-aktif-last-update-date" 		=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"alternatif-eposta-hesabi" 			=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => array("@iyte","@std") , "default" => "-" ) ,
				"password-reset-kodu" 				=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => array("@iyte","@std") , "default" => "-" ) ,
				"last-password-reset-istek-date" 	=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"acct-silinecek-date" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" )
				);


			$this->ldap_fields = array(
				"cn" 								=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"givenname" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"sn" 								=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"userpassword" 						=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"ntpassword" 						=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"uid" 								=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"mail" 								=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"description" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"displayname" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"tc-kimlik" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"uidNumber" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 100 ) ,
				"gidNumber" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 200 ) ,
				"homeDirectory" 					=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "/home/" ) ,
				"loginShell" 						=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "/usr/bin/sh/" ) ,
				"gecos" 							=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => "@" , "default" => "-" ) ,
				"guestaccount" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 0 ) ,
				"eduroamenabled" 					=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 1 ) ,
				"isrehberuser" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 1 ) ,
				"ismailuser" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 0 ) ,
				"isspcluser" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@" , "default" => 0 ) ,

				"sicil-no" 							=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => 0 ) ,
				"group-id" 							=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "Akademik" ) ,
				"r-unvan-kadro" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-unvan-gorev" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-unvan-kisa" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-birim-kadro" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-birim-gorev" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-oda-no" 							=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-telefon-index" 					=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@iyte" , "default" => array() ) ,
				"r-telefon-no" 						=> array( "type" => $this->ATTR_TYPE_PHONE 			, "size" => 128 , "filter" => "@iyte" , "default" => array() ) ,
				"r-faks-index" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => "@iyte" , "default" => array() ) ,
				"r-faks-no" 						=> array( "type" => $this->ATTR_TYPE_PHONE 			, "size" => 128 , "filter" => "@iyte" , "default" => array() ) ,
				"r-birim-telefon" 					=> array( "type" => $this->ATTR_TYPE_PHONE 			, "size" => 128 , "filter" => "@iyte" , "default" => "7506000" ) ,
				"r-web-sayfa" 						=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "http://" ) ,
				"r-son-giris-tarih" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => "@iyte" , "default" => ";" ) ,
				"r-son-giris-ip-adres" 				=> array( "type" => $this->ATTR_TYPE_IPADDR 		, "size" => 128 , "filter" => "@iyte" , "default" => "0.0.0.0" ) ,
				"r-olusturan-kisi" 					=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "firatkocak" ) ,
				"r-guncelleyen-kisi" 				=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-ilisik-kesen-kisi" 				=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => "@iyte" , "default" => "-" ) ,
				"r-olusturma-tarih" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => "@iyte" , "default" => ";" ) ,
				"r-guncelleme-tarih" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => "@iyte" , "default" => ";" ) ,

				"mail-kotasi" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => 0 ) ,
				"web-kotasi" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => 0 ) ,
				"acct-creation-date" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"last-login-date" 					=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"acct-aktif" 						=> array( "type" => $this->ATTR_TYPE_INT 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => 1 ) ,
				"passwd-last-update-date" 			=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"acct-aktif-last-update-date" 		=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"alternatif-eposta-hesabi" 			=> array( "type" => $this->ATTR_TYPE_UNICODE 		, "size" => 128 , "filter" => array("@iyte","@std") , "default" => "-" ) ,
				"password-reset-kodu" 				=> array( "type" => $this->ATTR_TYPE_STRING 		, "size" => 128 , "filter" => array("@iyte","@std") , "default" => "-" ) ,
				"last-password-reset-istek-date" 	=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" ) ,
				"acct-silinecek-date" 				=> array( "type" => $this->ATTR_TYPE_DATE 			, "size" => 128 , "filter" => array("@iyte","@std") , "default" => ";" )
				);*/

		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setLdapServer( $srv ) {
			$this->ldap_server = $srv;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setLdapAdmin( $adm ) {
			$this->ldap_admin = $adm;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setLdapPassword( $pwd ) {
			$this->ldap_password = $pwd;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setLogHandle( $hnd ) {
			$this->log_handle = $hnd;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setBaseDc( $bdc ) {
			$this->base_dc = $bdc;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setBaseDn( $bdn ) {
			$this->base_dn = $bdn;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// Done
		public function log_write( $msg ) {
			if( !$this->log_handle ) return false;

			$writestr = date("Y/m/d G:i:s") . " --> ";
			$writestr .= $msg . "\r\n";

			ini_set('track_errors', 1);
			fputs( $this->log_handle , $writestr );
			ini_set('track_errors', 0);
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function log_write_error( $err_header ) {
			$err_code = ldap_errno( $this->connection );
			$err_msg = ldap_error( $this->connection );
			$extended_error = "";
			$err_extended = "";

			if( ldap_get_option( $this->connection, $this->LDAP_OPT_DIAGNOSTIC_MESSAGE , $extended_error ) )
				$err_extended = $extended_error;
			else
				$err_extended = "No additional information is available.";

			$outmsg = 	"Detailed info --> " .
						"Code = ( " . $err_code . " ) " .
						"Msg = ( " . $err_msg . " ) " .
						"Detail = ( " . $err_extended . " ).";


			//$writestr = date("D M j G:i:s T Y") . " --> ";
			$writestr = date("Y/m/d G:i:s") . " --> ";
			$ldap_error_message = $err_header . " " . $outmsg;
			$writestr .= $ldap_error_message . "\r\n";

			$xfilehandle = fopen( $options[ "current_log_file" ] , "a+" );

			if( $xfilehandle )
			{
				fputs( $xfilehandle , $writestr );
				fclose( $xfilehandle );
			}

			return $err_code;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//
		// Utility functions
		//
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function NTLMHash( $Input ) {
			$new_password  = "";

			$new_password = iconv( 'UTF-8' , 'UTF-16LE' , $Input );
			//$MD4Hash=bin2hex(mhash(MHASH_MD4,$Input));
			$MD4Hash = hash( 'md4' , $new_password );
			$NTLMHash = strtoupper( $MD4Hash );

			return( $NTLMHash );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function CorrectDateFormat( $this_string )
		{
			$change_str = substr( $this_string , 8 , 2 ) . "." . substr( $this_string , 5 , 2 ) . "." . substr( $this_string , 0 , 4 );
			return $change_str;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function strcmp_tr($str1, $str2)
		{
			$abc = "ABCÇDEFGÐHIÝJKLMNOÖPQRSÞTUÜVXWYZabcçdefgðhýijklmnoöpqrsþtuüvxwyz";
			$len = min( strlen( $str1 ), strlen( $str2 ) );
			for( $i = 0; $i < $len; $i ++ )
			{
				$s1 = strlen( $abc );
				$s2 = strlen( $abc );
				for( $j = 0; $j < strlen( $abc ); $j ++ ) if( $str1[ $i ] == $abc[ $j ] ) $s1 = $j;
				for( $k = 0; $k < strlen( $abc ); $k ++ ) if( $str2[ $i ] == $abc[ $k ] ) $s2 = $k;
				if( $s1 < $s2 ) return -1;
				else if( $s1 > $s2 ) return 1;
			}
			return 0;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function CompareArrayByDisplayName( $val1 , $val2 )
		{
			return $this->strcmp_tr( $val1["displayname"] , $val2["displayname"] );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function resetAccountData() {
			$this->add_record_field = array();
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setAccountData( $field_name , $field_value , $convert_utf8 ) {
			$field_array = array();

			if( is_array( $field_value ) ) {
				foreach( $field_value as $key => $value ) {
					if( $convert_utf8 == true )
						$this->add_record_field[ $field_name ][] = iconv( "ISO-8859-9" , "UTF-8" , $value );
					else
						$this->add_record_field[ $field_name ][] = $value;
				}
			}
			else {
				if( $convert_utf8 == true )
					$this->add_record_field[ $field_name ] = iconv( "ISO-8859-9" , "UTF-8" , $field_value );
				else
					$this->add_record_field[ $field_name ] = $field_value;
			}

		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setAccountAttributeData( $field_name , $field_value , $convert_utf8 ) {
			$field_array = array();

			if( is_array( $field_value ) ) {
				foreach( $field_value as $key => $value ) {
					if( $convert_utf8 == true )
						$field_array[$key] = array( iconv( "ISO-8859-9" , "UTF-8" , $value ) );
					else
						$field_array[$key] = array( $value );
				}

				$this->add_record_field[ $field_name ] = $field_array;
			}
			else {
				if( $convert_utf8 == true )
					$this->add_record_field[ $field_name ] = array( iconv( "ISO-8859-9" , "UTF-8" , $field_value ) );
				else
					$this->add_record_field[ $field_name ] = array( $field_value );
			}

		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setAccountObjectClassData( $field_array ) {
			foreach( $field_array as $key => $value ) {
				$this->add_record_field[ "objectclass" ][] = $value;
			}
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function get_ldap_error( $str ) {
			$errno = @ldap_errno( $this->connection );
			$last_error_message = @ldap_error( $this->connection );
			$extended_error = "";
			$last_error_extended = "nothing";

			if( @ldap_get_option( $this->connection, $this->LDAP_OPT_DIAGNOSTIC_MESSAGE , $extended_error ) )
				$last_error_extended = $extended_error;

			$out  = $str . PHP_EOL;
			$out .= "error no => " . $errno . PHP_EOL;
			$out .= "error message => " . $last_error_message . PHP_EOL;
			$out .= "extended error message => " . $extended_error . PHP_EOL;

			return $out;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function check_utf8( $str ) {
			$len = strlen($str);
			for( $i = 0; $i < $len; $i++ ) {
				$c = ord( $str[$i] );
				if( $c > 128 ) {
					if( ( $c > 247 ) ) return false;
					elseif( $c > 239 ) $bytes = 4;
					elseif( $c > 223 ) $bytes = 3;
					elseif( $c > 191 ) $bytes = 2;
					else return false;

					if( ( $i + $bytes ) > $len ) return false;
					while( $bytes > 1 ) {
						$i++;
						$b = ord( $str[ $i ] );
						if( $b < 128 || $b > 191 ) return false;
						$bytes--;
					}
				}
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ToTurkishStr( $InputStr) {
			$input_value = $InputStr;

			if( $this->check_utf8( $InputStr ) ) {
				$input_value = iconv( "UTF-8" , "ISO-8859-9" , $InputStr );
			}

			$OutStr = $input_value;

			for( $i = 0; $i < strlen( $OutStr ); $i ++ ) {
				if( $input_value[ $i ] == 'ý' ) $OutStr[ $i ] = 'i';
				else if( $input_value[ $i ] == 'I' ) $OutStr[ $i ] = 'i';
				else if( $input_value[ $i ] == 'ð' ) $OutStr[ $i ] = 'g';
				else if( $input_value[ $i ] == 'Ð' ) $OutStr[ $i ] = 'G';
				else if( $input_value[ $i ] == 'ü' ) $OutStr[ $i ] = 'u';
				else if( $input_value[ $i ] == 'Ü' ) $OutStr[ $i ] = 'U';
				else if( $input_value[ $i ] == 'þ' ) $OutStr[ $i ] = 's';
				else if( $input_value[ $i ] == 'Þ' ) $OutStr[ $i ] = 'S';
				else if( $input_value[ $i ] == 'Ý' ) $OutStr[ $i ] = 'I';
				else if( $input_value[ $i ] == 'ö' ) $OutStr[ $i ] = 'o';
				else if( $input_value[ $i ] == 'Ö' ) $OutStr[ $i ] = 'O';
				else if( $input_value[ $i ] == 'ç' ) $OutStr[ $i ] = 'c';
				else if( $input_value[ $i ] == 'Ç' ) $OutStr[ $i ] = 'C';
			}
			return $OutStr;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		function CapitalizeTr( $input_str ) {
			$input_value = $input_str;

			if( $this->check_utf8( $input_str ) ) {
				$input_value = iconv( "UTF-8" , "ISO-8859-9" , $input_str );
			}

			$input_value = trim( $input_value );
			$input_length = strlen( $input_value );
			$value = "";
			$cap_mode = true;
			$char_index = 0;

			$input_value = str_replace( "Ð" , "ð" , $input_value );
			$input_value = str_replace( "Ü" , "ü" , $input_value );
			$input_value = str_replace( "Þ" , "þ" , $input_value );
			$input_value = str_replace( "Ý" , "i" , $input_value );
			$input_value = str_replace( "I" , "ý" , $input_value );
			$input_value = str_replace( "Ö" , "ö" , $input_value );
			$input_value = str_replace( "Ç" , "ç" , $input_value );
			$input_value = strtolower( $input_value );

			while( $char_index < $input_length ) {
				if( $input_value[ $char_index ] == ' ' ) {
					$cap_mode = true;
					$value .= " ";
				}
				else if( $input_value[ $char_index ] == '.' ) {
					$cap_mode = true;
					$value .= ".";
				}
				else {
					if( $cap_mode == true ) {
						switch( $input_value[ $char_index ] ) {
							case "i"	: $value .= "Ý"; break;
							case "ý"	: $value .= "I"; break;
							case "ð"	: $value .= "Ð"; break;
							case "ü"	: $value .= "Ü"; break;
							case "þ"	: $value .= "Þ"; break;
							case "ö"	: $value .= "Ö"; break;
							case "ç"	: $value .= "Ç"; break;
							default		: $value .= strtoupper( $input_value[ $char_index ] );
						}

						$cap_mode = false;
					}
					else {
						$value .= $input_value[ $char_index ];
					}
				}
				$char_index ++;
			}
			return $value;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx


		//
		// ADD functions
		//

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddMainGroup() {
			$dirinfo = array();
			$dirinfo["objectClass"][0] = "top";
			$dirinfo["objectClass"][1] = "organization";
			$dirinfo["objectClass"][2] = "dcObject";
			$dirinfo["dc"] = $this->base_dc;
			$dirinfo["o"] = $this->base_dc;
			$dirinfo["description"] = "iyte main group";

			// add data to directory
			$this->log_write( "ldapadd --> dn: " . $this->base_dn );
			$result = ldap_add( $this->connection , $this->base_dn , $dirinfo );
			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldapadd failed" );
				$this->log_write( $this->error_message );
			}
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddGroup( $ugrpname , $udesc ) {
			$dirinfo = array();
			$dirinfo["ou"] = $ugrpname;
			$dirinfo["objectClass"][0] = "top";
			$dirinfo["objectClass"][1] = "organizationalUnit";
			$dirinfo["description"] = $udesc;

			// add data to directory
			$this->log_write( "ldapadd --> dn: ou=$ugrpname," . $this->base_dn );
			$result = @ldap_add( $this->connection , "ou=$ugrpname," . $this->base_dn , $dirinfo );

			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldapadd failed" );
				$this->log_write( $this->error_message );
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddAdminAccount( $uadminname , $udesc ) {
			$dirinfo = array();

			$dirinfo["objectClass"] = "organizationalRole";
			$dirinfo["cn"] = $uadminname;
			$dirinfo["description"] = $udesc;

			// add data to directory
			$this->log_write( "ldapadd --> dn: cn=$uadminname," . $this->base_dn );
			$result = ldap_add( $this->connection , "cn=$uadminname , " . $this->base_dn , $dirinfo );

			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldapadd failed" );
				$this->log_write( $this->error_message );
			}

		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx


		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddAccount( $v_ldap_group ) {
			$err_code = 0;

			$dirinfo = $this->add_record_field;
			// add data to directory
			$this->log_write( "ldapadd --> dn:cn=" . $dirinfo[ "cn" ] . ",ou=" . $v_ldap_group . "," . $this->base_dn );
			$result = ldap_add( $this->connection , "cn=" . $dirinfo[ "cn" ] . ",ou=" . $v_ldap_group . "," . $this->base_dn , $dirinfo );
			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldapadd failed" );
				$this->log_write( $this->error_message );
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddIyteAccount() {
			return $this->ldapAddAccount( "iyteusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddGuestAccount() {
			return $this->ldapAddAccount( "guestusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddSpclAccount() {
			return $this->ldapAddAccount( "spclusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddGroupOfNames( $group_name , $parent_group ) {
			$dirinfo["objectclass"] = "groupofnames";
			$dirinfo["cn"] = $group_name;
			$dirinfo["member"] = "cn=superuser,ou=" . $parent_group . "," . $this->base_dn;
			$dn = "cn=" . $group_name . ",ou=groups," . $this->base_dn;

			// add data to directory
			$this->log_write( "ldapadd --> " . $dn );
			$result = @ldap_add( $this->connection , $dn , $dirinfo );
			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldapadd failed" );
				$this->log_write( $this->error_message );
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddMember( $user , $user_group , $member , $member_group ) {
			$this->log_write( "ldap_mod_add --> cn=$user,ou=$user_group,cn=$member,ou=$member_group," . $this->base_dn );
			$dirinfo["member"] = "cn=$user,ou=$user_group," . $options[ "base_dn" ];
			$result = @ldap_mod_add( $this->connection  , "cn=$member,ou=$member_group," . $this->base_dn , $dirinfo  );

			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldap_mod_add failed" );
				$this->log_write( $this->error_message );
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx


		//
		// MODIFY functions
		//

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapModifyAccount( $uid , $group ) {
			$dn = "cn=" . $uid . ",ou=" . $group . "," . $this->base_dn;
			$this->log_write( "ldapmodify --> dn:" . $dn );
			$result = ldap_modify( $this->connection , $dn , $this->add_record_field );
			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldapmodify failed" );
				$this->log_write( $this->error_message );
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapModifyIyteAccount( $uid ) {
			return $this->ldapModifyAccount( $uid , "iyteusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapModifyGuestAccount( $uid ) {
			return $this->ldapModifyAccount( $uid , "guestusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapModifySpclAccount( $uid ) {
			return $this->ldapModifyAccount( $uid , "spclusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapDisableEduroam( $uid ) {
			$this->add_record_field = array();
			$this->add_record_field["eduroamenabled"] = 0;
			if( strpos( $uid , "@guest" ) > 0 ) {
				return $this->ldapModifyGuestAccount( $uid , "guestusers" );
			}
			return $this->ldapModifyIyteAccount( $uid , "iyteusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapEnableEduroam( $uid ) {
			$this->add_record_field = array();
			$this->add_record_field["eduroamenabled"] = 1;
			if( strpos( $uid , "@guest" ) > 0 ) {
				return $this->ldapModifyGuestAccount( $uid );
			}
			return $this->ldapModifyIyteAccount( $uid );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx


		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapAddAttribute( $uid , $group , $attr , $attr_value ) {
			$users_array = array();
			$update_array = array();
			$this->error_message = "";

			if( $uid == "" ) {
				$users_array = $this->ldapReadIyteAccountIds();
				foreach( $users_array as $key => $value ) {
					$update_array[ $attr ] = $value;
					$dn = "cn=" . $key . ",ou=" . $group . "," . $this->base_dn;
					$this->log_write( "ldapmodify --> dn:" . $dn );
					$result = @ldap_modify( $this->connection , $dn , $update_array );
					if( !$result ) {
						$this->error_message = $this->get_ldap_error( "ldapmodify failed" );
						$this->log_write( $this->error_message );
					}
				}
			}
			else {
				$update_array[ $attr ] = $attr_value;
				$dn = "cn=" . $uid . ",ou=" . $group . "," . $this->base_dn;
				$this->log_write( "ldapmodify --> dn:" . $dn );
				$result = @ldap_modify( $this->connection , $dn , $update_array );
				if( !$result ) {
					$this->error_message = $this->get_ldap_error( "ldapmodify failed" );
					$this->log_write( $this->error_message );
					return false;
				}
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapDeleteAttribute( $uid , $group , $attr ) {
			$update_array = array();
			$update_array[ $attr ] = array();
			$dn = "cn=" . $uid . ",ou=" . $group . "," . $this->base_dn;
			$this->log_write( "ldapmodify --> dn:" . $dn );
			$result = @ldap_modify( $this->connection , $dn , $update_array );
			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldapmodify failed" );
				$this->log_write( $this->error_message );
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//
		// DELETE functions
		//
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapDeleteAccount( $user , $group ) {
			$dn = "cn=" . $user . ",ou=" . $group . "," . $this->base_dn;
			$this->log_write( "ldapdelete --> dn:" . $dn );
			$result = ldap_delete( $this->connection , $dn );
			if( !$result ) {
				$this->error_message = $this->get_ldap_error( "ldapdelete failed" );
				$this->log_write( $this->error_message );
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapDeleteGuestAccount( $user ) {
			return $this->ldapDeleteAccount( $user , "guestusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapDeleteSpclAccount( $user ) {
			return $this->ldapDeleteAccount( $user , "spclusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapDeleteGroup( $ou ) {
			if( ( $ou == "groups") || ( $ou == "guestusers") || ( $ou == "iyteusers") || ( $ou == "spclusers" ) ) {
				$this->error_message = "Deleting Group < " . $ou . " > is denied.";
				return false;
			}

			$delete_dn = "ou=" . $ou . "," . $this->base_dn;
			$result = @ldap_delete( $this->connection , $delete_dn );

			if( !$result ) {
				$error_message = "delete_group\r\n";
				$error_message .= "group = " . $ou . "\r\n";
				$error_message .= $this->get_error_message();
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		//
		public function ldapDeleteGroupOfNames( $ou ) {
			if( ( $ou == "mail_admin_group" ) ||
				( $ou == "rehber_admin_group" ) ||
				( $ou == "rehber_client_group" ) ||
				( $ou == "tis_admin_group" ) ||
				( $ou == "tis_client_group" ) ||
				( $ou == "tis_std_client_group" ) ||
				( $ou == "vpn_admin_group" ) ||
				( $ou == "vpn_client_group" ) ) {
				$this->error_message = "Deleting group < " . $ou . " > is denied.";
				return false;
			}

			$delete_dn = "cn=" . $ou . ",ou=groups," . $this->base_dn;
			$result = @ldap_delete( $this->connection , $delete_dn );

			if( !$result ) {
				$error_message = "delete_group_of_names\r\n";
				$error_message .= "group of names = " . $ou . "\r\n";
				$error_message .= $this->get_error_message();
				return false;
			}
			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadGroupOfNamesMembers( $groupofnames ) {
			$search_array = array();
			$search_dn = "ou=groups,dc=iyte,dc=edu,dc=tr";

			$result = @ldap_search( $this->connection , $search_dn , "cn=" . $groupofnames );
			if( !$result ) {
				$error_message = get_error_message();
				return $search_array;
			}

			$found_entry_count = @ldap_count_entries(  $this->connection , $result );
			if( $found_entry_count == 0 ) return $search_array;

			$entries = @ldap_get_entries( $this->connection , $result );
			$found_entry_count = $entries[0]["member"]["count"];

			for( $ix = 0; $ix < $found_entry_count; $ix ++ ) {
				$strarr = explode( "=" , $entries[0]["member"][$ix] , 2 );
				$strarr2 = $strarr[1];
				$strarr = explode( "," , $strarr2 , 11 );
				$strarr[1] = str_replace( "ou=" , "" , $strarr[1] );
				if( $strarr[0] != "superuser" )
					$search_array[ $strarr[0] ] = $strarr[1];
			}

			ksort( $search_array );
			$entries = array();

			return $search_array;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		public function ldapRemoveMember( $ugrpname , $vpnusername , $vpngrpname , $user ) {
			$dn_suffix = "cn=" . $vpnusername . " , ou=" . $vpngrpname . "," . $this->base_dn;
			$filter_suffix = "cn=" . $user;
			$justthese = array( "member" );

			$sr = @ldap_search( $this->connection , $dn_suffix , $filter_suffix , $justthese );
			if( $sr ) {
				$dirinfo["member"] = "cn=$user,ou=$ugrpname," . $this->base_dn;
				$result = @ldap_mod_del( $this->connection  , $dn_suffix , $dirinfo  );
				if( !$result ) {
					$this->error_message = $this->get_ldap_error( "ldap_mod_del failed" );
					$this->log_write( $this->error_message );
					ldap_free_result( $sr );
					return false;
				}
				ldap_free_result( $sr );
				return true;
			}
			else {
				return false;
			}
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx





		//
		// READ AND SEARCH functions
		//

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// FINISHED
		function ldapReadAttribute( $uemailaddr , $thisattr ) {
			$sr = ldap_search( $this->connection , "ou=iyteusers," . $this->base_dn , "cn=" . $uemailaddr . "*" );
			if( $sr ) {
				$dir_info = ldap_get_entries( $this->connection , $sr );
				if( ldap_count_entries( $this->connection , $sr ) > 0 ) {
					if( check_utf8( $dir_info[0][$thisattr][0] ) )
						return iconv( "UTF-8" , "ISO-8859-9" , $dir_info[0][$thisattr][0] );
					else
						return $dir_info[0][$thisattr][0];
				}
			}
			return "";
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

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadAccountIds( $usergroup ) {
			$result_array = array();
			setlocale( LC_ALL , "tr_TR.iso88599" );
			$search_filter = "";

			$sr = ldap_search( $this->connection , "ou=$usergroup," . $this->base_dn , "(cn=*)" , array("cn") );
			if( $sr ) {
				$result_count = ldap_count_entries( $this->connection , $sr );
				if( $result_count == 0 ) return false;
				$dir_info = ldap_get_entries( $this->connection , $sr );

				$result_count = $dir_info["count"];
				for( $ix = 0; $ix < $result_count; $ix ++ ) {
					$kkk = $dir_info[ $ix ]["cn"][0];
					if( strpos( $kkk , "@iyte.edu.tr" ) > 0 )
						$result_array[ $kkk ] = "1073741824";
					else $result_array[ $kkk ] = "1";
				}
			}
			return $result_array;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadAccounts( $usergroup , $srch_attr ) {
			$result_array = array();

			setlocale( LC_ALL , "tr_TR.iso88599" );
			$ldap_users = array();
			$search_filter = "";

			if( is_array( $srch_attr) ) {
				foreach( $srch_attr as $key => $value ) {
					$search_filter .= iconv( "ISO-8859-9" , "UTF-8" , $value );
				}
			}
			else $search_filter = $srch_attr;

			$sr = ldap_search( $this->connection , "ou=$usergroup," . $this->base_dn , $search_filter );
			if( $sr ) {
				$result_count = ldap_count_entries( $this->connection , $sr );
				if( $result_count == 0 ) return false;
				$dir_info = ldap_get_entries( $this->connection , $sr );
				//print_r( $dir_info );

				$result_count = $dir_info["count"];
				for( $ix = 0; $ix < $result_count; $ix ++ ) {
					$item_count = $dir_info[ $ix ]["count"];
					for( $iy = 0; $iy < $item_count; $iy ++ ) {
						$value_name = $dir_info[ $ix ][$iy];
						$value = $dir_info[ $ix ][$value_name];
						if( is_array( $value ) ) {
							//$arr_count = count( $value );
							$arr_count = $value["count"];
							if( $arr_count == 1 ) {
								$result_array[$ix][$value_name] = $value[0];
								if( $this->check_utf8( $value[0] ) )
									$result_array[$ix][$value_name] = iconv( "UTF-8" , "ISO-8859-9" , $value[0] );
							}
							else {
								for( $iz = 0; $iz < $arr_count; $iz ++ ) {
									$result_array[$ix][$value_name][$iz] = $value[$iz];
									if( $this->check_utf8( $value[$iz] ) )
										$result_array[$ix][$value_name][$iz] = iconv( "UTF-8" , "ISO-8859-9" , $value[$iz] );
								}
							}
						}
						else {
							$result_array[$ix][$value_name] = $value;
							if( $this->check_utf8( $value[$iz] ) )
								$result_array[$ix][$value_name] = iconv( "UTF-8" , "ISO-8859-9" , $value );
						}
					}
				}
			}

			if( count( $result_array ) > 0 ) {
				usort( $result_array , array( $this , "CompareArrayByDisplayName" ) );
			}

			return $result_array;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadIyteAccountIds() {
			return $this->ldapReadAccountIds( "iyteusers" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadIyteAccounts( $srch_attr ) {
			$filter = "(&(acct-silinecek-date=;)(cn=*@iyte.edu.tr)(|";
			$s1 = strtolower( $this->ToTurkishStr( $srch_attr ) );
			$filter .= "(mail=*$s1*)";
			$s1 = $this->CapitalizeTr( $srch_attr );
			$s1 = iconv( "ISO-8859-9" , "UTF-8" , $s1 );
			$filter .= "(displayname=*$s1*)";
			$filter .= "))";
			//print $filter;

			return $this->ldapReadAccounts( "iyteusers" , $filter );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadGuestAccounts() {
			return $this->ldapReadAccounts( "guestusers" , "(cn=*@guest.iyte.edu.tr)" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadSpclAccounts() {
			return $this->ldapReadAccounts( "spclusers" , "(cn=*@spcl.iyte.edu.tr)" );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadAccount( $usergroup , $uid ) {
			$result_array = array();
			setlocale( LC_ALL , "tr_TR.iso88599" );

			$sr = ldap_search( $this->connection , "ou=$usergroup," . $this->base_dn , "cn=" . $uid . "*" );
			if( $sr ) {
				$result_count = ldap_count_entries( $this->connection , $sr );
				if( $result_count == 0 ) {
					$this->error_message = "The user $uid could not be found in group $usergroup <br>" . PHP_EOL;
					return false;
				}
				$dir_info = ldap_get_entries( $this->connection , $sr );
				//print_r( $dir_info );

				$item_count = $dir_info[ 0 ]["count"];
				for( $iy = 0; $iy < $item_count; $iy ++ ) {
					$value_name = $dir_info[ 0 ][$iy];
					$value = $dir_info[ 0 ][$value_name];
					if( is_array( $value ) ) {
						$arr_count = count( $value );
						if( $arr_count == 1 ) {
							$result_array[$value_name] = $value[0];
							if( check_utf8( $value[$iz] ) ) $result_array[$value_name] = iconv( "UTF-8" , "ISO-8859-9" , $value[0] );
						}
						else {
							for( $iz = 0; $iz < $arr_count; $iz ++ ) {
								$result_array[$value_name][$iz] = $value[$iz];
								if( check_utf8( $value[$iz] ) ) $result_array[$value_name][$iz] = iconv( "UTF-8" , "ISO-8859-9" , $value[$iz] );
							}
						}
					}
					else {
						$result_array[$value_name] = $value;
						if( check_utf8( $value[$iz] ) ) $result_array[$value_name] = iconv( "UTF-8" , "ISO-8859-9" , $value );
					}
				}
			}

			if( !empty( $result_array ) ) {
				usort( $result_array , array( $this , "CompareArrayByDisplayName" ) );
			}

			return $result_array;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadIyteAccount( $uid ) {
			return $this->ldapReadAccount( "iyteusers" , $uid );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadGuestAccount( $uid ) {
			return $this->ldapReadAccount( "guestusers" , $uid );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadSpclAccount( $uid ) {
			return $this->ldapReadAccount( "spclusers" , $uid );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadGroups() {
			$search_array = array();
			$found_entry_count = 0;
			$just_these = array( "ou" );

			$search_dn = "dc=iyte,dc=edu,dc=tr";
			$srch_filter = "ou=*";

			$result = @ldap_list( $this->connection , $search_dn , $srch_filter , $just_these );
			if( !$result ) {
				$error_message = $this->get_error_message();
				return $search_array;
			}

			$found_entry_count = ldap_count_entries(  $this->connection , $result );

			if( $found_entry_count == 0 ) return $search_array;

			$entries = @ldap_get_entries( $this->connection , $result );
			$search_array = array();

			for( $ix = 0; $ix < $found_entry_count; $ix ++ ) {
				$search_array[] = $entries[ $ix ]["ou"][0];
			}

			sort( $search_array );
			$entries = array();

			return $search_array;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function ldapReadGroupOfNames() {
			$search_array = array();
			$found_entry_count = 0;

			$search_array = array();
			$search_dn = "ou=groups,dc=iyte,dc=edu,dc=tr";

			$result = @ldap_search( $this->connection , $search_dn , "cn=*" );
			if( !$result ) {
				$error_message = get_error_message();
				return $search_array;
			}

			$found_entry_count = @ldap_count_entries(  $this->connection , $result );

			if( $found_entry_count == 0 ) return $search_array;

			$entries = @ldap_get_entries( $this->connection , $result );
			$search_array = array();

			for( $ix = 0; $ix < $found_entry_count; $ix ++ ) {
				$search_array[] = $entries[ $ix ]["cn"][0];
			}

			sort( $search_array );
			$entries = array();

			return $search_array;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

	}

?>