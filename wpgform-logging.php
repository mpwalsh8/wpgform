<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 *  Copyright 2012 Mike Walsh - mpwalsh8@gmail.com
 *
 *  This code is derived from the Custom List Table Example plugin.
 *
 *  @see http://codex.wordpress.org/Class_Reference/WP_List_Table
 *  @see http://wordpress.org/extend/plugins/custom-list-table-example/
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
?>

<?php 
    if (!current_user_can('manage_options')) {
        wp_die(printf('<div class="error fade"><p>%s</p></div>',
            __('You are not allowed to view the submission log.', WPGFORM_I18N_DOMAIN)));
    } 

/*************************** LOAD THE BASE CLASS *******************************
 *******************************************************************************
 * The WP_List_Table class isn't automatically available to plugins, so we need
 * to check if it's available and load it if necessary.
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/************************** CREATE A PACKAGE CLASS *****************************
 *******************************************************************************
 * Create a new list table package that extends the core WP_List_Table class.
 * WP_List_Table contains most of the framework for generating the table, but we
 * need to define and override some methods so that our data can be displayed
 * exactly the way we need it to be.
 * 
 * To display this example on a page, you will first need to instantiate the class,
 * then call $yourInstance->prepare_items() to handle any data manipulation, then
 * finally call $yourInstance->display() to render the table to the page.
 * 
 */
class wpGForms_List_Table extends WP_List_Table {
    
