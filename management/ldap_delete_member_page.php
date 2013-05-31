<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	if( $ldap->ldapRemoveMember( $member_id , "iyteusers" ,  $member_group , "groups" ) ) {
		$out_result = $nm_web_interface->create_error_message( "ноLEM TAMAM" , $member_id . " sucessfully deleted from the group " . $member_group );
	}
	else {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
	}
	$ldap->disconnect();

	print $out_result;

?>