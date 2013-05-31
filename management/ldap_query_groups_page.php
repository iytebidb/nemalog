<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	$result_array = $ldap->ldapReadGroups();
	$ldap->disconnect();

	if( empty( $result_array) ) {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Group Name" ,
		"Commands" ));
	foreach( $result_array as $key => $value ) {
		if( ( $value == "groups" ) || ( $value == "guestusers" ) || ( $value == "iyteusers" ) || ( $value == "spclusers" ) ) {
			$event_delete = "";
		}
		else {
			$delete_action = "javascript:sendHostEx('ldapdeletegroup','" . $value . "' , 'window_result');";
			$event_delete = "<input type=\"image\" src=\"img/delete.png\" align=\"right\" title=\"Delete\" onClick=\"$delete_action\">";
		}

		$out_result .= $nm_web_interface->create_table_row( array(
			$value,
			$event_delete ));
	}
	$out_result .= $nm_web_interface->end_create_table();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>