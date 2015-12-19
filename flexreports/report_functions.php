<?php 
if (!defined('W2P_BASE_DIR')){
  die('You should not access this file directly.');
}

global $field_desc, $file_format, $sort_field, $user_function_list, $show_days;
global $AppUI;
/*
*    List of user functions : used when reporting on users
*/
$user_function_list = array(
        0        => array('', 'Select a function', ''),
        1        => array('projects|project_owner', 'Project owner', 'project_id'),
        2        => array('projects|project_creator', 'Project creator', 'project_id'),
        3        => array('tasks|task_owner', 'Task owner', 'task_id'),
        4        => array('tasks|task_creator', 'Task creator', 'task_id'),
        5        => array('user_tasks', 'Task assignee', 'task_id'),
        6        => array('task_log|task_log_creator', 'Log creator', 'task_log_task as task_id')
        );
/*
*     field_desc is the description of dotproject record fields :
*     each field is defined by
*         key = name of the field to be displayed in select field list
*         array :
*             field name in the database record or task_id if separate table is used (assignees, contacts)
*             function name to be used to generate the string value of the field
*             colum size for pdf
*             alignment for display
*/
$field_desc = array
    (
    // Users included only for filter definition
    // Field list is dynamically created when report based on users
    'users'        => array
            (
            'id_field'        =>    'user_id',
            'name_field'    =>    NULL,
            'join_field'    =>    'user_contact',
            'join_table'    =>    'contacts',
            'join_key'        =>    'u',
            'field_list'    =>    array()
            ),
    'projects'    => array
            (
            'id_field'        =>    'project_id',
            'name_field'    =>    'project_name',
            'join_field'    =>    'project_company',
            'join_table'    =>     'companies',
            'join_key'        =>    'p',
            'field_list'    =>    array(
                    'Company'            => array('project_company', NULL, 175, 'left', 6, 'projects|companies' ),
                    'Departments'        => array('project_id', 'strprojectdepts', 175, 'left', 7, 'project_departments|departments'),
                    'Projectid'            => array('project_id', NULL, 15, 'left', 0 , NULL),
                    'Name'                => array('project_name', NULL, 175, 'left', 2,  NULL),
                    'Short Name'        => array('project_short_name', NULL, 50, 'left', 2,  NULL),
                    'Project owner'     => array('project_owner', NULL, 75, 'left', 6,  'projects|users'),
                    'Start Date'         => array('project_start_date', 'strdate', 75, 'center', 5,  NULL),
                    'Target End Date'     => array('project_end_date', 'strdate', 75, 'center', 5,  NULL),
                    'Actual End Date'    => array('project_id', 'stractualenddate', 75, 'center', 0,  NULL),
                    'Scheduled work'     => array('project_id', 'strscheduleddays', 50, 'center', 0, NULL),
                    'Project work'         => array('project_id', 'strprojectdays', 50, 'center', 0, NULL),
                    'Time worked'        => array('project_id', 'strworkedhours', 50, 'center', 0, NULL),
                    'Progress'             => array('project_id', 'strprojectprogress', 50, 'center', 0, NULL),
                    'Status'             => array('project_status', '{ProjectStatus}', 75, 'center', 4,  '{ProjectStatus}'),
                    'Priority'             => array('project_priority', '{ProjectPriority}', 75, 'center', 4,  '{ProjectPriority}'),
                    'Description'         => array('project_description', NULL, 285, 'left', 2,  NULL),
                    'Target budget'     => array('project_target_budget', 'strbudget', 50, 'center', 1,  NULL),
                    'Actual budget'     => array('project_actual_budget',  'strbudget', 50, 'center', 1,  NULL),
                    'URL'                 => array('project_related_url', NULL, 175, 'left', 2,  NULL),
                    'Staging URL'        => array('project_demo_url', NULL, 175, 'left', 2,  NULL),
                    'Contacts'             => array('project_id', 'strprojectcontacts', 175, 'left', -1, 'project_contacts|contacts'),
                    'Type'                 => array('project_type', '{ProjectType}', 75, 'center', 4,  '{ProjectType}'),
                    'Creator'             => array('project_creator', NULL, 75, 'left', 6,  'projects|users')
                                     )
            ),
    'tasks' => array
            (
            'id_field'        =>     'task_id',
            'name_field'    =>    'task_name',
            'join_field'    =>    'task_project',
            'join_table'    =>    'projects',
            'join_key'        =>    't',
            'field_list'    => array(
                    'Task Name'         => array('task_name', NULL, 175, 'left', 2, NULL ),
                    'Task Parent'         => array('task_parent', NULL, 175, 'left', 6, 'tasks|tasks'),
                    'Milestone'         => array('task_milestone', 'strflagYN', 50, 'center', 3, NULL),
                    'Dynamic task'        => array('task_dynamic', 'strflagYN', 50, 'center', 3, NULL),
                    'Owner'             => array('task_owner', NULL, 75, 'center', 6, 'tasks|users'),
                    'Start Date'         => array('task_start_date', 'strdate', 75, 'center', 5, NULL ),
                    'Duration'             => array('task_id', 'strtaskduration', 50, 'center', 1, NULL),
                    'Time worked'         => array('task_id', 'strhoursworked', 75, 'center', 0, NULL),
                    'End Date'             => array('task_end_date', 'strdate', 75, 'center', 5, NULL),
                    'Status'             => array('task_status', '{TaskStatus}', 75, 'center', 4, '{TaskStatus}'),
                    'Priority'             => array('task_priority', '{TaskPriority}', 50, 'center', 4, '{TaskPriority}'),
                    'Progress'             => array('task_percent_complete', 'strpercent', 50, 'center', 1, NULL),
                    'Description'         => array('task_description', NULL, 350, 'left', 2, NULL),
                    'Target budget'     => array('task_target_budget', NULL, 50, 'center', 1, NULL),
                    'Related URL'         => array('task_related_url', NULL, 175, 'left', 2, NULL),
                    'Contacts'             => array('task_id', 'strtaskcontacts', 175, 'left', -1, 'task_contacts|contacts'),
                    'Type'                 => array('task_type', '{TaskType}', 50, 'center', 4, '{TaskType}'),
                    'Assigned Users'    => array('task_id', 'strassignees', 175, 'left', 7, 'user_tasks|users'),
                    'Last activity'        => array('task_id', 'strlastactivity', 75, 'center', 0, NULL),
                    'Creator'             => array('task_creator', NULL, 75, 'center', 6, 'tasks|users'),
                    'Departments'        => array('task_id', 'strtaskdepts', 175, 'center', 7, 'task_departments|departments')
                                    )
            ),
    'task_log'    => array
            (
            'id_field'        =>    'task_log_id',
            'name_field'    =>    'task_log_name',
            'join_field'    =>    'task_log_task',
            'join_table'    =>    'tasks',
            'join_key'        =>    'l',
            'field_list'    =>    array(
                    'Log name'        => array('task_log_name', NULL, 150, 'left', 2, NULL),
                    'Description'     => array('task_log_description', NULL, 250, 'left', 2, NULL),
                    'Log Reference'    => array('task_log_reference', '{TaskLogReference}', 75, 'center', 4, '{TaskLogReference}'),
                    'Creator'         => array('task_log_creator', NULL, 75, 'left', 6, 'task_log|users'),
                    'Hours'             => array('task_log_hours', 'strhours', 50, 'center', 1, NULL),
                    'Date'             => array('task_log_date', 'strdate', 50, 'center', 5, NULL),
                    'Cost Code'     => array('task_log_costcode', NULL, 75, 'center', 6, 'task_log|billingcode' ),
                    'Problem'        => array('task_log_problem', 'strflagYN', 50, 'center', 3, NULL),
                    'Log URL'        => array('task_log_related_url', NULL, 250, 'left', 2, NULL )
                                     )
            ),
/* Billing codes - Uncomment these lines if billingcades are used
    'billingcode' => array
            (
            'id_field'        =>    'billingcode_id',
            'name_field'    =>    'billingcode_name',
            'join_field'    =>    NULL,
            'join_table'    =>    NULL,
            'join_key'        =>    'bc',
            'field_list'    =>    array(
                    'Billing code name'            => array('billingcode_name', NULL, 75, 'left', 2, NULL),
                    'Billing code value'        => array('billingcode_value', 'strbudget', 50, 'center', 1, NULL ),
                    'Billing code description'    => array('billingcode_desc', NULL, 150, 'left', 2 , NULL),
                    'Billing code status'        => array('billingcode_status', '{BillingCodeStatus}', 75, 'center', 4, '{BillingCodeStatus}'),
                    'Billing code company'        => array('company_id', NULL, 75, 'center', 6, 'billingcode|companies')
                                    )
            ),
*/

// Companies included only for filter definition - Uncomment field_list lines if company information is used
    'companies'    => array(
            'id_field'        =>    'company_id',
            'name_field'    =>    'company_name',
            'join_field'    =>    NULL,
            'join_table'    =>    NULL,
            'join_key'        =>    'co',
            'field_list'    =>    array(
                    'Company Type'    => array('company_type', '{CompanyType}', 50, 'center', 4, '{COmpanyType}'),
                    'Company Name'    => array('company_name', '{CompanyName}', 50, 'center', 4, '{COmpanyName}'),
                    'Company Owner'    => array('company_owner', NULL, 75, 'center', 6, 'companies|users'),
                    'Company City'    => array('company_city', NULL, 75, 'center', 2, NULL),
                    'Company Zip'    => array('company_zip', NULL, 75, 'center', 2, NULL)
                    )
            ),
// Contacts inluded only for filter definition
    'contacts'    => array(
            'id_field'        =>    'contact_id',
            'name_field'    =>    '+contact_first_name,contact_last_name',
            'join_field'    =>    'contact_id',
            'join_table'    =>    NULL,
            'join_key'        =>    'con',
            'field_list'    =>    array()
            ),
/* Files - Yet to be defined
    'files' => array
            (
            'id_field'        =>    'file_id',
            'name_field'    =>    'file_name',
            'join_field'    =>    'file_project',
            'join_table'    =>    'projects',
            'join_key'        =>    'fi',
            'field_list'    =>    array(
                            )
            ),
*/

    );

