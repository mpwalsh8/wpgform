<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 *
 * post-type-extensions.php template file
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @package wpGForm
 * @subpackage post-types
 * @version $Revision$
 * @lastmodified $Author$
 * @lastmodifiedby $Date$
 *
 */

// WordPress Google Form Plugin 'Team' Custom Post Type
define('WPGFORM_CPT_FORM', 'wpgform') ;
define('WPGFORM_CPT_QV_FORM', WPGFORM_CPT_FORM . '_qv') ;
define('WPGFORM_CPT_SLUG_FORM', WPGFORM_CPT_FORM . 's') ;

/** Set up the post type(s) */
add_action('init', 'wpgform_register_post_types') ;
//add_action('init', 'wpgform_register_taxonomies') ;

/** Register post type(s) */
function wpgform_register_post_types()
{
    /** Set up the arguments for the WPGFORM_CPT_FORM post type. */
    $wpgform_args = array(
        'public' => true,
        'query_var' => WPGFORM_CPT_QV_FORM,
        'has_archive' => false,
        'rewrite' => array(
            'slug' => WPGFORM_CPT_SLUG_FORM,
            'with_front' => false,
        ),
        'supports' => array(
            'title',
            //'thumbnail',
            //'editor',
            'excerpt'
        ),
        'labels' => array(
            'name' => 'Google Forms',
            'singular_name' => 'Google Form',
            'add_new' => 'Add New Google Form',
            'add_new_item' => 'Add New Google Form',
            'edit_item' => 'Edit Google Form',
            'new_item' => 'New Google Form',
            'view_item' => 'View Google Form',
            'search_items' => 'Search Google Forms',
            'not_found' => 'No Google Forms Found',
            'not_found_in_trash' => 'No Google Forms Found In Trash'
        ),
        'menu_icon' => plugins_url('/images/forms-16.png', __FILE__)
    );

    // Register the WordPress Google Form post type
    register_post_type(WPGFORM_CPT_FORM, $wpgform_args) ;
}

//  Build custom meta box support

/**
 * Define the WordPress Google Form Meta Box fields so they can be
 * used to construct the form as well as validate it and save it.
 *
 */
function wpgform_form_meta_box_content()
{
    return array(
        'id' => 'wpgform-meta-box',
        'title' => 'Google Form Details',
        'page' => WPGFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => 'Form URL',
                'desc' => 'The full URL to the published Google Form.',
                'id' => WPGFORM_PREFIX . 'form',
                'type' => 'lgtext',
                'std' => '',
                'required' => true
            ),
            array(
                'name' => 'Confirm URL',
                'desc' => 'The full URL to the optional Confirmation Page.',
                'id' => WPGFORM_PREFIX . 'confirm',
                'type' => 'lgtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => 'Style',
                'desc' => 'Custom Confirmation Page Style',
                'id' => WPGFORM_PREFIX . 'style',
                'type' => 'select',
                'options' => array('None', 'Redirect', 'AJAX'),
                'required' => false,
                'br' => true
            ),
            array(
                'name' => 'Alert',
                'desc' => 'Javascript Alert Box message displayed upon submission',
                'id' => WPGFORM_PREFIX . 'alert',
                'type' => 'lgtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => 'Class',
                'desc' => 'CSS class(es) to add to the form\'s containing DIV',
                'id' => WPGFORM_PREFIX . 'class',
                'type' => 'medtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => 'Columns',
                'desc' => 'Number of columns to render form',
                'id' => WPGFORM_PREFIX . 'columns',
                'type' => 'select',
                'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
                'std' => '1',
                'required' => true
            ),
            array(
                'name' => 'Legal',
                'desc' => 'Google Legal Disclaimer',
                'id' => WPGFORM_PREFIX . 'legal',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'Read Only',
                'desc' => 'Set the form Read Only',
                'id' => WPGFORM_PREFIX . 'readonly',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'BR',
                'desc' => 'Insert &lt;BR&gt; tags between labels and input box',
                'id' => WPGFORM_PREFIX . 'br',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'CSS Prefix',
                'desc' => 'Prefix to add to all Google CSS classes',
                'id' => WPGFORM_PREFIX . 'css_prefix',
                'type' => 'smtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => 'CSS Suffix',
                'desc' => 'Suffix to add to all Google CSS classes',
                'id' => WPGFORM_PREFIX . 'css_suffix',
                'type' => 'smtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => 'Title',
                'desc' => 'Show or Hide the Google Form\'s title',
                'id' => WPGFORM_PREFIX . 'title',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'Map H1 to H2',
                'desc' => 'Map the Form\'s H1 elements to H2 elements',
                'id' => WPGFORM_PREFIX . 'maph1h2',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'Email',
                'desc' => 'Send email upon form submission',
                'id' => WPGFORM_PREFIX . 'email',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'Send To',
                'desc' => 'E-mail Address send submission email to',
                'id' => WPGFORM_PREFIX . 'sendto',
                'type' => 'medtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => 'Results',
                'desc' => 'The full URL to the published Results Page or Google Spreadsheet.',
                'id' => WPGFORM_PREFIX . 'results',
                'type' => 'lgtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => 'Email End User',
                'desc' => 'Send email to end user upon form submission.',
                'id' => WPGFORM_PREFIX . 'user_email',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'Validation',
                'desc' => 'jQuery Validation',
                'id' => WPGFORM_PREFIX . 'validation',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'CAPTCHA',
                'desc' => 'CAPTCHA',
                'id' => WPGFORM_PREFIX . 'captcha',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'Unite Theme Hack',
                'desc' => 'Unite Theme Hack',
                'id' => WPGFORM_PREFIX . 'unitethemehack',
                'type' => 'radio',
                'options' => array('On', 'Off'),
                'required' => false,
                'br' => false
            ),
            array(
                'name' => 'Form CSS',
                'desc' => 'Form specific CSS classes',
                'id' => WPGFORM_PREFIX . 'form_css',
                'type' => 'textarea',
                'std' => '',
                'required' => false
            ),
        )
    ) ;
}

