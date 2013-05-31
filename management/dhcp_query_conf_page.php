<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "querydhcp" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$DbUtil->setDatabase("dhcp");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_dhcp_conf();
	if( count( $result_array ) == 0 ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Dhcp veritabanýnda Host tablosu bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Authorative",
		"Ddns Ttl",
		"Ddns Updates",
		"Ddns Update Style",
		"Default Lease Time",
		"Log Facility",
		"Max Lease Time",
		"One Lease Per Client",
		"Option Domain Name",
		"Update Static Leases" ));

	$out_result .= $nm_web_interface->create_table_row( array(
		$result_array["authoritative"] ,
		$result_array["ddns_ttl"] ,
		$result_array["ddns_updates"] ,
		$result_array["ddns_update_style"] ,
		$result_array["default_lease_time"] ,
		$result_array["log_facility"] ,
		$result_array["max_lease_time"] ,
		$result_array["one_lease_per_client"] ,
		$result_array["option_domain-name"] ,
		$result_array["update_static_leases"] ));
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>