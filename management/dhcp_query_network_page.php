<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "querydhcp" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$DbUtil->setDatabase("dhcp");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_dhcp_network();
	if( empty( $result_array ) ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Dhcp veritabanýnda Network tablosu bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Subnet" ,
		"Netmask" ,
		"Network" ,
		"Range Start" ,
		"Range End" ,
		"Ddns Updates" ,
		"Ddns Domain Name" ,
		"Option Ddns Domain Name" ,
		"Option Routers" ,
		"Option Broadcast Address" ));
	foreach( $result_array as $key => $value ) {
		$out_result .= $nm_web_interface->create_table_row( array(
   		$key,
		$value["netmask"],
		$value["network"],
		$value["range_start"],
		$value["range_end"],
		$value["ddns_updates"],
		$value["ddns_domain_name"],
		$value["option_domain_name_servers"],
		$value["option_routers"],
		$value["option_broadcast_address"] ) );
	}
	$out_result .= $nm_web_interface->end_create_table();


	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>