/*
*    Add Departments definition if module is active
*/
if ( $AppUI->isActiveModule('forums') ) {
    $field_desc['forums'] = array
            (
            'id_field'        =>    'forum_id',
            'name_field'    =>    'forum_name',
            'join_field'    =>    'forum_project',
            'join_table'    =>    'projects',
            'join_key'        =>    'fo',
            'field_list'    =>    array(
                    'Forum Name'        => array('forum_name', NULL, 285, 'left', 2, NULL),
                    'Owner'                => array('forum_owner', NULL, 75, 'left', 6, 'forums|users'),
                    'Status'            => array('forum_status', '{ForumStatus}', 50, 'center', 4, '{ForumStatus}'),
                    'Create date'        => array('forum_create_date', 'strdate', 50, 'left', 5, NULL),
                    'Last date'            => array('forum_last_date', 'strdate', 50, 'center', 5, NULL),
                    'Message count'        => array('forum_message_count', NULL, 50, 'center', 1, NULL),
                    'Description'        => array('forum_description', NULL, 285, 'left', 2, NULL),
                    'Moderator'            => array('forum_moderated', NULL, 75, 'center', 6, 'forums|users')
                                    )
            );
    $field_desc['forum_messages'] =  array
            (
            'id_field'        =>    'message_id',
            'name_field'    =>    '',
            'join_field'    =>    '',
            'join_table'    =>    'forums',
            'join_key'        =>    'fm',
            'field_list'    =>    array
                                    (
// to be defined
                                    ),
            );

}

/*
*    Add Risks definition if module is active  CN-www.NUY.info
*/
if ( $AppUI->isActiveModule('risks') ) {

    $field_desc['risk_notes'] = array
        (
        'd_field'    =>    'risk_note_id',
        'name_field'    =>    NULL,
        'join_field'    =>    'risk_note_risk',
        'join_table'    =>    'risks',
        'join_key'        =>    'rn',
        'field_list'    => array(
            'Note creator'        => array('risk_note_creator', NULL, 75, 'center', 6, 'risk_notes|users'),
            'Note date'            => array( 'risk_note_date', 'strdate', 75, 'center', 5, NULL),
            'Note description'    => array('risk_note_description', NULL, 175, 2, NULL)
            )
        );


    $field_desc['risks'] = array(
        'id_field'    =>    'risk_id',
        'name_field'    =>    'risk_name',
        'join_field'    =>    'risk_project',
        'join_table'    =>    'projects',
        'join_key'    =>    'r',
        'field_list'    => array(
            'Risk name'         => array('risk_name', NULL, 75, 'left', 2, NULL ),
            'Risk description'    => array('risk_description', NULL, 175, 'left', 2, NULL ),
            'Risk status'        => array('risk_status', NULL, '75', 'center', 2, NULL),
            'risk_project'        => array('risk_project', NULL , 75, 'left', 6, 'risks|projects'),
            'Risk owner'        => array('risk_owner', NULL, 75, 'center', 6, 'risks|users'),
            'Risk task'            => array('risk_task', NULL, 75, 'center', 6, 'risks|tasks'),
            'Risk probability'    => array('risk_probability', 'strpercent', 50, 'center', 1, NULL),
            'Risk impact'        => array('risk_impact', NULL, 50, 'center', 1, NULL)
            )
        );

}

/*
*    Add Journal definition if module is active  CN-www.NUY.info
*/
if ( $AppUI->isActiveModule('journal') ) {
      $field_desc['journal'] = array(
        'id_field'    =>    'journal_id',
        'name_field'    =>    'journal_description',
        'join_field'    =>    'journal_project',
        'join_table'    =>    'projects',
        'join_key'    =>    'j',
        'field_list'    => array(
            'Journal description'    => array('journal_description', NULL, 175, 'left', 2, NULL ),
            'Journal date'        => array('journal_date', 'strdate', '75', 'center', 2, NULL),
            'Journal project'        => array('journal_project', NULL , 75, 'left', 6, 'journal|projects'),
            'Journal owner'        => array('journal_user', NULL, 75, 'center', 6, 'journal|users')
            )
        );
}

/*
*    Add Metrics definition if module is active  CN-www.NUY.info
*/
if ( $AppUI->isActiveModule('metrics1') ) {
      $field_desc['metrics_groups'] = array(
        'id_field'        =>    'metric_group_id',
        'name_field'    =>    'metric_group_name',
        'join_field'    =>    'metric_group_id',
        'join_table'    =>    'metrics',
        'join_key'    =>    'mg',
        'field_list'    => array(
            'Group Sequence'            => array('metric_group_seq', NULL, '75', 'left', 2, NULL ),
            'Group name'            => array('metric_group_name', NULL, '75', 'left', 2, NULL )
            )
        );
    $field_desc['metrics'] = array(
        'id_field'        =>    'metric_id',
        'name_field'    =>    'metric_name',
        'join_field'    =>    'metric_id',
        'join_table'    =>    'metrics_results',
        'join_key'    =>    'm',
        'field_list'    => array(
            'Metric Sequence'            => array('metric_seq', NULL, '75', 'left', 2, NULL ),
            'Metric name'            => array('metric_name', NULL, '75', 'left', 2, NULL ),
            'Metric list'            => array('metric_list', NULL, '75', 'left', 2, NULL ),
            'Metric description'    => array('metric_description', NULL, 175, 'left', 2, NULL )
            )
        );
     $field_desc['metrics_results'] = array(
        'id_field'        =>    'metric_id',
        'name_field'    =>    'metric_result',
        'join_field'    =>    'project_id',
        'join_table'    =>    'projects',
        'join_key'    =>    'mr',
        'field_list'    => array(
            'Metric project'        => array('project_id', NULL , 75, 'left', 6, 'metrics_results|projects'),
            'Metric task'            => array('task_id', NULL, '75', 'left', 2, 'metrics_results|tasks'),
            'Metric Result'            => array('metric_result', NULL, 175, 'left', 2, NULL )
            )
        );

}

/*
*    Add Departments definition if module is active
*/
if ( $AppUI->isActiveModule('departments') ) {
    $field_desc['departments'] = array(
                'id_field'        =>    'dept_id',
                'name_field'    =>    'dept_name',
                'join_field'    =>    'dept_company',
                'join_table'    =>    'companies',
                'join_key'        =>    'dept',
                'field_list'    =>    array(
                        'Dept Name'         => array('dept_name', NULL, 175, 'left', 2, NULL ),
                        'Dept Parent'         => array('dept_parent', NULL, 175, 'left', 6, 'departments|departments'),
                        'Dept Company'        => array('dept_company', NULL, 175, 'left', 6, 'departments|companies' ),
                        'Dept Description'    => array('dept_desc', NULL, 350, 'left', 2, NULL),
                        'Dept Owner'        => array('dept_owner', NULL, 75, 'center', 6, 'departments|users')
                        )
                );
    }
