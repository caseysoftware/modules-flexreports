<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

global $AppUI, $baseDir;


$report = new CFlexReport();
$del = w2PgetParam( $_POST, 'del', 0 );
/*
*	Delete report
*/
if ( $del ) {
	$report_id = w2PgetParam( $_POST, 'report_id', 0 );
	if ( !$report_id || !$report->load( $report_id ) ) {
		$AppUI->setMsg( 'Report' );
		$AppUI->setMsg( 'invalidID', UI_MSG_ERROR, true );
		$AppUI->redirect();
	}
	$msg = $report->delete();
	if ( $msg ) {
		$AppUI->setMsg('Delete failed', UI_MSG_ERROR, true );
		$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
		$AppUI->redirect();
	}
	$AppUI->setMsg('Report', UI_MSG_ALERT );
	$AppUI->setMsg('deleted', UI_MSG_ALERT, true );
	$AppUI->redirect();
}

require_once $baseDir.'/modules/flexreports/report_functions.php';

// List of selected fields
$selected_field1 = w2PgetParam( $_POST, 'selected_field1', '');
$selected_field2 = w2PgetParam( $_POST, 'selected_field2', '');

// Retrieve filter list
$filter_list = w2PgetParam( $_POST, 'filter_list', '');
/*
*	Create or update report record
*/

$msg = $report->report_id ? "updated" : "added" ;

if ( $msg = $report->store() ) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}
$AppUI->setMsg( 'Report', UI_MSG_ALERT, true );
$AppUI->setMsg( $msg, UI_MSG_ALERT, true );
/*
*	Set access restricted records
*/

$q = new w2p_Database_Query();
// List of allowed company/project/user if access is  restricted
if ( $report->report_type == 1 ) {
	$q->setDelete('flexreport_access');
	$q->addWhere('report_access_report = '. $report->report_id );
	$q->exec();
	$q->clear();
	$allowedId_list[COMPANY_RESTRICTED] = ( $list = w2PgetParam( $_POST, 'company_list', '') ) ? explode( ',', $list ) : array();
	$allowedId_list[PROJECT_RESTRICTED] = ( $list = w2PgetParam( $_POST, 'project_list', '') ) ? explode( ',', $list ) : array();
	$allowedId_list[USER_RESTRICTED] = ( $list = w2PgetParam( $_POST, 'user_list', '') ) ? explode( ',', $list ) : array();
	for ( $i=0 ; $i<count($allowedId_list) ; $i++ ) {
		if ( count($allowedId_list[$i]) ) {
			foreach ( $allowedId_list[$i] as $aId ) {
                $report->addReportAccess( $i, $aId );
            }
        }
	}
}
/*
*	Create report_field records
*/

$q->setDelete('flexreport_fields');
$q->addWhere('report_field_report = ' . $report->report_id );
$q->exec();
$q->clear();
if ( $selected_field1 ) {
	$fields = explode( ',', $selected_field1 );
	for ( $i=0 ; $i<count( $fields ) ; $i++ ) {
		$report->addField ( $fields[$i], $i );
    }
}
if ( $selected_field2 && $report->report_layout > 0 ) {
	$fields = explode( ',', $selected_field2 );
	for ( $i=0 ; $i<count( $fields ) ; $i++ ) {
		$rank = 100 + $i ;
		$report->addField ( $fields[$i], $rank );
    }
}
/*
*	Create filters records
*/
// Delete existing filters records

$q->setDelete('flexreport_filters');
$q->addWhere('report_filter_report = ' . $report->report_id );
$q->exec();
// Process new filters
if ( $filter_list ) {
	$filters = explode ( '&&', $filter_list );
	foreach ( $filters as $f ) {
		$filter = explode ( '|', $f );
		$report->addReportFilter( $filter );
    }
}
$AppUI->redirect();