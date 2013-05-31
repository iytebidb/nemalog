<?php

	foreach( $guest as $key => $value ) {
		if( $value == "" ) {
			$guest[ $key ] = "notdefined";
		}
	}

	if( ( $guest["name"] == "notdefined") || ( $guest["surname"] == "notdefined") || ( $guest["adviser"] == "notdefined") || ( $guest["adviser_e_mail"] == "notdefined") ) {
		$out_result = $nm_web_interface->create_error_message( "HATA" , "Name , Surname , Adviser Person Name , Adviser Person EMail can not be empty. Please correct and try again !!!" );
		print $out_result;
		die();
	}

	$guest_account = array();
	$clear_text_key = $global_functions->create_clear_text_password();
	$clear_text_password = $global_functions->create_clear_text_password();
	$passwd = crypt( $clear_text_password , $clear_text_key );
	$nthash = $global_functions->NTLMHash( $clear_text_password );
	$created_user_id = $global_functions->create_user_id( $guest["name"] , $guest["surname"] );
	$created_user_id .= "@guest.iyte.edu.tr";
	$this_date = date( "Y-m-d" );

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	$ldap->resetAccountData();
	$ldap->setAccountObjectClassData( array( "top" , "person" , "guestuser" ) );
	$ldap->setAccountData("cn" , $created_user_id , false );
	$ldap->setAccountData("sn" , $guest["surname"] , true );
	$ldap->setAccountData("givenname" , $guest["name"] , true );
	$ldap->setAccountData("mail" , $created_user_id , false );
	$ldap->setAccountData("uid" , $created_user_id , false );
	$ldap->setAccountData("displayname" , $guest["name"] . " " . $guest["surname"] , true );
	$ldap->setAccountData("userpassword" , "{crypt}" . $passwd , false );
	$ldap->setAccountData("ntpassword" , $nthash , false );
	$ldap->setAccountData("description" , $guest["description"] , true );
	$ldap->setAccountData("tc-kimlik" , $guest["tc_passport_id"] , true );
	$ldap->setAccountData("eduroamenabled" , 1 , true );
	$ldap->setAccountData("faculty" , $guest["faculty"] , true );
	$ldap->setAccountData("adviser" , $guest["adviser"] , true );
	$ldap->setAccountData("adviser-email" , $guest["adviser_e_mail"] , false );
	//$ldap->setAccountData("created-by" , $logged_in_user_id , false );
	$ldap->setAccountData("created-by" , "firatkocak@iyte.edu.tr" , false );
	$ldap->setAccountData("date-created" , $this_date , false );
	$ldap->setAccountData("date-valid" , $guest["date_valid"] , false );

	if( !$ldap->ldapAddGuestAccount( $guest_account ) ) {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
	}
	else {
		$DbUtil->connect();
		$DbUtil->setDatabase("eduroam");
		$DbUtil->write_eduroam_guest_user_info( $created_user_id , $clear_text_password , $this_date , "firatkocak@iyte.edu.tr" , $guest );
		$DbUtil->disconnect();
		$out_result = $nm_web_interface->create_success_message( "ноLEM TAMAM" , "Guest account successfully created." );
	}
	$ldap->disconnect();

	print $out_result;

?>