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

    add_action('template_redirect', 'wpgform_head') ;

    //add_action('wp_footer', 'wpgform_footer') ;
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

    //  Merge the default with any options currently saves by WordPress
    $plugin_options = wp_parse_args(get_option('wpgform_options', array()), $default_options) ;

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
        //  If no URL then return as nothing useful can be done.
        if (!$options['form'])
        {
            return false; 
        }
        else
        {
            $form = $options['form'] ;
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

        if (!empty($_POST))
        {
            $posted = true ;
            //echo '<h1>Posted!</h1>' ;
            //var_dump($_POST) ;
            //var_dump($form) ;
            $action = $_POST['gform-action'] ;
            //var_dump($action) ;
            unset($_POST['gform-action']) ;

            $params = array() ;

            //  The name of the form fields are munged, they need
            //  to be restored before the parameters can be posted

            foreach ($_POST as $key => $value)
            {
                $params[str_replace('_', '.', $key)] = $value ;
            }

            //  Remove the action from the form and POST it

            $form = str_replace($action, 'action=""', $form) ;

            $response = wp_remote_post($action, array('sslverify' => false, 'body' => $params)) ;
            //var_dump($response) ;

            /*
            if( is_wp_error( $response ) ) {
                echo 'Something went wrong!';
            } else {
                echo 'Response:<pre>';
                //print_r( $response );
                echo '</pre>';
            }
             */
        }
        else
        {
            $posted = false ;
            //echo '<h1>Not Posted!</h1>' ;
            $response = wp_remote_get($form, array('sslverify' => false)) ;
        }

        //  Retrieve the HTML from the URL

        if (is_wp_error($response))
            return '<div class="gform-error">Unable to retrieve Google Form.  Please try reloading this page.</div>' ;
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
           ,'i' => array()
           ,'label' => array('class' => array(), 'for' => array())
           ,'input' => array('id' => array(), 'name' => array(), 'class' => array(), 'type' => array(), 'value' => array())
           ,'select' => array('name' => array(), 'for' => array())
           ,'option' => array('value' => array(), 'checked' => array())
           ,'form' => array('id' => array(), 'class' => array(), 'action' => array(), 'method' => array(), 'target' => array(), 'onsubmit' => array())
           ,'script' => array('type' => array())
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
            return '<div class="gform-error">Unable to retrieve Google Form.</div>' ;

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

        if (!$legal)
            $html = preg_replace('/<div class="ss-legal"/i',
                '<div class="ss-legal" style="display:none;"', $html) ;

        //  Need to extract form action and rebuild form tag,
        //  and add hidden field which contains the original
        //  action.  This action is used to submit the form via
        //  wp_remote_post().

        if (preg_match_all('/(action(\\s*)=(\\s*)([\"\'])?(?(1)(.*?)\\2|([^\s\>]+)))/', $html, $matches)) 
        { 
            for ($i=0; $i< count($matches[0]); $i++)
            {
                //echo "<h1>matched $i: ".$matches[0][$i]."</h1>"; 
                $action = $matches[0][$i] ;
            }

            $html = str_replace($action, 'action=""', $html) ;
            $action = preg_replace('/^action/i', 'value', $action) ;

            $html = preg_replace('/<\/form>/i',
                "<input type=\"hidden\" {$action} name=\"gform-action\"></form>", $html) ;
        } 
        else 
        {
            $action = null ;
            //print "<h1>A match was not found.</h1>"; 
        }
        
        //  Output custom CSS?
 
        $wpgform_options = wpgform_get_plugin_options() ;

        if ($wpgform_options['custom_css'] == 1)
            $css = '<style>' . $wpgform_options['custom_css_styles'] . '</style>' ;
        else
            $css = '' ;

        //  Output Javscript for form validation
        $js = '
<script type="text/javascript">
jQuery(document).ready(function($) {

    /*
    $("div > .ss-item-required input").addClass("gform-required");
    $("div > .ss-item-required textarea").addClass("gform-required");
    $.validator.addClassRules("gform-required", {
        required: true
    });
    //$("#ss-form").validate({
        //errorClass: "gform-error"
    //}) ;*/' ;

        //  Before closing the <script> tag, is the form read only?
        if ($readonly) $js .= '
    $("div.ss-form-container :input").attr("disabled", true);
        ' ;

        //  Before closing the <script> tag, is this the confirmation
        //  AND do we have a custom confiormation page?
        if ($posted && is_null($action) && !is_null($confirm))
            $js .= PHP_EOL . 'window.location.replace("' . $confirm . '") ;' ;

        $js .= '
});
</script>
        ' ;

        return $js . $css . $html ;
    }

    /**
     * WordPress Shortcode handler.
     *
     * @return HTML
     */
    function RenderGForm($atts) {
        $params = shortcode_atts(array(
            'form'      => false,        // Google Form URL
            'confirm'   => false,        // Custom confirmation page URL to redirect to
            'class'     => 'gform',      // Container element's custom class value
            'legal'     => 'on',         // Display Google Legal Stuff
            'br'        => 'off',        // Insert <br> tags between labels and inputs
            'suffix'    => null,         // Add suffix character(s) to all labels
            'prefix'    => null,         // Add suffix character(s) to all labels
            'readonly'  => 'off',        // Set all form elements to disabled
            'title'     => 'on',         // Remove the H1 element(s) from the Form
            'maph1h2'   => 'off'         // Map H1 element(s) on the form to H2 element(s)
        ), $atts) ;

        return wpGForm::ConstructGForm($params) ;
    }
}

/**
 * wpgform_head()
 *
 * WordPress header actions
 */
function wpgform_head()
{
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
        'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.8.1/jquery.validate.min.js',
        array('jquery'), false, true) ;
    wp_enqueue_script('jquery-validate') ;
}

/**
 * wpgform_footer()
 *
 * WordPress footer actions
 */
function wpgform_footer()
{
    //
    //  jQuery script to initialize the form validation
    //  neccessary so bad or missing data is submitted.
    //  When required fields are blank the normal Google
    //  processing for form errors doesn't occur, this
    //  jQuery script handles it gracefully.  The fields
    //  have only rudimentary validation.
    //
?>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $("div > .ss-item-required input").addClass("gform-required");
    $("div > .ss-item-required textarea").addClass("gform-required");
    $.validator.addClassRules("gform-required", {
        required: true
    });
    $("#ss-form").validate({
        errorClass: "gform-error"
    }) ;
});
</script>
<?php
}
?>
