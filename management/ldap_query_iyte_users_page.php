<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	$result_array = $ldap->ldapReadIyteAccounts( $query_text );
	$ldap->disconnect();

	if( empty( $result_array) ) {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
	$out_result .= $nm_web_interface->create_table_header( array(
		"Account Id" ,
		"Name" ,
		"Surname",
		"Commands" ));
	foreach( $result_array as $key => $value ) {
		$detail_action = "javascript:sendHostEx('ldapdetailiyteuser','" . $value["cn"] . "' , 'window_detail');";
		$event_detail = "<input type=\"image\" src=\"img/detail.png\" align=\"right\" title=\"Detail\" onClick=\"$detail_action\">";
		$update_action = "javascript:sendHostEx('ldapeditiyteuser','" . $value["cn"] . "' , 'window_edit');";
		$event_update = "<input type=\"image\" src=\"img/edit.png\" align=\"right\" title=\"Edit\" onClick=\"$update_action\">";

		$out_result .= $nm_web_interface->create_table_row( array(
   		$value["cn"],
		$value["givenname"],
		$value["sn"],
		$event_detail . "&nbsp;" . $event_update ));
	}
	$out_result .= $nm_web_interface->end_create_table();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>