<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

$form_submited = w2PgetParam($_POST, "form_submited", 0);
if ( $form_submited )
{
	$returnfield = w2PgetParam($_POST, "returnfield", "");
	$selected_id = w2PgetParam($_POST, "selected_id", 0);
	$selected_name = w2PgetParam( $_POST, "selected_name", '');
	$call_back = w2PgetParam($_POST, "callback", "");
	$call_back_string = "window.opener.$call_back('$returnfield', '$selected_id', '$selected_name');" ;
	?>
	<script language="javascript">
	<?php echo $call_back_string ?>
	self.close();
	</script>
	<?php
} else {
	$fieldtable = w2PgetParam($_REQUEST, "fieldtable", "");
	$fieldname = w2Pgetparam($_REQUEST, "fieldname", "");
	$returnfield = w2PgetParam($_REQUEST, "returnfield", "");
	$selected_id = w2PgetParam($_REQUEST, "selected_id", "");
	$call_back = w2PgetParam($_REQUEST, "callback", "");
	$show_default = w2PgetParam($_REQUEST, "showdefault", 0);
	$selected_list = explode(',', $selected_id );
	$select_list = array();
	$multiple_select = 1 ;
	if ( preg_match( "/\{([A-Za-z]+)\}/", $fieldtable, $matches ) ) {
		$select_list = w2PgetSysVal ( $matches[1] );
		$title = $AppUI->_("System values");
		$show_default = 0 ;
		$translate = 1 ;
	} else {
		require_once( w2PgetConfig( 'root_dir' )."/modules/flexreports/report_functions.php" );
		if ( !$fieldtable ) {
			// predefined date period selection
			$select_list= $date_filter_list;
			$title = $AppUI->_('Date');
			$multiple_select = 0 ;
			$show_default = 0 ;
		} else {
			$select_list = getRecordNames( $fieldtable, $fieldname );
			$title = $fieldtable ;
			$translate = 0 ;
			}
		}
	if ( count($select_list)== 0 ) {
		$call_back_string = "window.opener.$call_back('$returnfield', '', '');" ;
		?>
		<script language="javascript">
		<?php echo $call_back_string ?>
		self.close();
		</script>
		<?php
		}
	?>
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

	function setSingleID(index) {
		var field = getCellsByName("input", "select_id_item");
		document.frmSelect.selected_id.value = field[index].value;
		var name = getCellsByName("td", "select_name_item");
		document.frmSelect.selected_name.value = name[index].firstChild.nodeValue; ;
		document.frmSelect.form_submited.value = "1";
	 	document.frmSelect.submit();
		}
	// Similar to modules/public/contact_selector.php
	function setMultipleIDs() {
		var field = getCellsByName("input", "select_id_item");
		var name = getCellsByName("td", "select_name_item");
		var selected_id = document.frmSelect.selected_id;
		var tmp = new Array();
		var tmp2 = new Array();
		var tmp3 = new Array();
		tmp = selected_id.value.split(",");
		// We copy the values of tmp to tmp2, using
		// the value of tmp as an indice for tmp2, therefore
		// we can later on easily check if a checked field exists
		// we do not use the associative Array hack here, because
		// then methods like tmp2.length would not work.
		for (i = 0; i < tmp.length; i++) {
			tmp2[tmp[i]] = tmp[i];
			}
		for (i = 0; i < field.length; i++) {
			if (field[i].checked == true) {
				if (!tmp2[field[i].value]) {
					tmp2[field[i].value] = field[i].value;
					tmp3[field[i].value] = name[i].firstChild.nodeValue;
					}
			} else {
				if (tmp2[field[i].value]) {
					delete tmp2[field[i].value];
					}
				}
			}
		tmp = new Array();
		var count = 0;
		for (i = 0; i < tmp2.length; i++) {
			if (tmp2[i]) {
				tmp[count] = tmp2[i];
				count++;
				}
			}
		document.frmSelect.selected_id.value = tmp.join(',');
		tmp = new Array();
		count = 0 ;
		for ( i = 0 ; i < tmp2.length ; i++ ) {
			if (tmp2[i]) {
				tmp[count] = tmp3[i];
				count++;
				}
			}
		document.frmSelect.selected_name.value = tmp.join(',');
		document.frmSelect.form_submited.value = "1";
	 	document.frmSelect.submit();
		}
	</script>
	
	<form name="frmSelect" action="index.php?m=flexreports&a=selector&suppressHeaders=1" method="post">
	<input type="hidden" name="returnfield" value="<?php echo $returnfield ; ?>" />
	<input type="hidden" name="selected_id" value="<?php echo $selected_id ; ?>" />
	<input type="hidden" name="selected_name" value="" />
	<input type="hidden" name="callback" value="<?php echo $call_back ; ?>" />
	<input type="hidden" name="form_submited" value="0" />
	<h4><?php echo $AppUI->_('Select'). ' ' . $title ; ?></h4>
	<table cellpadding="4" cellspacing="0" border="0" width="100%" class="hilite">
	<?php
		if ( $show_default ) {
			if ( $title == 'users') {
				$text = $AppUI->_('Current user');
			} else {
				$text = $AppUI->_('My') . ' ' . $AppUI->_($fieldtable) ;
				}
			echo "\n<tr><td width=\"10%\">";
			echo "\n<input type=\"checkbox\" name=\"select_id_item\" value=\"0\" onChange=\"javascript:if ( this.checked ) {setSingleID(0) }\"/>";
			echo "\n</td><td width=\"90%\" name=\"select_name_item\">";
			echo addslashes($text) ;
			echo "</td>\n</tr>";
			}
		$line = $show_default? 1 : 0 ;
		foreach($select_list as $key_id => $key_data) {
			$options = $multiple_select ? "" : "onClick=\"javascript:setSingleID(" . $line . ")\" " ;
			$options .= $selected_id && in_array($key_id, $selected_list) ? "checked" : "";
			echo "\n<tr><td width=\"10%\">" ;
			echo "\n<input type=\"checkbox\" name=\"select_id_item\" value=\"$key_id\" $options />";
			echo "\n</td><td width=\"90%\" name=\"select_name_item\">";
			echo addslashes( $translate ? $AppUI->_($key_data) : $key_data ) ;
			echo "</td>\n</tr>";
			$line++;
			}
	?>
	</table>
	<hr />
	<?php
	if ( $multiple_select ) {
		?>
		<input type='submit' class='button' value='<?php echo $AppUI->_("Confirm"); ?>' onClick="setMultipleIDs()"  />
		<?php
		}
		?>
	</form>
	<?php 	
}