/*
*     Add custom fields in field description table
*/
$q = new w2p_Database_Query();
$modules = array_keys($field_desc);
foreach ( $modules as $module ) {
    $q->addTable( 'custom_fields_struct' );
    $q->addQuery( '*' );
    $q->addWhere( 'field_module = "' . $module . '"');
    $q->addWhere( 'field_htmltype != "separator"');
    $custom_fields = $q->loadList();
    if ( count($custom_fields) ) {
        $custom_id_field = $field_desc[$module]['id_field'];
        foreach ( $custom_fields as $cf ) {
            $custom_field_name = $cf['field_description'];
            $custom_field_type = $cf['field_htmltype'];
            $custom_field_table = $cf['field_name'];
            switch ( $custom_field_type ) {
                case "checkbox" :
                    $custom_field_length = 50 ;
                    break;
                case "href" :
                case "select" :
                case "label" :
                    $custom_field_length = 100 ;
                    break;
                default :
                    $custom_field_length = 175 ;
                    break;
            }
            $field_desc[$module]['field_list'][$custom_field_name] =
                    array( $custom_id_field, NULL, $custom_field_length, 'left', -1, $custom_field_table );
        }
    }
}
/*
* User field array will be included depending on report reference
*/
$user_field_list = array(
                    'First name'        => array('contacts|contact_first_name', NULL, 50, 'left', 2, NULL),
                    'Last name'            => array('contacts|contact_last_name', NULL, 50, 'left', 2, NULL),
                    'Full name'            => array('user_id', NULL, 75, 'left', 6, '|users'),
                    'User name'            => array('user_username', NULL, 50, 'left', 2, NULL),
                    'Assigned task'        => array('user_tasks|task_id', 'strassignedtask', 175, 'left', 7, 'user_tasks|users'),
                    'Assigned percent'    => array('user_tasks|perc_assignment', 'strpercent', 50, 'center', 1, NULL),
                    'Assigned time'        => array('user_tasks|perc_assignment', 'strassignedtime', 50, 'center', 0, 'tasks:task_id,users:user_id'),
                    'Allocated time'    => array('user_tasks|task_id', 'strallocatedtime', 50, 'center', 0, 'tasks:task_id,users:user_id'),
                    'Phone number'        => array('contacts|contact_phone', NULL, 50, 'left', 2, NULL),
                    'Email'                => array('contacts|contact_email', NULL, 75, 'left', 2, NULL),
                    'Company'            => array('contacts|contact_company', NULL, 50, 'left', 6, 'contacts|companies'),
                    'Department'        => array('contacts|contact_department', NULL, 50, 'left', 6, 'contacts|departments')
                    );
/*
*     Indirection tables
*/
$indirection_table = array
    (
// Project_departments only for filter definition
    'project_departments'     => array
            (
            'join_key'    =>    'pdept',
            'left'        => array(
                                'field'    =>    'project_id',
                                'table'    =>    'projects' ),
            'right'        => array(
                                'field'    =>    'department_id',
                                'table'    =>    'departments')
            ),
    'project_contacts'        => array
            (
            'join_key'    =>    'pcont',
            'left'        => array(
                            'field'    =>    'project_id',
                            'table'    =>    'projects' ),
            'right'        => array(
                            'field'    =>    'contact_id',
                            'table'    =>    'contacts')
            ),
    'task_departments'     => array
            (
            'join_key'    =>    'tdept',
            'left'        => array(
                                'field'    =>    'task_id',
                                'table'    =>    'tasks' ),
            'right'        => array(
                                'field'    =>    'department_id',
                                'table'    =>    'departments')
            ),
    'task_contacts'        => array
            (
            'join_key'    =>    'tcont',
            'left'        => array(
                            'field'    =>    'task_id',
                            'table'    =>    'tasks' ),
            'right'        => array(
                            'field'    =>    'contact_id',
                            'table'    =>    'contacts')
            ),
    'user_tasks'        => array
            (
            'join_key'    =>    'ut',
            'left'        => array(
                            'field'    =>    'task_id',
                            'table'    =>    'tasks'),
            'right'        => array(
                            'field'    =>    'user_id',
                            'table'    =>    'users')
            ),
    );
/*
*     Define available date filters
*/
$report_datefilter_list = array(
            '  '                             =>    array('None',''),
            'projects:project_start_date'     =>    array('Project start date','Projects started'),
            'projects:project_end_date'        =>    array('Project end date', 'Projects finished'),
            'tasks:task_start_date'            =>    array('Task start date', 'Tasks started'),
            'tasks:task_end_date'            =>    array('Task end date', 'Tasks finished'),
            'task_log:task_log_date'        =>    array('Log date','Task log entries'),
            'forums:forum_create_date'        =>    array('Forum create date', 'Forums created'),
            'forums:forum_last_date'        =>    array('Forum last date', 'Forums last date')
            );
/*
*     List of predefined value string for filters
*/
$predefined_value = array(
            'companies'        => 'MYCOMPANY',
            'projects'        => 'MYPROJECTS',
            'tasks'            => 'MYTASKS',
            'users'            => 'USER'
            );

/*
*    List of available comparison operators
*/
$operator_list = array(
                    'none',                    // Default value
                    'equals',                // Appliez to all scalar values
                    'not equals',
                    'greater than',            // Applies to numerical and dates
                    'greater or equals',
                    'less than',
                    'less or equals',
                    'contains',                // Applies to text
                    'does not contain',
                    'starts with',
                    'equals',                // Applies to lists (sysval, IDs, ...)
                    'not equals',
                    'is set',                // Applies to flags such as milestone, problem, ....
                    'is not set',
                    'is defined',            // Applies to assignees
                    'is not defined'
                    );
/*
*    List of predefined period for date filters
*/
$date_filter_list = array(
                    'PQ'    =>    'Previous quarter',
                    'PM'    =>    'Previous month',
                    'PF'    =>    'Previous 2 weeks',
                    'PW'    =>    'Previous week',
                    'PD'    =>    'Yesterday',
                    'NOW'    =>    'Today',
                    'ND'    =>    'Tomorrow',
                    'NW'    =>    'Next week',
                    'NF'    =>    'Next 2 weeks',
                    'NM'    =>    'Next month',
                    'NQ'    =>    'Next quarter'
                    );
/*
*    List of tuser time fields for display per period
*/
$user_time_field = array(
                    'None',
                    'Allocated time',
                    'Assigned time'
                    );
/*
*      List of available file formats
*/
$report_format_list = array(
                    0 => 'No file',
                    1 => 'PDF file',
                    2 => 'CSV file',
                    3 => 'OOXML file'
                    );
/*
*    List of report access type
*/
$report_type_list = array(
                    0 => 'Public',
                    1 => 'Restricted',
                    2 => 'Personal',
                    4 => 'Admin'
                    );
/*
*    List of report layout
*/
$report_layout_list = array(
                    0 => 'Columnar',
                    1 => 'Tabular'
                    );

