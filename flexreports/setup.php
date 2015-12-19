<?php
if (!defined('W2P_BASE_DIR')){
    die('You should not access this file directly.');
}

/*
* MODULE CONFIGURATION DEFINITION
* 
* module based upon Reports module created by Aramis for dotproject
*
*/ 


$config = array();
$config['mod_name'] = 'Flexreports';
$config['mod_version'] = '0.2';
$config['mod_directory'] = 'flexreports';
$config['mod_setup_class'] = 'CSetupFlexReports';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'FlexReports';
$config['mod_ui_icon'] = '';
$config['mod_description'] = 'A module for dynamic reports';

if (@$a == 'setup') {
    echo w2PshowModuleConfig( $config );
}

class CSetupFlexReports extends w2p_System_Setup
{
    public function install() {

        $q = $this->_getQuery();
        $q->createTable('flexreports');
        /* Reports table describe
        - the name, title and description of the report
        - the creator ID and creation date
        - report type = type of access to report
        * 0 = public
        * 1 = restricted according to rights granted through report_access table records
        * 2 = restricted to owner
        * 3 = restricted to admin
        */
        $sql = '(
            `report_id`                 int(10)         unsigned NOT NULL auto_increment,
            `report_name`                 varchar(50)     default NULL,
            `report_creator`             int(10)         default NULL,
            `report_title`                 varchar(50)     default NULL,
            `report_date`                 datetime         NOT NULL default \'0000-00-00 00:00:00\',
            `report_description`         text            default NULL,
            `report_type`                 tinyint(2)        default 0,
            `report_reference`             tinyint(2)        default NULL,
            `report_datefilter`             varchar(50)        default NULL,
            `report_format`                 varchar(10)        default NULL,
            `report_layout`                 tinyint(2)        default 0,
            `report_orientation`         tinyint(2)        default 0,
            `report_sortfields`         varchar(255)    default NULL,
            `report_showoptions`         varchar(10)        default NULL,
            `report_code`                 varchar(50)     default NULL,
            `report_user_time`            varchar(10)     default NULL,
            PRIMARY KEY  (`report_id`))
            ENGINE = MYISAM DEFAULT CHARSET=utf8 ';
        $q->createDefinition($sql);
        $q->exec();

        $q->clear();
        $q->createTable('flexreport_fields');
        /* Report_fields table describes
        - report ID
        - field table, column, name and display rank
        */
        $sql = '(
            `report_field_report`        int(10)         default NULL,
            `report_field_table`         varchar(25)     default NULL,
            `report_field_column`         varchar(50)     default NULL,
            `report_field_name`         varchar(50)     default NULL,
            `report_field_rank`            tinyint(3)         default NULL
            )
            ENGINE = MYISAM DEFAULT CHARSET=utf8 ';
        $q->createDefinition($sql);
        $q->exec();

        $q->clear();
        $q->createTable('flexreport_filters');
        /* Report_filters table describes
        - report filter name
        - filter mode, type and definition
        */
        $sql = '(
            `report_filter_report`        int(10)         default NULL,
            `report_filter_mode`        tinyint(3)         default NULL,
            `report_filter_table`         varchar(25)     default NULL,
            `report_filter_column`         varchar(50)     default NULL,
            `report_filter_name`         varchar(50)     default NULL,
            `report_filter_operator`    tinyint(3)         default NULL,
            `report_filter_value`        varchar(255)    default NULL,
            `report_filter_label`        varchar(255)     default NULL
            )
            ENGINE = MYISAM DEFAULT CHARSET=utf8 ';
        $q->createDefinition($sql);
        $q->exec();

        /* Report_access table describe
        - report ID
        - access type
        * 0 =
        * 1 = restricted to report_access_value company ID list
        * 2 = restricted to report_access_value project status list
        * 3 = restricted to report_access_value user ID list
        */
        $q->clear();
        $q->createTable('flexreport_access');
        $sql = '(
            `report_access_report`         int(10)         default NULL,
            `report_access_type`         tinyint(2)         default NULL,
            `report_access_id`             int(10)            default NULL
            )
            ENGINE = MYISAM DEFAULT CHARSET=utf8 ';
        $q->createDefinition($sql);
        $q->exec();

        return parent::install();
    }

    public function remove() {
        $q = $this->_getQuery();
        $q->dropTable('flexreport_access');
        $q->exec();
        $q->clear();
        $q->dropTable('flexreport_fields');
        $q->exec();
        $q->clear();
        $q->dropTable('flexreport_filters');
        $q->exec();
        $q->clear();
        $q->dropTable('flexreports');
        $q->exec();

        return parent::remove();
    }
}