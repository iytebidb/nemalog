<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "querydhcp" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$DbUtil->setDatabase("dhcp");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_dhcp_host();
	if( count( $result_array ) == 0 ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Dhcp veritabanýnda Host tablosu bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Host",
		"Subnet",
		"Subnet Name",
		"Fixed Address Start",
		"Ethernet Mac Address",
		"Status" ));
	foreach( $result_array as $key => $value ) {
		$out_result .= $nm_web_interface->create_table_row( array(
   		$key,
		$value["subnet"],
		$value["subnet_name"],
		$value["fixed_address"],
		$value["hardware_ethernet"],
		$value["status"] ) );
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>