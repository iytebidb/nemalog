<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	$result_array = $ldap->ldapReadSpclAccounts();
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
		"Account Id",
		"Name",
		"Surname",
		"Faculty",
		"Adviser Person",
		"Adviser Person E-Mail",
		"Created By",
		"Description",
		"Commands" ));
	foreach( $result_array as $key => $value ) {
		$delete_action = "javascript:sendHostEx('ldapdeletespcluser','" . $value["cn"] . "' , 'window_result');";
		$event_delete = "<input type=\"image\" src=\"img/delete.png\" align=\"right\" title=\"Delete\" onClick=\"$delete_action\">";
		$update_action = "javascript:sendHostEx('ldapeditspcluser','" . $value["cn"] . "' , 'window_edit');";
		$event_update = "<input type=\"image\" src=\"img/edit.png\" align=\"right\" title=\"Edit\" onClick=\"$update_action\">";

		$out_result .= $nm_web_interface->create_table_row( array(
			$value["cn"],
			$value["givenname"],
			$value["sn"],
			$value["faculty"],
			$value["adviser"],
			$value["adviser-email"],
			$value["created-by"],
			$value["description"],
			$event_update . $event_delete ));
	}
	$out_result .= $nm_web_interface->end_create_table();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>