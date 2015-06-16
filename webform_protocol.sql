SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `protocol` (
`id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `tech` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `remote` bit(1) NOT NULL DEFAULT b'0',
  `action_id_1` int(11) NOT NULL,
  `action_id_2` int(11) DEFAULT NULL,
  `action_id_3` int(11) DEFAULT NULL,
  `action_id_4` int(11) DEFAULT NULL,
  `action_id_5` int(11) DEFAULT NULL,
  `action_id_6` int(11) DEFAULT NULL,
  `action_id_7` int(11) DEFAULT NULL,
  `action_id_8` int(11) DEFAULT NULL,
  `action_id_9` int(11) DEFAULT NULL,
  `action_id_10` int(11) DEFAULT NULL,
  `action_id_11` int(11) DEFAULT NULL,
  `action_id_12` int(11) DEFAULT NULL,
  `action_id_13` int(11) DEFAULT NULL,
  `action_id_14` int(11) DEFAULT NULL,
  `work_total` float NOT NULL DEFAULT '0',
  `breaks` int(3) NOT NULL DEFAULT '0',
  `work_net` float NOT NULL DEFAULT '0',
  `part_id_1` int(11) DEFAULT NULL,
  `part_id_2` int(11) DEFAULT NULL,
  `part_id_3` int(11) DEFAULT NULL,
  `part_id_4` int(11) DEFAULT NULL,
  `part_id_5` int(11) DEFAULT NULL,
  `addendum_1` varchar(250) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `addendum_2` varchar(250) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `addendum_3` varchar(250) CHARACTER SET utf8 NOT NULL DEFAULT ''
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `protocol_action` (
`id` int(11) NOT NULL,
  `action` varchar(250) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `begin_time` time NOT NULL DEFAULT '00:00:00',
  `end_time` time NOT NULL DEFAULT '00:00:00',
  `hours` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `protocol_customer` (
`id` int(11) NOT NULL,
  `c_name` varchar(100) NOT NULL DEFAULT '',
  `postcode` int(5) NOT NULL DEFAULT '0',
  `city` varchar(35) NOT NULL DEFAULT '',
  `street` varchar(50) NOT NULL DEFAULT '',
  `street_num` varchar(16) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `contact_form_of_address` varchar(10) NOT NULL DEFAULT '',
  `contact_title` varchar(10) NOT NULL DEFAULT '',
  `contact_first` varchar(30) NOT NULL DEFAULT '',
  `contact_last` varchar(50) NOT NULL DEFAULT ''
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `protocol_part` (
`id` int(11) NOT NULL,
  `p_name` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `descr` varchar(100) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `serial` varchar(50) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `num` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;


ALTER TABLE `protocol`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `protocol_action`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `protocol_customer`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `protocol_part`
 ADD PRIMARY KEY (`id`);


ALTER TABLE `protocol`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
ALTER TABLE `protocol_action`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
ALTER TABLE `protocol_customer`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
ALTER TABLE `protocol_part`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
