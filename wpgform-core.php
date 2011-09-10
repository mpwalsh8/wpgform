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
    $wpgform_options = get_option('wpgform_options');

    if ($wpgform_options['sc_posts'] == 1)
        add_shortcode('wpgform', 'wpgform_shortcode');

    if ($wpgform_options['sc_widgets'] == 1)
        add_filter('widget_text', 'do_shortcode');

    if ($wpgform_options['default_css'] == 1)
        add_action('template_redirect', 'wpgform_head');
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
        'manage_options', 'wpgform-options.php', 'wpgform_options_page');
    add_action('admin_head-'.$wpgform_options_page, 'wpgform_options_admin_head');
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
       ,'donation_message' => 1
    ) ;

    add_option('wpgform_options', $default_wpgform_options);
    //add_shortcode('wpgform', 'wpgform_shortcode');
    add_filter('widget_text', 'do_shortcode');

    //$wpgform = new wpGForm();
    //printf('<h3>%s::%s</h3>', basename(__FILE__), __LINE__) ;
    //add_shortcode('gform', array($wpgform, 'RenderGForm'));
}
    add_shortcode('gform', array('wpGForm', 'RenderGForm'));


/**
 * wpGForm class definition
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @access public
 * @see wp_remote_get()
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
        //var_dump($options) ;
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

        //  Google Legal Stuff?
        $legal = $options['legal'] !== 'off' ;

        //  WordPress converts all of the ampersand characters to their
        //  appropriate HTML entity or some variety of it.  Need to undo
        //  that so the URL can be actually be used.

        $form = str_replace(array('&#038;','&#38;','&amp;'), '&', $form) ;
        $confirm = str_replace(array('&#038;','&#38;','&amp;'), '&', $confirm) ;
        
        //  Retrieve the HTML from the URL
        $response = wp_remote_get($form, array('sslverify' => false)) ;

        if (is_wp_error($response))
            return '<div class="gform-error">Unable to retrieve Google Form.</div>' ;
        else
            $html = $response['body'] ;


        //  Need to filter the HTML retrieved from the form and strip off the stuff
        //  we don't want.  This gets rid of the HTML wrapper from the Google page.

        $allowed_tags = array(
            'a' => array('href' => array(), 'title' => array(), 'target' => array())
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
           ,'label' => array('class' => array(), 'for' => array())
           ,'input' => array('id' => array(), 'name' => array(), 'class' => array(), 'type' => array(), 'value' => array())
           ,'form' => array('id' => array(), 'class' => array(), 'action' => array(), 'method' => array(), 'target' => array(), 'onsubmit' => array())
           ,'script' => array('type' => array())
        );

        $html = wp_kses($html, $allowed_tags) ;

        //  Augment class names with some sort of a prefix?
        if (!is_null($prefix))
        {
            //printf('<h4>%s::%s</h4>', basename(__FILE__), __LINE__) ;
            $html = preg_replace('/ class="/i', " class=\"{$prefix}", $html) ;
        }

        //  Augment labels with some sort of a suffix?
        if (!is_null($suffix))
        {
            //printf('<h4>%s::%s</h4>', basename(__FILE__), __LINE__) ;
            $html = preg_replace('/<\/label>/i', "{$suffix}</label>", $html) ;
        }

        //  Hide Google Legal Stuff?

        if (!$legal)
        {
            //printf('<h4>%s::%s</h4>', basename(__FILE__), __LINE__) ;
            $html = preg_replace('/<div class="ss-legal"/i', '<div class="ss-legal" style="display:none;"', $html) ;
        }

        //  By default Google will display it's own confirmation page which can't
        //  be styled and is not contained within the web site.  The plugin can
        //  optionally add some Javascript to redirect to a different URL upon
        //  submission of the form.
 
        //  Redirect to a custom confirmation page instead of the Google default?

        if (!is_null($confirm))
        {
            //  Need to modify the FORM tag and add some new attributes.
            $xtra_form_attrs = 'target="gform_iframe" onsubmit="submitted=true;"' ;
            $html = preg_replace("/<form/i", "<form {$xtra_form_attrs}", $html) ;
            //$form = str_replace(array('&#038;','&#38;','&amp;'), '&', $form) ;

            //  Need some extra HTML which must be inserted before the extract FORM HTML.
            $xtra_html = '<script type="text/javascript">var submitted=false;</script>' ;
            $xtra_html .= '<iframe name="gform_iframe" id="gform_iframe" style="display:none;" onload="if(submitted){window.location=\'' . $confirm . '\';}"></iframe>' ;
        }
        else
            $xtra_html = '' ;

        //  Output custom CSS?
 
        $wpgform_options = get_option('wpgform_options');

        if ($wpgform_options['custom_css'] == 1)
        {
            //printf('<h4>%s::%s</h4>', basename(__FILE__), __LINE__) ;
            //var_dump($wpgform_options['custom_css_styles']) ;
            //$css = safecss_filter_attr('<style>' . $wpgform_options['custom_css_styles'] . '</style>') ;
            $css = '<style>' . $wpgform_options['custom_css_styles'] . '</style>' ;
            //var_dump($css) ;
        }
        else
            $css = '' ;

        return $css . $xtra_html . $html ;
    }

    /**
     * WordPress Shortcode handler.
     *
     * @return HTML
     */
    function RenderGForm($atts) {
        $params = shortcode_atts(array(
            'form'     => false,                // Google Form URL
            'confirm'  => false,                // Optional URL to redirect to instead of Google confirmation
            'class'    => 'gform',              // Container element's custom class value
            'legal'    => 'on',                 // Display Google Legal Stuff
            'suffix'   => null,                 // Add suffix character(s) to all labels
            'prefix'   => null                  // Add suffix character(s) to all labels
        ), $atts);

        return wpGForm::ConstructGForm($params);
    }
}

function wpgform_head()
{
    wp_enqueue_style('gform',
        plugins_url(plugin_basename(dirname(__FILE__) . '/gforms.css'))) ;
}


?>