add_action('admin_menu', 'wpgform_add_form_meta_box') ;
//add_action('admin_menu', 'wpgform_add_player_profile_meta_box') ;

// Add form meta box
function wpgform_add_form_meta_box()
{
    $mb = wpgform_form_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpgform_show_form_meta_box', $mb['page'], $mb['context'], $mb['priority']);
}

// Callback function to show fields in meta box
function wpgform_show_form_meta_box()
{
    $mb = wpgform_form_meta_box_content() ;
    wpgform_build_meta_box($mb) ;
}

/**
 * Build meta box form
 *
 * @see http://www.deluxeblogtips.com/2010/04/how-to-create-meta-box-wordpress-post.html
 *
 */
function wpgform_build_meta_box($mb)
{
    global $post;

    // Use nonce for verification
    echo '<input type="hidden" name="' . WPGFORM_PREFIX .
        'meta_box_nonce" value="', wp_create_nonce(plugin_basename(__FILE__)), '" />';

    echo '<table class="form-table">';

    foreach ($mb['fields'] as $field)
    {
        //  Only show the fields which are not hidden
        if ($field['type'] !== 'hidden')
        {
            // get current post meta data
            $meta = get_post_meta($post->ID, $field['id'], true);
    
            echo '<tr>',
                    '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
                    '<td>';
            switch ($field['type']) {
                case 'text':
                case 'lgtext':
                    echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />', '<br />', '<small>', $field['desc'], '</small>';
                    break;
                case 'medtext':
                    echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:47%" />', '<br />', '<small>', $field['desc'], '</small>';
                    break;
                case 'smtext':
                    echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:27%" />', '<br />', '<small>', $field['desc'], '</small>';
                    break;
                case 'textarea':
                    echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>', '<br />', '<small>', $field['desc'], '</small>';
                    break;
                case 'select':
                    echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                    foreach ($field['options'] as $option => $value) {
                        echo '<option ', $meta == strtolower($value) ? ' selected="selected"' : '', 'value="', strtolower($value), '">', $value . '&nbsp;&nbsp;', '</option>';
                    }
                    echo '</select>';
                    echo '<br />', '<small>', $field['desc'], '</small>';
                    break;
                case 'radio':
                    foreach ($field['options'] as $option => $value) {
                        echo '<input type="radio" name="', $field['id'], '" value="', strtolower($value), '"', $meta == strtolower($value) ? ' checked="checked"' : '', ' />&nbsp;', $value, $field['br'] === true ? '<br />' : '&nbsp;&nbsp;';
                    }
                    echo '<br />', '<small>', $field['desc'], '</small>';
                    break;
                case 'checkbox':
                    echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
                    break;
                default :
                    break ;
            }
            echo     '<td>',
                '</tr>';
        }
    }

    echo '</table>';
}

    //wpgform_whereami(__FILE__, __LINE__) ;
