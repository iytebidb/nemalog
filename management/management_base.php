<?php

	include_once( $web_source_path . "classes/clsLOG.php");
	include_once( $web_source_path . "classes/clsNMWEBINTERFACE.php");
	include_once( $web_source_path . "classes/clsDBUTIL.php");
	include_once( $web_source_path . "classes/clsSWITCH.php");
	include_once( $web_source_path . "classes/clsGLOBALFUNCTIONS.php");
	include_once( $web_source_path . "classes/clsIYTELDAP.php");

	$nm_web_interface = new clsNMWEBINTERFACE( $web_source_path );
	$global_functions = new clsGLOBALFUNCTIONS();
	$DbUtil = new clsDBUTIL();
	$Log = new clsLOG();
	$Switch = new clsSWITCH();
	$ldap = new clsnewIYTELDAP();

	$logged_in_user_id = "";
	$guest = array();
	$guest_batch = array();
	$spcl = array();

	foreach( $_POST as $key => $value ) {
		$_POST[ $key ] = trim( $value );
		$_POST[ $key ] = str_replace( "*" , "" , $_POST[ $key ] );
	}

	$req_type = isset( $_POST[ "reqtype" ] ) ? $_POST[ "reqtype" ] : null;
	$req_code = isset( $_POST[ "reqcode" ] ) ? $_POST[ "reqcode" ] : 0;
	$logged_in_user_id = isset( $_POST[ "sid" ] ) ? $_POST[ "sid" ] : "";

	$query_text = isset( $_POST[ "qtext" ] ) ? $_POST[ "qtext" ] : "";
	$findmac = isset( $_POST[ "findmac" ] ) ? $_POST[ "findmac" ] : "";
	$findip = isset( $_POST[ "findip" ] ) ? $_POST[ "findip" ] : "";
	$dhcpfilter = isset( $_POST[ "dhcpfilter" ] ) ? $_POST[ "dhcpfilter" ] : "";
	$eduroamfilter = isset( $_POST[ "eduroamfilter" ] ) ? $_POST[ "eduroamfilter" ] : "";
	$switchip = isset( $_POST[ "switchip" ] ) ? $_POST[ "switchip" ] : "";
	$apip = isset( $_POST[ "apip" ] ) ? $_POST[ "apip" ] : "";

	$member_id = isset( $_POST[ "member_id" ] ) ? $_POST[ "member_id" ] : "";
	$member_group = isset( $_POST[ "member_group" ] ) ? $_POST[ "member_group" ] : "";

	$user_id = isset( $_POST[ "user_id" ] ) ? $_POST[ "user_id" ] : "";
	$user_group = isset( $_POST[ "user_group" ] ) ? $_POST[ "user_group" ] : "";
	$attribute = isset( $_POST[ "attribute" ] ) ? $_POST[ "attribute" ] : "";
	$attribute_value = isset( $_POST[ "attribute_value" ] ) ? $_POST[ "attribute_value" ] : "";

	$guest["name"] = isset( $_POST[ "guest_name" ] ) ? $_POST[ "guest_name" ] : "";
	$guest["surname"] = isset( $_POST[ "guest_surname" ] ) ? $_POST[ "guest_surname" ] : "";
	$guest["description"] = isset( $_POST[ "guest_description" ] ) ? $_POST[ "guest_description" ] : "";
	$guest["tc_passport_id"] = isset( $_POST[ "guest_tc_passport_id" ] ) ? $_POST[ "guest_tc_passport_id" ] : "";
	$guest["faculty"] = isset( $_POST[ "guest_faculty" ] ) ? $_POST[ "guest_faculty" ] : "";
	$guest["adviser"] = isset( $_POST[ "guest_adviser" ] ) ? $_POST[ "guest_adviser" ] : "";
	$guest["adviser_e_mail"] = isset( $_POST[ "guest_adviser_e_mail" ] ) ? $_POST[ "guest_adviser_e_mail" ] : "";
	$guest["date_valid"] = isset( $_POST[ "guest_date_valid" ] ) ? $_POST[ "guest_date_valid" ] : "";

	$batch_guest["count"] = isset( $_POST[ "guest_batch_count" ] ) ? $_POST[ "guest_batch_count" ] : "";
	$batch_guest["description"] = isset( $_POST[ "guest_batch_description" ] ) ? $_POST[ "guest_batch_description" ] : "";
	$batch_guest["faculty"] = isset( $_POST[ "guest_batch_faculty" ] ) ? $_POST[ "guest_batch_faculty" ] : "";
	$batch_guest["adviser"] = isset( $_POST[ "guest_batch_adviser" ] ) ? $_POST[ "guest_batch_adviser" ] : "";
	$batch_guest["adviser_e_mail"] = isset( $_POST[ "guest_batch_adviser_e_mail" ] ) ? $_POST[ "guest_batch_adviser_e_mail" ] : "";
	$batch_guest["date_valid"] = isset( $_POST[ "guest_batch_date_valid" ] ) ? $_POST[ "guest_batch_date_valid" ] : "";

	$spcl["name"] = isset( $_POST[ "spcl_name" ] ) ? $_POST[ "spcl_name" ] : "";
	$spcl["surname"] = isset( $_POST[ "spcl_surname" ] ) ? $_POST[ "spcl_surname" ] : "";
	$spcl["description"] = isset( $_POST[ "spcl_description" ] ) ? $_POST[ "spcl_description" ] : "";
	$spcl["faculty"] = isset( $_POST[ "spcl_faculty" ] ) ? $_POST[ "spcl_faculty" ] : "";
	$spcl["adviser"] = isset( $_POST[ "spcl_adviser" ] ) ? $_POST[ "spcl_adviser" ] : "";
	$spcl["adviser_e_mail"] = isset( $_POST[ "spcl_adviser_e_mail" ] ) ? $_POST[ "spcl_adviser_e_mail" ] : "";
	$spcl["date_created"] = isset( $_POST[ "spcl_date_created" ] ) ? $_POST[ "spcl_date_created" ] : "";

	$switch_ip = isset( $_POST[ "switch_ip" ] ) ? $_POST[ "switch_ip" ] : "";
	$switch_port = isset( $_POST[ "switch_port" ] ) ? $_POST[ "switch_port" ] : "";

	$query_text = isset( $_POST[ "qtext" ] ) ? $_POST[ "qtext" ] : "";


	if( !is_null( $req_type ) ) {
		if( !$global_functions->check_one_time_password( $req_code ) ) {
			include_once( "load_illegal_access_page.php" );
			die();
		}
	}

	$req_code = $global_functions->create_one_time_password();
	$nm_web_interface->setReqCode( $req_code );
	$nm_web_interface->setLoggedInUSer( $logged_in_user_id );

	switch( $req_type ) {
		case null : include_once( "load_start_page.php" ); break;
		case "login" : include_once( "check_login_page.php" ); break;

		case "ldapquerygroups" : include_once( "ldap_query_groups_page.php" ); break;
		case "ldapquerygroupofnames" : include_once( "ldap_query_group_of_names_page.php" ); break;
		case "ldapqueryiyteusers" : include_once( "ldap_query_iyte_users_page.php" ); break;
		case "ldapqueryguestusers" : include_once( "ldap_query_guest_users_page.php" ); break;
		case "ldapqueryspclusers" : include_once( "ldap_query_spcl_users_page.php" ); break;

		case "ldapdetailgroupofnames" : include_once( "ldap_detail_group_of_names_page.php" ); break;

		case "ldapaddgroup" : include_once( "ldap_add_group_page.php" ); break;
		case "ldapaddgroupofnames" : include_once( "ldap_add_group_of_names_page.php" ); break;
		case "ldapaddguestuser" : include_once( "ldap_add_guest_user_page.php" ); break;
		case "ldapaddbatchguestuser" : include_once( "ldap_add_batch_guest_user_page.php" ); break;
		case "ldapaddspcluser" : include_once( "ldap_add_spcl_user_page.php" ); break;
		case "ldapaddattribute" : include_once( "ldap_add_attribute_page.php" ); break;

		case "ldapdetailgroupofnames" : include_once( "ldap_detail_group_of_names_page.php" ); break;

		case "ldapeditiyteuser" : include_once( "ldap_edit_iyte_user_page.php" ); break;
		case "ldapeditguestuser" : include_once( "ldap_edit_guest_user_page.php" ); break;
		case "ldapeditspcluser" : include_once( "ldap_edit_spcl_user_page.php" ); break;

		case "ldapupdateiyteuser" : include_once( "ldap_update_iyte_user_page.php" ); break;
		case "ldapupdateguestuser" : include_once( "ldap_update_guest_user_page.php" ); break;
		case "ldapupdatespcluser" : include_once( "ldap_update_spcl_user_page.php" ); break;
		case "ldapupdateattribute" : include_once( "ldap_update_attribute_page.php" ); break;

		case "ldapdeletegroup" : include_once( "ldap_delete_group_page.php" ); break;
		case "ldapdeletegroupofnames" : include_once( "ldap_delete_group_of_names_page.php" ); break;
		case "ldapdeleteguestuser" : include_once( "ldap_delete_guest_user_page.php" ); break;
		case "ldapdeletespcluser" : include_once( "ldap_delete_spcl_user_page.php" ); break;
		case "ldapdeleteattribute" : include_once( "ldap_delete_attribute_page.php" ); break;
		case "ldapdeletemember" : include_once( "ldap_delete_member_page.php" ); break;

		case "switchquerydevicelist" : include_once( "switch_query_device_list_page.php" ); break;
		case "switchquerydeviceinfo" : include_once( "switch_query_device_info_page.php" ); break;
		case "switchquerydeviceinfoall" : include_once( "switch_query_device_info_all_page.php" ); break;
		case "switchquerydeviceinterfacelist" : include_once( "switch_query_device_interface_list_page.php" ); break;
		case "switchquerydeviceinterfacelistall" : include_once( "switch_query_device_interface_list_all_page.php" ); break;
		case "switchquerydevicecdplist" : include_once( "switch_query_device_cdp_list_page.php" ); break;
		case "switchquerydevicecdplistall" : include_once( "switch_query_device_cdp_list_all_page.php" ); break;
		case "switchquerydeviceipmaclist" : include_once( "switch_query_device_ip_mac_list_page.php" ); break;
		case "switchquerydeviceipmaclistall" : include_once( "switch_query_device_ip_mac_list_all_page.php" ); break;

		case "apquerydevicelist" : include_once( "ap_query_device_list_page.php" ); break;
		case "apquerydeviceinfo" : include_once( "ap_query_device_info_page.php" ); break;
		case "apquerydeviceinfoall" : include_once( "ap_query_device_info_all_page.php" ); break;

		case "eduroamacceptqueryuser" : include_once( "eduroam_accept_query_user_page.php" ); break;
		case "eduroamacceptqueryall" : include_once( "eduroam_accept_query_all_page.php" ); break;
		case "eduroamrejectqueryuser" : include_once( "eduroam_reject_query_user_page.php" ); break;
		case "eduroamrejectqueryall" : include_once( "eduroam_reject_query_all_page.php" ); break;
		case "eduroamrejectqueryldaperrors" : include_once( "eduroam_reject_query_ldap_errors_page.php" ); break;
		case "eduroamrejectquerycertificateerrors" : include_once( "eduroam_reject_query_certificate_errors_page.php" ); break;
		case "eduroamrejectquerydomainerrors" : include_once( "eduroam_reject_query_domain_errors_page.php" ); break;
		case "eduroamrejectqueryunspecifiederrors" : include_once( "eduroam_reject_query_unspecified_errors_page.php" ); break;

		case "dhcpqueryconf" : include_once( "dhcp_query_conf_page.php" ); break;
		case "dhcpqueryhost" : include_once( "dhcp_query_host_page.php" ); break;
		case "dhcpquerynetwork" : include_once( "dhcp_query_network_page.php" ); break;
		case "dhcpleasequeryuserip" : include_once( "dhcp_lease_query_user_ip_page.php" ); break;
		case "dhcpleasequeryusermac" : include_once( "dhcp_lease_query_user_mac_page.php" ); break;
		case "dhcpqueryleaseall" : include_once( "dhcp_query_lease_all_page.php" ); break;

		case "queryuserbymac" : include_once( "find_user_by_mac_page.php" ); break;
		case "queryuserbyip" : include_once( "find_user_by_ip_page.php" ); break;

		case "shutdownport" : include_once( "shutdown_port_page.php" ); break;
		case "disableeduroam" : include_once( "disable_eduroam_page.php" ); break;
		//default : include_once( "load_illegal_access_page.php" ); break;
	}

?>