/*
*    Utility functions
*
*     field_SQLname
*         create name to be used in SQL statement from field_desc table
*        @param field designator in the form <table>:<colujmn>
*/
function field_SQLname( $field ) {
    global $field_desc;
    $nc = strpos( $field, ':' );
    $table = substr( $field, 0, $nc );
    $nc++ ;
    $name = substr( $field, $nc );
    $field_ref = $field_desc[$table]['field_list'][$name][0];
    if ( $nc=strpos( $field_ref, '|') ) {
        // Let's check that the name does not include an indirection to another table ( user fields)
        $table = substr( $field_ref, 0, $nc );
        $nc++;
        $name = substr( $field_ref, $nc );
        return $field_desc[$table]['join_key'] . "." . $name ;
    }
    return $field_desc[$table]['join_key'] . "." . $field_desc[$table]['field_list'][$name][0] ;
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelectWithOptgroup( $arr, $select_name, $select_attribs, $selected, $translate=false ) {
    GLOBAL $AppUI;
    if (! is_array($arr)) {
        dprint(__FILE__, __LINE__, 0, "arraySelect called with no array");
        return '';
    }
    reset( $arr );
    $s = "\n<select name=\"$select_name\" $select_attribs>";
    $s .= "\n\t<option value=\" \" selected=\"selected\" >" . $AppUI->_('Select a field') . "</option>" ;
    foreach ($arr as $group => $list ) {
        $s.= "\n<optgroup label=\"\" >&nbsp</optgroup>" ;
        $s .= "\n<optgroup label=\""  . $AppUI->_($group) . "\" >" . $AppUI->_($group) . "</optgroup>" ;
        foreach ( $list as $k => $v ) {
            if ($translate) {
                $v = @$AppUI->_( $v );
            // This is supplied to allow some Hungarian characters to
            // be translated correctly. There are probably others.
            // As such a more general approach probably based upon an
            // array lookup for replacements would be a better approach. AJD.
                $v=str_replace('&#369;','�',$v);
                $v=str_replace('&#337;','�',$v);
            }
            $s .= "\n\t<option value=\"".$k."\"".($k == $selected ? " selected=\"selected\"" : '').">&nbsp;&nbsp;&nbsp;" .  $v  . "</option>";
        }
    }
    $s .= "\n</select>\n";
    return $s;
}
/*
*  Return a list of names based on ID
*         @param = commaseparated list of ID
*         @param = DB table name
*/
function getNamesFromID ( $id_list, $table ) {
    global $field_desc, $date_filter_list, $AppUI;
    if ( preg_match( "/\{([A-Z]+)\}/", $id_list, $matches )) {
        // default value
        return $id_list ;
    }
    if ( !$table ) {
        // predefined date period
        return $date_filter_list[$id_list];
    }
    if ( preg_match( "/\{([A-Za-z]+)\}/", $table, $matches )) {
        $string = '';
        $sysval_array = w2PgetSysVal( $matches[1] );
        $index_list = explode(',', $id_list );
        for ( $i=0 ; $i<count($sysval_array); $i++ ) {
            if ( in_array( $i, $index_list ) ) {
                $string .= ( $string ? ',' : '' ) . $AppUI->_($sysval_array[$i]) ;
            }
        }
        return $string ;
    }
    // query the DB to get names
    $tbl = strpos( $table, '|') ? substr( $table, strpos( $table, '|')+1 ) : $table ;
    $q = new w2p_Database_Query();
    $q->addTable( $tbl );
    $name_field = $field_desc[$tbl]['name_field'];
    $join_key = "";
    if ( !isset($name_field) ) {
        $jtable = $field_desc[$tbl]['join_table'];
        if ( !$jtable ) die ("Invalid field description table") ;
        $q->addJoin( $jtable, $field_desc[$jtable]['join_key'], $field_desc[$jtable]['join_key'].".".$field_desc[$jtable]['id_field']." = ".$field_desc[$tbl]['join_field'] );
        $name_field = $field_desc[$jtable]['name_field'];
        $join_key = $field_desc[$jtable]['join_key'] . ".";
    }
    if ( substr( $name_field, 0, 1) == '+' ) {
        $fields = explode( ',', substr( $name_field, 1) ) ;
        $name_field = 'CONCAT_WS( " ", ' ;
        foreach ( $fields as $f ) {
            $name_field .= $join_key . $f . ',' ;
        }
        $name_field = substr( $name_field, 0, -1 ) . ')';
    }
    $q->addQuery( $name_field );

    $q->addWhere( $field_desc[$tbl]['id_field'] . ' IN (' . $id_list . ')' );
    $result = $q->loadColumn();
    return implode( ',', $result );
}

/*
*     Retrieve allowed IDs list for a given table
*          @param = table name or <ID table>|<possible values table>
*         @param = field in <possible values table> if used
* 
*/
function getRecordNames( $table, $field='') {
    global $AppUI, $field_desc, $user_field_list;
    $debug = 0 ;
    if ( $debug ) echo "Start getRecordNames ( $table, $field ) <br>";
    $field_desc['users']['field_list'] = $user_field_list ;
    if ( $debug ) {
        print_r ( array_keys($field_desc['users']['field_list'] ));
        echo "<br>";
    }
    $q = new CFlexReport_Query( 0, $debug );
    $nc = strpos( $table, '|');
    if ( $nc ) {
        $join_table = substr( $table, 0, $nc );
        $ref_table = substr( $table, $nc+1 );
    } else {
        $ref_table = $table;
        $join_table = '';
    }
// Special case users : return list of all users 
    if ( $ref_table == 'users' && !$join_table ) {
        $return_list = getRecordNames( 'projects|users', 'project_owner')
                     + getRecordNames( 'projects|users', 'project_creator')
                     + getRecordNames( 'tasks|users', 'task_owner')
                     + getRecordNames( 'tasks|users', 'task_creator')
                     + getRecordNames( 'user_tasks|users')
                     + getRecordNames( 'task_log|users', 'task_log_creator') ;
        return $return_list;
    }
    if ( $debug ) echo "Add FROM $ref_table <br>";
    $q->addFromClause ( $ref_table );
    if ( $join_table ) {
        $fld = ( $nc=strpos($field, '|') ) ? substr($field, $nc+1 ) : $field ;
        $q->addJoinClause( $join_table, $fld, $ref_table);
    }
    $name_table = $ref_table;
    $hash_field = $field_desc[$ref_table]['id_field'];
    if ( $debug ) Echo "Retrieve names from $name_table, $hash_field <br>" ;
    if ( ! $field_desc[$name_table]['name_field'] ) {
        $name_table = $field_desc[$ref_table]['join_table'];
        $q->addJoinClause( $name_table, '', $ref_table, $field_desc[$ref_table]['join_field'] );
    }
    $name_field = $field_desc[$name_table]['name_field'];
    if ( $debug ) echo "Generate column name for $name_table, $name_field <br>" ;
    if ( substr( $name_field, 0, 1) == '+' ) {
        $fields = explode( ',', substr( $name_field, 1) ) ;
        $name_field = 'CONCAT_WS( " ", ' ;
        foreach ( $fields as $f ) {
            $name_field .= $field_desc[$name_table]['join_key'] . '.' . $f . ',' ;
        }
        $name_field = substr( $name_field, 0, -1 ) . ') as item';
        $item_field = 'item';
    } else {
        $item_field = $name_field ;
        $name_field = $field_desc[$name_table]['join_key'] . '.' . $name_field ;
    }
    $q->addQuery( $field_desc[$ref_table]['join_key'] . ".$hash_field, $name_field" );
    // Now Let's retrieve the names
    if ( $debug ) echo "Get names query = " . $q->prepare() . "<br>";
    $query_result = $q->loadList();
    $return_list = array();
    foreach ( $query_result as $qr ) {
        $return_list[$qr[$hash_field]] = $qr[$item_field] ;
    }
    if ( $debug ) {
        echo "getRecordNames Result = ";
        print_r ( $return_list );
        echo "<br>" ;
    }
    return $return_list ;
}

/*
*     Generates a field string
*         @param    = field description array in field_desc
*         @param    = row in DB query result
*
*/
function strfield( $table, $field_name, $data ) {
    global $AppUI, $field_desc;
    $row = $field_desc[$table]['field_list'][$field_name];
    if ( $row[4] < 0 ) {
        // Retrieve custom field structure
        $q = new w2p_Database_Query();
        $q->addTable( 'custom_fields_struct' );
        $q->addQuery( '*' );
        $q->addWhere( 'field_module = "' . $table . '"');
        $q->addWhere( 'field_description = "' . $field_name . '"' );
        $rs = $q->exec();
        $custom_field = $q->fetchRow();
        $q->clear();
        // Retrieve custom field value
        if ( isset($row[0]) && isset($data[$row[0]]) && $data[$row[0]] != "" ) {
            $q->addTable('custom_fields_values');
            $q->addQuery('value_charvalue, value_intvalue');
            $q->addWhere("value_field_id = ".$custom_field['field_id']);
            $q->addWhere("value_object_id = ".$data[$row[0]]);
            $q->exec();
            $result = $q->fetchRow();
            $q->clear();
            // Create string value
            switch ( $custom_field['field_htmltype'] ) {
                case "select" :
                    $q->addTable('custom_fields_lists');
                    $q->addQuery('*');
                    $q->addWhere('field_id = '.$custom_field['field_id']);
                    $values = $q->loadHashList('list_option_id');
                    $result_string = $values[$result['value_intvalue']]['list_value'] ;
                    break;
                case "checkbox" :
                    $result_string = $result['value_intvalue'] ? $AppUI->_('Yes') : $AppUI->_('No') ;
                    break;
                default :
                    $result_string = $result['value_charvalue'] ;
                    break;
                }
            return $result_string ;
        } else {
            return "";
        }
    }
    if ( $row[4] == 0 && $row[5] ) {
        // Composite field : display function call uses a comma separated list of values from row
        $string = substr( $row[5], 0 );
        $fc = strlen( $string );
        $fields = array();
        while ( $fc ) {
            $fc = strpos( $string, ',');
            $nc = strpos( $string, ':');
            $length = $fc ? $fc-$nc-1 : strlen($string)-$nc-1 ;
            $fields[] = $data[substr( $string, $nc+1, $length )];
            $string = substr( $string, $fc+1 );
            }
        $value = implode(',', $fields);
    } else {
        // Standard field : use value from row
        $field = ( $nc = strpos( $row[0], '|') ) ? substr( $row[0], $nc+1 ) : $row[0] ;
        $value = $data[$field] ;
        }
    if ( function_exists($row[1]) ) {
        return isset($value) && $value != "" ? $row[1]($value) : "" ;
        }
    // No function defined = check first if system value array
    if ( preg_match( "/\{([A-Za-z]+)\}/", $row[1], $matches ) ) {
        $str_array = w2PgetSysVal( $matches[1] );
        return isset($data[$field]) && $data[$field] != "" ? $AppUI->_( $str_array[$data[$field]] ) : "" ;
        }
    if ( $row[4] == 6 && isset($data[$field]) && $data[$field] != "" ) {
        // Query a DB table to retrieve the display value
        $nc = strpos( $row[5], '|');
        $table = substr( $row[5], $nc+1 );
        if ( $table == 'users' )
            return w2PgetUsernameFromID( $data[$field] );
        $q = new w2p_Database_Query();
        $q->addTable( $table );
        $q->addQuery( $field_desc[$table]['name_field'] );
        $q->addWhere( $field_desc[$table]['id_field'] . '=' . $data[$field] );
        return $q->loadResult();
        }
    // None of the above : display field value
    return isset($data[$field]) && $data[$field] != "" ? $data[$field] : "";
    }

/*
*    Utility function to round days
*/
function strrounddays( $fv ) {
    if ( ! $fv )
        return NULL ;
    if ( round($fv) == ( $floor = floor($fv)) )
        $value = $floor;
    else
        $value = $floor + 0.5 ;
    $delta = $fv - $value ;
    if ( $delta > 0.375 )
        $value += 0.5 ;
    else
        if ( $delta > 0.125 )
            $value += 0.25 ;
    return round($value) == $value ? number_format( $value ) : number_format( $value, 2 );
    }

/*
*    Return user firstname, userlastname
*        @param    user_id
*/
function strusername( $fv ) {
    return w2PgetUsernameFromID( $fv );
    }
//    return date string
//        @param    database date field
function strdate( $fv ) {
    global $df ;
    $date = intval($fv) ? new CDate($fv) : NULL ;
    return $date ? $date->format($df) : '' ;
    }
/*
*    Return Yes or No depending on flag value
*         @param flag value
*/
function strflagYN( $fv ) {
    global $AppUI;
    return $AppUI->_( $fv ? 'Yes' : 'No' , UI_OUTPUT_RAW ) ;
    }
/*
*     B. PROJECT SPECIFIC FUNCTIONS
*/
function strcompany( $fv ) {
    $q = new w2p_Database_Query();
    $q->addTable('companies');
    $q->addQuery('company_name');
    $q->addWhere('company_id = '. $fv );
    return $q->loadResult();
    }
function strprojectdepts( $fv ) {
    global $AppUI, $perms ;
    if ($AppUI->isActiveModule('departments') && $perms->checkModule('departments', 'view')) {
        $q = new w2p_Database_Query();
        $q->addTable('project_departments');
        $q->leftjoin('departments', 'd', 'department_id = d.dept_id');
        $q->addQuery('dept_name');
        $q->addWhere('project_id = '. $fv );
        return implode(', ', $q->loadColumn());
    } else {
        return '' ;
        }
    }
function stractualenddate( $fv ) {
    $q = new w2p_Database_Query();
    $q->addTable('tasks');
    $q->addQuery('task_end_date');
    $q->addOrder('task_end_date DESC');
    $q->addWhere("task_project = " . $fv . " AND task_end_date IS NOT NULL AND task_end_date != '0000-00-00 00:00:00'");
    $q->setLimit(1);
    return strdate( $q->loadResult() );
    }
function strprojectdays( $fv ) {
    global $w2Pconfig, $show_days, $AppUI ;
    $total_project_hours = 0;
    $q = new w2p_Database_Query();
    $q->addTable('tasks', 't');
    $q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2)');
    $q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
    $q->addWhere("t.task_project = " . $fv . " AND t.task_duration_type = 24 AND t.task_dynamic = 0");
    $days = $q->loadResult();
    $q->clear();
    $q->addTable('tasks', 't');
    $q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2)');
    $q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
    $q->addWhere("t.task_project = " . $fv . " AND t.task_duration_type = 1 AND t.task_dynamic = 0");
    $hours = $q->loadResult();
    $total_hours = $days * $w2Pconfig['daily_working_hours'] + $hours;
    $value = $show_days ? strrounddays( $total_hours/$w2Pconfig['daily_working_hours']) : number_format( $total_hours ) ;
    return  ( $value == 0 ? '0' : $value ) . " " . ( $show_days ?  $AppUI->_('d') : $AppUI->_('h') ) ;
    }
