<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

global $report, $report_id, $tab, $field_desc, $select_reference, $user_time_field, $date_filter_list ;

// Retrieve existing options id report update
if ( $report_id ) {
	if ( $report->report_sortfields ){
		$sorts = explode( '+', $report->report_sortfields );
		foreach ( $sorts as $s ) {
			$sort_fields[] = explode( ',', $s);
        }
    }
	if ( $report->report_showoptions ) {
		$show_options = preg_split( "//", $report->report_showoptions, -1, PREG_SPLIT_NO_EMPTY );
    }
} else {
	$sort = array( "", "");
	$sort_fields=array( $sort, $sort, $sort);
	for ( $i=0 ; $i<8 ; $i++ ) {
		$show_options[$i]='0';
    }
}
// Build sort field array
$sort_field_select = array();
$table = array_keys( $field_desc );
foreach ( $table as $tbl )
	foreach ( $field_desc[$tbl]['field_list'] as $k => $v )
		if ( $v[4] >= 0 )
			$sort_field_select[$tbl][$tbl.':'.$k] = $AppUI->_($k);

?>
<script language="javascript">
function setOption( field, index) {
	var input=getCellsByName( "input", "showOptions");
	var checkbox = eval("document.optionsFrm.checkOption" + index );
	if ( field.checked ) {
		input[index].value = "1";
		checkbox.checked = true ;
    } else {
		input[index].value = "0" ;
		checkbox.checked = false ;
    }
}
</script>

