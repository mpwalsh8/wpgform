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

    add_filter('the_content', 'wpautop');
    add_action('template_redirect', 'wpgform_head') ;
    add_action('wp_footer', 'wpgform_footer') ;
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
       ,'email_format' => WPGFORM_EMAIL_FORMAT_PLAIN
       ,'http_api_timeout' => 5
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

    $plugin_options = get_option('wpgform_options', $default_options) ;

    //  If the array key doesn't exist, it means it is a check box option
    //  that is not enabled so the array element(s) needs to be set to zero.

    foreach ($default_options as $key => $value)
        if (!array_key_exists($key, $plugin_options)) $plugin_options[$key] = 0 ;

    return $plugin_options ;
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
        'WordPress Google Form Submission Log', /*page title*/
        'Form Submission Log', /*menu title*/
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
    register_setting('wpgform_options', 'wpgform_options') ;
}

/**
 * wpgform_register_activation_hook()
 *
 * Adds the default options so WordPress options are
 * configured to a default state upon plugin activation.
 *
 * @return null
 */
function wpgform_register_activation_hook()
{
    wpgform_init_i18n() ;
    add_option('wpgform_options', wpgform_get_default_plugin_options()) ;
    add_filter('widget_text', 'do_shortcode') ;
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
     * Property to store Javascript output in footer
     */
    static $wpgform_footer_js = '' ;

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
        'form'           => false,          // Google Form URL
        'confirm'        => null,           // Custom confirmation page URL to redirect to
        'alert'          => null,           // Optional Alert Message
        'class'          => 'wpgform',      // Container element's custom class value
        'legal'          => 'on',           // Display Google Legal Stuff
        'br'             => 'off',          // Insert <br> tags between labels and inputs
        'columns'        => '1',            // Number of columns to render the form in
        'suffix'         => null,           // Add suffix character(s) to all labels
        'prefix'         => null,           // Add suffix character(s) to all labels
        'readonly'       => 'off',          // Set all form elements to disabled
        'title'          => 'on',           // Remove the H1 element(s) from the Form
        'maph1h2'        => 'off',          // Map H1 element(s) on the form to H2 element(s)
        'email'          => 'off',          // Send an email confirmation to blog admin on submission
        'sendto'         => null,           // Send an email confirmation to a specific address on submission
        'user_email'     => 'off',          // Send an email confirmation to user on submission
        'user_sendto'    => null,           // Send an email confirmation to a specific address on submission
        'results'        => false,          // Results URL
        'spreadsheet'    => false,          // Google Spreadsheet URL
        'captcha'        => 'off',          // Display a CAPTCHA when enabled
        'validation'     => 'off',          // Use jQuery validation for required fields
        'unitethemehack' => 'off',          // Send an email confirmation to blog admin on submission
        'style'          => null,           // How to present the custom confirmation after submit
    ) ;

    /**
     * Constructor
     */
    function wpGForm()
    {
        // empty for now
    }

    /**
     * 'gform' short code handler
     *
     * @since 0.1
     * @deprecated
     */
    function gform_sc($options)
    {
        if (self::ProcessShortCodeOptions($options))
        {
            return self::ConstructGoogleForm() ;
        }
        else
        {
            return sprintf('<div class="wpgform-google-error gform-google-error">%s</div>',
               __('Unable to process Google Form short code.', WPGFORM_I18N_DOMAIN)) ;
        }
    }

    /**
     * 'wpgform' short code handler
     *
     * @since 1.0
     */
    function wpgform_sc($options)
    {
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
    function ProcessShortCodeOptions($options)
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
    function ProcessWpGFormCPT($options)
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
        {
            return false ;
        }

        // get current form meta data

        $mb = wpgform_form_meta_box_content() ;

        foreach ($mb['fields'] as $field)
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
    function ConstructGoogleForm()
    {
        //  Property short cut
        $o = &self::$options ;

        $wpgform_options = wpgform_get_plugin_options() ;

        if (WPGFORM_DEBUG && $wpgform_options['http_request_timeout'])
            $timeout = $wpgform_options['http_request_timeout_value'] ;
        else
            $timeout = $wpgform_options['http_api_timeout'] ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;

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
            $prefix = $o['prefix'] ;
            $suffix = $o['suffix'] ;
            $confirm = $o['confirm'] ;
            $alert = $o['alert'] ;
            $sendto = $o['sendto'] ;

            //  The old short code supports the 'spreadsheet' attribute which
            //  takes precedence over the new attribute 'results' for backward
            //  compatibility.

            $results = ($o['spreadsheet'] === false) ? $o['results'] : $o['spreadsheet'] ;
        }

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;

        //  Should email confirmation be sent to user?
        $user_email = $o['user_email'] === 'on' ;

        $user_email_html = '' ;
        $user_email_sendto = "" ;

        //  Generate the User Email HTML if requested

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
            $user_email_html .= sprintf('<input style="width: 250px;" type="text" id="wpgform-user-email" class="%sss-q-short" value="%s" name="wpgform-user-email">', $prefix, $user_email_sendto) ;
            $user_email_html .= '</div></div></div>' ;
        }

        //  Display CAPTCHA?
        $captcha_html = '' ;

        $captcha = $o['captcha'] === 'on' ;

        //  Generate the CAPTCHA HTML if requested

        if ($captcha)
        {
            $a = rand(0, 19) ;
            $b = rand(5, 24) ;
            $c = $a + $b ;

            self::$wpgform_captcha = array('a' => $a, 'b' => $b, 'c' => $c) ;

            $captcha_html .= '<div class="wpgform-captcha">' ;
            $captcha_html .= sprintf('<div class="%sss-item %sss-item-required %sss-text">', $prefix, $prefix, $prefix) ;
            $captcha_html .= sprintf('<div class="%sss-form-entry">', $prefix) ;
            $captcha_html .= sprintf('<label for="wpgform-captcha" class="%sss-q-title">What is %s + %s ?', $prefix, $a, $b) ;
            $captcha_html .= sprintf('<span class="%sss-required-asterisk">*</span></label>', $prefix) ;
            $captcha_html .= sprintf('<label for="wpgform-captcha" class="%sss-q-help"></label>', $prefix) ;
            $captcha_html .= sprintf('<input style="width: 100px;" type="text" id="wpgform-captcha" class="%sss-q-short" value="" name="wpgform-captcha">', $prefix) ;
            $captcha_html .= '</div></div></div>' ;
        }

        //  Output the H1 title included in the Google Form?
        $title = $o['title'] === 'on' ;

        //  Map H1 tags to H2 tags?  Apparently helps SEO ...
        $maph1h2 = $o['maph1h2'] === 'on' ;

        //  Insert <br> elements between labels and input boxes?
        $br = $o['br'] === 'on' ;

        //  Use jQuery validation?
        $validation = $o['validation'] === 'on' ;

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

        //  The Unite theme from Paralleus mucks with the submit buttons
        //  which breaks the ability to submit the form to Google correctly.
        //  This hack will "unbreak" the submit buttons.

        $unitethemehack = $o['unitethemehack'] === 'on' ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;

        //  Show the custom confirmation via AJAX instead of redirect?
        $style = $o['style'] === 'none' ? null : $o['style'] ;

        //  WordPress converts all of the ampersand characters to their
        //  appropriate HTML entity or some variety of it.  Need to undo
        //  that so the URL can be actually be used.

        $form = str_replace(array('&#038;','&#38;','&amp;'), '&', $form) ;
        if (!is_null($confirm))
            $confirm = str_replace(array('&#038;','&#38;','&amp;'), '&', $confirm) ;
        
        //  The initial rendering of the form content is done using this
        //  "remote get", all subsequent renderings will be the result of
        //  "post processing".

        if (!self::$posted)
        {
            self::$response = wp_remote_get($form, array('sslverify' => false, 'timeout' => $timeout, 'redirection' => 12)) ;
        }

        //  Retrieve the HTML from the URL

        if (is_wp_error(self::$response))
        {
            $error_string = self::$response->get_error_message();
            echo '<div id="message" class="wpgform-google-error"><p>' . $error_string . '</p></div>';
            if (WPGFORM_DEBUG)
            {
                printf('<h2>%s::%s</h2>', basename(__FILE__), __LINE__) ;
                print '<pre>' ;
                print_r(self::$response) ;
                print '</pre>' ;
                wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;
                wpgform_preprint_r(self::$response) ;
            }

            return sprintf('<div class="wpgform-google-error gform-google-error">%s</div>',
               __('Unable to retrieve Google Form.  Please try reloading this page.', WPGFORM_I18N_DOMAIN)) ;

        }
        else
            $html = self::$response['body'] ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGoogleForm') ;

        //  Need to filter the HTML retrieved from the form and strip off the stuff
        //  we don't want.  This gets rid of the HTML wrapper from the Google page.

        $allowed_tags = array(
            'a' => array('href' => array(), 'title' => array(), 'target' => array())
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
           ,'label' => array('class' => array(), 'for' => array())
           ,'input' => array('id' => array(), 'name' => array(), 'class' => array(), 'type' => array(), 'value' => array(), 'checked' => array())
           ,'select' => array('name' => array(), 'for' => array(), 'checked' => array())
           ,'option' => array('value' => array(), 'selected' => array())
           ,'form' => array('id' => array(), 'class' => array(), 'action' => array(), 'method' => array(), 'target' => array(), 'onsubmit' => array())
           ,'script' => array('type' => array())
           ,'span' => array('class' => array(), 'style' => array())
           ,'style' => array()
           ,'table' => array()
           ,'tbody' => array()
           ,'textarea' => array('id' => array(), 'name' => array(), 'class' => array(), 'type' => array(), 'value' => array(), 'rows' => array(), 'cols' => array())
           ,'thead' => array()
           ,'tr' => array('class' => array())
           ,'td' => array('class' => array(), 'style' => array())
        ) ;

        //  Process the HTML

        $html = wp_kses($html, $allowed_tags) ;

        //  Did we end up with anything prior to the first DIV?  If so, remove it as
        //  it should have been removed by wp_kses() but sometimes stuff slips through!

        $first_div = strpos($html, '<div') ;

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
            $action = base64_encode(serialize($action)) ;
            $wgformid = self::$wpgform_form_id++ ;

            //  Add some hidden input fields to faciliate control of subsquent actions
            $html = preg_replace('/<\/form>/i',
                "<input type=\"hidden\" value=\"{$action}\" name=\"wpgform-action\"><input type=\"hidden\" value=\"{$wgformid}\" name=\"wpgform-form-id\"></form>", $html) ;
        } 
        else 
        {
            $action = null ;
            $wgformid = self::$wpgform_form_id++ ;
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
            base64_encode(serialize($o)) . "\" name=\"wpgform-options\"></form>", $html) ;

        //  Output custom CSS?
 
        if (($wpgform_options['custom_css'] == 1) && !empty($wpgform_options['custom_css_styles']))
            $css = '<style>' . $wpgform_options['custom_css_styles'] . '</style>' ;
        else
            $css = '' ;

        //  Tidy up Javascript to ensure it isn't affected by 'the_content' filters
        $patterns = array('/[\r\n]+/', '/ +/') ;
        $replacements = array('', ' ') ;
        $css = preg_replace($patterns, $replacements, $css) . PHP_EOL ;


        //  Output Javscript for form validation, make sure any class prefix is included
        //  Need to fix the name arguments for checkboxes so PHP will pass them as an array correctly.
        //  This jQuery script reformats the checkboxes so that Googles Python script will read them.

        $vMsgs_js = array() ;
        $vRules_js = array() ;

        $js = sprintf('
<script type="text/javascript">
//  WordPress Google Form v%s jQuery script
jQuery(document).ready(function($) {
', WPGFORM_VERSION) ;

        //  Insert breaks between labels and input fields?
        if ($br) $js .= '
    //  Insert br elements before input and textarea boxes
    $("#ss-form textarea").before("<br/>");
    $("#ss-form input[type=text]").before("<br/>");
' ;

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
        if (!(bool)$legal) $js .= sprintf('
    //  Hide Google Legal content
    $("div.%sss-legal").hide();
', $prefix) ;

        //  Is Email User enabled?
        if ($user_email)
        {
            $js .= sprintf('
    //  Construct Email User Validation
    if ($("#ss-form input[type=submit][name=submit]").length) {
        $("#ss-form input[type=submit][name=submit]").before(\'%s\');
        $("div.wpgform-user-email").show();
        $.validator.addClassRules("wpgform-user-email", {
            required: true
        });
        /*
        $("#ss-form").validate({
            errorClass: "wpgform-error",
			rules: {
				"wpgform-user-email": {
					email: true
				}
			},
			messages: {
				"wpgform-user-email": "A valid email address is required."
			}
		});
        */
    }
', $user_email_html) ;
            $vRules_js[] = '
				"wpgform-user-email": {
					email: true,
				},
' ;
            $vMsgs_js[] = '
				"wpgform-user-email": "A valid email address is required.",
' ;
        }

        //  Is CAPTCHA enabled?
        if ($captcha)
        {
            $js .= sprintf('
    //  Construct CAPTCHA
    $.validator.methods.equal = function(value, element, param) { return value == param; };
    if ($("#ss-form input[type=submit][name=submit]").length) {
        $("#ss-form input[type=submit][name=submit]").before(\'%s\');
        $("div.wpgform-captcha").show();
        $.validator.addClassRules("wpgform-captcha", {
            required: true
        });
        /*
        $("#ss-form").validate({
            errorClass: "wpgform-error",
			rules: {
				"wpgform-captcha": {
					equal: %s
				}
			},
			messages: {
				"wpgform-captcha": "Incorrect answer."
			}
		});
        */
    }
', $captcha_html, self::$wpgform_captcha['c']) ;
            $vRules_js[] = sprintf('
				"wpgform-captcha": {
					equal: %s
				},
', self::$wpgform_captcha['c']) ;
            $vMsgs_js[] = '
				"wpgform-captcha": "Incorrect answer."
' ;
        }

        //  Include jQuery validation?
        if ($validation) $js .= sprintf('
    //  jQuery inline validation
    $("div > .ss-item-required textarea").addClass("wpgform-required");
    $("div > .ss-item-required input:not(.ss-q-other)").addClass("wpgform-required");
    $("div > .%sss-item-required textarea").addClass("wpgform-required");
    $("div > .%sss-item-required input:not(.%sss-q-other)").addClass("wpgform-required");
    $.validator.addClassRules("wpgform-required", { required: true });
    /*
    $("#ss-form").validate({
        errorClass: "wpgform-error",
        rules: {
            %s
        },
        messages: {
            %s
        }
    }) ;
     */
', $prefix, $prefix, $prefix, '', '') ;

        //  Now the tricky part - need to output rules and messages
        if ($validation)
        {
            $js .= '
    $("#ss-form").validate({
        errorClass: "wpgform-error",
        rules: {
' ;
            foreach ($vRules_js as $r)
                $js .= sprintf('%s', $r) ;
            $js .= '
        },
        messages: {
' ;
           foreach ($vMsgs_js as $m)
                $js .= sprintf('%s', $m) ;
           $js .= '
        }
    }) ;
' ;
        }
 
        //  Always include the jQuery to clean up the checkboxes
        $js .= sprintf('
    //  Fix checkboxes to work with Google Python
    $("div.%sss-form-container input:checkbox").each(function(index) {
        this.name = this.name + \'[]\';
    });
', $prefix) ;
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
            $js .= PHP_EOL . 'alert("' . $alert . '") ;' ;
        }

        //  Load the confirmation URL via AJAX?
        if (self::$posted && is_null($action) && !is_null($confirm) &&
            (self::$wpgform_submitted_form_id == self::$wpgform_form_id - 1) &&
            ($style === WPGFORM_CONFIRM_AJAX) && !self::$post_error)
        {
            $js .= PHP_EOL . '$("body").load("' . $confirm . '") ;' ;
        }

        //  Load the confirmation URL via Redirect?
        if (self::$posted && is_null($action) && !is_null($confirm) &&
            (self::$wpgform_submitted_form_id == self::$wpgform_form_id - 1) &&
            ($style === WPGFORM_CONFIRM_REDIRECT) && !self::$post_error)
        {
            $js .= PHP_EOL . 'window.location.replace("' . $confirm . '") ;' ;
        }

        //  Add jQuery to support multiple columns
        $js .= sprintf('
    //  Columnize the form
    //  Make sure we don\'t split labels and input fields
    $("div.%sss-item").addClass("wpgform-dontsplit");
    //  Wrap all of the form content in a DIV so it can be split
    $("#ss-form").wrapInner("<div style=\"border: 0px dashed blue;\" class=\"wpgform-wrapper\"></div>");
    //  Columnize the form content.
    $(function(){
        $(".wpgform-wrapper").columnize({
            columns : %s,
            cssClassPrefix : "wpgform"
        });
        //  Wrap each column so it can styled easier
        $(".wpgform-column").wrapInner("<div style=\"border: 0px dashed green;\" class=\"wpgform-column-wrapper\"></div>");
    });
    $("#ss-form").append("<div style=\"border: 0px dashed black; clear: both;\"></div>");
    $("div.%sss-form-container").after("<div style=\"border: 0px dashed black; clear: both;\"></div>");
});
</script>
        ', $prefix, $columns, $prefix) ;

        //  Tidy up Javascript to ensure it isn't affected by 'the_content' filters
        //$js = preg_replace($patterns, $replacements, $js) . PHP_EOL ;

        //  Send email?
        if (self::$posted && is_null($action) && $email)
        {
            wpGForm::SendConfirmationEmail($wpgform_options['email_format'], $sendto, $results) ;

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
                self::$wpgform_js = true ;
                self::$wpgform_footer_js = $js ;
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
        if (self::$posted && is_null($action))
        {
            $log = array(
                'url' => $_SERVER['URL']
               ,'timestamp' => date('Y-m-d H:i:s')
               ,'remote_addr' => $_SERVER['REMOTE_ADDR']
               ,'remote_host' => $_SERVER['REMOTE_HOST']
               ,'http_referer' => $_SERVER['HTTP_REFERER']
               ,'http_user_agent' => $_SERVER['HTTP_USER_AGENT']
               ,'form' => (array_key_exists('id', $o) ? $o['id'] : null)
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
    function ProcessGoogleForm()
    {
        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;
        if (!empty($_POST) && array_key_exists('wpgform-action', $_POST))
        {
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
                self::$wpgform_user_sendto = $_POST['wpgform-user-email'] ;
                unset($_POST['wpgform-user-email']) ;
            }

            //  Need the form ID to handle multiple forms per page
            self::$wpgform_submitted_form_id = $_POST['wpgform-form-id'] ;
            unset($_POST['wpgform-form-id']) ;

            //  Need the action which was saved during form construction
            $action = unserialize(base64_decode($_POST['wpgform-action'])) ;
            unset($_POST['wpgform-action']) ;
            $options = $_POST['wpgform-options'] ;
            unset($_POST['wpgform-options']) ;
            $options = unserialize(base64_decode($options)) ;

            if (WPGFORM_DEBUG) wpgform_preprint_r($options) ;
            $form = $options['form'] ;

            $body = '' ;

            //  The name of the form fields are munged, they need
            //  to be restored before the parameters can be posted

            $patterns = array('/^entry_([0-9]+)_(single|group)_/', '/^entry_([0-9]+)_/', '/^entry_([0-9]+)/') ;
            $replacements = array('entry.\1.\2.', 'entry.\1.', 'entry.\1') ;

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
                        $body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($pv) . '&' ;
                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                }
                else if ($key === 'draftResponse')
                {
                    //  draftResponse is a special parameter for multi-page forms and needs
                    //  some special processing.  We need to remove the escapes on double quotes.

                    $value = preg_replace('/\\\"/', '"', $value) ;

                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                    $body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($value) . '&' ;
                }
                else
                {
                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
                    $body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($value) . '&' ;
                }
            }

            //$form = str_replace($action, 'action="' . get_permalink(get_the_ID()) . '"', $form) ;
            $form = str_replace($action, 'action=""', $form) ;


            //  WordPress converts all of the ampersand characters to their
            //  appropriate HTML entity or some variety of it.  Need to undo
            //  that so the URL can be actually be used.
    
            $action = str_replace(array('&#038;','&#38;','&amp;'), '&', $action) ;
            if (WPGFORM_DEBUG)
            {
                wpgform_preprint_r($action) ;
                wpgform_preprint_r($body) ;
            }
        
            self::$response = wp_remote_post($action,
                array('sslverify' => false, 'body' => $body, 'timeout' => $timeout)) ;

            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGoogleForm') ;
            //if (WPGFORM_DEBUG) wpgform_preprint_r(self::$response) ;

            //  Double check response from wp_remote_post()

            if (is_wp_error(self::$response))
            {
                self::$post_error = true ;

                $error_string = self::$response->get_error_message();
                echo '<div id="message" class="wpgform-google-error"><p>' . $error_string . '</p></div>';
                if (WPGFORM_DEBUG)
                {
                    printf('<h2>%s::%s</h2>', basename(__FILE__), __LINE__) ;
                    print '<pre>' ;
                    print_r(self::$response) ;
                    print '</pre>' ;
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
    function SendConfirmationEmail($format = WPGFORM_EMAIL_FORMAT_HTML, $sendto = false, $results = null)
    {
        $wpgform_options = wpgform_get_plugin_options() ;

        if ($sendto === false || $sendto === null) $sendto = get_bloginfo('admin_email') ;

        if ($results === false || $results === null)
            $results = 'N/A' ;
        elseif ($format == WPGFORM_EMAIL_FORMAT_HTML)
            $results = sprintf('<a href="%s">%s</a>',
                $results, __('View Form Results', WPGFORM_I18N_DOMAIN)) ;

        if ($format == WPGFORM_EMAIL_FORMAT_HTML)
        {
            $headers  = 'MIME-Version: 1.0' . PHP_EOL ;
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . PHP_EOL ;
        }
        else
        {
            $headers = '' ;
        }

        $headers .= sprintf("From: %s <%s>",
            get_bloginfo('name'), $sendto) . PHP_EOL ;

        $headers .= sprintf("Cc: %s", $sendto) . PHP_EOL ;

        //  Bcc Blog Admin?
        if ($wpgform_options['bcc_blog_admin'])
            $headers .= sprintf("Bcc: %s", get_bloginfo('admin_email')) . PHP_EOL ;

        $headers .= sprintf("Reply-To: %s", $sendto) . PHP_EOL ;
        $headers .= sprintf("X-Mailer: PHP/%s", phpversion()) ;

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
                __('Date', WPGFORM_I18N_DOMAIN), date('Y-m-d'),
                __('Time', WPGFORM_I18N_DOMAIN), date('H:i'),
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
                $results, date('Y-m-d'), date('H:i'), get_option('blogname')) ;
        }

        $to = sprintf('%s wpGForm Contact <%s>', get_option('blogname'), $sendto) ;
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
 */
function wpgform_head()
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
    wp_register_script('jquery-validate',
        'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.js',
        array('jquery'), false, true) ;
    wp_enqueue_script('jquery-validate') ;

    //  Load the jQuery Columnizer script from the plugin
    wp_register_script('jquery-columnizer',
            plugins_url(plugin_basename(dirname(__FILE__) . '/js/jquery.columnizer.js')),
        array('jquery'), false, true) ;
    wp_enqueue_script('jquery-columnizer') ;
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

    print wpGForm::$wpgform_footer_js ;
}
?>