function strscheduleddays( $fv ) {
    global $w2Pconfig, $show_days, $AppUI ;
    $q = new w2p_Database_Query();
    $q->addTable('tasks');
    $q->addQuery('ROUND(SUM(task_duration),2)');
    $q->addWhere("task_project = ". $fv . " AND task_duration_type = 24 AND task_dynamic = 0");
    $days = $q->loadResult();
    $q->clear();
    $q->addTable('tasks');
    $q->addQuery('ROUND(SUM(task_duration),2)');
    $q->addWhere("task_project = ". $fv . " AND task_duration_type = 1 AND task_dynamic = 0");
    $hours = $q->loadResult();
    $total_hours = $days * $w2Pconfig['daily_working_hours'] + $hours;
    $value = $show_days ? strrounddays( $total_hours/$w2Pconfig['daily_working_hours']) : number_format( $total_hours ) ;
    return  ( $value == 0 ? '0' : $value ) . " " . ( $show_days ?  $AppUI->_('d') : $AppUI->_('h') ) ;
    }
function strworkedhours( $fv ) {
    global $w2Pconfig, $show_days, $AppUI ;
    $q = new w2p_Database_Query();
    $q->addTable('tasks');
    $q->addQuery('ROUND(SUM(task_hours_worked),2) as task_hours');
    $q->addQuery('ROUND(SUM(tl.task_log_hours),2) as log_hours');
    $q->addJoin('task_log', 'tl', 'tl.task_log_task = task_id');
    $q->addWhere("task_project = " . $fv );
    $q->exec();
    $worked = $q->fetchRow();
    $hours = $worked['task_hours']+$worked['log_hours'];
    $value = $show_days ? strrounddays($hours/$w2Pconfig['daily_working_hours']) : number_format( $hours ) ;
    return ( $value == 0 ? '0' : $value ) . " " . ( $show_days ?  $AppUI->_('d') : $AppUI->_('h') ) ;
    }
function strprojectprogress( $fv ) {
    global $w2Pconfig;
    $working_hours = $w2Pconfig['daily_working_hours'];
    $q = new w2p_Database_Query;
    $q->addTable('projects');
    $q->addQuery(
        "SUM(t1.task_duration * t1.task_percent_complete * IF(t1.task_duration_type = 24, ".$working_hours.", t1.task_duration_type))/
        SUM(t1.task_duration * IF(t1.task_duration_type = 24, ".$working_hours.", t1.task_duration_type))");
    $q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project');
    $q->addWhere(" project_id = " . $fv );
    return round( $q->loadResult() )."%";
    }
function strbudget( $fv ) {
    global $w2Pconfig ;
    return $w2Pconfig['currency_symbol'] . $fv ;
    }
function strprojectcontacts ( $fv ) {
    global $AppUI, $perms ;
    if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) {
        $q = new w2p_Database_Query();
        $q->addTable('contacts', 'c');
        $q->leftJoin('project_contacts', 'pc', 'pc.contact_id = c.contact_id');
        $q->addWhere('pc.project_id = ' . $fv );
        $q->addQuery('contact_first_name, contact_last_name');
        $sql_contact = $q->loadList();
        $contacts= NULL;
        foreach ( $sql_contact as $c ) {
            $contacts .= ( $contacts ? ', ' : ''). $c['contact_first_name'] . " " . $c['contact_last_name'] ;
            }
        return $contacts ;
        }
    return '';
    }
