<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	if( $ldap->ldapAddGroupOfNames( $query_text , "iyteusers" ) ) {
		$out_result = $nm_web_interface->create_success_message( "ноLEM TAMAM" , "GroupofNames $query_text added sucessfully." );
	}
	else {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
	}
	$ldap->disconnect();

	print $out_result;

?>