    /** ************************************************************************
     * REQUIRED. Set up a constructor that references the parent constructor. We 
     * use the parent reference to set some default configs.
     ***************************************************************************/
    function __construct() {
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'form',     //singular name of the listed records
            'plural'    => 'forms',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) );
        
    }
    
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'last_name', it would first see if a method named $this->column_last_name() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_last_name() method later on, this method doesn't
     * need to concern itself with any column with a name of 'last_name'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default($item, $column_name) {
        switch($column_name){
            case 'timestamp':
            case 'url':
            case 'remote_addr':
            case 'remote_host':
            case 'http_referer':
            case 'http_user_agent':
            case 'form':
            case 'post_id':
                return $item->$column_name;
            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'last_name'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_last_name} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (last_name only)
     **************************************************************************/
    function column_timestamp($item) {
        
        //Build row actions
        $actions = array(
		//    'edit' => sprintf('<a href="%s%s%s">Edit User Profile</a>',
		//        get_admin_url(), 'user-edit.php?user_id=',$item->ID),
		    'delete' => sprintf('<a href="%s%s%s">Delete Log Entry</a>',
                get_admin_url(), 'edit.php?post_type=' . WPGFORM_CPT_FORM .
                '&page=wpgform-entry-log-page&action=delete&form=', $item->form),
        );
        
        //Return the last_name contents
        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $item->timestamp,
            /*$2%s*/ $item->form,
            /*$3%s*/ $this->row_actions($actions)
        );
    }
    
    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("last_name")
            /*$2%s*/ $item->form                //The value of the checkbox should be the record's id
        );
    }
    
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'               => '<input type="checkbox" />', //Render a checkbox instead of text
            'timestamp'        => __('Timestamp', WPGFORM_I18N_DOMAIN),
            'url'              => __('URL', WPGFORM_I18N_DOMAIN),
            'remote_addr'      => __('Remote Addr', WPGFORM_I18N_DOMAIN),
            'remote_host'      => __('Remote Host', WPGFORM_I18N_DOMAIN),
            'http_referer'     => __('HTTP Refer', WPGFORM_I18N_DOMAIN),
            'http_user_agent'  => __('HTTP User Agent', WPGFORM_I18N_DOMAIN),
            //'form'             => __('wpGForm Id', WPGFORM_I18N_DOMAIN),
            //'post_id'          => __('Post Id', WPGFORM_I18N_DOMAIN)
        );
        return $columns;
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'timestamp'       => array('timestamp',true),     //true means its already sorted
            'url'             => array('url',false),
            'remote_addr'     => array('remote_addr',false),
            'remote_host'     => array('remote_host',false),
            'http_referer'    => array('http_referer',false),
            'http_user_agent' => array('http_user_agent',false),
            //'form'            => array('form',false),
            //'post_id'         => array('post_id',false)
        );
        return $sortable_columns;
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete' => __('Delete', WPGFORM_I18N_DOMAIN)
        );
        return $actions;
    }
    
    
    /** ************************************************************************
     * Optional. You can handle your bulk actions anywhere or anyhow you prefer.
     * For this example package, we will handle it in the class to keep things
     * clean and organized.
     * 
     * @see $this->prepare_items()
     **************************************************************************/
    function process_bulk_action() {
        
        $c = 0 ;
        $actions = $this->get_bulk_actions() ;

        if (($this->current_action() !== false) && array_key_exists('form', $_GET))
        {
            /* -- Fetch the items -- */
            $raw_meta_values = $this->get_meta_key_values(WPGFORM_LOG_ENTRY_META_KEY, '', -1);

            //  Since the log entries are serialized by WordPress' meta process,
            //  they need to be unserialized and turned into the proper array of
            //  objects before they can be used identify the proper meta value.

            $meta_values = array() ;

            foreach ($raw_meta_values as $raw_meta_value)
            {
                $meta_value = wpGFormArrayToObject(array_merge(maybe_unserialize($raw_meta_value->mv),
                    array('meta_value' => $raw_meta_value->mv))) ;
                $meta_values[$meta_value->form] = $meta_value ;
            }

            //  Make sure we handle the case where the form id isn't in an array

            $forms = is_array($_GET['form']) ? $_GET['form'] : array($_GET['form']) ;

            //  Loop through the form ids and delete the entries which match

            foreach ($forms as $form)
            {
                switch ($this->current_action())
                {
                    case 'delete':
                        $c++ ;
                        delete_post_meta($meta_values[$form]->form,
                            WPGFORM_LOG_ENTRY_META_KEY, maybe_unserialize($meta_values[$form]->meta_value)) ;
                        break ;
                }
            }

            printf('<div class="updated fade"><h4>%s completed for %d log entr%s.</h4></div>',
                $actions[$this->current_action()], $c, $c == 1 ? 'y' : 'ies') ;
        }
        else if ($this->current_action() !== false)
        {
            printf('<div class="error fade"><h4>%s.</h4></div>',
                __('No log entries selected', WPGFORM_I18N_DOMAIN)) ;
        }

        $_SERVER['REQUEST_URI'] = remove_query_arg( array( 'delete', 'page', 'post_type',
            'action', 'action2', 'form', '_wp_http_referer', '_wp_nonce' ), $_SERVER['REQUEST_URI'] );
    }
    
    /** ************************************************************************
     * REQUIRED! This is where you prepare your data for display. This method will
     * usually be used to query the database, sort and filter the data, and generally
     * get it ready to be displayed. At a minimum, we should set $this->items and
     * $this->set_pagination_args(), although the following properties and methods
     * are frequently interacted with here...
     * 
     * @uses $this->_column_headers
     * @uses $this->items
     * @uses $this->get_columns()
     * @uses $this->get_sortable_columns()
     * @uses $this->get_pagenum()
     * @uses $this->set_pagination_args()
     **************************************************************************/

    function prepare_items() {
        global $wpdb, $_wp_column_headers;
        $screen = get_current_screen() ;
        
        /**
         * First, lets decide how many records per page to show
         */
        //$per_page = wpgform_get_user_settings_table_rows() ;
        $per_page = 10 ;

        if ($per_page === false) $per_page = 10 ;

        /* -- Pagination parameters -- */
        //Number of elements in your table?
        $totalitems = $this->get_meta_key_count(WPGFORM_LOG_ENTRY_META_KEY);

        //Which page is this?
        $paged = !empty($_GET['paged']) ? mysql_real_escape_string($_GET['paged']) : '';

        //Page Number
        if (empty($paged) || !is_numeric($paged) || $paged <= 0 ) $paged=1;

        //How many pages do we have in total?
        $totalpages = ceil($totalitems[0]->count/$per_page);

        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            'total_items' => $totalitems[0]->count,
            'total_pages' => $totalpages,
            'per_page' => $per_page,
            'post_type' => WPGFORM_CPT_FORM,
            'page' => 'wpgform-entry-log-page'
        ) );
        //The pagination links are automatically built according to those parameters
 
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        
        /**
         * Optional. You can handle your bulk actions however you see fit. In this
         * case, we'll handle them within our package just to keep things clean.
         */
        $this->process_bulk_action();
        
        /* -- Fetch the items -- */
        $raw_meta_values = $this->get_meta_key_values(WPGFORM_LOG_ENTRY_META_KEY, $paged, $per_page);

        //  Since the log entries are serialized by WordPress' meta process,
        //  they need to be unserialized and turned into the proper array of
        //  objects before they can be used to construct the table.

        foreach ($raw_meta_values as $raw_meta_value)
            $this->items[] = wpGFormArrayToObject(array_merge(maybe_unserialize($raw_meta_value->mv),
                array('meta_value' => $raw_meta_value->mv))) ;
    }

    function get_meta_key_values($key = '', $paged = '', $per_page = 10, $type = 'wpgform', $status = 'publish')
    {
        global $wpdb;

        if (empty($key)) return ;

        //adjust the query to take pagination into account

        if(!empty($paged) && !empty($per_page))
        {
            $offset=($paged-1)*$per_page;

            $r = $wpdb->get_results($wpdb->prepare( "
                SELECT pm.meta_value AS mv FROM {$wpdb->postmeta} pm
                LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = '%s'
                AND pm.meta_value != '' 
                AND p.post_type = '%s'
                GROUP BY pm.meta_id
                ORDER BY pm.meta_id          
                LIMIT %d,%d
                ", $key, $type, (int)$offset, (int)$per_page)
            );
        }
        else
        {
            $r = $wpdb->get_results($wpdb->prepare( "
                SELECT pm.meta_value AS mv FROM {$wpdb->postmeta} pm
                LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = '%s'
                AND pm.meta_value != '' 
                AND p.post_type = '%s'
                GROUP BY pm.meta_id
                ORDER BY pm.meta_id          
                ", $key, $type)
            );
        }

        return $r;
    }
    
    /**
     * Determine how many post meta values there for the specified key
     *
     * @param $Key string meta key
     * @param $type string post type
     * @param $status optional string status of post
     * @return mixed count of post meta values
     */
    function get_meta_key_count($key = '', $type = 'wpgform', $status = 'publish') {
        global $wpdb;

        if (empty($key)) return ;

        $r = $wpdb->get_results(  $wpdb->prepare( "
            SELECT count(*) AS count FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key = '%s'
            AND pm.meta_value != '' 
            AND p.post_type = '%s'
            ", $key, $type)
        );

        return $r;
    }
}


/***************************** RENDER PAGE CONTENT ********************************
 **********************************************************************************
 * This function renders the admin page and the example list table. Although it's
 * possible to call prepare_items() and display() from the constructor, there
 * are often times where you may need to include logic here between those steps,
 * so we've instead called those methods explicitly. It keeps things flexible, and
 * it's the way the list tables are used in the WordPress core.
 */
function wpgform_render_list_page(){
    
    //Create an instance of our package class...
    $wpgformListTable = new wpGForms_List_Table();

    //Fetch, prepare, sort, and filter our data...
    $wpgformListTable->prepare_items();

    ?>
    <div class="wrap">
        
        <div id="icon-users" class="icon32"><br/></div>
        <h2>WordPress Google Form Submission Log</h2>
        
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="wpgform-log-entries-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="post_type" value="<?php echo WPGFORM_CPT_FORM ?>" />
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <input type="hidden" name="_wp_http_referer" value="<?php echo admin_url('edit.php?post_type=' . WPGFORM_CPT_FORM .  '&amp;page=wpgform-entry-log-page' ); ?>" />
            <!-- Now we can render the completed list table -->
            <?php //$wpgformListTable->search_box(__('Search', WPGFORM_I18N_DOMAIN), 'search_id'); ?>
            <?php $wpgformListTable->display() ; ?>
        </form>
        
    </div>
    <?php
}

/**
 * Map an array into a standard PHP object
 *
 * @param $d array
 * @return stdObject
 */
function wpGFormArrayToObject($d)
{
    if (is_array($d)) {
        /*
         * Return array converted to object
         * Using __FUNCTION__ (Magic constant)
         * for recursive call
         */
        return (object) array_map(__FUNCTION__, $d);
    }
    else {
        // Return object
        return $d;
    }
}

//  Render the page ...
wpgform_render_list_page() ;
?>
