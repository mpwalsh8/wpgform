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
define('WPGFORM_DEBUG', true) ;

function wpgform_debug()
{
    global $wp_filter ;

    if (!is_admin())
    {
        //error_log(sprintf('%s::%s', basename(__FILE__), __LINE__)) ;
        wpgform_whereami(__FILE__, __LINE__) ;
        wpgform_preprint_r($_SERVER) ;
        wpgform_whereami(__FILE__, __LINE__) ;
        wpgform_preprint_r($_ENV) ;
        wpgform_whereami(__FILE__, __LINE__) ;
        wpgform_preprint_r($_POST) ;
        wpgform_whereami(__FILE__, __LINE__) ;
        wpgform_preprint_r($_GET) ;
        return;
        if (array_key_exists('init', $wp_filter))
        {
            wpgform_whereami(__FILE__, __LINE__) ;
            wpgform_preprint_r($wp_filter['init']) ;
        }
        if (array_key_exists('template_redirect', $wp_filter))
        {
            wpgform_whereami(__FILE__, __LINE__) ;
            wpgform_preprint_r($wp_filter['template_redirect']) ;
        }
    }
}
if (WPGFORM_DEBUG)
    add_action('init', 'wpgform_debug', 0) ;

if (WPST_DEBUG) :
/**
 * wpgform_send_headers()
 *
 * @return null
 */
function wpgform_send_headers()
{
    header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
    header('Expires: ' . date(DATE_RFC822, strtotime('yesterday'))); // Date in the past
}
endif ;

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
        add_shortcode('wpgform', 'wpgform_shortcode') ;

    if ($wpgform_options['sc_widgets'] == 1)
        add_filter('widget_text', 'do_shortcode') ;

    if (WPST_DEBUG)
        add_action('send_headers', 'wpgform_send_headers') ;

    add_action('template_redirect', 'wpgform_head') ;
    add_filter('the_content', 'wpautop');
}

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

    foreach ($default_options as $key => $value)
    {
        if (!array_key_exists($key, $plugin_options))
            $plugin_options[$key] = null ;
    }

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
    $default_wpgform_options = array(
        'sc_posts' => 1
       ,'sc_widgets' => 1
       ,'default_css' => 1
       ,'custom_css' => 0
       ,'custom_css_styles' => ''
       ,'donation_message' => 0
       ,'email_format' => WPGFORM_EMAIL_FORMAT_PLAIN
       ,'browser_check' => 0
    ) ;

    add_option('wpgform_options', $default_wpgform_options) ;
    //add_shortcode('wpgform', 'wpgform_shortcode') ;
    add_filter('widget_text', 'do_shortcode') ;
}

