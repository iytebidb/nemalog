<?php

	foreach( $spcl as $key => $value ) {
		if( $value == "" ) {
			$spcl[ $key ] = "notdefined";
		}
	}

	if( ( $spcl["name"] == "notdefined") || ( $spcl["surname"] == "notdefined") || ( $spcl["adviser"] == "notdefined") || ( $spcl["adviser_e_mail"] == "notdefined") ) {
		$out_result = $nm_web_interface->create_error_message( "HATA" , "Name , Surname , Adviser Person Name , Adviser Person EMail can not be empty. Please correct and try again !!!" );
		print $out_result;
		die();
	}

	$spcl_account = array();
	$clear_text_key = $global_functions->create_clear_text_password();
	$clear_text_password = $global_functions->create_clear_text_password();
	$passwd = crypt( $clear_text_password , $clear_text_key );
	$created_user_id = $global_functions->create_user_id( $spcl["name"] , $spcl["surname"] );
	$created_user_id .= "@spcl.iyte.edu.tr";
	$this_date = date( "Y-m-d" );

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	$ldap->resetAccountData();
	$ldap->setAccountObjectClassData( array( "top" , "person" , "spcluser" ) );
	$ldap->setAccountData("cn" , $created_user_id , false );
	$ldap->setAccountData("sn" , $spcl["surname"] , true );
	$ldap->setAccountData("givenname" , $spcl["name"] , true );
	$ldap->setAccountData("mail" , $created_user_id , false );
	$ldap->setAccountData("uid" , $created_user_id , false );
	$ldap->setAccountData("displayname" , $spcl["name"] . " " . $spcl["surname"] , true );
	$ldap->setAccountData("userpassword" , "{crypt}" . $passwd , false );
	$ldap->setAccountData("description" , $spcl["description"] , true );
	$ldap->setAccountData("faculty" , $spcl["faculty"] , true );
	$ldap->setAccountData("adviser" , $spcl["adviser"] , true );
	$ldap->setAccountData("adviser-email" , $spcl["adviser_e_mail"] , false );
	//$ldap->setAccountData("created-by" , $logged_in_user_id , false );
	$ldap->setAccountData("created-by" , "firatkocak@iyte.edu.tr" , false );
	$ldap->setAccountData("date-created" , $this_date , false );

	if( $ldap->ldapAddSpclAccount( $spcl_account ) ) {
		$out_result = $nm_web_interface->create_success_message( "ноLEM TAMAM" , "Spcl account successfully created." );
	}
	else {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
	}
	$ldap->disconnect();

	print $out_result;

	?>