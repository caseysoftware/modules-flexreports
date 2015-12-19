<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

$width = 0 ;
$ncols = count($selected_fields) ;
foreach ( $selected_fields as $sf ) {
	$width += $field_desc[$sf['report_field_table']]['field_list'][$sf['report_field_name']][2] ;
}
if ( $show_period ) {
	$array_header = buildArrayHeader( $period_start_date, $period_end_date , $hideNonWorkingDays );
	$width += 25*(count($array_headers)-1) ;
	$ncols += count($array_header)-1 ;
}
/*
 *	Display table headers
 */
echo "<tr>\n";
foreach ( $selected_fields as $sf ) {
	$row = $field_desc[$sf['report_field_table']]['field_list'][$sf['report_field_name']];
	echo "<th width=\"". round(100*$row[2]/$width) . "%\" align=\"". $row[3] . "\">" . $AppUI->_( $sf['report_field_name'] ) . "</th>\n" ;
	$pdfcolumns[] = "<b>" . strEzPdf( $AppUI->_( $sf['report_field_name'], UI_OUTPUT_RAW )) . "</b>" ;
	$csvcolumns[] = strEzPdf( $AppUI->_( $sf['report_field_name'], UI_OUTPUT_RAW ));
}
if ( $show_period ) {
	for ( $i=0 ; $i<count($array_header)-1 ; $i++ ) {
		$str_date = $array_header[$i]->format($df);
		echo "<th width=\"". round(2500/$width) . "%\" align=\"center\">" . $str_date . "</th>\n" ;
		$pdfcolumns[] = "<b>" . strEzPdf( $str_date, UI_OUTPUT_RAW ) . "</b>" ;
		$csvcolumns[] = strEzPdf( $str_date, UI_OUTPUT_RAW );
		$array_header[$i] = $str_date ;
    }
}
echo "</tr>\n" ;

/*
* 	Display loop
*/

$group_name = "" ;
$level2_name = "";
$table_break = array();
$show_subtitle = ( $nc = strpos( $show_subtitle, '|')) ? substr( $show_subtitle, $nc+1 ) : $show_subtitle ;
$show_level2 = ( $nc = strpos($show_level2, '|')) ? substr( $show_level2, $nc+1 ) : $show_level2 ;
foreach ( $query_list as $item ) {
	$str =  "<tr>\n";
	$pdfline = array();
	if ( $show_subtitle && $group_name != $item[$show_subtitle] ) {
		$field_string = strfield( $show_table, $show_name, $item ) ;
		$str .= "<td colspan=\"" . $ncols ."\"><strong>" . $field_string . "</strong></td>\n</tr>";
		echo $str ;
		$str =  "<tr>\n";
		$pdfline[] = strEzPdf( $field_string ) ;
		$table_break[] = count($pdfdata);
		for ( $i=1 ; $i<$ncols; $i++ ) {
			$pdfline[]="";
        }
		$pdfdata[] = $pdfline ;
		$group_name = $item[$show_subtitle];
		$level2_name = "" ;
		$pdfline = array();
    }
/*
* 	Field display loop
*/
	foreach ( $selected_fields as $sf ) {
		$row = $field_desc[$sf['report_field_table']]['field_list'][$sf['report_field_name']];
		if ( $show_level2 && $row[0] == $show_level2 && $item[$show_level2] == $level2_name ) {
			$field_string = "" ;
		} else {
			$field_string = strfield( $sf['report_field_table'], $sf['report_field_name'], $item ) ;
			$level2_name = $item[$show_level2] ;
        }
		$str .= "<td align=\"". $row[3] . "\" valign=\"top\" >" . $field_string ."</td>\n";
		$pdfline[] = strEzPdf( $field_string ) ;
    }
	if ( $show_period ) {
		for ( $i=0 ; $i<count($array_header)-1 ; $i++ ) {
			$field_string = $show_days ? strrounddays((float)$item[$array_header[$i]]/$w2Pconfig['daily_working_hours']) : number_format( $item[$array_header[$i]] ) ;
			$str .= "<td align=\"center\" valign=\"top\" >" . $field_string ."</td>\n";
			$pdfline[] = strEzPdf( $field_string ) ;
        }
    }
/*
* 	End of field display loop
*/
	$str .= "</tr>\n";
	echo $str;
	$pdfdata[] = $pdfline ;
/*
* 	End of display loop	
*/
}
$table_break[]=count($pdfdata);
echo "</table>";