<?php
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

global $field_desc, $predefined_value, $operator_list, $filter_select_array ;
global $AppUI, $report_id, $report, $tab ;

/*
*	Retrieve the list of conditions and filters for Edit
*/
$report_filters = array();
if ( $report_id ) {
	$report_filters = $report->getReportFilter();
}
if ( count($report_filters) ) {
	foreach ( $report_filters as $rf ) {
		$row = $field_desc[$rf['report_filter_table']]['field_list'][$rf['report_filter_name']];
		$report_valuename = ( $row[4] >= 4 && $rf['report_filter_value'] ) ? getNamesFromID( $rf['report_filter_value'], $row[5] ) : $rf['report_filter_value'] ;
		$temp = array(
					  'index'		=> $rf['report_filter_table'] . ':' . $rf['report_filter_name'],
					  'name' 		=> $AppUI->_($rf['report_filter_table']) . ':' . $AppUI->_($rf['report_filter_name']),
					  'operator'	=> $rf['report_filter_operator'],
					  'value'		=> $rf['report_filter_value'],
					  'valuename'	=> $report_valuename ,
					  'label'		=> $rf['report_filter_label']
					  );
		if ( $rf['report_filter_mode']) {
			$selected_filter[] = $temp ;
		} else {
			$selected_condition[] = $temp ;
        }
    }
}
/*
* 	Build list of available filters for select
* 	(use optgroup to separate the various tables)
*/

$table = array_keys( $field_desc );

$filter_select_array = array();
foreach ( $table as $tbl ) {
	foreach ( $field_desc[$tbl]['field_list'] as $field => $filter ) {
		if ( $filter[4] > 0 ) {
			$filter_select_array[$tbl][$tbl.':'.$field] = $AppUI->_($field);
        }
    }
}
$selectNone = array( 0 => $AppUI->_('None').".............."); // Should keep the dots for IE (pb with redimensioning select options)

?>
<script language="javascript">
// Array of filter definition objects
var filterArray = new Array();
// Filter definition object constructor
function CFilter( filterType, fieldTable, fieldName, filterPredef ) {
	this.filterType = filterType;
	this.fieldTable = fieldTable;
	this.fieldName = fieldName;
	this.filterPredef = filterPredef;
	return;
}
<?php
	// generate a javascript array defining the filter type of each field
	foreach ( $table as $tbl ) {
		foreach( $field_desc[$tbl]['field_list'] as $field =>$filter ) {
			if ( $filter[4] > 0 ) {
				$param = array();
				$param[0] = "\"" . $filter[4] . "\"";
				$fieldtable = ( $nc = strpos( $filter[5], "|") ) ? substr($filter[5], $nc+1 ) : $filter[5] ;
				$param[1] = "\"$fieldtable\"";
				$param[2] = "\"" . ( $filter[4] == 6 ? $filter[0] : "" ) . "\"" ;
				$field_tbl = "\"" . ( $nc = strpos( $filter[5], "|") ? substr($filter[5], $nc+1) : $filter[5] ) . "\"";
				$param[3] = "\"" . ( $filter[4] >= 4  ? $predefined_value[$fieldtable] : "" ) . "\"";
                ?>filterArray[<?php echo "\"$tbl:$field\""; ?>] = new CFilter( <?php echo implode( ", ", $param ); ?> );<?php
            }
        }
    }
	?>var table_name = new Array();<?php
	// generate javascript array for table name translation
	echo "\ntable_name[\"\"] = \"-\" ; " ;
	foreach ( $table as $tbl ) {
		echo "\ntable_name[\"" . $tbl . "\"]= \"" . $AppUI->_($tbl) . "\";" ;
    }
	echo "\n" ;
	?>

