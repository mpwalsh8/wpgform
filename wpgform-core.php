<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * GForm functions.
 *
 * $Id$
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @package wpGForm
 * @subpackage functions
 * @version $Revision$
 * @lastmodified $Date$
 * @lastmodifiedby $Author$
 *
 */

// Filesystem path to this plugin.
define('WPGFORM_PREFIX', 'wpgform_') ;
define('WPGFORM_PATH', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__))) ;
define('WPGFORM_EMAIL_FORMAT_HTML', 'html') ;
define('WPGFORM_EMAIL_FORMAT_PLAIN', 'plain') ;
define('WPGFORM_CONFIRM_AJAX', 'ajax') ;
define('WPGFORM_CONFIRM_LIGHTBOX', 'lightbox') ;
define('WPGFORM_CONFIRM_REDIRECT', 'redirect') ;
define('WPGFORM_CONFIRM_NONE', 'none') ;
define('WPGFORM_LOG_ENTRY_META_KEY', '_wpgform_log_entry') ;
define('WPGFORM_FORM_TRANSIENT', 'wpgform_form_response') ;
define('WPGFORM_FORM_TRANSIENT_EXPIRE', 5) ;

// i18n plugin domain
define( 'WPGFORM_I18N_DOMAIN', 'wpgform' );

/**
 * Initialise the internationalisation domain
 */
