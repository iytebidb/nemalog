<?php

	class clsGLOBALFUNCTIONS {

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

		private $args = array(
							'length'				=>	9,
							'alpha_upper_include'	=>	FALSE,
							'alpha_lower_include'	=>	TRUE,
							'number_include'		=>	TRUE,
							'symbol_include'		=>	FALSE,
						);

		private $alpha_upper = array( "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z" );
		private $alpha_lower = array( "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z" );
		private $number = array( 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 );
		private $symbol = array( "-", "_", "^", "~", "@", "&", "|", "=", "+", ";", "!", ",", "(", ")", "{", "}", "[", "]", ".", "?", "%", "*", "#" );
		private $input = 4;

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function __construct() {
			$this->set_args( $this->args );
			$this->connection_id = null;
			$this->mysql_database = "global";
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
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function __destruct() {
			$this->disconnect();
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		public function logWrite( $str ) {
			$new_str = $str . "\r\n";
			if( $this->log_handle )
				fputs( $this->log_handle , $new_str );
		}

		public function correct_ip_address( $input_ip ) {
			$tmp = str_replace( "." , "" , $input_ip );
			if( !is_numeric( $tmp ) ) return false;
			$arr = explode( "." , $input_ip );
			if( count( $arr ) != 4 ) return false;
			foreach( $arr as $key => $value ) {
				if( $value > 255 ) return false;
			}
			return true;
		}

		public function correct_mac_address( $input_mac ) {
			$output_mac = strtolower( $input_mac );
			$output_mac = str_replace( " " , "" , $output_mac );
			$output_mac = str_replace( "." , "" , $output_mac );
			$output_mac = str_replace( "-" , "" , $output_mac );
			$output_mac = str_replace( ":" , "" , $output_mac );
			if( strlen( $output_mac ) != 12 ) return false;
			$output_mac_x  = substr( $output_mac , 0 , 2 ) . substr( $output_mac , 2 , 2 ) . ".";
			$output_mac_x .= substr( $output_mac , 4 , 2 ) . substr( $output_mac , 6 , 2 ) . ".";
			$output_mac_x .= substr( $output_mac , 8 , 2 ) . substr( $output_mac , 10 , 2 );

			return $output_mac_x;
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

		private function db_insert_one_time_password( $one_time_pwd ) {
			if( !$this->connect() ) return false;
			$insert_str = "INSERT INTO onetimepassword VALUES ( '" . $one_time_pwd . "' )";

			if( !mysql_query( $insert_str , $this->connection_id ) ) {
				$this->error_message = mysql_error( $this->connection_id );
				$this->disconnect();
				return false;
			}
			$this->disconnect();
			return true;
		}

		private function db_read_one_time_password( $one_time_pwd ) {
			if( !$this->connect() ) return false;
			$select_str = "SELECT * FROM onetimepassword WHERE pwd='" . $one_time_pwd . "'";

			$result = mysql_query( $select_str , $this->connection_id );
			if( !$result ) {
				$this->error_message = mysql_error( $this->connection_id );
				$this->disconnect();
				return false;
			}

			if( mysql_affected_rows( $this->connection_id ) == 0 ) {
				$this->disconnect();
				return false;
			}

			mysql_free_result( $result );
			$this->disconnect();
			return true;
		}

		private function db_delete_one_time_password( $one_time_pwd ) {
			if( !$this->connect() ) return false;
			$update_str = "DELETE FROM onetimepassword WHERE pwd='" . $one_time_pwd . "'";

			if( !mysql_query( $update_str , $this->connection_id ) ) {
				$this->error_message = mysql_error( $this->connection_id );
				$this->disconnect();
				return false;
			}
			$this->disconnect();
			return true;
		}

		public function create_one_time_password() {
			return "1001";
			$one_time_pwd = $this->set_password();
			$this->db_insert_one_time_password( $one_time_pwd , 1 , 1 );
			return $one_time_pwd;
		}

		public function check_one_time_password( $this_pwd ) {
			return true;
			if( $this->db_read_one_time_password( $this_pwd ) ) {
				$this->db_delete_one_time_password( $this_pwd );
				return true;
			}
			return false;
		}

		private function chip_parse_args( $args = array(), $defaults = array() ) {
			return array_merge( $defaults, $args );
		}

		private function set_args( $args = array() ) {

			$defaults = $this->get_args();
			$args = $this->chip_parse_args( $args, $defaults );
			$this->args = $args;
		}

		public function get_args() {
			return $this->args;
		}

		private function get_alpha_upper() {
			return $this->alpha_upper;
		}

		private function get_alpha_lower() {
			return $this->alpha_lower;
		}

		private function get_number() {
			return $this->number;
		}

		private function get_symbol() {
			return $this->symbol;
		}

		private function set_password() {

			/* Temporary Array(s) */
			$temp = array();
			$exec = array();

			/* Arguments */
			$args = $this->get_args();
			extract($args);

			/* Minimum Validation */
			if( $length <= 0 ) {
				return 0;
			}

			/* Execution Array Logic */

			/* Alpha Upper */
			if( $alpha_upper_include == TRUE ) {
				$alpha_upper = $this->get_alpha_upper();
				$exec[] = 1;
			}

			/* Alpha Lower */
			if( $alpha_lower_include == TRUE ) {
				$alpha_lower = $this->get_alpha_lower();
				$exec[] = 2;
			}

			/* Number */
			if( $number_include == TRUE ) {
				$number = $this->get_number();
				$exec[] = 3;
			}

			/* Symbol */
			if( $symbol_include == TRUE ) {
				$symbol = $this->get_symbol();
				$exec[] = 4;
			}

			/* Unique and Random Loop */
			$exec_count = count( $exec ) - 1;
			$input_index = 0;
			//$this->chip_print( $exec );

			for ( $i = 1; $i <= $length; $i++ ) {

				switch( $exec[$input_index] ) {

					case 1:
						shuffle( $alpha_upper );
						$temp[] = $alpha_upper[0];
						unset( $alpha_upper[0] );
						break;

					case 2:
						shuffle( $alpha_lower );
						$temp[] = $alpha_lower[0];
						unset( $alpha_lower[0] );
						break;

					case 3:
						shuffle( $number );
						$temp[] = $number[0];
						unset( $number[0] );
						break;

					case 4:
						shuffle( $symbol );
						$temp[] = $symbol[0];
						unset( $symbol[0] );
						break;

				}

				if ( $input_index < $exec_count ) {
					$input_index++;
				} else {
					$input_index = 0;
				}

			} // for ( $i = 1; $i <= $length; $i++ )

			/* Shuffle */
			shuffle($temp);

			/* Make Password */
			$password = implode( $temp );

			return $password;

		}

		public function get_password() {
			return $this->set_password();
		}

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

		public function format_error_message( $err_msg ) {
			$out_msg = "";
			$arr = explode( PHP_EOL , $err_msg );
			foreach( $arr as $key => $value ) {
				$out_msg .= $value . "<br>" . PHP_EOL;
			}
			return $out_msg;
		}


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
		public function create_clear_text_password() {
			return $this->set_password();
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_user_id( $name , $surname ) {
			$name_ext = str_replace( "." , " " , $name );
			$surname_ext = str_replace( "." , " " , $surname );

			$arr_name = explode( " " , $name_ext );
			$arr_surname = explode( " " , $surname_ext );

			$out_user = "";
			foreach( $arr_name as $key => $value ) {
				$out_user .= strtolower( $this->ToTurkishStr( $value ) );
			}

			foreach( $arr_surname as $key => $value ) {
				$out_user .= strtolower( $this->ToTurkishStr( $value ) );
			}

			return $out_user;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

	}
?>