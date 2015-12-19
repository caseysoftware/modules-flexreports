<?php 

define('PUBLIC_REPORT',        '0');
define('RESTRICTED_REPORT',    '1');
define('PRIVATE_REPORT',    '2');
define('ADMIN_REPORT',        '3');
define('COMPANY_RESTRICTED','0');
define('PROJECT_RESTRICTED','1');
define('USER_RESTRICTED',    '2');

##
## CFlexReport Class
##

class CFlexReport extends w2p_Core_BaseObject
{
    public $report_id = NULL;                    // Unique Id
    public $report_name = NULL;                    // Report name (used in report list)
    public $report_creator = NULL;                // User Id of report creator
    public $report_title = NULL;                // Title of the report (used un pdf report heading)
    public $report_date = NULL;                    // Creation/last modification date
    public $report_description = NULL;            // Description of report content and usage
    public $report_type = NULL;                    // Access control type
    public $report_reference = NULL;            // Table used as reference for retrieving report data
    public $report_datefilter = NULL;            // Date filter DB column reference
    public $report_format = NULL;                // Available file formats (none, pdf, CSV)
    public $report_layout = NULL;                // 0=tabular; 1=columnar (number of columns?)
    public $report_orientation = NULL;            // 0=landscape (default), 1=portrait
    public $report_sortfields = NULL;            // comma separated list of sort fields
    public $report_showoptions = NULL;            // comma separated list of display options group names, tasks stats, Gantt chart
    public $report_code = NULL;                    // PHP file name (compatibility with previous reports)
    public $report_user_time;                    // user time field (allocated/assigned time) and predefined period code

    public function __construct() {
        parent::__construct('flexreports', 'report_id');
    }

    public function hook_postDelete()
    {
        $q = $this->_getQuery();
        // Delete report field records
        $q->setDelete('flexreport_fields');
        $q->addWhere('report_field_report = ' . $this->report_id);
        $q->exec();
        // Delete report filter records
        $q->clear();
        $q->setDelete('flexreport_filters');
        $q->addWhere('report_filter_report = ' . $this->report_id);
        $q->exec();
        // Delete report access records
        $q->clear();
        $q->setDelete('flexreport_access');
        $q->addWhere('report_access_report = ' . $this->report_id);
        $q->exec();

        parent::hook_postDelete();
    }

