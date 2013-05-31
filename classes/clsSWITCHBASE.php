<?php

	class clsSWITCHBASE {
		private $ip_address = "";
		private $ip_address_long = "";
		private $community = "switch snmp communuty";
		private $log_base = "log dizini";
		private $log_extension = ".log";
		private $log_file = "";
		private $log_handle = null;
		private $oids = array();

		private $TYPE_DISPLAY_STRING = 1;
		private $TYPE_GAUGE32 = 2;
		private $TYPE_COUNTER32 = 3;
		private $TYPE_IP_ADDRESS = 4;
		private $TYPE_MAC_ADDRESS = 5;
		private $TYPE_OID = 6;
		private $TYPE_TIME_TICK = 7;
		private $TYPE_INTEGER = 8;
		private $TYPE_INTEGER32 = 9;
		private $TYPE_TIME_STAMP = 10;
		private $TYPE_INTERFACE_INDEX = 11;
		private $TYPE_IANA_IF_TYPE = 12;
		private $TYPE_PHYSICAL_ADDRESS = 13;
		private $TYPE_SNMP_ADMIN_STRING = 14;
		private $TYPE_OCTET_STRING = 15;
		private $TYPE_VLAN_INDEX = 16;
		private $TYPE_CISCO_NETWORK_ADDRESS = 17;


		public function _getDisplayString( $instr ){
			$ret_str = str_replace( "\"" , "" , $instr );
			$ret_str = str_replace( "string:: " , "" , $ret_str );
			$ret_str = str_replace( "string: " , "" , $ret_str );
			$ret_str = str_replace( "STRING: " , "" , $ret_str );
			$ret_str = trim( $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _getOctetString( $instr ){
			$ret_str = str_replace( "\"" , "" , $instr );
			$ret_str = str_replace( "string:: " , "" , $ret_str );
			$ret_str = str_replace( "string: " , "" , $ret_str );
			$ret_str = str_replace( "STRING: " , "" , $ret_str );
			$ret_str = trim( $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _getSnmpAdminString( $instr ){
			$ret_str = str_replace( "\"" , "" , $instr );
			$ret_str = str_replace( "string:: " , "" , $ret_str );
			$ret_str = str_replace( "string: " , "" , $ret_str );
			$ret_str = str_replace( "STRING: " , "" , $ret_str );
			$ret_str = trim( $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _removeOid( $instr ){
			$ret_str = str_replace( "OID: " , "" , $instr );
			$ret_str = str_replace( "oid: " , "" , $ret_str );
			$ret_str = trim( $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _getGauge32( $instr ) {
			$ret_str = str_replace( "gauge32: " , "" , $instr );
			$ret_str = str_replace( "Gauge32: " , "" , $instr );
			$ret_str = str_replace( "GAUGE32: " , "" , $ret_str );
			$ret_str = trim( $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _getCounter32( $instr ) {
			$ret_str = str_replace( "counter32: " , "" , $instr );
			$ret_str = str_replace( "Counter32: " , "" , $instr );
			$ret_str = str_replace( "COUNTER32: " , "" , $ret_str );
			$ret_str = trim( $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _getInteger32( $instr ) {
			$ret_str = str_replace( "INTEGER: " , "" , $instr );
			$ret_str = str_replace( "integer: " , "" , $ret_str );
			return $ret_str;
		}

		public function _getInteger( $instr ) {
			$ret_str = str_replace( "INTEGER: " , "" , $instr );
			$ret_str = str_replace( "integer: " , "" , $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _getInterfaceIndex( $instr ) {
			$ret_str = str_replace( "INTEGER: " , "" , $instr );
			$ret_str = str_replace( "integer: " , "" , $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _getVlanIndex( $instr ) {
			$ret_str = str_replace( "INTEGER: " , "" , $instr );
			$ret_str = str_replace( "integer: " , "" , $ret_str );
			return $ret_str;
			//return $this->clear_all_prefixes( $instr );
		}

		public function _getTimeTick( $instr ) {
			//[.iso.3.6.1.2.1.2.2.1.9.10101] => Timeticks: (1822239512) 210 days, 21:46:35.12
			$ret_str = str_replace( "TimeTicks: " , "" , $instr );
			$ret_str = str_replace( "TIMETICKS: " , "" , $ret_str );
			$ret_str = trim( $ret_str );
			//$ret_str = $this->clear_all_prefixes( $instr );
			$arr = explode( " " , $ret_str );
			$ret_str = "";
			for( $i = 1; $i < count( $arr ); $i ++ ) { $ret_str .= $arr[ $i ] . " "; }

			$p = strpos( $ret_str , ")" );
			if( $p ) { $ret_str = substr( $ret_str , $p + 1 ); }
			$ret_str = trim( $ret_str );

			return $ret_str;
		}

		public function _getTimeStamp( $instr ) {
			//sysORLastChange => Timeticks: (0) 0:00:00.00
			$ret_str = str_replace( "TimeTicks: " , "" , $instr );
			$ret_str = str_replace( "TIMETICKS: " , "" , $ret_str );
			//$ret_str = $this->clear_all_prefixes( $instr );
			$arr = explode( " " , $ret_str );
			$ret_str = "";
			for( $i = 1; $i < count( $arr ); $i ++ ) { $ret_str .= $arr[ $i ] . " "; }

			$p = strpos( $ret_str , ")" );
			if( $p ) { $ret_str = substr( $ret_str , $p + 1 ); }
			$ret_str = trim( $ret_str );

			return $ret_str;
		}

		public function _getIpAddr( $instr ) {
			//$ret_str = str_replace( "IpAddress: " , "" , $instr );
			//return $ret_str;
			return $this->clear_all_prefixes( $instr );
		}

		public function _get_mac_address( $instr ) {
			$ret_str = "";
			if( strlen( $instr ) == 0 ) {
				$ret_str = "0000.0000.0000";
			}
			else if( strlen( $instr ) == 6 ) {
				$arr = str_split( $instr );
				$d1 = base_convert( Ord( $arr[0] ) , 10 , 16 );
				if( strlen( $d1 ) == 1 ) $d1 = "0" . $d1;
				$d2 = base_convert( Ord( $arr[1] ) , 10 , 16 );
				if( strlen( $d2 ) == 1 ) $d2 = "0" . $d2;
				$d3 = base_convert( Ord( $arr[2] ) , 10 , 16 );
				if( strlen( $d3 ) == 1 ) $d3 = "0" . $d3;
				$d4 = base_convert( Ord( $arr[3] ) , 10 , 16 );
				if( strlen( $d4 ) == 1 ) $d4 = "0" . $d4;
				$d5 = base_convert( Ord( $arr[4] ) , 10 , 16 );
				if( strlen( $d5 ) == 1 ) $d5 = "0" . $d5;
				$d6 = base_convert( Ord( $arr[5] ) , 10 , 16 );
				if( strlen( $d6 ) == 1 ) $d6 = "0" . $d6;

				$ret_str = $d1.$d2 . "." . $d3.$d4 . "." . $d5.$d6;
			}
			else {
				$ret_str = strtolower( $instr );
				$ret_str = str_replace( "hex-: " , "" , $ret_str );
				$ret_str = str_replace( "hex-:" , "" , $ret_str );
				$ret_str = str_replace( "hex-" , "" , $ret_str );
				$ret_str = str_replace( "hex" , "" , $ret_str );
				$ret_str = str_replace( "\"" , "" , $ret_str );
				$ret_str = trim( $ret_str );
				$ret_str = str_replace( " " , ":" , $ret_str );
				$arr = explode( ":" , $ret_str );
				if( count( $arr ) > 1 ) {
					$ret_str = $arr[0].$arr[1] . "." . $arr[2].$arr[3] . "." . $arr[4].$arr[5];
				}
			}
			return $ret_str;
		}

		public function _getMacAddress( $instr ) {
			return $this->_get_mac_address( $instr );
		}

		public function _getPhysicalAddress( $instr ) {
			return $this->_get_mac_address( $instr );
		}

		public function _getCiscoNetworkAddress( $instr ) {
			if( strlen( $instr ) == 4 ) {
				$arr = str_split( $instr );
				$d1 = Ord( $arr[0] );
				//if( $d1 == 46 ) $d1 = "10";
				$d2 = Ord( $arr[1] );
				//if( $d2 == 46 ) $d2 = "10";
				$d3 = Ord( $arr[2] );
				//if( $d3 == 46 ) $d3 = "11";
				$d4 = Ord( $arr[3] );
				//if( $d4 == 46 ) $d4 = "13";

				$ret_str = $d1 . "." . $d2 . "." . $d3 . "." . $d4;
				if( $ret_str == "46.46.46.46" ) $ret_str = "10.10.11.13";
				$ret_str = str_replace( "46.46.46" , "10.10.11" , $ret_str );
			}
			else {
				$ret_str = str_replace( "Hex: " , "" , $instr );
				$ret_str = str_replace( "\"" , "" , $ret_str );
				$ret_str = str_replace( "STRING: " , "" , $ret_str );
				$ret_str = str_replace( "string:: " , "" , $ret_str );
				$ret_str = str_replace( "IpAddress: " , "" , $ret_str );
				$ret_str = str_replace( "IPADDRESS: " , "" , $ret_str );
				$ret_str = trim( $ret_str );

				if( strlen( $ret_str ) > 4 ) {
					$arr = explode( " " , $ret_str );
					$ret_str = "";
					for( $i = 0; $i < count( $arr ) - 1; $i ++ ) {
						$ret_str .= base_convert( $arr[ $i ] , 16 , 10 ) . ".";
					}

					$ret_str .= base_convert( $arr[ count( $arr ) - 1 ] , 16 , 10 );
				}
			}
			return $ret_str;
		}

		public function _getIanaIfType( $intype ) {
			$out_type = "none";

			switch( $intype ) {
				case 1: $out_type = "other"; break;
				case 2: $out_type = "regular1822"; break;
				case 3: $out_type = "hdh1822"; break;
				case 4: $out_type = "ddnX25"; break;
				case 5: $out_type = "rfc877x25"; break;
				case 6: $out_type = "ethernetCsmacd"; break;
				case 7: $out_type = "iso88023Csmacd"; break;
				case 8: $out_type = "iso88024TokenBus"; break;
				case 9: $out_type = "iso88025TokenRing"; break;
				case 10: $out_type = "iso88026Man"; break;
				case 11: $out_type = "starLan"; break;
				case 12: $out_type = "proteon10Mbit"; break;
				case 13: $out_type = "proteon80Mbit"; break;
				case 14: $out_type = "hyperchannel"; break;
				case 15: $out_type = "fddi"; break;
				case 16: $out_type = "lapb"; break;
				case 17: $out_type = "sdlc"; break;
				case 18: $out_type = "ds1"; break;
				case 19: $out_type = "e1"; break;
				case 20: $out_type = "basicISDN"; break;
				case 21: $out_type = "primaryISDN"; break;
				case 22: $out_type = "propPointToPointSerial"; break;
				case 23: $out_type = "ppp"; break;
				case 24: $out_type = "softwareLoopback"; break;
				case 25: $out_type = "eon"; break;
				case 26: $out_type = "ethernet3Mbit"; break;
				case 27: $out_type = "nsip"; break;
				case 28: $out_type = "slip"; break;
				case 29: $out_type = "ultra"; break;
				case 30: $out_type = "ds3"; break;
				case 31: $out_type = "sip"; break;
				case 32: $out_type = "frameRelay"; break;
				case 33: $out_type = "rs232"; break;
				case 34: $out_type = "para"; break;
				case 35: $out_type = "arcnet"; break;
				case 36: $out_type = "arcnetPlus"; break;
				case 37: $out_type = "atm"; break;
				case 38: $out_type = "miox25"; break;
				case 39: $out_type = "sonet"; break;
				case 40: $out_type = "x25ple"; break;
				case 41: $out_type = "iso88022llc"; break;
				case 42: $out_type = "localTalk"; break;
				case 43: $out_type = "smdsDxi"; break;
				case 44: $out_type = "frameRelayService"; break;
				case 45: $out_type = "v35"; break;
				case 46: $out_type = "hssi"; break;
				case 47: $out_type = "hippi"; break;
				case 48: $out_type = "modem"; break;
				case 49: $out_type = "aal5"; break;
				case 50: $out_type = "sonetPath"; break;
				case 51: $out_type = "sonetVT"; break;
				case 52: $out_type = "smdsIcip"; break;
				case 53: $out_type = "propVirtual"; break;
				case 54: $out_type = "propMultiplexor"; break;
				case 55: $out_type = "ieee80212"; break;
				case 56: $out_type = "fibreChannel"; break;
				case 57: $out_type = "hippiInterface"; break;
				case 58: $out_type = "frameRelayInterconnect"; break;
				case 59: $out_type = "aflane8023"; break;
				case 60: $out_type = "aflane8025"; break;
				case 61: $out_type = "cctEmul"; break;
				case 62: $out_type = "fastEther"; break;
				case 63: $out_type = "isdn"; break;
				case 64: $out_type = "v11"; break;
				case 65: $out_type = "v36"; break;
				case 66: $out_type = "g703at64k"; break;
				case 67: $out_type = "g703at2mb"; break;
				case 68: $out_type = "qllc"; break;
				case 69: $out_type = "fastEtherFX"; break;
				case 70: $out_type = "channel"; break;
				case 71: $out_type = "ieee80211"; break;
				case 72: $out_type = "ibm370parChan"; break;
				case 73: $out_type = "escon"; break;
				case 74: $out_type = "dlsw"; break;
				case 75: $out_type = "isdns"; break;
				case 76: $out_type = "isdnu"; break;
				case 77: $out_type = "lapd"; break;
				case 78: $out_type = "ipSwitch"; break;
				case 79: $out_type = "rsrb"; break;
				case 80: $out_type = "atmLogical"; break;
				case 81: $out_type = "ds0"; break;
				case 82: $out_type = "ds0Bundle"; break;
				case 83: $out_type = "bsc"; break;
				case 84: $out_type = "async"; break;
				case 85: $out_type = "cnr"; break;
				case 86: $out_type = "iso88025Dtr"; break;
				case 87: $out_type = "eplrs"; break;
				case 88: $out_type = "arap"; break;
				case 89: $out_type = "propCnls"; break;
				case 90: $out_type = "hostPad"; break;
				case 91: $out_type = "termPad"; break;
				case 92: $out_type = "frameRelayMPI"; break;
				case 93: $out_type = "x213"; break;
				case 94: $out_type = "adsl"; break;
				case 95: $out_type = "radsl"; break;
				case 96: $out_type = "sdsl"; break;
				case 97: $out_type = "vdsl"; break;
				case 98: $out_type = "iso88025CRFPInt"; break;
				case 99: $out_type = "myrinet"; break;
				case 100: $out_type = "voiceEM"; break;
				case 101: $out_type = "voiceFXO"; break;
				case 102: $out_type = "voiceFXS"; break;
				case 103: $out_type = "voiceEncap"; break;
				case 104: $out_type = "voiceOverIp"; break;
				case 105: $out_type = "atmDxi"; break;
				case 106: $out_type = "atmFuni"; break;
				case 107: $out_type = "atmIma"; break;
				case 108: $out_type = "pppMultilinkBundle"; break;
				case 109: $out_type = "ipOverCdlc"; break;
				case 110: $out_type = "ipOverClaw"; break;
				case 111: $out_type = "stackToStack"; break;
				case 112: $out_type = "virtualIpAddress"; break;
				case 113: $out_type = "mpc"; break;
				case 114: $out_type = "ipOverAtm"; break;
				case 115: $out_type = "iso88025Fiber"; break;
				case 116: $out_type = "tdlc"; break;
				case 117: $out_type = "gigabitEthernet"; break;
				case 118: $out_type = "hdlc"; break;
				case 119: $out_type = "lapf"; break;
				case 120: $out_type = "v37"; break;
				case 121: $out_type = "x25mlp"; break;
				case 122: $out_type = "x25huntGroup"; break;
				case 123: $out_type = "trasnpHdlc"; break;
				case 124: $out_type = "interleave"; break;
				case 125: $out_type = "fast"; break;
				case 126: $out_type = "ip"; break;
				case 127: $out_type = "docsCableMaclayer"; break;
				case 128: $out_type = "docsCableDownstream"; break;
				case 129: $out_type = "docsCableUpstream"; break;
				case 130: $out_type = "a12MppSwitch"; break;
				case 131: $out_type = "tunnel"; break;
				case 132: $out_type = "coffee"; break;
				case 133: $out_type = "ces"; break;
				case 134: $out_type = "atmSubInterface"; break;
				case 135: $out_type = "l2vlan"; break;
				case 136: $out_type = "l3ipvlan"; break;
				case 137: $out_type = "l3ipxvlan"; break;
				case 138: $out_type = "digitalPowerline"; break;
				case 139: $out_type = "mediaMailOverIp"; break;
				case 140: $out_type = "dtm"; break;
				case 141: $out_type = "dcn"; break;
				case 142: $out_type = "ipForward"; break;
				case 143: $out_type = "msdsl"; break;
				case 144: $out_type = "ieee1394"; break;
				case 145: $out_type = "if-gsn"; break;
				case 146: $out_type = "dvbRccMacLayer"; break;
				case 147: $out_type = "dvbRccDownstream"; break;
				case 148: $out_type = "dvbRccUpstream"; break;
				case 149: $out_type = "atmVirtual"; break;
				case 150: $out_type = "mplsTunnel"; break;
				case 151: $out_type = "srp"; break;
				case 152: $out_type = "voiceOverAtm"; break;
				case 153: $out_type = "voiceOverFrameRelay"; break;
				case 154: $out_type = "idsl"; break;
				case 155: $out_type = "compositeLink"; break;
				case 156: $out_type = "ss7SigLink"; break;
				case 157: $out_type = "propWirelessP2P"; break;
				case 158: $out_type = "frForward"; break;
				case 159: $out_type = "rfc1483"; break;
				case 160: $out_type = "usb"; break;
				case 161: $out_type = "ieee8023adLag"; break;
				case 162: $out_type = "bgppolicyaccounting"; break;
				case 163: $out_type = "frf16MfrBundle"; break;
				case 164: $out_type = "h323Gatekeeper"; break;
				case 165: $out_type = "h323Proxy"; break;
				case 166: $out_type = "mpls"; break;
				case 167: $out_type = "mfSigLink"; break;
				case 168: $out_type = "hdsl2"; break;
				case 169: $out_type = "shdsl"; break;
				case 170: $out_type = "ds1FDL"; break;
				case 171: $out_type = "pos"; break;
				case 172: $out_type = "dvbAsiIn"; break;
				case 173: $out_type = "dvbAsiOut"; break;
				case 174: $out_type = "plc"; break;
				case 175: $out_type = "nfas"; break;
				case 176: $out_type = "tr008"; break;
				case 177: $out_type = "gr303RDT"; break;
				case 178: $out_type = "gr303IDT"; break;
				case 179: $out_type = "isup"; break;
				case 180: $out_type = "propDocsWirelessMaclayer"; break;
				case 181: $out_type = "propDocsWirelessDownstream"; break;
				case 182: $out_type = "propDocsWirelessUpstream"; break;
				case 183: $out_type = "hiperlan2"; break;
				case 184: $out_type = "propBWAp2Mp"; break;
				case 185: $out_type = "sonetOverheadChannel"; break;
				case 186: $out_type = "digitalWrapperOverheadChannel"; break;
				case 187: $out_type = "aal2"; break;
				case 188: $out_type = "radioMAC"; break;
				case 189: $out_type = "atmRadio"; break;
				case 190: $out_type = "imt"; break;
				case 191: $out_type = "mvl"; break;
				case 192: $out_type = "reachDSL"; break;
				case 193: $out_type = "frDlciEndPt"; break;
				case 194: $out_type = "atmVciEndPt"; break;
				case 195: $out_type = "opticalChannel"; break;
				case 196: $out_type = "opticalTransport"; break;
				case 197: $out_type = "propAtm"; break;
				case 198: $out_type = "voiceOverCable"; break;
				case 199: $out_type = "infiniband"; break;
				case 200: $out_type = "teLink"; break;
				case 201: $out_type = "q2931"; break;
				case 202: $out_type = "virtualTg"; break;
				case 203: $out_type = "sipTg"; break;
				case 204: $out_type = "sipSig"; break;
				case 205: $out_type = "docsCableUpstreamChannel"; break;
				case 206: $out_type = "econet"; break;
				case 207: $out_type = "pon155"; break;
				case 208: $out_type = "pon622"; break;
				case 209: $out_type = "bridge"; break;
				case 210: $out_type = "linegroup"; break;
				case 211: $out_type = "voiceEMFGD"; break;
				case 212: $out_type = "voiceFGDEANA"; break;
				case 213: $out_type = "voiceDID"; break;
				case 214: $out_type = "mpegTransport"; break;
				case 215: $out_type = "sixToFour"; break;
				case 216: $out_type = "gtp"; break;
				case 217: $out_type = "pdnEtherLoop1"; break;
				case 218: $out_type = "pdnEtherLoop2"; break;
				case 219: $out_type = "opticalChannelGroup"; break;
				case 220: $out_type = "homepna"; break;
				case 221: $out_type = "gfp"; break;
				case 222: $out_type = "ciscoISLvlan"; break;
				case 223: $out_type = "actelisMetaLOOP"; break;
				case 224: $out_type = "fcipLink"; break;
				case 225: $out_type = "rpr"; break;
				case 226: $out_type = "qam"; break;
				case 227: $out_type = "lmp"; break;
				case 228: $out_type = "cblVectaStar"; break;
				case 229: $out_type = "docsCableMCmtsDownstream"; break;
				case 230: $out_type = "adsl2"; break;
				case 231: $out_type = "macSecControlledIF"; break;
				case 232: $out_type = "macSecUncontrolledIF"; break;
				case 233: $out_type = "aviciOpticalEther"; break;
				case 234: $out_type = "atmbond"; break;
			}

			return $out_type;
		}

		public function _getCiscoNetworkProtocol( $instr ) {
			$out = "unknown";

			switch( $instr ) {
				case 1  : $out = "ip"; break;
				case 2  : $out = "decnet"; break;
				case 3  : $out = "pup"; break;
				case 4  : $out = "chaos"; break;
				case 5  : $out = "xns"; break;
				case 6  : $out = "x121"; break;
				case 7  : $out = "appletalk"; break;
				case 8  : $out = "clns"; break;
				case 9  : $out = "lat"; break;
				case 10 : $out = "vines"; break;
				case 11 : $out = "cons"; break;
				case 12 : $out = "apollo"; break;
				case 13 : $out = "stun"; break;
				case 14 : $out = "novell"; break;
				case 15 : $out = "qllc"; break;
				case 16 : $out = "snapshot"; break;
				case 17 : $out = "atmIlmi"; break;
				case 18 : $out = "bstun"; break;
				case 19 : $out = "x25pvc"; break;
				case 20 : $out = "ipv6"; break;
				case 21 : $out = "cdm"; break;
				case 22 : $out = "nbf"; break;
				case 23 : $out = "bpxIgx"; break;
				case 24 : $out = "clnsPfx"; break;
				case 25 : $out = "http"; break;
			}

			return $out;
		}

		public function _getIpLongAddress( $inIp ) {
			$out = "";
			$arr = explode( "." , $inIp );

			if( strlen( $arr[0] ) == 1 ) $out .= "00" . $arr[0] . ".";
			else if( strlen( $arr[0] ) == 2 ) $out .= "0" . $arr[0] . ".";
			else $out .= $arr[0] . ".";

			if( strlen( $arr[1] ) == 1 ) $out .= "00" . $arr[1] . ".";
			else if( strlen( $arr[1] ) == 2 ) $out .= "0" . $arr[1] . ".";
			else $out .= $arr[1] . ".";

			if( strlen( $arr[2] ) == 1 ) $out .= "00" . $arr[2] . ".";
			else if( strlen( $arr[2] ) == 2 ) $out .= "0" . $arr[2] . ".";
			else $out .= $arr[2] . ".";

			if( strlen( $arr[3] ) == 1 ) $out .= "00" . $arr[3];
			else if( strlen( $arr[3] ) == 2 ) $out .= "0" . $arr[3];
			else $out .= $arr[3];

			return $out;
		}

		public function _logWrite( $str ) {
			$new_str = $str . "\r\n";
			if( $this->log_handle )
				fputs( $this->log_handle , $new_str );
		}

		public function clear_all_prefixes( $instr ) {
			$ret_str = str_replace( "String::" , "" , $instr );
			$ret_str = str_replace( "STRING:" , "" , $ret_str );
			$ret_str = str_replace( "hex-:" , "" , $ret_str );
			$ret_str = str_replace( "Hex-:" , "" , $ret_str );
			$ret_str = str_replace( "Hex- " , "" , $ret_str );
			$ret_str = str_replace( "hex-" , "" , $ret_str );
			$ret_str = str_replace( "HEX:" , "" , $ret_str );
			$ret_str = str_replace( "hex:" , "" , $ret_str );
			$ret_str = str_replace( "Counter32:" , "" , $ret_str );
			$ret_str = str_replace( "COUNTER32:" , "" , $ret_str );
			$ret_str = str_replace( "Gauge32:" , "" , $ret_str );
			$ret_str = str_replace( "GAUGE:" , "" , $ret_str );

			$ret_str = str_replace( "INTEGER:" , "" , $ret_str );
			$ret_str = str_replace( "integer:" , "" , $ret_str );
			$ret_str = str_replace( "OID:" , "" , $ret_str );
			$ret_str = str_replace( "oid:" , "" , $ret_str );
			$ret_str = str_replace( "TimeTicks:" , "" , $ret_str );
			$ret_str = str_replace( "TIMETICKS:" , "" , $ret_str );
			$ret_str = str_replace( "IpAddress:" , "" , $ret_str );
			$ret_str = str_replace( "IPADDRESS:" , "" , $ret_str );
			$ret_str = str_replace( "\"" , "" , $ret_str );

			//$ret_str = str_replace( ":" , "" , $ret_str );
			$ret_str = trim( $ret_str );
			return $ret_str;
		}

		public function _getAdminStatus( $adm ) {
			$ret_val = "not_defined";

			switch( $adm ) {
				case 1 : $ret_val = "up"; break;
				case 2 : $ret_val = "down"; break;
				case 3 : $ret_val = "testing"; break;
			}

			return $ret_val;
		}

		public function _getOperationStatus( $adm ) {
			$ret_val = "not_defined";

			switch( $adm ) {
				case 1 : $ret_val = "up"; break;
				case 2 : $ret_val = "down"; break;
				case 3 : $ret_val = "testing"; break;
				case 4 : $ret_val = "unknown"; break;
				case 5 : $ret_val = "dormant"; break;
				case 6 : $ret_val = "not present"; break;
				case 7 : $ret_val = "lower layer down"; break;
			}

			return $ret_val;
		}

		public function _getPortAdditionalOperationStatus( $adm ) {
			$ret_val = "not_defined";

			switch( $adm ) {
				case 0 : $ret_val = "other"; break;
				case 1 : $ret_val = "connected"; break;
				case 2 : $ret_val = "standby"; break;
				case 3 : $ret_val = "faulty"; break;
				case 4 : $ret_val = "notConnected"; break;
				case 5 : $ret_val = "inactive"; break;
				case 6 : $ret_val = "shutdown"; break;
				case 7 : $ret_val = "dripDis"; break;
				case 8 : $ret_val = "disabled"; break;
				case 9 : $ret_val = "monitor"; break;
				case 10 : $ret_val = "errdisable"; break;
				case 11 : $ret_val = "linkFaulty"; break;
				case 12 : $ret_val = "onHook"; break;
				case 13 : $ret_val = "offHook"; break;
				case 14 : $ret_val = "reflector"; break;
			}

			return $ret_val;
		}

		public function _getLearnedStatus( $lst ) {
			$ret_val = "";

			switch( $lst ) {
				case 1 : $ret_val = "other"; break;
				case 2 : $ret_val = "invalid"; break;
				case 3 : $ret_val = "learned"; break;
				case 4 : $ret_val = "self"; break;
				case 5 : $ret_val = "management"; break;
				case 6 : $ret_val = "not defined"; break;
			}

			return $ret_val;
		}

		public function _getIpKey( $inKey ) {
			$out = "";
			$arr = explode( "." , $inKey );
			$cnt = count( $arr );
			if( $cnt > 4 ) {
				$out .= $arr[ $cnt - 4 ] . ".";
				$out .= $arr[ $cnt - 3 ] . ".";
				$out .= $arr[ $cnt - 2 ] . ".";
				$out .= $arr[ $cnt - 1 ];
			}
			else $out = $inKey;

			return $out;
		}

		public function _setOidTable( $strOidName , $strOidVal , $oidType ) {
			$this->oids[ $strOidName ]["oid"] = $strOidVal;
			$this->oids[ $strOidName ]["oid"] = str_replace( "iso" , "1" , $this->oids[ $strOidName ]["oid"] );
			$this->oids[ $strOidName ]["type"] = $oidType;
		}

		public function _readValue( $this_oid , $val ){

			$ret_val = "";

			switch( $this->oids[ $this_oid ]["type"] ) {
				case $this->TYPE_DISPLAY_STRING : $ret_val = $this->_getDisplayString( $val ); break;
				case $this->TYPE_OCTET_STRING : $ret_val = $this->_getOctetString( $val ); break;
				case $this->TYPE_SNMP_ADMIN_STRING : $ret_val = $this->_getSnmpAdminString( $val ); break;
				case $this->TYPE_OID : $ret_val = $this->_removeOid( $val ); break;

				case $this->TYPE_IANA_IF_TYPE : $ret_val = $this->_getIanaIfType( $val ); break;

				case $this->TYPE_GAUGE32 : $ret_val = $this->_getGauge32( $val ); break;
				case $this->TYPE_COUNTER32 : $ret_val = $this->_getCounter32( $val ); break;
				case $this->TYPE_INTEGER32 : $ret_val = $this->_getInteger32( $val ); break;
				case $this->TYPE_INTEGER : $ret_val = $this->_getInteger( $val ); break;
				case $this->TYPE_INTERFACE_INDEX : $ret_val = $this->_getInterfaceIndex( $val ); break;
				case $this->TYPE_VLAN_INDEX : $ret_val = $this->_getVlanIndex( $val ); break;

				case $this->TYPE_TIME_TICK : $ret_val = $this->_getTimeTick( $val ); break;
				case $this->TYPE_TIME_STAMP : $ret_val = $this->_getTimeStamp( $val ); break;

				case $this->TYPE_IP_ADDRESS : $ret_val = $this->_getIpAddr( $val ); break;
				case $this->TYPE_MAC_ADDRESS : $ret_val = $this->_getMacAddress( $val ); break;
				case $this->TYPE_PHYSICAL_ADDRESS : $ret_val = $this->_getPhysicalAddress( $val ); break;
				case $this->TYPE_CISCO_NETWORK_ADDRESS : $ret_val = $this->_getCiscoNetworkAddress( $val ); break;
			}

			return $ret_val;
		}

		public function init_oids(){

			$this->oids = array();
			$this->_setOidTable( "sysDescr" 					, ".iso.3.6.1.2.1.1.1.0" 				, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "sysObjectID" 					, ".iso.3.6.1.2.1.1.2.0" 				, $this->TYPE_OID );
			$this->_setOidTable( "sysUpTime" 					, ".iso.3.6.1.2.1.1.3.0" 				, $this->TYPE_TIME_TICK );
			$this->_setOidTable( "sysContact" 					, ".iso.3.6.1.2.1.1.4.0" 				, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "sysName" 						, ".iso.3.6.1.2.1.1.5.0" 				, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "sysLocation" 					, ".iso.3.6.1.2.1.1.6.0" 				, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "sysServices" 					, ".iso.3.6.1.2.1.1.7.0" 				, $this->TYPE_INTEGER );
			$this->_setOidTable( "sysORLastChange" 				, ".iso.3.6.1.2.1.1.8.0" 				, $this->TYPE_TIME_STAMP );

			$this->_setOidTable( "ifNumber" 					, ".iso.3.6.1.2.1.2.1.0" 				, $this->TYPE_INTEGER32 );

			$this->_setOidTable( "probeDownloadFile" 			, ".iso.3.6.1.2.1.16.19.6.0" 			, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "netDefaultGateway" 			, ".iso.3.6.1.2.1.16.19.12.0" 			, $this->TYPE_IP_ADDRESS );

			$this->_setOidTable( "dot1dBaseNumPorts" 			, ".iso.3.6.1.2.1.17.1.2.0" 			, $this->TYPE_INTEGER );
			$this->_setOidTable( "ipAdEntAddr" 					, ".iso.3.6.1.2.1.4.20.1.1.0" 			, $this->TYPE_IP_ADDRESS );

			$this->_setOidTable( "ifIndex" 						, ".iso.3.6.1.2.1.2.2.1.1" 				, $this->TYPE_INTERFACE_INDEX );
			$this->_setOidTable( "ifDescr" 						, ".iso.3.6.1.2.1.2.2.1.2" 				, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "ifType" 						, ".iso.3.6.1.2.1.2.2.1.3" 				, $this->TYPE_IANA_IF_TYPE );
			$this->_setOidTable( "ifMtu" 						, ".iso.3.6.1.2.1.2.2.1.4" 				, $this->TYPE_INTEGER32 );
			$this->_setOidTable( "ifSpeed" 						, ".iso.3.6.1.2.1.2.2.1.5" 				, $this->TYPE_GAUGE32 );
			$this->_setOidTable( "ifPhysAddress" 				, ".iso.3.6.1.2.1.2.2.1.6" 				, $this->TYPE_PHYSICAL_ADDRESS );
			$this->_setOidTable( "ifAdminStatus" 				, ".iso.3.6.1.2.1.2.2.1.7" 				, $this->TYPE_INTEGER );
			$this->_setOidTable( "ifOperStatus" 				, ".iso.3.6.1.2.1.2.2.1.8" 				, $this->TYPE_INTEGER );
			$this->_setOidTable( "ifLastChange" 				, ".iso.3.6.1.2.1.2.2.1.9" 				, $this->TYPE_TIME_TICK );
			$this->_setOidTable( "ifInOctets" 					, ".iso.3.6.1.2.1.2.2.1.10" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifInUcastPkts" 				, ".iso.3.6.1.2.1.2.2.1.11" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifInNUcastPkts" 				, ".iso.3.6.1.2.1.2.2.1.12" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifInDiscards" 				, ".iso.3.6.1.2.1.2.2.1.13" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifInErrors" 					, ".iso.3.6.1.2.1.2.2.1.14" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifInUnknownProtos" 			, ".iso.3.6.1.2.1.2.2.1.15" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifOutOctets" 					, ".iso.3.6.1.2.1.2.2.1.16" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifOutUcastPkts" 				, ".iso.3.6.1.2.1.2.2.1.17" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifOutNUcastPkts" 				, ".iso.3.6.1.2.1.2.2.1.18" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifOutDiscards" 				, ".iso.3.6.1.2.1.2.2.1.19" 			, $this->TYPE_COUNTER32 );
			$this->_setOidTable( "ifOutErrors" 					, ".iso.3.6.1.2.1.2.2.1.20" 			, $this->TYPE_COUNTER32 );

			$this->_setOidTable( "dot3StatsIndex" 				, ".iso.3.6.1.2.1.10.7.2.1.1" 			, $this->TYPE_INTERFACE_INDEX );

			$this->_setOidTable( "dot1dTpFdbAddress" 			, ".iso.3.6.1.2.1.17.4.3.1.1" 			, $this->TYPE_MAC_ADDRESS ); // mac address table
			$this->_setOidTable( "dot1dTpFdbPort" 				, ".iso.3.6.1.2.1.17.4.3.1.2" 			, $this->TYPE_INTEGER ); // mac address learned port number
			$this->_setOidTable( "dot1dTpFdbStatus" 			, ".iso.3.6.1.2.1.17.4.3.1.3" 			, $this->TYPE_INTEGER );

			$this->_setOidTable( "ifName" 						, ".iso.3.6.1.2.1.31.1.1.1.1" 			, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "ifHighSpeed" 					, ".iso.3.6.1.2.1.31.1.1.1.15" 			, $this->TYPE_GAUGE32 );
			$this->_setOidTable( "ifAlias" 						, ".iso.3.6.1.2.1.31.1.1.1.18" 			, $this->TYPE_DISPLAY_STRING );

			$this->_setOidTable( "entPhysicalDescr" 			, ".iso.3.6.1.2.1.47.1.1.1.1.2" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entPhysicalName" 				, ".iso.3.6.1.2.1.47.1.1.1.1.7" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entPhysicalHardwareRev" 		, ".iso.3.6.1.2.1.47.1.1.1.1.8" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entPhysicalFirmwareRev" 		, ".iso.3.6.1.2.1.47.1.1.1.1.9" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entPhysicalSoftwareRev" 		, ".iso.3.6.1.2.1.47.1.1.1.1.10" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entPhysicalSerialNum" 		, ".iso.3.6.1.2.1.47.1.1.1.1.11" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entPhysicalMfgName" 			, ".iso.3.6.1.2.1.47.1.1.1.1.12" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entPhysicalModelName" 		, ".iso.3.6.1.2.1.47.1.1.1.1.13" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entPhysicalAlias" 			, ".iso.3.6.1.2.1.47.1.1.1.1.14" 		, $this->TYPE_SNMP_ADMIN_STRING );

			$this->_setOidTable( "entLogicalDescr" 				, ".iso.3.6.1.2.1.47.1.2.1.1.2" 		, $this->TYPE_SNMP_ADMIN_STRING );
			$this->_setOidTable( "entLogicalCommunity" 			, ".iso.3.6.1.2.1.47.1.2.1.1.4" 		, $this->TYPE_OCTET_STRING );
			$this->_setOidTable( "entLogicalContextName" 		, ".iso.3.6.1.2.1.47.1.2.1.1.8" 		, $this->TYPE_SNMP_ADMIN_STRING );

			$this->_setOidTable( "vtpVlanIndex" 				, ".iso.3.6.1.4.1.9.9.46.1.3.1.1.1" 	, $this->TYPE_VLAN_INDEX );
			$this->_setOidTable( "vtpVlanName" 					, ".iso.3.6.1.4.1.9.9.46.1.3.1.1.4" 	, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "vtpVlanBridgeNumber" 			, ".iso.3.6.1.4.1.9.9.46.1.3.1.1.8" 	, $this->TYPE_INTEGER );
			$this->_setOidTable( "vtpVlanBridgeType" 			, ".iso.3.6.1.4.1.9.9.46.1.3.1.1.13" 	, $this->TYPE_INTEGER );
			$this->_setOidTable( "vtpVlanIfIndex" 				, ".iso.3.6.1.4.1.9.9.46.1.3.1.1.18" 	, $this->TYPE_INTEGER );
			$this->_setOidTable( "vlanTrunkPortDynamicStatus" 	, ".iso.3.6.1.4.1.9.9.46.1.6.1.1.14" 	, $this->TYPE_INTEGER );

			$this->_setOidTable( "dot1dBasePort" 				, ".iso.3.6.1.2.1.17.1.4.1.1" 			, $this->TYPE_INTEGER );
			$this->_setOidTable( "dot1dBasePortIfIndex" 		, ".iso.3.6.1.2.1.17.1.4.1.2" 			, $this->TYPE_INTEGER );

			$this->_setOidTable( "vmVlan" 						, ".iso.3.6.1.4.1.9.9.68.1.2.2.1.2" 	, $this->TYPE_INTEGER );

			$this->_setOidTable( "ipNetToMediaPhysAddress" 		, ".iso.3.6.1.2.1.4.22.1.2" 			, $this->TYPE_PHYSICAL_ADDRESS );
			$this->_setOidTable( "cdpCacheAddress" 				, ".iso.3.6.1.4.1.9.9.23.1.2.1.1.4" 	, $this->TYPE_CISCO_NETWORK_ADDRESS );
			$this->_setOidTable( "cdpCacheDeviceId" 			, ".iso.3.6.1.4.1.9.9.23.1.2.1.1.6" 	, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "cdpCacheDevicePort" 			, ".iso.3.6.1.4.1.9.9.23.1.2.1.1.7" 	, $this->TYPE_DISPLAY_STRING );
			$this->_setOidTable( "cdpChachePlatform" 			, ".iso.3.6.1.4.1.9.9.23.1.2.1.1.8" 	, $this->TYPE_DISPLAY_STRING );

			$this->_setOidTable( "portAdditionalOperStatus" 	, ".iso.3.6.1.4.1.9.5.1.4.1.1.23" 		, $this->TYPE_INTEGER );

		}

		public function _getOid( $oidName , $default , $rplc ) {
			$value = @snmpget( $this->ip_address , $this->community , $this->oids[ $oidName ]["oid"] );
			if( !$value ) $value = $default;
			else {
				$value = $this->_readValue( $oidName , $value );
				foreach( $rplc as $rplc_key => $rplc_value ) {
					$value = str_replace( $rplc_key , $rplc_value , $value );
				}
			}

			return $value;
		}

		public function _setOid( $oidName , $type , $value , $index ) {
			$value = @snmpset( $this->ip_address , $this->community , $this->oids[ $oidName ]["oid"] . "." . $index  , $type , $value );
			return $value;
		}

		public function _walkOid( $vlandid , $oidName ) {
			$new_walk_result = array();
			$new_community = $this->community;
			if( ( $vlandid != "" ) && ( $vlandid != null ) ) $new_community .= "@" . $vlandid;

			$walk_result = snmprealwalk( $this->ip_address , $new_community , $this->oids[ $oidName ]["oid"] );
			//$walk_result = snmpwalk( $this->ip_address , $new_community , $this->oids[ $oidName ]["oid"] );

			if( !$walk_result ) {
				return array();
			}
			else {
				foreach( $walk_result as $key => $value ) {
					$vvv = $this->oids[ $oidName ]["oid"];
					if( strpos( $key , ".iso" ) === false ) {
						$new_key = str_replace( "iso" , "1" , $vvv );

					}
					$new_key = str_replace( $vvv . "." , "" , $key );
					$new_walk_result[ $new_key ] = $this->_readValue( $oidName , $value );
				}
				$walk_result = array();
			}
			return $new_walk_result;
		}

		public function _walkOidX( $vlandid , $oidName ) {
			$new_walk_result = array();
			$new_community = $this->community;
			if( ( $vlandid != "" ) && ( $vlandid != null ) ) $new_community .= "@" . $vlandid;

			$walk_result = @snmprealwalk( $this->ip_address , $new_community , $this->oids[ $oidName ]["oid"] );

			if( !$walk_result ) { print "\r\nwalkOid FAILED for OID => " . $oidName . " , VLAN => " . $vlandid . "\r\n"; return false; }
			else {
				foreach( $walk_result as $key => $value ) {
					$new_key = str_replace( $this->oids[ $oidName ]["oid"] . "." , "" , $key );
					$new_walk_result[ $new_key ] = $this->_readValue( $oidName , $value );
				}
				$walk_result = array();

				//print "\r\n" . $this->ip_address . " => " . $oidName . " => " . $vlandid . "\r\n"; print_r( $new_walk_result ); print "\r\n";
			}

			return $new_walk_result;
		}

		public function _read_oid( $oidName , $vlanArray ) {
			if( is_array( $vlanArray )) {
				foreach( $vlanArray as $key => $value ) {
					$arx = @snmprealwalk( $this->ip_address , $this->community . "@" . $value , $this->oids[ $oidName ]["oid"] );
					if( $arx ) {
						foreach( $arx as $key_x => $value_x ) {
							$tmp = str_replace( $this->oids[ $oidName ]["oid"] . "." , "" , $key_x );
							$this->oidBuffer[ $oidName ][ $tmp ] = $this->_readValue( $oidName , $value_x );
						}
						$arx = array();
						//print $oidName . "\r\n"; print_r( $this->oidBuffer[ $oidName ] ); print "\r\n";
					}
					//else {
					//	print "\r\nFAILED : " . $oidName . "@" . $value . "\r\n";
					//}
				}
			}
			else {
				$arx = @snmprealwalk( $this->ip_address , $this->community , $this->oids[ $oidName ]["oid"] );
				foreach( $arx as $key_x => $value_x ) {
					$tmp = str_replace( $this->oids[ $oidName ]["oid"] . "." , "" , $key_x );
					$this->oidBuffer[ $oidName ][ $tmp ] = $this->_readValue( $oidName , $value_x );
				}
				$arx = array();
				//print $oidName . "\r\n"; print_r( $this->oidBuffer[ $oidName ] ); print "\r\n";
			}
		}


		public function __construct() {
			if( function_exists( "snmp_set_oid_numeric_print" ) ) snmp_set_oid_numeric_print(true);
			if( function_exists( "snmp_set_quick_print" ) ) snmp_set_quick_print(true);
			if( function_exists( "snmp_set_valueretrieval" ) ) snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
			$this->init_oids();
		}

		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------
		//----------------------------------------------------------------------------------------------------------


		public function _sysDescr() { return $this->_getOid( "sysDescr" , "" , array() ); }
		public function _sysObjectID() { return $this->_getOid( "sysObjectID" , "" , array() ); }
		public function _sysUpTime() { return $this->_getOid( "sysUpTime" , "" , array() ); }
		public function _sysContact() { return $this->_getOid( "sysContact" , "" , array() ); }
		public function _sysName() { return $this->_getOid( "sysName" , "" , array( ".iyte.edu.tr" => "" ) ); }
		public function _sysLocation() { return $this->_getOid( "sysLocation" , "" , array() ); }
		public function _sysServices() { return $this->_getOid( "sysServices" , "" , array() ); }
		public function _sysORLastChange() { return $this->_getOid( "sysORLastChange" , "" , array() ); }
		public function _ifNumber() { return $this->_getOid( "ifNumber" , 0 , array() ); }
		public function _probeDownloadFile() { return $this->_getOid( "probeDownloadFile" , "" , array( "flash:" => "" ) ); }
		public function _netDefaultGateway() { return $this->_getOid( "netDefaultGateway" , "" , array() ); }

		public function _ipAdEntAddr() { return $this->_walkOid( "" , "ipAdEntAddr" ); }
		public function _ifIndex() { return $this->_walkOid( "" , "ifIndex" ); }
		public function _ifDescr() { return $this->_walkOid( "" , "ifDescr" ); }
		public function _ifType() { return $this->_walkOid( "" , "ifType" ); }
		public function _ifMtu() { return $this->_walkOid( $key_x , "ifMtu" ); }
		public function _ifSpeed() { return $this->_walkOid( $key_x , "ifSpeed" ); }
		public function _ifPhysAddress() { return $this->_walkOid( $key_x , "ifPhysAddress" ); }
		public function _ifAdminStatus() { return $this->_walkOid( "" , "ifAdminStatus" ); }
		public function _ifOperStatus() { return $this->_walkOid( "" , "ifOperStatus" ); }
		public function _portAdditionalOperStatus() { return $this->_walkOid( "" , "portAdditionalOperStatus" ); }
		public function _ifLastChange() { return $this->_walkOid( "" , "ifLastChange" ); }
		public function _ifInOctets() { return $this->_walkOid( "" , "ifInOctets" ); }
		public function _ifInUcastPkts() { return $this->_walkOid( "" , "ifInUcastPkts" ); }
		public function _ifInNUcastPkts() { return $this->_walkOid( "" , "ifInNUcastPkts" ); }
		public function _ifInDiscards() { return $this->_walkOid( "" , "ifInDiscards" ); }
		public function _ifInErrors() { return $this->_walkOid( "" , "ifInErrors" ); }
		public function _ifInUnknownProtos() { return $this->_walkOid( "" , "ifInUnknownProtos" ); }
		public function _ifOutOctets() { return $this->_walkOid( "" , "ifOutOctets" ); }
		public function _ifOutUcastPkts() { return $this->_walkOid( "" , "ifOutUcastPkts" ); }
		public function _ifOutNUcastPkts() { return $this->_walkOid( "" , "ifOutNUcastPkts" ); }
		public function _ifOutDiscards() { return $this->_walkOid( "" , "ifOutDiscards" ); }
		public function _ifOutErrors() { return $this->_walkOid( "" , "ifOutErrors" ); }
		public function _dot3StatsIndex() { return $this->_walkOid( "" , "dot3StatsIndex" ); }
		public function _vtpVlanName() { return $this->_walkOid( "" , "vtpVlanName" ); }
		public function _ifName() { return $this->_walkOid( "" , "ifName" ); }
		public function _ifHighSpeed() { return $this->_walkOid( "" , "ifHighSpeed" ); }
		public function _ifAlias() { return $this->_walkOid( "" , "ifAlias" ); }
		public function _entPhysicalDescr() { return $this->_walkOid( "" , "entPhysicalDescr" ); }
		public function _entPhysicalName() { return $this->_walkOid( "" , "entPhysicalName" ); }
		public function _entPhysicalHardwareRev() { return $this->_walkOid( "" , "entPhysicalHardwareRev" ); }
		public function _entPhysicalFirmwareRev() { return $this->_walkOid( "" , "entPhysicalFirmwareRev" ); }
		public function _entPhysicalSoftwareRev() { return $this->_walkOid( "" , "entPhysicalSoftwareRev" ); }
		public function _entPhysicalSerialNum() { return $this->_walkOid( "" , "entPhysicalSerialNum" ); }
		public function _entPhysicalMfgName() { return $this->_walkOid( "" , "entPhysicalMfgName" ); }
		public function _entPhysicalModelName() { return $this->_walkOid( "" , "entPhysicalModelName" ); }
		public function _entPhysicalAlias() { return $this->_walkOid( "" , "entPhysicalAlias" ); }
		public function _entLogicalDescr() { return $this->_walkOid( "" , "entLogicalDescr" ); }
		public function _entLogicalCommunity() { return $this->_walkOid( "" , "entLogicalCommunity" ); }
		public function _entLogicalContextName() { return $this->_walkOid( "" , "entLogicalContextName" ); }
		public function _vlanTrunkPortDynamicStatus() { return $this->_walkOid( "" , "vlanTrunkPortDynamicStatus" ); }

		public function _dot1dBaseNumPorts( $vlan_array ) {
			$count = 0;

			if( is_null( $vlan_array ) ) {

			}
			else {
				foreach( $vlan_array as $key_x => $value_x ) {
					$value = $this->_getOid( "dot1dBaseNumPorts" , 0 , array() );
					$count += $this->_readValue( "dot1dBaseNumPorts" , $value );
				}
			}
			return $count;
		}

		public function _dot1dTpFdbAddress( $arr_list ) {
			$out_value = array();
			if( !is_array( $arr_list ) ) return $out_value;

			foreach( $arr_list as $key => $value ) {
				$arx_ports = $this->_walkOid( $key , "dot1dTpFdbAddress" );
				foreach( $arx_ports as $key_x => $value_x ) {
					$out_value[ $key_x ] = $value_x;
				}
			}
			return $out_value;
		}

		public function _dot1dTpFdbPort( $arr_list ) {
			$out_value = array();
			if( !is_array( $arr_list ) ) return $out_value;

			foreach( $arr_list as $key => $value ) {
				$arx_ports = $this->_walkOid( $key , "dot1dTpFdbPort" );
				foreach( $arx_ports as $key_x => $value_x ) {
					$out_value[ $key_x ] = $value_x;
				}
			}
			return $out_value;
		}

		public function _dot1dTpFdbStatus( $arr_list ) {
			$out_value = array();
			if( !is_array( $arr_list ) ) return $out_value;

			foreach( $arr_list as $key => $value ) {
				$arx_ports = $this->_walkOid( $key , "dot1dTpFdbStatus" );
				foreach( $arx_ports as $key_x => $value_x ) {
					$out_value[ $key_x ] = $value_x;
				}
			}
			return $out_value;
		}

		public function _vtpVlanIndex( $arr_list ) {
			$out_value = array();
			if( !is_array( $arr_list ) ) return $out_value;

			foreach( $arr_list as $key => $value ) {
				$arx_ports = $this->_walkOid( $key , "vtpVlanIndex" );
				foreach( $arx_ports as $key_x => $value_x ) {
					$out_value[ $key_x ] = $value_x;
				}
			}
			return $out_value;
		}

		public function _vtpVlanBridgeNumber( $arr_list ) {
			$out_value = array();
			if( !is_array( $arr_list ) ) return $out_value;

			foreach( $arr_list as $key => $value ) {
				$arx_ports = $this->_walkOid( $key , "vtpVlanBridgeNumber" );
				foreach( $arx_ports as $key_x => $value_x ) {
					$out_value[ $key_x ] = $value_x;
				}
			}
			return $out_value;
		}

		public function _vtpVlanBridgeType( $arr_list ) {
			$out_value = array();
			if( !is_array( $arr_list ) ) return $out_value;

			foreach( $arr_list as $key => $value ) {
				$arx_ports = $this->_walkOid( $key , "vtpVlanBridgeType" );
				foreach( $arx_ports as $key_x => $value_x ) {
					$out_value[ $key_x ] = $value_x;
				}
			}
			return $out_value;
		}

		public function _vtpVlanIfIndex( $arr_list ) {
			$out_value = array();
			if( !is_array( $arr_list ) ) return $out_value;

			foreach( $arr_list as $key => $value ) {
				$arx_ports = $this->_walkOid( $key , "vtpVlanIfIndex" );
				foreach( $arx_ports as $key_x => $value_x ) {
					$out_value[ $key_x ] = $value_x;
				}
			}
			return $out_value;
		}

		public function _dot1dBasePort( $arr_list ) {
			$out_value = array();
			if( !is_array( $arr_list ) ) return $out_value;

			foreach( $arr_list as $key => $value ) {
				$arx_ports = $this->_walkOid( $key , "dot1dTpFdbStatus" );
				foreach( $arx_ports as $key_x => $value_x ) {
					$out_value[ $key_x ] = $value_x;
				}
			}
			return $out_value;
		}

		public function _dot1dBasePortIfIndex( $array_list ) {
			$out_array = array();

			foreach( $array_list as $key_x => $value_x ) {
				$arx = $this->_walkOid( $key_x , "dot1dBasePortIfIndex" );
				if( $arx ) {
					foreach( $arx as $key => $value ) {
						$out_array[ $key ] = $value;
					}
				}
			}
			return $out_array;
		}

		public function _vmVlan() {
			$arx = $this->_walkOid( "" , "vmVlan" );
			return $arx;
		}

		public function _ipNetToMediaPhysAddress() { return $this->_walkOid( "" , "ipNetToMediaPhysAddress" ); }
		public function _cdpCacheAddress() { return $this->_walkOid( "" , "cdpCacheAddress" ); }
		public function _cdpCacheDeviceId() { return $this->_walkOid( "" , "cdpCacheDeviceId" ); }
		public function _cdpCacheDevicePort() { return $this->_walkOid( "" , "cdpCacheDevicePort" ); }
		public function _cdpChachePlatform() { return $this->_walkOid( "" , "cdpChachePlatform" ); }

		public function setIp( $ip ) {
			$this->ip_address = $ip;
			$this->ip_address_long = $this->_getIpLongAddress( $ip );
		}

		public function setSnmpCommunity( $cmnyt ) {
			$this->community = $cmnyt;
		}

		public function setLogHandle( $hndl ) {
			$this->log_handle = $hndl;
		}

	}
?>