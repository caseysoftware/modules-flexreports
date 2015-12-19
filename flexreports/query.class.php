<?php
/**
 * Class CReportQuery
 */
class CFlexReport_Query extends w2p_Database_Query {
    public $field_list = NULL;
    public $join_list = NULL;
    public $debug = NULL;

    /**
     * @param int $project_id
     * @param bool $debug
     */
    public function __construct( $project_id=0, $debug=false ) {
        parent::__construct();
        $this->field_list = array();
        $this->join_list = array();
        $this->project_id = $project_id;
        $this->debug = $debug ;
    }

    /**
     * @param $table
     */
    public function addFromClause ( $table ) {
        global $field_desc, $indirection_table, $AppUI;
        if ( $this->debug ) { echo "Start addFromClause( $table )<br>"; }
        $join_key = $field_desc[$table]['join_key'] ;
        $field = $field_desc[$table]['id_field'] ;
        $this->addTable( $table, $join_key );
        $this->join_list[$table]=$table;
        switch ( $table ) {
            case 'companies' :
                $object = new CCompany();
                $allowed_objects = $object->getAllowedRecords( $AppUI->user_id, 'company_id, company_name' );
                if ( count( $allowed_objects )) {
                    $allowed_list = implode( ',', array_keys( $allowed_objects ) );
                    $this->addWhere( "$join_key.$field IN ($allowed_list)" );
                    if ( $this->debug ) echo "<br>\t WHERE $join_key.$field IN ($allowed_list) <br>";
                } else {
                    $this->addWhere( "1=0" );
                }
                break;
            case 'projects' :
                if ( $this->project_id ) {
                    $this->addWhere( "$join_key.$field = " . $this->project_id );
                } else {
                    $object = new CProject();
                    $allowed_objects = $object->getAllowedRecords( $AppUI->user_id, 'project_id, project_name' );
                    if ( count( $allowed_objects ) ) {
                        $allowed_list = implode( ',', array_keys( $allowed_objects ) );
                        $this->addWhere( "$join_key.$field IN ($allowed_list)" );
                        if ( $this->debug ) echo "<br>\t WHERE $join_key.project_id IN ($allowed_list) <br>";
                    } else {
                        $this->addWhere( "1=0" );
                    }
                }
                break;
            default :
                break;
        }
    }

    /**
     * @param $table
     * @param $name
     * @param $join_flag
     * @param int $flag
     */
    public function addQueryField( $table, $name, $join_flag, $flag=0 ) {
        global $field_desc, $indirection_table ;
        if ( $this->debug ) echo "Start addQueryField( $table, $name, $join_flag, $flag ) <br>";
        if ( ! $flag ) {
            $row = $field_desc[$table]['field_list'][$name];
            if ( $nc = strpos($row[0], '|') ) {
                $ref_table = $table;
                $join_table = substr($row[0], 0 , $nc );
                $column = substr($row[0], $nc+1 );
                $join_key = $field_desc[$join_table] ? $field_desc[$join_table]['join_key'] : $indirection_table[$join_table]['join_key'] ;
            } else {
                $ref_table = "";
                $join_table = $table ;
                $join_key = $field_desc[$table]['join_key'];
                $column = $row[0];
            }
            if ( $row[4] == 0 && $row[5] ) {
                if ( $debug ) echo "Processing composite field " . $row[5] . "<br>" ;
                $string = substr( $row[5], 0 );
                $fc = strlen( $string );
                while ( $fc ) {
                    $fc = strpos( $string, ',');
                    $nc = strpos( $string, ':');
                    $tbl = substr( $string, 0, $nc );
                    $length = $fc ? $fc-$nc-1 : strlen($string)-$nc-1 ;
                    $fld = substr( $string, $nc+1, $length );
                    $this->addQueryField( $tbl, $fld, $join_flag, 1 );
                    $string = substr( $string, $fc+1 );
                }
                return ;
            }
        } else {
            $join_key = $field_desc[$table] ? $field_desc[$table]['join_key'] : $indirection_table[$table]['join_key'];
            $column = $name ;
        }
        if ( $this->debug ) echo "Generated query field = $join_key.$column <br>";
        // Check if the field is already included in the list
        if ( $this->field_list["$join_key.$column"] ) {
            return ;
        }
        // Create appropriate JOIN clause if needed
        if ( ! $this->join_list[$join_table] ) {
            $this->addJoinClause( $join_table, '', $ref_table, '', $join_flag );
        }
        $this->addQuery("$join_key.$column");
        $this->field_list["$join_key.$column"] = 1 ;
        if ( $this->debug ) echo "Added query field = $join_key.$column <br>";
        return ;
    }

