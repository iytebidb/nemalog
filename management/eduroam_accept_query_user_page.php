<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "queryeduroamacceptuser" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryeduroamacceptuser" );
	$DbUtil->setDatabase("eduroam");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_eduroam_accept( $eduroamfilter , 1000 );
	if( empty( $result_array ) ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Accept tablosunda $query_text için herhangi bir kayýt bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Authentication Time",
		"Client AP",
		"User Name",
		"Mac Address",
		"Dhcp Ip Address" ));
	foreach( $result_array as $key => $value ) {
		$out_result .= $nm_web_interface->create_table_row( array(
			$value["auth_date"],
			$value["ap_ip_address"],
			$value["user_name"],
			$value["user_mac_address"],
			$value["user_ip_address"] ));
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>