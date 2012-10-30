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
define('WPGFORM_PATH', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__))) ;
define('WPGFORM_EMAIL_FORMAT_HTML', 'html') ;
define('WPGFORM_EMAIL_FORMAT_PLAIN', 'plain') ;
define('WPGFORM_CONFIRM_AJAX', 'ajax') ;
define('WPGFORM_CONFIRM_LIGHTBOX', 'lightbox') ;
define('WPGFORM_CONFIRM_REDIRECT', 'redirect') ;

//  Need the plugin options to initialize debug
$wpgform_options = wpgform_get_plugin_options() ;

//  Enable debug content?
define('WPGFORM_DEBUG', $wpgform_options['enable_debug'] == 1) ;

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
        add_shortcode('gform', array('wpGForm', 'RenderGForm')) ;

    if ($wpgform_options['sc_widgets'] == 1)
        add_filter('widget_text', 'do_shortcode') ;

    add_filter('the_content', 'wpautop');
    add_action('template_redirect', 'wpgform_head') ;
    add_action('wp_footer', 'wpgform_footer') ;
}

add_action('init', array('wpGForm', 'ProcessGForm')) ;

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
    require_once(WPGFORM_PATH . '/wpgform-options.php') ;

    $wpgform_options_page = add_options_page('WP Google Form', 'WP Google Form ',
        'manage_options', 'wpgform-options.php', 'wpgform_options_page') ;
    add_action('admin_footer-'.$wpgform_options_page, 'wpgform_options_admin_footer') ;
    add_action('admin_print_scripts-'.$wpgform_options_page, 'wpgform_options_print_scripts') ;
    add_action('admin_print_styles-'.$wpgform_options_page, 'wpgform_options_print_styles') ;
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
 * @see RenderGForm()
 * @see ConstructGForm()
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
     * Constructor
     */
    function wpGForm()
    {
        // empty for now
    }

    /**
     * Function ConstructGForm loads HTML from a Google Form URL,
     * processes it, and inserts it into a WordPress filter to output
     * as part of a post, page, or widget.
     *
     * @param $options array Values passed from the shortcode.
     * @return An HTML string if successful, false otherwise.
     * @see RenderGForm
     */
    function ConstructGForm($options)
    {
        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGForm') ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;

        //  Some servers running ModSecurity issue 403 errors because something
        //  in the form's POST parameters has triggered a positive match on a rule.

        if (!empty($_SERVER) && array_key_exists('REDIRECT_STATUS', $_SERVER) && ($_SERVER['REDIRECT_STATUS'] == '403'))
            return '<div class="gform-google-error">Unable to process Google Form.  Server is responding with <span class="gform-google-error">403 Permission Denied</span> error.</div>' ;

        //  If no URL then return as nothing useful can be done.
        if (!$options['form'])
        {
            return false; 
        }
        else
        {
            $form = $options['form'] ;
        }

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGForm') ;

        //  Custom Alert Message?  Optional
        if (!$options['alert'])
        {
            $alert = null ;
        }
        else
        {
            $alert = $options['alert'] ;
        }

        //  Custom Confirmation URL?  Optional
        if (!$options['confirm'])
        {
            $confirm = null ;
        }
        else
        {
            $confirm = $options['confirm'] ;
        }

        //  Custom Class?  Optional
        if (!$options['class'])
        {
            $class = null ;
        }
        else
        {
            $class = $options['class'] ;
        }

        //  Class Prefix?  Optional
        if (!$options['prefix'])
        {
            $prefix = null ;
        }
        else
        {
            $prefix = $options['prefix'] ;
        }

        //  Label Suffix?  Optional
        if (!$options['suffix'])
        {
            $suffix = null ;
        }
        else
        {
            $suffix = $options['suffix'] ;
        }

        //  Spreadsheet URL?  Optional
        if (!$options['spreadsheet'])
        {
            $spreadsheet = null ;
        }
        else
        {
            $spreadsheet = $options['spreadsheet'] ;
        }

        //  Breaks between labels and inputs?
        $br = $options['br'] === 'on' ;

        //  Use jQuery validation?
        $validation = $options['validation'] === 'on' ;

        //  Display CAPTCHA?
        $captcha = $options['captcha'] === 'on' ;
        $captcha_html = '' ;

        if ($captcha)
        {
            $a = rand(0, 19) ;
            $b = rand(5, 24) ;
            $c = $a + $b ;

            self::$wpgform_captcha = array('a' => $a, 'b' => $b, 'c' => $c) ;

            $captcha_html .= '<div style="margin-top: 5px; display: none;" class="gform-captcha">' ;
            $captcha_html .= sprintf('<div class="%sss-item %sss-item-required %sss-text">', $prefix, $prefix, $prefix) ;
            $captcha_html .= sprintf('<div class="%sss-form-entry">', $prefix) ;
            $captcha_html .= sprintf('<label for="gform-captcha" class="%sss-q-title">What is %s + %s ?', $prefix, $a, $b) ;
            $captcha_html .= sprintf('<span class="%sss-required-asterisk">*</span></label>', $prefix) ;
            $captcha_html .= sprintf('<label for="gform-captcha" class="%sss-q-help"></label>', $prefix) ;
            $captcha_html .= sprintf('<input style="width: 100px;" type="text" id="gform-captcha" class="%sss-q-short" value="" name="gform-captcha">', $prefix) ;
            $captcha_html .= '</div></div></div>' ;
        }

        //  Output the H1 title included in the Google Form?
        $title = $options['title'] === 'on' ;

        //  Map H1 tags to H2 tags?  Apparently helps SEO ...
        $maph1h2 = $options['maph1h2'] === 'on' ;

        //  Google Legal Stuff?
        $legal = $options['legal'] !== 'off' ;

        //  Should form be set to readonly?
        $readonly = $options['readonly'] === 'on' ;

        //  Should email confirmation be sent?
        $email = $options['email'] === 'on' ;

        //  Who should email confirmation be sent to?
        if (!$options['sendto'])
        {
            $sendto = null ;
        }
        else
        {
            $sendto = is_email($options['sendto']) ;
        }

        //  The Unite theme from Paralleus mucks with the submit buttons
        //  which breaks the ability to submit the form to Google correctly.
        //  This hack will "unbreak" the submit buttons.

        $unitethemehack = $options['unitethemehack'] === 'on' ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGForm') ;

        //  Show the custom confirmation via AJAX instead of redirect?
        $style = $options['style'] ;

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
            self::$response = wp_remote_get($form, array('sslverify' => false)) ;
        }

        //  Retrieve the HTML from the URL

        if (is_wp_error(self::$response))
        {
            printf('<h2>%s::%s</h2>', basename(__FILE__), __LINE__) ;
            print '<pre>' ;
            print_r(self::$response) ;
            print '</pre>' ;
            $error_string = self::$response->get_error_message();
            echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
            if (WPGFORM_DEBUG)
            {
                //wpgform_whereami(__FILE__, __LINE__, 'ConstructGForm') ;
                //wpgform_preprint_r(self::$respone) ;
                
            }

            return '<div class="gform-google-error">Unable to retrieve Google Form.  Please try reloading this page.</div>' ;
        }
        else
            $html = self::$response['body'] ;

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGForm') ;

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
            return '<div class="gform-google-error">Unexpected content encountered, unable to retrieve Google Form.</div>' ;
        }

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGForm') ;

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

        //  Insert breaks between labels and input fields?

        //if ($br)
            //$html = preg_replace('/<\/label>[\w\n]*<input/i', '</label><br/><input', $html) ;

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
                "<input type=\"hidden\" value=\"{$action}\" name=\"gform-action\"><input type=\"hidden\" value=\"{$wgformid}\" name=\"gform-form-id\"></form>", $html) ;
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

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ConstructGForm') ;

        //  Encode all of the short code options so they can
        //  be referenced if/when needed during form processing.

        $html = preg_replace('/<\/form>/i', "<input type=\"hidden\" value=\"" .
            base64_encode(serialize($options)) . "\" name=\"gform-options\"></form>", $html) ;
            //base64_encode($options) . "\" name=\"gform-options\"></form>", $html) ;

        //  Output custom CSS?
 
        $wpgform_options = wpgform_get_plugin_options() ;

        if (($wpgform_options['custom_css'] == 1) && !empty($wpgform_options['custom_css_styles']))
            $css = '<style>' . $wpgform_options['custom_css_styles'] . '</style>' ;
        else
            $css = '' ;

        //  Tidy up Javascript to ensure it isn't affected by 'the_content' filters
        $patterns = array('/[\r\n]+/', '/ +/') ;
        $replacements = array('', ' ') ;
        $css = preg_replace($patterns, $replacements, $css) . PHP_EOL ;
        //$css = preg_replace('/[\r\n]+/', '', $css) . PHP_EOL ;


        //  Output Javscript for form validation, make sure any class prefix is included
        //  Need to fix the name arguments for checkboxes so PHP will pass them as an array correctly.
        //  This jQuery script reformats the checkboxes so that Googles Python script will read them.

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

        //  Is CAPTCHA enabled?
        if ($captcha) $js .= sprintf('
    //  Construct CAPTCHA
    $.validator.methods.equal = function(value, element, param) { return value == param; };
    $("#ss-form").append(\'%s\');
    if ($("#ss-form input[type=submit][name=submit]").length) {
        $("div.gform-captcha").show();
        $.validator.addClassRules("gform-captcha", {
            required: true
        });
        $("#ss-form").validate({
            errorClass: "gform-error",
			rules: {
				"gform-captcha": {
					equal: %s
				}
			},
			messages: {
				"gform-captcha": "Incorrect answer."
			}
		});
    }
', $captcha_html, self::$wpgform_captcha['c']) ;

        //  Include jQuery validation?
        if ($validation) $js .= sprintf('
    //  jQuery inline validation
    $("div > .ss-item-required textarea").addClass("gform-required");
    $("div > .ss-item-required input:not(.ss-q-other").addClass("gform-required");
    $("div > .%sss-item-required textarea").addClass("gform-required");
    $("div > .%sss-item-required input:not(.%sss-q-other").addClass("gform-required");
    $.validator.addClassRules("gform-required", { required: true });
    $("#ss-form").validate({ errorClass: "gform-error" }) ;
', $prefix, $prefix, $prefix) ;

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
            $style === WPGFORM_CONFIRM_AJAX)
        {
            $js .= PHP_EOL . '$("body").load("' . $confirm . '") ;' ;
        }

        //  Load the confirmation URL via Redirect?
        if (self::$posted && is_null($action) && !is_null($confirm) &&
            (self::$wpgform_submitted_form_id == self::$wpgform_form_id - 1) &&
            $style === WPGFORM_CONFIRM_REDIRECT)
        {
            //printf('<h2>%s::%s</h2>', basename(__FILE__), __LINE__) ;
            $js .= PHP_EOL . 'window.location.replace("' . $confirm . '") ;' ;
        }

        $js .= '
});
</script>
        ' ;

        //  Tidy up Javascript to ensure it isn't affected by 'the_content' filters
        //$js = preg_replace($patterns, $replacements, $js) . PHP_EOL ;

        //  Send email?
        if (self::$posted && is_null($action) && $email)
        {
            wpGForm::SendConfirmationEmail($wpgform_options['email_format'], $sendto, $spreadsheet) ;
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
                    $css .= '<div class="gform-browser-warning"><h4>' .
                        __('Warning:  You are using an insecure browser!') . '</h4></div>' ;
		        else
                    $css .= '<div class="gform-browser-warning"><h4>' .
                        __('Warning:  Your browser is out of date!  Please update now.') . '</h4></div>' ;
	        }
        }

        if (WPGFORM_DEBUG)
            $debug = '<h2 class="gform-debug"><a href="#" class="gform-debug-wrapper">Show wpGForm Debug Content</a></h2>' ;
        else
            $debug = '' ;

        //  Assemble final HTML to return.  To handle pages with more than one
        //  form, Javascript, CSS, and debug control should only be rendered once!
 
        $onetime_html = '' ;

        if (WPGFORM_DEBUG)
        {
            printf('<h2>Form Id:  %s</h2>', self::$wpgform_form_id - 1) ;
            if (!is_null(self::$wpgform_submitted_form_id))
                printf('<h2>Submitted Form Id:  %s</h2>', self::$wpgform_submitted_form_id) ;
            else
                printf('<h2>No Submitted Form Id:</h2>') ;
        }

        if (!self::$wpgform_js)
        {
            if (is_null(self::$wpgform_submitted_form_id) ||
                self::$wpgform_submitted_form_id == self::$wpgform_form_id - 1)
            {
                //$onetime_html .= $js ;
                self::$wpgform_js = true ;
                self::$wpgform_footer_js = $js ;
            }
        }

        if (!self::$wpgform_css)
        {
            $onetime_html .= $css ;
            self::$wpgform_css = true ;
        }

        if (!self::$wpgform_debug)
        {
            $onetime_html .= $debug ;
            self::$wpgform_debug = true ;
        }

        return $onetime_html . $html ;
    }

    /**
     * Function ConstructGForm loads HTML from a Google Form URL,
     * processes it, and inserts it into a WordPress filter to output
     * as part of a post, page, or widget.
     *
     * @param $options array Values passed from the shortcode.
     * @return An HTML string if successful, false otherwise.
     * @see RenderGForm
     */
    function ProcessGForm()
    {
        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGForm') ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;
        if (!empty($_POST) && array_key_exists('gform-action', $_POST))
        {
            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGForm') ;

            self::$posted = true ;

            $wpgform_options = wpgform_get_plugin_options() ;

            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGForm') ;
            if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;
            
            //  Need the form ID to handle multiple forms per page
            self::$wpgform_submitted_form_id = $_POST['gform-form-id'] ;
            unset($_POST['gform-form-id']) ;

            //  Need the action which was saved during form construction
            $action = unserialize(base64_decode($_POST['gform-action'])) ;
            unset($_POST['gform-action']) ;
            $options = $_POST['gform-options'] ;
            unset($_POST['gform-options']) ;
            $options = unserialize(base64_decode($options)) ;

            if (WPGFORM_DEBUG) wpgform_preprint_r($options) ;
            $form = $options['form'] ;

            $body = '' ;

            //  The name of the form fields are munged, they need
            //  to be restored before the parameters can be posted

            $patterns = array('/^entry_([0-9]+)_(single|group)_/', '/^entry_([0-9]+)_/') ;
            $replacements = array('entry.\1.\2.', 'entry.\1.') ;

            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGForm') ;
            if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;

            foreach ($_POST as $key => $value)
            {
                if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGForm') ;
                if (WPGFORM_DEBUG) wpgform_preprint_r($key, $value) ;

                //  Need to handle parameters passed as array values
                //  separately because of how Python (used Google)
                //  handles array arguments differently than PHP does.

                if (is_array($_POST[$key]))
                {
                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGForm') ;
                    $pa = &$_POST[$key] ;
                    foreach ($pa as $pv)
                        $body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($pv) . '&' ;
                }
                else
                {
                    if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGForm') ;
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
                array('sslverify' => false, 'body' => $body)) ;

            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__, 'ProcessGForm') ;
            if (WPGFORM_DEBUG) wpgform_preprint_r(self::$response) ;
        }
        else
        {
            sprintf('%s::%s', basename(__FILE__), __LINE__) ;
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
    function RenderGForm($atts) {
        $params = shortcode_atts(array(
            'form'           => false,                   // Google Form URL
            'confirm'        => false,                   // Custom confirmation page URL to redirect to
            'alert'          => null,                    // Optional Alert Message
            'class'          => 'gform',                 // Container element's custom class value
            'legal'          => 'on',                    // Display Google Legal Stuff
            'br'             => 'off',                   // Insert <br> tags between labels and inputs
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

        return wpGForm::ConstructGForm($params) ;
    }

    /**
     * Send Confirmation E-mail
     *
     * Send an e-mail to the blog administrator informing
     * them of a form submission.
     * 
     * @param string $action - action to take, register or unregister
     */
    function SendConfirmationEmail($mode = WPGFORM_EMAIL_FORMAT_HTML, $sendto = false, $spreadsheet = null)
    {
        $wpgform_options = wpgform_get_plugin_options() ;

        if ($sendto === false || $sendto === null) $sendto = get_bloginfo('admin_email') ;

        if ($spreadsheet === false || $spreadsheet === null)
            $spreadsheet = 'N/A' ;
        else
            $spreadsheet = sprintf('<a href="%s">View Form Submissions</a>', $spreadsheet) ;

        if ($mode == WPGFORM_EMAIL_FORMAT_HTML)
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

        if ($mode == WPGFORM_EMAIL_FORMAT_HTML)
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
                A form was submitted on your web site.
                <ul>
                <li>Form:  %s</li>
                <li>Responses:  %s</li>
                <li>Date: %s</li>
                <li>Time: %s</li>
                </ul>
                </p>
                <p>
                Thank you,<br/><br/>
                %s
                </p>
                </body>
                </html>' ;

            $message = sprintf($html, get_bloginfo('name'), get_the_title(),
                $spreadsheet, date('Y-m-d'), date('H:i'), get_bloginfo('name')) ;
        }
        else
        {
            $plain = 'FYI -' . PHP_EOL . PHP_EOL ;
            $plain .= 'A form was submitted on your web site:' . PHP_EOL . PHP_EOL ;
            $plain .= 'Form:  %s' . PHP_EOL . 'Responses:  %s' . PHP_EOL . 'Date:  %s' . PHP_EOL ;
            $plain .= 'Time:  %s' . PHP_EOL . PHP_EOL . 'Thank you,' . PHP_EOL . PHP_EOL . '%s' . PHP_EOL ;

            $message = sprintf($plain, get_the_title(),
                $spreadsheet, date('Y-m-d'), date('H:i'), get_option('blogname')) ;
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
        wp_enqueue_style('gform',
            plugins_url(plugin_basename(dirname(__FILE__) . '/gforms.css'))) ;
    }

    //  Load the jQuery Validate from the Microsoft CDN, it isn't
    //  available from the Google CDN or I'd load it from there!
    wp_register_script('jquery-validate',
        'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.10.0/jquery.validate.js',
        array('jquery'), false, true) ;
    wp_enqueue_script('jquery-validate') ;
}

/**
 * wpgform_head()
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
