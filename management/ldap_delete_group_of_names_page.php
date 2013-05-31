<?php

	$ldap->setLdapServer( "ldap server adresi");
	$ldap->connect();
	if( $ldap->ldapDeleteGroupOfNames( $query_text ) ) {
		$result_array = $ldap->ldapReadGroupOfNames();
		if( count( $result_array) > 0 ) {
			$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
			$out_result = $nm_web_interface->addPrefix("success");
			$out_result .= $nm_web_interface->start_create_table( "center" , 1200 , 1 );
			$out_result .= $nm_web_interface->create_table_header( array(
				"Group Of Names" ,
				"Commands" ));
			foreach( $result_array as $key => $value ) {
				if( ( $value == "mail_admin_group" ) ||
					( $value == "rehber_admin_group" ) ||
					( $value == "tis_admin_group" ) ||
					( $value == "vpn_admin_group" ) ||
					( $value == "vpn_faculty_admin_group" ) ||
					( $value == "rehber_client_group" ) ||
					( $value == "tis_client_group" ) ||
					( $value == "tis_std_client_group" ) ||
				( $value == "vpn_client_group" ) ) {
					$event_delete = "";
					$event_detail = "";
				}
				else {
					$delete_action = "javascript:sendHostEx('ldapdeletegroupofnames','" . $value . "' , 'window_result');";
					$event_delete = "<input type=\"image\" src=\"img/delete.png\" align=\"right\" title=\"Delete\" onClick=\"$delete_action\">";
					$detail_action = "javascript:sendHostEx('ldapdetailgroupofnames','" . $value . "' , 'window_detail');";
					$event_detail = "<input type=\"image\" src=\"img/detail.png\" align=\"right\" title=\"Detail\" onClick=\"$detail_action\">";
				}

				$out_result .= $nm_web_interface->create_table_row( array(
				$value,
					$event_detail . "&nbsp;" . $event_delete ));
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