<?php

	// avea => 459532
	$path = "d:/sourcecodes/";
	//$path = "/iyte/run/";
	date_default_timezone_set( "Europe/Istanbul" );
	include_once( $path . "classes/clsSWITCH.php");
	include_once( $path . "classes/clsSWITCHDB.php");
	include_once( $path . "classes/clsLOG.php");

	function print_array( $this_array , $this_text ) {
		print $this_text . PHP_EOL;
		print_r( $this_array );
		print PHP_EOL;
	}

	$log = new clsLOG();
	$log->setLogDirectory( "/iyte/log/" , "swipmac" );
	$log->start();
	$switch_list = array();
	$backbone = new clsSWITCH();
	$switch = new clsSWITCH();
	$backbone->setSnmpCommunity( "switch snmp string" );
	$switch->setSnmpCommunity( "switch snmp string" );
	$switchDb = new clsSWITCHDB();
	if( !$switchDb->connect() ) die();

	$ip = "10.10.1.1";
	$backbone->setIp( $ip );
	$backbone->setLogHandle( $log->getLogHandle() );
	$switchDb->setLogHandle( $log->getLogHandle() );
	$backbone->getIpMacTable();

	$switch_list = $switchDb->query_device_list( null , 1 , 1 );

	$rdate = date("Y.m.d");
	$rtime = date("H:i:s");
	$switchDb->setStartDatetime( $rdate , $rtime );

	foreach( $switch_list as $ip => $host ) {
		$switch->setIp( $ip );
		$switch->setLogHandle( $log->getLogHandle() );
		$switchDb->setIpAddress( $ip );
		$switchDb->setSwitchHost( $host );
		$switchDb->setLogHandle( $log->getLogHandle() );

		$switch->getMacTable( $backbone->ip_mac_table );
		$switchDb->write_mac_table( $switch->mac_table );
	}
	$log->stop();

?>