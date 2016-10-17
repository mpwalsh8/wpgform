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
            'name' => __('Google Forms', WPGFORM_I18N_DOMAIN),
            'singular_name' => __('Google Form', WPGFORM_I18N_DOMAIN),
            'add_new' => __('Add New Google Form', WPGFORM_I18N_DOMAIN),
            'add_new_item' => __('Add New Google Form', WPGFORM_I18N_DOMAIN),
            'edit_item' => __('Edit Google Form', WPGFORM_I18N_DOMAIN),
            'new_item' => __('New Google Form', WPGFORM_I18N_DOMAIN),
            'view_item' => __('View Google Form', WPGFORM_I18N_DOMAIN),
            'search_items' => __('Search Google Forms', WPGFORM_I18N_DOMAIN),
            'not_found' => __('No Google Forms Found', WPGFORM_I18N_DOMAIN),
            'not_found_in_trash' => __('No Google Forms Found In Trash', WPGFORM_I18N_DOMAIN),
        ),
        'menu_icon' => plugins_url('/images/forms-16.png', __FILE__)
    );

    //  Register the WordPress Google Form post type
    register_post_type(WPGFORM_CPT_FORM, $wpgform_args) ;
}

/** Perform routine maintenance */
function wpgform_routine_maintenance()
{
    //  Post type is registered, do some hygiene on any that exist in the database.
    //  Insert the "wpgform" shortcode for that post into the post content. This
    //  ensures the form will be displayed properly when viewed through the CPT URL.

    $args = array('post_type' => WPGFORM_CPT_FORM, 'posts_per_page' => -1) ;

    // unhook this function so it doesn't update the meta data incorrectly
    remove_action('save_post_' . WPGFORM_CPT_FORM, 'wpgform_save_meta_box_data');
	
    $loop = new WP_Query($args);

    while ($loop->have_posts()) :
        $loop->the_post() ;
        $content = sprintf('[wpgform id=\'%d\']', get_the_ID()) ;

        if ($content !== get_the_content())
            wp_update_post(array('ID' => get_the_ID(), 'post_content' => $content)) ;
    endwhile ;

    // re-hook this function
    add_action('save_post_' . WPGFORM_CPT_FORM, 'wpgform_save_meta_box_data');
}

//  Build custom meta box support
//
//  There are three (3) meta boxes.  The primary meta box collects
//  the key fields and longer text input fields.  The secondary meta
//  box provides on/off settings and other selectable options.  The
//  third meta box allows entry of advanced validation rules and is
//  hidden by default.
//

/**
 * Define the WordPress Google Form Primary Meta Box fields so they
 * can be used to construct the form as well as validate and save it.
 *
 */