    /**
     * @param $uid
     * @param string $search
     * @param int $pid
     * @param int $cid
     * @return array|Indexed
     */
    public function getAllowedReportId( $uid, $search='all', $pid=0, $cid=0 ) {
        global $AppUI;

        if ( !$uid ) {
            return array() ;
        }
        if ( $search == 'company' && !$cid ) {
            $Ocpy = new CCompany();
            $Acpies = $Ocpy->getAllowedRecords( $uid, 'company_id');
            if ( count( $Acpies ) ) {
                $cid = implode(',', array_keys($Acpies) );
            } else {
                return array();
            }
        }
        if ( $search == 'project' ) {
            if ( $pid ) {
                $project = new CProject();
                $project->load( $pid );
                $cid = $project->project_company;
            } else {
                $project = new CProject();
                $projects = $project->getAllowedRecords( $uid, 'projects.project_id, project_company' );
                if ( count( $projects ) ) {
                    $pid = implode(',', array_keys($projects) );
                    $cid = implode(',', $projects);
                } else {
                    return array();
                }
            }
        }
        if ( $search == 'user' ) {
            $q = new w2p_Database_Query();
            $q->addTable('users');
            $q->addQuery('c.contact_company');
            $q->addJoin('contacts', 'c', 'c.contact_id = user_contact');
            $q->addWhere('user_id = '. $uid );
            $cid = $q->loadResult();
            $cid = $cid ? $cid : 0 ;
        }

//    Build Query
        $q = new w2p_Database_Query();
        $q->addTable('flexreports');
        $q->addQuery('DISTINCT report_id');
//    Build where clause
        $where = "";
        if ( $search == 'all' || $search == 'public' || $search == 'user' ) {
            $where .= "( report_type = ". PUBLIC_REPORT . " )";
        }
        // Company specific reports
        if ( $search == 'all' || $search == 'company' || $search == 'project' || $search == 'user' ) {
            if ( $search == 'all' ) {
                $company = new CCompany ();
                $company_list = $company->getAllowedRecords($uid, "company_id");
                if (count($company_list)) {
                    $where .= $where ? " OR " : "" ;
                    $where .= "( ra.report_access_type = " . COMPANY_RESTRICTED . " AND ra.report_access_id IN (" . implode(', ', array_keys($company_list)) ."))";
                }
            } else {
                $where .= $where ? " OR " : "" ;
                $where .= "( ra.report_access_type = " . COMPANY_RESTRICTED . " AND ra.report_access_id IN ( " . $cid . ") )";
            }
        }
        // Project specific reports
        if ( $search == 'all' || $search == 'project' ) {
            if ( $search == 'all' ) {
                $project= new CProject();
                $project_list = $project->getAllowedRecords($uid, 'projects.project_id');
                if ( count( $project_list ) ) {
                    $where .= $where ? " OR " : "" ;
                    $where .= "( ra.report_access_type = " . PROJECT_RESTRICTED . " AND ra.report_access_id IN (" . implode( ', ', $project_list ) . "))" ;
                }
            } else {
                $where .= $where ? " OR " : "" ;
                $where .= "( ra.report_access_type = " . PROJECT_RESTRICTED . " AND ra.report_access_id IN ( " .$pid . " ) )";

            }
        }
// User specific reports
        if ( $search == 'all' || $search == 'user') {
            // User restricted report
            $where .= $where ? " OR " : "" ;
            $where .= " ( ra.report_access_type = ". USER_RESTRICTED . " AND ra.report_access_id = " . $uid . ")" ;
            $where .= $where ? " OR " : "" ;
            $where .= " ( report_type = ". PRIVATE_REPORT . " AND report_creator = " . $uid . ")";
        }
// Admin report
        $perms =& $AppUI->acl();
        if ( ( $search == 'admin' || $search == 'user' ) && $perms->checkModule( 'admin', 'view' ) ) {
            $where .= $where ? " OR " : "" ;
            $where .= " ( report_type = " . ADMIN_REPORT . ")" ;
        }
// Build query
        $q->addWhere( "(" . $where . ")" );
        if ( $search == 'all' || $search == 'company' || $search == 'project' || $search == 'user' ) {
            $q->leftJoin('flexreport_access', 'ra', 'ra.report_access_report = report_id' );
        }
        return $q->loadColumn();
    }

    /**
     * @param $uid
     * @param string $fields
     * @param string $orderby
     * @param null $index
     * @param null $extra
     * @return array|Associative
     */
    public function getTargetProjects( $uid, $fields='*', $orderby='', $index=NULL, $extra=NULL ) {
        global $AppUI;
        if ( ! $this->report_id ) {
            return array();
        }

        $proj = new CProject();

        if ( $this->report_type == RESTRICTED_REPORT ) {
            $q = new w2p_Database_Query();
            $q->addTable('flexreport_access');
            $q->addQuery('*');
            $q->addWhere('report_access_report = ' . $this->report_id );
            $access_rights = $q->loadList();
            $all_project = false ;
            $company_list = '';
            foreach ( $access_rights as $ra ) {
                switch ( $ra['report_access_type'] ) {
                    case COMPANY_RESTRICTED :
                        $company_list .= ($company_list ? ',' : '').$ra['report_access_id'];
                        break;
                    case PROJECT_RESTRICTED :
                        $projects[] = $ra['report_access_id'];
                        break;
                    case USER_RESTRICTED :
                        $all_project = true ;
                        break;
                }
            }
            if ( ! $all_project ) {
                // Retrieve company list
                $company_projects = array();
                if ( $company_list ) {
                    $q = new w2p_Database_Query();
                    $q->addTable('projects');
                    $q->addQuery('project_id');
                    $q->addWhere('project_company IN ('.$company_list.')');
                    $company_projects = $q->loadColumn();
                }
                $access_list = array_merge( $company_projects, $projects );
                if ( count($access_list) == 0 ) {
                    return array();
                }
                $extra['where'] = isset($extra['where']) ? $extra['where'] . " AND " : "";
                $extra['where'] .= "project_id IN (" . implode(', ', $access_list) .")" ;
            }
        }
        return $proj->getAllowedRecords( $uid, $fields, $orderby, $index, $extra );
    }