var max_select_option = <?php echo count($operator_list); ?> ;
// operator_option defines available filter operator options depending on field type
var none_option =   new Array(1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
var num_option  =   new Array(1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0);
var text_option =   new Array(1,1,1,0,0,0,0,1,1,1,0,0,0,0,0,0);
var flag_option =	new Array(1,0,0,0,0,0,0,0,0,0,0,0,1,1,0,0);
var sysval_option = new Array(1,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0);
var date_option =	new Array(1,1,1,1,1,1,1,0,0,0,0,0,0,0,0,0);
var id_option =		new Array(1,0,0,0,0,0,0,0,0,0,1,1,0,0,0,0);
var custom_option = new Array(1,0,0,0,0,0,0,0,0,0,1,1,0,0,1,1);
var operator_option = new Array(
		 					none_option,		// none (no selected filter)
		 					num_option,			// numerical values
							text_option,		// text values
							flag_option,		// flag values
							sysval_option,		// SysVal values
							date_option,		// date values
							id_option,			// ID values
							custom_option		// Custom values
	);
// Array select_operator provides text for each filter operator option
var select_operator = new Array(
<?php
$s = '';
foreach ( $operator_list as $op ) {
	$s .= ( $s ? ",\n" : "" ) . "'" . $AppUI->_($op) ."'" ;
}
echo $s ;
?>
					);

function updateSelect( table, field ) {
	var selectArray = eval( "document.filtersFrm." + table + "_selectoperator") ;
	if ( ! field ) {
		// Reset operator select
		var type = 0 ;
	} else {
		// find the type of the field selected in table_select
		if ( field.options.selectedIndex <= 0 ) {
			alert("<?php echo $AppUI->_('InvalidFieldSelection', UI_OUTPUT_JS); ?>");
			return false ;
        }
		var name = field.options[field.options.selectedIndex].value;
		var type = filterArray[name].filterType;
    }
	// Set new options
	var flagArray = operator_option[type] ;
	var Arraycount = 0 ;
	for ( var i = 0 ; i < flagArray.length; i++ ) {
		// use Arraycount as index because select options are renumbered when an option is set to null
		selectArray.options[Arraycount]=null;
		if ( flagArray[i] ) {
			opt = new Option( select_operator[i], i );
			selectArray.options[Arraycount] = opt ;
			Arraycount++;
        }
    }
	selectArray.options.selectedIndex=0;
	// Show appropriate value input element
	var inputvalue = getCellsByName( "input", table + "_inputvalue" )[0];
	inputvalue.value = "";
	var filter_value = getCellsByName( "input", table + "_inputvaluename" )[0];
	filter_value.value = "";
	if ( type >= 4 ) {
		filter_value.readOnly="readOnly";
		if ( type < 12 ) {
			getCellsByName( "input", table + "_inputbutton" )[0].style.visibility="visible";
        }
	} else {
		getCellsByName( "input", table + "_inputbutton" )[0].style.visibility="hidden";
		filter_value.readOnly="";
    }
}

function deleteFilter( table ) {
	var box = getCellsByName( "input", table + "_delete");
	var counter = eval( "document.filtersFrm." + table + "_count" );
	for ( i=box.length-1; i>=0; i-- ) {
		if ( box[i].checked ) {
			var rank = i+3 ;
			document.getElementById(table).deleteRow(rank);
        }
    }
	counter.value = getCellsByName( "input", table + "_delete").length ;
	if ( counter.value == 0 ) {
		var f = document.getElementById( table + "_button" );
		f.style.visibility = "hidden" ;
    }
}

function addFilter( table ) {

	var field=eval( "document.filtersFrm." + table + "_selectfield");
	var operator=eval( "document.filtersFrm." + table + "_selectoperator");
	var value=eval("document.filtersFrm." + table + "_inputvalue");
	var valuename=eval("document.filtersFrm." + table + "_inputvaluename" );
	// check entries
	if ( field.options.selectedIndex <= 0 ) {
        alert("<?php echo $AppUI->_('InvalidFieldSelection', UI_OUTPUT_JS); ?>");
        return false ;
    }
	if ( operator.options.selectedIndex <= 0 ) {
        alert("<?php echo $AppUI->_('InvalidOperatorSelection', UI_OUTPUT_JS); ?>");
        return false ;
    }
	if ( table == "condition" && operator.options.selectedIndex < 12 && valuename.value.length == 0 ) {
        alert("<?php echo $AppUI->_('InvalidValueInput', UI_OUTPUT_JS); ?>");
        return false ;
    }
	// Check input value does not contain '|'
	if ( valuename.value.lastIndexOf('|') >= 0 ) {
		alert("<?php echo $AppUI->_('InvalidValueInput', UI_OUTPUT_JS) ; ?>") ;
		return false;
    }
	// Initiate var label if filter table
	// Check label is set if value is not set
	if ( table == 'filter' ) {
		var label=eval("document.filtersFrm.filter_inputlabel");
		if ( valuename.value.length == 0 && label.value.length == 0 ) {
			alert( "<?php echo $AppUI->_('NoFilterLabel') ; ?>" );
			return false ;
        }
		if ( label.value.lastIndexOf('|') >= 0 ) {
			alert("<?php echo $AppUI->_('InvalidLabelInput', UI_OUTPUT_JS) ; ?>") ;
			return false;
        }
    }
	// Check input value is numeric if field type == 1
	if ( valuename.value.length > 0 ) {
		if ( filterArray[field.options[field.options.selectedIndex].value].filterType == 1 ) {
			if ( isNaN(valuename.value) ) {
				alert("<?php echo $AppUI->_('InvalidValueInput', UI_OUTPUT_JS) ; ?>") ;
				return false;
            }
        }
    }
	// Create new row from input
	var counter = eval( "document.filtersFrm." + table + "_count" );
	var rownum = parseInt(counter.value)+3;
	var row=document.getElementById(table).insertRow(rownum);
	// Build input readOnly fieldname
	var cell=document.createElement("td");
	var input=document.createElement("input");
	input.name= table+"_fieldname";
	input.type="text";
	input.size="22";
	var classText = document.createAttribute("class");
    classText.nodeValue = "text";
    input.setAttributeNode(classText);
	// generate user field name = translation(table name):translation(field name)
	txt = field.options[field.options.selectedIndex].value;
	tablename = txt.substring( 0, txt.lastIndexOf( ":"));
	// we need to skip the blank chars inserted in the field name in select array to show indentation
	input.value=table_name[tablename] + ":" + field.options[field.options.selectedIndex].text.substring(3);
	input.readOnly="readOnly";
	cell.appendChild(input);
	// Bulid input hidden field
	var input=document.createElement("input");
	input.name= table+"_field";
	input.type="hidden";
	input.value=field.options[field.options.selectedIndex].value;
	cell.appendChild(input);
	row.appendChild(cell);
	// Build operator index input (hidden)
	var cell=document.createElement("td");
	var input=document.createElement("input");
	input.name=table+"_operator";
	input.type="hidden";
	input.value=operator.options[operator.options.selectedIndex].value;
	cell.appendChild(input);
	// build operator text input (visible)
	var input=document.createElement("input");
	input.name=table+"_operatorname";
	input.type="text";
	var classText = document.createAttribute("class");
    classText.nodeValue = "text";
    input.setAttributeNode(classText);
	input.size="20";
	input.readOnly="readOnly";
	input.value=operator.options[operator.options.selectedIndex].text;
	cell.appendChild(input);
	row.appendChild(cell);
	// build filter valuename input (visible)
	var cell=document.createElement("td");
	var input=document.createElement("input");
	input.name=table+"_valuename";
	input.type="text";
	var classText = document.createAttribute("class");
    classText.nodeValue = "text";
    input.setAttributeNode(classText);
	input.size="20";
	input.readOnly="readOnly";
	input.value=valuename.value;
	cell.appendChild(input);
	// Build filter value input (hidden)
	var input=document.createElement("input");
	input.name=table+"_value";
	input.type="hidden";
	if ( value.value == "" ) {
		input.value=valuename.value;
	} else {
		input.value=value.value
    }
	cell.appendChild(input);
	row.appendChild(cell);
	// Label only for filter
	if ( table == 'filter' ) {
		var cell=document.createElement("td");
		var input=document.createElement("input");
		input.name=table+"_label";
		input.type="text";
		var classText = document.createAttribute("class");
		classText.nodeValue = "text";
		input.setAttributeNode(classText);
		input.size="20";
		input.readOnly="readOnly";
		input.value=label.value;
		cell.appendChild(input);
		row.appendChild(cell);
		// Reset label input
		label.value="";
    }
	// include here a delete button
	var cell=document.createElement("td");
	cell.align="center";
	var input=document.createElement("input");
	input.name=table+"_delete";
	input.type="checkbox";
	cell.appendChild(input);
	row.appendChild(cell);
	// Show delete button
	var cell = document.getElementById(table + "_button");
	cell.style.visibility = "visible";
	// Reset selection fields
	field.options.selectedIndex=0;
	updateSelect( table, null );
	value.value=""; // re-init input text
	valuename.value="";
	// Update number of fields selected as condtions or filter
	counter.value++;
}

var opened_window ;
function popFilterValue( table ) {
	var selectname = eval("document.filtersFrm." + table +"_selectfield");
	var text = selectname.options[selectname.options.selectedIndex].value ;
	var fieldtable = filterArray[text].fieldTable;
	var fieldname = filterArray[text].fieldName;
	var showdefault = 1 ;
	if ( filterArray[text].filterType < 6 ) {
		showdefault = 0 ;
    }
	opened_window = window.open('./index.php?m=flexreports&a=selector&suppressHeaders=1&fieldtable='+fieldtable+'&fieldname='+fieldname+'&returnfield='+table+'&callback=setFilterValue&showdefault='+showdefault, table,'height=600,width=400,resizable,scrollbars=yes');
}

function setFilterValue( field, selected_id, selected_name ) {
	var save_id = eval('document.filtersFrm.' + field + "_inputvalue") ;
	var save_name = eval('document.filtersFrm.' + field + "_inputvaluename") ;
	if ( selected_id.length == 0 ) {
		opened_window.close();
		alert("<?php echo $AppUI->_('No item for selection'); ?>");
		save_id.value = '';
		save_name.value = '';
		return false;
    }
	if ( selected_id != "0" ) {
		save_id.value = selected_id ;
		save_name.value = selected_name ;
	} else {
		var fieldNameSelect = eval("document.filtersFrm." + field + "_selectfield");
		var fieldname = fieldNameSelect.options[fieldNameSelect.options.selectedIndex].value;
		save_id.value = "{" + filterArray[fieldname].filterPredef + "}";
		save_name.value = save_id.value;
    }
	opened_window.close();
}
</script>

<form name="filtersFrm" action="index.php?m=flexreports&a=do_report_aed" method="post">
    <input type="hidden" name="condition_count" value="<?php echo count($selected_condition) ; ?>" />
    <input type="hidden" name="filter_count" value="<?php echo count($selected_filter) ; ?>" />
    <input type="hidden" name="condition_list" value="" />
    <input type="hidden" name="filter_list" value="" />

    <table cellpadding="0" cellspacing="0" border="1" class="std">
        <tr>
            <td width="45%" valign="top">
                <table id="condition" cellpadding="4" cellspacing="0" border="0" class="tbl" width="100%">
                    <tr>
                        <td colspan="4"><strong><?php echo $AppUI->_('List of conditions'); ?></strong></td>
                    </tr>
                    <tr><td colspan="4">&nbsp;</td></tr>
                    <tr>
                        <td width="35%">
                            <?php echo arraySelectWithOptgroup( $filter_select_array, 'condition_selectfield', 'size="1" class="text" onChange="javascript:updateSelect(\'condition\', this)"' , 0, false, 'Select field' );?>
                        </td>
                        <td width="20%">
                            <?php echo arraySelect( $selectNone, 'condition_selectoperator', 'size="1" class="text"', 0, true ); ?>
                        </td>
                        <td width="45%" nowrap="nowrap">
                            <input type="text" class="text" name="condition_inputvaluename" size="20" value="" />
                            <input type="hidden" name="condition_inputvalue" value="" />
                            <input type="button" class="button" name="condition_inputbutton" style="visibility:hidden"  value="<?php echo $AppUI->_('Select') ; ?>" onClick="javascript:popFilterValue('condition')" />
                        </td>
                        <td width="1%" align="center">
                            <input type="button" class="button" name="condition_validate" value="&darr;" onClick="javascript:addFilter('condition')" />
                        </td>
                    </tr>
                    <?php if ( count($selected_condition) ) {
                        foreach ( $selected_condition as $v ) { ?>
                            <tr>
                                <td>
                                    <input type="text" class="text" name="condition_fieldname" size="22" readOnly value="<?php echo $v['name'] ; ?>" />
                                    <input type="hidden" name="condition_field" value="<?php echo $v['index'] ; ?>" />
                                </td>
                                <td>
                                    <input type="text" class="text" name="condition_operatorname" size="20" readOnly value="<?php echo $AppUI->_($operator_list[$v['operator']]) ; ?>" />
                                    <input type="hidden" name="condition_operator" value="<?php echo $v['operator'] ; ?>" />
                                </td>
                                <td>
                                    <input type="text" class="text" name="condition_valuename" size="20" readOnly value="<?php echo $v['valuename'] ; ?>" />
                                    <input type="hidden" name="condition_value" value="<?php echo $v['value'] ; ?>" />
                                </td>
                                <td align="center">
                                    <input type="checkbox" name="condition_delete" unchecked />
                                </td>
                            </tr>
                            <?php
                        }
                    } ?>
                    <tr>
                        <td colspan="4" align="right">
                        <input type="button" class="button" id="condition_button" style="visibility:<?php echo count($selected_condition) ? 'visible' : 'hidden' ; ?>" value="<?php echo $AppUI->_('Delete checked item'); ?>" onClick="javascript:deleteFilter('condition')" />
                        </td>
                    </tr>
                </table>
            </td>
            <td width="55%" valign="top">
                <table id="filter" cellpadding="4" cellspacing="0" border="0" class="tbl" width="100%">
                    <tr>
                        <td colspan="5"><strong><?php echo $AppUI->_('List of filters'); ?></strong></td>
                    </tr>
                    <tr><td colspan="5">&nbsp;</td></tr>
                    <tr>
                        <td width="25%">
                            <?php echo arraySelectWithOptgroup( $filter_select_array, 'filter_selectfield', 'size="1" class="text" onChange="javascript:updateSelect(\'filter\', this)"' , 0, false, 'Select field' );?>
                        </td>
                        <td width="19%">
                            <?php echo arraySelect( $selectNone, 'filter_selectoperator', 'size="1" class="text"', 0, true ); ?>
                        </td>
                        <td width="30%" nowrap="nowrap">
                            <input type="text" class="text" name="filter_inputvaluename" size="20" value="" />
                            <input type="hidden" class="text" name="filter_inputvalue" value="" />
                            <input type="button" class="button" name="filter_inputbutton" style="visibility:hidden" value="<?php echo $AppUI->_('Select') ; ?>" onClick="javascript:popFilterValue('filter')" />
                        </td>
                        <td width="25%">
                            <input type="text" class="text" name="filter_inputlabel" size="20" value="" />
                        </td>
                        <td width="1%" align="center">
                            <input type="button" class="button" name="filter_validate" value="&darr;" onClick="javascript:addFilter('filter')" />
                        </td>
                    </tr>
                    <?php if ( count($selected_filter) ) {
                        foreach ( $selected_filter as $v ) { ?>
                            <tr>
                                <td>
                                    <input type="text" class="text" name="filter_fieldname" size="22" readonly value="<?php echo $v['name'] ; ?>" />
                                    <input type="hidden" name="filter_field" value="<?php echo $v['index'] ; ?>" />
                                </td>
                                <td>
                                    <input type="text" class="text" name="filter_operatorname" size="20" readonly value="<?php echo $AppUI->_($operator_list[$v['operator']]) ; ?>" />
                                    <input type="hidden" name="filter_operator" value="<?php echo $v['operator'] ; ?>" />
                                </td>
                                <td>
                                    <input type="text" class="text" name="filter_valuename" size="20" readonly value="<?php echo $v['valuename'] ; ?>" />
                                    <input type="hidden" name="filter_value" value="<?php echo $v['value']; ?>" />
                                </td>
                                <td>
                                    <input type="text" class="text" name="filter_label" size="20" readonly value="<?php echo $v['label'] ; ?>" />
                                </td>
                                <td align="center">
                                    <input type="checkbox" name="filter_delete" unchecked />
                                </td>
                            </tr>
                            <?php
                        }
                    } ?>
                    <tr>
                        <td colspan="5" align="right">
                        <input type="button" class="button" id="filter_button" style="visibility:<?php echo count($selected_filter) ? 'visible' : 'hidden' ; ?>" value="<?php echo $AppUI->_('Delete checked item'); ?>" onClick="javascript:deleteFilter('filter')" />
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</form>

<script language="javascript">
subForm.push(new FormDefinition(<?php echo $tab;?>, document.filtersFrm, filtersCheck, filtersSave));
</script>
