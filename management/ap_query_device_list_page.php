<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "queryaplist" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$DbUtil->setDatabase("network");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_ap_list();
	if( count( $result_array ) == 0 ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Cihazlar tablosunda AP ler için herhangi bir kayýt bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"AP Ip Address" ,
		"AP Name" ));
		//"AP Status" ));
	foreach( $result_array as $key => $value ) {
		$out_result .= $nm_web_interface->create_table_row( array(
   		$key,
		$value["name"] ));
		//$value["status"] ));
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>