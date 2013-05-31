<?php

	$result_array = array();
	$user_found = false;
	$out_result = "";

	$check = $global_functions->correct_ip_address( $findip );
	if( !$check ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , $findip . " -> Hatalý IP adres. Lütfen, düzeltip tekrar giriniz !!!" );
		print $out_result;
		die();
	}

	$Log->setLogDirectory( "D:/iyte/log/" , "finduser" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "finduser" );
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	$found_in_eduroam = false;
	$found_in_dhcp_lease = false;
	$found_in_switch_port = false;

	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$DbUtil->setDatabase( "eduroam" );
	$result_array = $DbUtil->query_eduroam_log_ip( $findip );
	if( empty( $result_array ) ) {
		$DbUtil->setDatabase( "dhcp" );
		$query_result_dhcp_lease = $DbUtil->query_dhcp_lease_log_ip( $findip );
		$query_result_dhcp_host = $DbUtil->query_dhcp_host_log_ip( $findip );
		foreach( $query_result_dhcp_host as $key => $value ) {
			$result_array[ $key ] = $value;
		}

		foreach( $query_result_dhcp_lease as $key => $value ) {
			$result_array[ $key ] = $value;
		}
		$query_result_dhcp_host = array();
		$query_result_dhcp_lease = array();

	}

	$DbUtil->setDatabase( "network" );
	$query_result_switch = $DbUtil->query_switch_connected_port_ip( $findip );
	foreach( $query_result_switch as $key => $value ) {
		$result_array[ $key ]["switch_ip_address"] = $query_result_switch[ $key ]["switch_ip_address"];
		$result_array[ $key ]["switch_name"] = $query_result_switch[ $key ]["switch_name"];
		$result_array[ $key ]["switch_port"] = $query_result_switch[ $key ]["switch_port"];
		$result_array[ $key ]["switch_port_vlan"] = $query_result_switch[ $key ]["switch_port_vlan"];
		$result_array[ $key ]["user_mac_address"] = $query_result_switch[ $key ]["user_mac_address"];
		$result_array[ $key ]["user_ip_address"] = $query_result_switch[ $key ]["user_ip_address"];

	}
	$query_result_switch = array();

	foreach( $result_array as $key => $value ) {
		if( !@array_key_exists( "user_ip_address" , $result_array[$key] ) ) $result_array[ $key ]["user_ip_address"] = "";
		if( !@array_key_exists( "user_mac_address" , $result_array[$key] ) ) $result_array[ $key ]["user_mac_address"] = "";

		if( !@array_key_exists( "mail_address" , $result_array[$key] ) ) $result_array[ $key ]["mail_address"] = "";
		else {
			if( ($result_array[$key]["mail_address"] != "" ) && ( strpos( $result_array[$key]["mail_address"] , "@iyte.edu.tr") > 0 ) ) {
				// here we will find user's phone number from ldap rehber database via clsREHBER class if exists and display in our result table
			}
		}

		if( !@array_key_exists( "user_host_name" , $result_array[$key] ) ) $result_array[ $key ]["user_host_name"] = "";
		if( !@array_key_exists( "ap_ip_address" , $result_array[$key] ) ) $result_array[ $key ]["ap_ip_address"] = "";
		if( !@array_key_exists( "switch_ip_address" , $result_array[$key] ) ) $result_array[ $key ]["switch_ip_address"] = "";
		if( !@array_key_exists( "switch_name" , $result_array[$key] ) ) $result_array[ $key ]["switch_name"] = "";
		if( !@array_key_exists( "switch_port" , $result_array[$key] ) ) $result_array[ $key ]["switch_port"] = "";
		if( !@array_key_exists( "switch_port_vlan" , $result_array[$key] ) ) $result_array[ $key ]["switch_port_vlan"] = "";
	}

	if( count( $result_array ) == 0 ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , $query_text . " için herhangi bir kayýt bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"User Ip Address",
		"User Mac Address",
		"Mail Address",
		"User Host Name",
		"Connected AP Ip",
		"Connected Switch Ip",
		"Connected Switch Name",
		"Connected Switch Port",
		"Action" ));
	foreach( $result_array as $key => $value ) {
		$shutdownevent = "";
		$eduroamevent = "";
		if( $value["switch_ip_address"] !== "" ) {
			$shutdown_action_script = "javascript:doSendHost('shutdownport','&switch_ip=" . $value["switch_ip_address"] . "&switch_port=" . $value["switch_port"] . "' , 'window_result');";
			$shutdownevent = "<input type=\"image\" src=\"img/shutdown.png\" align=\"center\" title=\"Shutdown Port\" onClick=\"$shutdown_action_script\">";
		}

		if( $value["mail_address"] !== "" ) {
			$eduroam_action_script = "javascript:doSendHost('disableeduroam','&qtext=" . $value["mail_address"] . "' , 'window_result');";
			$eduroamevent = "<input type=\"image\" src=\"img/wirelessoff.jpg\" align=\"center\" title=\"Disable eduroam\" onClick=\"$eduroam_action_script\">";
		}

		$out_result .= $nm_web_interface->create_table_row( array(
			$value["user_ip_address"],
			$value["user_mac_address"],
			$value["mail_address"],
			$value["user_host_name"],
			$value["ap_ip_address"],
			$value["switch_ip_address"],
			$value["switch_name"],
			$value["switch_port"],
			$shutdownevent . $eduroamevent ));
	}
	$out_result .= $nm_web_interface->end_create_table();

	$result_array = array();

	$Log->Start();
	$query_out = "";

	$query_out = $nm_web_interface->encode_str( $out_result , true );
	print $query_out;

	$Log->Stop();

?>