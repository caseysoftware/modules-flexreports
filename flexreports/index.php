<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}
require_once ( $AppUI->getModuleClass( 'flexreports'));

// check permission
$perms =& $AppUI->acl();
if ( ! $perms->checkModule( 'Flexreports', 'view', $user_id ) ) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$project_id = intval( w2PgetParam( $_REQUEST, 'project_id', 0 ));
$tab = intval( w2PgetParam( $_REQUEST, 'project_id', 0 ) );

// Retrieve permissions
$canAdd = $perms->checkModule( 'Flexreports' , 'add' , $user_id ) ;

// Setup the title block
if ( $tab == 0 ) {
	$titleBlock = new w2p_Theme_TitleBlock( 'FlexReports', 'colored_folder.png', $m, "$m.$a" );
	if ( $canAdd ) {
		$titleBlock->addCell(
			'<form action="?m=flexreports&amp;a=addedit" method="post">
				<input type="submit" class="button" value="'.$AppUI->_('new report').'" />
			</form>', '',	'', '');
    }
	$titleBlock->show() ;
}

$report = new CFlexReport();
$colspan = $project_id ? 3 : 4 ;
//--- print Table Headers ----//
?>
<script language="javascript">
function popTest()
{
	window.open('./index.php?m=flexreports&a=test');
}
</script>
<table border="0" width="100%" cellspacing="1" cellpadding="2" class="tbl">
    <tr>
        <th width="1%">&nbsp;</th>
        <th width="30%" nowrap="nowrap"><?php echo $AppUI->_('Report name') ; ?></th>
        <th width="<?php echo $project_id ? '70' : '50' ; ?>%"><?php echo $AppUI->_('Report description') ; ?></th>
        <?php if ( ! $project_id ) { ?>
            <th width="20%" "nowrap="nowrap"><?php echo $AppUI->_('Allowed projects') ; ?></th>
        <?php } ?>
    </tr>
<?php
/*
* Retrieve reports that the current user can display for all projects 
*/

if ( ! $project_id )
{
?>
	<tr>
		<td colspan="<?php echo $colspan ; ?>">
		<strong><?php echo $AppUI->_('Available for all projects') ; ?></strong>
		</td>
	</tr>
<?php
}
$all_project_report = $report->getAllowedReportId( $AppUI->user_id, 'user' );
if ( count($all_project_report)) {
	foreach ( $all_project_report as $rep_id ){
		$report->load($rep_id);
		$report_edit = ( $perms->checkModule('flexreports', 'edit') && $report->report_creator == $AppUI->user_id ) || $perms->checkModule('admin', 'edit');
		$report->show_report( $project_id, $report_edit, 'all' );
    }
} else {
    if ( ! $project_id ) {
        echo "<tr>";
        echo "<td colspan=\"" . $colspan . "\"><strong>" . $AppUI->_('No report available') ."</stong></td>";
        echo "</tr>";
    }
}
if ( ! $project_id ) {
	echo "<tr>";
	echo "<td colspan=\"" . $colspan . "\"><strong>" . $AppUI->_('Restricted access') . "</stong></td>";
	echo "</tr>";
}
/*
* Retrieve reports available for specific projects
*/
$some_project_report = $report->getAllowedReportId( $AppUI->user_id, 'project', $project_id );
if (count($some_project_report))
{
	foreach ( $some_project_report as $rep_id ) {
		$report->load($rep_id);
		$report_edit = ( $perms->checkModule('flexreports', 'edit') && $report->report_creator == $AppUI->user_id ) || $perms->checkModule('admin', 'edit');
		$report->show_report( $project_id, $report_edit, 'project' );
    }
} else {
	if ( ! $project_id ) {
		echo "<tr>";
		echo "<td colspan=\"" . $colspan . "\"><strong>" . $AppUI->_('No report available') ."</stong></td>";
		echo "</tr>";
    }
}
if ( $project_id && (count($all_project_report) + count($some_project_report)) == 0 ) {
	echo "<tr>";
	echo "<td colspan=\"" . $colspan . "\"><strong>" . $AppUI->_('No report available') ."</stong></td>";
	echo "</tr>";
}
// end display reports
?>
</table>