function wpgform_primary_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpgform-primary-meta-box',
        'title' => __('Google Form Details', WPGFORM_I18N_DOMAIN),
        'page' => WPGFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('Form URL', WPGFORM_I18N_DOMAIN),
                'desc' => __('The full URL to the published Google Form', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'form',
                'type' => 'lgtext',
                'std' => '',
                'placeholder' => __('Google Form URL', WPGFORM_I18N_DOMAIN),
                'required' => true
            ),
            array(
                'name' => __('Confirm URL', WPGFORM_I18N_DOMAIN),
                'desc' => __('The full URL to the optional Confirmation Page', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'confirm',
                'type' => 'lgtext',
                'std' => '',
                'placeholder' => __('Confirmation Page URL', WPGFORM_I18N_DOMAIN),
                'required' => false
            ),
            array(
                'name' => __('Style', WPGFORM_I18N_DOMAIN),
                'desc' => __('Custom Confirmation Page Style', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'style',
                'type' => 'select',
                'options' => array('None', 'Redirect', 'AJAX'),
                'required' => false,
                'br' => true
            ),
            array(
                'name' => __('Alert', WPGFORM_I18N_DOMAIN),
                'desc' => __('Javascript Alert Box message displayed upon submission', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'alert',
                'type' => 'lgtext',
                'std' => '',
                'placeholder' => __('Alert Message', WPGFORM_I18N_DOMAIN),
                'required' => false
            ),
            array(
                'name' => __('Class', WPGFORM_I18N_DOMAIN),
                'desc' => __('CSS class(es) to add to the form\'s containing DIV', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'class',
                'type' => 'medtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => __('Email', WPGFORM_I18N_DOMAIN),
                'desc' => __('Send email upon form submission', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'email',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Send To', WPGFORM_I18N_DOMAIN),
                'desc' => __('Email address send submission email to', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'sendto',
                'type' => 'medtext',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => __('Form CSS', WPGFORM_I18N_DOMAIN),
                'desc' => __('Form specific CSS rules', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'form_css',
                'type' => 'textarea',
                'std' => '',
                'placeholder' => 'Define form specific CSS rules',
                'required' => false
            ),
            array(
                'name' => __('Form Caching', WPGFORM_I18N_DOMAIN),
                'desc' => __('Enable Form Caching using Wordpress Transient API', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'use_transient',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Form Caching Timeout', WPGFORM_I18N_DOMAIN),
                'desc' => __('How often will the forms reloaded (in minutes)', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'transient_time',
                'type' => 'smtext',
                'std' => WPGFORM_FORM_TRANSIENT_EXPIRE,
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Reset Form Cache?', WPGFORM_I18N_DOMAIN),
                'desc' => __('This will force reloading the form from Google Drive', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'transient_reset',
                'type' => 'checkbox',
                'label' => 'Flush the Transient to force reload of the form from Google Drive',
                'required' => false,
                'br' => false
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}

/**
 * Define the WordPress Google Form Secondary Meta Box fields so they
 * can be used to construct the form as well as validate and save it.
 *
 */
function wpgform_secondary_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpgform-secondary-meta-box',
        'title' => __('Google Form Options', WPGFORM_I18N_DOMAIN),
        'page' => WPGFORM_CPT_FORM,
        'context' => 'side',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('CAPTCHA', WPGFORM_I18N_DOMAIN),
                'desc' => __('CAPTCHA', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'captcha',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Columns', WPGFORM_I18N_DOMAIN),
                'desc' => __('Split form into columns', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'columns',
                'type' => 'select',
                'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
                'std' => '1',
                'required' => true
            ),
            array(
                'name' => __('Column Order', WPGFORM_I18N_DOMAIN),
                'desc' => __('Order Columns Left to Right or Right to Left', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'columnorder',
                'type' => 'select',
                'options' => array(__('Left-to-Right', WPGFORM_I18N_DOMAIN), __('Right-to-Left', WPGFORM_I18N_DOMAIN)),
                'std' => 'ltr',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Columnize Min Width', WPGFORM_I18N_DOMAIN),
                'desc' => __('Minimum browser viewport width to columnize form (0 to ignore)', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'minvptwidth',
                'type' => 'text',
                'std' => '0',
                'required' => false
            ),
            array(
                'name' => __('Email End User', WPGFORM_I18N_DOMAIN),
                'desc' => __('Send email to end user upon form submission', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'user_email',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Legal', WPGFORM_I18N_DOMAIN),
                'desc' => __('Google Legal Disclaimer', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'legal',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Read Only', WPGFORM_I18N_DOMAIN),
                'desc' => __('Set the form Read Only', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'readonly',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('BR', WPGFORM_I18N_DOMAIN),
                'desc' => __('Insert &lt;BR&gt; tags between labels and input box', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'br',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('CSS Prefix', WPGFORM_I18N_DOMAIN),
                'desc' => __('Prefix to add to all Google CSS classes', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'css_prefix',
                'type' => 'text',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => __('CSS Suffix', WPGFORM_I18N_DOMAIN),
                'desc' => __('Suffix to add to all Google CSS classes', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'css_suffix',
                'type' => 'text',
                'std' => '',
                'required' => false
            ),
            array(
                'name' => __('Title', WPGFORM_I18N_DOMAIN),
                'desc' => __('Show or Hide the Google Form\'s title', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'title',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Map H1 to H2', WPGFORM_I18N_DOMAIN),
                'desc' => __('Map H1 elements to H2 elements', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'maph1h2',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Unite Theme Hack', WPGFORM_I18N_DOMAIN),
                'desc' => __('Unite Theme Hack', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'unitethemehack',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}

/**
 * Define the WordPress Google Form Validation Meta Box fields so they
 * can be used to construct the form as well as validate and save it.
 *
 */
function wpgform_validation_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpgform-validation-meta-box',
        'title' => __('Google Form Field Validation', WPGFORM_I18N_DOMAIN),
        'page' => WPGFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('Validation', WPGFORM_I18N_DOMAIN),
                'desc' => __('Enable default jQuery Validation on all required fields', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'validation',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Form Fields', WPGFORM_I18N_DOMAIN),
                'desc' => __('Name of the field on the Google Form (e.g. entry.1.single, entry.12345678, etc.) - <a href="http://jqueryvalidation.org/rules">Additional details on jQuery Rules</a>', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'validation_field_name',
                'type' => 'validation',
                'std' => '',
                'required' => true,
                'type_id' => WPGFORM_PREFIX . 'validation_field_type',
                'value_id' => WPGFORM_PREFIX . 'validation_field_value',
                'options' => array(
                    'required',
                    'remote',
                    'email',
                    'url',
                    'date',
                    'dateISO',
                    'number',
                    'digits',
                    'creditcard',
                    'equalTo',
                    'accept',
                    'maxlength',
                    'minlength',
                    'rangelength',
                    'range',
                    'max',
                    'min',
                    'regex',
                ),
            ),
            array(
                'name' => __('Type', WPGFORM_I18N_DOMAIN),
                'desc' => __('Type of validation', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'validation_field_type',
                'type' => 'hidden',
                'std' => '',
                'required' => true
            ),
            array(
                'name' => __('Value', WPGFORM_I18N_DOMAIN),
                'desc' => __('Value to validate against', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'validation_field_value',
                'type' => 'hidden',
                'std' => '',
                'required' => false
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}


/**
 * Define the WordPress Google Form Preset Meta Box fields so they
 * can be used to construct the form as well as validate and save it.
 *
 */
function wpgform_placeholder_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpgform-placeholder-meta-box',
        'title' => __('Google Form Field Placeholder', WPGFORM_I18N_DOMAIN),
        'page' => WPGFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('Form Fields', WPGFORM_I18N_DOMAIN),
                'desc' => __('Name of the field on the Google Form (e.g. entry.1.single, entry.12345678, etc.) - <a href="http://www.w3schools.com/tags/att_input_placeholder.asp">Additional details on Placeholders</a>', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'placeholder_field_name',
                'type' => 'placeholder',
                'std' => '',
                'required' => true,
                'type_id' => WPGFORM_PREFIX . 'placeholder_field_type',
                'value_id' => WPGFORM_PREFIX . 'placeholder_field_value',
            ),
            array(
                'name' => __('Value', WPGFORM_I18N_DOMAIN),
                'desc' => __('Value to validate against', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'placeholder_field_value',
                'type' => 'hidden',
                'std' => '',
                'required' => true
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}

/**
 * Define the WordPress Google Form Hidden Fields Meta Box fields so they
 * can be used to construct the form as well as validate and save it.
 *
 */
function wpgform_hiddenfields_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpgform-hiddenfield-meta-box',
        'title' => __('Google Form Hidden Fields', WPGFORM_I18N_DOMAIN),
        'page' => WPGFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('Hidden Fields', WPGFORM_I18N_DOMAIN),
                'desc' => __('Configure hidden fields', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'hiddenfield',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => false,
                'br' => false
            ),
            array(
                'name' => __('Form Fields', WPGFORM_I18N_DOMAIN),
                'desc' => __('Name of the field on the Google Form (e.g. entry.1.single, entry.12345678, etc.).  The optional value is only used for fields of type <b><i>value</i></b>, <b><i>url</i></b>, and <b><i>timestamp</i></b>.  For all other field types WordPress will set the hidden input to a system derived value.', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'hiddenfield_field_name',
                'type' => 'hiddenfield',
                'std' => '',
                'required' => true,
                'type_id' => WPGFORM_PREFIX . 'hiddenfield_field_type',
                'value_id' => WPGFORM_PREFIX . 'hiddenfield_field_value',
                'options' => array(
                    'value',
                    'url',
                    'timestamp',
                    'remote_addr',
                    'remote_host',
                    'http_referer',
                    'http_user_agent',
                    'user_email',
                    'user_login',
                ),
            ),
            array(
                'name' => __('Type', WPGFORM_I18N_DOMAIN),
                'desc' => __('Type of hiddenfield', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'hiddenfield_field_type',
                'type' => 'hidden',
                'std' => '',
                'required' => true
            ),
            array(
                'name' => __('Value', WPGFORM_I18N_DOMAIN),
                'desc' => __('Optional value to use as a preset', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'hiddenfield_field_value',
                'type' => 'hidden',
                'std' => '',
                'required' => false
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}

/**
 * Define the WordPress Google Form Primary Meta Box fields so they
 * can be used to construct the form as well as validate and save it.
 *
 */
function wpgform_text_overrides_meta_box_content($fieldsonly = false)
{
    $content = array(
        'id' => 'wpgform-button-overrides-meta-box',
        'title' => __('Google Form Default Text Overrides', WPGFORM_I18N_DOMAIN),
        'page' => WPGFORM_CPT_FORM,
        'context' => 'normal',
        'priority' => 'high',
        'fields' => array(
            array(
                'name' => __('Override', WPGFORM_I18N_DOMAIN),
                'desc' => __('Override <i><b>Google Default Text</b></i>', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'override_google_default_text',
                'type' => 'radio',
                'options' => array('on' => __('On', WPGFORM_I18N_DOMAIN), 'off' => __('Off', WPGFORM_I18N_DOMAIN)),
                'std' => 'off',
                'required' => true,
                'br' => false
            ),
            array(
                'name' => __('Required', WPGFORM_I18N_DOMAIN),
                'desc' => __('This is text that indicates a field is required.', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'required_text_override',
                'type' => 'medtext',
                'std' => __('Required', WPGFORM_I18N_DOMAIN),
                'placeholder' => __('Required', WPGFORM_I18N_DOMAIN),
                'required' => true
            ),
            array(
                'name' => __('Submit Button', WPGFORM_I18N_DOMAIN),
                'desc' => __('This is text used for the Submit button.', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'submit_button_text_override',
                'type' => 'medtext',
                'std' => __('Submit', WPGFORM_I18N_DOMAIN),
                'placeholder' => __('Submit', WPGFORM_I18N_DOMAIN),
                'required' => true
            ),
            array(
                'name' => __('Back Button', WPGFORM_I18N_DOMAIN),
                'desc' => __('This is text used for the Back button.', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'back_button_text_override',
                'type' => 'medtext',
                'std' => __('Back', WPGFORM_I18N_DOMAIN),
                'placeholder' => __('Back', WPGFORM_I18N_DOMAIN),
                'required' => true
            ),
            array(
                'name' => __('Continue Button', WPGFORM_I18N_DOMAIN),
                'desc' => __('This is text used for the Continue button.', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'continue_button_text_override',
                'type' => 'medtext',
                'std' => __('Continue', WPGFORM_I18N_DOMAIN),
                'placeholder' => __('Continue', WPGFORM_I18N_DOMAIN),
                'required' => true
            ),
            array(
                'name' => __('Radio Buttons', WPGFORM_I18N_DOMAIN),
                'desc' => __('This is text used for the Radio Buttons hint.', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'radio_buttons_text_override',
                'type' => 'medtext',
                'std' => __('Mark only one oval.', WPGFORM_I18N_DOMAIN),
                'placeholder' => __('Mark only one oval.', WPGFORM_I18N_DOMAIN),
                'required' => true
            ),
            array(
                'name' => __('Radio Buttons - Other', WPGFORM_I18N_DOMAIN),
                'desc' => __('This is text used for the Radio Buttons Other option.', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'radio_buttons_other_text_override',
                'type' => 'medtext',
                'std' => __('Other:', WPGFORM_I18N_DOMAIN),
                'placeholder' => __('Other:', WPGFORM_I18N_DOMAIN),
                'required' => true
            ),
            array(
                'name' => __('Check Boxes', WPGFORM_I18N_DOMAIN),
                'desc' => __('This is text used for the Check Boxes hint.', WPGFORM_I18N_DOMAIN),
                'id' => WPGFORM_PREFIX . 'check_boxes_text_override',
                'type' => 'medtext',
                'std' => __('Mark only one oval.', WPGFORM_I18N_DOMAIN),
                'placeholder' => __('Check all that apply.', WPGFORM_I18N_DOMAIN),
                'required' => true
            ),
        )
    ) ;

    return $fieldsonly ? $content['fields'] : $content ;
}

add_action('admin_menu', 'wpgform_add_primary_meta_box') ;
//add_action('admin_menu', 'wpgform_add_player_profile_meta_box') ;

// Add form meta box
function wpgform_add_primary_meta_box()
{
    $mb = wpgform_primary_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpgform_show_primary_meta_box', $mb['page'], $mb['context'], $mb['priority']);

    $mb = wpgform_secondary_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpgform_show_secondary_meta_box', $mb['page'], $mb['context'], $mb['priority']);

    $mb = wpgform_validation_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpgform_show_validation_meta_box', $mb['page'], $mb['context'], $mb['priority']);

    $mb = wpgform_hiddenfields_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpgform_show_hiddenfields_meta_box', $mb['page'], $mb['context'], $mb['priority']);

    $mb = wpgform_placeholder_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpgform_show_placeholder_meta_box', $mb['page'], $mb['context'], $mb['priority']);

    $mb = wpgform_text_overrides_meta_box_content() ;

    add_meta_box($mb['id'], $mb['title'],
        'wpgform_show_text_overrides_meta_box', $mb['page'], $mb['context'], $mb['priority']);
}

// Callback function to show fields in meta box
function wpgform_show_primary_meta_box()
{
    $mb = wpgform_primary_meta_box_content() ;
    wpgform_build_meta_box($mb) ;
}

// Callback function to show fields in meta box
function wpgform_show_secondary_meta_box()
{
    $mb = wpgform_secondary_meta_box_content() ;
    wpgform_build_meta_box($mb) ;
}

// Callback function to show validation in meta box
function wpgform_show_validation_meta_box()
{
    $mb = wpgform_validation_meta_box_content() ;
    wpgform_build_meta_box($mb) ;
}

// Callback function to show hidden fields in meta box
function wpgform_show_hiddenfields_meta_box()
{
    $mb = wpgform_hiddenfields_meta_box_content() ;
    wpgform_build_meta_box($mb) ;
}

// Callback function to show placeholder in meta box
function wpgform_show_placeholder_meta_box()
{
    $mb = wpgform_placeholder_meta_box_content() ;
    wpgform_build_meta_box($mb) ;
}

// Callback function to show fields in meta box
function wpgform_show_text_overrides_meta_box()
{
    $mb = wpgform_text_overrides_meta_box_content() ;
    wpgform_build_meta_box($mb) ;
}

/**
 * Build meta box form
 *
 * @see http://www.deluxeblogtips.com/2010/04/how-to-create-meta-box-wordpress-post.html
 * @see http://wp.tutsplus.com/tutorials/reusable-custom-meta-boxes-part-3-extra-fields/
 * @see http://wp.tutsplus.com/tutorials/reusable-custom-meta-boxes-part-4-using-the-data/
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
                    printf('<input type="text" name="%s" id="%s" style="width: 97%%;" value="%s" placeholder="%s" /><br /><small>%s</small>',
                        $field['id'], $field['id'], $meta ? $meta : $field['std'],
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', $field['desc']) ;
                    break;

                case 'medtext':
                    printf('<input type="text" name="%s" id="%s" size="30" style="width: 47%%;" value="%s" placeholder="%s" /><br /><small>%s</small>',
                        $field['id'], $field['id'], $meta ? $meta : $field['std'],
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', $field['desc']) ;
                    break;

                case 'smtext':
                    printf('<input type="text" name="%s" id="%s" size="30" style="width: 27%%;" value="%s" placeholder="%s" /><br /><small>%s</small>',
                        $field['id'], $field['id'], $meta ? $meta : $field['std'],
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', $field['desc']) ;
                    break;

                case 'textarea':
                    printf('<textarea name="%s" id="%s" cols="%s" rows="%s" style="width: 97%%;" placeholder="%s">%s</textarea><br /><small>%s</small>',
                        $field['id'], $field['id'], array_key_exists('cols', $field) ? $field['cols'] : 60,
                        array_key_exists('rows', $field) ? $field['rows'] : 4,
                        array_key_exists('placeholder', $field) ? $field['placeholder'] : '', 
                        $meta ? $meta : $field['std'], $field['desc']) ;
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
                        echo '<input type="radio" name="', $field['id'], '" value="', strtolower($value), '"', $meta == strtolower($value) ? ' checked="checked"' : empty($meta) && $field['std'] === $option ? ' checked="checked"' : '', ' />&nbsp;', $value, $field['br'] === true ? '<br />' : '&nbsp;&nbsp;';
                    }
                    echo '<br />', '<small>', $field['desc'], '</small>';
                    break;

                case 'checkbox':
                    echo '<span><input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />', '&nbsp;', $field['label'], '</span>';
                    break;

                case 'validation':
                case 'hiddenfield':
                case 'placeholder':
	                $meta_field = get_post_meta($post->ID, $field['id'], true);
                    $meta_type = get_post_meta($post->ID, $field['type_id'], true);
                    $meta_value = get_post_meta($post->ID, $field['value_id'], true);

                    echo '<a class="repeatable-add button" href="#">+</a>
			                <ul id="'.$field['id'].'-repeatable" class="custom_repeatable">';

	                $i = 0;

	                if ($meta_field) {
		                foreach($meta_field as $key => $value) {
			                echo '<li>' ;

						    printf('<label for="%s">%s:&nbsp;</label>', $field['id'].'['.$i.']', __('Name', WPGFORM_I18N_DOMAIN)) ;
						    echo '<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="'.$meta_field[$key].'" size="30" />' ;

                            if ('placeholder' !== $field['type']) {
						        printf('<label for="%s">&nbsp;%s:&nbsp;</label>', $field['type_id'].'['.$i.']', ('hiddenfield' === $field['type']) ? __('Type', WPGFORM_I18N_DOMAIN) : __('Check', WPGFORM_I18N_DOMAIN)) ;
                                echo '<select name="', $field['type_id'].'['.$i.']', '" id="', $field['type_id'], '">';
                                foreach ($field['options'] as $option) {
                                    echo '<option ', $meta_type[$key] == $option ? 'selected="selected" ' : '', 'value="', $option, '">', $option . '&nbsp;&nbsp;', '</option>';
                                }
                                echo '</select>';
                            }

                            if ('placeholder' !== $field['type'])
						        printf('<i><label for="%s">&nbsp;%s:&nbsp;</label></i>', $field['value_id'].'['.$i.']', __('Value', WPGFORM_I18N_DOMAIN)) ;
                            else
						        printf('<label for="%s">&nbsp;%s:&nbsp;</label>', $field['value_id'].'['.$i.']', __('Value', WPGFORM_I18N_DOMAIN)) ;
						    echo '<input type="text" name="'.$field['value_id'].'['.$i.']" id="'.$field['value_id'].'" value="'.$meta_value[$key].'" size="15" />' ;
						    echo '<a class="repeatable-remove button" href="#">-</a></li>';

			                $i++;
		                }
	                } else {
			                echo '<li>' ;
						    printf('<label for="%s">%s:&nbsp;</label>', $field['id'].'['.$i.']', __('Field', WPGFORM_I18N_DOMAIN)) ;
						    echo '<input type="text" name="'.$field['id'].'['.$i.']" id="'.$field['id'].'" value="" size="30" />' ;
                            if ('placeholder' !== $field['type']) {
						        printf('<label for="%s">&nbsp;%s:&nbsp;</label>', $field['type_id'].'['.$i.']', ('hiddenfield' === $field['type']) ? __('Type', WPGFORM_I18N_DOMAIN) : __('Check', WPGFORM_I18N_DOMAIN)) ;
                                echo '<select name="', $field['type_id'].'['.$i.']', '" id="', $field['type_id'], '">';
                                foreach ($field['options'] as $option) {
                                    echo '<option value="', $option, '">', $option . '&nbsp;&nbsp;', '</option>';
                                }
                                echo '</select>';
                            }

                            if ('placeholder' !== $field['type'])
						        printf('<i><label for="%s">&nbsp;%s:&nbsp;</label></i>', $field['value_id'].'['.$i.']', __('Value', WPGFORM_I18N_DOMAIN)) ;
                            else
						        printf('<label for="%s">&nbsp;%s:&nbsp;</label>', $field['value_id'].'['.$i.']', __('Value', WPGFORM_I18N_DOMAIN)) ;
						    echo '<input type="text" name="'.$field['value_id'].'['.$i.']" id="'.$field['value_id'].'" value="" size="15" />' ;
						    echo '<a class="repeatable-remove button" href="#">-</a></li>';
	                }
	                echo '</ul>
		                <small>'.$field['desc'].'</small>';
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

add_action( 'quick_edit_custom_box', 'wpgform_add_quick_edit_nonce', 10, 2 );
/**
 * Action to add a nonce to the quick edit form for the custom post types
 *
 */
function wpgform_add_quick_edit_nonce($column_name, $post_type)
{
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

add_action('save_post_' . WPGFORM_CPT_FORM, 'wpgform_save_meta_box_data');
/**
 * Action to save WordPress Google Form meta box data for CPT.
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
            $fields = array_merge(
                wpgform_primary_meta_box_content(true),
                wpgform_secondary_meta_box_content(true),
                wpgform_validation_meta_box_content(true),
                wpgform_hiddenfields_meta_box_content(true),
                wpgform_placeholder_meta_box_content(true),
                wpgform_text_overrides_meta_box_content(true)
            ) ;
        else
            return $post_id ;

        //  Loop through all of the fields and update what has changed
        //  accounting for the fact that Short URL fields are always
        //  updated and CPT fields are ignored in Quick Edit except for
        //  the Short URL field.

        foreach ($fields as $field)
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
                        update_post_meta($post_id, $field['id'], $new) ;
                    }
                    elseif ('' == $new && $old)
                    {
                        delete_post_meta($post_id, $field['id'], $old) ;
                    }
                    else
                    {
                         if( ($field['id'] == WPGFORM_PREFIX.'form') || ($field['id'] == WPGFORM_PREFIX . 'transient_reset') )
                         {
                             // If form cache reset was selected, or the URL was updated
                             // let's delete the transient and uncheck the "reset" option
                             delete_transient(WPGFORM_FORM_TRANSIENT.$post_id);
                             if( ($field['id'] == WPGFORM_PREFIX . 'transient_reset') && ($new == 'on') )
                             {
                                 $new = '';
                             }
                         }
                        //wpgform_whereami(__FILE__, __LINE__);
                    }
                }
                else
                {
                    delete_post_meta($post_id, $field['id']) ;
                }
            }
        }

        //  Set the post content to the shortcode for the form for rendering the CPT URL slug

        if (!wp_is_post_revision($post_id))
        {
		    // unhook this function so it doesn't loop infinitely
		    remove_action('save_post_' . WPGFORM_CPT_FORM, 'wpgform_save_meta_box_data');
	
		    // update the post, which calls save_post again
            wp_update_post(array('ID' => $post_id,
                'post_content' => sprintf('[wpgform id=\'%d\']', $post_id))) ;

		    // re-hook this function
		    add_action('save_post_' . WPGFORM_CPT_FORM, 'wpgform_save_meta_box_data');
	    }
    }
    else
    {
        return $post_id ;
    }
}

/**
 * CPT Save form - check form URL
 *
 * Quickly scan the form to make sure it is the proper
 * (supported) version of Google Forms.  An error is
 * generated if the HTML does not match what is expected.
 *
 */
function wpgform_check_form_url($post_id) {  
    $form = get_post_field('wpgform_form', $post_id) ;

    //  Need the HTTP API timeout value
    $wpgform_options = wpgform_get_plugin_options() ;

    if (WPGFORM_DEBUG && $wpgform_options['http_request_timeout'])
        $timeout = $wpgform_options['http_request_timeout_value'] ;
    else
        $timeout = $wpgform_options['http_api_timeout'] ;

    //  Retrieve the HTML from Google so it can be scanned
    $response = wp_remote_get($form, array('sslverify' => false, 
        'timeout' => $timeout, 'redirection' => 12, 'user-agent' => $_SERVER['HTTP_USER_AGENT'])) ;

    if (is_wp_error($response)) {
        $error_string = $response->get_error_message();
        set_transient(WPGFORM_CPT_FORM . '_admin_notice', $error_string, WEEK_IN_SECONDS) ;
    } else {
        $html = $response['body'] ;
        if (false == preg_match_all('/<div\s*class="ss-form-container"\s*>(.*?)<\/div>/', $html, $matchesArray)) {
            $error_string = __('<p>Google Form does not contain expected HTML.  Google Forms must be downgraded in order to work with this plugin.</p><p>More details can be found <a href="https://support.google.com/docs/answer/6281888?hl=en">here</a> under the <b><i>Opt out of the new Forms</i></b> section.</p>', WPGFORM_I18N_DOMAIN) ;
            set_transient(WPGFORM_CPT_FORM . '_admin_notice', $error_string, WEEK_IN_SECONDS) ;
        }
    }
}
add_action('save_post_' . WPGFORM_CPT_FORM, 'wpgform_check_form_url', 10, 1);

/**
 * CPT Admin Notices - action to note if an invalid form has been detected.
 */
function wpgform_invalid_form_error($post_id) {  
    if (false !== ($value = get_transient(WPGFORM_CPT_FORM . '_admin_notice'))) {
?>
<div class="updated error"><?php echo $value ; ?></div>
<?php
        delete_transient(WPGFORM_CPT_FORM . '_admin_notice') ;
    }
}
add_action( 'admin_notices', 'wpgform_invalid_form_error', 10, 1 );

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
 * for the WPGFORM_CPT_FORM custom post type.  The meta box which has all
 * of the custom fields in it will appear before the Visual Editor.
 * This is accomplished using a simple jQuery script once the
 * document is loaded.
 * 
 *
 */
function wpgform_admin_footer_hook()
{
    global $post ;
    $screen = get_current_screen() ;

    if ($screen->post_type == WPGFORM_CPT_FORM && $screen->id == WPGFORM_CPT_FORM)
    {
        //  wpGForm needs jQuery!
        wp_enqueue_script('jquery') ;

        //  Load the WordPress Google Form jQuery Admin script from the plugin
        wp_register_script('wpgform-post-type',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/wpgform-post-type.js')),
            array('jquery'), false, true) ;
        wp_enqueue_script('wpgform-post-type') ;
        return;

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
add_action('admin_footer','wpgform_admin_footer_hook');

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
