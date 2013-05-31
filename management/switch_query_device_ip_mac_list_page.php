<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "queryswitchipmaclist" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryswitchipmaclist" );
	$DbUtil->setDatabase("network");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_switch_ip_mac_list( $query_text );
	if( empty( $result_array ) ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Ip-Mac tablosunda $query_text için herhangi bir kayýt bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Switch Ip",
		"Interface Name",
		"Vlan",
		"Mac Address",
		"Ip Address" ));
	foreach( $result_array as $key => $value ) {
		foreach( $value as $key_x => $value_x ) {
			foreach( $value_x as $key_y => $value_y ) {
				$out_result .= $nm_web_interface->create_table_row( array(
				$key ,
				$key_x ,
				$value_y["vlan"] ,
				$key_y ,
				$value_y["ip_address"] ));
			}
		}
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>