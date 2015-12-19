<?php
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

$selected_field1 = array();
$selected_field2 = array();
foreach ( $selected_fields as $sf ) {
	if ($sf['report_field_rank'] < 100 ) {
		$selected_field1[] = $sf ;
    } else {
		$selected_field2[] = $sf ;
    }
}
// Number of lines for each queried record
$nlines = max( count( $selected_field1), count($selected_field2)) ;

echo "<tr><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th><th>&nbsp;</th></tr>" ;

foreach ( $query_list as $item ) {
	for( $i = 0; $i < $nlines ; $i++ ) {
		echo "<tr>\n";
		if ( isset( $selected_field1[$i] ) ) {
			$sf = $selected_field1[$i];
			$row = $field_desc[$sf['report_field_table']]['field_list'][$sf['report_field_name']];
			$title_string1 = strEzPdf( $AppUI->_( $sf['report_field_name'], UI_OUTPUT_RAW )) . ": ";
			$field_string = strfield( $sf['report_field_table'], $sf['report_field_name'], $item ) ;
			$field_string1 = strEzPdf( $field_string );
			echo "<td width=\"15%\" align=\"right\" valign=\"top\">" .  $AppUI->_( $sf['report_field_name'] ) . "&nbsp;:&nbsp;</td>\n" ;
			echo "<td width=\"". round(100*$row[2]/750) . "%\" align=\"". $row[3] . "\" valign=\"top\">" . $field_string . "</td>\n" ;
        } else {
			$title_string1 = "";
			$field_string1 = "";
			echo "<td colspan=\"2\">&nbsp;</td>" ;
        }
		if ( isset( $selected_field2[$i] ) ) {
			$sf = $selected_field2[$i];
			$row = $field_desc[$sf['report_field_table']]['field_list'][$sf['report_field_name']];
			$title_string2 = strEzPdf( $AppUI->_( $sf['report_field_name'], UI_OUTPUT_RAW )) . ": ";
			$field_string = strfield( $sf['report_field_table'], $sf['report_field_name'], $item ) ;
			$field_string2 = strEzPdf( $field_string );
			echo "<td width=\"15%\" align=\"right\" valign=\"top\">" .  $AppUI->_( $sf['report_field_name'] ) . "&nbsp;:&nbsp;</td>\n" ;
			echo "<td width=\"". round(100*$row[2]/750) . "%\" align=\"". $row[3] . "\" valign=\"top\">" . $field_string . "</td>\n" ;
        } else {
			$title_string2 = "";
			$field_string2 = "";
			echo "<td colspan=\"2\">&nbsp;</td>" ;
        }
		echo "</tr>\n" ;
		$pdfdata[] = array ( $title_string1, $field_string1, $title_string2, $field_string2 );
		$csvcolumns[] = array ( $title_string1, $field_string1, $title_string2, $field_string2 );
    }
	echo "<tr>";
	$pdfdata[] = array( '', '', '', '');
	echo "<td colspan=\"4\"><hr width=\"100%\" size=\"1\"></tr>";
}