    /**
     * @param string $j_table
     * @param string $j_field
     * @param string $r_table
     * @param string $r_field
     * @param int $flag
     * @return string
     */
    public function addJoinClause ( $j_table='', $j_field='', $r_table='', $r_field='', $flag=0 ) {
        global $field_desc, $indirection_table, $AppUI;
        // Create the first JOIN clause based on parameters
        if ( $this->debug ) {
            echo "Start addJoinClause( $j_table, $j_field, $r_table, $r_field )<br>";
            echo "Join list = ";
            print_r ( $this->join_list );
            echo"<br>";
        }
        if ( ! $r_table ) {
            // Let's use field_desc join table description recursively until it is NULL
            // Used in view.php to genetrate JOIN clauses for the displayed fields
            $join_table = $j_table ;
            if ( $field_desc[$join_table]['join_table'] ) {
                if ( $this->debug ) echo "Recursive Call = " . $field_desc[$join_table]['join_table'] . "<br>";
                $root_table = $this->addJoinClause( $field_desc[$join_table]['join_table'], '', '', '', 1 );
                $ref_table = $field_desc[$join_table]['join_table'];
                $join_field = $field_desc[$join_table]['join_field'];
            } else {
                if ( $this->debug ) echo "End recursive call chain - return = " . $join_table ."<br>";
                return $join_table ;
            }
        } else {
            // Usual case : the reference table is defined in the call (used in getReportNames)
            $ref_table = $r_table ;
            $join_table = $j_table ? $j_table : $field_desc[$ref_table]['join_table'];
        }
        if ( $field_desc[$ref_table] ) {
            // Standard database table
            $ref_key = $field_desc[$ref_table]['join_key'];
            $ref_field = $r_field ? $r_field : $field_desc[$ref_table]['id_field'];
        } else {
            // Indirection table - Check which leg of the indirection we are using
            $ref_key = $indirection_table[$ref_table]['join_key'];
            $ref_field = $r_field ? $r_field
                : ( $indirection_table[$ref_table]['right']['table'] == $join_table ? $indirection_table[$ref_table]['right']['field']
                    : $indirection_table[$ref_table]['left']['field'] ) ;
        }
        if ( $field_desc[$join_table] ) {
            // Standard database table
            $join_key = $field_desc[$join_table]['join_key'];
            $join_field = $join_field? $join_field : ( $j_field ? $j_field : $field_desc[$join_table]['id_field'] );
            $linked_table = $field_desc[$join_table]['join_table'];
            $linked_field = $field_desc[$join_table]['join_field'];
        } else {
            // Indirection table - Check which leg of the indirection we are using - Set linked_table, linked_field to add the other leg
            $join_key = $indirection_table[$join_table]['join_key'];
            if ( $indirection_table[$join_table]['right']['table'] == $ref_table ) {
                $join_field = $indirection_table[$join_table]['right']['field'];
                $linked_table = $indirection_table[$join_table]['left']['table'];
                $linked_field = $indirection_table[$join_table]['left']['field'];
            } else {
                $join_field =$indirection_table[$join_table]['left']['field'];
                $linked_table = $indirection_table[$join_table]['right']['table'];
                $linked_field = $indirection_table[$join_table]['right']['field'];
            }
        }
        if (  ! $this->join_list[$join_table] ) {
            $this->addJoin( $join_table, $join_key, "$ref_key.$ref_field = $join_key.$join_field" );
            if ( $this->debug ) echo "<br>\tJOIN $join_table AS $join_key ON $ref_key.$ref_field = $join_key.$join_field<br>";
            $this->join_list[$join_table] = $ref_table;
            // Include WHERE clause for allowed records
            switch ( $join_table ) {
                case 'companies' :
                    $object = new CCompany();
                    $allowed_objects = $object->getAllowedRecords( $AppUI->user_id, 'company_id, company_name' );
                    if ( count( $allowed_objects )) {
                        $allowed_list = implode( ',', array_keys( $allowed_objects ) );
                        $this->addWhere( "$join_key.company_id IN ($allowed_list)" );
                        if ( $this->debug ) echo "<br>\t WHERE $join_key.company_id IN ($allowed_list) <br>";
                    }
                    break;
                case 'projects' :
                    if ( $this->project_id ) {
                        $this->addWhere( "$join_key.project_id = " . $this->project_id );
                    } else {
                        $object = new CProject();
                        $allowed_objects = $object->getAllowedRecords( $AppUI->user_id, 'projects.project_id, project_name' );
                        if ( count( $allowed_objects )) {
                            $allowed_list = implode( ',', array_keys( $allowed_objects ) );
                            $this->addWhere( "$join_key.project_id IN ($allowed_list)" );
                            if ( $this->debug ) echo "<br>\t WHERE $join_key.project_id IN ($allowed_list) <br>";
                        }
                    }
                    break;
                case 'tasks' :
                    $object = new CTask();
                    $denied_tasks = $object->getDeniedRecords( $AppUI->user_id, 'task_id, task_name' );
                    if ( count($denied_tasks) ) {
                        $denied_list = implode ( ',', array_keys($denied_tasks) ) ;
                        $query->addWhere( "$join_key.task_id NOT IN ($denied_list)" );
                        if ( $this->debug ) echo "<br>\t WHERE $join_key.task_id NOT IN ($denied_list) <br>";
                    }
                default :
                    break;
            }
        } else {
            if ( $this->join_list[$join_table] != $ref_table ) {
                // Not sure that this branch can be reached - An alert message is set just in case....
                $this->addWhere("$ref_key.$ref_field = $join_key.$join_field");
                $AppUI->setMsg("MultipleJoin", UI_MSG_ALERT);
                if ( $this->debug ) echo "<br>\t WHERE $ref_key.$ref_field = $join_key.$join_field <br>";
            }
        }
        if ( $this->debug ) echo "Linked table = $linked_table// R_table = $r_table//<br>";
        if ( $flag == 0 ) {
            // exclude NULL records in the first JOIN ($flag == 0)
            $this->addWhere( "$join_key.$join_field IS NOT NULL" );
            if ( $this->debug ) echo "<br>\tWHERE ( $join_key.$join_field IS NOT NULL )<br>";
        }
        if ( $linked_table && $r_table ) {
            // If $r_table is not set, the linked_table branch is already included in the recursive JOINs
            if ( $this->debug ) echo "Call addJoinClause for linked_table ( $linked_table, $linked_field, $join_table, , 1 )<br>";
            $root_table = $this->addJoinClause( $linked_table,  '', $join_table, $linked_field, 1 );
        }
        return $r_table ? $r_table : $root_table ;
    }

