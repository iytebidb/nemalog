<?php

	$result_array = array();
	$user_found = false;
	$out_result = "";

	$Log->setLogDirectory( "D:/iyte/log/" , "findmac" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$Log->Start();
	$Switch->setLogHandle( $Log->getLogHandle() );
	$Switch->setIp( $switch_ip );


	if( !$Switch->shutdown_port( $switch_port , "Port was shutdown for security reason ..." ) ) {
		$out_result = $nm_web_interface->create_error_message( "Snmp Hatasý" , "Port could not be shutdown !!!" );
		print $out_result;
		$Log->Stop();
		die();
	}

	$out_result = $nm_web_interface->prefix_success . "Port was sucessfully shutdown";
	$query_out = $nm_web_interface->encode_str( $out_result , true );
	print $query_out;

	$Log->Stop();

?>