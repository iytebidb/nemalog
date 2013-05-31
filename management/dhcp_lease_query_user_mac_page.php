<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "querydhcp" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$DbUtil->setDatabase("dhcp");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_dhcp_lease_mac( $dhcpfilter );
	if( count( $result_array ) == 0 ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Lease tablosunda " . $dhcpfilter . " için herhangi bir kayýt bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Lease Ip Address",
		"Ethernet Mac Address",
		"Start Date Time",
		"End Date Time",
		"Tstp Date Time",
		"Binding State",
		"Next Binding State",
		"Uid",
		"User Host Name",
		"Ddns Text",
		"Ddns Forward Text" ));
	foreach( $result_array as $key => $value ) {
		foreach( $value as $key_x => $value_x ) {
			$out_result .= $nm_web_interface->create_table_row( array(
				$key ,
				$value_x["hardware_ethernet"] ,
				$value_x["starts_date"] . " " . $value_x["starts_time"] ,
				$value_x["ends_date"] . " " . $value_x["ends_time"] ,
				$value_x["tstp_date"] . " " . $value_x["tstp_time"] ,
				$value_x["binding_state"] ,
				$value_x["next_binding_state"] ,
				$value_x["uid"] ,
				$value_x["user_host_name"] ,
				$value_x["ddns_txt"] ,
				$value_x["ddns_fwd_name"] ) );
		}
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

	?>