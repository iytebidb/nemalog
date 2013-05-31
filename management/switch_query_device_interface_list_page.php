<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "queryswitchinterfacelist" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryswitchinterfacelist" );
	$DbUtil->setDatabase("network");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_switch_interface_list( $query_text );
	if( empty( $result_array ) ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Interface tablosunda $query_text i�in herhangi bir kay�t bulunamad� !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Switch Name",
		"Interface Name",
		"Vlan",
		"Trunking",
		"Admin State",
		"Current State",
		"Down Reason" ));
	foreach( $result_array as $key => $value ) {
		foreach( $value as $key_x => $value_x ) {
			$out_result .= $nm_web_interface->create_table_row( array(
				$key ,
				$key_x,
				$value_x["vlan"],
				$value_x["trunk"],
				$value_x["admin_status"],
				$value_x["oper_status"],
				$value_x["additional_oper_status"] ));
		}
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>