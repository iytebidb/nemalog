	var show_div_list = [];
	var hide_div_list = [];
	var popup_item = '';
	var default_pos_y = 0;
	var caller_window = null;
	var currentDiv = null;
	var acceptable_key_array = [];
	var allowed_chars;
	var post_data = '';
	var info_window_created = false;
	var fade_out_time = 500;
	var resultWindowDisplayed = false;
	var detailWindowDisplayed = false;
	var editWindowDisplayed = false;

	//----------------------------------------------------------------------
	//
	function addShowDivList( div_id ) {
		show_div_list.push( div_id );
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function deleteShowDivList( div_id ) {
		return show_div_list.push();
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function hideAllWindows() {
		$('[id^="window_"]').hide();
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function hideWindow( window_id ) {
		$( '#' + window_id).hide('fast');
		if( window_id == 'window_edit' ) $( '#window_result').show('fast');
		else if( window_id == 'window_detail' ) $( '#window_result').show('fast');
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function displayWindow( window_id ) {
		var popMargLeft = ($('body').width() - $('#' + window_id).width() ) / 2;
		$('#' + window_id).css({
			'margin-top' : default_pos_y,
			'margin-left' : popMargLeft - 12
		});
		$( '#' + window_id).show('fast');
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function showWindow( window_id ) {
		hideAllWindows();
		//addShowDivList( window_id )
		displayWindow( window_id );
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function showResultWindow( window_header , insert_text ) {
		var popMargLeft;
		var result_width;

		$('#header_window_result').html( window_header );
		$('#content_window_result').html( insert_text );
		result_width = $('#window_result').width();
		popMargLeft = ($('body').width() - result_width ) / 2;
		$('#window_result').css({
			'margin-top' : default_pos_y,
			'margin-left' : popMargLeft - 12
		});

		$('a.closey').live('click', function() {
			resultWindowDisplayed = false;
			$( '#window_result' ).hide('slow');
		});

		resultWindowDisplayed = true;
		$( '#result_table' ).tablesorter();
		$( '#window_result' ).show('slow');
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function showDetailWindow( window_header , insert_text ) {
		var popMargLeft;

		$('#header_window_detail').html( window_header );
		$('#content_window_detail').html( insert_text );
		popMargLeft = ($('body').width() - $('#window_detail').width() ) / 2;
		$('#window_detail').css({
			'margin-top' : default_pos_y,
			'margin-left' : popMargLeft - 12
		});

		$('a.closey').live('click', function() {
			detailWindowDisplayed = false;
			$( '#window_detail' ).hide('slow');
			if( resultWindowDisplayed ) {
				$( '#window_result' ).show('slow');
			}
		});

		detailWindowDisplayed = true;
		$( '#window_detail' ).show('slow');
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function showEditWindow( window_header , insert_text ) {
		var popMargLeft;

		$('#header_window_edit').html( window_header );
		$('#content_window_edit').html( insert_text );
		popMargLeft = ($('body').width() - $('#window_edit').width() ) / 2;
		$('#window_edit').css({
			'margin-top' : default_pos_y,
			'margin-left' : popMargLeft - 12
		});

		$('a.closey').live('click', function() {
			editWindowDisplayed = false;
			$( '#window_edit' ).hide('slow');
			if( detailWindowDisplayed ) {
				$( '#window_detail' ).show('slow');
			}
		});

		editWindowDisplayed = true;
		$( '#window_edit' ).show('slow');
	}
	//----------------------------------------------------------------------

	/*function showResultWindow( result_div , insert_text ) {
		var popMargLeft;
		var new_html;

		new_html = '<a href="#" class="closey"><img src="img/close_pop.png" class="btn_close" title="Pencereyi Kapat" alt="Kapat" /></a>';
		new_html += '<div id="cnt">';
		new_html += insert_text;
		new_html += '</div>';

		$('#' + result_div).html( new_html );
		popMargLeft = ($('body').width() - $('#' + result_div).width() ) / 2;
		$('#' + result_div).css({
			'margin-top' : default_pos_y,
			'margin-left' : popMargLeft - 12
		});

		$('a.closey').live('click', function() {
			$( '#' + result_div ).hide('slow');
		});

		$( '#' + result_div ).show('slow');
	}*/

	//----------------------------------------------------------------------
	//
	$.fn.updateOptionList = function( newlist ) {
		var sIndex = $(this).val();
		var i = 0;
		var newHtml = '';

		for( i = 0; i < newlist.length; i ++ ) {
			if( i == sIndex )
				newHtml += '<option value="' + i + '" selected="selected">' + newlist[i] + '</value>';
			else
				newHtml += '<option value="' + i + '">' + newlist[i] + '</value>';
		}

		$(this).html( newHtml );

		return true;
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
    function setDisplayDivs( active_div , switch_div ) {
    	$('#spcl_active_div').val( active_div );
    	$('#spcl_switch_div').val( switch_div );
    }
    //----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
    function InitFields() {
    	var i;

    	allowed_chars = 'abcdefghijklmnopqrstuvwxyz';
    	var allowed_indexes = [8,9,35,36,37,39,46,48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105];

    	for( i = 0; i < 256; i ++ ) {
    		acceptable_key_array[ i ] = 0;
    	}
    	for( i = 0; i < allowed_indexes.length; i ++ )
    		acceptable_key_array[ allowed_indexes[i] ] = 1;
    }
    //----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function isNumeric( eve ) {
		var keyCode = eve.keyCode;
		if( acceptable_key_array[ keyCode ] == 1 ) return true;

		eve.returnValue = null;
		return false;
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function processKeyCode( eve ) {
		var kCode = eve.keyCode;

		if( acceptable_key_array[ kCode ] == 1 ) return true;

		eve.returnValue = null;
		return false;
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function printpage(){
		$('#window_result').printArea();
		return false;
	}
	//----------------------------------------------------------------------

	function showIndicator(){
		var popMargLeft;
		$('body').append('<div id="indicatormask"></div>');
		popMargLeft = ($('body').width() - $('#indicatormask').width() ) / 2;
		popMargTop = ($('body').height() - $('#indicatormask').height() ) / 2;
		$('#indicatormask').css({
			'margin-top' : popMargTop,
			'margin-left' : popMargLeft
		});

		$('#indicatormask').show('fast');
		$(' #indicatormask').activity({segments: 16, steps: 5, width:5, space: 3, length: 15, color: '#020202', speed: 2.0});
	}

	function hideIndicator(){
		$('#indicatormask').activity(false);
		$('#indicatormask').hide('fast');
		$('#indicatormask').remove();
	}

	//----------------------------------------------------------------------
	//
	function showPopUp( contentHtml ) {
		var new_html = '';
		var txt = '';

		new_html = '<div id="popupwindow">';
		new_html += '<a href="#" class="closey"><img src="img/close_pop.png" class="btn_close" title="Pencereyi Kapat" alt="Kapat" /></a>';
		new_html += '<div id="cnt">';
		new_html += contentHtml;
		new_html += '</div>';
		new_html += '</div>';

		$('body').append('<div id="mask"></div>');
		$('body').append( new_html );
		var popMargLeft = ($('body').width() - $('#popupwindow').width() ) / 2;
		$('#popupwindow').css({
			'margin-top' : default_pos_y,
			'margin-left' : popMargLeft
		});

		$('a.closey').live('click', function() {
	  		$('#mask , #popupwindow').fadeOut(fade_out_time , function() {
				$('#mask').remove();
				$('#popupwindow').remove();
			});
		});

		$('#popupwindow').show('fast');

		return false;
	}
	//----------------------------------------------------------------------

	//2757353
	//----------------------------------------------------------------------
	//
	function doSendHost( sender , send_data , display_div ){
		var otp;
		var dw;
		var dw_title;
		var liu;
		var rdata;
		var rcode;
		$('a.closey').click();
		post_data = 'reqtype=' + sender;
		post_data += '&reqcode=' + $('#input_hidden_otc').val();
		post_data += '&sid=' + $('#input_hidden_liuid').val();
		post_data += send_data;
		//alert( post_data );
		showIndicator();

		$.ajax({
			data: post_data,
			success: function( responseText ) {
				//alert( responseText );
				hideIndicator();
				var rText = responseText.split(':::');
				rcode = rText[0];
				otp = rText[1];
				dw = rText[2];
				dw_title = rText[3];
				liu = rText[4];
				rdata = rText[5];
				$('#input_hidden_otc').val( otp );
				$('#input_hidden_liuid').val(liu);

				if( rcode == 'error' ) {
					showPopUp( rdata );
				}
				else if( rcode == 'success' ) {
					if( dw == 'window_result' )
						showResultWindow( dw_title , rdata );
					else if( dw == 'window_detail' )
						showDetailWindow( dw_title , rdata );
					else if( dw == 'window_edit' )
						showEditWindow( dw_title , rdata );
					else if( dw == 'popup' )
						showPopUp( rdata );
				}
			}
		});

		return false;
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function sendHost( sender , display_div ) {
		var post_data;
		post_data = '';

		// ldap section
		if( sender == 'ldapqueryiyteusers' ) {
			post_data = '&qtext=' + $('#text_ldap_query_iyte_users').val();
		}
		else if( sender == 'ldapaddgroup' ) {
			post_data = '&qtext=' + $('#text_ldap_add_group').val();
		}
		else if( sender == 'ldapaddgroupofnames' ) {
			post_data = '&qtext=' + $('#text_ldap_add_group_of_names').val();
		}
		else if( sender == 'ldapaddguestuser' ) {
			post_data = '&guest_name=' 				+ $('#text_ldap_add_guest_user_name').val();
			post_data += '&guest_surname=' 			+ $('#text_ldap_add_guest_user_surname').val();
			post_data += '&guest_description=' 		+ $('#text_ldap_add_guest_user_description').val();
			post_data += '&guest_tc_passport_id=' 	+ $('#text_ldap_add_guest_user_tc_passport_id').val();
			post_data += '&guest_faculty=' 			+ $('#text_ldap_add_guest_user_faculty').val();
			post_data += '&guest_adviser=' 			+ $('#text_ldap_add_guest_user_adviser_person').val();
			post_data += '&guest_adviser_e_mail=' 	+ $('#text_ldap_add_guest_user_adviser_person_e_mail').val();
			post_data += '&guest_date_valid=' 		+ $('#text_ldap_add_guest_user_date_valid').val();
		}
		else if( sender == 'ldapaddbatchguestuser' ) {
			post_data = '&guest_batch_count=' 			+ $('#text_ldap_add_batch_guest_user_count').val();
			post_data += '&guest_batch_description=' 	+ $('#text_ldap_add_batch_guest_user_description').val();
			post_data += '&guest_batch_faculty=' 		+ $('#text_ldap_add_batch_guest_user_faculty').val();
			post_data += '&guest_batch_adviser=' 		+ $('#text_ldap_add_batch_guest_user_adviser_person').val();
			post_data += '&guest_batch_adviser_e_mail=' + $('#text_ldap_add_batch_guest_user_adviser_person_e_mail').val();
			post_data += '&guest_batch_date_valid=' 	+ $('#text_ldap_add_batch_guest_user_date_valid').val();
		}
		else if( sender == 'ldapaddspcluser' ) {
			post_data = '&spcl_name=' 				+ $('#text_ldap_add_spcl_user_name').val();
			post_data += '&spcl_surname=' 			+ $('#text_ldap_add_spcl_user_surname').val();
			post_data += '&spcl_description=' 		+ $('#text_ldap_add_spcl_user_description').val();
			post_data += '&spcl_faculty=' 			+ $('#text_ldap_add_spcl_user_faculty').val();
			post_data += '&spcl_adviser=' 			+ $('#text_ldap_add_spcl_user_adviser_person').val();
			post_data += '&spcl_adviser_e_mail=' 	+ $('#text_ldap_add_spcl_user_adviser_person_e_mail').val();
		}
		else if( sender == 'ldapaddattribute' ) {
			post_data = '&user_id=' 			+ $('#text_ldap_add_attribute_user_id').val();
			post_data += '&user_group=' 		+ $('#text_ldap_add_attribute_user_group').val();
			post_data += '&attribute=' 			+ $('#text_ldap_add_attribute_attribute').val();
			post_data += '&attribute_value=' 	+ $('#text_ldap_add_attribute_attribute_value').val();
		}
		else if( sender == 'ldapeditiyteuser' ) {
			post_data = '&qtext=' + $('#').val();
		}
		else if( sender == 'ldapeditguestuser' ) {
			post_data = '&gtext=' + $('#').val();
		}
		else if( sender == 'ldapeditspcluser' ) {
			post_data = '&qtext=' + $('#').val();
		}
		else if( sender == 'ldapupdateiyteuser' ) {
			post_data = '&qtext=' + $('#').val();
		}
		else if( sender == 'ldapupdateguestuser' ) {
			post_data = '&gtext=' + $('#').val();
		}
		else if( sender == 'ldapupdatespcluser' ) {
			post_data = '&qtext=' + $('#').val();
		}
		else if( sender == 'ldapupdateattribute' ) {
			post_data = '&user_id=' 			+ $('#text_ldap_add_attribute_user_id').val();
			post_data += '&user_group=' 		+ $('#text_ldap_add_attribute_user_group').val();
			post_data += '&attribute=' 			+ $('#text_ldap_add_attribute_attribute').val();
			post_data += '&attribute_value=' 	+ $('#text_ldap_add_attribute_attribute_value').val();
		}
		else if( sender == 'ldapdeletegroup' ) {
			post_data = '&qtext=' + $('#').val();
		}
		else if( sender == 'ldapdeletegroupofnames' ) {
			post_data = '&qtext=' + $('#').val();
		}
		else if( sender == 'ldapdeleteguestuser' ) {
			post_data = '&qtext=' + $('#').val();
		}
		else if( sender == 'ldapdeletespcluser' ) {
			post_data = '&qtext=' + $('#').val();
		}
		else if( sender == 'ldapdeleteattribute' ) {
			post_data = '&qtext=' + $('#').val();
		}

		// switch section
		else if( sender == 'switchquerydeviceinfo' ) {
			post_data = '&qtext=' + $('#text_switch_query_device_info').val();
		}
		else if( sender == 'switchquerydeviceinterfacelist' ) {
			post_data = '&qtext=' + $('#text_switch_query_device_interface_list').val();
		}
		else if( sender == 'switchquerydevicecdplist' ) {
			post_data = '&qtext=' + $('#text_switch_query_device_cdp_list').val();
		}
		else if( sender == 'switchquerydeviceipmaclist' ) {
			post_data = '&qtext=' + $('#text_switch_query_device_ip_mac_list').val();
		}

		// access point section
		else if( sender == 'apquerydeviceinfo' ) {
			post_data = '&qtext=' + $('#text_ap_query_device_info').val();
		}

		// eduroam section
		else if( sender == 'eduroamacceptqueryuser' ) {
			post_data = '&eduroamfilter=' + $('#text_eduroam_accept_query_user').val();
		}
		else if( sender == 'eduroamrejectqueryuser' ) {
			post_data = '&eduroamfilter=' + $('#text_eduroam_reject_query_user').val();
		}

		// dhcp section
		else if( sender == 'dhcpleasequeryuserip' ) {
			post_data = '&dhcpfilter=' + $('#text_dhcp_lease_query_user_ip').val();
		}
		else if( sender == 'dhcpleasequeryusermac' ) {
			post_data = '&dhcpfilter=' + $('#text_dhcp_lease_query_user_mac').val();
		}

		// find user section
		else if( sender == 'queryuserbymac' ) {
			post_data = '&findmac=' + $('#text_find_user_input_by_mac').val();
		}
		else if( sender == 'queryuserbyip' ) {
			post_data = '&findip=' + $('#text_find_user_input_by_ip').val();
		}

		//alert( sender + ' - ' + post_data );
		return doSendHost( sender , post_data , display_div );
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	//
	function sendHostEx( sender , user_id , display_div ){
		var post_data = '&qtext=' + user_id;
		doSendHost( sender , post_data , display_div );
	}
	//----------------------------------------------------------------------

	//----------------------------------------------------------------------
	$(document).ready( function() {

		default_pos_y = $('#qm0').height();
		default_pos_y = default_pos_y + $('#cssmenu').height();
		//default_pos_y = default_pos_y + 10;

		InitFields();

		//----------------------------------------------------------------------
		//
   		$.ajaxSetup({
			type: 'POST',
			url: 'index.php',
        	error: function(jqXHR, exception) {
        		hideIndicator();
        		setDisplayDivs( '' , '' );
            	if (jqXHR.status === 0) {
            		showPopUp( 'Not connect.\n Verify Network.' );
            	} else if (jqXHR.status == 404) {
            		showPopUp( 'Requested page not found. [404]' );
            	} else if (jqXHR.status == 500) {
            		showPopUp( 'Internal Server Error [500].' );
            	} else if (exception === 'parsererror') {
            		showPopUp( 'Requested JSON parse failed.' );
            	} else if (exception === 'timeout') {
            		showPopUp( 'Time out error.' );
            	} else if (exception === 'abort') {
            		showPopUp( 'Ajax request aborted.' );
            	} else {
            		showPopUp( 'Uncaught Error.\n' + jqXHR.responseText );
            	}
        	}
    	});
    	//----------------------------------------------------------------------

    	// Key input functions
		//----------------------------------------------------------------------
		//
    	$('#text_query_user_by_ip').keydown( function( eve ) {
			if( ( eve.keyCode == 13 ) || ( eve.keyCode == 10 ) ) {
				eve.returnValue = null;
				$('#button_query_user_by_ip').click();
				return true;
			}

			return true;
    	});
    	//----------------------------------------------------------------------

		//----------------------------------------------------------------------
		//
    	$('#text_query_user_by_mac').keydown( function( eve ) {
			if( ( eve.keyCode == 13 ) || ( eve.keyCode == 10 ) ) {
				eve.returnValue = null;
				$('#button_query_user_by_mac').click();
				return true;
			}

			return true;
    	});
    	//----------------------------------------------------------------------

		//----------------------------------------------------------------------
		//
		$('#iyte_logo_tbl').click( function() {
			if( $('#spcl_lang_id').val() == "tr" )
				window.open( "http://www.iyte.edu.tr" , 'userwindow' );
			else
				window.open( "http://www.iyte.edu.tr/AnaSayfa.aspx?d=ENG" , 'userwindow' );
		});
		//----------------------------------------------------------------------

		//----------------------------------------------------------------------
		//
		$('#imglang').click( function() {
			post_data = 'rtype=langchange';
			post_data += '&langid=' + $('#spcl_lang_id').val();
			post_data += '&uname=' + $('#spcl_logged_in_user').val();
			post_data += '&recordcount=' + $('#spcl_search_record_count').val();
			post_data += '&currentloggeduser=' + $('#spcl_logged_in_user_display').val();

			$.ajax({
				data: post_data,
				success: function( responseText ) {
					//alert( responseText );
					var i;
					var j;
					var newid = '';
					var phone_index;
					var fax_index;
					var rText = responseText.split(':::');
					if( rText[0] == 'success' ) {
						$('#loggedinuser').html( rText[1] );
						$('#spcl_lang_id').val( rText[2] )

						for( i = 3; i < rText.length; i += 2 ) {
							if( rText[i] == 'imglang' ) $('#' + rText[i] ).attr('src' , rText[i+1] );
							else if( rText[i] == 'iyte_logo_tbl' ) $('#' + rText[i] ).attr('background' , rText[i+1] );
							else if( rText[i] == 'phonearray' ) {
								newid = rText[i+1];
								phone_index = newid.split(',');

								for( j = 0; j < $('#spcl_phone_count').val(); j ++ ) {
									$('#text_edit_phone_index' + j ).updateOptionList( phone_index );
								}
							}
							else if( rText[i] == 'faxarray' ) {
								newid = rText[i+1];
								fax_index = newid.split(',');

								for( j = 0; j < $('#spcl_fax_count').val(); j ++ ) {
									$('#text_edit_fax_index' + j ).updateOptionList( fax_index );
								}
							}
							else if( rText[i].indexOf('ph_') == 0 ) {
								newid = rText[i];
								newid = newid.replace('ph_','');
							}
							else {
								$('#' + rText[i] ).val( rText[i+1] );
								$('#' + rText[i] ).html( rText[i+1] );
							}
						}
					}
					else if( rText[0] == 'fail' ) {
						setDisplayDivs( '' , '' );
						showPopUp( 'Dil dosyasý sunucudan alýnamadý ...' );
					}
					else {
						showPopUp( 'Script Error !!!' );
					}
				}
			});

			return false;
		});
		//----------------------------------------------------------------------

		//----------------------------------------------------------------------
		// Submit functions ...
		//----------------------------------------------------------------------


		//----------------------------------------------------------------------
		// Submit Login
		$('#button_search_reset').click( function() {
			$('#text_search_general' ).val('');
			$('#text_search_name' ).val('');
			$('#text_search_surname' ).val('');
			$('#text_search_title' ).val('');
			$('#text_search_branch' ).val('');
			$('#text_search_branch_phone' ).val('');
			$('#text_search_phone' ).val('');
			$('#text_search_fax' ).val('');
			$('#text_search_web' ).val('');

			return false;
		});
		//----------------------------------------------------------------------

		//----------------------------------------------------------------------
		// Submit Login
		$('#button_login_submit').click( function() {
			post_data = 'rtype=login' + '&';
			post_data += '&langid=' + $('#spcl_lang_id').val();
			post_data += '&uname=' + $('#text_login_user_name').val();
			post_data += '&upwd=' + $('#text_login_user_password').val();

			$.ajax({
				data: post_data,
				success: function( responseText ) {
					var rText = responseText.split(':::');
					if( rText[0] == 'success' ) {
						$('#spcl_sid').val( rText[2] );
						$('#spcl_logged_in_user').val( rText[1] );
						$('#spcl_logged_in_user_welcome').val( rText[3] );
						$('#spcl_logged_in_user_display').val( rText[4] );
						$('#loggedinuser').html( rText[3] + ' ' + rText[4] );

						$('#window_login').fadeOut(0);
						$('#menu_login').fadeOut(0);
						$('#menu_logout').fadeIn(0);
						$('#menu_edit').fadeIn(0);
						$('#menu_edit').click();
					}
					else if( rText[0] == 'fail' ) {
						setDisplayDivs( '#window_login' , '' );
						showPopUp( rText[1] );
					}
					else {
						setDisplayDivs( '' , '' );
						showPopUp( 'Script Error !!!' );
					}
				}
			});

			return false;
		});
		//----------------------------------------------------------------------

		//----------------------------------------------------------------------
		//
		$('#menu_logout').click( function() {
			post_data = 'rtype=logout';
			post_data += '&sid=' + $('#spcl_sid').val();
			post_data += '&langid=' + $('#spcl_lang_id').val();
			post_data += '&uname=' + $('#spcl_logged_in_user').val();

			$.ajax({
				data: post_data,
				success: function( responseText ) {
					var rTextArr = responseText.split(':::');
					if( rTextArr[0] == 'success' ) {
						$('#spcl_sid').val( 0 );
						$('#spcl_logged_in_user').val( 0 );
						$('#loggedinuser').html( rTextArr[1] );
						$('#spcl_logged_in_user_display').val( '' );

						$( '#window_edit' ).fadeOut(0);
						$( '#menu_logout' ).fadeOut(0);
						$( '#menu_edit' ).fadeOut(0);
						$( '#menu_login' ).fadeIn(0);
						$('#menu_search').click();
					}
					else if( rTextArr[0] == 'fail' ) {
						setDisplayDivs( '' , '' );
						showPopUp( 'Çýkýþ iþlemi baþarýsýz. Lütfen tekrar deneyiniz...' );
					}
					else {
						setDisplayDivs( '' , '' );
						showPopUp( 'Script Error !!!' );
					}
				}
			});

			return false;
		});
		//----------------------------------------------------------------------

		// Submit Search
		//----------------------------------------------------------------------
		//
		$('#button_search_submit').click( function() {
			var request_send_srchgenel = $('#text_search_general').val();
			var request_send_srchad = $('#text_search_name').val();
			var request_send_srchsoyad = $('#text_search_surname').val();
			var request_send_srchtelefon = $('#text_search_phone').val();
			var request_send_srchbirimtelefon = $('#text_search_branch_phone').val();
			var request_send_srchfaks = $('#text_search_fax').val();
			var request_send_srchweb = $('#text_search_web').val();
			var request_send_srchgorevbirim = $('#text_search_branch').val();
			var request_send_srchgorevunvan = $('#text_search_title').val();
			var search_length_state = 0;

			if( request_send_srchgenel.length >= 3 ) search_length_state += 1;
			if( request_send_srchad.length >= 3 ) search_length_state += 1;
			if( request_send_srchsoyad.length >= 3 ) search_length_state += 1;
			if( request_send_srchtelefon.length >= 3 ) search_length_state += 1;
			if( request_send_srchbirimtelefon.length >= 3 ) search_length_state += 1;
			if( request_send_srchfaks.length >= 3 ) search_length_state += 1;
			if( request_send_srchweb.length >= 3 ) search_length_state += 1;
			if( ( request_send_srchgorevbirim != 0 ) && ( request_send_srchgorevbirim != null ) ) search_length_state += 1;
			if( ( request_send_srchgorevunvan != 0 ) && ( request_send_srchgorevunvan != null ) ) search_length_state += 1;

			if( search_length_state == 0 ) {
				setDisplayDivs( '#window_search' , '' );
				if( $('#spcl_lang_id').val() == 'tr' )
					showPopUp( 'Arama kriter uzunluðu en az 3 karakter olmalý. Lütfen kontrol edip tekrar deneyiniz !!!' );
				else
					showPopUp( 'The search criteria must be at least 3 characters. Check your input and then try again, please !!!' );

				return 0;
			}

			post_data = 'rtype=search';
			post_data += '&langid=' + $('#spcl_lang_id').val();
			post_data += '&uname=' + $('#text_login_user_name').val();
			post_data += '&sid=' + $('#spcl_sid').val();

			if( request_send_srchgenel != '' ) post_data += '&srchgenel=' + request_send_srchgenel;
			if( request_send_srchad != '' ) post_data += '&srchad=' + request_send_srchad;
			if( request_send_srchsoyad != '' ) post_data += '&srchsoyad=' + request_send_srchsoyad;
			if( request_send_srchbirimtelefon != '' ) post_data += '&srchbirimtelefon=' + request_send_srchbirimtelefon;
			if( request_send_srchtelefon != '' ) post_data += '&srchtelefon=' + request_send_srchtelefon;
			if( request_send_srchfaks != '' ) post_data += '&srchfaks=' + request_send_srchfaks;
			if( request_send_srchweb != '' ) post_data += '&srchweb=' + request_send_srchweb;
			if( request_send_srchgorevbirim != '' ) post_data += '&srchgorevbirim=' + request_send_srchgorevbirim;
			if( request_send_srchgorevunvan != '' ) post_data += '&srchgorevunvan=' + request_send_srchgorevunvan;

			$.ajax({
				data: post_data,
				success: function( responseText ) {
					//alert( responseText );
					var rText = responseText.split(':::');
					if( rText[0] == 'success' ) {
						$( '#window_search' ).fadeOut(0);
						$( '#spcl_search_record_count' ).val( rText[1] );
						$('#window_search_result').html( rText[2] );
						$('#menu_search_result').fadeIn(0);
						setDisplayDivs( '#window_search_result' , '' );
						showBox( '#window_search_result' );
					}
					else if( rText[0] == 'sessionout' ) {
						$('#spcl_sid').val( 0 );
						$('#spcl_logged_in_user').val( 0 );
						$( '#spcl_search_record_count' ).val( rText[1] );
						$('#loggedinuser').html( 'Yetkisiz Kullanýcý' );
						$( '#menu_logout' ).fadeOut(0);
						$( '#menu_edit' ).fadeOut(0);
						$( '#window_edit' ).fadeOut(0);
						$( '#menu_login' ).fadeIn(0);
						$( '#window_search' ).fadeOut(0);
						$('#window_search_result').html( rText[2] );
						setDisplayDivs( '#window_search_result' , '' );
						showBox( '#window_search_result' );
					}
					else if( rText[0] == 'fail' ) {
						setDisplayDivs( '#window_search' , '' );
						showPopUp( rText[1] );
					}
					else {
						setDisplayDivs( '#window_search' , '' );
						showPopUp( 'Script Error !!!' );
					}
				}
			});

			return false;
		});
		//----------------------------------------------------------------------

		//----------------------------------------------------------------------
		//

	});

