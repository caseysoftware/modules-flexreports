<?php 
global $AppUI, $baseDir, $report_id, $report, $field_desc, $report_format_list, $report_datefilter_list, $tab;

// Retrieve list of report format
$report_format = array();
if ( $report_id ) {
	$report_format = explode( ',', $report->report_format);
}
// Retrieve company list
$company = new CCompany();
$cpies =$company->getAllowedRecords( $AppUI->user_id, 'company_id, company_name');
foreach ( $cpies as $k => $c ) {
	$company_select_list[$k] = $c['company_name'];
}
// Retrieve project list
$project = new CProject();
$proj = $project->getAllowedRecords( $AppUI->user_id, 'projects.project_id, project_name');
foreach ( $proj as $k => $p ) {
	$project_select_list[$k] = $p['project_name'];
}
// Build datefilter select list
foreach ( $report_datefilter_list as $field => $value ) {
	$datefilter_select_list[$field] = $AppUI->_($value[0]);
}
// retrieve allowed project, company and user list if edit
if ( $report_id ) {
	// Retrieve allowed company list
	$q = new w2p_Database_Query();
	$q->addTable('flexreport_access');
	$q->addQuery('report_access_id');
	$q->addWhere('report_access_report = ' . $report_id);
	$q->addWhere('report_access_type = ' . COMPANY_RESTRICTED );
	$companies = $q->loadColumn();
	$company_list = count( $companies ) ? implode( ',', $companies) : "" ;
	// Retrieve allowed project list
	$q->clear();
	$q->addTable('flexreport_access');
	$q->addQuery('report_access_id');
	$q->addWhere('report_access_report = ' . $report_id);
	$q->addWhere('report_access_type = ' . PROJECT_RESTRICTED );
	$projects = $q->loadColumn();
	$project_list = count( $projects ) ? implode( ',', $projects) : "" ;
	// Retrieve allowed user list
	$q->clear();
	$q->addTable('flexreport_access');
	$q->addQuery('report_access_id');
	$q->addWhere('report_access_report = ' . $report_id);
	$q->addWhere('report_access_type = ' . USER_RESTRICTED );
	$users = $q->loadColumn();
	$user_list = count( $users ) ? implode( ',', $users ) : "" ;
} else {
	$company_list = "";
	$project_list = "";
	$user_list = "";	
}
?>
<script language='javascript'>
var opened_window ;
function popSelect( table, field, list ) {
	opened_window = window.open('./index.php?m=flexreports&a=selector&suppressHeaders=1&fieldtable='+table+'&returnfield='+field+'&selected_id='+list+'&callback=setSelect', table,'height=600,width=400,resizable,scrollbars=yes');
}
function setSelect( field, selected_id, names ) {
	var savefield = eval('document.detailFrm.' + field) ;
	if ( selected_id == '0' ) {
		opened_window.close();
		alert("<?php echo $AppUI->_('No item for selection'); ?>");
		savefield.value = '';
		return false;
    }
	savefield.value = selected_id ;
	opened_window.close();
}
</script>
<form name="detailFrm" action="index.php?m=flexreports&a=do_report_aed" method="post">
    <input type="hidden" name="company_list" value="<?php echo $company_list ; ?>" />
    <input type="hidden" name="project_list" value ="<?php echo $project_list ; ?>" />
    <input type="hidden" name="user_list" value="<?php echo $user_list ; ?>" />
    <input type="hidden" name="report_format" value="<?php echo $report->report_format ; ?>" />

    <table cellspacing="0" cellpadding="4" border="0" width="100%" class="tbl">
        <tr>
            <td>
                <table cellspacing="0" cellpadding="4" border="0" width="50%" class="tbl">
                    <tr>
                        <td colspan="2">
                            <strong><?php echo $AppUI->_('Description') ; ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td width="40%" align="right" nowrap="nowrap"><?php echo $AppUI->_('Report Title') ; ?>:</td>
                        <td width="60%"><input type="text" class="text" size="40" name="report_title" value="<?php echo $report->report_title ; ?>" /></td>
                    </tr>
                    <tr>
                        <td width="40%" align="right" nowrap><?php echo $AppUI->_('Date range filter on'); ?>:</td>
                        <td width="60%"><?php echo arraySelect( $datefilter_select_list, 'report_datefilter', 'class="text" size="1"', $report->report_datefilter, false) ; ?></td>
                    </tr>
                    <tr>
                        <td width="40%" align="right" valign="top" nowrap><?php echo $AppUI->_('Available file formats'); ?>:</td>
                        <td width="60%">
                            <?php
                            for ( $i=1 ; $i<count($report_format_list) ; $i++)
                                {
                                ?>
                                <input type="checkbox" class="text" name="format_list" value="<?php echo $i ; ?>" <?php echo in_array( $i, $report_format) ? "checked" : "" ; ?> />&nbsp;<?php echo $AppUI->_($report_format_list[$i]) ; ?><br>
                                <?php
                                }
                                ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="40%" align="right" valign="top"><?php echo $AppUI->_('Report orientation'); ?>:</td>
                        <td width="60%">
                            <input type="radio" class="text" name="report_orientation" value="0" <?php echo $report->report_orientation ? "" : "checked" ; ?> />&nbsp;<?php echo $AppUI->_('landscape'); ?>
                            <br>
                            <input type="radio" class="text" name="report_orientation" value="1" <?php echo $report->report_orientation ? "checked" : "" ; ?> />&nbsp;<?php echo $AppUI->_('portrait'); ?>
                        </td>
                    </tr>
                </table>
            </td>
            <?php if ( $report->report_type == "1" ) { ?>
            <td align="left" valign="top">
                <table cellspacing="0" cellpadding="4" border="0" width="50%" class="tbl">
                    <tr>
                        <td>
                            <strong><?php echo $AppUI->_('Access rights') ; ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="button" class="button" value="<?php echo $AppUI->_('Select companies') ; ?>" onClick="javascript:popSelect('companies', 'company_list', this.form.company_list.value )" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="button" class="button" value="<?php echo $AppUI->_('Select projects') ; ?>" onClick="javascript:popSelect('projects', 'project_list', this.form.project_list.value )" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="button" class="button" value="<?php echo $AppUI->_('Select users') ; ?>" onClick="javascript:popSelect('users', 'user_list', this.form.user_list.value)" />
                        </td>
                    </tr>
                </table>
            </td>
            <?php } ?>
        </tr>
    </table>
</form>
<script language="javascript">
subForm.push(new FormDefinition(<?php echo $tab;?>, document.detailFrm, detailsCheck, detailsSave));
</script>