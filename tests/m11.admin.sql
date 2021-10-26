use test;

DROP TABLE IF EXISTS `admin`;

CREATE TABLE `admin` (
  `admin_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) DEFAULT NULL,
  `pass` varchar(32) DEFAULT '',
  `real_name` varchar(32) DEFAULT NULL,
  `pid` int(10) unsigned DEFAULT NULL COMMENT 'editor的operator',
  `role` enum('admin','operator','editor') DEFAULT NULL,
  `status` enum('normal','block') DEFAULT 'normal',
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

/*Data for the table `admin` */

insert  into `admin`(`admin_id`,`name`,`pass`,`real_name`,`pid`,`role`,`status`,`date`) values (2,'dkd','df3d9bbdad22d9d9ba41d7e60ffff32e','张顺口',0,'operator','normal','2018-07-19 13:40:32');
insert  into `admin`(`admin_id`,`name`,`pass`,`real_name`,`pid`,`role`,`status`,`date`) values (7,'zskss','df3d9bbdad22d9d9ba41d7e60ffff32e','张顺口',2,'editor','normal','2018-07-20 13:17:18');
insert  into `admin`(`admin_id`,`name`,`pass`,`real_name`,`pid`,`role`,`status`,`date`) values (8,'dds','df3d9bbdad22d9d9ba41d7e60ffff32e','张顺口',2,'editor','normal','2018-07-20 13:23:08');


create table `muti_pk_test` (
	`x` int (11),
	`y` varchar (24),
	`data` varchar (24)
);
insert into `muti_pk_test` (`x`, `y`, `data`) values('1','aa','qw');
insert into `muti_pk_test` (`x`, `y`, `data`) values('2','aa','ccc');
insert into `muti_pk_test` (`x`, `y`, `data`) values('2','cc','eeeeeee');


