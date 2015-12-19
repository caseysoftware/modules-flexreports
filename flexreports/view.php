<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

/*
* 	Retrieve global parameters
*/
global $field_desc, $user_function_list, $indirection_table, $join_list;
global $AppUI, $field_list, $show_days ;

require_once W2P_BASE_DIR . '/modules/flexreports/report_functions.php';
$perms =& $AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');
 
/*
*	Retrieve report parameters
*/
$report_id = w2PgetParam( $_REQUEST, 'report_id', 0 );
$report = new CFlexReport();
if ( ! $report_id || ! $report->load( $report_id )) {
	$AppUI->setMsg('Report', UI_MSG_ERROR ) ;
	$AppUI->setMsg('InvalidID', UI_MSG_ERROR, true );
	$AppUI->redirect() ;
}
// Add users fields to field_desc if report based on users
if ( $report->report_reference ) {
	$field_desc['users']['field_list'] = $user_field_list ;
}
$project_id = w2PgetParam( $_REQUEST, 'project_id', 0 );

/*
*	Retrieve list of allowed projects for this report
*/
$target_projects = $report->getTargetProjects( $AppUI->user_id, 'projects.project_id, project_name' );

if ( count($target_projects) == 0 ) {
	$AppUI->setMsg('NoProjectForDisplay', UI_MSG_ERROR );
	$AppUI->redirect();
}
// Build project select array
$select_project_list = array( 0  => $AppUI->_('All projects')) + $target_projects ;
// Build report project list
$report_project_list = array_keys( $target_projects );

/*
*	Set Title block
*/
$titleBlock = new w2p_Theme_TitleBlock( 'View report', 'colored_folder.png', $m, "$m.$a" );
$titleBlock->addCrumb( '?m=flexreports', 'report list' );
if ( $perms->checkModule( 'flexreports', 'edit') && ( $report->report_creator == $AppUI->user_id || $perms->checkModule( 'admin', 'edit' ) ) ) {
	$titleBlock->addCrumb('?m=flexreports&a=addedit&report_id='. $report->report_id, 'edit this report');
}
if ( $project_id ) {
	$titleBlock->addCrumb('?m=flexprojects&a=view&project_id='.$project_id, 'view this project');
}
$titleBlock->show();

// Special code
if ( $report->report_code ) {
	include_once W2P_BASE_DIR . '/modules/flexreports/' . $report->report_code;
	exit ;
}

// Build select format array
$select_format = array();
if ( $report->report_format ) {
	$report_format = explode( ',', $report->report_format);
	$select_format[0] = $report_format_list[0];
	for ( $i = 0 ; $i < count($report_format) ; $i++ ) {
		$select_format[$report_format[$i]]= $report_format_list[$report_format[$i]];
    }
}
// Retrieve fields definition
$selected_fields = $report->getReportField();
/*
*	Retrieve options and set option flags
*/

$show_options = preg_split( "//", $report->report_showoptions, -1, PREG_SPLIT_NO_EMPTY );
$sort_field = explode('+', $report->report_sortfields);
$show_subtitle = $show_options[0] && $sort_field[0] ;
$show_level2 = $show_options[1] && $sort_field[1] ;
$show_days = $show_options[2];
$show_parent = $show_options[3];
$show_incomplete = $show_options[4];
$show_Gantt = $show_options[5];
$show_period = $report->report_user_time ? 1 : 0 ;
/*
*	Retrieve report parameters
*/

//  Dynamic parameters
$do_report = w2PgetParam( $_POST, 'do_report', 0 );
$output_format = w2PgetParam( $_POST, 'output_format', 0 );
$display_all = w2PgetParam( $_POST, 'display_all', 0 );

/*
*	Conditions and filters 
*/
$selected_filter = $report->getReportFilter();

/*
* 	Retrieve and define start_date and end_date if date filter is used
*	- if predefined period => generate start and end dates from selected period
* 	- else use user_defined start and end dates
*/
if ( $report->report_datefilter ) {
	$list_start_date = w2PgetParam( $_POST, "list_start_date", 0 );
	$list_end_date = w2PgetParam( $_POST, "list_end_date", 0 );
	$days = w2PgetParam( $_POST, 'days', 30 );
	$period = intval( w2PgetParam($_POST, "period", 0) );
	$period_value = w2PgetParam($_POST, "pvalue", 1);

	$today = new CDate();
	if ($period) {
//	if period is set dates are defined as an offset relative to today
		$ts = $today->format(FMT_TIMESTAMP_DATE);
		$days = $period  * $period_value;
		$start_date = new CDate($ts);
		$end_date = new CDate($ts);
		if ( $period > 0 ) {
			$end_date->addDays( $days ) ; 
        } else {
			$start_date->addDays( $days ) ;
        }
		$do_report = 1 ;
    } else {
// create Date objects from the datetime fields
// if $list_start_date not defined then set NULL to query all entries since project start date
// Set end_date to today at initialisation
		$start_date = intval( $list_start_date ) ? new CDate( $list_start_date ) : NULL ;
		$end_date = intval( $list_end_date ) ? new CDate( $list_end_date ) : ( $do_report ? NULL : $today ) ;
    }
//	Set time
	if ( $end_date ) $end_date->setTime( 23, 59, 59 );
	if ( $start_date ) $start_date->setTime( 0, 0, 0 ) ;
} else {
	// Default value = 'all' if no datefilter
	$display_all = 1 ;
}
/*
* 	Display input form
*/