/*
*     C. TASK SPECIFIC FUNCTIONS
*/
function strtaskparent( $fv ) {
    $q = new w2p_Database_Query();
    $q->addTable('tasks');
    $q->addQuery('task_name');
    $q->addWhere('task_id = '. $fv );
    return $q->loadResult();
    }
function strtaskduration( $fv ) {
    global $AppUI;
    $q = new w2p_Database_Query();
    $q->addTable('tasks');
    $q->addQuery('task_duration, task_duration_type');
    $q->addWhere('task_id = '. $fv);
    $sql_duration = $q->loadList();
    $durnTypes = w2PgetSysVal( 'TaskDurationType' );
    return $sql_duration[0]['task_duration'] ? $sql_duration[0]['task_duration'] . " " . $AppUI->_( $durnTypes[$sql_duration[0]['task_duration_type']]) : '' ;
    }
function strhoursworked( $fv ) {
    global $w2Pconfig, $AppUI;
    $q = new w2p_Database_Query();
    $q->addTable('tasks');
    $q->leftJoin('task_log', 'tl', 'tl.task_log_task = task_id');
    $q->addWhere('task_id = ' . $fv);
    $q->addQuery('task_hours_worked, task_duration_type');
    $q->addQuery('ROUND(SUM(task_log_hours),2) as log_hours_worked');
    $q->addGroup('task_id');
    $sql_hours = $q->loadList();
    $durnTypes = w2PgetSysVal( 'TaskDurationType' );
    $hrs = $sql_hours[0]['task_hours_worked'] + $sql_hours[0]['log_hours_worked'];
    $duration = $sql_hours[0]['task_duration_type'] == 24 ? strrounddays( $hrs/$w2Pconfig['daily_working_hours']) : number_format($hrs) ;
    return $duration ? $duration . " " . $AppUI->_( $durnTypes[$sql_hours[0]['task_duration_type']]) : '' ;
    }
function strpercent( $fv ) {
    return $fv."%";
    }
function strtaskcontacts ( $fv ) {
    global $AppUI, $perms ;
    if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) {
        $q = new w2p_Database_Query();
        $q->addTable('contacts', 'c');
        $q->leftJoin('task_contacts', 'tc', 'tc.contact_id = c.contact_id');
        $q->addWhere('tc.task_id = ' . $fv );
        $q->addQuery('contact_first_name, contact_last_name');
        $sql_contact = $q->loadList();
        $contacts= NULL;
        foreach ( $sql_contact as $c ) {
            $contacts .= ( $contacts ? ', ' : ''). $c['contact_first_name'] . " " . $c['contact_last_name'] ;
            }
        return $contacts ;
        }
    return '';
    }
function strassignees( $fv ) {
    $q = new w2p_Database_Query;
    $q->addTable('user_tasks');
    $q->addQuery('user_id');
    $q->addWhere('task_id = '.$fv);
    $sql_user = $q->loadColumn();
    $users = null;
    foreach ( $sql_user as $uid )
        $users .= ( $users ? ', ' : '') . w2PgetUsernameFromID( $uid ) ;
    return $users ;
    }
function strtaskdepts( $fv ) {
    global $AppUI, $perms ;
    if ($AppUI->isActiveModule('departments') && $perms->checkModule('departments', 'view')) {
        $q = new w2p_Database_Query();
        $q->addTable('task_departments');
        $q->leftjoin('departments', 'd', 'department_id = d.dept_id');
        $q->addQuery('dept_name');
        $q->addWhere('task_id = '. $fv );
        return implode(', ', $q->loadColumn());
    } else {
        return '' ;
        }
    }
function strlastactivity( $fv ) {
    $q = new w2p_Database_Query();
    $q->addTable("task_log");
    $q->addQuery("task_log_date");
    $q->addWhere("task_log_task = $fv");
    $q->addOrder("task_log_date DESC");
    $q->setLimit(1);
    $result = $q->loadResult();
    return strdate($result) ;
    }
/*
*     D. LOG SPECIFIC FUNCTIONS
*/
function strhours ( $fv ) {
    global $show_days, $w2Pconfig, $AppUI ;
    $out = $show_days? strrounddays($fv/$w2Pconfig['daily_working_hours']) : $fv ;
    return sprintf( "%.2f", $out) . " " . ( $show_days ? $AppUI->_('days') : $AppUI->_('hours')) ;
    }
/*
*    E. USER SPECIFIC FUNCTIONS
*/
function strassignedtask( $fv ) {
    $q = new w2p_Database_Query();
    $q->addTable('tasks');
    $q->addQuery('task_name');
    $q->addWhere('task_id = ' . $fv );
    return $q->loadResult();
    }

function struserdept( $fv ) {
    $q = new w2p_Database_Query();
    $q->addTable('departments');
    $q->addQuery('dept_name');
    $q->addWhere('dept_id = ' . $fv );
    return $q->loadResult();
    }
function strassignedtime( $fv ) {
    global $w2Pconfig ;
    $nc = strpos( $fv, ',');
    $task_id = substr( $fv, 0, $nc );
    $user_id = substr( $fv, $nc+1 );
    $q = new w2p_Database_Query();
    $q->addTable("user_tasks", "ut");
    $q->addQuery("ut.perc_assignment, t.task_duration, t.task_duration_type");
    $q->addJoin( "tasks", "t", "t.task_id = ut.task_id " );
    $q->addWhere( "ut.user_id = " . $user_id );
    $q->addWhere( "ut.task_id = " . $task_id );
    $q->setLimit(1);
    $result = $q->loadList();
    $hours = (float)$result[0]['task_duration']*$result[0]['perc_assignment']*( $result[0]['task_duration_type'] == '24' ? $w2Pconfig['daily_working_hours'] : 1 )/100;
    return strhours( $hours ) ;
    }
function strallocatedtime( $fv ) {
    $nc = strpos( $fv, ',');
    $task_id = substr( $fv, 0, $nc );
    $user_id = substr( $fv, $nc+1 );
    $q = new w2p_Database_Query();
    $q->addTable("user_tasks", "ut");
    $q->addQuery("SUM(l.task_log_hours)");
    $q->addJoin("task_log", "l", "l.task_log_task = ut.task_id " );
    $q->addWhere("ut.user_id = " . $user_id );
    $q->addWhere("ut.task_id = " . $task_id );
    $hours = (float)$q->loadResult();
    return strhours( $hours ) ;
    }

