<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "queryswitchlist" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryswitchlist" );
	$DbUtil->setDatabase("network");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_switch_list();
	if( empty( $result_array ) ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Cihazlar tablosunda Switchler için herhangi bir kayýt bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Switch Ip Address",
		"Switch Name" ));
	foreach( $result_array as $key => $value ) {
		$out_result .= $nm_web_interface->create_table_row( array(
			$key ,
			$value["name"] ));
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>