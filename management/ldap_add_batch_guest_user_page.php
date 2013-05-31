<?php

	foreach( $batch_guest as $key => $value ) {
		if( $value == "" ) {
			$batch_guest[ $key ] = "notdefined";
		}
	}

	$message_emty = "Count , Adviser Person Name , Adviser Person EMail can not be empty. Please correct and try again !!!";
	$message_count_not_number = "Count value must be numeric. Correct and try again !!!";
	$message_count = "Count value can not be bigger than 1000. Correct and try again !!!";
	$message_check = "";

	if( ( $batch_guest["count"] == "notdefined") || ( $batch_guest["adviser"] == "notdefined") || ( $batch_guest["adviser_e_mail"] == "notdefined") ) {
		$message_check = $message_emty;
	}

	$batch_guest["count"] = (int) $batch_guest["count"];
	if( !is_numeric( $batch_guest["count"] ) ) {
		$message_check = $message_count_not_number;
	}

	if( $batch_guest["count"] > 1000 ) {
		$message_check = $message_count;
	}

	if( $message_check != "" ) {
		$out_result = $nm_web_interface->create_error_message( "HATA" , $message_check );
		print $out_result;
		$ldap->disconnect();
		die();
	}

	$batch_guest_account = array();
	$error_list = "";

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();

	for( $i = 0; $i < $batch_guest["count"]; $i ++ ) {
		if( $i < 10 ) $index = "00" . $i;
		else if( ( $i >= 10 ) && ( $i < 100 ) )$index = "0" . $i;
		else if( $i >= 100 ) $index = $i;

		$clear_text_key = $global_functions->create_clear_text_password();
		$clear_text_password = $global_functions->create_clear_text_password();
		$passwd = crypt( $clear_text_password , $clear_text_key );
		$nthash = $global_functions->NTLMHash( $clear_text_password );
		$bg_name = date( "YmdHis" );
		$bg_surname = $index;
		$created_user_id = $bg_name . $bg_surname;
		$created_user_id .= "@guest.iyte.edu.tr";
		$this_date = date( "Y-m-d" );

		$ldap->resetAccountData();
		$ldap->setAccountObjectClassData( array( "top" , "person" , "guestuser" ) );
		$ldap->setAccountData("cn" , $created_user_id , false );
		$ldap->setAccountData("sn" , $bg_surname , true );
		$ldap->setAccountData("givenname" , $bg_name , true );
		$ldap->setAccountData("mail" , $created_user_id , false );
		$ldap->setAccountData("uid" , $created_user_id , false );
		$ldap->setAccountData("displayname" , $bg_name . " " . $bg_surname , true );
		$ldap->setAccountData("userpassword" , "{crypt}" . $passwd , false );
		$ldap->setAccountData("ntpassword" , $nthash , false );
		$ldap->setAccountData("description" , $batch_guest["description"] , true );
		$ldap->setAccountData("tc-kimlik" , "notdefined" , true );
		$ldap->setAccountData("eduroamenabled" , 1 , true );
		$ldap->setAccountData("faculty" , $batch_guest["faculty"] , true );
		$ldap->setAccountData("adviser" , $batch_guest["adviser"] , true );
		$ldap->setAccountData("adviser-email" , $batch_guest["adviser_e_mail"] , false );
		//$ldap->setAccountData("created-by" , $logged_in_user_id , false );
		$ldap->setAccountData("created-by" , "firatkocak@iyte.edu.tr" , false );
		$ldap->setAccountData("date-created" , $this_date , false );
		$ldap->setAccountData("date-valid" , $batch_guest["date_valid"] , false );

		if( !$ldap->ldapAddGuestAccount() ) {
			$error_list .= $global_functions->format_error_message( $ldap->error_message );
		}
		else {
			$error_list .= $created_user_id . " successfully created.<br>" . PHP_EOL;
		}
		//print $error_list;
	}

	$out_result = $nm_web_interface->create_error_message( "HATA" , $error_list );

	$ldap->disconnect();
	print $out_result;

?>