    /**
     * @param $table
     * @param $name
     * @param $operator
     * @param $value
     * @return bool
     */
    public function addWhereClause( $table, $name, $operator, $value ) {
        global $field_desc, $operator_list, $indirection_table, $AppUI ;

        $row = $field_desc[$table]['field_list'][$name];
        /*
        *    Let's create the appropriate column name depending on filter type
        *     Add JOIN clauses if required
        */
        if ( $this->debug ) echo "Start addWhereClause ( $table, $name, $operator, $value ) <br>";
        if ( $row[4] < 7 ) {
            $nc = strpos( $row[0], '|');
            $join_table = $nc ? substr($row[0], 0, $nc ) : $table ;
            $join_key = $field_desc[$join_table] ? $field_desc[$join_table]['join_key'] : $indirection_table[$join_table]['join_key'];
            $query_field = $nc ? substr( $row[0], $nc+1 ) : $row[0] ;
            if ( ! $this->join_list[$join_table] ) {
                $this->addJoinClause( $join_table, '', $table );
            }
        } else {
            // add Join clause through table given in $row[5]
            // $row[5] structure = <join table>|<table of column to be used to retrieve names>
            // retrieve indirection table name
            $nc = strpos( $row[5], '|');
            $indirect_table = substr( $row[5], 0, $nc );
            $join = $indirection_table[$indirect_table];
            $join_key = $join['join_key'];
            $join_table = $join['left']['table'];
            if ( ! $this->join_list[$indirect_table] ) {
                $this->addJoinClause( $indirect_table, '', $join_table );
            }
            if ( ! $this->join_list[$join_table] ) {
                $this->addJoinClause( $join_table );
            }
            // Generate column name for WHERE clause
            $query_field = $join['right']['field'];
        }
        $column = "$join_key.$query_field" ;
        if ( $this->debug ) echo "Generated column name = $column <br>";
        /*
        *    Let's create appropriate 'value' to be compared depending on filter type
        */
        if ( $row[4] == 5 ) {
            $date = new CDate();
            if ( substr( $value, 0, 1) == 'N') {
                // if NOW or Next period set time to end of day
                $date->setTime( 23, 59, 59 );
            } else {
                // if Previous period set time to start of day
                $date->setTime( 0, 0, 0 );
            }
            switch ( $value ) {
                case 'PQ' : $date->addMonths(-3);    // Previous Quarter
                    break;
                case 'PM' : $date->addMonths(-1);    // Previous Month
                    break;
                case 'PF' : $date->addDays(-14);    // Previous 2 weeks (Fortnight)
                    break;
                case 'PW' : $date->addDays(-7);        // Previous week
                    break;
                case 'PD' : $date->addDays(-1);        // previous Day (yesterday)
                    break;
                case 'NOW': break;
                case 'ND' : $date->addDays(1);        // Next day (tomorrow)
                    break;
                case 'NW' : $date->addDays(7);        // Next Week
                    break;
                case 'NF' : $date->addDays(14);        // Next 2 weeks (Fortnight)
                    break;
                case 'NM' : $date->addMonths(1);    // Next Month
                    break;
                case 'NQ' : $date->addMonths(3);    // Next Quarter
                    break;
                default      : return ;
            }
            $value = "'" . $date->format(FMT_DATETIME_MYSQL) . "'";
        } else {
            if ( preg_match( "/\{([A-Z]+)\}/", $value, $matches )) {
                // Predefined values
                switch ( $matches[1] ) {
                    case 'NOW' :
                        $date = new CDate();
                        $value = "'" . $date->format(FMT_DATETIME_MYSQL) . "'";
                        break;
                    case 'USER' :
                        $value = $AppUI->user_id ;
                        break;
                    case 'MYCOMPANY' :
                    case 'MYPROJECTS' :
                    case 'MYTASKS' :
                        // Not implemented : user's company/projects/tasks
                    default : return ;
                }
            }
        }
        /*
        *    Let's create the appropriate where clause depending on operator
        */
        $where = '';
        $value = $row[4] == 2 ? "'".$value."'" : $value;
        switch ( $operator ) {
            case 0 : return;         // not used as filter - should have been detected before...
            case 1 :                 // operator = equals
                $where = $column . ' = ' . $value ;
                break;
            case 2 :                 // operator = not equal
                $where = $column . ' != ' . $value ;
                break;
            case 3 :                 // operator = greater than
                $where = $column . ' > ' . $value ;
                break;
            case 4 :                 // operator = greater than or equals
                $where = $column . ' >= ' . $value ;
                break;
            case 5 :                 // operator = less than
                $where = $column . ' < ' . $value ;
                break;
            case 6 :                 // operator = less than or equals
                $where = $column . ' <= ' . $value ;
                break;
            case 7 :                // operator = contains
                $where = $column . ' LIKE "%' . $value .'%"' ;
                break;
            case 8 :                // operator = does not contain
                $where = $column . ' NOT LIKE "%' . $value .'%"' ;
                break;
            case 9 :                // operator = start with
                $where = $column . ' LIKE "' . $value .'%"' ;
                break;
            case 10 :                // operator = equals (list of values)
                $where = $column . ' IN (' . $value .')' ;
                break;
            case 11 :                // operator = not equal (list of values))
                $where = $column . ' NOT IN (' . $value .')' ;
                break;
            case 12 :                // operator = is set for flag
                $where = $column . ' = ' . $value ;
                break;
            case 13 :                // operator = is not set for flag
                $where = $column . ' = 0 ' ;
                break;
            case 14 :                // is assigned (special case for tasks assignee)
                $where = $column . ' IS NOT NULL' ;
                break;
            case 15 :                // is not assigned
                $where = $column . ' IS NULL' ;
                break;
            default :
                return;
        }
        $this->addWhere( $where );

        if ( $this->debug ) echo "WHERE $where <br>" ;
        return true ;
    }
}