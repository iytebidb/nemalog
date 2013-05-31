<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	$result_array = $ldap->ldapReadGuestAccount( $query_text );
	$ldap->disconnect();

	if( empty( $result_array) ) {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_edit_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_edit_table_header( array(
		"Field",
		"Old Value",
		"New Value" ));
	foreach( $result_array as $key => $value ) {
		$out_result .= $nm_web_interface->create_edit_table_row( array(
			$key,
			$value,
			$value ));
	}
	$out_result .= $nm_web_interface->end_edit_create_table();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

	?>