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

	$backbone = new clsSWITCH();
	$backbone->setSnmpCommunity( "switch snmp string" );
	$switchDb = new clsSWITCHDB();
	if( !$switchDb->connect() ) die();

	$rdate = date("Y.m.d");
	$rtime = date("H:i:s");
	$switchDb->setStartDatetime( $rdate , $rtime );

	$backbone->setIp( "10.10.1.1" );
	$switchDb->setIpAddress( "10.10.1.1" );
	$switchDb->setSwitchHost( "sw-backbone" );
	$backbone->getCdpTable();
	$switchDb->write_cdp_table( $backbone->cdp_table );

?>