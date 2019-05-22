DROP TABLE IF EXISTS `#__brands`;

CREATE TABLE `#__brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `primary_colour` varchar(7) NOT NULL DEFAULT '#000000',
  `secondary_colour` varchar(7) NOT NULL DEFAULT '#000000',
  `tertiary` varchar(7) NOT NULL DEFAULT '#000000',
  `logo_svg` mediumtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `params` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(10) NOT NULL DEFAULT '0',
  `checked_out` int(10) unsigned NOT NULL DEFAULT '0',
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `access` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) 
    ENGINE          = MyISAM
    AUTO_INCREMENT  = 0
    DEFAULT CHARSET = utf8;
