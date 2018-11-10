-- [database log]

CREATE TABLE `dayevent` (
`id` 			bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
`date` 			date DEFAULT NULL,
`countcalc` 	int(10) UNSIGNED NULL DEFAULT NULL,
`dbtrafic` 		int(10) UNSIGNED NULL DEFAULT NULL,
`dbsize` 		int(10) UNSIGNED NULL DEFAULT NULL,
`user` 			int(10) UNSIGNED NULL DEFAULT NULL,
`activeuser` 	int(10) UNSIGNED NULL DEFAULT NULL,
`deactiveuser` 	int(10) UNSIGNED NULL DEFAULT NULL,
`log` 			int(10) UNSIGNED NULL DEFAULT NULL,
`visitor` 		int(10) UNSIGNED NULL DEFAULT NULL,
`agent` 		int(10) UNSIGNED NULL DEFAULT NULL,
`session` 		int(10) UNSIGNED NULL DEFAULT NULL,
`urls` 			int(10) UNSIGNED NULL DEFAULT NULL,
`ticket` 		int(10) UNSIGNED NULL DEFAULT NULL,
`comment` 		int(10) UNSIGNED NULL DEFAULT NULL,
`address` 		int(10) UNSIGNED NULL DEFAULT NULL,
`news` 			int(10) UNSIGNED NULL DEFAULT NULL,
`page` 			int(10) UNSIGNED NULL DEFAULT NULL,
`post` 			int(10) UNSIGNED NULL DEFAULT NULL,
`transaction` 	int(10) UNSIGNED NULL DEFAULT NULL,
`term` 			int(10) UNSIGNED NULL DEFAULT NULL,
`termusages` 	int(10) UNSIGNED NULL DEFAULT NULL,
`datecreated` 	timestamp NULL DEFAULT CURRENT_TIMESTAMP,
`datemodified` 	timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