add_action( 'quick_edit_custom_box', 'wpgform_add_quick_edit_nonce', 10, 2 );
//add_action( 'quick_edit_custom_box', function() { error_log(__LINE__) ; }, 10, 2 );
    //wpgform_whereami(__FILE__, __LINE__) ;
/**
 * Action to add a nonce to the quick edit form for the custom post types
 *
 */
function wpgform_add_quick_edit_nonce($column_name, $post_type)
{
    //error_log(__LINE__) ;
    //wpgform_whereami(__FILE__, __LINE__) ;
    static $printNonce = true ;

    if ($post_type == WPGFORM_CPT_FORM)
    {
        if ($printNonce)
        {
            $printNonce = false ;
            wp_nonce_field( plugin_basename( __FILE__ ), WPGFORM_PREFIX . 'meta_box_qe_nonce' ) ;
        }
    }
}

add_action('save_post', 'wpgform_save_meta_box_data');
/**
 * Action to save WordPress Google Form meta box data for both
 * team and player Custom Post Types.
 *
 */
function wpgform_save_meta_box_data($post_id)
{
    global $post ;

    // verify nonce - needs to come from either a CPT Edit screen or CPT Quick Edit

    if ((isset( $_POST[WPGFORM_PREFIX . 'meta_box_nonce']) &&
        wp_verify_nonce($_POST[WPGFORM_PREFIX . 'meta_box_nonce'], plugin_basename(__FILE__))) ||
        (isset( $_POST[WPGFORM_PREFIX . 'meta_box_qe_nonce']) &&
        wp_verify_nonce($_POST[WPGFORM_PREFIX . 'meta_box_qe_nonce'], plugin_basename(__FILE__))))
    {
        //wpgform_whereami(__FILE__, __LINE__) ;
        // check for autosave - if autosave, simply return

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        {
            return $post_id ;
        }

        // check permissions - make sure action is allowed to be performed

        if ('page' == $_POST['post_type'])
        {
            if (!current_user_can('edit_page', $post_id))
            {
                return $post_id ;
            }
        }
        elseif (!current_user_can('edit_post', $post_id))
        {
            return $post_id ;
        }

        //  Get the meta box fields for the appropriate CPT and
        //  return if the post isn't a CPT which shouldn't happen

        if (get_post_type($post_id) == WPGFORM_CPT_FORM)
            $mb = wpgform_form_meta_box_content() ;
        else
            return $post_id ;

        //  Loop through all of the fields and update what has changed
        //  accounting for the fact that Short URL fields are always
        //  updated and CPT fields are ignored in Quick Edit except for
        //  the Short URL field.

        //wpgform_preprint_r($_POST) ;
        foreach ($mb['fields'] as $field)
        {
            //  Only update other Post Meta fields when on the edit screen - ignore in quick edit mode

            if (isset($_POST[WPGFORM_PREFIX . 'meta_box_nonce']))
            {
                if (array_key_exists($field['id'], $_POST))
                {
                    $new = $_POST[$field['id']];

                    $old = get_post_meta($post_id, $field['id'], true) ;

                    if ($new && $new != $old)
                    {
                        //wpgform_whereami(__FILE__, __LINE__);
                        update_post_meta($post_id, $field['id'], $new) ;
                    }
                    elseif ('' == $new && $old)
                    {
                        //wpgform_whereami(__FILE__, __LINE__);
                        delete_post_meta($post_id, $field['id'], $old) ;
                    }
                    else
                    {
                        //wpgform_whereami(__FILE__, __LINE__);
                    }
                }
            }
        }
    }
    else
    {
        return $post_id ;
    }
}