    /**
     * @param $uid
     * @param string $fields
     * @param string $orderby
     * @param null $index
     * @param null $extra
     * @return array|Associative
     */
    public function getTargetCompanies( $uid, $fields='*', $orderby='', $index=NULL, $extra=NULL )
    {
        $q = new w2p_Database_Query();
        $q->addTable('flexreport_access');
        $q->addQuery('report_access_id');
        $q->addWhere('report_access_report = ' . $this->report_id );
        $q->addWhere('report_access_type = ' . COMPANY_REPORT );
        $access_list = $q->loadColumn();
        if ( !$access_list ) {
            return array();
        }
        if ( isset( $extra['where'])) {
            $extra['where'] .= " AND ";
        } else {
            $extra['where'] = "";
        }
        $extra['where'] .= "company_id IN (" . $access_list .")" ;
        $Cpy = new CCompany();
        return $Cpy->getAllowedRecords( $uid, $fields, $orderby, $index, $extra );
    }

    /**
     * @param $uid
     * @param string $fields
     * @param string $orderby
     * @param null $index
     * @param null $extra
     * @return array|Associative
     */
    public function getTargetUsername( $uid, $fields='', $orderby='', $index=NULL, $extra=NULL )
    {
        $q = new w2p_Database_Query();
        $q->addTable('flexreport_access');
        $q->addQuery('report_access_id');
        $q->addWhere('report_access_report = ' . $this->report_id );
        $q->addWhere('report_access_type = ' . USER_REPORT );
        $access_list = $q->loadColumn();
        if ( !$access_list ) {
            return array();
        }
        $q->clear();
        $q->addTable('users');
        $q->addTable('contacts');
        $q->addWhere('contact_user = user_id');
        $q->addQuery('user_id, WS_CONCAT(", ", contact_firstname, contact_lastname) as username');
        if ( $fields ) {
            $q->addQuery($fields);
        }
        if ( isset( $extra['where'])) {
            $q->addWhere($extra['where']);
        }
        $q->addWhere( "user_id IN (" . implode( ', ', $access_list) .")" ) ;
        return $q->loadHashList('user_id');
    }

    /**
     * @param $project_id
     * @param $canEdit
     * @param string $target
     */
    public function show_report( $project_id, $canEdit, $target='all' )
    {
        global $AppUI ;
        $id_list = array();
        if ( ! $project_id && $target == 'project' ) {
            $id_list = $this->getTargetProjects( $AppUI->user_id, 'project_id, project_name');
        }
        $rowspan = count( $id_list ) ? "rowspan=\"" . count( $id_list ) . "\" " : "";
        echo "<tr>\n<td valign=\"top\" ". $rowspan . ">";
        if ( $canEdit ) {
            echo "<a href=\"?m=flexreports&a=addedit&report_id=" . $this->report_id . "\">";
            echo "<img src=\"./modules/flexreports/images/pencil.gif\" width=\"12\" heigth=\"12\" border=\"0\" alt=\"". $AppUI->_('Edit report') . "\" >\n" ;
            echo "</a>";
        }
        echo "</td>";
        echo "<td valign=\"top\" ". $rowspan . ">";
        $s = "<a href=\"?m=flexreports&a=view&report_id=" . $this->report_id ;
        if ( $project_id ) {
            $s .= "&project_id=" . $project_id ;
        }
        $s .= "\" >" . $this->report_name . "</a></td>" ;
        echo $s ;
        $description = $this->report_description ? $this->report_description : "&nbsp;" ;
        echo "<td valign=\"top\" ". $rowspan . ">" . $description . "</td>" ;
        if ( ! $project_id && $target == 'all' ) {
            echo "<td valign=\"top\">" . $AppUI->_("All projects") ."</td>" ;
        } else {
            if ( ! $project_id ) {
                $s = '';
                foreach ( $id_list as $pid => $pname ) {
                    $s .=  $s ? "</tr>\n<tr><td>" : "<td>" ;
                    $s .= "<a href=\"?m=flexreports&a=view&report_id=" . $this->report_id ."&project_id=" . $pid . "\" >" . $pname . "</a></td>\n" ;
                }
                echo $s ;
            }
        }
        echo "</tr>\n" ;
    }

