<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "queryaplist" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$DbUtil->setDatabase("network");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_ap_info_all();
	if( empty( $result_array ) ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Info tablosunda herhangi bir kayýt bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Ip Address" ,
		"Host Name" ,
		"Description" ,
		"Interface Count" ,
		"Default Gateway" ,
		"Up Time" ,
		"Contact" ,
		"Location" ,
		"Last Change Time" ));
	foreach( $result_array as $key => $value ) {
		$out_result .= $nm_web_interface->create_table_row( array(
			$key ,
			$value["host_name"] ,
			$value["description"] ,
			$value["interface_count"] ,
			$value["default_gateway"] ,
			$value["up_time"] ,
			$value["contact"] ,
			$value["location"] ,
			$value["last_change"] ));
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>