if ( $report->report_datefilter || $report->report_user_time )
// Include Javascript for date input
	{
?>
<script language="javascript">

var calendarField = '';

function popCalendar( field ){

	calendarField = field;
	idate = eval( 'document.editFrm.list_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */

function setCalendar( idate, fdate ) {

	fld_date = eval( 'document.editFrm.list_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

	var tabPeriod = new Array("-30", "-7", "-1", "+1", "+7", "+30" )
function setPeriod( index ) {
	document.editFrm.period.value = tabPeriod[index] ;
	document.editFrm.submit() ;
}
</script>
<?php
	}
// Start displaying report infos and options
?>
<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<form name="editFrm" action="index.php?m=flexreports&a=view" method="post">
<input type="hidden" name="report_id" value="<?php echo $report_id;?>" />
<input type="hidden" name="period" value="" />
<tr>
	<td colspan="3"><strong><?php echo $report->report_title; ?></strong></td>
	<td colspan="3" align="right">
		<?php echo $AppUI->_('Selected project'). ":" ; 
			  echo arraySelect( $select_project_list, 'project_id', 'class="text" size="1"', $project_id, false);
		?>
	</td>
</tr>
<?php if ( trim($report->report_datefilter) )
	{
	$date_label = $report_datefilter_list[$report->report_datefilter][1];
?>
<tr>
	<td colspan="6"><hr width="100%" size="1"></tr>
<tr>
<tr>
	<td align="right" nowrap="nowrap" width="10%"><b><?php echo $AppUI->_('Date Range'); ?>&nbsp;:</b>&nbsp;</td>
	<td colspan="5">
		<input type="checkbox" name="display_all" <?php if ($display_all) echo "checked" ?> />
		<?php echo $AppUI->_( 'All entries' );?></td>
	</td>
</tr>
<tr>
	<td align="right" nowrap="nowrap" width="10%"><?php echo $AppUI->_($date_label) . " " . $AppUI->_('from');?>&nbsp;:&nbsp;</td>
	<td nowrap="nowrap">
		<input type="hidden" name="list_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="start_date" value="<?php echo $start_date ? $start_date->format( $df ) : '' ;?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('start_date')">
			<img src="./modules/flexreports/images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to');?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="list_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" />
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./modules/flexreports/images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td nowrap="nowrap">&nbsp;</td>
</tr>
<tr>
        <td align="right" valign="top"><?php echo $AppUI->_('Preset Periods'); ?>:&nbsp;</td>
		<td align="right" valign="top"><?php echo $AppUI->_('Number of periods') ; ?>:&nbsp;</td>
		<td valign="top"><input class="text" type="field" size="2" name="pvalue" value="1" /></td>
        <td nowrap="nowrap">
			<table cellpadding="2" cellspacing="0" border="0" class="tbl">
			<tr>
				<td><input class="button" type="button" value="<?php echo $AppUI->_('Previous Month'); ?>" onClick="setPeriod( 0 )" /></td>
				<td><input class="button" type="button" value="<?php echo $AppUI->_('Previous Week'); ?>"  onClick="setPeriod( 1 )"/></td>
				<td><input class="button" type="button" value="<?php echo $AppUI->_('Previous Day'); ?>"  onClick="setPeriod( 2 )"/></td>
        	</tr>
			<tr>
				<td><input class="button" type="button" value="<?php echo $AppUI->_('Next Day'); ?>"  onClick="setPeriod( 3 )"/></td>
				<td><input class="button" type="button" value="<?php echo $AppUI->_('Next Week'); ?>"  onClick="setPeriod( 4 )"/></td>
				<td><input class="button" type="button" value="<?php echo $AppUI->_('Next Month'); ?>"  onClick="setPeriod( 5 )"/></td>
			</tr>
			</table>
        </td>
        <td width="50%" colspan="1">&nbsp;</td>
</tr>
<?php
	}
if ( count($selected_filter))
	{
/*
*	Build filter selection input
*/

	$header = 0 ;
	foreach ( $selected_filter as $filter )
		{
		/*
		*  The type of input depends on the filter definition
		*  	- If both value and label are set : use a checbox named 'label'
		* 	- else it depends on the type of the filter field :
		* 		* 0 => not a filter hence break;
		* 		* 1 => numeric input field
		* 		* 2 => text input field
		* 		* 3 => flag
		* 		* 4 => System lookup value
		* 		* 5 => date using predefined periods
		* 		* 6 => object ID use select with object name
		* 		* 7 => object found through an indiraction table
		*/
		// Let's skip the conditions
		if ( $filter['report_filter_mode'] == 0 )
			continue ;
		if ( ! $header )
			{
			?>
<tr>
	<td colspan="7"><hr width="100%" size="1"></tr>
<tr>
<tr>
	<td align="right" nowrap="nowrap" width="10%"><strong><?php echo $AppUI->_('Filter'); ?>&nbsp;:</strong>&nbsp;</td>
	<td>
			<?php
			}
		$header++;
		echo "<tr>\n" ;
		$input_name = $filter['report_filter_table']. '_' . str_replace( ' ', '', $filter['report_filter_name']) . '_' . str_replace( ' ', '', $filter['report_filter_label']) ;
		$input_value = w2PgetParam( $_POST, $input_name, '');
		$row = $field_desc[$filter['report_filter_table']]['field_list'][$filter['report_filter_name']];
		$filter_case = ( $filter['report_filter_operator'] >= 12 || strlen(rtrim($filter['report_filter_value']))) ? 3 : $row[4] ;
		$label = $filter['report_filter_label'] ? $filter['report_filter_label'] : $filter['report_filter_name'] ;
		echo "<td align=\"right\">" . $label . ':&nbsp;' . "</td>";
		switch ( $filter_case )
			{
			case 0 : break;
			case 1 :
			case 2 :
					?>
					<td><input type="text" class="text" name="<?php echo $input_name ; ?>" value="<?php echo $input_value ; ?>" /></td>
					<?php
					break;
			case 3 : // Use checkbox named label
					$checked = $input_value ? 'checked' : '' ;
					?>
					<td><input type="checkbox" name="<?php echo $input_name ; ?>" <?php echo $checked ; ?> /></td>
					<?php
					break;
			case 4 : // use select
					$sysval_ref = $row[1];
					if ( ! preg_match( "/\{([A-Za-z]+)\}/", $sysval_ref, $matches ) )
						{
						echo "<td>". "Invalid SysVal name " . $sysval_ref . "</td>\n" ;
						}
					else
						{
						$sysval_array = w2PgetSysVal( $matches[1] );
						echo "<td>";
						echo arraySelect( $sysval_array, $input_name, 'size="1" class="text"', $input_value, true );
						echo "</td>\n";
						}
					break;
			case 5 : // Use select of predefined periods
					$input_date = w2PgetParam( $_POST, 'list_'.$input_name, '');
					echo "<td>";
					echo arraySelect( $date_filter_list, $input_name, 'size="1" class="text"', $input_value, true );
					echo "</td>\n";
					break;
			case 6 : // Create select
					$select_list = array ( 0 => $AppUI->_('All') ) + getRecordNames( $row[5], $row[0] );
					echo "<td>";
					echo arraySelect( $select_list, $input_name, 'size="1" class="text"', $input_value, false );
					echo "</td>\n";
					break;
			case 7 : // same as case 6 but need to extract the table name from $row[5]
					$select_list =  array ( 0 => $AppUI->_('All') ) + getRecordNames( $row[5] );
					echo "<td>";
					echo arraySelect( $select_list, $input_name, 'size="1" class="text"', $input_value, false );
					echo "</td>\n";
					break;			
			}
		echo "<td colspan=\"4\">&nbsp;</td>\n";
		echo "</tr>\n" ;
		}
	}
/*
*	Build user time period start and end date input, if required
*/

$show_time = array();
if ( $show_period )
	{
	$show_time = explode ( ',', $report->report_user_time );
	if ( ! rtrim($show_time[1]) )
		{
		?>
		<tr>
			<td colspan="7"><hr width="100%" size="1"></tr>
		<tr>
		<?php
		$period_start_date = w2PgetParam( $_POST, 'list_period_start_date', '');
		$period_end_date = w2PgetParam( $_POST, 'list_period_end_date', '');
		$end_date = $period_end_date ? new CDate($period_end_date) : new CDate();
		if ( $period_start_date )
			{
			$start_date = new CDate($period_start_date) ;
			}
		else
			{
			$start_date = $end_date ;
			$start_date->addDays(-7) ;
			}
		$hideNonWorkingDays = w2PgetParam( $_POST, 'hide_NWD', 0 );
		?>
		<td align="right" nowrap="nowrap" width="10%"><strong><?php echo $AppUI->_('Select user time period'); ?>:&nbsp;</strong></td>
		<td nowrap="nowrap">
			<?php $AppUI->_('from') ; ?>:&nbsp;
			<input type="hidden" name="list_period_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE ); ?>" />
			<input type="text" name="period_start_date" value="<?php echo $start_date->format( $df ) ;?>" class="text" disabled="disabled" />
			<a href="#" onClick="popCalendar('period_start_date')">
				<img src="./modules/flexreports/images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
			</a>
		</td>
		<td nowrap="nowrap">
			<?php echo $AppUI->_('to');?>:&nbsp;
			<input type="hidden" name="list_period_end_date" value="<?php echo $end_date->format( FMT_TIMESTAMP_DATE ); ?>" />
			<input type="text" name="period_end_date" value="<?php echo $end_date->format( $df ); ?>" class="text" disabled="disabled" />
			<a href="#" onClick="popCalendar('period_end_date')">
				<img src="./modules/flexreports/images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
			</a>
		</td>
		<td nowrap="nowrap">
			<?php echo $AppUI->_("Hide non working days"); ?>&nbsp;:
			<input type="checkbox" name="hide_NWD" <?php echo $hideNonWorkingDays ? "checked" : "" ; ?> />
		</td>
		</tr>
		<?php
		}
	}
	?>
<tr>
	<td colspan="7"><hr width="100%" size="1"></tr>
<tr>
<tr>
<?php
	if ( count($select_format) )
		{
		?>
		<td align="right" nowrap="nowrap" width="10%"><strong><?php echo $AppUI->_( 'Select file format' );?>&nbsp;:</strong>&nbsp;</td>
		<td nowrap="nowrap"><?php echo arraySelect( $select_format, 'output_format', 'size="1" class="text"', $output_format, true ); ?>
		<?php
		}
	else
		{
		?>
		<td colspan="2"><strong><?php echo $AppUI->_("Only screen display") ; ?></strong></td>
		<?php
		}
	?>
	<td align="right" colspan="5" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>
</form>
<tr>
	<td colspan="7"><hr width="100%" size="1"></tr>
<tr>
</table>
<?php
/*
*	CREATE AND DISPLAY REPORT
*/
if ($do_report) {

/*
* 	Process $show_level2 flag : add field in first row of selected fields array
*/
	if ( $show_level2 )
		{
		$nc = strpos( $sort_field[1], ',');
		$sort = $nc ? substr( $sort_field[1], 0, $nc ) : $sort_field[1] ;
		$nc = strpos($sort, ':');
		$table = substr( $sort, 0, $nc );
		$name = substr($sort, $nc+1 );
		$row = $field_desc[$table]['field_list'][$name];
		$temp = array( array(
					'report_field_id'		=> 0,
					'report_field_table'	=> $table,
					'report_field_column'	=> $row[0],
					'report_field_name'		=> $name,
					'report_field_rank'		=> 0
						)
					);
		$selected_fields = $temp + $selected_fields ;
		// Set flag to the field name for use in display processing
		$show_level2 = $row[0] ;
		}
/*
* 	Create FROM clause
*/

	$q = new CFlexReport_Query( $project_id );
	if ( $report->report_reference ) {
		$field_desc['users']['field_list'] = $user_field_list ;
		$reference = $user_function_list[$report->report_reference][0];
		if ( $nc = strpos( $reference, '|') ) {
			$table = substr( $reference, 0, $nc );
			$nc++;
			$field = substr( $reference, $nc );
			$root_table = $q->addJoinClause( $table, $field, 'users' );
		} else {
			// Throuigh an indirection table (assignees)
			$root_table = $q->addJoinClause( $reference, '', 'users' );
			$table = $reference;
			}
		$q->addQueryField( $table, $user_function_list[$report->report_reference][2], 0, 1);
		$q->addQueryField( 'users', 'user_id', 0, 1);
	} else {
		$root_table = 'companies';
		}
	$q->addFromClause( $root_table );
/*
*  Create SELECT Field list and associated JOIN clauses
*/
	foreach ( $selected_fields as $field )
		$q->addQueryField( $field['report_field_table'], $field['report_field_name'], $show_incomplete ) ;
/*
*	Query first sort field when sort levels should be displayed
*/
	if ( $show_subtitle )
		{
		// Add field in SELECT list
		$nc = strpos( $sort_field[0], ',');
		$sort = $nc ? substr( $sort_field[0], 0, $nc ) : $sort_field[0] ;
		$nc = strpos($sort, ':');
		$show_table = substr( $sort, 0, $nc );
		$show_name = substr($sort, $nc+1 );
		$row = $field_desc[$show_table]['field_list'][$show_name];
		$q->addQueryField( $show_table, $show_name, $show_incomplete );
		// Set flag to the field name for use in display processing
		$show_subtitle = $row[0] ;
		}

/*
*	Create ORDER BY clausse
*/
	$sort_list="";
	foreach ( $sort_field as $sf )
		if ( $sf ) {
			$sort = explode( ',', $sf);
			$sort_list .= ( $sort_list ? "," : "" ) . field_SQLname($sort[0]) . " " . $sort[1] ;
			}
	if ( $sort_list )
		$q->addOrder($sort_list);

/*
*	if show task parent
* 		- Generate a query including task_id and task_parent
* 		- Retrieve all records (no WHERE clause) 
*/
	if ( $show_parent ) {
		$q->addQueryField( 'tasks', 'task_id', $show_incomplete, 1 );
		$q->addQueryField( 'tasks', 'task_parent', $show_incomplete, 1 );
		$all_task_sql = $q->prepare();
		$all_record_data = $q->loadList();
	}
	
/*
*	Include WHERE clauses according to filters
*/

	foreach ( $selected_filter as $filter ) {
		$input_name = $filter['report_filter_table']. '_' . str_replace( ' ', '', $filter['report_filter_name']) . '_' . str_replace( ' ', '', $filter['report_filter_label']) ;
		$input_value = w2PgetParam( $_POST, $input_name, 0 );
		// Determine if the filter where clause must be included
		$row = $field_desc[$filter['report_filter_table']]['field_list'][$filter['report_filter_name']];
		if ( $filter['report_filter_mode'] == 0 )
			{
			// the filter record is a condition to be met by all selected records
			$filter_ok = true ;
			$input_value = $filter['report_filter_value'];
			}
		else
			if ( strlen($filter['report_filter_value']) || $filter['report_filter_operator'] >= 12 )
				{
				// The filter is displayed as a checkbox
				if ( $input_value )
					{
					// if the checkbox is checked, the filter must apply
					$filter_ok = true ;
					$input_value = $filter['report_filter_value'];
					}
				else
					{
					// if the checkbox is not set, the filter does not apply
					$filter_ok = false ;
					}
				}
			else
				{
				// the filter is not a checkbox and must apply if a value is selected
				$filter_ok = $input_value ? true : false ;
				}
		if ( $filter_ok )
			$q->addWhereClause( $filter['report_filter_table'], $filter['report_filter_name'], $filter['report_filter_operator'], $input_value );
		}
/*
* 	Add WHERE clauses for date filters
*/
	if ( !$display_all ) {
		$nc = strpos( $report->report_datefilter, ':');
		$table = substr( $report->report_datefilter, 0, $nc );
		$nc++;
		$column = substr( $report->report_datefilter, $nc );
		if ( $start_date )
			$q->addWhere( $field_desc[$table]['join_key'] . ".$column >= '" . $start_date->format( FMT_DATETIME_MYSQL )."'");
		if ( $end_date )
			$q->addWhere( $field_desc[$table]['join_key'] . ".$column <= '" . $end_date->format( FMT_DATETIME_MYSQL )."'");
		}
/*
* 	Retrieve selected records
*/
	$query_list = $q->loadList();
	$tc = count( $query_list ) ;

/*	
* 	if Show parent task option is selected
* 	-	sort all tasks by task parent
* 	-	prepare array of all tasks to be displayed
*/
	if ( $show_parent )
		$query_list = sortByParentTask( $query_list, $all_record_data ) ;
/*
* 	Prepare user time per period if set
*/

	if ( $show_period ) {
		$user_time = $show_time[0];
		$period_time = $show_time[1];
		$hideNonWorkingDays = $show_time[2];
		if ( $period_time ) {
			$date = new CDate();
			switch ( $period_time ) {
				case 'PQ' :
					$period_end_date = Date_Calc::endOfPrevMonth( $date->day, $date->month, $date->year );
					$date->addMonths(-3);
					$period_start_date = Date_Calc::beginOfMonth( $date->month, $date->year );
					break;
				case 'PM' :
					$period_end_date = Date_Calc::endOfPrevMonth( $date->day, $date->month, $date->year );
					$period_start_date = Date_Calc::beginOfPrevMonth( $date->day, $date->month, $date->year );
					break;
				case 'PF' :
					$date->addDays(-7);
					$period_end_date = Date_Calc::endOfWeek( $date->day, $date->month, $date->year );
					$date->addDays(-7);
					$period_start_date = Date_Calc::beginOfWeek( $date->day, $date->month, $date->year );
					break;
				case 'PW' :
					$date->addDays(-7);
					$period_end_date = Date_Calc::endOfWeek( $date->day, $date->month, $date->year );
					$period_start_date = Date_Calc::beginOfWeek( $date->day, $date->month, $date->year );
					break;
				case 'PD' :
					$date->addDays(-1);
					$period_end_date = $date->format( FMT_TIMESTAMP_DATE );
					$period_start_date = $date->format( FMT_TIMESTAMP_DATE );
					break;
				case 'NOW' :
					$period_end_date = $date->format( FMT_TIMESTAMP_DATE );
					$period_start_date = $date->format( FMT_TIMESTAMP_DATE );
					break ;
				case 'ND' :
					$date->addDays(1);
					$period_end_date = $date->format( FMT_TIMESTAMP_DATE );
					$period_start_date = $date->format( FMT_TIMESTAMP_DATE );
					break;
				case 'NW' :
					$date->addDays(7);
					$period_end_date = Date_Calc::endOfWeek( $date->day, $date->month, $date->year );
					$period_start_date = Date_Calc::beginOfWeek( $date->day, $date->month, $date->year );
					break;
				case 'NF' :
					$date->addDays(7);
					$period_start_date = Date_Calc::beginOfWeek( $date->day, $date->month, $date->year );
					$date->addDays(7);
					$period_end_date = Date_Calc::endOfWeek( $date->day, $date->month, $date->year );
					break;
				case 'NM' :
					$period_end_date = Date_Calc::endOfNextMonth( $date->day, $date->month, $date->year );
					$date->addMonths(1);
					$period_start_date = Date_Calc::beginOfMonth( $date->month, $date->year );
					break;
				case ' NQ' :
					$date->addMonths(1);
					$period_start_date = Date_Calc::beginOfMonth( $date->month, $date->year );
					$date->addMonths(1);
					$period_end_date = Date_Calc::endOfNextMonth( $date->day, $date->month, $date->year );
					break;
				}
			}
		$query_list = putUserTimePerPeriod( $query_list, $user_time, $period_start_date, $period_end_date, $hideNonWorkingDays );
		}
/*
* 	Display queried records on screnn and generate pdf data
*/

	echo "<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" width= \"100%\" class=\"tbl\">\n" ;
/*
* 	Use appropriate PHP code depending on report layout
*/
	$pdfdata = array();
	$pdfcolumns = array();
	$csvcolumns = array();
	if ( $report->report_layout == 0 ) {
		require_once ( DP_BASE_DIR."/modules/flexreports/vw_columnar.php");
	} else {
		require_once ( DP_BASE_DIR."/modules/flexreports/vw_tabular.php");
		}
echo "</table>" ;
/*
* 	Switch depending of generated report format
*/
	switch ( $output_format ) {
//	No report
	case 0 : break;
//	Pdf Report
	case 1 :
		// Initialize document
		$font_dir = w2PgetConfig( 'root_dir' )."/lib/ezpdf/fonts";
		$orientation = $report->report_orientation ?  'portrait' : 'landscape' ;
		/*
		* 		Set page format and header/footer
		*/
		$pdf = new Cezpdf( 'A4', $orientation );
		$pdf->ezSetCmMargins( 3.25, 2, 1.5, 1.5 );
		$company_name = w2PgetConfig( 'company_name' );
		$page_header = $pdf->openObject();
		$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
		// Document title middle
		$ypos=$pdf->ez['pageHeight'] - ( 30 + $pdf->getFontHeight(12) );
		$pwidth=$pdf->ez['pageWidth'];
		$xpos= round( ($pwidth - $pdf->getTextWidth( 12, $report->report_title ))/2, 2 );
		$pdf->addText( $xpos, $ypos, 12, $report->report_title) ;
		// Company name left
		$pdf->selectFont( "$font_dir/Helvetica.afm" );
		$pdf->addText( round( $pdf->ez['leftMargin'], 2 ), $ypos, 10, $company_name );
		// Current date right
		$date = new CDate();
		$xpos = round( $pwidth - $pdf->getTextWidth( 10, $date->format($df)) - $pdf->ez['rightMargin'] , 2);
		$pdf->addText( $xpos, $ypos, 10, $date->format( $df ) );
		if ( !$doc_subtitle )
			{
			$pdf->ezText( strEzPdf( $doc_subtitle ), 12 ) ;
			$ypos -= round ( 1.2*$pdf->getFontHeight(12) , 2 ) ;
			}
		$pdf->ezSetY( $ypos );
		$pdf->closeObject($page_header);
		$pdf->addObject($page_header, 'all');
		// End of page header definition
		$xpos = round( $pwidth - $pdf->getTextWidth( 8, "Page xx/yy" ) - $pdf->ez['rightMargin'], 2 );
		$pdf->ezStartPageNumbers( $xpos , 30 , 8 ,'right','Page {PAGENUM}/{TOTALPAGENUM}') ;
		if ( $display_all || ( !$start_date && !$end_date ) )
			{
			$text = $AppUI->_( 'All entries', UI_OUTPUT_RAW ) ;
			}
		else
			{
			$tt1 = $end_date ? 'from' : 'after';
			$tt2 = $start_date ? 'to' : 'before' ;
			$text = strEzPdf( $AppUI->_( $report_datefilter_list[$report->report_datefilter][1], UI_OUTPUT_RAW)).' ' ;
			if ( $start_date ) $text .= strEzPdf( $AppUI->_( $tt1, UI_OUTPUT_RAW )).' '.$start_date->format( $df ).' ' ;
			if ( $end_date ) $text .= strEzPdf( $AppUI->_( $tt2, UI_OUTPUT_RAW)).' '.$end_date->format( $df ) ;
			}

		$pdf->ezText( $text , 9 ) ;
		$pdf->ezText( "\n" );
		// Create PDF print options array
		$title = null;
		$tbl_width = $pdf->ez['pageWidth'] - $pdf->ez['leftMargin'] - $pdf->ez['rightMargin'] ;
		// Set column definition
		$cols_definition = array();
		if ( $report->report_layout )
		// Settings for tabular format : fixed column size
			{
			$label_width = round( $tbl_width/10 );
			$data_width = round(   0.4 * $tbl_width );
			$title = null;
			$pdfcolumns = '';
			$cols_definition = array();
			$cols_definition[] =
				array(
					'justification' => 'right',
					'width'			=> $label_width
					);
			$cols_definition[] =
				array(
					'justification' => 'left',
					'width'			=> $data_width
					);
			$cols_definition[] =
				array(
					'justification' => 'right',
					'width'			=> $label_width
					);
			$cols_definition[] =
				array(
					'justification' => 'left',
					'width'			=> $data_width
					);
			$options = array
				(
				'showLines' 	=> 0,
				'showHeadings' 	=> 0,
				'fontSize' 		=> 9,
				'rowGap' 		=> 4,
				'colGap' 		=> 5,
				'xPos' 			=> 50,
				'xOrientation' 	=> 'right',
				'width'			=> $tbl_width,
				'shaded'		=> 0,
				'cols'			=> $cols_definition
				);
			// Create document body and PDF temp file
			// if show options are set split by subtable to include additional information
			if ( $show_subtitle || $show_Gantt ) {
				$pdf_subtable = array();
				$skip_page = 0 ;
				for ( $i=0 ; $i<count($pdfdata) ; $i++) {
					if ( $pdfdata[$i][0]) {
						$pdf_subtable[]=$pdfdata[$i];
					} else {
						if ( $skip_page ) $pdf->ezNewPage();
						$skip_page=$show_options[0];
						$pdf->ezTable( $pdf_subtable, $pdfcolumns, $title, $options );
						$pdf_subtable= array();
						// Add here call to functions for tasks stats, assignee stats and project Gantt if options is selected
						}
					}
			} else {
				$pdf->ezTable( $pdfdata, $pdfcolumns, $title, $options );
				}
			}
		else
		// Settings for columnar layout : calculate column width based on selected fields width
		// Show border lines and alternate shaded background
			{
			$total_width = 0 ;
			foreach( $selected_fields as $sf )
				$total_width += $field_desc[ $sf['report_field_table']] ['field_list'] [$sf['report_field_name']] [2] . "<br>" ;
			foreach ( $selected_fields as $sf ) {
				$dw = $field_desc[ $sf['report_field_table']] ['field_list'] [$sf['report_field_name']] [2] ;
				$data_width = round( $tbl_width * ( $dw/$total_width ) ) ;
				$cols_definition[] =
					array(
						'justification' => $field_desc[ $sf['report_field_table']] ['field_list'] [$sf['report_field_name']] [3],
						'width'			=> $data_width
						);
				}
			$options = array (
				'showLines' 	=> 2,
				'showHeadings' 	=> 1,
				'fontSize' 		=> 9,
				'rowGap' 		=> 4,
				'colGap'		=> 5,
				'xPos' 			=> 50,
				'xOrientation' 	=> 'right',
				'width'			=> $tbl_width,
				'shaded'		=> 0,
				'cols'			=> $cols_definition
				);
			// Create document body and PDF temp file
			if ( $show_subtitle ) {
				$count = 0;
				$break_idx = 1;
				// Split $pdfdata using $table_break
				while ( $count < count($pdfdata) ) {
					$sub_title = $pdfdata[$count][0];
					if ( $count )
						$pdf->ezNewPage();
					$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
					$pdf->ezText( $sub_title, 11 );
					$pdf->selectFont( "$font_dir/Helvetica.afm" );
					$pdf->ezText( "\n", 9 );
					$pdf_subtable = array();
					$count++;
					while ( $count < $table_break[$break_idx] ) {
						$pdf_subtable[]=$pdfdata[$count];
						$count++ ;
						}
					$pdf->ezTable( $pdf_subtable, $pdfcolumns, $title, $options );
					$break_idx++;
					}
				}
			else
				{
				$pdf->ezTable( $pdfdata, $pdfcolumns, $title, $options );
				}
			}
		// Create document body and PDF temp file
		$temp_dir = w2PgetConfig( 'root_dir' )."/files/temp";
		$base_url  = w2PgetConfig( 'base_url' );
		if ($fp = fopen( "$temp_dir/temp$AppUI->user_id.pdf", 'wb' )) {
			fwrite( $fp, $pdf->ezOutput() );
			fclose( $fp );
			$string = "<a href=\"$base_url/files/temp/temp$AppUI->user_id.pdf\" target=\"pdf\">";
			$string .= $AppUI->_( "View PDF File" );
			$string .= "</a>";
		} else {
			$string = "Could not open file to save PDF.  ";
			if (!is_writable( $temp_dir ))
				$string .= "The files/temp directory is not writable.  Check your file system permissions.";
			}
		echo $string ;
		break;
//	CSV report
	case 2 :
		// Initialize CSV table
		$ncols = $report->report_layout ? 4 : count($selected_fields);
		$csv = new Cw2Pcsv( $ncols );
		// Create header
		$csv->w2PcsvLine( w2PgetConfig( 'company_name' ) );
		$csv->w2PcsvLine( strEzPdf( $AppUI->_('Project Task Report', UI_OUTPUT_RAW)));
		if ( $project_id != 0 )
			$csv->w2PcsvLine( strEzPdf( $pname ));
		if ($display_all) {
			$csv->w2PcsvLine( strEzPdf( $AppUI->_( "All logs", UI_OUTPUT_RAW)));
		} else {
			$csv->w2PcsvLine( array(
							strEzPdf( $AppUI->_( 'Log entries', UI_OUTPUT_RAW)) ,
							strEzPdf( $AppUI->_('from', UI_OUTPUT_RAW )),
							strEzPdf( $AppUI->_( 'to', UI_OUTPUT_RAW))
							)
					) ;
			$csv->w2PcsvLine( array(
							'',
							$start_date ? $start_date->format( $df ): '' ,
							$end_date ? $end_date->format( $df ): ''
							)
					);
			}
		if ( ! $report->report_layout )
			$csv->w2PcsvLine( $csvcolumns );
		// Populate CSV report and create CSV temp file
		for ( $i=0; $i<count($pdfdata); $i++ )
			$csv->w2PcsvLine($pdfdata[$i]) ;
		echo $csv->w2PcsvStroke( "temp$AppUI->user_id.csv" ) ;
		break ;
	case 3 :
		// Initialize OOXML document
		include_once ( DP_BASE_DIR . "/modules/flexreports/cwordxml.php" );
		$orientation = $report->report_orientation ?  'portrait' : 'landscape' ;
		$docXML = new CWordXML( "A4", $orientation );
		// Use default styles
		$docXML->CWordDefault();
		// Create header/footer
		$docXML->newSection( "continue" );
		$docXML->setMargin( 2.25, 2, 1.5, 1.5, 1, 1);
		$pageWidth = $orientation == 'portrait' ? 18 : 26.7 ;
		$middle = $pageWidth/2;
		$right = $pageWidth ;
		$docXML->addStyle( "HF_Style", "CW_Normal", 
							array(	'tabs'		=> array( 'left:0', 'center:'.$middle, 'right:'.$right ),
									'spacing' 	=> array( 6, 6),
									'rPr'		=> array( "Arial", 10 )
								)
							);
		$company_name = conv2utf8(w2PgetConfig( 'company_name' ));
		$header = $docXML->startHFObj();
		$date = new CDate();
		$docXML->addText( 	array( $company_name, conv2utf8($report->report_title), conv2utf8($date->format($df)) ),
							"HF_Style",
							null,
							array( "", "tab", "tab" )
						);
		$docXML->endHFObj();
		$docXML->addHFObj( $header, 'hdr' );
		$footer = $docXML->startHFObj();
		$docXML->addText(	array( "", "", "{PAGE}", "/", "{NUMPAGES}"),
							"HF_Style",
							null,
							array( "", "tab", "tab")
						);
		$docXML->endHFObj();
		$docXML->addHFObj( $footer, 'ftr');
		// Report dates if any
		if ( $display_all || ( !$start_date && !$end_date ) ) {
			$text = $AppUI->_( 'All entries', UI_OUTPUT_RAW ) ;
		} else {
			$tt1 = $end_date ? 'from' : 'after';
			$tt2 = $start_date ? 'to' : 'before' ;
			$text = strEzPdf( $AppUI->_( $report_datefilter_list[$report->report_datefilter][1], UI_OUTPUT_RAW)).' ' ;
			if ( $start_date ) $text .= conv2utf8( $AppUI->_( $tt1, UI_OUTPUT_RAW )).' '.$start_date->format( $df ).' ' ;
			if ( $end_date ) $text .= conv2utf8( $AppUI->_( $tt2, UI_OUTPUT_RAW)).' '.$end_date->format( $df ) ;
			}
		$docXML->addText( $text, "CW_Normal");
		$docXML->addText( "", "CW_Normal");
		// Create style for table content
		$docXML->addStyle( "Table_Normal", "CW_Normal", array( 'spacing' => array( 3, 3) ) );
		$docXML->addStyle( "Table_Header", "Table_Normal", array( 'pControl' => 'center', 'rPr' => array( "Arial", 11, "b" ) ) );
		// Set table parameters
		if ( $report->report_layout ) {
			// No table header - 4 columns
			$cols = array(	array( 0.1*$pageWidth, "", "right"),
							array( 0.4*$pageWidth, "", "left"),
							array( 0.1*$pageWidth, "", "right"),
							array( 0.4*$pageWidth, "", "left")
							);
			$options = array(
						'width'			=> $pageWidth,
						'showBorders'	=> 0,
						'showHeaders'	=> 0,
						'cellStyle'		=> "Table_Normal",
						'splitRows'		=> 0
						);
		} else {
			$total_width = 0 ;
			foreach( $selected_fields as $sf )
				$total_width += $field_desc[ $sf['report_field_table']] ['field_list'] [$sf['report_field_name']] [2] . "<br>" ;
			if ( $show_period ) {
				$array_header = buildArrayHeader( $period_start_date, $period_end_date , $hideNonWorkingDays );
				$width += 25*(count($array_headers)-1) ;
				$ncols += count($array_header)-1 ;
				}
			foreach ( $selected_fields as $sf ) {
				$row = $field_desc[ $sf['report_field_table']] ['field_list'] [$sf['report_field_name']] ;
				$data_width = $pageWidth*($row[2]/$total_width) ;
				$cols[] = array( $data_width, $AppUI->_($sf['report_field_name'], UI_OUTPUT_RAW), $field_desc[ $sf['report_field_table']]['field_list'][$sf['report_field_name']][3] );
				}
			if ( $show_period )
				for ( $i=0 ; $i<count($array_header)-1 ; $i++ ) {
					$str_date = $array_header[$i]->format($df);
					$data_width = $pageWidth*(25/$total_width) ;
					$cols[] = array( $data_width, $str_date, 'center' );
					}
			$options = array(
						'width'			=> $pageWidth,
						'showBorders'	=> 3,
						'outerborderType' => 'double',
						'innerborderType' => 'single',
						'showHeaders'	=> 2,
						'headerStyle'	=> "Table_Header",
						'cellStyle'		=> "Table_Normal",
						'splitRows'		=> 0
						);
			}
		$docdata = conv2utf8($pdfdata);
		$docXML->addTable( $docdata, "", $cols, $options );
		$filename = w2PgetConfig( 'root_dir' )."/files/temp/temp$AppUI->user_id.doc";
		$docXML->output( $filename );
		echo "<a href=\"" . w2PgetConfig( "base_url") . "/files/temp/temp$AppUI->user_id.doc\" type=\"application/msword\" >";
		echo $AppUI->_( "View File" );
		echo "</a>";
		break;
	}
}