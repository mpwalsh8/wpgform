<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * GForm options.
 *
 * $Id$
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mike@walshcrew.com>
 * @package wpGForm
 * @subpackage options
 * @version $Revision$
 * @lastmodified $Date$
 * @lastmodifiedby $Author$
 *
 */

/**
 * wpgform_options_admin_footer()
 *
 * Hook into Admin head when showing the options page
 * so the necessary jQuery script that controls the tabs
 * is executed.
 *
 * @return null
 */
function wpgform_options_admin_footer()
{
?>
<!-- Setup jQuery Tabs -->
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#gform-tabs").tabs() ;
    }) ;
</script>
<?php
} /* function wpgform_options_admin_footer() */

/**
 * wpgform_options_print_scripts()
 *
 * Hook into Admin Print Scripts when showing the options page
 * so the necessary scripts that controls the tabs are loaded.
 *
 * @return null
 */
function wpgform_options_print_scripts()
{
    //  Need to load jQuery UI Tabs to make the page work!

    wp_enqueue_script('jquery-ui-tabs') ;
}

/**
 * wpgform_options_print_styles()
 *
 * Hook into Admin Print Styles when showing the options page
 * so the necessary style sheets that control the tabs are
 * loaded.
 *
 * @return null
 */
function wpgform_options_print_styles()
{
    //  Need the jQuery UI CSS to make the tabs look correct.
    //  Load them from Google - should not be an issue since
    //  this plugin is all about consuming Google content!

    wp_enqueue_style('xtra-jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css') ;
}

/**
 * wpgform_options_page()
 *
 * Build and render the options page.
 *
 * @return null
 */