/*
*     Utility functions used to build the tree of parent/children tasks
*/
function sortByParentTask( $sel_record_data, $all_record_data ) {
global $recordDisplayIndex, $task_array, $output_array ;
/*
*    Function include_task
*         if parent task : put in array current task and all task children
*         if not include parent task
*/
    function include_task( $task_id ) {
        global $task_array ;

        if ( $task_id == $task_array[$task_id]['parent'] ) {
            // the current task is a root task => display task and selected subtree
            $task_tree = include_subtree( $task_id, 0 );
            foreach ( $task_tree as $tid => $level )
                include_records( $tid, $level);
        } else {
            $parent_id = $task_array[$task_id]['parent'];
            if ( ! isset($task_array[$parent_id]) ) {
                // the query contains a JOIN that is not fit by the parent task => include only the current task
                include_records( $task_id, 0 );
            } else {
                // Task has a parent => display parent task and its subtree
                include_task( $parent_id );
                }
            }
        return ;
        }
/*
*     Function include subtree
*         include all children tasks of a parent task
*/
    function include_subtree( $parent_id, $level ) {
        global $task_array ;
        // Retrieve all child tasks
        $include = array() ;
        $child_tasks = array();

        foreach ( $task_array as $tid => $tarr )
            if ( $tarr['parent'] == $parent_id && $tarr['parent'] != $tid )
                $child_tasks[] = $tid ;

        foreach( $child_tasks as $child )
                $include = $include + include_subtree( $child, $level+1 );

        if ( $task_array[$parent_id]['selected'] || count($include) )
            $include = array($parent_id => $level) + $include ;

        return $include ;
        }
/*
*     Function include_records
*         include task_id and parent level for display
*/
    function include_records( $task_id, $level ) {
        global $recordDisplayIndex, $task_array ;

        $record_list = $task_array[$task_id]['indexes'];
        $task_record = $task_array[$task_id]['selected'];
        if ( $task_record ) {
            // display all selected records
            foreach ( $record_list as $index )
                $recordDisplayIndex[] = array( $index, 1, $level ) ;
        } else {
            // Display first record in all record data
            $recordDisplayIndex[] = array( $task_array[$task_id]['all_record_index'], 0, $level ) ;
            }
        $task_array[$task_id]['is_displayed'] = 1 ;
        }

/*
*    Function main body
*/

// task_array[task_id] contains selected record indexes and flags for task_id
$task_array = array();
$recordDisplayIndex = array();
$output_array = array();

for ( $i=0 ; $i<count($all_record_data) ; $i++ ) {
    $tid = $all_record_data[$i]['task_id'];
    if ( isset( $task_array[$tid] ) )
        // already done
        continue;
    $task_array[$tid]['is_displayed'] = 0 ;
    $task_array[$tid]['parent'] = $all_record_data[$i]['task_parent'];
    $task_array[$tid]['all_record_index'] = $i ;
    // Find all records containing data on this task
    $record_indexes = array();
    for ( $j=0; $j<count($sel_record_data); $j++ )
        if ( $sel_record_data[$j]['task_id'] == $tid ) {
            $record_indexes[] = $j ;
            }
    $task_array[$tid]['selected']= count( $record_indexes ) ;
    $task_array[$tid]['indexes'] = $record_indexes;
    }

// Build recursively the tlist of tasks to be displayed
for ( $i=0 ; $i<count($sel_record_data); $i++ )
    if ( ! $task_array[$sel_record_data[$i]['task_id']]['is_displayed'] )
        include_task( $sel_record_data[$i]['task_id'] );

// Build the output array = all record data in appropriate order for display
foreach ( $recordDisplayIndex as $index ) {
    $task_record = $index[1];
    $record = $task_record? $sel_record_data[$index[0]] : $all_record_data[$index[0]];
    $level = $index[2];
    $record['level'] = $level;
    if ( isset($record['task_name']))
        $record['task_name'] = $level ? str_repeat( "  ", $level ) . "- " . $record['task_name'] : $record['task_name'];
    $output_array[] = $record ;
    }

return $output_array ;
}
/*
*    Function used to build allocated/assigned time per period
*         @param    all selected records
*         @param    field name to be used (Allocated time vs. Assigned time)
*         @param    start date of display period
*         @param    end date of display period
*         @param    flag if set non working days are hidden
*/
function putUserTimePerPeriod( $record_data, $field, $period_start_date, $period_end_date, $hideNonWorkingDays = true ) {
    global $AppUI, $df, $show_days;
/*
*    Function buildArrayHeader
*         returns an array of date objects that define the start dates of each period + last entry = end of period date
*         @param    period start date
*         @param     period end date
*        @param    flag if set non working days are not shown
*/
    function buildArrayHeader( $period_start_date, $period_end_date, $hideNonWorkingDays = true ) {
//        echo "Build Headers ( $period_start_date, $period_end_date, $hideNonWorkingDays )<br>" ;
        $sd = new CDate($period_start_date);
        if ( $hideNonWorkingDays )
            $sd->next_working_day();
        $sd->setTime( 0, 0, 0 );
        $ed = new CDate($period_end_date);
        $ed->addDays(1);
        $ed->setTime( 0, 0, 0 ) ;
        $days = $ed->dateDiff($sd);
        $headers = array();
        if ( $days <= 12 ) {
            $day_diff = 1 ;
        } else
            if ( $days <= 68) {
                $day_diff = 7 ;
                $date = Date_Calc::beginOfWeek( $sd->day, $sd->month, $sd->year );
                $sd = new CDate($date);
            } else {
                $day_diff = 30 ;
                }
        while ( $sd->before($ed) ) {
            $headers[] = $sd ;
            if ( $day_diff < 30 ) {
                $sd->addDays( $day_diff );
            } else {
                $sd->addMonths(1);
                }
            if ( $hideNonWorkingDays )
                $sd->next_working_day();
            }
        $headers[count($headers)] = $ed;
        return $headers ;
        }
/*
*    Function putTimeArray
*         returns an array of time allocated/assigned per period
*         @param array
*                     [0] = number of hours allocated/assigned during the period from
*                     [1] = start date
*                     [2] = end date
*         @param array start dates of each time slots to be considered
*/
    function putTimeArray( $timeline, $arrHeader ) {
        global $df ;
//        echo "Call putTimeArray <br> Timeline = " ;
//        print_r($timeline);
//        echo "<br>";
        $time = $timeline[0];
        $sd = new CDate($timeline[1]);
        $ed = new CDate($timeline[2]);
        $dateMin = $arrHeader[0];
        $dateMax = $arrHeader[count($arrHeader)-1] ;
        // init output array
        $output = array();
        for ( $i=0 ; $i<count($arrHeader)-1 ; $i++ )
            $output[$arrHeader[$i]->format($df)] = 0 ;
        $days_per_cell = round( $dateMax->dateDiff($dateMin) / ( count($arrHeader) - 1 )) ;
        $day_diff = $ed->dateDiff( $sd );
        $current_day = new CDate($ed);
        $working_days = 0 ;
        for ( $i=0 ; $i<=$day_diff; $i++) {
            if ( $current_day->isWorkingDay() )
                $working_days++ ;
            $current_day->addDays(1);
            }
        if ( ! $working_days )
            $working_days = 1 ;
        $time_per_day = (float)$time/$working_days ;
//        echo "PutTimeArray time/day = " . number_format($time_per_day, 2 ) . "<br>";
        $sd = $sd->before( $dateMin ) ? $dateMin : $sd ;
        $ed = $ed->after( $dateMax ) ?  $dateMax : $ed ;
        $ed->setTime( 23, 59, 59);
        $i = 0 ;
        while ( $sd->before($ed) && $i<count($arrHeader)-1 ) {
            $date = new CDate( $arrHeader[$i+1] ) ;
            $date->addDays(-1);
            $date->setTime( 23, 59, 59 );
            $index = $arrHeader[$i]->format($df);
            while ( $sd->before($date) && $sd->before($ed) ) {
                if ( $sd->isWorkingDay() ) {
                    $output[$index] += $time_per_day ;
//                    echo "Add time for current date = " . $sd->format( FMT_DATETIME_MYSQL ) . " Current index = " . $arrHeader[$i]->format( FMT_DATETIME_MYSQL ) . " Start next period = " . $date->format( FMT_DATETIME_MYSQL ) . "<br>";
                    }
                $sd->addDays(1);
                }
            $i++ ;
            }
//        echo "PutTimeArray result = ";
//        print_r ($output);
//        echo "<br>";
        return $output ;
        }
/*
*    Function getALlocatedTime
*         returns the allocated effort in hours (as per task logs))
*         @param    user ID
*         @param    task ID
*/
    function getAllocatedTime ( $user_id, $task_id ) {
        $q = new w2p_Database_Query();
        $q->addTable( 'task_log' );
        $q->addQuery( 'task_log_hours, task_log_date, task_start_date');
        $q->addJoin( 'tasks', 't', 'task_log_task = t.task_id' );
        $q->addWhere( 'task_log_creator = ' . $user_id );
        $q->addWhere( 'task_log_task = ' . $task_id );
        $result = $q->loadList();
        $output = array();
        if ( count($result) ) {
            $prev_date = $result[0]['task_start_date'] ;
            foreach ( $result as $res ) {
                $output[]= array( $res['task_log_hours'], $prev_date, $res['task_log_date'] );
                $prev_date = $res['task_log_date'];
                }
            }
        return $output ;
        }
/*
*    Function getAssignedTime
*         returns the assigned effort in hours (as per task assignment)
*         @param user ID
*         @param task ID
*/
    function getAssignedTime ( $user_id, $task_id ) {
        global $w2Pconfig, $AppUI ;
        $q = new w2p_Database_Query();
        $q->addTable("user_tasks", "ut");
        $q->addQuery("ut.perc_assignment, t.task_duration, t.task_duration_type, t.task_start_date, t.task_end_date");
        $q->addJoin( "tasks", "t", "t.task_id = ut.task_id " );
        $q->addWhere( "ut.user_id = " . $user_id );
        $q->addWhere( "ut.task_id = " . $task_id );
        $q->setLimit(1);
        $result = $q->loadList();
        $hours = (float)$result[0]['task_duration']*$result[0]['perc_assignment']*( $result[0]['task_duration_type'] == '24' ? $w2Pconfig['daily_working_hours'] : 1 )/100;
//        echo "Get Assigned time task ID = " . $task_id . " Hours = " . $hours . "<br>";
        return array( array($hours, $result[0]['task_start_date'], $result[0]['task_end_date']) ) ;
        }
/*
*    Function main body
*/
//    echo "Start user time  ( $field, $period_start_date, $period_end_date ) <br>" ;
    $output = array();
    $user_time = array();
    $task_usage = array();
    $level = 0 ;
    $header = buildArrayHeader( $period_start_date, $period_end_date, $hideNonWorkingDays );
    switch ( $field ) {
        case 1 :
            $calc_function = 'getAllocatedTime' ;
            break;
        case 2 :
            $calc_function = 'getAssignedTime' ;
            break;
        default :
            $AppUI->setMsg("Unknown user time field", UI_MSG_ERROR);
            return $record_data ;
        }

    for ( $i=0 ; $i<count($record_data); $i++ ) {
        $user_id = $record_data[$i]['user_id'];
        $task_id = $record_data[$i]['task_id'];
        if ( $user_id && $task_id ) {
            $time_line = $calc_function( $user_id, $task_id );
            foreach ( $time_line as $v ) {
                $result = putTimeArray( $v, $header );
                foreach ( $result as $d => $u )
                    $user_time[$i][$d] += $u ;
                }
        } else {
            $AppUI->setMsg(" Invalid User_id/Task_id : $user_id / $task_id -", UI_MSG_ERROR, true);
            }
        }

    for ( $i=0; $i<count( $record_data ); $i++ ) {
        $temp = $record_data[$i] ;
        if ( isset($user_time[$i]) ) {
            foreach ( $user_time[$i] as $d => $v )
                $temp[$d] = $v ;
            }
        $output[] = $temp ;
        }
//echo "PutUserTimePerPeriod returns " . count($output) . " records <br />";
    return $output ;
    }