/**
 * CPT Update/Edit form
 */
function wpgform_update_edit_form() {  
    echo ' enctype="multipart/form-data"';  
}
add_action('post_edit_form_tag', 'wpgform_update_edit_form');  

// Add to admin_init function
add_filter('manage_edit-wpgform_columns', 'wpgform_add_new_form_columns');

/**
 * Add more columns
 */
function wpgform_add_new_form_columns($cols)
{
    //  The "Title" column is re-labeled as "Form Name"!
    $cols['title'] = __('Form Name', WPGFORM_I18N_DOMAIN) ;

	return array_merge(
		array_slice($cols, 0, 2),
        array(
            WPGFORM_PREFIX . 'shortcode' => __('Short Code', WPGFORM_I18N_DOMAIN),
            WPGFORM_PREFIX . 'excerpt' => __('Form Description', WPGFORM_I18N_DOMAIN),
        ),
        array_slice($cols, 2)
	) ;
}

/**
 * Display custom columns
 */
function wpgform_form_custom_columns($column, $post_id)
{
    switch ($column)
    {
        case WPGFORM_PREFIX . 'excerpt':
            $p = get_post($post_id);
            echo $p->post_excerpt;
            break;

        case WPGFORM_PREFIX . 'shortcode':
            printf('[wpgform id=\'%d\']', $post_id) ;
            break;

        case 'id':
            echo $post_id ;
            break ;
    }
}
add_action('manage_posts_custom_column', 'wpgform_form_custom_columns', 10, 2) ;
 
/**
 * Make these columns sortable
 */
function wpgform_form_sortable_columns()
{
    return array(
        'title' => 'title',
        WPGFORM_PREFIX . 'shortcode' => WPGFORM_PREFIX . 'shortcode',
        WPGFORM_PREFIX . 'excerpt' => WPGFORM_PREFIX . 'excerpt',
        'date' => 'date',
    ) ;
}
add_filter('manage_edit-wpgform_sortable_columns', 'wpgform_form_sortable_columns') ;

/**
 * Set up a footer hook to rearrange the post editing screen
 * for the WPGFORM_CPT_PLAYER custom post type.  The meta box which has all
 * of the custom fields in it will appear before the Visual Editor.
 * This is accomplished using a simple jQuery script once the
 * document is loaded.
 * 
 *
 */
function wpgform_admin_footer_hook()
{
    global $post ;

    if (get_post_type($post) == WPGFORM_CPT_FORM)
    {
?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#normal-sortables').insertBefore('#postdivrich') ;
    }) ;
</script>

<?php
    }
}

/**  Hook into the Admin Footer */
//add_action('admin_footer','wpgform_admin_footer_hook');

/**  Filter to change the Title field for the Player post type */
add_filter('enter_title_here', 'wpgform_enter_title_here_filter') ;

function wpgform_enter_title_here_filter($title)
{
    global $post ;

    if (get_post_type($post) == WPGFORM_CPT_FORM)
        return __('Enter WordPress Google Form Title', WPGFORM_I18N_DOMAIN) ;
    else
        return $title ;
}

/** Filter to show all posts for Position and Roster Taxonomies when viewing an archive */
//add_filter('option_posts_per_page', 'wpgform_option_posts_per_page' );

function wpgform_option_posts_per_page( $value ) {
    return (!is_admin() && (is_tax(WPGFORM_TAX_POSITION ) || is_tax(WPGFORM_TAX_ROSTER))) ? -1 : $value ;
}


/**
 * wpgform_admin_css()
 *
 */
function wpgform_admin_css()
{
    global $post_type;
    if ((array_key_exists('post_type', $_GET) && ($_GET['post_type'] == WPGFORM_CPT_FORM)) || ($post_type == WPGFORM_CPT_FORM))
    {
        wp_enqueue_style('wpgform-admin-css',
            plugins_url(plugin_basename(dirname(__FILE__) . '/css/wpgform-admin.css'))) ;
    }
}
add_action('admin_head', 'wpgform_admin_css');
?>