<form name="optionsFrm" action="?m=flexreports" method="post">
    <input type="hidden" name="report_sortfields" value="" />
    <input type="hidden" name="report_showoptions" value="" />
    <input type="hidden" name="report_user_time" value="" />
    <?php
    for ( $i=0 ; $i<count($show_options); $i++ ) {
        echo "<input type=\"hidden\" name=\"showOptions\" value=\"" . $show_options[$i] . "\" />" ;
    }
    ?>

    <table cellpadding="4" cellspacing="0" border="0" class="tbl">
        <tr>
            <td width="30%" valign="top">
                <table callpadding="0" cellspacing="0" border="0" class="tbl" width="100%">
                    <tr>
                        <td colspan="2" nowrap="nowrap"><strong><?php echo $AppUI->_('Select sort fields'); ?></strong></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td></tr>
                    <tr>
                        <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Sort field"). " 1:" ; ?></td>
                        <td nowrap="nowrap">
                            <?php echo arraySelectWithOptgroup( $sort_field_select, 'sortFields', 'size="1" class="text"' , $sort_fields[0][0], false );?>
                            &nbsp;&nbsp;<input type="checkbox" name="descending" <?php echo $sort_fields[0][1] ? "checked" : "" ; ?> /><?php echo $AppUI->_('descending') ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Sort field"). " 2:" ; ?></td>
                        <td nowrap="nowrap">
                            <?php echo arraySelectWithOptgroup( $sort_field_select, 'sortFields', 'size="1" class="text"' , $sort_fields[1][0], false );?>
                            &nbsp;&nbsp;<input type="checkbox" name="descending" <?php echo $sort_fields[1][1] ? "checked" : "" ; ?> /><?php echo $AppUI->_('descending') ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Sort field"). " 3:" ; ?></td>
                        <td nowrap="nowrap">
                            <?php echo arraySelectWithOptgroup( $sort_field_select, 'sortFields', 'size="1" class="text"' , $sort_fields[2][0], false );?>
                            &nbsp;&nbsp;<input type="checkbox" name="descending" <?php echo $sort_fields[2][1] ? "checked" : "" ; ?> /><?php echo $AppUI->_('descending') ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td>&nbsp;</td>
            <td width="30%" valign="top">
                <table callpadding="0" cellspacing="0" border="0" class="tbl" width="100%">
                    <tr>
                        <td colspan="2" nowrap="nowrap"><strong><?php echo $AppUI->_("Sort field display options") ; ?></strong><br></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_( $report->report_layout ? "Skip page between groups" : "Show group names") ; ?>:&nbsp;</td>
                        <td nowrap="nowrap">
                        <input type="checkbox" name="checkOption0" <?php echo $show_options[0] ? "checked" : "" ; ?> onChange="javascript:setOption(this, 0)" />
                        <?php echo "(" . $AppUI->_('First level only') .")" ; ?>
                        </td>
                    </tr>
                    <?php if ( $report->report_layout == 0 ) { ?>
                        <tr>
                            <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_( "No field value repeat" ) ; ?>:&nbsp;</td>
                            <td nowrap="nowrap">
                            <input type="checkbox" name="checkOption0" <?php echo $show_options[1] ? "checked" : "" ; ?> onChange="javascript:setOption(this, 1)" />
                            <?php echo "(" . $AppUI->_('Second level only') .")" ; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </td>
            <td>&nbsp;</td>
            <td width="30%" valign="top">
                <table callpadding="0" cellspacing="0" border="0" class="tbl" width="100%">
                    <tr>
                        <td colspan="2" nowrap="nowrap"><strong><?php echo $AppUI->_("Report content options"); ?></strong></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Show time in days") ; ?>:&nbsp;</td>
                        <td nowrap="nowrap">
                            <input type="checkbox" name="checkOption2" <?php echo $show_options[2] ? "checked" : "" ; ?> onChange="javascript:setOption(this, 2)"/>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Show parent tasks") ; ?>:&nbsp;</td>
                        <td nowrap="nowrap">
                            <input type="checkbox" name="checkOption3" <?php echo $show_options[3] ? "checked" : "" ; ?> onChange="javascript:{setOption(this, 3); setOption(this, 4)}"/>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Show incomplete lines") ; ?>:&nbsp;</td>
                        <td nowrap="nowrap">
                            <input type="checkbox" name="checkOption4" <?php echo $show_options[4] ? "checked" : "" ; ?> onChange="javascript:setOption(this, 4)"/>
                        </td>
                    </tr>
                </table>
            </td>
            <td>&nbsp;</td>
            <td width="30%" valign="top">
                <table callpadding="0" cellspacing="0" border="0" class="tbl" width="100%">
                    <tr>
                        <td colspan="2" nowrap="nowrap"><strong><?php echo $AppUI->_("Report specific options"); ?></strong></td>
                    </tr>
                    <tr><td colspan="2">&nbsp;</td>
                    </tr>
                    <?php
                    // options available only for columnar layout
                    if ( $report->report_layout == 0 ) {
                        if ( $select_reference ) {
                            $date_select_list = array_merge( array( '  ' => 'User defined'), $date_filter_list );
                            if ( $report->report_user_time ) {
                                $time = explode( ',', $report->report_user_time );
                                $time_field = $time[0];
                                $time_period = $time[1];
                                $hideNonWorkingDays = $time[3];
                            } else {
                                $time_field = 0 ;
                                $time_period = 0 ;
                            }
                            ?>
                            <tr>
                                <td align="right"nowrap="nowrap" width="25%"><?php echo $AppUI->_("Show user time per period"); ?>:&nbsp;</td>
                                <td nowrap="nowrap">
                                <?php echo arraySelect( $user_time_field, 'time_field', 'size="1" class="text"', $time_field, true ); ?>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Period definition"); ?>:&nbsp;</td>
                                <td nowrap="nowrap">
                                <?php echo arraySelect( $date_select_list, 'time_period', 'size="1" class="text"', $time_period, true ); ?>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Hide non working days"); ?>:&nbsp;</td>
                                <td nowrap="nowrap">
                                <input type="checkbox" name="time_hide_NWD" <?php echo $hideNonWorkingDays ? "checked" : "" ; ?> />
                                </td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="2" nowrap="nowrap"><?php echo $AppUI->_("No further option available"); ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr>
                            <td align="right" nowrap="nowrap" width="25%"><?php echo $AppUI->_("Show Gantt chart"); ?>:&nbsp;
                            </td>
                            <td nowrap="nowrap">
                            <input type="checkbox" name="checkOption5" <?php echo $show_options[5] ? "checked" : "" ; ?> disabled onChange="javascript:setOption(this, 5)"/>
                            <?php echo "(" . $AppUI->_("Only in pdf") . ")" ; ?>
                            </td>
                        </tr><?php
                    } ?>
                </table>
            </td>
        </tr>
    </table>
</form>

<script language="javascript">
subForm.push(new FormDefinition(<?php echo $tab;?>, document.optionsFrm, optionsCheck, optionsSave));
</script>