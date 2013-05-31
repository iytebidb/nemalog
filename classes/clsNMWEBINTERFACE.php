<?php

	class clsNMWEBINTERFACE {
		public $req_code;
		public $display_window;
		public $display_window_title;
		public $logged_in_user;
		public $html_header;
		public $html_menu;
		public $html_footer;
		public $prefix_success;
		public $prefix_notfound;
		public $prefix_fail;
		public $prefix_error;
		public $file_path;
		public $class_table;
		public $class_tr;
		public $class_th;
		public $class_th_error;
		public $class_th_success;
		public $class_td;
		public $class_td_error;
		public $class_td_success;
		public $class_td_key;
		public $class_td_data;

		public $language;
		public $language_texts;

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function __construct( $web_path ) {
			$this->req_code = "1001";
			$this->display_window = "window_result";
			$this->display_window_title = "Query Result";
			$this->logged_in_user = "notloggeduser";
			$this->language = "tr";
			$this->html_header = "";
			$this->html_menu = "";
			$this->html_footer = "";
			$this->prefix_success = "success:::";
			$this->prefix_notfound = "notfound:::";
			$this->prefix_fail = "fail:::";
			$this->prefix_error = "error:::";
			$this->file_path = $web_path;
			$this->language_texts = array();
			$this->class_table = "resulttable";
			$this->class_tr = "resulttable-tr";
			$this->class_th = "resulttable-th";
			$this->class_th_error = "resulttable-th-error";
			$this->class_th_success = "resulttable-th-success";
			$this->class_td = "resulttable-td";
			$this->class_td_error = "resulttable-td-error";
			$this->class_td_success = "resulttable-td-success";
			$this->class_td_key = "resulttable-td-key";
			$this->class_td_data = "resulttable-td-data";

			$this->load_files();
			$this->init_language_texts();

		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function __destruct() {

		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		private function check_utf8( $str ) {
			$len = strlen($str);
			for( $i = 0; $i < $len; $i++ ) {
				$c = ord( $str[$i] );
				if( $c > 128 ) {
					if( ( $c > 247 ) ) return false;
					elseif( $c > 239 ) $bytes = 4;
					elseif( $c > 223 ) $bytes = 3;
					elseif( $c > 191 ) $bytes = 2;
					else return false;

					if( ( $i + $bytes ) > $len ) return false;
					while( $bytes > 1 ) {
						$i++;
						$b = ord( $str[ $i ] );
						if( $b < 128 || $b > 191 ) return false;
						$bytes--;
					}
				}
			}

			return true;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function encode_str( $thisstr , $convert_it ) {
			$outstr = "";
			if( $convert_it ) {
				if( $this->check_utf8( $thisstr ) ) $outstr = $thisstr;
				else $outstr = iconv( "ISO-8859-9" , "UTF-8" , $thisstr );
			}
			else $outstr = $thisstr;

			return $outstr;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setLanguageTextTr( $table , $key , $value ) {
			$this->language_texts["tr"][ $table ][ $key ] = $value;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setLanguageTextEn( $table , $key , $value ) {
			$this->language_texts["en"][ $table ][ $key ] = $value;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setReqCode( $new_req_code ) {
			$this->req_code = $new_req_code;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setDisplayWindow( $new_display_window , $new_display_window_title ) {
			$this->display_window = $new_display_window;
			$this->display_window_title = $new_display_window_title;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function setLoggedInUser( $liu ) {
			$this->logged_in_user = $liu;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function init_language_texts() {
			//$this->setLanguageTextTr( $table , $key , $value );
			//$this->setLanguageTextEn( $table , $key , $value );
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		private function load_files() {
			$this->html_header = file_get_contents( $this->file_path . "management/html_header.html");
			$this->html_menu = file_get_contents( $this->file_path . "management/html_menu.html");
			$this->html_footer = file_get_contents( $this->file_path . "management/html_footer.html");
		}

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function get_hidden_div( $one_time_code , $logged_in_user_id ) {
			$out_msg = "<div style=\"display:none\">";
			$out_msg .= "<input type=\"text\" id=\"input_hidden_otc\" value=\"" . $one_time_code . "\"/>";
			$out_msg .= "<input type=\"text\" id=\"input_hidden_liuid\" value=\"" . $logged_in_user_id . "\"/>";
			$out_msg .= "</div>";

			return $out_msg;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function add_get_text_input_box_item( $label_id , $input_id , $type , $value_id , $e_handler , $disabled , $allow_admin , $arr_wdt ) {
			$new_style = "color:red;background: #aaaaaa url('bg_form.png') left top repeat-x;background: -webkit-gradient(linear, left top, left 25, from(#aaaaaa), color-stop(4%, #EEEEEE), to(#aaaaaa));background: -moz-linear-gradient(top, #aaaaaa, #EEEEEE 1px, #aaaaaa 25px);";
			$out_text = "";
			$new_value = "";
			$new_disabled = $disabled;
			$event_handler = "";

			if( $e_handler != null ) $event_handler = $e_handler;

			if( $allow_admin ) {
				if( $this->user_permission == $this->USER_CAN_EDIT_ALL ) $new_disabled = false;
			}

			if( $new_disabled ) $new_style = " style=\"color:red;background: #aaaaaa url('bg_form.png') left top repeat-x;background: -webkit-gradient(linear, left top, left 25, from(#aaaaaa), color-stop(4%, #EEEEEE), to(#aaaaaa));background: -moz-linear-gradient(top, #aaaaaa, #EEEEEE 1px, #aaaaaa 25px);\" readonly=\"readonly\" ";
			else $new_style = " ";

			if( is_array( $this->ldap_users[ $value_id ] ) ) {
				if( count( $this->ldap_users[ $value_id ] ) == 0 ) $new_value = "";
				else $new_value = $this->ldap_users[ $value_id ][0];
			}
			else $new_value = $this->ldap_users[ $value_id ];

			$out_text .= "<tr>" . PHP_EOL;

			$out_text .= "<td width=\"" . $arr_wdt[0] . "%\" id=\"" . $label_id . "\">" . PHP_EOL;
			$out_text .= $this->lang_msg[ $label_id ][$this->lang_id] . PHP_EOL;
			$out_text .= "</td>" . PHP_EOL;

			$out_text .= "<td width=\"" . $arr_wdt[1] . "%\">:</td>" . PHP_EOL;

			if( $arr_wdt[3] == 0 )
				$out_text .= "<td width=\"" . $arr_wdt[2] . "%\" colspan=\"2\">" . PHP_EOL;
			else
				$out_text .= "<td width=\"" . $arr_wdt[2] . "%\">" . PHP_EOL;

			if( $new_disabled )
				$out_text .= "<input id=\"" . $input_id . "\" value=\"" .  $new_value . "\" type=\"" . $type . "\"" . $new_style . " readonly=\"readonly\" " . $event_handler . "/>" . PHP_EOL;
			else
				$out_text .= "<input id=\"" . $input_id . "\" value=\"" .  $new_value . "\" type=\"" . $type . "\"" . $new_style . " " . $event_handler . "/>" . PHP_EOL;
			$out_text .= "</td>" . PHP_EOL;

			if( $arr_wdt[3] > 0 ) {
				$out_text .= "<td width=\"" . $arr_wdt[3] . "%\">" . PHP_EOL;
				$out_text .= "&nbsp" . PHP_EOL;
				$out_text .= "</td>" . PHP_EOL;
			}

			$out_text .= "</tr>" . PHP_EOL;

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function add_get_select_input_box_item( $label_id , $select_id , $input_id , $index , $list_array , $lval , $dval , $e_handler , $arr_wdt ) {
			$new_style = "color:red;background: #aaaaaa url('bg_form.png') left top repeat-x;background: -webkit-gradient(linear, left top, left 25, from(#aaaaaa), color-stop(4%, #EEEEEE), to(#aaaaaa));background: -moz-linear-gradient(top, #aaaaaa, #EEEEEE 1px, #aaaaaa 25px);";
			$out_text = "";
			$event_handler = "";

			if( $e_handler != null ) $event_handler = $e_handler;

			$out_text .= "<tr>" . PHP_EOL;

			$out_text .= "<td width=\"" . $arr_wdt[0] . "%\" id=\"" . $label_id . ( $index + 1) . "\">" . PHP_EOL;
			//$out_text .= $this->lang_msg[ $label_id . ( $index + 1) ][$this->lang_id] . " " . ( $index + 1) . PHP_EOL;
			$out_text .= $this->lang_msg[ $label_id . ( $index + 1) ][$this->lang_id] . PHP_EOL;
			$out_text .= "</td>" . PHP_EOL;

			$out_text .= "<td width=\"" . $arr_wdt[1] . "%\">:</td>" . PHP_EOL;


			if( $dval !== "" ) {
				$out_text .= "<td width=\"" . $arr_wdt[2] . "%\">";
				$out_text .= "<select size=\"1\" id=\"" . $select_id . $index . "\" value=\"" . $lval . "\">&nbsp;" . PHP_EOL;
				foreach( $list_array[$this->lang_id] as $key => $value ) {
					if( (int) $key == (int) $lval ) $out_text .= "<option selected value=\"$key\">$value</option>" . PHP_EOL;
					else $out_text .= "<option value=\"$key\">$value</option>" . PHP_EOL;
				}
				$out_text .= "</select>" . PHP_EOL;
				$out_text .= "</td>" . PHP_EOL;
				$out_text .= "<td width=\"" . $arr_wdt[3] . "%\">";
				$out_text .= "<input type=\"text\" id=\"" . $input_id .  $index . "\" size=\"10\" " . " " . $event_handler . " maxlength=\"10\" value=\"" . $dval . "\" />" . PHP_EOL;
				$out_text .= "</td>" . PHP_EOL;
			}
			else {
				$out_text .= "<td width=\"" . $arr_wdt[2] . "%\">";
				$out_text .= "<select size=\"1\" id=\"" . $select_id . $index . "\" value=\"0\">&nbsp;" . PHP_EOL;
				foreach( $list_array[$this->lang_id] as $key => $value ) {
					$out_text .= "<option value=\"$key\">$value</option>" . PHP_EOL;
				}
				$out_text .= "</select>" . PHP_EOL;
				$out_text .= "</td>" . PHP_EOL;
				$out_text .= "<td width=\"" . $arr_wdt[3] . "%\">";
				$out_text .= "<input type=\"text\" id=\"" . $input_id .  $index . "\" size=\"10\" " . " " . $event_handler . " maxlength=\"10\" value=\"" . $dval . "\" />" . PHP_EOL;
				$out_text .= "</td>" . PHP_EOL;
			}

			$out_text .= "</tr>" . PHP_EOL;

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function add_text_input_box_item( $label_id , $label_text , $input_id , $type , $e_handler ) {
			$out_text = "";
			$event_handler = "";
			$alt = "";

			if( $e_handler != null ) $event_handler = $e_handler;

			$out_text .= "<tr><td id=\"" . $label_id . "\" class=\"labeltext\">" . $label_text . "</td></tr>" . PHP_EOL;
			$out_text .= "<tr><td><input id=\"" . $input_id . "\" value=\"\" type=\"" . $type . "\" " . $alt . $event_handler . " /></td></tr>" . PHP_EOL;

			return $out_text;
		}

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function add_text_edit_box_item( $label_id , $label_text , $input_id , $input_class , $type , $e_handler ) {
			$out_text = "";
			$event_handler = "";
			$alt = "";

			if( $e_handler != null ) $event_handler = $e_handler;

			$out_text = "<tr>" . PHP_EOL;
			$out_text .= "<td id=\"" . $label_id . "\" class=\"labeltext\">" . $label_text . "</td>" . PHP_EOL;
			$out_text .= "<td><input class=\"td-disabled\" id=edit_old_\"" . $input_id . "\" value=\"\" type=\"" . $type . "\" /></td>" . PHP_EOL;
			$out_text .= "<td><input class=\"" . $input_class . "\" id=edit_new_\"" . $input_id . "\" value=\"\" type=\"" . $type . "\" " . $alt . $event_handler . " /></td>" . PHP_EOL;
			$out_text .= "</tr>" . PHP_EOL;

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function addPrefix( $prefix_type ) {
			if( $prefix_type == "success" )
				$out = $this->prefix_success;
			else if( $prefix_type == "error" )
				$out = $this->prefix_error;

			$out .= $this->req_code . ":::";
			$out .= $this->display_window . ":::";
			$out .= $this->display_window_title . ":::";
			$out .= $this->logged_in_user . ":::";

			return $out;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function add_select_input_box_item( $label_id , $input_id , $e_handler , $list_table , $arr_wdt ) {
			$out_text = "";

			$out_text .= "<tr>" . PHP_EOL;

			$out_text .= "<td width=\"" . $arr_wdt[0] . "%\" id=\"" . $label_id . "\">" . PHP_EOL;
			$out_text .= $this->lang_msg[ $label_id ][$this->lang_id] . PHP_EOL;
			$out_text .= "</td>" . PHP_EOL;

			$out_text .= "<td width=\"" . $arr_wdt[1] . "%\">:</td>" . PHP_EOL;

			$out_text .= "<td width=\"" . $arr_wdt[2] . "%\">" . PHP_EOL;
			$out_text .= "<select size=\"1\" id=\"" . $input_id . "\" value=\"0\">" . PHP_EOL;
			foreach( $list_table as $key => $value ) {
				$out_text .= "<option value=\"$key\">$value</option>" . PHP_EOL;
			}
			$out_text .= "</select>" . PHP_EOL;
			$out_text .= "</td>" . PHP_EOL;

			$out_text .= "<td width=\"" . $arr_wdt[3] . "%\"></td>" . PHP_EOL;

			$out_text .= "</tr>" . PHP_EOL;

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function add_button_item( $button_id , $button_text , $doEvent , $wdt ) {
			$out_text = "";
			$new_doEvent = "";

			if( !is_null( $doEvent ) ) $new_doEvent = $doEvent;

			if( is_null( $button_id ) ) {
				$out_text .= "<td width=\"" . $wdt . "%\">" . PHP_EOL;
				$out_text .= "&nbsp;";
				$out_text .= "</td>" . PHP_EOL;
			}
			else {
				$out_text .= "<td align=\"left\" width=\"" . $wdt . "\">" . PHP_EOL;
				$out_text .= "<input id=\"" . $button_id . "\" type=\"button\" class=\"submit\" align=\"center\" value=\"" . $button_text . "\" " . $new_doEvent . "/>" . PHP_EOL;
				$out_text .= "</td>" . PHP_EOL;
			}

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function div_start( $div_id , $div_class ) {
			return "<div align=\"center\" id=\"" . $div_id . "\" class=\"" . $div_class . "\" style=\"display:none;\">";
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function div_start_ex( $div_id , $div_class ) {
			return "<div align=\"center\" id=\"" . $div_id . "\" class=\"" . $div_class . "\" style=\"display:block;\">";
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function div_end() {
			return "</div>" . PHP_EOL . PHP_EOL;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function form_start( $form_id ) {
			return "<form id=\"" . $form_id . "\">";
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function form_end() {
			return "</form>";
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function table_start( $width , $border ) {
			return "<table align=\"center\" width=\"" . $width . "\" border=\"" . $border . "\">";
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function table_end() {
			return "</table>";
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_input_form( $div_id , $extension , $label_text , $button_text , $call_function , $result_window ) {
			$out_text = "";
			$out_text .= $this->div_start( "window_" . $div_id , "input-form" );
			$out_text .= $this->form_start( "form_" . $div_id );

			$out_text .= $this->table_start( 600 , 0 );
			if( is_array( $label_text ) ) {
				foreach( $label_text as $key => $value ) {
					//$out_text .= $this->add_text_input_box_item( "label_" . $div_id . "_" . $extension[$key] , $label_text[$key] , "text_" . $div_id . "_" . $extension[$key] , "text" , null , array(25,5,70) );
					$out_text .= $this->add_text_input_box_item( "label_" . $div_id . "_" . $extension[$key] , $label_text[$key] , "text_" . $div_id . "_" . $extension[$key] , "text" , null );
				}
			}
			else {
				//$out_text .= $this->add_text_input_box_item( "label_" . $div_id , $label_text , "text_" . $div_id , "text" , null , array(25,5,70) );
				$out_text .= $this->add_text_input_box_item( "label_" . $div_id , $label_text , "text_" . $div_id , "text" , null );
			}

			$out_text .= $this->table_end();

			$out_text .= $this->table_start( 600 , 0 );
			$out_text .= $this->add_button_item( "button_" . $div_id  , $button_text , "onClick=\"javascript:sendHost('" . $call_function . "' , '" . $result_window . "');\"" , 15 );
			$out_text .= $this->table_end();

			$out_text .= $this->form_end();
			$out_text .= $this->div_end();

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_window( $div_id  ) {
			$out_text = "";
			$out_text .= $this->div_start( $div_id , "input-form" );
				$out_text .= "<a href=\"#\" class=\"closey\"><img src=\"img/close_pop.png\" class=\"btn_close\" title=\"Pencereyi Kapat\" alt=\"Kapat\" /></a>";
				$out_text .= $this->div_start_ex( "header_" . $div_id , "input-form-header" );
				$out_text .= $this->div_end();
				$out_text .= $this->div_start_ex( "content_" . $div_id , "input-form-content" );
				$out_text .= $this->div_end();
			$out_text .= $this->div_end();

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_result_window( $div_id  ) {
			$out_text = "";
			$out_text .= $this->div_start( $div_id , "input-form" );
			$out_text .= "<a href=\"#\" class=\"closey\"><img src=\"img/close_pop.png\" class=\"btn_close\" title=\"Pencereyi Kapat\" alt=\"Kapat\" /></a>";
			$out_text .= $this->div_start_ex( "header_" . $div_id , "input-form-header" );
			$out_text .= $this->div_end();
			$out_text .= $this->div_start_ex( "content_" . $div_id , "input-form-content" );
			$out_text .= $this->div_end();
			$out_text .= $this->div_end();

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_edit_form( $div_id , $div_class , $table_width , $extension , $label_text , $button_text , $call_function , $result_window ) {
			$out_text = "";
			$out_text .= $this->div_start( "window_edit_" . $div_id , $div_class );
			$out_text .= $this->form_start( "form_edit_" . $div_id );

			$out_text .= $this->table_start( $table_width , 0 );
			if( is_array( $label_text ) ) {
				foreach( $label_text as $key => $value ) {
					//$out_text .= $this->add_text_input_box_item( "label_" . $div_id . "_" . $extension[$key] , $label_text[$key] , "text_" . $div_id . "_" . $extension[$key] , "text" , null , array(25,5,70) );
					$out_text .= $this->add_text_input_box_item( "label_edit_" . $div_id . "_" . $extension[$key] , $label_text[$key] , "text_edit_" . $div_id . "_" . $extension[$key] , "text" , null );
				}
			}
			else {
				//$out_text .= $this->add_text_input_box_item( "label_" . $div_id , $label_text , "text_" . $div_id , "text" , null , array(25,5,70) );
				$out_text .= $this->add_text_input_box_item( "label_edit_" . $div_id , $label_text , "text_edit_" . $div_id , "text" , null );
			}

			$out_text .= $this->table_end();

			$out_text .= $this->table_start( $table_width , 0 );
			$out_text .= $this->add_button_item( "button_edit_" . $div_id  , $button_text , "onClick=\"javascript:sendHost('" . $call_function . "' , '" . $result_window . "');\"" , 15 );
			$out_text .= $this->table_end();

			$out_text .= $this->form_end();
			$out_text .= $this->div_end();

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_input_div( $div_id , $div_class , $table_width , $extension , $label_text , $button_text , $call_function , $result_window ) {
			$out_text = "";
			$out_text .= $this->div_start( "window_" . $div_id , $div_class );
			$out_text .= $this->form_start( "form_" . $div_id );

			$out_text .= $this->table_start( $table_width , 0 );
			if( is_array( $label_text ) ) {
				foreach( $label_text as $key => $value ) {
					$out_text .= $this->add_text_input_box_item( "label_" . $div_id . "_" . $extension[$key] , $label_text[$key] , "text_" . $div_id . "_" . $extension[$key] , "text" , null );
				}
			}
			else {
				$out_text .= $this->add_text_input_box_item( "label_" . $div_id , $label_text , "text_" . $div_id , "text" , null );
			}

			$out_text .= $this->table_end();

			$out_text .= $this->table_start( $table_width , 0 );
			$out_text .= $this->add_button_item( "button_" . $div_id  , $button_text , "onClick=\"javascript:sendHost('" . $call_function . "' , '" . $result_window . "');\"" , 15 );
			$out_text .= $this->table_end();

			$out_text .= $this->form_end();
			$out_text .= $this->div_end();

			return $out_text;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_error_message( $header , $message ) {
			$this->setDisplayWindow( "popup" , $header );
			$out_result = $this->addPrefix("error");
			$out_result .= "<table class=\"" . $this->class_table . "\" align=\"center\" width=\"1200\" border=\"1\">" . PHP_EOL;
			$out_result .= "<tr class=\"" . $this->class_tr . "\"><th align=\"center\" class=\"" . $this->class_th_error . "\">" . $header . "</th></tr>" . PHP_EOL;
			$out_result .= "<tr>" . PHP_EOL;
			$out_result .= "<td class=\"" . $this->class_td_error . "\" align=\"center\">" . $message . "</td>" . PHP_EOL;
			$out_result .= "</tr>" . PHP_EOL;
			$out_result .= "</table><br>" . PHP_EOL;
			$out_result = $this->encode_str( $out_result , true );

			return $out_result;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_success_message( $header , $message ) {
			$this->setDisplayWindow( "popup" );
			$out_result = $this->addPrefix("success");
			$out_result .= "<table class=\"" . $this->class_table . "\" align=\"center\" width=\"1200\" border=\"1\">" . PHP_EOL;
			$out_result .= "<tr class=\"" . $this->class_tr . "\"><th align=\"center\" class=\"" . $this->class_th_success . "\">" . $header . "</th></tr>" . PHP_EOL;
			$out_result .= "<tr>" . PHP_EOL;
			$out_result .= "<td class=\"" . $this->class_td_success . "\" align=\"center\">" . $message . "</td>" . PHP_EOL;
			$out_result .= "</tr>" . PHP_EOL;
			$out_result .= "</table><br>" . PHP_EOL;
			$out_result = $this->encode_str( $out_result , true );

			return $out_result;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//303874825857
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function start_create_table( $align , $width , $border ) {
			$out_result = "<table id=\"result_table\" class=\"" . $this->class_table . "\" align=\"" . $align . "\" width=\"" . $width . "\" border=\"" . $border . "\">" . PHP_EOL;
			return $out_result;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function end_create_table() {
			$out_result = "</tbody></table><br>" . PHP_EOL;
			return $out_result;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_table_header( $header_data ) {
			$out_result = "<thead><tr class=\"" . $this->class_tr . "\">" . PHP_EOL;
			foreach( $header_data as $key => $value ) {
				$out_result .= "<th class=\"" . $this->class_th . "\">" . $value . "</th>" . PHP_EOL;
			}
			$out_result .= "</tr></thead><tbody>" . PHP_EOL;
			return $out_result;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		public function create_table_row( $row_data ) {
			$out_result = "<tr class=\"" . $this->class_tr . "\">" . PHP_EOL;
			foreach( $row_data as $key => $value ) {
				if( $value == "" ) $out_result .= "<td class=\"" . $this->class_td . "\">&nbsp;</td>" . PHP_EOL;
				else $out_result .= "<td class=\"" . $this->class_td . "\">" . $value . "</td>" . PHP_EOL;
			}
			$out_result .= "</tr>" . PHP_EOL;
			return $out_result;
		}
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	}

?>