    /**
     * @param $field_ref
     * @param $rank
     * @return null
     */
    public function addField( $field_ref, $rank )
    {
        global $field_desc;

        $nc = strpos($field_ref, ':');
        $table = substr( $field_ref, 0, $nc );
        $nc++ ;
        $name = substr( $field_ref, $nc );
        $column = $field_desc[$table]['field_list'][$name][0];
        $q = new w2p_Database_Query();
        $q->addTable( 'flexreport_fields' );
        $q->addInsert( 'report_field_report', $this->report_id );
        $q->addInsert( 'report_field_table', $table );
        $q->addInsert( 'report_field_column', $column );
        $q->addInsert( 'report_field_name', $name );
        $q->addInsert( 'report_field_rank', $rank );
        if ( !$q->exec() ) {
            return db_error();
        }
        return NULL ;
    }

    /**
     * @param string $fields
     * @return Array
     */
    public function getReportField( $fields = "*" )
    {
        $q = new w2p_Database_Query();
        $q->addTable('flexreport_fields');
        $q->addQuery( $fields );
        $q->addWhere( 'report_field_report = ' . $this->report_id );
        $q->addOrder( 'report_field_rank' );
        return $q->loadList();
    }

    /**
     * @param $filter
     * @return null
     */
    public function addReportFilter( $filter )
    {
        global $field_desc;
        $field_ref = $filter[1];
        $nc = strpos($field_ref, ':');
        $table = substr( $field_ref, 0, $nc );
        $nc++ ;
        $name = substr( $field_ref, $nc );
        $column = $field_desc[$table]['field_list'][$name][0];
        $q = new w2p_Database_Query();
        $q->addTable('flexreport_filters');
        $q->addInsert( 'report_filter_report', $this->report_id );
        $q->addInsert( 'report_filter_mode', $filter[0] );
        $q->addInsert( 'report_filter_name', $name );
        $q->addInsert( 'report_filter_table', $table );
        $q->addInsert( 'report_filter_column', $column );
        $q->addInsert( 'report_filter_operator', $filter[2] );
        $q->addInsert( 'report_filter_value', $filter[3] );
        $q->addInsert( 'report_filter_label', $filter[4] );
        if ( !$q->exec() ) {
            return db_error();
        }
        return NULL ;
    }

    /**
     * @param string $fields
     * @return Array
     */
    public function getReportFilter( $fields = '*')
    {
        $q = new w2p_Database_Query();
        $q->addTable('flexreport_filters');
        $q->addQuery( $fields );
        $q->addWhere( 'report_filter_report = ' . $this->report_id );
        return $q->loadList();
    }

    /**
     * @param $type
     * @param $id
     * @return null
     */
    public function addReportAccess( $type, $id )
    {
        global $AppUI;
        $q = new w2p_Database_Query();
        $q->addTable('flexreport_access');
        $q->addInsert('report_access_report', $this->report_id );
        $q->addInsert('report_access_type', $type );
        $q->addInsert('report_access_id', $id );
        if ( !$q->exec() ) {
            return db_error();
        }
        return NULL ;
    }

    /**
     * @return Array
     */
    public function getReportAccess()
    {
        $q = new w2p_Database_Query();
        $q->addTable('flexreport_access');
        $q->addQuery('*');
        $q->addWhere('report_access_report = '.$this->report_id );
        return $q->loadList() ;
    }
}