function wpgform_options_page()
{
?>
<div class="wrap">
<?php
    if (function_exists('screen_icon')) screen_icon() ;
?>
<h2><?php _e('WordPress Google Form Plugin Settings') ; ?></h2>
<?php
    $wpgform_options = wpgform_get_plugin_options() ;
    if (!$wpgform_options['donation_message'])
    {
?>
<small>Please consider making a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC" target="_blank">PayPal donaton</a> if you find this plugin useful.</small>
<?php
    }
?>
<br /><br />
<div class="container">
    <div id="gform-tabs">
        <ul>
            <li><a href="#gform-tabs-1">Options</a></li>
            <li><a href="#gform-tabs-2">FAQs</a></li>
            <li><a href="#gform-tabs-3">Usage</a></li>
            <li><a href="#gform-tabs-4">About</a></li>
        </ul>
        <div id="gform-tabs-1">
            <form method="post" action="options.php">
                <?php settings_fields('wpgform_options') ; ?>
                <?php wpgform_settings_input() ; ?>
                <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </form>
        </div>
        <div id="gform-tabs-2">
<?php
    //  Instead of duplicating the FAQ content in the ReadMe.txt file,
    //  let's simply extract it from the WordPress plugin repository!
    //
    //  This solutution is derived from a discussion found on www.DevNetwork.net.
    //  See full discussion:  http://www.devnetwork.net/viewtopic.php?f=38&t=102670
    //

    /*
    //  Commented regex to extract contents from <div class="main">contents</div>
    //  where "contents" may contain nested <div>s.
    //  Regex uses PCRE's recursive (?1) sub expression syntax to recurs group 1

    $pattern_long = '{                    # recursive regex to capture contents of "main" DIV
    <div\s+class="main"\s*>               # match the "main" class DIV opening tag
      (                                   # capture "main" DIV contents into $1
        (?:                               # non-cap group for nesting * quantifier
          (?: (?!<div[^>]*>|</div>). )++  # possessively match all non-DIV tag chars
        |                                 # or 
          <div[^>]*>(?1)</div>            # recursively match nested <div>xyz</div>
        )*                                # loop however deep as necessary
      )                                   # end group 1 capture
    </div>                                # match the "main" class DIV closing tag
    }six';  // single-line (dot matches all), ignore case and free spacing modes ON
    */

    //  WordPress plugin repository places the content in DIV with a class of
    //  "block-content" but there are actually several DIVs that have the same class.
    //  We only want the first one.

    $url = 'http://wordpress.org/extend/plugins/wpgform/faq/' ;
    $response= wp_remote_get($url) ;

    if (is_wp_error($response))
    {
?>
<div class="updated error">Unable to retrive FAQ content from WordPress plugin repository.</div>
<?php
    }
    else
    {
?>
<?php
        $data = &$response['body'] ;
        $pattern_short = '{<div\s+[^>]*?class="block-content"[^>]*>((?:(?:(?!<div[^>]*>|</div>).)++|<div[^>]*>(?1)</div>)*)</div>}si';
        $matchcount = preg_match_all($pattern_short, $data, $matches);

        //  Did we find something?
        if ($matchcount > 0)
        {
            //  The content we want will be the first match
            echo($matches[1][0]); // print 1st capture group for match number i
        }
        else
        {
?>
<div class="updated error">Unable to retrive FAQ content from WordPress plugin repository.</div>
<?php
        }
    }
        
?>
        </div>
        <div id="gform-tabs-3">
<?php
    //  Instead of duplicating the FAQ content in the ReadMe.txt file,
    //  let's simply extract it from the WordPress plugin repository!
    //
    //  This solutution is derived from a discussion found on www.DevNetwork.net.
    //  See full discussion:  http://www.devnetwork.net/viewtopic.php?f=38&t=102670
    //

    /*
    //  Commented regex to extract contents from <div class="main">contents</div>
    //  where "contents" may contain nested <div>s.
    //  Regex uses PCRE's recursive (?1) sub expression syntax to recurs group 1

    $pattern_long = '{                    # recursive regex to capture contents of "main" DIV
    <div\s+class="main"\s*>               # match the "main" class DIV opening tag
      (                                   # capture "main" DIV contents into $1
        (?:                               # non-cap group for nesting * quantifier
          (?: (?!<div[^>]*>|</div>). )++  # possessively match all non-DIV tag chars
        |                                 # or 
          <div[^>]*>(?1)</div>            # recursively match nested <div>xyz</div>
        )*                                # loop however deep as necessary
      )                                   # end group 1 capture
    </div>                                # match the "main" class DIV closing tag
    }six';  // single-line (dot matches all), ignore case and free spacing modes ON
    */

    //  WordPress plugin repository places the content in DIV with a class of
    //  "block-content" but there are actually several DIVs that have the same class.
    //  We only want the first one.

    $url = 'http://wordpress.org/extend/plugins/wpgform/other_notes/' ;
    $response= wp_remote_get($url) ;

    if (is_wp_error($response))
    {
?>
<div class="updated error">Unable to retrive FAQ content from WordPress plugin repository.</div>
<?php
    }
    else
    {
?>
<?php
        $data = &$response['body'] ;
        $pattern_short = '{<div\s+[^>]*?class="block-content"[^>]*>((?:(?:(?!<div[^>]*>|</div>).)++|<div[^>]*>(?1)</div>)*)</div>}si';
        $matchcount = preg_match_all($pattern_short, $data, $matches);

        //  Did we find something?
        if ($matchcount > 0)
        {
            //  The content we want will be the first match
            echo($matches[1][0]); // print 1st capture group for match number i
        }
        else
        {
?>
<div class="updated error">Unable to retrive FAQ content from WordPress plugin repository.</div>
<?php
        }
    }
        
?>
        </div>
        <div id="gform-tabs-4">
            <h4>About WordPress Google Form</h4>
	    <p>An easy to implement integration of a Google Form with WordPress. This plugin allows you to leverage the power of Google Docs Spreadsheets and Forms to collect data while retaining the look and feel of your WordPress based web site.  The forms can optionally be styled to better integrate with your WordPress theme.</p>
            <p>WordPress Google Form is based on the <a href="http://codex.wordpress.org/HTTP_API"><b>WordPress HTTP API</b></a> and in particular, the <a href="http://codex.wordpress.org/Function_API/wp_remote_get"><b>wp_remote_get()</b></a> and <a href="http://codex.wordpress.org/Function_API/wp_remote_post"><b>wp_remote_post()</b></a> functions for retrieving and posting the form.  WordPress Google Form also makes use of the <a href="http://codex.wordpress.org/Function_Reference/wp_kses"><b>wp_kses()</b></a> function for processing the HTML retrieved from Google and extracting the relevant parts of the form.</p><p>If you find this plugin useful, please consider <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC" target="_blank">making small donation towards this plugin</a> to help keep it up to date.</p>
        </div>
    </div>
</div>
<?php
}


/**
 * wpgform_settings_input()
 *
 * Build the form content and populate with any current plugin settings.
 *
 * @return none
 */
