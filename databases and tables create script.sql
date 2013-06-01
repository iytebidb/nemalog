CREATE DATABASE `dhcp` /*!40100 DEFAULT CHARACTER SET latin1 */;
CREATE DATABASE `eduroam` /*!40100 DEFAULT CHARACTER SET latin1 */;
CREATE DATABASE `global` /*!40100 DEFAULT CHARACTER SET latin1 */;
CREATE DATABASE `network` /*!40100 DEFAULT CHARACTER SET latin1 */;

use dhcp;

CREATE TABLE `conf` (
  `parameter` varchar(45) NOT NULL,
  `value` varchar(45) NOT NULL,
  PRIMARY KEY (`parameter`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `host` (
  `host` varchar(45) NOT NULL,
  `subnet` varchar(45) NOT NULL,
  `subnet_name` varchar(45) NOT NULL,
  `fixed_address` varchar(15) NOT NULL,
  `hardware_ethernet` varchar(17) NOT NULL,
  `status` varchar(15) NOT NULL,
  PRIMARY KEY (`host`,`subnet`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `lease` (
  `lease_ip_address` varchar(32) NOT NULL,
  `lease_index` int(11) NOT NULL DEFAULT '0',
  `starts_date` varchar(10) NOT NULL,
  `starts_time` varchar(8) NOT NULL,
  `ends_date` varchar(10) NOT NULL,
  `ends_time` varchar(8) NOT NULL,
  `tstp_date` varchar(10) NOT NULL,
  `tstp_time` varchar(8) NOT NULL,
  `binding_state` int(11) NOT NULL DEFAULT '0',
  `next_binding_state` int(11) NOT NULL DEFAULT '0',
  `hardware_ethernet` varchar(32) NOT NULL,
  `uid` varchar(32) NOT NULL,
  `client_host_name` varchar(64) NOT NULL,
  `ddns_txt` varchar(64) NOT NULL,
  `ddns_fwd_name` varchar(64) NOT NULL,
  PRIMARY KEY (`lease_ip_address`,`lease_index`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `networks` (
  `network` varchar(15) NOT NULL,
  `network_id` varchar(45) NOT NULL DEFAULT '0',
  `network_name` varchar(45) NOT NULL,
  PRIMARY KEY (`network`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `subnet` (
  `subnet` varchar(45) NOT NULL,
  `netmask` varchar(15) NOT NULL,
  `network` varchar(45) NOT NULL,
  `range_start` varchar(15) NOT NULL,
  `range_end` varchar(15) NOT NULL,
  `ddns_updates` varchar(45) NOT NULL,
  `ddns_domain_name` varchar(45) NOT NULL,
  `option_domain_name_servers` varchar(45) NOT NULL,
  `option_routers` varchar(45) NOT NULL,
  `option_broadcast_address` varchar(45) NOT NULL,
  PRIMARY KEY (`subnet`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

use eduroam;
CREATE TABLE `accept` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ap_ip_address` varchar(64) NOT NULL DEFAULT '',
  `user_name` varchar(64) NOT NULL DEFAULT '',
  `user_mac_address` varchar(32) NOT NULL DEFAULT '',
  `dhcp_ip_address` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2468543 DEFAULT CHARSET=latin1;

CREATE TABLE `reject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ap_ip_address` varchar(64) NOT NULL DEFAULT '',
  `user_name` varchar(64) NOT NULL DEFAULT '',
  `user_mac_address` varchar(32) NOT NULL DEFAULT '',
  `reject_reason` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=134584 DEFAULT CHARSET=latin1;

use global;
CREATE TABLE `banned_users` (
  `bdate` varchar(10) NOT NULL,
  `btime` varchar(8) NOT NULL,
  `userid` varchar(45) DEFAULT NULL,
  `userhost` varchar(45) DEFAULT NULL,
  `sip` varchar(15) DEFAULT NULL,
  `sport` varchar(45) DEFAULT NULL,
  `bannedby` varchar(45) DEFAULT NULL,
  `portshutdown` int(11) DEFAULT NULL,
  `eduroamdisabled` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`bdate`,`btime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `guest_info` (
  `user_id` varchar(45) NOT NULL,
  `user_name` varchar(45) NOT NULL,
  `user_surname` varchar(45) NOT NULL,
  `created_date` varchar(45) NOT NULL,
  `created_by` varchar(45) NOT NULL,
  `password` varchar(45) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `onetimepassword` (
  `pwd` varchar(64) NOT NULL,
  `used` int(11) NOT NULL,
  `enabled` int(11) NOT NULL,
  PRIMARY KEY (`pwd`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

use network;
CREATE TABLE `ap_info` (
  `long_ip_address` varchar(45) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `host_name` varchar(45) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `interface_count` int(11) DEFAULT NULL,
  `default_gateway` varchar(45) DEFAULT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `up_time` varchar(45) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `last_change` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`long_ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `cdp` (
  `ip_long` varchar(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `name` varchar(255) NOT NULL,
  `interface` varchar(45) NOT NULL,
  `remote_ip` varchar(15) NOT NULL,
  `remote_name` varchar(255) NOT NULL,
  `remote_interface` varchar(45) NOT NULL,
  `remote_platform` varchar(255) NOT NULL,
  PRIMARY KEY (`ip_long`,`ip`,`name`,`interface`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `devices` (
  `ip_long` varchar(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` int(11) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  PRIMARY KEY (`ip_long`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `interfaces` (
  `ip_long` varchar(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `name` varchar(255) NOT NULL,
  `interface` varchar(45) NOT NULL,
  `vlan` int(11) NOT NULL,
  `trunk` int(11) NOT NULL,
  `admin_status` int(11) NOT NULL,
  `oper_status` int(11) NOT NULL,
  `additional_oper_status` int(11) NOT NULL,
  PRIMARY KEY (`ip_long`,`ip`,`interface`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ip_mac` (
  `ip_long` varchar(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `name` varchar(255) NOT NULL,
  `vlan` int(11) NOT NULL,
  `interface` varchar(45) NOT NULL,
  `mac_address` varchar(17) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  PRIMARY KEY (`ip_long`,`ip`,`name`,`vlan`,`interface`,`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ip_mac_mobil` (
  `rdate` varchar(10) NOT NULL,
  `rtime` varchar(8) NOT NULL,
  `ip_long` varchar(15) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `name` varchar(255) NOT NULL,
  `vlan` int(11) NOT NULL,
  `interface` varchar(45) NOT NULL,
  `mac_address` varchar(17) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  PRIMARY KEY (`rdate`,`rtime`,`ip_long`,`ip`,`name`,`vlan`,`interface`,`mac_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `switch_info` (
  `long_ip_address` varchar(45) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `host_name` varchar(45) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `interface_count` int(11) DEFAULT NULL,
  `default_gateway` varchar(45) DEFAULT NULL,
  `image_name` varchar(255) DEFAULT NULL,
  `up_time` varchar(45) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `last_change` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`long_ip_address`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

