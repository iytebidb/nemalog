<?php

	$result_array = array();
	$user_found = false;
	$out_result = "";

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	$Log->setLogDirectory( "D:/iyte/log/" , "findmac" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$Log->Start();
	$ldap->setLogHandle( $Log->getLogHandle() );
	$Switch->setLogHandle( $Log->getLogHandle() );

	if( $ldap->ldapDisableEduroam( $query_text ) ) {
		$out_result = $nm_web_interface->create_success_message( "ноLEM TAMAM" , "Users's eduroam credientials disabled." );
	}
	else {
		$out_result = $nm_web_interface->create_error_message( "HATA" , "The User could not be disabled !!!" );
	}
	$ldap->disconnect();
	$Log->Stop();

	print $query_out;

?>