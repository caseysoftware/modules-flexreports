<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

global $AppUI, $baseDir, $report, $tab;

/*
* 	Retrieve parameters
*/
$report_id = w2PgetParam($_REQUEST, 'report_id', 0);
if ( isset($_REQUEST['tab'])) {
	$AppUI->setState('ReportTabIdx', w2PgetParam($_REQUEST, 'tab', 0));
}
$tab = $AppUI->getState('ReportTabIdx', 0);
$add_step = w2PgetParam($_POST, 'add_step', 0);
$cancel = $AppUI->getPlace();

/*
*	Check permissions
*/
$perms =& $AppUI->acl();
if (!$report_id ){
	$canEdit = $perms->checkModule( 'flexreports', 'add');
}
if ( !$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
/*
*  Check if report model to be updated exists
*/
$report = new CFlexReport();
if ( $report_id && !$report->load( $report_id )) {
	$AppUI->setMsg( 'Report' );
	$AppUI->setMsg( 'invalidID', UI_MSG_ERROR, true );
	$AppUI->redirect();
}
// Retrieve first add step input
if ( $add_step ) {
	$report->bind( $_POST );
}

/*
*	Set Title block
*/

$ttl = $report_id > 0 ? 'Edit Report' : 'New Report';
$titleBlock = new w2p_Theme_TitleBlock( $ttl, 'colored_folder.png', $m, "$m.$a" );
$titleBlock->addCrumb( '?m=flexreports', 'reports list' );
if ( $report_id ) {
	$titleBlock->addCrumb('?m=flexreports&a=view&report_id=' . $report_id, 'view this report');
}
// Include here delete right crumb
$canDelete = $report_id &&  ( ( $perms->checkModule( 'flexreports', 'delete') && $report->report_creator == $AppUI->user_id ) || $perms->checkModule( 'admin', 'edit' ) ) ;
if ( $canDelete ) {
	$titleBlock->addCrumbDelete( 'Delete this report', true, true );
}
$titleBlock->show();

require_once $baseDir.'/modules/flexreports/report_functions.php';

if ( $canDelete ) { ?>
<script language="javascript">
function delIt() {
    if ( confirm("<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS) . $AppUI->_('report', UI_OUTPUT_JS) ; ?>") ) {
    document.frmDelete.submit();
    }
}
</script>
<?php } ?>
<script language="javascript">
/*
*	Utility function to fix IE bug
*	@param	tag of the searched elements (for IE only)
*	@param	name of the searched elements
*/
function getCellsByName( tag, name ) {
	var nav=navigator.appName;
	var arr = new Array();
	if (nav == "Microsoft Internet Explorer") {
		var iarr = 0;
		var elem = document.getElementsByTagName(tag);
		for ( i = 0; i < elem.length; i++) {
			att = elem[i].getAttribute("name");
			if (att == name) {
				arr[iarr] = elem[i];
				iarr++;
			}
		}
	} else {
		arr = document.getElementsByName(name);
	}
	return arr;
}

/*
*	Functions used to build a table of selected fields
*/
function MoveField(l1,l2, table) {
// Copy the selected field name in l1 to l2
	if (l1.options.selectedIndex>=0) {
		o=new Option( table+':'+l1.options[l1.options.selectedIndex].text,l1.options[l1.options.selectedIndex].value);
		l2.options[l2.options.length]=o;
	} else {
		alert("<?php echo $AppUI->_('InvalidFieldSelection', UI_OUTPUT_JS); ?>");
		}
	}

function DelField(l) {
//	Delete the selected field name from the list
	if ( l.options.selectedIndex>=0 ) {
		l.options[l.options.selectedIndex]=null;
	} else {
		alert("<?php echo $AppUI->_('InvalidFieldSelection', UI_OUTPUT_JS); ?>");
		}
	}

function UpField(l) {
// Move the selected field name up in the list
	if ( l.options.selectedIndex > 0 ) {
		var index = l.options.selectedIndex ;
		var t = l.options[index-1].text;
		var v = l.options[index-1].value;
		l.options[index-1].text = l.options[index].text;
		l.options[index-1].value = l.options[index].value;
		l.options[index].text = t;
		l.options[index].value = v;
		l.options.selectedIndex = index-1 ;
	} else {
		alert("<?php echo $AppUI->_('InvalidFieldSelection', UI_OUTPUT_JS); ?>");
		}
	}

function DownField(l) {
//	Move the selected field name down in the list
	if ( l.options.selectedIndex < l.options.length-1 && l.options.selectedIndex>=0 ) {
		var index = l.options.selectedIndex ;
		var t = l.options[index].text ;
		var v = l.options[index].value ;
		l.options[index].text = l.options[index+1].text;
		l.options[index].value = l.options[index+1].value;
		l.options[index+1]. text = t;
		l.options[index+1]. value = v;
		l.options.selectedIndex = index+1 ;
	} else {
		alert("<?php echo $AppUI->_('InvalidFieldSelection', UI_OUTPUT_JS); ?>");
		}
	}

/*
*	SubForm functions
*/
var subForm = new Array();

function FormDefinition(id, form, check, save) {
	this.id = id;
	this.form = form;
	this.checkHandler = check;
	this.saveHandler = save;
	this.check = fd_check;
	this.save = fd_save;
}

function fd_check() {
	if (this.checkHandler) {
		return this.checkHandler();
	} else {
		return true;
    }
}

function fd_save() {
	if (this.saveHandler) {
		var copy_list = this.saveHandler();
		return copyForm(this.form, document.editFrm, copy_list );
    }
    return true ;
}

function copyForm(form, to, fields) {
/*
* 	Copy elements in array fields from form 'form' into form 'to'
*		@param	source form
*		@param	target form
*		@param	field array
*/
	var h = new HTMLex;
	for (var i = 0; i < fields.length; i++) {
		for ( var j = 0; j < form.elements.length ; j++) {
			if ( form.elements[j].name == fields[i] ) {
				var elem = form.elements[j];
				switch (elem.type) {
					case 'text':
					case 'textarea':
					case 'hidden':
					// Return value
						to.appendChild(h.addHidden(elem.name, elem.value));
						break;
					case 'select-one':
					case 'select-multiple':
					// Return selected option value
						if ( elem.id == 'list' ) {
							var temp = new Array();
							var count = 0 ;
							for (var k = 0; k < elem.options.length; k++) {
								temp[count] = elem.options[k].value ;
								count++;
                            }
							var valeur = temp.join(',');
							to.appendChild(h.addHidden(elem.name, temp.join(',')));
						} else {
							if (elem.options.length > 0) {
								to.appendChild(h.addHidden(elem.name, elem.options[elem.selectedIndex].value));
                            }
                        }
						break;
					case 'radio':
					case 'checkbox' :
					// Return result as a comma separated list
						var temp = new Array();
						var count = 0 ;
						for ( k=j ; k < form.elements.length ; k++ ) {
						// Read all form elements with the same name
							if ( form.elements[k].name == fields[i] && form.elements[k].checked ) {
								temp[count] = form.elements[k].value ;
								count++ ;
                            }
                        }
						var valeur = temp.join(',');
						to.appendChild(h.addHidden(elem.name, temp.join(',')));
						// skip the rest of the form elements for this field
						j = form.elements.length ;
						break;
                }
            }
            continue ;
        }
    }
	return true;
}

/*
*	Check form and subforms before submit
*/
function editCheck() {
	if ( document.editFrm.report_name.value.length < 4 ) {
		alert ("<?php echo $AppUI->_('InvalidReportName', UI_OUTPUT_JS) ; ?>");
		return false;
    }
	for ( var i=0 ; i<document.editFrm.layout.length; i++ ) {
		if ( document.editFrm.layout[i].checked ) {
			document.editFrm.report_layout.value = i ;
			continue ;
        }
    }
// Store report_type from type select array (this is not done when the select array is disabled)
	document.editFrm.report_type.value = document.editFrm.type.options[document.editFrm.type.options.selectedIndex].value ;
// End form check before submit if report add 1st step
	if ( document.editFrm.add_step.value > 1 ) {
// Check the sub forms
		for ( var i = 0; i < subForm.length; i++ ) {
			if (!subForm[i].check()) {
				return false;
            }
			// Save the subform, this may involve seeding this form  with data
			subForm[i].save();
        }
    }
	if ( confirm ( "<?php echo $AppUI->_('Confirm', UI_OUTPUT_JS) ; ?>"+" ?" ) ) {
		document.editFrm.submit();
    }
	return 
}
/*
*	ae_details check subfunctions
*/
function detailsCheck() {
	if ( document.detailFrm.report_title.value.length <=3 ) {
		alert("<?php echo $AppUI->_('InvalidReportTitle', UI_OUTPUT_JS) ; ?>");
		return false;
    }
	return true ;
}

function detailsSave() {
	fields = new Array( 'report_title',
						'report_datefilter',
						'report_format',
						'report_orientation');
	var f = getCellsByName( "input", "format_list");
	var flist = new Array();
	var count = 0 ;
	for ( var i = 0 ; i <f.length ; i++ ) {
		if ( f[i].checked ) {
			flist[count] = i+1 ;
			count++ ;
        }
    }
	document.detailFrm.report_format.value=flist.join(",");
	if ( document.editFrm.report_type.value == 1 ) {
		i = fields.length ;
		fields[i]= 'company_list' ;
		fields[i+1] = 'project_list' ;
		fields[i+2] = 'user_list';
    }
	return fields ;
}
/*
* 	ae_fields check subfunctions
*/						
function fieldsCheck() {
	var count_fields = document.fieldsFrm.selected_field1.options.length ;
	if ( document.editFrm.report_layout.value > 0 ) {
		count_fields += document.fieldsFrm.selected_field2.options.length ;
    }
	if ( count_fields <= 0 ) {
		if ( ! confirm("<?php echo $AppUI->_('NoFieldSelected', UI_OUTPUT_JS); ?>") ) {
			return false ;
        }
    }
// Check report reference
	if ( document.editFrm.reference.checked ) {
		if ( document.fieldsFrm.user_function.options.selectedIndex <= 0 ) {
			alert( "<?php echo $AppUI->_('InvalidUserFunction', UI_OUTPUT_JS) ; ?>" );
			return false ;
        }
    }
	return true;
}

function fieldsSave() {
	if ( document.editFrm.reference.checked ) {
		document.editFrm.report_reference.value = document.fieldsFrm.user_function.options.selectedIndex ;
	} else {
		document.editFrm.report_reference.value = 0 ;
    }
	fields = new Array( 'selected_field1' );
	if ( document.editFrm.report_layout.value > 0 ) {
		fields[fields.length] = 'selected_field2' ;
    }
	return fields ;
}

function implodeFilter( table, count ) {
	var field = getCellsByName( "input", table+"_field" );
	if ( field.length != parseInt(count) ) { return false }
	var operator = getCellsByName( "input", table+"_operator" );
	if ( operator.length != count ) { return false }
	var value = getCellsByName( "input", table+"_value" );
	if ( operator.length != parseInt(count) ) { return false }
	var mode=0 ;
	if ( table == "filter" ) {
		var label = getCellsByName( "input", table+"_label" );
		if ( label.length != parseInt(count) ) {return false }
		mode=1;
    }
	var output = document.filtersFrm.filter_list.value ;
	for ( var i=0 ; i<count ; i++ ) {
		if ( output.length > 0 ) {
			output += "&&" ;
        }
		output += mode + "|" + field[i].value + "|" + operator[i].value + "|" + value[i].value + "|" ;
		if ( mode == 1 ) {
			output += label[i].value ;
        }
    }
	document.filtersFrm.filter_list.value = output ;
	return true ;
}

function filtersCheck() {
	// reset filters variable (in case of multiple submit + cancel )
	document.filtersFrm.filter_list.value = "" ;
	// Implode condition and filter definition input fields
	// Format : field,operator,value[,label]&&field,operator,value[,label]
	var count = document.filtersFrm.condition_count.value ;
	if ( ! implodeFilter( 'condition', count ) ) {
		alert("An error occurred when saving conditions");
		return false ;
    }
//	alert( "Condition = " + document.filtersFrm.condition_list.value );
	var count = document.filtersFrm.filter_count.value ;
	if ( ! implodeFilter( 'filter', count ) ) {
		alert("An error occurred when saving filters");
		return false ;
    }
//	alert( "Filter = " + document.filtersFrm.filter_list.value );
	return true ;
}

function filtersSave() {
	// copy imploded fields
	filters = new Array ( "filter_list" );
	return filters ;
}
/*
*	ae_options check subfunctions
*/
function optionsCheck() {
	return true;
}

function optionsSave() {
// Retrieve sort fields
	var sort = getCellsByName( "select", "sortFields");
	var desc = getCellsByName( "input", "descending");
	var temp = new Array();
	var count = 0 ;
	for ( var i=0; i<sort.length ; i++ ) {
		if ( sort[i].options[sort[i].options.selectedIndex].value != " " ) {
			temp[count]= sort[i].options[sort[i].options.selectedIndex].value;
			if ( desc[i].checked ) {
				temp[count] = temp[count] + ",DESC" ;
            }
			count++ ;
        }
    }
	if ( count ) {
		document.optionsFrm.report_sortfields.value=temp.join('+');
	} else {
		document.optionsFrm.report_sortfields.value="" ;
    }
//	alert("Sort = " + document.optionsFrm.report_sortfields.value );
//	retrieve all options
	var show = getCellsByName( "input", "showOptions" );
// Special case: if "show parent task" is set then set "show incomplete tasks"
	if ( show[3].value == "1" ) { show[4].value = "1"; }
	temp = "";
	for ( var i=0; i<show.length ; i++ ) {
		temp= temp+show[i].value;
    }
	document.optionsFrm.report_showoptions.value=temp ;
//	alert("Options = " + document.optionsFrm.report_showoptions.value );
//	retrieve user time display option
	if ( getCellsByName( "select", "time_field" ).length ) {
		var u1 = document.optionsFrm.time_field.options[document.optionsFrm.time_field.selectedIndex].value;
		var u2 = document.optionsFrm.time_period.options[document.optionsFrm.time_period.selectedIndex].value;
		var u3 = 0 ;
		if ( document.optionsFrm.time_hide_NWD.checked ) {
			u3 = 1 ;
        }
		if ( u1 == 0 ) {
			document.optionsFrm.report_user_time.value = "";
		}else{
			document.optionsFrm.report_user_time.value = u1 + "," + u2 + "," + u3 ;
        }
    }
//	alert("Display user time = " + document.optionsFrm.report_user_time.value ) ;
	return Array( "report_sortfields", "report_showoptions", "report_user_time" );
}
</script>
<form name="frmDelete" action="?m=flexreports&a=do_report_aed" method="post" >
    <input type="hidden" name="del" value="1" />
    <input type="hidden" name="report_id" value="<?php echo $report_id ; ?>" />
</form>
<?php if ( $add_step ) {
    // Display all tabs for update
    // form action = do_aed (will create/update  report definition)
    ?>
    <form name="editFrm" action="?m=flexreports&a=do_report_aed&report_id=<?php echo $report_id; ?>" method="post" >
        <input type="hidden" name="add_step" value="2" />
<?php } else {
    // Display report structure fields only on first step
    // form action = addedit (will generate tabs with appropriate input fields)
    ?>
    <form name="editFrm" action="?m=flexreports&a=addedit&report_id=<?php echo $report_id; ?>" method="post" >
        <input type="hidden" name="add_step" value="1" />
<?php } ?>
        <input type="hidden" name="report_id" value="<?php echo $report_id;?>" />
        <input type="hidden" name="report_date" value="<?php $date=new CDate() ; echo $date->format(FMT_DATETIME_MYSQL) ;?>" />
        <input type="hidden" name="report_creator" value="<?php echo $report_id ? $report->report_creator : $AppUI->user_id ; ?>" />
        <input type="hidden" name="report_type" value="<?php echo $report_id ? $report->report_type : 0 ; ?>" />
        <input type="hidden" name="report_layout" value="<?php echo $report->report_layout ; ?>" />
        <input type="hidden" name="report_reference" value="<?php echo $report->report_reference ; ?>" />

        <table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">

            <tr>
                <td width="10%" align="right"><?php echo $AppUI->_('Report Name') ; ?>:</td>
                <td width="40%"><input type="text" class="text" name="report_name" size="40" value="<?php echo $report->report_name ; ?>" /></td>
                <td width="10%" align="right" valign="top" rowspan="3"><?php echo $AppUI->_('Description') ; ?>:</td>
                <td width="40%" rowspan="3" valign="top">
                <textarea cols="60" rows="5" name="report_description"><?php echo $report->report_description ; ?></textarea>
                </td>
            </tr>
            <tr>
                <td width="10%" align="right" valign="top"><?php echo $AppUI->_('Report type') ; ?>:</td>
                <td width="40%">
                    <?php
                        echo arraySelect( $report_type_list, 'type', 'class="text" size="1"' . ( $add_step ? ' disabled' : "" ), $report->report_type, true );
                    ?>
                </td>
            </tr>
            <tr>
                <td width="10%" align="right" valign="top"><?php echo $AppUI->_('Reporting on users'); ?>:</td>
                <td width="40%">
                <?php
                $checked =  ( ( $add_step && w2PgetParam( $_POST, 'reference', '') ) || $report->report_reference ) ? "checked" : "" ;
                ?>
                    <input type="checkbox" name="reference" <?php echo $checked ; ?> <?php echo $add_step ? "disabled" : "" ; ?> /><br>
                </td>
            </tr>
            <tr>
                <td width="10%" align="right" valign="top"><?php echo $AppUI->_('Report layout') ; ?>:</td>
                <td width="40%">
                <?php
                for ( $i=0 ; $i<count($report_layout_list) ;  $i++ )
                    {
                    $layout = $report_layout_list[$i];
                    $checked = $report->report_layout == $i ? "checked" :"" ;
                    ?>
                    <input type="radio" name="layout" <?php echo $checked ; ?> <?php echo $add_step ? "disabled" : "" ; ?> /><?php echo $AppUI->_($layout) ; ?><br>
                    <?php
                    }
                    ?>
                </td>
            </tr>
            <?php if ( $perms->checkModule( 'admin', 'edit' ) ) { ?>
            <tr>
                <td width="10%" align="right"><?php echo $AppUI->_('PHP code'); ?>:</td>
                <td colspan="3">
                    <input type="text" class="text" size="40" name="report_code" value="<?php echo $report->report_code ; ?>" />
                    &nbsp;(<?php echo $AppUI->_('Use only for non standard reports'); ?>)
                </td>
            </tr>
            <?php } ?>
            <tr>
                <td>
                    <input type="button" class="button" name="cancel" value="<?php echo $AppUI->_('Cancel');?>" onClick="javascript:if ( confirm('<?php echo $AppUI->_("Cancel ?") ; ?>')) {location.href='?<?php echo $cancel ; ?>'}" />
                </td>
                <td colspan="3" align="right">
                    <input type="button" class="button" name="submit" value="<?php echo $add_step ? $AppUI->_('Submit') : $AppUI->_('Continue') ; ?>" onClick="javacript:editCheck()" />
                </td>
            </tr>
        </table>
    </form>

<?php
// Create tabs

if ( $add_step ) {
	$tabBox =& new CTabBox('?m=flexreports&amp;a=addedit&amp;report_id='.$report_id, '', $tab, '');
	$tabBox->add($w2Pconfig['root_dir'].'/modules/flexreports/ae_details', 'Details');
	$tabBox->add($w2Pconfig['root_dir'].'/modules/flexreports/ae_fields', 'Fields');
	$tabBox->add($w2Pconfig['root_dir'].'/modules/flexreports/ae_filters', 'Filters');
	$tabBox->add($w2Pconfig['root_dir'].'/modules/flexreports/ae_options', 'Options');
	$tabBox->show('', true);
}