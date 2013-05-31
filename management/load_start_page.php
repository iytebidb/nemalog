<?php

	$guest_user_extensions 			= array("name","surname","tc_passport_id","faculty","adviser_person","adviser_person_e_mail","date_valid","description");
	$guest_user_texts 				= array("Name","Surname","TC/Passport Id","Faculty","Adviser Person","Adviser Person E-Mail","Date Valid","Description");

	$guest_user_batch_extensions 	= array("count","faculty","adviser_person","adviser_person_e_mail","date_valid","description");
	$guest_user_batch_texts 		= array("Count","Faculty","Adviser Person","Adviser Person E-Mail","Date Valid","Description");

	$spcl_user_extensions 			= array("name","surname","faculty","adviser_person","adviser_person_e_mail","description");
	$spcl_user_texts 				= array("Name","Surname","Faculty","Adviser Person","Adviser Person E-Mail","Description");

	$attribute_extensions 			= array("user_id","user_group","attribute","attribute_value");
	$attribute_texts 				= array("User Id","User Group","Attribute To Add","Attribute Value");

	$one_time_code = $global_functions->create_one_time_password();

	$out_text  = $nm_web_interface->html_header;
	$out_text .= $nm_web_interface->get_hidden_div( $one_time_code , $logged_in_user_id );
	$out_text .= $nm_web_interface->html_menu;

	$out_text .= $nm_web_interface->create_input_form( "ldap_query_iyte_users" 				, null , "ÝYTE User To Search" , "Search" , "ldapqueryiyteusers" , "IYTE Users List" );
	$out_text .= $nm_web_interface->create_input_form( "ldap_add_group" 					, null , "Group To Add" , "Add" , "ldapaddgroup" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "ldap_add_group_of_names" 			, null , "Group of Names To Add" , "Add" , "ldapaddgroupofnames" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "ldap_add_guest_user" 				, $guest_user_extensions , $guest_user_texts , "Add" , "ldapaddguestuser" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "ldap_add_batch_guest_user" 			, $guest_user_batch_extensions , $guest_user_batch_texts , "Add" , "ldapaddbatchguestuser" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "ldap_add_spcl_user" 				, $spcl_user_extensions , $spcl_user_texts , "Add" , "ldapaddspcluser" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "ldap_add_attribute" 				, $attribute_extensions , $attribute_texts , "Add" , "ldapaddattribute" , "window_result" );

	$out_text .= $nm_web_interface->create_input_form( "switch_query_device_info" 			, null , "Switch Ip Address" , "Search" , "switchquerydeviceinfo" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "switch_query_device_interface_list" , null , "Switch Ip Address" , "Search" , "switchquerydeviceinterfacelist" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "switch_query_device_cdp_list" 		, null , "Switch Ip Address" , "Search" , "switchquerydevicecdplist" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "switch_query_device_ip_mac_list" 	, null , "Switch Ip Address" , "Search" , "switchquerydeviceipmaclist" , "window_result" );

	$out_text .= $nm_web_interface->create_input_form( "ap_query_device_info" 				, null , "Access Point Ip Address" , "Search" , "apquerydeviceinfo" , "window_result" );

	$out_text .= $nm_web_interface->create_input_form( "eduroam_accept_query_user" 			, null , "Search String" , "Search" , "eduroamacceptqueryuser" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "eduroam_reject_query_user" 			, null , "Search String" , "Search" , "eduroamrejectqueryuser" , "window_result" );

	$out_text .= $nm_web_interface->create_input_form( "dhcp_lease_query_user_ip" 			, null , "Lease IP Address" , "Search" , "dhcpleasequeryuserip" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "dhcp_lease_query_user_mac" 			, null , "Lease Mac Address" , "Search" , "dhcpleasequeryusermac" , "window_result" );

	$out_text .= $nm_web_interface->create_input_form( "find_user_input_by_mac" 			, null , "User MAC Address" , "Find" , "queryuserbymac" , "window_result" );
	$out_text .= $nm_web_interface->create_input_form( "find_user_input_by_ip" 				, null , "User IP Address" , "Find" , "queryuserbyip" , "window_result" );

	$out_text .= $nm_web_interface->create_window( "window_result" );
	$out_text .= $nm_web_interface->create_window( "window_detail" );
	$out_text .= $nm_web_interface->create_window( "window_edit" );

	$out_text .= $nm_web_interface->html_footer;

	print $out_text;

?>