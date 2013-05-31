<?php

	//$path = "d:/sourcecodes/";
	$path = "/iyte/run/";
	date_default_timezone_set( "Europe/Istanbul" );
	include_once( $path . "classes/clsSWITCH.php");
	include_once( $path . "classes/clsSWITCHDB.php");
	include_once( $path . "classes/clsLOG.php");

	function print_array( $this_array , $this_text ) {
		print $this_text . PHP_EOL;
		print_r( $this_array );
		print PHP_EOL;
	}

	$switch_ip = null;
	$switch_host = "sw-unknown";
	if( array_key_exists( 1 , $argv ) ) $switch_ip = $argv[1];
	if( array_key_exists( 2 , $argv ) ) $switch_host = $argv[2];

	//print "switch ip = " . $switch_ip . PHP_EOL;
	//print "switch host = " . $switch_host . PHP_EOL;
	//die();

	$switch_list = array();
	$switch = new clsSWITCH();
	$switch->setSnmpCommunity( "switch snmp string" );
	$switchDb = new clsSWITCHDB();
	if( !$switchDb->connect() ) die();

	$rdate = date("Y.m.d");
	$rtime = date("H:i:s");
	$switchDb->setStartDatetime( $rdate , $rtime );

	if( is_null( $switch_ip ) ) {
		$switch_list = $switchDb->query_device_list( null , 1 , 1 );

		foreach( $switch_list as $ip => $host ) {
			$switch->setIp( $ip );
			$switchDb->setIpAddress( $ip );
			$switchDb->setSwitchHost( $host );
			$switch->getInterfaces();
			$switchDb->write_interfaces( $switch->interfaces );
		}
	}
	else {
		$switch->setIp( $switch_ip );
		$switchDb->setIpAddress( $switch_ip );
		$switchDb->setSwitchHost( $switch_host );
		$switch->getInterfaces();
		$switchDb->write_interfaces( $switch->interfaces );
	}

?>