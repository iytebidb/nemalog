<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();

	if( !$ldap->ldapAddAttribute( $user_id , $user_group , $attribute , $attribute_value ) ) {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
	}
	else {
		if( $user_id == "" ) $out_result = $nm_web_interface->create_success_message( "ноLEM TAMAM" , "Attribute $attribute baўar§l§ bir ўekilde $user_group daki hesaplara eklendi." );
		else $out_result = $nm_web_interface->create_success_message( "ноLEM TAMAM" , "Attribute $attribute baўar§l§ bir ўekilde $user_id hesab§na eklendi." );
	}

	$ldap->disconnect();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>