$is_wpgform_i18n_setup = false ;
function wpgform_init_i18n()
{
	global $is_wpgform_i18n_setup;

	if ($is_wpgform_i18n_setup == false) {
		load_plugin_textdomain(WPGFORM_I18N_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages/') ;
		$is_wpgform_i18n_setup = true;
	}
}

//  Need the plugin options to initialize debug
$wpgform_options = wpgform_get_plugin_options() ;

//  Disable fsockopen transport?
if ($wpgform_options['fsockopen_transport'] == 1)
    add_filter('use_fsockopen_transport', '__return_false') ;

//  Disable streams transport?
if ($wpgform_options['streams_transport'] == 1)
    add_filter('use_streams_transport', '__return_false') ;

//  Disable curl transport?
if ($wpgform_options['curl_transport'] == 1)
    add_filter('use_curl_transport', '__return_false') ;

//  Disable local ssl verify?
if ($wpgform_options['local_ssl_verify'] == 1)
    add_filter('https_local_ssl_verify', '__return_false') ;

//  Disable ssl verify?
if ($wpgform_options['ssl_verify'] == 1)
    add_filter('https_ssl_verify', '__return_false') ;

//  Change the HTTP Time out?
if ($wpgform_options['http_request_timeout'] == 1)
{
    if (is_int($wpgform_options['http_request_timeout_value'])
        || ctype_digit($wpgform_options['http_request_timeout_value']))
        add_filter('http_request_timeout', 'wpgform_http_request_timeout') ;
}

/**
 * Optional filter to change HTTP Request Timeout
 *
 */
function wpgform_http_request_timeout($timeout) {
    $wpgform_options = wpgform_get_plugin_options() ;
    return $wpgform_options['http_request_timeout'] ;
}

//  Enable debug content?
define('WPGFORM_DEBUG', $wpgform_options['enable_debug'] == 1) ;
//define('WPGFORM_DEBUG', true) ;

if (WPGFORM_DEBUG)
{
    error_reporting(E_ALL) ;
    require_once('wpgform-debug.php') ;
    add_action('send_headers', 'wpgform_send_headers') ;
}

/**
 * wpgform_init()
 *
 * Init actions to enable shortcodes.
 *
 * @return null
 */
function wpgform_init()
{
    $wpgform_options = wpgform_get_plugin_options() ;

    if ($wpgform_options['sc_posts'] == 1)
    {
        add_shortcode('gform', array('wpGForm', 'gform_sc')) ;
        add_shortcode('wpgform', array('wpGForm', 'wpgform_sc')) ;
    }

    if ($wpgform_options['sc_widgets'] == 1)
        add_filter('widget_text', 'do_shortcode') ;

    //add_filter('the_content', 'wpautop');
    //add_filter('the_content', 'wpgform_the_content');
    //add_action('template_redirect', 'wpgform_head') ;
    add_action( 'wp_enqueue_scripts', 'wpgform_head' );
    add_action('wp_footer', 'wpgform_footer') ;
}

/**
 * Filter to render a Google Form when a public CPT URL is
 * requested.  The filter will inject the proper shortcode into
 * the content which is then in turn processed by WordPress to
 * render the form as a regular short code would be processed.
 *
 * @param $content string post content
 * @since v0.46
 */
function wpgform_the_content($content)
{
    return (WPGFORM_CPT_FORM == get_post_type(get_the_ID())) ?
        sprintf('[wpgform id=\'%s\']', get_the_ID()) : $content ;
}

add_action('init', array('wpGForm', 'ProcessGoogleForm')) ;

/**
 * Returns the default options for wpGForm.
 *
 * @since wpGForm 0.11
 */
function wpgform_get_default_plugin_options()
{
	$default_plugin_options = array(
        'sc_posts' => 1
       ,'sc_widgets' => 1
       ,'default_css' => 1
       ,'custom_css' => 0
       ,'custom_css_styles' => ''
       ,'donation_message' => 0
       ,'curl_transport_missing_message' => 0
       ,'captcha_terms' => 2
       ,'captcha_operator_plus' => 1
       ,'captcha_operator_minus' => 0
       ,'captcha_operator_mult' => 0
       ,'captcha_description' => ''
       ,'email_format' => WPGFORM_EMAIL_FORMAT_PLAIN
       ,'http_api_timeout' => 5
       ,'form_submission_log' => 0
       ,'disable_html_filtering' => 0
       ,'browser_check' => 0
       ,'enable_debug' => 0
       ,'serialize_post_vars' => 0
       ,'bcc_blog_admin' => 1
       ,'fsockopen_transport' => 0
       ,'streams_transport' => 0
       ,'curl_transport' => 0
       ,'local_ssl_verify' => 0
       ,'ssl_verify' => 0
       ,'http_request_timeout' => 0
       ,'http_request_timeout_value' => 30
       ,'override_google_default_text' => 0
       ,'required_text_override' => __('Required', WPGFORM_I18N_DOMAIN)
       ,'submit_button_text_override' => __('Submit', WPGFORM_I18N_DOMAIN)
       ,'back_button_text_override' => __('Back', WPGFORM_I18N_DOMAIN)
       ,'continue_button_text_override' => __('Continue', WPGFORM_I18N_DOMAIN)
       ,'radio_buttons_text_override' => __('Mark only one oval.', WPGFORM_I18N_DOMAIN)
       ,'radio_buttons_other_text_override' => __('Other:', WPGFORM_I18N_DOMAIN)
       ,'check_boxes_text_override' => __('Check all that apply.', WPGFORM_I18N_DOMAIN)
	) ;

	return apply_filters('wpgform_default_plugin_options', $default_plugin_options) ;
}

/**
 * Returns the options array for the wpGForm plugin.
 *
 * @since wpGForm 0.11
 */
function wpgform_get_plugin_options()
{
    //  Get the default options in case anything new has been added
    $default_options = wpgform_get_default_plugin_options() ;

    //  If there is nothing persistent saved, return the default

    if (get_option('wpgform_options') === false)
        return $default_options ;

    //  One of the issues with simply merging the defaults is that by
    //  using checkboxes (which is the correct UI decision) WordPress does
    //  not save anything for the fields which are unchecked which then
    //  causes wp_parse_args() to incorrectly pick up the defaults.
    //  Since the array keys are used to build the form, we need for them
    //  to "exist" so if they don't, they are created and set to null.

    $plugin_options = wp_parse_args(get_option('wpgform_options'), $default_options) ;

    //  If the array key doesn't exist, it means it is a check box option
    //  that is not enabled so the array element(s) needs to be set to zero.

    //foreach ($default_options as $key => $value)
    //    if (!array_key_exists($key, $plugin_options)) $plugin_options[$key] = 0 ;

    return $plugin_options ;
}

/**
 * Returns the options array for the wpGForm plugin.
 *
 * @param input mixed input to validate
 * @return input mixed validated input
 * @since wpGForm 0.58-beta-4
 *
 */
function wpgform_options_validate($input)
{
    if ('update' === $_POST['action'])
    {
        // Get the options array defined for the form
        $options = wpgform_get_default_plugin_options();

        //  Loop through all of the default options
        foreach ($options as $key => $value)
        {
            //  If the default option doesn't exist, which it
            //  won't if it is a checkbox, default the value to 0
            //  which means the checkbox is turned off.

            if (!array_key_exists($key, $input))
                $input[$key] = 0 ;
        }
    }

    //  Was the Reset button pushed?
    if (__('Reset', WPGFORM_I18N_DOMAIN) === $_POST['Submit'])
        $input = wpgform_get_default_plugin_options();

    return $input ;
}

/**
 * wpgform_admin_menu()
 *
 * Adds admin menu page(s) to the Dashboard.
 *
 * @return null
 */
function wpgform_admin_menu()
{
    wpgform_init_i18n() ;
    require_once(WPGFORM_PATH . '/wpgform-options.php') ;

    $wpgform_options_page = add_options_page(
        __('Google Forms', WPGFORM_I18N_DOMAIN),
        __('Google Forms', WPGFORM_I18N_DOMAIN),
        'manage_options', 'wpgform-options.php', 'wpgform_options_page') ;
    add_action('admin_footer-'.$wpgform_options_page, 'wpgform_options_admin_footer') ;
    add_action('admin_print_scripts-'.$wpgform_options_page, 'wpgform_options_print_scripts') ;
    add_action('admin_print_styles-'.$wpgform_options_page, 'wpgform_options_print_styles') ;

    add_submenu_page(
        'edit.php?post_type=wpgform',
        __('Google Forms Submission Log', WPGFORM_I18N_DOMAIN), /*page title*/
        __('Form Submission Log', WPGFORM_I18N_DOMAIN), /*menu title*/
        'manage_options', /*roles and capabiliyt needed*/
        'wpgform-entry-log-page',
        'wpgform_entry_log_page' /*replace with your own function*/
    );
}

function wpgform_entry_log_page()
{
    require_once('wpgform-logging.php') ;
}



/**
 * wpgform_admin_init()
 *
 * Init actions for the Dashboard interface.
 *
 * @return null
 */
function wpgform_admin_init()
{
    register_setting('wpgform_options', 'wpgform_options', 'wpgform_options_validate') ;
    wpgform_routine_maintenance() ;
}

/**
 * wpgform_activate()
 *
 * Adds the default options so WordPress options are
 * configured to a default state upon plugin activation.
 *
 * @return null
 */
function wpgform_activate()
{
    wpgform_init_i18n() ;
    add_option('wpgform_options', wpgform_get_default_plugin_options()) ;
    add_filter('widget_text', 'do_shortcode') ;
    flush_rewrite_rules() ;
}

/**
 * wpgform_deactivate()
 *
 * Adds the default options so WordPress options are
 * configured to a default state upon plugin activation.
 *
 * @return null
 */
function wpgform_deactivate()
{
    flush_rewrite_rules() ;
}

/**
 * wpGForm class definition
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @access public
 * @see wp_remote_get()
 * @see wp_remote_post()
 * @see RenderGoogleForm()
 * @see ConstructGoogleForm()
 */
class wpGForm
{
    /**
     * Property to hold Browser Check response
     */
    static $browser_check ;

    /**
     * Property to hold Google Form Response
     */
    static $response ;

    /**
     * Property to hold Google Form Post Error
     */
    static $post_error = false ;

    /**
     * Property to hold Google Form Post Status
     */
    static $posted = false ;

    /**
     * Property to indicate Javascript output state
     */
    static $wpgform_js = false ;

    /**
     * Property to hold global plugin Javascript output
     */
    static $wpgform_plugin_js = '' ;

    /**
     * Property to hold form specific Javascript output
     */
    static $wpgform_form_js = array() ;

    /**
     * Property to store Javascript output in footer
     */
    static $wpgform_footer_js = '' ;

    /**
     * Property to store state of Javascript output in footer
     */
    static $wpgform_footer_js_printed = false ;

    /**
     * Property to indicate CSS output state
     */
    static $wpgform_css = false ;

    /**
     * Property to indicate Debug output state
     */
    static $wpgform_debug = false ;

    /**
     * Property to store unique form id
     */
    static $wpgform_form_id = 1 ;

    /**
     * Property to store unique form id
     */
    static $wpgform_submitted_form_id = null ;

    /**
     * Property to store captcha values
     */
    static $wpgform_captcha = null ;

    /**
     * Property to user email address to send email confirmation to
     */
    static $wpgform_user_sendto = null ;

    /**
     * Property to store jQuery Validation messages
     */
    //static $vMsgs_js = array() ;

    /**
     * Property to store jQuery Validation rules
     */
    //static $vRules_js = array() ;

    /**
     * Property to store the various options which control the
     * HTML manipulation and generation.  These array keys map
     * to the meta data stored with the wpGForm Custom Post Type.
     *
     * The Unite theme from Paralleus mucks with the submit buttons
     * which breaks the ability to submit the form to Google correctly.
     * This "special" hack will "unbreak" the submit buttons.
     *
     */
    protected static $options = array(
        'form'           => false,           // Google Form URL
        'uid'            => '',              // Unique identifier string to prepend to id and name attributes
        'confirm'        => null,            // Custom confirmation page URL to redirect to
        'alert'          => null,            // Optional Alert Message
        'class'          => 'wpgform',       // Container element's custom class value
        'legal'          => 'on',            // Display Google Legal Stuff
        'br'             => 'off',           // Insert <br> tags between labels and inputs
        'columns'        => '1',             // Number of columns to render the form in
        'minvptwidth'    => '0',             // Minimum viewport width for columnization, 0 to ignore
        'columnorder'    => 'left-to-right', // Order to show columns - Left to Right or Right to Left
        'css_suffix'     => null,            // Add suffix character(s) to all labels
        'css_prefix'     => null,            // Add suffix character(s) to all labels
        'readonly'       => 'off',           // Set all form elements to disabled
        'title'          => 'on',            // Remove the H1 element(s) from the Form
        'maph1h2'        => 'off',           // Map H1 element(s) on the form to H2 element(s)
        'email'          => 'off',           // Send an email confirmation to blog admin on submission
        'sendto'         => null,            // Send an email confirmation to a specific address on submission
        'user_email'     => 'off',           // Send an email confirmation to user on submission
        'user_sendto'    => null,            // Send an email confirmation to a specific address on submission
        'results'        => false,           // Results URL
        'spreadsheet'    => false,           // Google Spreadsheet URL
        'captcha'        => 'off',           // Display a CAPTCHA when enabled
        'validation'     => 'off',           // Use jQuery validation for required fields
        'unitethemehack' => 'off',           // Send an email confirmation to blog admin on submission
        'style'          => null,            // How to present the custom confirmation after submit
        'use_transient'  => false,           // Toogles the use of WP Transient API for form caching
        'transient_time' => WPGFORM_FORM_TRANSIENT_EXPIRE,  // Sets how long (in minutes) the forms will be cached using WP Transient
    ) ;

    /**
     * Constructor
     */
    //function wpGForm()
    //{
    //    // empty for now - this syntax is deprecated in PHP7!
    //}

    /**
     * 'gform' short code handler
     *
     * @since 0.1
     * @deprecated
     * @see http://scribu.net/wordpress/conditional-script-loading-revisited.html
     */
    static function gform_sc($options)
    {
        wpgform_enqueue_scripts() ;
        if (self::ProcessShortCodeOptions($options))
            return self::ConstructGoogleForm() ;
        else
            return sprintf('<div class="wpgform-google-error gform-google-error">%s</div>',
               __('Unable to process Google Form short code.', WPGFORM_I18N_DOMAIN)) ;
    }

    /**
     * 'wpgform' short code handler
     *
     * @since 1.0
     * @see http://scribu.net/wordpress/conditional-script-loading-revisited.html
     */
    static function wpgform_sc($options)
    {
        wpgform_enqueue_scripts() ;
        if (self::ProcessWpGFormCPT($options))
            return self::ConstructGoogleForm() ;
        else
            return sprintf('<div class="wpgform-google-error gform-google-error">%s</div>',
               __('Unable to process Google Form short code.', WPGFORM_I18N_DOMAIN)) ;
    }

    /**
     * Function ProcessShortcode loads HTML from a Google Form URL,
     * processes it, and inserts it into a WordPress filter to output
     * as part of a post, page, or widget.
     *
     * @param $options array Values passed from the shortcode.
     * @see gform_sc
     * @return boolean - abort processing when false
     */
    static function ProcessShortCodeOptions($options)
    {
        //  Property short cut
        $o = &self::$options ;

        //  Override default options based on the short code attributes

        foreach ($o as $key => $value)
        {
            if (array_key_exists($key, $options))
                $o[$key] = $options[$key] ;
        }

        //  If a confirm has been supplied but a style has not, default to redirect style.

        if (!array_key_exists('confirm', $o) || is_null($o['confirm']) || empty($o['confirm']))
        {
            $o['style'] = WPGFORM_CONFIRM_NONE ;
        }
        elseif ((array_key_exists('confirm', $o) && !array_key_exists('style', $o)) ||
            (array_key_exists('confirm', $o) && array_key_exists('style', $o) && $o['style'] == null))
        {
            $o['style'] = WPGFORM_CONFIRM_REDIRECT ;
        }

        //  Validate columns - make sure it is a reasonable number
 
        if (is_numeric($o['columns']) && ($o['columns'] > 1) && ($o['columns'] == round($o['columns'])))
            $o['columns'] = (int)$o['columns'] ;
        else
            $o['columns'] = 1 ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessShortCodeOptions') ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($o) ;

        //  Have to have a form URL otherwise the short code is meaningless!

        return (!empty($o['form'])) ;
    }

    /**
     * Function ProcessShortcode loads HTML from a Google Form URL,
     * processes it, and inserts it into a WordPress filter to output
     * as part of a post, page, or widget.
     *
     * @param $options array Values passed from the shortcode.
     * @see RenderGoogleForm
     * @return boolean - abort processing when false
     */
    static function ProcessWpGFormCPT($options)
    {
        //  Property short cut
        $o = &self::$options ;

        //  Id?  Required - make sure it is reasonable.

        if ($options['id'])
        {
            $o['id'] = $options['id'] ;

            //  Make sure we didn't get something nonsensical
            if (is_numeric($o['id']) && ($o['id'] > 0) && ($o['id'] == round($o['id'])))
                $o['id'] = (int)$o['id'] ;
            else
                return false ;
        }
        else
            return false ;

        if (array_key_exists('uid', $options)) $o['uid'] = $options['uid'] ;

        // get current form meta data fields

        $fields = array_merge(
            wpgform_primary_meta_box_content(true),
            wpgform_secondary_meta_box_content(true),
            wpgform_validation_meta_box_content(true),
            wpgform_placeholder_meta_box_content(true),
            wpgform_hiddenfields_meta_box_content(true),
            wpgform_text_overrides_meta_box_content(true)
        ) ;

        foreach ($fields as $field)
        {
            //  Only show the fields which are not hidden
            if ($field['type'] !== 'hidden')
            {
                // get current post meta data
                $meta = get_post_meta($o['id'], $field['id'], true);

                //  If a meta value is found, strip off the prefix
                //  from the meta key so the id matches the options
                //  used by the form rendering method.

                if ($meta)
                    $o[substr($field['id'], strlen(WPGFORM_PREFIX))] = $meta ;
            }
        }

        //  Validate columns - make sure it is a reasonable number
 
        if (is_numeric($o['columns']) && ($o['columns'] > 1) && ($o['columns'] == round($o['columns'])))
            $o['columns'] = (int)$o['columns'] ;
        else
            $o['columns'] = 1 ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessWpGFormCPT') ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($o) ;

        //  Have to have a form URL otherwise the short code is meaningless!

        return (!empty($o['form'])) ;
    }

    /**
     * Function ConstructGoogleForm loads HTML from a Google Form URL,
     * processes it, and inserts it into a WordPress filter to output
     * as part of a post, page, or widget.
     *
     * @return An HTML string if successful, false otherwise.
     * @see RenderGoogleForm
     */
    static function ConstructGoogleForm()
    {
        //wpgform_load_js_css() ;

        //  Any preset params?
        $presets = $_GET ;

        //  Eliminate the Google Form's query variable if it is set
        if (!empty($presets) && array_key_exists(WPGFORM_CPT_QV_FORM, $presets))
            unset($presets[WPGFORM_CPT_QV_FORM]) ;

        $locale_cookie = new WP_HTTP_Cookie(array('name' => 'locale', 'value' => get_locale())) ;

        //  Property short cut
        $o = &self::$options ;

        //  Account for existing forms not having the override option
        if (!array_key_exists('override_google_default_text', $o))
            $o['override_google_default_text'] = 'off' ;

        $wpgform_options = wpgform_get_plugin_options() ;

        if (WPGFORM_DEBUG && $wpgform_options['http_request_timeout'])
            $timeout = $wpgform_options['http_request_timeout_value'] ;
        else
            $timeout = $wpgform_options['http_api_timeout'] ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;

        $global_override_google_default_text = (int)$wpgform_options['override_google_default_text'] === 1 ;

        //  Some servers running ModSecurity issue 403 errors because something
        //  in the form's POST parameters has triggered a positive match on a rule.

        if (!empty($_SERVER) && array_key_exists('REDIRECT_STATUS', $_SERVER) && ($_SERVER['REDIRECT_STATUS'] == '403'))
            return sprintf('<div class="wpgform-google-error gform-google-error">%s %s<span class="wpgform-google-error gform-google-error">%s</span> %s.</div>',
                __('Unable to process Google Form.', WPGFORM_I18N_DOMAIN),
                __('Server is responding with', WPGFORM_I18N_DOMAIN),
                __('403 Permission Denied', WPGFORM_I18N_DOMAIN), __('error', WPGFORM_I18N_DOMAIN)) ;

        //  If no URL then return as nothing useful can be done.
        if (!$o['form'])
        {
            return false; 
        }
        else
        {
            $form = $o['form'] ;
            $uid = $o['uid'] ;
            $prefix = $o['css_prefix'] ;
            $suffix = $o['css_suffix'] ;
            $confirm = $o['confirm'] ;
            $alert = $o['alert'] ;
            $sendto = $o['sendto'] ;

            //  The old short code supports the 'spreadsheet' attribute which
            //  takes precedence over the new attribute 'results' for backward
            //  compatibility.

            $results = ($o['spreadsheet'] === false) ? $o['results'] : $o['spreadsheet'] ;

            //  Check to see if form has a Google Form default text overrides - older forms won't
            $local_override_google_default_text = (int)(array_key_exists('override_google_default_text', $o)) ?
                $o['override_google_default_text'] === 'on' : 0 ;
        }

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;

        //  Should email confirmation be sent to user?
        $user_email = $o['user_email'] === 'on' ;

        $user_email_html = '' ;
        $user_email_sendto = "" ;

        //  Generate the User Email HTML if requested
        //printf('<h1>%s::%s -> %s</h1>', basename(__FILE__), __LINE__, $uid) ;

        if ($user_email)
        {
            $current_user = wp_get_current_user();

            if (0 != $current_user->ID)
                $user_email_sendto = $current_user->user_email ;
            
            $user_email_html .= '<div class="wpgform-user-email">' ;
            $user_email_html .= sprintf('<div class="%sss-item %sss-item-required %sss-text">', $prefix, $prefix, $prefix) ;
            $user_email_html .= sprintf('<div class="%sss-form-entry">', $prefix) ;
            $user_email_html .= sprintf('<label for="wpgform-user-email" class="%sss-q-title">%s', $prefix,
               __('Email Address')) ;
            $user_email_html .= sprintf('<span class="%sss-required-asterisk">*</span></label>', $prefix) ;
            $user_email_html .= sprintf('<label for="wpgform-user-email" class="%sss-q-help"></label>', $prefix) ;
            $user_email_html .= sprintf('<input style="width: 250px;" type="text" id="%swpgform-user-email" class="%sss-q-short" value="%s" name="%swpgform-user-email">', $uid, $prefix, $user_email_sendto, $uid) ;
            $user_email_html .= '</div></div></div>' ;
        }

        //  Display CAPTCHA?
        $captcha_html = '' ;

        $captcha = $o['captcha'] === 'on' ;

        //  Generate the CAPTCHA HTML if requested

        if ($captcha)
        {
            $captcha_operators = array() ;

            if ((int)$wpgform_options['captcha_operator_plus'] === 1) $captcha_operators[] = '+' ;
            if ((int)$wpgform_options['captcha_operator_minus'] === 1) $captcha_operators[] = '-' ;
            if ((int)$wpgform_options['captcha_operator_mult'] === 1) $captcha_operators[] = '*' ;

            //  Default to addition if for some reason no operators are enabled
            if (empty($captcha_operators)) $captcha_operators[] = '+' ;

            //  Get random operator for A and B terms
            $op1 = $captcha_operators[rand(0, count($captcha_operators) - 1)] ;
            //  Get random operator for including C term when using 3 terms, use '+' otherwise
            $op2 = ((int)$wpgform_options['captcha_terms'] === 3) ? $captcha_operators[rand(0, count($captcha_operators) - 1)] : '+';

            //  Generate a random value for A
            $a = rand(0, 19) ;
            //  Generate a random value for B
            $b = rand(0, 19) ;
            //  Generate a random value for C only when using 3 terms, use 0 otherwise
            $c = ((int)$wpgform_options['captcha_terms'] === 3) ? rand(0, 19) : 0 ;

            if ((int)$wpgform_options['captcha_terms'] === 2)
                $x = eval('return sprintf("%s%s%s", $a, $op1, $b);') ;
            else
                $x = eval('return sprintf("%s%s%s%s%s", $a, $op1, $b, $op2, $c);') ;

            self::$wpgform_captcha = array('a' => $a, 'b' => $b, 'c' => $c, 'x' => $x) ;

            //  Build the CAPTCHA HTML

            $captcha_html .= '<div class="wpgform-captcha">' ;
            $captcha_html .= sprintf('<div class="%sss-item %sss-item-required %sss-text">', $prefix, $prefix, $prefix) ;
            $captcha_html .= sprintf('<div class="%sss-form-entry">', $prefix) ;
            if ((int)$wpgform_options['captcha_terms'] === 2)
                $captcha_html .= sprintf('<label for="%swpgform-captcha" class="%sss-q-title">%s %s %s %s ?', $uid, $prefix, __('What is', WPGFORM_I18N_DOMAIN), $a, $op1, $b) ;
            else
                $captcha_html .= sprintf('<label for="%swpgform-captcha" class="%sss-q-title">%s %s %s %s %s %s?', $uid, $prefix, __('What is', WPGFORM_I18N_DOMAIN), $a, $op1, $b, $op2, $c) ;
            $captcha_html .= sprintf('<span class="%sss-required-asterisk">*</span></label>', $prefix) ;
            $captcha_html .= sprintf('<label for="%swpgform-captcha" class="%sss-q-help"></label>', $uid, $prefix) ;
            $captcha_html .= sprintf('<input style="width: 100px;" type="text" id="%swpgform-captcha" class="%sss-q-short" value="" name="%swpgform-captcha">', $uid, $prefix, $uid) ;
            $captcha_html .= '</div></div>' ;

            //  Add in the optional CAPTCHA description if one has been set

            if (!empty($wpgform_options['captcha_description']))
            {
                $captcha_html .= sprintf('<div class="wpgform-captcha-description">%s</div>', $wpgform_options['captcha_description']) ;
            }

            $captcha_html .= '</div>' ;
        }

        //  Use jQuery validation?  Force it on when CAPTCHA is on
        $validation = $o['validation'] === 'on' | $captcha ;

        //  Output the H1 title included in the Google Form?
        $title = $o['title'] === 'on' ;

        //  Map H1 tags to H2 tags?  Apparently helps SEO ...
        $maph1h2 = $o['maph1h2'] === 'on' ;

        //  Insert <br> elements between labels and input boxes?
        $br = $o['br'] === 'on' ;

        //  Google Legal Stuff?
        $legal = $o['legal'] !== 'off' ;

        //  Should form be set to readonly?
        $readonly = $o['readonly'] === 'on' ;

        //  Should email confirmation be sent to admin?
        $email = $o['email'] === 'on' ;

        //  Who should email confirmation be sent to?
        if (is_email($o['sendto']))
            $sendto = $o['sendto'] ;

        //  How many columns?
        $columns = $o['columns'] ;

        //  Minimum columnize width
        $minvptwidth = $o['minvptwidth'] ;

        //  Column order?
        $columnorder = $o['columnorder'] == 'right-to-left' ? 'right' : 'left' ;

        //  The Unite theme from Paralleus mucks with the submit buttons
        //  which breaks the ability to submit the form to Google correctly.
        //  This hack will "unbreak" the submit buttons.

        $unitethemehack = $o['unitethemehack'] === 'on' ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;

        //  Show the custom confirmation via AJAX instead of redirect?
        $style = $o['style'] === 'none' ? null : $o['style'] ;

        // Use WP Transient API Cache?
        $use_transient = $o['use_transient'] === 'on';
        $transient_time = $o['transient_time'];

        //  WordPress converts all of the ampersand characters to their
        //  appropriate HTML entity or some variety of it.  Need to undo
        //  that so the URL can be actually be used.

        $form = str_replace(array('&#038;','&#38;','&amp;'), '&', $form) ;
        if (!is_null($confirm))
            $confirm = str_replace(array('&#038;','&#38;','&amp;'), '&', $confirm) ;
        
        //  If there were any preset values to pass into the form, add them to the URL

        if (!empty($presets))
        {
            //  The name of the form fields are munged, they need
            //  to be restored before the parameters can be posted
            //  so they match what Google expects.

            $patterns = array('/entry_([0-9]+)_(single|group)_/', '/entry_([0-9]+)_/', '/entry_([0-9]+)/') ;
            $replacements = array('entry.\1.\2.', 'entry.\1.', 'entry.\1') ;

            foreach ($replacements as $key => $value)
                $replacements[$key] = sprintf('%s%s', $uid, $value) ;

            foreach ($presets as $key => $value)
            {
                $presets[preg_replace($patterns, $replacements, $key)] = urlencode($value) ;

                //  Really shouldn't need both forms of the field but to
                //  handle old and new Google Forms we keep both.  This may
                //  go away once Google completely converts to the new version
                //  of Google Forms.
 
                $presets[$key] = urlencode($value) ;
                //unset($presets[$key]) ;
            }

            $form = add_query_arg($presets, $form) ;
        }

        //  The initial rendering of the form content is done using this
        //  "remote get", all subsequent renderings will be the result of
        //  "post processing".


        if (!self::$posted)
        {
            if ($use_transient && is_multisite())
            {
                if (false === ( self::$response = get_site_transient( WPGFORM_FORM_TRANSIENT.$o['id'] ) ) ) 
                {
                    // There was no transient, so let's regenerate the data and save it
                    self::$response = wp_remote_get($form, array('sslverify' => false, 'timeout' => $timeout, 'redirection' => 12, 'user-agent' => $_SERVER['HTTP_USER_AGENT'])) ;
                    set_site_transient( WPGFORM_FORM_TRANSIENT.$o['id'], self::$response, $transient_time*MINUTE_IN_SECONDS );
                }
            }
            elseif ($use_transient && !is_multisite())
            {
                if (false === ( self::$response = get_transient( WPGFORM_FORM_TRANSIENT.$o['id'] ) ) ) 
                {
                    // There was no transient, so let's regenerate the data and save it
                    self::$response = wp_remote_get($form, array('sslverify' => false, 'timeout' => $timeout, 'redirection' => 12, 'user-agent' => $_SERVER['HTTP_USER_AGENT'])) ;
                    set_transient( WPGFORM_FORM_TRANSIENT.$o['id'], self::$response, $transient_time*MINUTE_IN_SECONDS );
                }
            }
            else
            {
                self::$response = wp_remote_get($form, array('sslverify' => false, 'timeout' => $timeout, 'redirection' => 12, 'user-agent' => $_SERVER['HTTP_USER_AGENT'])) ;
            }
        }

        //  Retrieve the HTML from the URL

        if (is_wp_error(self::$response))
        {
            $error_string = self::$response->get_error_message();
            echo '<div id="message" class="wpgform-google-error"><p>' . $error_string . '</p></div>';
            if (WPGFORM_DEBUG)
            {
                wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;
                wpgform_preprint_r(self::$response) ;
            }

            //  Clean up the transient if an error is encountered

            if ($use_transient && is_multisite())
                delete_site_transient(WPGFORM_FORM_TRANSIENT . $o['id']);
            elseif ($use_transient)
                delete_transient(WPGFORM_FORM_TRANSIENT . $o['id']);

            return sprintf('<div class="wpgform-google-error gform-google-error">%s</div>',
               __('Unable to retrieve Google Form.  Please try reloading this page.', WPGFORM_I18N_DOMAIN)) ;

        }
        else
        {
            $html = self::$response['body'] ;
            $headers = self::$response['headers'] ;

            if (WPGFORM_DEBUG)
            {
                wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;
                wpgform_preprint_r(array_keys(self::$response)) ;
                wpgform_preprint_r($headers) ;
            }
        }

        if (WPGFORM_DEBUG)
        {
            wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;
            wpgform_htmlspecialchars_preprint_r(self::$response) ;
            //wpgform_htmlspecialchars_preprint_r($html) ;
        }

        //  Filter the HTML unlesss explicitly told not to

        if ((int)$wpgform_options['disable_html_filtering'] !== 1) 
        {
            if (WPGFORM_DEBUG)
            {
                wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm (before wp_kses())') ;
                wpgform_htmlspecialchars_preprint_r($html) ;
            }

            //  Need to filter the HTML retrieved from the form and strip off the stuff
            //  we don't want.  This gets rid of the HTML wrapper from the Google page.
    
            $allowed_tags = array(
                'a' => array('href' => array(), 'title' => array(), 'target' => array(), 'class' => array())
               ,'b' => array()
               ,'abbr' => array('title' => array()),'acronym' => array('title' => array())
               ,'code' => array()
               ,'pre' => array()
               ,'em' => array()
               ,'strong' => array()
               ,'ul' => array()
               ,'ol' => array()
               ,'li' => array()
               ,'p' => array()
               ,'br' => array()
               ,'div' => array('class' => array())
               ,'h1' => array('class' => array())
               ,'h2' => array('class' => array())
               ,'h3' => array('class' => array())
               ,'h4' => array('class' => array())
               ,'h5' => array('class' => array())
               ,'h6' => array('class' => array())
               ,'i' => array()
               ,'img' => array('class' => array(), 'alt' => array(), 'title' => array(), 'src' => array())
               ,'label' => array('class' => array(), 'for' => array())
               ,'input' => array('id' => array(), 'name' => array(), 'class' => array(), 'type' => array(), 'value' => array(), 'checked' => array())
               ,'select' => array('name' => array(), 'for' => array(), 'checked' => array())
               ,'option' => array('value' => array(), 'selected' => array())
               ,'form' => array('id' => array(), 'class' => array(), 'action' => array(), 'method' => array(), 'target' => array(), 'onsubmit' => array())
               ,'script' => array('type' => array())
               ,'span' => array('class' => array(), 'style' => array())
               ,'style' => array()
               ,'table' => array('class' => array(), 'style' => array())
               ,'tbody' => array('class' => array(), 'style' => array())
               ,'textarea' => array('id' => array(), 'name' => array(), 'class' => array(), 'type' => array(), 'value' => array(), 'rows' => array(), 'cols' => array())
               ,'thead' => array('class' => array(), 'style' => array())
               ,'tr' => array('class' => array())
               ,'td' => array('class' => array(), 'style' => array())
            ) ;
    
            //  Process the HTML
    
            $html = wp_kses($html, $allowed_tags) ;

            if (WPGFORM_DEBUG)
            {
                wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm (after wp_kses())') ;
                wpgform_htmlspecialchars_preprint_r($html) ;
            }

        }

        $patterns = array(
            '/entry\.([0-9]+)\.(single|group)\./',
            '/entry\.([0-9]+)_/',
            '/entry\.([0-9]+)/',
            '/entry_([0-9]+)\.(single|group)\./',
            '/entry_([0-9]+)_/',
            '/entry_([0-9]+)/',
        ) ;

        $replacements = array(
            'entry.\1_\2_',
            'entry.\1_',
            'entry.\1',
            'entry_\1_\2_',
            'entry_\1_',
            'entry_\1',
        ) ;

        foreach ($replacements as $key => $value)
            $replacements[$key] = sprintf('%s%s', $uid, $value) ;

        //  Handle form id attribute
        $patterns[] = '/id="ss-form"/' ;
        $replacements[] = sprintf('id="%sss-form"', $uid) ;

        //  Handle submit button id attribute
        $patterns[] = '/id="ss-submit"/' ;
        $replacements[] = sprintf('id="%sss-submit"', $uid) ;

        //  Handle "submit another response" link
        //$patterns[] = '/' . $form . '/' ;
        //$replacements[] = "ZZZ" ;

        //  Process HTML replacements
        $html = preg_replace($patterns, $replacements, $html) ;

        if (WPGFORM_DEBUG)
        {
            wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;
            wpgform_htmlspecialchars_preprint_r($html) ;
        }

        //  Did we end up with anything prior to the first DIV?  If so, remove it as
        //  it should have been removed by wp_kses() but sometimes stuff slips through!

        $first_div = strpos($html, '<div') ;

        if (WPGFORM_DEBUG)
        {
            wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;
            wpgform_htmlspecialchars_preprint_r($html) ;
            wpgform_preprint_r($first_div) ;
        }

        //  If there are no DIVs, then we have garbage and should stop now!

        if ($first_div === false)
        {
            return sprintf('<div class="wpgform-google-error gform-google-error">%s</div>',
               __('Unexpected content encountered, unable to retrieve Google Form.', WPGFORM_I18N_DOMAIN)) ;
        }

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;

        //  Strip off anything prior to the first  DIV, we don't want it.

        if ($first_div !== 0)
            $html = substr($html, $first_div) ;

        //  The Google Forms have some Javascript embedded in them which needs to be
        //  stripped out.  I am not sure why it is in there, it doesn't appear to do
        //  much of anything useful.

        $html = preg_replace('#<script[^>]*>.*?</script>#is',
            '<!-- Google Forms unnessary Javascript removed -->' . PHP_EOL, $html) ;

        //  Allow H1 tags through if user wants them (which is the default)

        if (!$title)
            $html = preg_replace('#<h1[^>]*>.*?</h1>#is', '', $html) ;

        //  Map H1 tags to H2 tags?

        if ($maph1h2)
        {
            $html = preg_replace('/<h1/i', '<h2', $html) ;
            $html = preg_replace('/h1>/i', 'h2>', $html) ;
        }

        //  Augment labels with some sort of a suffix?

        if (!is_null($suffix))
            $html = preg_replace('/<\/label>/i', "{$suffix}</label>", $html) ;

        //  Need to extract form action and rebuild form tag, and add hidden field
        //  which contains the original action.  This action is used to submit the
        //  form via wp_remote_post().

        if (preg_match_all('/(action(\\s*)=(\\s*)([\"\'])?(?(1)(.*?)\\2|([^\s\>]+)))/', $html, $matches)) 
        { 
            for ($i=0; $i< count($matches[0]); $i++)
            {
                $action = $matches[0][$i] ;
            }

            //$html = str_replace($action, 'action="' . get_permalink(get_the_ID()) . '"', $html) ;
            $html = str_replace($action, 'action=""', $html) ;
            $action = preg_replace(array('/^action=/i', '/"/'), array('', ''), $action) ;

            $action = base64_encode(json_encode($action)) ;

            $wgformid = self::$wpgform_form_id++ ;
            $nonce = wp_nonce_field('wpgform_submit', 'wpgform_nonce', true, false) ;

            //  Add some hidden input fields to faciliate control of subsquent actions
            $html = preg_replace('/<\/form>/i',
                $nonce . "<input type=\"hidden\" value='{$action}' name=\"wpgform-action\"><input type=\"hidden\" value=\"{$wgformid}\" name=\"wpgform-form-id\"></form>", $html) ;
        } 
        else 
        {
            $action = null ;
            $wgformid = self::$wpgform_form_id++ ;
        }
        
        //  Handle "placeholders"
 
        $fields = wpgform_placeholder_meta_box_content(true) ;

        foreach ($fields as $field)
        {
            if ('placeholder' == $field['type'])
            {
                //  When using deprecated gform shortcode there is no meta data
                if (!array_key_exists('id', $o)) continue ;

    	        $meta_field = get_post_meta($o['id'], $field['id'], true);
                $meta_type = get_post_meta($o['id'], $field['type_id'], true);
                $meta_value = get_post_meta($o['id'], $field['value_id'], true);

                if (!empty($meta_field)) {
                    foreach ($meta_field as $key => $value)
                    {
                        $pattern = sprintf('/name="%s"/', $meta_field[$key]) ;
                        $replacement = sprintf('name="%s" placeholder="%s"',
                            $meta_field[$key], $meta_value[$key]) ;
                        $html = preg_replace($pattern, $replacement, $html) ;
                    }
                }
            }
        }

        //  The Unite theme from Paralleus mucks with the submit buttons
        //  which breaks the ability to submit the form to Google correctly.
        //  This hack will "unbreak" the submit buttons.

        if ($unitethemehack)
            $html = preg_replace('/<input type="submit"/i', '<input class="noStyle" type="submit"', $html) ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;

        //  Encode all of the short code options so they can
        //  be referenced if/when needed during form processing.

        $html = preg_replace('/<\/form>/i', "<input type=\"hidden\" value=\"" .
            esc_attr(base64_encode(json_encode($o))) . "\" name=\"wpgform-options\"></form>", $html) ;

        //  Output custom CSS?

        $css = '' ;
 
        if (($wpgform_options['custom_css'] == 1) && !empty($wpgform_options['custom_css_styles']))
            $css .= '<style>' . $wpgform_options['custom_css_styles'] . '</style>' ;

        //  Output form specific custom CSS?
 
        if (($wpgform_options['custom_css'] == 1) && !empty($o['form_css']))
            $css .= '<style>' . $o['form_css'] . '</style>' ;

        //  Tidy up CSS to ensure it isn't affected by 'the_content' filters
        $patterns = array('/[\r\n]+/', '/ +/') ;
        $replacements = array('', ' ') ;
        $css = preg_replace($patterns, $replacements, $css) . PHP_EOL ;

        //  Output Javscript for form validation, make sure any class prefix is included
        //  Need to fix the name arguments for checkboxes so PHP will pass them as an array correctly.
        //  This jQuery script reformats the checkboxes so that Googles Python script will read them.

        //$vMsgs_js = &self::$vMsgs_js ;
        //$vRules_js = &self::$vRules_js ;
        $vMsgs_js = array() ;
        $vRules_js = array() ;

        $js = sprintf('
<script type="text/javascript">
//  Google Forms v%s jQuery script
jQuery(document).ready(function($) {
', WPGFORM_VERSION) ;

        //  Insert breaks between labels and input fields?
        if ($br) $js .= sprintf('
    //  Insert br elements before input and textarea boxes
    $("#%sss-form textarea").before("<br/>");
    $("#%sss-form input[type=text]").before("<br/>");
', $uid, $uid) ;

        //  Did short code specify a CSS prefix?
        if (!is_null($prefix)) $js .= sprintf('
    //  Manipulate CSS classes to account for CSS prefix
    $("div.ss-form-container [class]").each(function(i, el) {
        var c = $(this).attr("class").split(" ");
        for (var i = 0; i < c.length; ++i) {
            $(this).removeClass(c[i]).addClass("%s" + c[i]);
        }
    });
    $("div.ss-form-container").removeClass("ss-form-container").addClass("%s" + "ss-form-container");
', $prefix, $prefix) ;

        //  Hide Google Legal Stuff?
        if (!(bool)$legal)
        {
            $js .= sprintf('
    //  Hide Google Legal content
    $("div.%sss-legal").hide();
', $prefix) ;

            //  Somewhat unsupported but it works, a Google Spreadsheet can
            //  be rendered by Google Forms.  If the Legal is disabled,
            //  the block of code that Google adds to the form is removed.

            $js .= sprintf('
    //  Remove Powered by Google content
    $("div div span.powered").parent().empty();
') ;
        }

        //  Is Email User enabled?
        if ($user_email)
        {
            $js .= sprintf('
    //  Construct Email User Validation
    if ($("#%sss-form input[type=submit][name=submit]").length) {
        $("#%sss-form input[type=submit][name=submit]").before(\'%s\');
        $("div.wpgform-user-email").show();
        $.validator.addClassRules("wpgform-user-email", {
            required: true
        });
    }
', $uid, $uid, $user_email_html) ;
            $vRules_js[] = '    "wpgform-user-email": { email: true }' ;
            $vMsgs_js[] = sprintf('    "wpgform-user-email": "%s"', __('A valid email address is required.', WPGFORM_I18N_DOMAIN)) ;
        }

        //  Is CAPTCHA enabled?
        if ($captcha)
        {
            $js .= sprintf('
    //  Construct CAPTCHA
    $.validator.methods.equal = function(value, element, param) { return value == param; };
    if ($("#%sss-form input[type=submit][name=submit]").length) {
        $("#%sss-form input[type=submit][name=submit]").before(\'%s\');
        $("div.wpgform-captcha").show();
        $.validator.addClassRules("wpgform-captcha", {
            required: true,
        });
    }
', $uid, $uid, $captcha_html, self::$wpgform_captcha['c']) ;

            $vRules_js[] = sprintf('    "%swpgform-captcha": { equal: %s }', $uid, self::$wpgform_captcha['x']) ;
            $vMsgs_js[] = sprintf('    "%swpgform-captcha": "%s" ', $uid, __('Incorrect answer.', WPGFORM_I18N_DOMAIN)) ;
        }

        //  Build extra jQuery Validation rules

        $fields = wpgform_validation_meta_box_content(true) ;

        $patterns = array('/^entry.([0-9]+).(single|group)./', '/^entry.([0-9]+)_/', '/^entry.([0-9]+)/') ;
        $replacements = array('entry_\1_\2_', 'entry_\1_', 'entry_\1') ;

        foreach ($fields as $field)
        {
            if ('validation' == $field['type'])
            {
                //  When using deprecated gform shortcode there is no meta data
                if (!array_key_exists('id', $o)) continue ;

    	        $meta_field = get_post_meta($o['id'], $field['id'], true);
                $meta_type = get_post_meta($o['id'], $field['type_id'], true);
                $meta_value = get_post_meta($o['id'], $field['value_id'], true);

                if (!empty($meta_field))
                {
                    foreach ($meta_field as $key => $value)
                    {
                        $mf = preg_replace($patterns, $replacements, $meta_field[$key]) ;

                        if (!empty($value))
                        {
                            if ($meta_type[$key] == 'regex')
                            {
                                $extras[$value][] = sprintf('%s: "%s"',
                                    $meta_type[$key], empty($meta_value[$key]) ? 'true' : $meta_value[$key]) ;
                            }
                            elseif (($meta_type[$key] == 'required') || ($meta_type[$key] == 'email'))
                            {
                                $vRules_js[] = sprintf('    "%s": { %s: true }', $value, $meta_type[$key]) ;
                                if (!empty($meta_value[$key]))
                                    $vMsgs_js[] = sprintf('    "%s": "%s"', $value, $meta_value[$key]) ;
                            }
                            else
                            {
                                $extras[$value][] = sprintf('%s: %s',
                                    $meta_type[$key], empty($meta_value[$key]) ? 'true' : $meta_value[$key]) ;
                            }
                        }
                    }
                }
            }
        }

        //  Include jQuery validation?
        if ($validation) $js .= sprintf('
    //  jQuery inline validation
    $("div > .ss-item-required textarea").addClass("wpgform-required");
    $("div > .ss-item-required input:not(.ss-q-other)").addClass("wpgform-required");
    $("div > .%sss-item-required textarea").addClass("wpgform-required");
    $("div > .%sss-item-required input:not(.%sss-q-other)").addClass("wpgform-required");
    $.validator.addClassRules("wpgform-required", { required: true });
    $.validator.addMethod("regex", function(value, element, regexp) { var re = new RegExp(regexp); return this.optional(element) || re.test(value); }, "Please check your input.");
', $prefix, $prefix, $prefix, '', '', __('Please check your input.', WPGFORM_I18N_DOMAIN)) ;


        //  Now the tricky part - need to output rules and messages
        if ($validation)
        {
            $js .= sprintf('
    $("#%sss-form").validate({
        invalidHandler: function(event, validator) {
            // \'this\' refers to the form
            var errors = validator.numberOfInvalids();
            if (errors) {
              var message = errors == 1
                ? \'You missed 1 field. It has been highlighted\'
                : \'You missed \' + errors + \' fields. They have been highlighted\';
              $("div.error span").html(message);
              $("div.error").show();
              //$("div.error-message").show();
            } else {
              $("div.error").hide();
              //$("div.error-message").hide();
            }
          },
        //errorClass: "wpgform-error",
        errorClass: "error-message",
        rules: {%s', $uid, PHP_EOL) ;
            if (!empty($extras))
            {
                foreach ($extras as $key => $value)
                {
                    $js .= sprintf('           "%s%s": {', $uid, $key) ;
                    foreach ($value as $vk => $extra)
                    {
                        $k = array_keys($value) ;
                        $js .= sprintf('%s%s', $extra, end($k) === $vk ? '}' : ', ') ;
                    }
                    $k = array_keys($extras) ;
                    $js .= sprintf('%s%s%s', end($k) === $key ? '' : ',', PHP_EOL, end($k) === $key ? '        ' : '') ;
                }
            }


            if (!empty($vRules_js))
            {
                //  Clean up JS if extras were already output
                if (!empty($extras))
                    $js = sprintf('%s,%s', substr($js, 0, strrpos($js, '}') + 1),  PHP_EOL) ;

                foreach ($vRules_js as $rk => $r)
                {
                    $k = array_keys($vRules_js) ;
                    $js .= sprintf('       %s%s', $r, end($k) === $rk ? sprintf('%s        },', PHP_EOL) : sprintf(',%s', PHP_EOL)) ;
                }
            }
            else
                $js .= '},' ;

            $js .= sprintf('%s        messages: {%s', PHP_EOL, PHP_EOL) ;

            if (!empty($vMsgs_js))
            {
                foreach ($vMsgs_js as $mk => $m)
                {
                    $k = array_keys($vMsgs_js) ;
                    $js .= sprintf('       %s%s', $m, end($k) === $mk ? sprintf('%s        },', PHP_EOL) : sprintf(',%s', PHP_EOL)) ;
                }
            }
            else
                $js .= '        }' ;
            $js .= '
    }) ;' . PHP_EOL ;
        }
 
        //  Handle hidden fields

        $u = wp_get_current_user() ;
        $unknown = __('Unknown', WPGFORM_I18N_DOMAIN) ;

        $values = array(
            'value' => $unknown
           ,'url' => array_key_exists('URL', $_SERVER) ? $_SERVER['URL'] : $unknown
           ,'timestamp' => current_time('Y-m-d H:i:s')
           ,'remote_addr' => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : $unknown
           ,'remote_host' => array_key_exists('REMOTE_HOST', $_SERVER) ? $_SERVER['REMOTE_HOST'] : $unknown
           ,'http_referer' => array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : $unknown
           ,'http_user_agent' => array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : $unknown
           ,'user_email' => ($u instanceof WP_User) ? $u->user_email : $unknown
           ,'user_login' => ($u instanceof WP_User) ? $u->user_login : $unknown
        ) ;

        //  We'll ignore the optional value for any field type except value, url, or timestamp
        $ignore = array_slice(array_keys($values), 3) ;
 
        //  Handle and "hiddenfields"
        $fields = wpgform_hiddenfields_meta_box_content(true) ;

        $patterns = array('/^entry.([0-9]+).(single|group)./', '/^entry.([0-9]+)_/', '/^entry.([0-9]+)/') ;
        $replacements = array('entry_\1_\2_', 'entry_\1_', 'entry_\1') ;

        foreach ($fields as $field)
        {
            if ('hiddenfield' == $field['type'])
            {
                //  When using deprecated gform shortcode there is no meta data
                if (!array_key_exists('id', $o)) continue ;

    	        $meta_field = get_post_meta($o['id'], $field['id'], true);
                $meta_type = get_post_meta($o['id'], $field['type_id'], true);
                $meta_value = get_post_meta($o['id'], $field['value_id'], true);

                foreach ($replacements as $key => $value)
                    $replacements[$key] = sprintf('%s%s', $uid, $value) ;

                if (!empty($meta_field))
                {
                    foreach ($meta_field as $key => $value)
                    {
                        $mf = preg_replace($patterns, $replacements, $meta_field[$key]) ;
    
                        if (empty($meta_value[$key]) || in_array($meta_type[$key], $ignore))
                            $meta_value[$key] = $values[$meta_type[$key]] ;
    
                        if (!empty($mf))
                        {
                            $js .= sprintf('    $("#%s").val("%s");%s', $mf, $meta_value[$key], PHP_EOL) ;
                            $js .= sprintf('    $("#%s").parent().css("display", "none");%s', $mf, PHP_EOL) ;
                        }
                    }
                }
            }
        }

        //  Always include the jQuery to clean up the checkboxes
        $js .= sprintf('
    //  Fix checkboxes to work with Google Python
    $("div.%sss-form-container input:checkbox").each(function(index) {
        this.name = this.name + \'[]\';
    });
', $prefix) ;

//    if ($("div.%sss-radio label:last+span.%sss-q-other-container").length) {
//        $("div.%sss-radio label:last+span.%sss-q-other-container").prev().contents().filter(function() {
//            return this.nodeType == 3;
//        })[0].nodeValue = "%s";
//    }
        //  Replace Google supplied text?  Form specific or global?
        if ($local_override_google_default_text) $js .= sprintf('
    //  Replace Google supplied text with "form specific override" values
    $("div.%sss-required-asterisk").text("* %s");
    $("div.%sss-radio div.%sss-printable-hint").text("%s");
    if ($("div.%sss-radio label:last+span.%sss-q-other-container").length) {
        $("div.%sss-radio label:last+span.%sss-q-other-container").prev().find("span.%sss-choice-label").text("%s");
    }
    $("div.%sss-checkbox div.%sss-printable-hint").text("%s");
    $("div.%sss-form-container :input[name=\"back\"]").attr("value", "\u00ab %s");
    $("div.%sss-form-container :input[name=\"continue\"]").attr("value", "%s \u00bb");
    $("div.%sss-form-container :input[name=\"submit\"]").attr("value", "%s");'
        ,$prefix, $o['required_text_override']
        ,$prefix, $prefix, $o['radio_buttons_text_override']
        ,$prefix, $prefix
        ,$prefix, $prefix, $prefix, $o['radio_buttons_other_text_override']
        ,$prefix, $prefix, $o['check_boxes_text_override']
        ,$prefix, $o['back_button_text_override']
        ,$prefix, $o['continue_button_text_override']
        ,$prefix, $o['submit_button_text_override']) ;

        elseif ($global_override_google_default_text) $js .= sprintf('
    //  Replace Google supplied text with "global override" values
    $("div.%sss-required-asterisk").text("* %s");
    $("div.%sss-radio div.%sss-printable-hint").text("%s");
    if ($("div.%sss-radio label:last+span.%sss-q-other-container").length) {
        $("div.%sss-radio label:last+span.%sss-q-other-container").prev().contents().filter(function() {
            return this.nodeType == 3;
        })[0].nodeValue = "%s";
    }
    $("div.%sss-checkbox div.%sss-printable-hint").text("%s");
    $("div.%sss-form-container :input[name=\"back\"]").attr("value", "\u00ab %s");
    $("div.%sss-form-container :input[name=\"continue\"]").attr("value", "%s \u00bb");
    $("div.%sss-form-container :input[name=\"submit\"]").attr("value", "%s");'
        ,$prefix, $wpgform_options['required_text_override']
        ,$prefix, $prefix, $wpgform_options['radio_buttons_text_override']
        ,$prefix, $prefix
        ,$prefix, $prefix, $wpgform_options['radio_buttons_other_text_override']
        ,$prefix, $prefix, $wpgform_options['check_boxes_text_override']
        ,$prefix, $wpgform_options['back_button_text_override']
        ,$prefix, $wpgform_options['continue_button_text_override']
        ,$prefix, $wpgform_options['submit_button_text_override']) ;

        //  Before closing the <script> tag, is the form read only?
        if ($readonly) $js .= sprintf('
    //  Put form in read-only mode
    $("div.%sss-form-container :input").attr("disabled", true);
        ', $prefix) ;

        //  Before closing the <script> tag, is this the confirmation
        //  AND do we have a custom confirmation page or alert message?

        if (self::$posted && is_null($action) && !is_null($alert) &&
            (self::$wpgform_submitted_form_id == self::$wpgform_form_id - 1))
        {
            $js .= PHP_EOL . '    alert("' . $alert . '") ;' ;
        }

        //  Add jQuery to support multiple columns
        $js .= sprintf('
    //  Columnize the form
    //  Make sure we don\'t split labels and input fields
    $("div.%sss-item").addClass("wpgform-dontsplit");
    //  Wrap all of the form content in a DIV so it can be split
    $("#%sss-form").wrapInner("<div style=\"border: 0px dashed blue;\" class=\"wpgform-wrapper\"></div>");
    //  Columnize the form content taking into account the new minwidth setting.
    $(function(){
        var width = $(window).width();
        var minwidth = %s;
        if (minwidth == 0 || width > minwidth) {
        $(".wpgform-wrapper").columnize({
            columns : %s,
            columnFloat : "%s",
            cssClassPrefix : "wpgform"
        });
        //  Wrap each column so it can styled easier
        $(".wpgform-column").wrapInner("<div style=\"border: 0px dashed green;\" class=\"wpgform-column-wrapper\"></div>");
        }
    });
    $("#%sss-form").append("<div style=\"border: 0px dashed black; clear: both;\"></div>");
    $("div.%sss-form-container").after("<div style=\"border: 0px dashed black; clear: both;\"></div>");
        ', $prefix, $uid, $minvptwidth, $columns, $columnorder, $uid, $prefix) ;

        //  Remap the re-submit form URL
        $js .= sprintf('
    //  Change "Submit another response" URL to point to WordPress URL
    //  Changing the HREF attribute to an empty string results in the URL
    //  being the page the form is on.
    $("a.ss-bottom-link").attr("href", "");
') ;

        //  Load the confirmation URL via AJAX?
        if (self::$posted && is_null($action) && !is_null($confirm) &&
            (self::$wpgform_submitted_form_id == self::$wpgform_form_id - 1) &&
            ($style === WPGFORM_CONFIRM_AJAX) && !self::$post_error)
        {
            $js .= PHP_EOL . '    //  Confirmation page by AJAX page load' ;
            //$js .= PHP_EOL . '    $("body").load("' . $confirm . ' body") ;' ;
            $js .= PHP_EOL . '    $.get( "' . $confirm . '", function( data ) {
        $( ".result" ).html( data );
    });' ;
            
        }

        //  Load the confirmation URL via Redirect?
        if (self::$posted && is_null($action) && !is_null($confirm) &&
            (self::$wpgform_submitted_form_id == self::$wpgform_form_id - 1) &&
            ($style === WPGFORM_CONFIRM_REDIRECT) && !self::$post_error)
        {
            $js .= PHP_EOL . '    //  Confirmation page by redirect' ;
            $js .= PHP_EOL . '    window.location.replace("' . $confirm . '") ;' ;
        }

        $js .= PHP_EOL . '});' . PHP_EOL . '</script>' ;

        //  Tidy up Javascript to ensure it isn't affected by 'the_content' filters
        //$js = preg_replace($patterns, $replacements, $js) . PHP_EOL ;

        //  Send email?
        if (self::$posted && is_null($action) && ($email || $user_email))
        {
            if (is_null($sendto) || empty($sendto)) $sendto = get_bloginfo('admin_email') ;

            //  If sending notification email, need to account for more than one address
            if ($email)
                foreach (explode(';', $sendto) as $s)
                    if (is_email(trim($s)))
                        wpGForm::SendConfirmationEmail($wpgform_options['email_format'], trim($s), $results) ;

            //  Send confirmation email?
            if ($user_email && is_email(self::$wpgform_user_sendto))
                wpGForm::SendConfirmationEmail($wpgform_options['email_format'], self::$wpgform_user_sendto) ;
        }

        //  Check browser compatibility?  The jQuery used by this plugin may
        //  not work correctly on old browsers or IE running in compatibility mode.

        if ($wpgform_options['browser_check'] == 1)
        {
            require_once(ABSPATH . '/wp-admin/includes/dashboard.php') ;
 
            //  Let's check the browser version just in case ...

            self::$browser_check = wp_check_browser_version();

            if (self::$browser_check && self::$browser_check['upgrade'])
            {
		        if (self::$browser_check['insecure'])
                    $css .= '<div class="wpgform-browser-warning gform-browser-warning"><h4>' .
                        __('Warning:  You are using an insecure browser!') . '</h4></div>' ;
		        else
                    $css .= '<div class="wpgform-browser-warning gform-browser-warning"><h4>' .
                        __('Warning:  Your browser is out of date!  Please update now.') . '</h4></div>' ;
	        }
        }

        if (WPGFORM_DEBUG)
            $debug = '<h2 class="wpgform-debug gform-debug"><a href="#" class="wpgform-debug-wrapper gform-debug-wrapper">Show wpGForm Debug Content</a></h2>' ;
        else
            $debug = '' ;

        //  Assemble final HTML to return.  To handle pages with more than one
        //  form, Javascript, CSS, and debug control should only be rendered once!
 
        $onetime_html = '' ;

        if (WPGFORM_DEBUG)
        {
            printf('<h2>%s:  %s</h2>', __('Form Id:', WPGFORM_I18N_DOMAIN), self::$wpgform_form_id - 1) ;
            if (!is_null(self::$wpgform_submitted_form_id))
                printf('<h2>%s:  %s</h2>', __('Submitted Form Id',
                    WPGFORM_I18N_DOMAIN), self::$wpgform_submitted_form_id) ;
            else
                printf('<h2>%s:</h2>', __('No Submitted Form Id', WPGFORM_I18N_DOMAIN)) ;
        }

        if (!self::$wpgform_js)
        {
            if (is_null(self::$wpgform_submitted_form_id) ||
                self::$wpgform_submitted_form_id == self::$wpgform_form_id - 1)
            {
                //self::$wpgform_js = true ;
                self::$wpgform_footer_js .= $js ;
            }
        }

        if (!self::$wpgform_css)
        {
            $onetime_html .= PHP_EOL . $css ;
            self::$wpgform_css = true ;
        }

        if (!self::$wpgform_debug)
        {
            $onetime_html .= $debug ;
            self::$wpgform_debug = true ;
        }

        $html = $onetime_html . $html ;

        //  Log form submission?
        if (self::$posted && is_null($action) && $wpgform_options['form_submission_log'] == 1)
        {
            $unknown = __('Unknown', WPGFORM_I18N_DOMAIN) ;

            $log = array(
                'url' => array_key_exists('URL', $_SERVER) ? $_SERVER['URL'] : $unknown
               ,'timestamp' => current_time('Y-m-d H:i:s')
               ,'remote_addr' => array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : $unknown
               ,'remote_host' => array_key_exists('REMOTE_HOST', $_SERVER) ? $_SERVER['REMOTE_HOST'] : $unknown
               ,'http_referer' => array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : $unknown
               ,'http_user_agent' => array_key_exists('HTTP_USER_AGENT', $_SERVER) ? $_SERVER['HTTP_USER_AGENT'] : $unknown
               ,'form' => array_key_exists('id', $o) ? $o['id'] : null
               ,'post_id' => get_the_ID()
            ) ;
               
            //  Try and log against the Google Form post ID but
            //  fall back to Post or Page ID if the old short code
            //  is being used.
 
            if (!is_null($log['form']))
                add_post_meta($log['form'], WPGFORM_LOG_ENTRY_META_KEY, $log, false) ;
        }

        return $html ;
    }

    /**
     * Function ConstructGoogleForm loads HTML from a Google Form URL,
     * processes it, and inserts it into a WordPress filter to output
     * as part of a post, page, or widget.
     *
     * @param $options array Values passed from the shortcode.
     * @return An HTML string if successful, false otherwise.
     * @see RenderGoogleForm
     */
    static function ProcessGoogleForm()
    {
        $tabFound = false ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;
        if (!empty($_POST) && array_key_exists('wpgform-action', $_POST))
        {
            //  Verify WordPress nonce
            if ( ! isset( $_POST['wpgform_nonce'] ) || ! wp_verify_nonce( $_POST['wpgform_nonce'], 'wpgform_submit' ) ) {
                wp_die('Sorry, your nonce did not verify.');
                exit;
            }
        
            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;

            self::$posted = true ;

            $wpgform_options = wpgform_get_plugin_options() ;

            if (WPGFORM_DEBUG && $wpgform_options['http_request_timeout'])
                $timeout = $wpgform_options['http_request_timeout_value'] ;
            else
                $timeout = $wpgform_options['http_api_timeout'] ;

            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
            if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;
            
            //  Need the form ID to handle multiple forms per page
            if (array_key_exists('wpgform-user-email', $_POST))
            {
                self::$wpgform_user_sendto = sanitize_email($_POST['wpgform-user-email']) ;
                unset($_POST['wpgform-user-email']) ;
            }

            //  Need the form ID to handle multiple forms per page
            self::$wpgform_submitted_form_id = absint(sanitize_text_field($_POST['wpgform-form-id'])) ;
            unset($_POST['wpgform-form-id']) ;

            //  Need the action which was saved during form construction
            $action = json_decode(base64_decode(sanitize_text_field($_POST['wpgform-action'])), true) ;

            unset($_POST['wpgform-action']) ;

            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm (action)') ;
            if (WPGFORM_DEBUG) wpgform_preprint_r($action) ;

            //  As a safety precaution make sure the action provided resolves to Google (docs.google.com drive.google.com).
            if (!preg_match( '/^(http|https):\\/\\/(docs|drive)\.google\.com/i' ,$action))
            {
                wp_die(sprintf('<div class="wpgform-google-error gform-google-error">%s</div>',
                   __('Google Form submit action does not resolve to <b>drive.google.com</b>.  Form submission aborted.', WPGFORM_I18N_DOMAIN))) ;
            }

            $options = json_decode(base64_decode(sanitize_text_field($_POST['wpgform-options'])), true) ;
            unset($_POST['wpgform-options']) ;

            if (WPGFORM_DEBUG) wpgform_preprint_r($options) ;
            $form = $options['form'] ;
            $uid = $options['uid'] ;

            //$body = '' ;
            $body = array() ;

            //  The name of the form fields are munged, they need
            //  to be restored before the parameters can be posted

            $patterns = array('entry_([0-9]+)_(single|group)_', 'entry_([0-9]+)_', 'entry_([0-9]+)') ;
            $replacements = array('entry.\1.\2.', 'entry.\1.', 'entry.\1') ;

            foreach ($patterns as $key => $value)
                $patterns[$key] = sprintf('/^%s%s/', $uid, $value) ;

            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
            if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;

            foreach ($_POST as $key => $value)
            {
                if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                if (WPGFORM_DEBUG) wpgform_preprint_r($key, $value) ;

                //  Need to handle parameters passed as array values
                //  separately because of how Python (used Google)
                //  handles array arguments differently than PHP does.

                if (is_array($_POST[$key]))
                {
                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                    $pa = &$_POST[$key] ;
                    foreach ($pa as $pv)
                    {
                        //$body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($pv) . '&' ;
                        $formkey = preg_replace($patterns, $replacements, $key);
                            $body[$formkey][] = $pv;
                    }
                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                }
                else if ($key === 'draftResponse')
                {
                    //  draftResponse is a special parameter for multi-page forms and needs
                    //  some special processing.  We need to remove the escapes on double quotes,
                    //  handled embedded tabs, and encoded ampersands.

                    $patterns = array('/\\\"/', '/\\\t/', '/\\\u0026/', '/\\\n/') ;
                    $replacements = array('"', 't', '&', '\n') ;

                    $value = preg_replace($patterns, $replacements, $value) ;

                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                    //$body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($value) . '&' ;
                    $formkey = preg_replace($patterns, $replacements, $key);
                    $body[$formkey] = $value;
                }
                else
                {
                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                    //$body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($value) . '&' ;
                    $formkey = preg_replace($patterns, $replacements, $key);
                    $body[$formkey] = $value;
                }
            }

            $form = str_replace($action, 'action=""', $form) ;

            //  WordPress converts all of the ampersand characters to their
            //  appropriate HTML entity or some variety of it.  Need to undo
            //  that so the URL can be actually be used.
    
            //$body = stripslashes_deep(urldecode($body)) ;
            //$body = stripslashes_deep($body) ;
            //  Clean up any single quotes and newlines which are escaped
            $patterns = array('/%5C%27/', '/%5Cn/') ;
            $replacements = array('%27', 'n') ;

            foreach ($body as $key => $value)
                $body[$key] = preg_replace($patterns, $replacements, stripslashes_deep($value)) ;

            $action = str_replace(array('&#038;','&#38;','&amp;'), '&', $action) ;

            if (WPGFORM_DEBUG)
            {
                wpgform_preprint_r($action) ;
                wpgform_preprint_r($body) ;
            }


            //  Special processing for checkboxes!

            $q = http_build_query($body) ;
            $q = preg_replace('/%5B[0-9]+%5D/', '', $q);

            self::$response = wp_remote_post($action,
                //array('sslverify' => false, 'body' => $body, 'timeout' => $timeout)) ;
                array('sslverify' => false, 'body' => $q, 'timeout' => $timeout, 'user-agent' => $_SERVER['HTTP_USER_AGENT'])) ;

            if (WPGFORM_DEBUG)
            {
                wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                wpgform_preprint_r(array('action' => $action, 'sslverify' => false,
                    'body' => $q, 'timeout' => $timeout, 'user-agent' => $_SERVER['HTTP_USER_AGENT'])) ;
            }

            //  Double check response from wp_remote_post()

            if (is_wp_error(self::$response))
            {
                self::$post_error = true ;

                $error_string = self::$response->get_error_message();
                echo '<div id="message" class="wpgform-google-error"><p>' . $error_string . '</p></div>';
                if (WPGFORM_DEBUG)
                {
                    //printf('<h2>%s::%s</h2>', basename(__FILE__), __LINE__) ;
                    //print '<pre>' ;
                    //print_r(self::$response) ;
                    //print '</pre>' ;
                    wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                    wpgform_preprint_r(self::$response) ;
                }

                return sprintf('<div class="wpgform-google-error gform-google-error">%s</div>',
                   __('Unable to submit Google Form.  Please try reloading this page.', WPGFORM_I18N_DOMAIN)) ;
            }
        }
    }

    /**
     * Get Page URL
     *
     * @return string
     */
    function GetPageURL()
    {
        global $pagenow ;
        $pageURL = 'http' ;

        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on') $pageURL .= 's' ;

        $pageURL .= '://' ;

        if ($_SERVER['SERVER_PORT'] != '80')
            $pageURL .= $_SERVER['SERVER_NAME'] .
                ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'] ;
        else
            $pageURL .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ;

        return $pageURL ;
    }
            
    /**
     * WordPress Shortcode handler.
     *
     * @return HTML
     */
    function RenderGoogleForm($atts) {
        /*
        $params = shortcode_atts(array(
            'form'           => false,                   // Google Form URL
            'confirm'        => false,                   // Custom confirmation page URL to redirect to
            'alert'          => null,                    // Optional Alert Message
            'class'          => 'wpgform',                 // Container element's custom class value
            'legal'          => 'on',                    // Display Google Legal Stuff
            'br'             => 'off',                   // Insert <br> tags between labels and inputs
            'columns'        => '1',                     // Number of columns to render the form in
            'suffix'         => null,                    // Add suffix character(s) to all labels
            'prefix'         => null,                    // Add suffix character(s) to all labels
            'readonly'       => 'off',                   // Set all form elements to disabled
            'title'          => 'on',                    // Remove the H1 element(s) from the Form
            'maph1h2'        => 'off',                   // Map H1 element(s) on the form to H2 element(s)
            'email'          => 'off',                   // Send an email confirmation to blog admin on submission
            'sendto'         => null,                    // Send an email confirmation to a specific address on submission
            'spreadsheet'    => false,                   // Google Spreadsheet URL
            'captcha'        => 'off',                   // Display a CAPTCHA when enabled
            'validation'     => 'off',                   // Use jQuery validation for required fields
            'unitethemehack' => 'off',                   // Send an email confirmation to blog admin on submission
            'style'          => WPGFORM_CONFIRM_REDIRECT // How to present the custom confirmation after submit
        ), $atts) ;
         */
        $params = shortcode_atts(wpGForm::$options) ;

        return wpGForm::ConstructGoogleForm($params) ;
    }

    /**
     * Send Confirmation E-mail
     *
     * Send an e-mail to the blog administrator informing
     * them of a form submission.
     * 
     * @param string $format - format of email (plain or HTML)
     * @param string $sendto - email address to send content to
     * @param string $results - URL of the spreadsheet which holds submitted data
     */
    static function SendConfirmationEmail($format = WPGFORM_EMAIL_FORMAT_HTML, $sendto = false, $results = null)
    {
        $headers = array() ;
        $wpgform_options = wpgform_get_plugin_options() ;

        if ($sendto === false || $sendto === null) $sendto = get_bloginfo('admin_email') ;

        if ($results === false || $results === null)
            $results = 'N/A' ;
        elseif ($format == WPGFORM_EMAIL_FORMAT_HTML)
            $results = sprintf('<a href="%s">%s</a>',
                $results, __('View Form Results', WPGFORM_I18N_DOMAIN)) ;

        if ($format == WPGFORM_EMAIL_FORMAT_HTML)
        {
            $headers[] = 'MIME-Version: 1.0' . PHP_EOL ;
            $headers[] = 'Content-type: text/html; charset=iso-8859-1' . PHP_EOL ;
        }

        $headers[] = sprintf("From: %s <%s>",
            get_bloginfo('name'), $sendto) . PHP_EOL ;

        $headers[] = sprintf("Cc: %s", $sendto) . PHP_EOL ;

        //  Bcc Blog Admin?
        if ($wpgform_options['bcc_blog_admin'])
            $headers[] = sprintf("Bcc: %s", get_bloginfo('admin_email')) . PHP_EOL ;

        $headers[] = sprintf("Reply-To: %s", $sendto) . PHP_EOL ;
        $headers[] = sprintf("X-Mailer: PHP/%s", phpversion()) ;

        if ($format == WPGFORM_EMAIL_FORMAT_HTML)
        {
            $html = '
                <html>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <head>
                <title>%s</title>
                </head>
                <body>
                <p>
                FYI -
                </p>
                <p>
                %s
                <ul>
                <li>%s:  %s</li>
                <li>%s:  %s</li>
                <li>%s:  %s</li>
                <li>%s: %s</li>
                <li>%s: %s</li>
                </ul>
                </p>
                <p>
                %s,<br/><br/>
                %s
                </p>
                </body>
                </html>' ;

            $message = sprintf($html, get_bloginfo('name'),
                __('A form was submitted on your web site.', WPGFORM_I18N_DOMAIN),
                __('Form', WPGFORM_I18N_DOMAIN), get_the_title(),
                __('URL', WPGFORM_I18N_DOMAIN), get_permalink(),
                __('Responses', WPGFORM_I18N_DOMAIN), $results,
                __('Date', WPGFORM_I18N_DOMAIN), current_time('Y-m-d'),
                __('Time', WPGFORM_I18N_DOMAIN), current_time('H:i'),
                __('Thank you', WPGFORM_I18N_DOMAIN), get_bloginfo('name')) ;
        }
        else
        {
            $plain = 'FYI -' . PHP_EOL . PHP_EOL ;
            $plain .= sprintf('%s:',
                __('A form was submitted on your web site', WPGFORM_I18N_DOMAIN)) . PHP_EOL . PHP_EOL ;
            $plain .= sprintf('%s:', __('Form', WPGFORM_I18N_DOMAIN)) .'  %s' . PHP_EOL ;
            $plain .= sprintf('%s:', __('URL', WPGFORM_I18N_DOMAIN)) .'  %s' . PHP_EOL ;
            $plain .= sprintf('%s:', __('Responses', WPGFORM_I18N_DOMAIN)) .'  %s' . PHP_EOL ;
            $plain .= sprintf('%s:', __('Date', WPGFORM_I18N_DOMAIN)) .'  %s' . PHP_EOL ;
            $plain .= sprintf('%s:', __('Time', WPGFORM_I18N_DOMAIN)) .'  %s' . PHP_EOL . PHP_EOL ;
            
            $plain .= sprintf('%s,', __('Thank you', WPGFORM_I18N_DOMAIN)) . PHP_EOL . PHP_EOL . '%s' . PHP_EOL ;

            $message = sprintf($plain, get_the_title(), get_permalink(),
                $results, current_time('Y-m-d'), current_time('H:i'), get_option('blogname')) ;
        }

        //  TO email address may actually contain multiple addresses.
        //  Need to split the string using semicolon as a delimiter.

        $to = "" ;
        $sendto = explode(';', $sendto) ;

        foreach ($sendto as $s)
            $to .= sprintf('%s wpGForm Contact <%s>', get_option('blogname'), trim($s)) ;

        $subject = sprintf('Form Submission from %s', get_option('blogname')) ;

        if (WPGFORM_DEBUG) 
            wpgform_preprint_r($headers, htmlentities($message)) ;

        $status = wp_mail($to, $subject, $message, $headers) ;

        return $status ;
    }

}

/**
 * wpgform_head()
 *
 * WordPress header actions
 * @see http://scribu.net/wordpress/conditional-script-loading-revisited.html
 */
function wpgform_head()
{
    //  Need to enqueue jQuery or inline jQuery script will fail
    //  Everything else is enqueued if/when the shortcode is processed
    wp_enqueue_script('jquery') ;
    wpgform_register_scripts() ;
    wpgform_enqueue_styles() ;
}

/**
 * wpgform_load_js_css()
 *
 * WordPress header actions
 */
/*
function wpgform_load_js_css()
{
    //  wpGForm needs jQuery!
    wp_enqueue_script('jquery') ;
    
    $wpgform_options = wpgform_get_plugin_options() ;

    //  Load default gForm CSS?
    if ($wpgform_options['default_css'] == 1)
    {
        wp_enqueue_style('wpgform-css',
            plugins_url(plugin_basename(dirname(__FILE__) . '/css/wpgform.css'))) ;
    }

    //  Load the jQuery Validate from the Microsoft CDN, it isn't
    //  available from the Google CDN or I'd load it from there!

    if (defined('SCRIPT_DEBUG')) {
        wp_register_script('jquery-validate',
            '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.js',
            array('jquery'), false, true) ;
    } else {
        wp_register_script('jquery-validate',
            '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js',
            array('jquery'), false, true) ;
    }
    wp_enqueue_script('jquery-validate') ;

    //  Load the jQuery Columnizer script from the plugin
    wp_register_script('jquery-columnizer',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/jquery.columnizer.js')),
        array('jquery'), false, true) ;
    wp_enqueue_script('jquery-columnizer') ;

    //  Load the Google Forms jQuery Validate script from the plugin
    wp_register_script('wpgform-jquery-validate',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/wpgform.js')),
        array('jquery', 'jquery-validate'), false, true) ;
    wp_enqueue_script('wpgform-jquery-validate') ;
    wp_localize_script('wpgform-jquery-validate', 'wpgform_script_vars', array(
        'required' => __('This field is required.', WPGFORM_I18N_DOMAIN),
        'remote' => __('Please fix this field.', WPGFORM_I18N_DOMAIN),
        'email' => __('Please enter a valid email address.', WPGFORM_I18N_DOMAIN),
        'url' => __('Please enter a valid URL.', WPGFORM_I18N_DOMAIN),
        'date' => __('Please enter a valid date.', WPGFORM_I18N_DOMAIN),
        'dateISO' => __('Please enter a valid date (ISO).', WPGFORM_I18N_DOMAIN),
        'number' => __('Please enter a valid number.', WPGFORM_I18N_DOMAIN),
        'digits' => __('Please enter only digits.', WPGFORM_I18N_DOMAIN),
        'creditcard' => __('Please enter a valid credit card number.', WPGFORM_I18N_DOMAIN),
        'equalTo' => __('Please enter the same value again.,', WPGFORM_I18N_DOMAIN),
        'accept' => __('Please enter a value with a valid extension.', WPGFORM_I18N_DOMAIN),
        'maxlength' => __('Please enter no more than {0} characters.', WPGFORM_I18N_DOMAIN),
        'minlength' => __('Please enter at least {0} characters.', WPGFORM_I18N_DOMAIN),
        'rangelength' => __('Please enter a value between {0} and {1} characters long.', WPGFORM_I18N_DOMAIN),
        'range' => __('Please enter a value between {0} and {1}.', WPGFORM_I18N_DOMAIN),
        'max' => __('Please enter a value less than or equal to {0}.', WPGFORM_I18N_DOMAIN),
        'min' => __('Please enter a value greater than or equal to {0}.', WPGFORM_I18N_DOMAIN),
        'regex' => __('Please enter a value which matches {0}.', WPGFORM_I18N_DOMAIN)
    )) ;
}
*/

/**
 * wpgform_register_scripts()
 *
 * WordPress script registration for wpgform
 */
function wpgform_register_scripts()
{
    //  Load the jQuery Validate from the Microsoft CDN, it isn't
    //  available from the Google CDN or I'd load it from there!

    if (defined('SCRIPT_DEBUG')) {
        wp_register_script('jquery-validate',
            '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.js',
            array('jquery'), false, true) ;
    } else {
        wp_register_script('jquery-validate',
            '//ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js',
            array('jquery'), false, true) ;
    }

    //  Load the jQuery Columnizer script from the plugin
    wp_register_script('jquery-columnizer',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/jquery.columnizer.js')),
        array('jquery'), false, true) ;

    //  Load the Google Forms jQuery Validate script from the plugin
    wp_register_script('wpgform-jquery-validate',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/wpgform.js')),
        array('jquery', 'jquery-validate'), false, true) ;
}

/**
 * wpgform_enqueue_scripts()
 *
 * WordPress script enqueuing for wpgform
 */
function wpgform_enqueue_scripts()
{
    //  wpGForm needs jQuery!
    //wp_enqueue_script('jquery') ;
    
    //  Enqueue the jQuery Validate script
    wp_enqueue_script('jquery-validate') ;

    //  Enqueue the jQuery Columnizer script
    wp_enqueue_script('jquery-columnizer') ;

    //  Enqueue the Google Forms jQuery Validate script from the plugin
    wp_enqueue_script('wpgform-jquery-validate') ;
    wp_localize_script('wpgform-jquery-validate', 'wpgform_script_vars', array(
        'required' => __('This field is required.', WPGFORM_I18N_DOMAIN),
        'remote' => __('Please fix this field.', WPGFORM_I18N_DOMAIN),
        'email' => __('Please enter a valid email address.', WPGFORM_I18N_DOMAIN),
        'url' => __('Please enter a valid URL.', WPGFORM_I18N_DOMAIN),
        'date' => __('Please enter a valid date.', WPGFORM_I18N_DOMAIN),
        'dateISO' => __('Please enter a valid date (ISO).', WPGFORM_I18N_DOMAIN),
        'number' => __('Please enter a valid number.', WPGFORM_I18N_DOMAIN),
        'digits' => __('Please enter only digits.', WPGFORM_I18N_DOMAIN),
        'creditcard' => __('Please enter a valid credit card number.', WPGFORM_I18N_DOMAIN),
        'equalTo' => __('Please enter the same value again.,', WPGFORM_I18N_DOMAIN),
        'accept' => __('Please enter a value with a valid extension.', WPGFORM_I18N_DOMAIN),
        'maxlength' => __('Please enter no more than {0} characters.', WPGFORM_I18N_DOMAIN),
        'minlength' => __('Please enter at least {0} characters.', WPGFORM_I18N_DOMAIN),
        'rangelength' => __('Please enter a value between {0} and {1} characters long.', WPGFORM_I18N_DOMAIN),
        'range' => __('Please enter a value between {0} and {1}.', WPGFORM_I18N_DOMAIN),
        'max' => __('Please enter a value less than or equal to {0}.', WPGFORM_I18N_DOMAIN),
        'min' => __('Please enter a value greater than or equal to {0}.', WPGFORM_I18N_DOMAIN),
        'regex' => __('Please enter a value which matches {0}.', WPGFORM_I18N_DOMAIN)
    )) ;
}

/**
 * wpgform_enqueue_styles()
 *
 * WordPress style enqueuing for wpgform
 */
function wpgform_enqueue_styles()
{
    $wpgform_options = wpgform_get_plugin_options() ;

    //  Load default gForm CSS?
    if ($wpgform_options['default_css'] == 1)
    {
        wp_enqueue_style('wpgform-css',
            plugins_url(plugin_basename(dirname(__FILE__) . '/css/wpgform.css'))) ;
    }
}

/**
 * wpgform_footer()
 *
 * WordPress footer actions
 *
 */
function wpgform_footer()
{
    //  Output the generated jQuery script as part of the footer

    if (!wpGForm::$wpgform_footer_js_printed)
    {
        print wpGForm::$wpgform_footer_js ;
        wpGForm::$wpgform_footer_js_printed = true ;
    }
}

function wpgform_pre_http_request($args)
{
    error_log(sprintf('%s::%s -->  %s', basename(__FILE__), __LINE__, print_r($args, true))) ;
    return $args ;
}

//add_filter('pre_http_request', 'wpgform_pre_http_request') ;


function wpgform_http_api_transports($args)
{
    $args = array('fsockopen') ;
    error_log(sprintf('%s::%s -->  %s', basename(__FILE__), __LINE__, print_r($args, true))) ;
    return $args ;
}

//add_filter('http_api_transports', 'wpgform_http_api_transports') ;

function wpgform_curl_transport_missing_notice()
{
    $wpgform_options = wpgform_get_plugin_options() ;

    //  Skip check if disabled in settings
    if ($wpgform_options['curl_transport_missing_message']) return ;

    //  Test for cURL transport

    $t = new WP_Http() ;

    if (strtolower($t->_get_first_available_transport('')) != 'wp_http_curl')
    {
?>
<div class="update-nag">
<?php
        _e('The <a href="http://codex.wordpress.org/HTTP_API">WordPress HTTP API</a> cURL transport was not detected.  The Google Forms plugin may not operate correctly.', WPGFORM_I18N_DOMAIN) ;
?>
<br />
<small>
<?php
        printf(__('This notification may be hidden via a setting on the <a href="%s">Google Forms settings page</a>.',
            WPGFORM_I18N_DOMAIN), admin_url('options-general.php?page=wpgform-options.php')) ;
?>
</small>
</div>
<?php
    }

    unset ($t) ;
}

add_action( 'admin_notices', 'wpgform_curl_transport_missing_notice' );
?>
