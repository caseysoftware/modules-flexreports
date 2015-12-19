CREATE TABLE IF NOT EXISTS `flexreport_filters` (
  `report_filter_report` int(10) default NULL,
  `report_filter_mode` tinyint(3) default NULL,
  `report_filter_table` varchar(25) default NULL,
  `report_filter_column` varchar(50) default NULL,
  `report_filter_name` varchar(50) default NULL,
  `report_filter_operator` tinyint(3) default NULL,
  `report_filter_value` varchar(255) default NULL,
  `report_filter_label` varchar(255) default NULL
) TYPE=MyISAM;



INSERT INTO `flexreport_filters` VALUES (2, 0, 'tasks', 'task_percent_complete', 'Progress', 1, '100', '');
INSERT INTO `flexreport_filters` VALUES (2, 1, 'tasks', 'task_end_date', 'End Date', 4, '', 'Completion period');
INSERT INTO `flexreport_filters` VALUES (3, 0, 'tasks', 'task_percent_complete', 'Progress', 5, '100', '');
INSERT INTO `flexreport_filters` VALUES (3, 0, 'tasks', 'task_end_date', 'End Date', 5, 'ND', '');



CREATE TABLE IF NOT EXISTS `flexreport_fields` (
  `report_field_report` int(10) default NULL,
  `report_field_table` varchar(25) default NULL,
  `report_field_column` varchar(50) default NULL,
  `report_field_name` varchar(50) default NULL,
  `report_field_rank` tinyint(3) default NULL
) TYPE=MyISAM;


INSERT INTO `flexreport_fields` VALUES (1, 'tasks', 'task_end_date', 'End Date', 4);
INSERT INTO `flexreport_fields` VALUES (1, 'tasks', 'task_start_date', 'Start Date', 3);
INSERT INTO `flexreport_fields` VALUES (1, 'tasks', 'task_id', 'Assigned Users', 2);
INSERT INTO `flexreport_fields` VALUES (1, 'tasks', 'task_description', 'Description', 1);
INSERT INTO `flexreport_fields` VALUES (1, 'tasks', 'task_name', 'Task Name', 0);
INSERT INTO `flexreport_fields` VALUES (1, 'tasks', 'task_percent_complete', 'Progress', 5);
INSERT INTO `flexreport_fields` VALUES (2, 'tasks', 'task_id', 'Assigned Users', 2);
INSERT INTO `flexreport_fields` VALUES (2, 'tasks', 'task_owner', 'Owner', 1);
INSERT INTO `flexreport_fields` VALUES (2, 'tasks', 'task_name', 'Task Name', 0);
INSERT INTO `flexreport_fields` VALUES (2, 'tasks', 'task_end_date', 'End Date', 3);
INSERT INTO `flexreport_fields` VALUES (3, 'tasks', 'task_id', 'Assigned Users', 2);
INSERT INTO `flexreport_fields` VALUES (3, 'tasks', 'task_owner', 'Owner', 1);
INSERT INTO `flexreport_fields` VALUES (3, 'tasks', 'task_name', 'Task Name', 0);
INSERT INTO `flexreport_fields` VALUES (3, 'tasks', 'task_end_date', 'End Date', 3);
INSERT INTO `flexreport_fields` VALUES (4, 'task_log', 'task_log_creator', 'Creator', 0);
INSERT INTO `flexreport_fields` VALUES (4, 'task_log', 'task_log_name', 'Log name', 1);
INSERT INTO `flexreport_fields` VALUES (4, 'task_log', 'task_log_description', 'Description', 2);
INSERT INTO `flexreport_fields` VALUES (4, 'task_log', 'task_log_date', 'Date', 3);
INSERT INTO `flexreport_fields` VALUES (4, 'task_log', 'task_log_hours', 'Hours', 4);
INSERT INTO `flexreport_fields` VALUES (4, 'task_log', 'task_log_costcode', 'Cost Code', 5);
INSERT INTO `flexreport_fields` VALUES (5, 'users', '', 'Assigned task', 0);
INSERT INTO `flexreport_fields` VALUES (5, 'users', '', 'Full name', 1);
INSERT INTO `flexreport_fields` VALUES (5, 'tasks', 'task_start_date', 'Start Date', 2);
INSERT INTO `flexreport_fields` VALUES (5, 'tasks', 'task_end_date', 'End Date', 3);



CREATE TABLE IF NOT EXISTS `flexreports` (
  `report_id` int(10) unsigned NOT NULL auto_increment,
  `report_name` varchar(50) default NULL,
  `report_creator` int(10) default NULL,
  `report_title` varchar(50) default NULL,
  `report_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `report_description` text,
  `report_type` tinyint(2) default '0',
  `report_reference` tinyint(2) default NULL,
  `report_datefilter` varchar(50) default NULL,
  `report_format` varchar(10) default NULL,
  `report_layout` tinyint(2) default '0',
  `report_orientation` tinyint(2) default '0',
  `report_sortfields` varchar(255) default NULL,
  `report_showoptions` varchar(10) default NULL,
  `report_code` varchar(50) default NULL,
  `report_user_time` varchar(10) default NULL,
  PRIMARY KEY  (`report_id`)
) TYPE=MyISAM AUTO_INCREMENT=6 ;



INSERT INTO `flexreports` VALUES (1, 'Tasklist report', 1, 'Task List Report', '2007-09-04 19:50:58', 'List all tasks starting during a user selected period of time', 0, 0, 'tasks:task_start_date', '1,2', 0, 0, 'projects:Name,DESC', '10011000', '', '');
INSERT INTO `flexreports` VALUES (2, 'Completed', 1, 'Completed Tasks', '2007-09-04 19:56:19', 'List all tasks that have been completed during the period of time selected by the user', 0, 0, '', '1', 0, 0, 'projects:Name,DESC+tasks:End Date', '10000000', '', '');
INSERT INTO `flexreports` VALUES (3, 'Overdue', 1, 'Overdue Tasks', '2007-09-04 19:59:57', 'Report all overdue tasks', 0, 0, '', '1,2', 0, 1, 'projects:Name,DESC', '10000000', '', '');
INSERT INTO `flexreports` VALUES (4, 'TaskLog', 1, 'Task Log Report', '2007-09-04 20:02:25', 'Report all task logs during a user selected period of time', 0, 0, 'task_log:task_log_date', '1', 0, 0, 'projects:Name,DESC', '10000000', NULL, NULL);
INSERT INTO `flexreports` VALUES (5, 'AllocatedUserHours', 1, 'Allocated User Hours', '2007-09-04 23:24:50', 'Report on allocated hours to users by task during a user defined period of time', 0, 5, 'tasks:task_start_date', '1', 0, 0, 'projects:Name+tasks:Task Name', '11000000', NULL, '1,PM,1');