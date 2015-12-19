<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

global $field_desc, $user_function_list, $user_field_list, $report_id, $report, $tab, $select_reference;
/*
*	Retrieve the list of selected fields for Edit
*/
$selected_fields = array();
if ( $report_id ) {
	$selected_fields = $report->getReportField();
}
if ( $report->report_layout > 0 ) {
//	Split field list in two columns if required
	foreach ( $selected_fields as $sf ) {
		if ( $sf['report_field_rank'] < 100 ) {
			$selected_field1[] = $sf;
		} else {
			$selected_field2[] = $sf;
		}
    }
} else {
	$selected_field1 = $selected_fields ;
	$selected_field2 = array();
}

$table_list = array_keys( $field_desc );

$select_reference = w2PgetParam( $_POST, 'reference', '');

if ( $select_reference ) {
	// Report referenced by users table : 
	//	- display user function table
	//	- add user fields for selection

	$field_desc['users']['field_list'] = $user_field_list ;
	foreach ( $user_function_list as $u ) {
		$user_function_select[]= $u[1];
    }
} else {
	$field_desc['users']['field_list'] = array() ;
}
?>
<form name="fieldsFrm" action="index.php?m=flexreports&a=do_report_aed" method="post">
    <input type="hidden" name="selected_field1_list" value="<?php implode(',', $selected_field1 ) ; ?>" />
    <input type="hidden" name="selected_field2_list" value="<?php implode(',', $selected_field2 ) ; ?>" />
    <input type="hidden" name="report_layout" value="<?php echo $report->report_layout ; ?>" />

    <table cellpadding="4" cellspacing="0" border="0" class="std" >
        <tr>
<?php
// Generate select field list from field description array
// One select for each DB table
if ( $select_reference ) {
    $ncols = 0 ;
    foreach( $table_list as $tbl ) {
        if ( count( $desc_field[$tbl]['field_list'] ) ) {
            $ncols++ ;
        }
    }
    ?>
    <td align="right" nowrap="nowrap" align="center" valign="top">
        <strong><?php echo $AppUI->_('User function') ; ?></strong>
    <br><br>
    <?php
        echo arraySelect( $user_function_select, 'user_function', 'class="text" size="1"', $report->report_reference, true );
        echo "</td>\n" ;
}
foreach ( $table_list as $tbl ) {
	if ( count($field_desc[$tbl]['field_list'])) {
        $table_name = $AppUI->_( $tbl );
        ?>
        <td nowrap="nowrap" align="center">
            <strong><?php echo $AppUI->_($tbl); ?></strong>
        <br><br>
        <?php
        // Build available field list
        $select_arr = array();
        foreach ( $field_desc[$tbl]['field_list'] as $field => $desc )
            $select_arr[$tbl.':'.$field] = $field ;
        echo arraySelect( $select_arr, $tbl.'_table', 'size="10" class="text"', NULL, true); ?>
        <br>
        <input type="button" class="button" value="Add >><?php echo $report->report_layout ? ' 1' : '>' ;?>" onClick="MoveField(this.form.<?php echo $tbl.'_table' ; ?>,this.form.selected_field1, '<?php echo $table_name ; ?>')"/>
        <?php if ( $report->report_layout == "1" )
                { ?>
                <br>
                <input type="button" class="button" value="Add >> 2" onClick="MoveField(this.form.<?php echo $tbl.'_table' ; ?>,this.form.selected_field2, '<?php echo $table_name ; ?>' )"/>
        <?php 	}	?>
        </td>
        <?php
	}
}
?>
	<td nowrap="nowrap" valign="top">
	<table cellpadding="0" cellspacing="0" border="0" class="tbl" >
	<tr>
	<td colspan="2" align="center" nowrap="nowrap">
		<strong><?php if ( $report->report_layout > 0 ) { echo $AppUI->_('Column').' 1' ; } else { echo $AppUI->_('Selected fields'); } ?></strong>
		<br><br>
	</td>
	</tr>
	<tr>
	<td align="center" nowrap="nowrap">
	<?php
	$select_arr = array();
	if ( count($selected_field1)) 
		foreach ( $selected_field1 as $k => $sf )
			$select_arr[$sf['report_field_table'].":".$sf["report_field_name"]] = $AppUI->_($sf['report_field_table']).":".$AppUI->_($sf["report_field_name"]) ;
	echo arraySelect( $select_arr, 'selected_field1', 'size="10" class="text" id="list"', NULL, false); ?>
	<br><br>
	</td>
	<td valign="center" nowrap="nowrap">
		<input type="button" class="button" value="&uarr;" onClick="UpField(this.form.selected_field1)"/>
				<br><br><br>
		<button type="button" class="button" value="" onClick="DelField(this.form.selected_field1)">
			<?php echo w2PshowImage( "trash_small.gif", 16, 16, "" ); ?>
		</button>
				<br><br><br>
		<input type="button" class="button" value="&darr;" onClick="DownField(this.form.selected_field1)"/>
			</td>
	</td>
	</tr>
	</table>
	</td>
<?php	
	if ( $report->report_layout == "1" )
		{			?>
	<td nowrap="nowrap" valign="top">
	<table cellpadding="0" cellspacing="0" border="0" class="tbl" >
	<tr>
	<td colspan="2" align="center" nowrap="nowrap">
		<strong><?php echo $AppUI->_('Column').' 2'; ?></strong>
		<br><br>
	</td>
	</tr>
	<tr>
	<td nowrap="nowrap">
	<?php
	$select_arr = array();
	if ( count($selected_field2)) 
		foreach ( $selected_field2 as $k => $sf )
			$select_arr[$sf['report_field_table'].":".$sf["report_field_name"]] = $AppUI->_($sf['report_field_table']).":".$AppUI->_($sf["report_field_name"]) ;
	echo arraySelect( $select_arr, 'selected_field2', 'size="10" class="text" id="list"', NULL, true); ?>
	<br><br>
	</td>
	<td valign="center" nowrap="nowrap">
		<input type="button" class="button" value="&uarr;" onClick="UpField(this.form.selected_field2)"/>
				<br><br><br>
		<button type="button" class="button" value="" onClick="DelField(this.form.selected_field2)">
			<?php echo w2PshowImage( "./modules/flexreports/images/stock_delete-16.png", 16, 16, "" ) ?>
		</button>
				<br><br><br>
		<input type="button" class="button" value="&darr;" onClick="DownField(this.form.selected_field2)"/>
			</td>
	</td>
	</tr>
	</table>
	</td>
<?php	}	?>
<td width="90%">&nbsp;</td>
</tr>
</form>
</table>
<script language="javascript">
subForm.push(new FormDefinition(<?php echo $tab;?>, document.fieldsFrm, fieldsCheck, fieldsSave));
</script>