/*
*     Functions used to generate CSV files
*         new_CSV        : table initialization
*         line_CSV    : generates a new line in the table
*         output_CSV    : generates a temp file
*/
class Cw2Pcsv {
    var $lines = NULL ;            // array containing the lines in the CSV file
    var $numline = NULL ;        // index in $lines
    var $ncols = NULL ;            // number of columns in the CSV file
    var $separator = NULL;        // separator (default is ; compatible with French version of Excel)

    function Cw2Pcsv($nc, $char=";" ) {
        $this->lines = array();
        $this->numline = 0;
        $this->ncols = $nc;
        $this->separator = $char ;
        }

    function w2PcsvLine( $newline ) {
        $out= '';
        // delete CR and LF
        $newline  = str_replace(array("\r\n", "\r", "\n")," ",$newline);
        if (is_array($newline)) {
            $out = implode( $this->separator, $newline);
            $nsc = $this->ncols-(count($newline)-1);
        }  else {
               $out = $newline;
            $nsc = $this->ncols;
               }
        $out.= $this->separator ;
        while ( --$nsc > 0 )
            $out .= $this->separator;
        $this->lines[$this->numline] = $out;
        $this->numline++;
        }

    function w2PcsvStroke( $filename ) {
    global $AppUI ;
    $temp_dir = w2PgetConfig( 'root_dir' )."/files/temp";
    $base_url  = w2PgetConfig( 'base_url' );
    if ($fp = fopen( "$temp_dir/temp$AppUI->user_id.csv", 'wb' )) {
        fwrite( $fp, implode( "\n", $this->lines ));
        fclose( $fp );
        $string = "<a href=\"$base_url/files/temp/$filename\" target=\"_blank\">";
        $string .= $AppUI->_( 'ViewFileOrSave' );
        $string .= "</a>";
    } else {
        $string = "Could not open file to save CSV.  ";
        if (!is_writable( $temp_dir ))
            $string .= "The files/temp directory is not writable.  Check your file system permissions.";
        }
    return $string ;
    }
}
/*
*     End of CSV class definition
*/
/*
*    Functions on Cezpdf class
*
*/
require( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );
global $w2Pformat ;
/*
*    Cw2Ppdf : Initialization standard orientation and margins
*         @param = paper size
*         @param = orientation 'landscape' or 'portrait'
*     return Cezpdf class object
* 
*/
    function Cw2Ppdf( $paper='A4', $orientation='landscape') {
        global $w2Pformat;
        $pdf = new Cezpdf( $paper, $orientation );
        $w2Pformat = $orientation == 'landscape' ;
        if ( $orientation = 'landscape' ) {
            $pdf->ezSetCmMargins( 3.25, 2, 1.5, 1.5 );
        } else {
            $pdf->ezSetCmMargins( 3.25, 2, 1.5, 1.5 );
            }
        echo " End Cw2Ppdf <br>";
        return $pdf ;
        }
/*
*    w2PpdfSetPage    Define page header and footer
*         @param = Cezpdf class object
*         @param = document title (string))
*         @param = company name (string)
*         @param = documentation sub-title (string) : main filter
* 
*/
    function w2PpdfSetPage( $pdf, $doc_title, $company_name, $doc_subtitle='' ) {
        global $w2Pformat;
        /*
        *         Define page header and footer
        */
        echo "Debut w2PpdfSetPage <br>";
        $page_header = $pdf->openObject();
        $pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
        // Document title middle
        $toto = $pdf->getFontHeight(12);
        echo "Bold font height = " . $toto ."<br>";
        $ypos=$pdf->ez['pageHeight'] - ( 30 + $pdf->getFontHeight(12) );
        $pwidth=$pdf->ez['pageWidth'];
        echo "Text width = " . Cpdf::getTextWidth( 12, $doc_title ) . " Page width = " . $pwidth . "<br>" ;
        $xpos= round( ($pwidth - $pdf->getTextWidth( 12, $doc_title ))/2, 2 );
        echo "x = ". $xpos . " y = " . $ypos . " text = " . $doc_title . "<br>" ;
        $pdf->addText( $xpos, $ypos, 12, $doc_title) ;
        // Company name left
        $pdf->selectFont( "$font_dir/Helvetica.afm" );
        $pdf->addText( round( $pdf->ez['leftMargin'], 2 ), $ypos, 10, $company_name );
        // Current date right
        $date = new CDate();
        $xpos = round( $pwidth - $pdf->getTextWidth( 10, $date->format($df)) - $pdf->ez['rightMargin'] , 2);
        $pdf->addText( $xpos, $ypos, 10, $date->format( $df ) );
        $ypos = $ypos - round ( 1.2*$pdf->getFontHeight(12) , 2 ) ;
        if ( $doc_subtitle ) {
            $pdf->ezText( strEzPdf( $doc_subtitle ), 12 ) ;
            $ypos -= round ( 1.2*$pdf->getFontHeight(12) , 2 ) ;
            }
        $pdf->ezSetY( $ypos );
        echo "SetY = " . $ypos . " Font height = " . $pdf->getFontHeight(12) ;
        $pdf->closeObject($page_header);
        $pdf->addObject($page_header, 'all');
        // End of page header definition
        $xpos = $pdf->w2Pformat ? 770 : 550 ;
        $pdf->ezStartPageNumbers( $xpos , 30 , 8 ,'right','Page {PAGENUM}/{TOTALPAGENUM}') ;
        echo "Fin w2PpdfSetPage <br>";
        }
/*
*     w2PpdfOutput
*        Create pdf file
*         @param CezPdf class object
*         reurn string HTML link to the generated file or error message
* 
*/
    function w2PpdfOutput( $pdf ) {
        global $AppUI;
        echo "Debut w2PpdfOutput <br>";
        $temp_dir = w2PgetConfig( 'root_dir' )."/files/temp";
        $base_url  = w2PgetConfig( 'base_url' );
        if ($fp = fopen( "$temp_dir/temp$AppUI->user_id.pdf", 'wb' )) {
            fwrite( $fp, $$pdf->ezOutput() );
            fclose( $fp );
            $string = "<a href=\"$base_url/files/temp/temp$AppUI->user_id.pdf\" target=\"pdf\">";
            $string .= $AppUI->_( "View PDF File" );
            $string .= "</a>";
        } else {
            $string = "Could not open file to save PDF.  ";
            if (!is_writable( $temp_dir ))
                $string .= "The files/temp directory is not writable.  Check your file system permissions.";
            }
        return $string ;
        }

/*
*     Convert to UTF_8 (recursive)
*/
    function conv2utf8( $text ) {
    if ( !is_array( $text ) ) return utf8_encode( $text );
    $arr = array();
    foreach ( $text as $key => $t ) $arr[$key] = conv2utf8( $t );
    return $arr ;
    }