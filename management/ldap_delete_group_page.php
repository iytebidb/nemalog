<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	if( $ldap->ldapDeleteGroup( $query_text ) ) {
		$result_array = $ldap->ldapReadGroups();
		if( count( $result_array) > 0 ) {
			$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
			$out_result = $nm_web_interface->addPrefix("success");
			$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
			$out_result .= $nm_web_interface->create_table_header( array(
				"Group Name" ,
				"Commands" ));
			foreach( $result_array as $key => $value ) {
				$out_result .= $nm_web_interface->create_table_row( array(
				$value,
				"" ));
			}
			$out_result .= $nm_web_interface->end_create_table();
		}
		else {
			$o_msg = $global_functions->format_error_message( $ldap->error_message );
			$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
		}
	}
	else {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
	}
	$ldap->disconnect();

	print $out_result;

	?>