add_shortcode('gform', array('wpGForm', 'RenderGForm')) ;


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
     * Constructor
     */
    function wpGForm()
    {
        // empty for now
    }

    /**
     * Function ConstructGForm grabs CSV data from a URL and returns an HTML table.
     *
     * @param $options array Values passed from the shortcode.
     * @return An HTML string if successful, false otherwise.
     * @see RenderGForm
     */
    function ConstructGForm($options)
    {
        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__) ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;
        //  If no URL then return as nothing useful can be done.
        if (!$options['form'])
        {
            return false; 
        }
        else
        {
            $form = $options['form'] ;
        }

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

        //  Breaks between labels and inputs?
        $br = $options['br'] === 'on' ;

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


        //  Show the custom confirmation via AJAX instead of redirect?
        $style = $options['style'] ;

        //  WordPress converts all of the ampersand characters to their
        //  appropriate HTML entity or some variety of it.  Need to undo
        //  that so the URL can be actually be used.

        $form = str_replace(array('&#038;','&#38;','&amp;'), '&', $form) ;
        if (!is_null($confirm))
            $confirm = str_replace(array('&#038;','&#38;','&amp;'), '&', $confirm) ;
        
        //  If we arrive here as a result of a POST then the Google Form was
        //  submitted (either completely or partially) so we need to act on
        //  the posted data appropriately.  The user "submitting" the form
        //  doesn't actually do anything - it just tells us that the form was
        //  submitted and now the plugin needs to "really" submit it to Google,
        //  get the response, and display it as part of the WordPress content.

        if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__) ;
        if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;
        if (!empty($_POST))
        {
            $posted = true ;
            if (WPGFORM_DEBUG) wpgform_whereami(__FILE__, __LINE__) ;
            if (WPGFORM_DEBUG) wpgform_preprint_r($_POST) ;
            $action = $_POST['gform-action'] ;
            unset($_POST['gform-action']) ;

            $body = '' ;

            //  The name of the form fields are munged, they need
            //  to be restored before the parameters can be posted

            $patterns = array('/^entry_([0-9]+)_(single|group)_/', '/^entry_([0-9]+)_/') ;
            $replacements = array('entry.\1.\2.', 'entry.\1.') ;

            foreach ($_POST as $key => $value)
            {
                //  Need to handle parameters passed as array values
                //  separately because of how Python (used Google)
                //  handles array arguments differently than PHP does.

                if (is_array($_POST[$key]))
                {
                    $pa = &$_POST[$key] ;
                    foreach ($pa as $pv)
                        $body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($pv) . '&' ;
                }
                else
                {
                    $body .= preg_replace($patterns, $replacements, $key) . '=' . rawurlencode($value) . '&' ;
                }
            }
            //  Remove the action from the form and POST it

            //$form = str_replace($action, 'action="' . get_permalink(get_the_ID()) . '"', $form) ;
            $form = str_replace($action, 'action=""', $form) ;

            $response = wp_remote_post($action,
                array('sslverify' => false, 'body' => $body)) ;
        }
        else
        {
            $posted = false ;
            $response = wp_remote_get($form, array('sslverify' => false)) ;
        }

        //  Retrieve the HTML from the URL

        if (is_wp_error($response))
            return '<div class="gform-google-error">Unable to retrieve Google Form.  Please try reloading this page.</div>' ;
        else
            $html = $response['body'] ;

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

        //  Augment class names with some sort of a prefix?

        if (!is_null($prefix))
            $html = preg_replace('/ class="/i', " class=\"{$prefix}", $html) ;

        //  Augment labels with some sort of a suffix?

        if (!is_null($suffix))
            $html = preg_replace('/<\/label>/i', "{$suffix}</label>", $html) ;

        //  Insert breaks between labels and input fields?

        if ($br)
            $html = preg_replace('/<\/label>[\w\n]*<input/i', '</label><br/><input', $html) ;

        //  Hide Google Legal Stuff?

        if (!(bool)$legal)
        {
            $html = preg_replace(sprintf('/<div class="%sss-legal"/i', $prefix),
                sprintf('<div class="%sss-legal" style="display:none;"', $prefix), $html) ;
        }

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
            $action = preg_replace('/^action/i', 'value', $action) ;

            $html = preg_replace('/<\/form>/i',
                "<input type=\"hidden\" {$action} name=\"gform-action\"></form>", $html) ;
        } 
        else 
        {
            $action = null ;
        }
        
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
jQuery(document).ready(function($) {
    $("div.%sss-form-container input:checkbox").each(function(index) {
        this.name = this.name + \'[]\';
    });
', $prefix) ;
        //  Before closing the <script> tag, is the form read only?
        if ($readonly) $js .= sprintf('
    $("div.%sss-form-container :input").attr("disabled", true);
        ', $prefix) ;

        //  Before closing the <script> tag, is this the confirmation
        //  AND do we have a custom confirmation page or alert message?

        if ($posted && is_null($action) && !is_null($alert))
            $js .= PHP_EOL . 'alert("' . $alert . '") ;' ;

        //  Load the confirmation URL via AJAX?
        if ($posted && is_null($action) && !is_null($confirm) && $style === WPGFORM_CONFIRM_AJAX)
            $js .= PHP_EOL . '$("body").load("' . $confirm . '") ;' ;

        //  Load the confirmation URL via Redirect?
        if ($posted && is_null($action) && !is_null($confirm) && $style === WPGFORM_CONFIRM_REDIRECT)
            //printf('<h2>%s::%s</h2>', basename(__FILE__), __LINE__) ;
            $js .= PHP_EOL . 'window.location.replace("' . $confirm . '") ;' ;

        $js .= '
});
</script>
        ' ;

        //  Tidy up Javascript to ensure it isn't affected by 'the_content' filters
        //$js = preg_replace('/[\r\n]+/', '', $js) . PHP_EOL ;
        $js = preg_replace($patterns, $replacements, $js) . PHP_EOL ;

        //  Send email?
        if ($posted && is_null($action) && $email)
        {
            wpGForm::SendConfirmationEmail($wpgform_options['email_format'], $sendto) ;
        }

        //  Check browser compatibility?  The jQuery used by this plugin may
        //  not work correctly on old browsers or IE running in compatibility mode.

        if ($wpgform_options['browser_check'] == 1)
        {
            require_once(ABSPATH . '/wp-admin/includes/dashboard.php') ;
 
            //  Let's check the browser version just in case ...

       	    $response = wp_check_browser_version();

            if ($response && $response['upgrade'])
            {
		        if ($response['insecure'])
                    $css .= '<div class="gform-browser-warning"><h4>' .
                        __('Warning:  You are using an insecure browser!') . '</h4></div>' ;
		        else
                    $css .= '<div class="gform-browser-warning"><h4>' .
                        __('Warning:  Your browser is out of date!  Please update now.') . '</h4></div>' ;
	        }
        }

        return $js . $css . $html ;
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

        if ($_SERVER["HTTPS"] == "on") $pageURL .= 's' ;

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
            'form'      => false,                   // Google Form URL
            'confirm'   => false,                   // Custom confirmation page URL to redirect to
            'alert'     => null,                    // Optional Alert Message
            'class'     => 'gform',                 // Container element's custom class value
            'legal'     => 'on',                    // Display Google Legal Stuff
            'br'        => 'off',                   // Insert <br> tags between labels and inputs
            'suffix'    => null,                    // Add suffix character(s) to all labels
            'prefix'    => null,                    // Add suffix character(s) to all labels
            'readonly'  => 'off',                   // Set all form elements to disabled
            'title'     => 'on',                    // Remove the H1 element(s) from the Form
            'maph1h2'   => 'off',                   // Map H1 element(s) on the form to H2 element(s)
            'email'     => 'off',                   // Send an email confirmation to blog admin on submission
            'sendto'    => null,                    // Send an email confirmation to a specific address on submission
            'style'     => WPGFORM_CONFIRM_REDIRECT // How to present the custom confirmation after submit
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
    function SendConfirmationEmail($mode = WPGFORM_EMAIL_FORMAT_HTML, $sendto = false)
    {
        if ($sendto === false || $sendto === null) $sendto = get_bloginfo('admin_email') ;

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
        $headers .= sprintf("Bcc: %s", get_bloginfo('admin_email')) . PHP_EOL ;
        $headers .= sprintf("Reply-To: %s", $sendto) . PHP_EOL ;
        $headers .= sprintf("X-Mailer: PHP/%s", phpversion()) ;

        if ($mode == WPGFORM_EMAIL_FORMAT_HTML)
        {
            $html = '
                <html>
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
                <li>URL:  %s</li>
                <li>Date: %s</li>
                <li>Time: %s</li>
                </ul>
                </p>
                <p>
                Thank you,<br/><br/>
                %s
                </p>
                <p>
                </p>
                </body>
                </html>
                ' ;

            $message = sprintf($html, get_bloginfo('name'),
                wpGForm::GetPageURL(), date('Y-m-d'), date('H:i'), get_bloginfo('name')) ;
        }
        else
        {
            $plain = 'FYI -' . PHP_EOL . PHP_EOL ;
            $plain .= 'A form was submitted on your web site:' . PHP_EOL . PHP_EOL ;
            $plain .= 'URL:  %s' . PHP_EOL . 'Date:  %s' . PHP_EOL . 'Time:  %s' . PHP_EOL . PHP_EOL ;
            $plain .= 'Thank you,' . PHP_EOL . PHP_EOL . '%s' . PHP_EOL ;

            $message = sprintf($plain, wpGForm::GetPageURL(),
                date('Y-m-d'), date('H:i'), get_option('blogname')) ;
        }

        $to = sprintf('%s wpGForm Contact <%s>, %s Admin<%s>',
            get_option('blogname'), $sendto,
            get_option('blogname'), get_option('admin_email')) ;

        $subject = sprintf('Form Submission from %s', get_option('blogname')) ;

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
}

function wpgform_whereami($f, $l)
{
    printf('<h2>%s::%s</h2>', basename($f), $l) ;
}

/**
 * Debug functions
 */
function wpgform_preprint_r()
{
    $numargs = func_num_args() ;
    $arg_list = func_get_args() ;
    for ($i = 0; $i < $numargs; $i++) {
	printf('<pre style="text-align:left;">%s</pre>', print_r($arg_list[$i], true)) ;
    }
}

?>
