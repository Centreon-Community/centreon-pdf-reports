
DELETE FROM `options` WHERE `key` LIKE 'pdfreports_%';

DELETE FROM `topology` WHERE `topology_page` = '5010150';
DELETE FROM `topology` WHERE `topology_page` = '64001';
DELETE FROM `topology` WHERE `topology_page` = '640';


DROP TABLE IF EXISTS `pdfreports_reports_contactgroup_relation`;
DROP TABLE IF EXISTS `pdfreports_reports_contact_relation`;
DROP TABLE IF EXISTS `pdfreports_reports_servicegroup_relation`;
DROP TABLE IF EXISTS `pdfreports_host_report_relation`;

DROP TABLE IF EXISTS `pdfreports_reports`;