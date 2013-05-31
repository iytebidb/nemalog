<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	if( $ldap->ldapDeleteGroupOfNamesMember( $group_id , $member_id ) ) {
		$result_array = $ldap->ldapReadGroupOfNamesMembers( $group_id );
		if( count( $result_array) > 0 ) {
			$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
			$out_result = $nm_web_interface->addPrefix("success");
			$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
			$out_result .= $nm_web_interface->create_table_header( array(
				"Group Members" ,
				"Parent Group" ,
				"Commands" ));
			foreach( $result_array as $key => $value ) {
				$delete_action = "javascript:sendHostEx('ldapdeletegroupofnamesmember','" . $query_text . "','" . $key . "' , 'window_result');";
				$event_delete = "<input type=\"image\" src=\"img/delete.png\" align=\"right\" title=\"Delete\" onClick=\"$delete_action\">";

				$out_result .= $nm_web_interface->create_table_row( array(
				$key,
				$value,
				$event_delete ));
			}
			$out_result .= $nm_web_interface->end_create_table();
		}
		else {
			$o_msg = $global_functions->format_error_message( $ldap->error_message );
			$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
		}
	}
	else {
		$o_msg = $global_functions->format_error_message( $ldap->error_message );
		$out_result = $nm_web_interface->create_error_message( "HATA" , $o_msg );
	}

	$ldap->disconnect();
	print $out_result;

?>