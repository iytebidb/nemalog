<?php

	class clsLOG {
		private $log_directory;
		private $file_id;
		private $file_name = "";
		private $file_id_list = array();
		private $log_time;
		private $log_handle;

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// Done
		public function __construct() {
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// Done
		public function __destruct() {
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		public function setLogDirectory( $dir , $logid ) {
			$this->log_directory = $dir;
			if( !is_dir( $this->log_directory . $logid ) ) {
				mkdir( $this->log_directory . $logid );
			}

			$this->log_time = date( "Y-m-d-H" );
			$this->file_name = $this->log_directory . $logid . "/" . $this->log_time . ".log";
		}

		public function Start() {
			ini_set('track_errors', 1);
			$this->log_handle = fopen( $this->file_name , "a+" );
			if( $this->log_handle ) {
				fputs( $this->log_handle , "\r\n" );
				fputs( $this->log_handle , "********************************************************************************\r\n" );
				fputs( $this->log_handle , "Logging started at " . date("Y/m/d G:i:s") . "\r\n" );
				fputs( $this->log_handle , "********************************************************************************\r\n" );
				fputs( $this->log_handle , "\r\n" );
			}
			else {
				$this->error_message = $php_errormsg;
				print $this->error_message;
			}
			ini_set('track_errors', 0);
		}

		public function Stop() {
			$str  = "******************************************************************************\r\n";
			$str .= " LOG Stopped at " . date("d/m/Y , H:i:s") . "\r\n";
			$str .= "******************************************************************************\r\n";
			$str .= "\r\n\r\n\r\n";
			if( $this->log_handle ) {
				fputs( $this->log_handle , $str );
				fclose( $this->log_handle );
			}

			$this->log_handle = null;
		}

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		// Done
		public function Write( $msg ) {
			$writestr = date("Y/m/d G:i:s") . " --> ";
			$writestr .= $msg . "\r\n";

			ini_set('track_errors', 1);
			//$this->log_handle = fopen( $this->file_name , "a+" );
			if( $this->log_handle ) {
				fputs( $this->log_handle , $writestr );
			}
			else {
				$this->error_message = $php_errormsg;
			}

			ini_set('track_errors', 0);
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		public function getLogHandle() {
			return $this->log_handle;
		}

	}

?>