function wpgform_settings_input()
{
    $wpgform_options = wpgform_get_plugin_options() ; ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label><b><i>gform</i></b> Shortcode</label></th>
            <td><fieldset>
            <label for="gform_sc_posts">
            <input name="wpgform_options[sc_posts]" type="checkbox" id="gform_sc_posts" value="1" <?php checked('1', $wpgform_options['sc_posts']) ; ?> />
            Enable shortcodes for posts and pages</label>
            <br />
            <label for="gform_sc_widgets">
            <input name="wpgform_options[sc_widgets]" type="checkbox" id="gform_sc_widgets" value="1" <?php checked('1', $wpgform_options['sc_widgets']) ; ?> />
            Enable shortcodes in text widget</label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><b><i>gform</i></b> CSS</label></th>
            <td><fieldset>
            <label for="gform_default_css">
            <input name="wpgform_options[default_css]" type="checkbox" id="gform_default_css" value="1" <?php checked('1', $wpgform_options['default_css']) ; ?> />
            Enable default WordPress Google Form CSS</label>
            <br />
            <label for="gform_custom_css">
            <input name="wpgform_options[custom_css]" type="checkbox" id="gform_custom_css" value="1" <?php checked('1', $wpgform_options['custom_css']) ; ?> />
            Enable custom WordPress Google Form CSS</label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label>Custom GForm CSS</label><br/><small><i>Optional CSS styles to control the appearance of the Google Form.</i></small></th>
            <td>
            <textarea class="regular-text code" name="wpgform_options[custom_css_styles]" rows="15" cols="80"  id="gform_custom_css_styles"><?php echo $wpgform_options['custom_css_styles']; ?></textarea>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label>Confirmation Email Format</label></th>
            <td><fieldset>
            <label for="gform_email_format">
            <input name="wpgform_options[email_format]" type="radio" id="gform_email_format" value="<?php echo WPGFORM_EMAIL_FORMAT_HTML ;?>" <?php checked(WPGFORM_EMAIL_FORMAT_HTML, $wpgform_options['email_format']) ; ?> />
            Send confirmation email (when used) in HTML format.</label>
            <br/>
            <input name="wpgform_options[email_format]" type="radio" id="gform_email_format" value="<?php echo WPGFORM_EMAIL_FORMAT_PLAIN ;?>" <?php checked(WPGFORM_EMAIL_FORMAT_PLAIN, $wpgform_options['email_format']) ; ?> />
            Send confirmation email (when used) in Plain Text format.</label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label>Browser Check</label></th>
            <td><fieldset>
            <label for="gform_browser_check">
            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
            <tr>
            <td style="padding: 5px 0px; vertical-align: top;">
            <input name="wpgform_options[browser_check]" type="checkbox" id="gform_browser_check" value="1" <?php checked('1', $wpgform_options['browser_check']) ; ?> />
            </td>
            <td style="padding: 5px;">
            Check browser compatibility?  The WordPress Google Form plugin may not work as expected with older browser versions (e.g. IE6, IE7, etc.).
            </td>
            </tr>
            </table>
            </label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label>Donation Request</label></th>
            <td><fieldset>
            <label for="gform_donation_message">
            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
            <tr>
            <td style="padding: 5px 0px; vertical-align: top;">
            <input name="wpgform_options[donation_message]" type="checkbox" id="gform_donation_message" value="1" <?php checked('1', $wpgform_options['donation_message']) ; ?> />
            </td>
            <td style="padding: 5px;">
            Hide the request for donation at the top of this page.<br/>The donation request will remain on the <b>About</b> tab.
            </td>
            </tr>
            </table>
            </label>
            </fieldset></td>
        </tr>
<!--
        <tr valign="top">
            <th scope="row"><label>Serialize Post Variables</label></th>
            <td><fieldset>
            <label for="gform_serialize_post_vars">
            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
            <tr>
            <td style="padding: 5px 0px; vertical-align: top;">
            <input name="wpgform_options[serialize_post_vars]" type="checkbox" id="gform_serialize_post_vars" value="1" <?php //checked('1', $wpgform_options['serialize_post_vars']) ; ?> />
            </td>
            <td style="padding: 5px;">
            Serialize POST data prior to form submission?  Enabling this <i><b>may</b></i> help with servers where <a href="http://www.modsecurity.org/">Apache's ModSecurity</a> module is installed.  If you're experiencing 403 errors after submitting a form, this may alleviate the problem.
            </td>
            </tr>
            </table>
            </label>
            </fieldset></td>
        </tr>
-->
        <tr valign="top">
            <th scope="row"><label>Enable Debug</label></th>
            <td><fieldset>
            <label for="gform_enable_debug">
            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
            <tr>
            <td style="padding: 5px 0px; vertical-align: top;">
            <input name="wpgform_options[enable_debug]" type="checkbox" id="gform_enable_debug" value="1" <?php checked('1', $wpgform_options['enable_debug']) ; ?> />
            </td>
            <td style="padding: 5px;">
            Enabling debug will collect data during the form rendering and processing process.  The data is added to the page footer but hidden with a link appearing above the form which can toggle the display of the debug data.  This data is useful when trying to understand why the plugin isn't operating as expected.
            </td>
            </tr>
            </table>
            </label>
            </fieldset></td>
        </tr>
    </table>
    <br /><br />
<?php
}
?>
