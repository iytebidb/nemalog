<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	if( $ldap->ldapDeleteAttribute( $account_id , $account_group , $attribute_id ) ) {
   		$out_result = $nm_web_interface->create_success_message( "ноLEM TAMAM" , $account_id . "'s " . $attribute_id . " successfully deleted." );
	}
	else {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
   		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
	}
	$ldap->disconnect();

	print $out_result;

?>