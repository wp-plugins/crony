DROP TABLE IF EXISTS `wp_crony_jobs`;
CREATE TABLE `wp_crony_jobs` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `disabled` int(1) NOT NULL,
  `start` datetime NOT NULL,
  `schedule` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `last_run` datetime NOT NULL,
  `next_run` datetime NOT NULL,
  `script` varchar(255) NOT NULL,
  `function` varchar(255) NOT NULL,
  `phpcode` longtext NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;