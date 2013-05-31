<?php

	$Log->setLogDirectory( "D:/iyte/log/" , "queryaplist" );
	//$Log->setLogDirectory( "/iyte/log/nmg/" , "queryaplist" );
	$DbUtil->setDatabase("network");
	$DbUtil->setLogHandle( $Log->getLogHandle() );
	if( !$DbUtil->connect() ) {
		print $nm_web_interface->create_error_message( "MySql" , $DbUtil->error_message );
		die();
	}

	$result_array = $DbUtil->query_switch_info( $query_text );
	if( empty( $result_array ) ) {
		$out_result = $nm_web_interface->create_error_message( "KAYIT BULUNAMADI" , "Info tablosunda $query_text ip numaralý herhangi bir kayýt bulunamadý !!!" );
		print $out_result;
		die();
	}

	$nm_web_interface->setDisplayWindow( "window_result" , "Query Result" );
	$out_result = $nm_web_interface->addPrefix("success");
	$out_result .= "<table $class_table align=\"center\" width=\"1200\" border=\"1\">" . PHP_EOL;

	$out_result .= "<tr $class_tr>" . PHP_EOL;
	$out_result .= "<td $class_td_key width=\"25%\">" . "Host Name" . "</td>" . PHP_EOL;
	$out_result .= "<td $class_td_data width=\"75%\">" . $result_array["host_name"] . "</td>" . PHP_EOL;
	$out_result .= "</tr>" . PHP_EOL;

	$out_result .= "<tr $class_tr>" . PHP_EOL;
	$out_result .= "<td $class_td_key width=\"25%\">" . "Description" . "</td>" . PHP_EOL;
	$out_result .= "<td $class_td_data width=\"75%\">" . $result_array["description"] . "</td>" . PHP_EOL;
	$out_result .= "</tr>" . PHP_EOL;

	$out_result .= "<tr $class_tr>" . PHP_EOL;
	$out_result .= "<td $class_td_key width=\"25%\">" . "Interface Count" . "</td>" . PHP_EOL;
	$out_result .= "<td $class_td_data width=\"75%\">" . $result_array["interface_count"] . "</td>" . PHP_EOL;
	$out_result .= "</tr>" . PHP_EOL;

	$out_result .= "<tr $class_tr>" . PHP_EOL;
	$out_result .= "<td $class_td_key width=\"25%\">" . "Default Gateway" . "</td>" . PHP_EOL;
	$out_result .= "<td $class_td_data width=\"75%\">" . $result_array["default_gateway"] . "</td>" . PHP_EOL;
	$out_result .= "</tr>" . PHP_EOL;

	$out_result .= "<tr $class_tr>" . PHP_EOL;
	$out_result .= "<td $class_td_key width=\"25%\">" . "Up Time" . "</td>" . PHP_EOL;
	$out_result .= "<td $class_td_data width=\"75%\">" . $result_array["up_time"] . "</td>" . PHP_EOL;
	$out_result .= "</tr>" . PHP_EOL;

	$out_result .= "<tr $class_tr>" . PHP_EOL;
	$out_result .= "<td $class_td_key width=\"25%\">" . "Contact" . "</td>" . PHP_EOL;
	$out_result .= "<td $class_td_data width=\"75%\">" . $result_array["contact"] . "</td>" . PHP_EOL;
	$out_result .= "</tr>" . PHP_EOL;

	$out_result .= "<tr $class_tr>" . PHP_EOL;
	$out_result .= "<td $class_td_key width=\"25%\">" . "Location" . "</td>" . PHP_EOL;
	$out_result .= "<td $class_td_data width=\"75%\">" . $result_array["location"] . "</td>" . PHP_EOL;
	$out_result .= "</tr>" . PHP_EOL;

	$out_result .= "<tr $class_tr>" . PHP_EOL;
	$out_result .= "<td $class_td_key width=\"25%\">" . "Last Change Time" . "</td>" . PHP_EOL;
	$out_result .= "<td $class_td_data width=\"75%\">" . $result_array["last_change"] . "</td>" . PHP_EOL;
	$out_result .= "</tr>" . PHP_EOL;

	$out_result .= "</table><br>" . PHP_EOL;
	$result_array = array();

	$out_result = $nm_web_interface->encode_str( $out_result , true );
	print $out_result;

?>