SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `test_id_error` (
  `test_id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`test_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `test_save_fields_custom` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `created_on` bigint(10) NOT NULL,
  `updated_on` bigint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

INSERT INTO `test_save_fields_custom` (`id`, `name`, `created_on`, `updated_on`) VALUES
(1, 'test', 0, 1292591236);

CREATE TABLE IF NOT EXISTS `test_save_fields_default` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `created` bigint(10) NOT NULL,
  `updated` bigint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

INSERT INTO `test_save_fields_default` (`id`, `name`, `created`, `updated`) VALUES
(1, 'test', 0, 1292591233);

CREATE TABLE IF NOT EXISTS `test_where` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `where_name` varchar(20) NOT NULL,
  `where_age` int(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

INSERT INTO `test_where` (`id`, `where_name`, `where_age`) VALUES
(1, 'name_1', 12),
(2, 'name_2', 34),
(3, 'name_3', 21),
(4, 'name_4', 7),
(5, 'name_5', 34);

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `first_name` varchar(30) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=40 ;

INSERT INTO `users` (`id`, `username`, `password`, `first_name`, `last_name`) VALUES
(1, 'rcrowe', 'test', 'Rob', 'Crowe');
