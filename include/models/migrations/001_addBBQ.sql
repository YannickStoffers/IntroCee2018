--
-- Table structure for table `registrations`
--

DROP TABLE IF EXISTS `barbecue`;
CREATE TABLE `barbecue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('First-year','Senior') NOT NULL,
  `first_name` varchar(255),
  `surname` varchar(255),
  `birthday` date,
  `address` varchar(255),
  `city` varchar(255),
  `email` varchar(254), -- official max length of an email-address
  `iban` varchar(34), -- official max length of an iban
  `bic` varchar(11), -- official max length of a bic
  `study` enum('Artificial Intelligence','Computing Science','Other') NOT NULL,
  `vegetarian` tinyint(1) NOT NULL DEFAULT '0',
  `status` enum('registered','cancelled') NOT NULL DEFAULT 'registered',
  PRIMARY KEY (`id`),
  UNIQUE (`email`)
) ENGINE = INNODB;
