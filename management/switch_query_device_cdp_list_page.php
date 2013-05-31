<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "queryswitchcdplist" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryswitchcdplist" );
	$DbUtil->setDatabase("network");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_switch_cdp_list( $query_text );
	if( empty( $result_array ) ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Cdp tablosunda $query_text i�in herhangi bir kay�t bulunamad� !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Switch Name",
		"Interface Name",
		"Remote Ip",
		"Remote Name",
		"Remote Interface",
		"Remote Platform" ));
	foreach( $result_array as $key => $value ) {
		foreach( $value as $key_x => $value_x ) {
			$out_result .= $nm_web_interface->create_table_row( array(
				$key,
				$key_x,
				$value_x["remote_ip"],
				$value_x["remote_name"],
				$value_x["remote_interface"],
				$value_x["remote_platform"] ));
		}
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>