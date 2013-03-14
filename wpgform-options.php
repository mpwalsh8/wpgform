<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * GForm options.
 *
 * $Id$
 *
 * (c) 2011 by Mike Walsh
 *
 * @author Mike Walsh <mpwalsh8@gmail.com>
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

    wp_enqueue_style('xtra-jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/themes/base/jquery-ui.css') ;
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
<small><?php printf(__('Please consider making a <a href="%s" target="_blank">PayPal donation</a> if you find this plugin useful.', WPGFORM_I18N_DOMAIN), 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC') ; ?></small>
<?php
    }
?>
<br /><br />
<div class="container">
    <div id="gform-tabs">
        <ul>
        <li><a href="#gform-tabs-1"><?php _e('Options', WPGFORM_I18N_DOMAIN);?></a></li>
        <li><a href="#gform-tabs-2"><?php _e('FAQs', WPGFORM_I18N_DOMAIN);?></a></li>
        <li><a href="#gform-tabs-3"><?php _e('Usage', WPGFORM_I18N_DOMAIN);?></a></li>
        <li><a href="#gform-tabs-4"><?php _e('Debug', WPGFORM_I18N_DOMAIN);?></a></li>
        <li><a href="#gform-tabs-5"><?php _e('About', WPGFORM_I18N_DOMAIN);?></a></li>
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
    //
    //  Instead of duplicating the FAQ and Other Notes content in the ReadMe.txt file,
    //  let's simply extract it from the WordPress plugin repository!
    //
	//  Fetch via the content via the WordPress Plugins API which is largely undocumented.
    //
    //  @see http://dd32.id.au/projects/wordpressorg-plugin-information-api-docs/
    //
    //  We want just the 'sections' content of the ReadMe file which will yield an array
    //  which contains an element for each section of the ReadMe file.  We'll use 'faq' and
    //  'other_notes'.
    //

	require_once( ABSPATH . '/wp-admin/includes/plugin-install.php' );
	$readme = plugins_api( 'plugin_information', array('slug' => 'wpgform', 'fields' => array( 'sections' ) ) );

    if (is_wp_error($readme))
    {
?>
<div class="updated error"><?php _e('Unable to retrive FAQ content from WordPress plugin repository.', WPGFORM_I18N_DOMAIN);?></div>
<?php
    }
    else
    {
        echo $readme->sections['faq'] ;
    }
?>
        </div>
        <div id="gform-tabs-3">
<?php

    if (is_wp_error($readme))
    {
?>
<div class="updated error"><?php _e('Unable to retrive Usage content from WordPress plugin repository.', WPGFORM_I18N_DOMAIN);?></div>
<?php
    }
    else
    {
        echo $readme->sections['other_notes'] ;
    }
?>
        </div>
        <div id="gform-tabs-4">
            <form method="post" action="options.php">
                <?php settings_fields('wpgform_options') ; ?>
                <?php wpgform_settings_debug() ; ?>
                <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
            </form>
        </div>
        <div id="gform-tabs-5">
        <h4><?php _e('About WordPress Google Form', WPGFORM_I18N_DOMAIN);?></h4>
        <p><?php _e('An easy to implement integration of a Google Form with WordPress. This plugin allows you to leverage the power of Google Docs Spreadsheets and Forms to collect data while retaining the look and feel of your WordPress based web site.  The forms can optionally be styled to better integrate with your WordPress theme.', WPGFORM_I18N_DOMAIN);?></p>
        <p>WordPress Google Form is based on the <a href="http://codex.wordpress.org/HTTP_API"><b>WordPress HTTP API</b></a> and in particular, the <a href="http://codex.wordpress.org/Function_API/wp_remote_get"><b>wp_remote_get()</b></a> and <a href="http://codex.wordpress.org/Function_API/wp_remote_post"><b>wp_remote_post()</b></a> functions for retrieving and posting the form.  WordPress Google Form also makes use of the <a href="http://codex.wordpress.org/Function_Reference/wp_kses"><b>wp_kses()</b></a> function for processing the HTML retrieved from Google and extracting the relevant parts of the form.</p><p>If you find this plugin useful, please consider <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC" target="_blank">making small donation towards this plugin</a> to help keep it up to date.</p>
        <p><?php printf('WordPress Google Form is based on the <a href="%s"><b>WordPress HTTP API</b></a> and in particular, the <a href="%s"><b>wp_remote_get()</b></a> and <a href="http://codex.wordpress.org/Function_API/wp_remote_post"><b>wp_remote_post()</b></a> functions for retrieving and posting the form.  WordPress Google Form also makes use of the <a href="%s"><b>wp_kses()</b></a> function for processing the HTML retrieved from Google and extracting the relevant parts of the form.</p><p>If you find this plugin useful, please consider <a href="%s" target="_blank">making small donation towards this plugin</a> to help keep it up to date.</p>', 'http://codex.wordpress.org/HTTP_API', 'http://codex.wordpress.org/Function_API/wp_remote_get', 'http://codex.wordpress.org/Function_Reference/wp_kses', 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC');?>
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
    $wpgform_options = wpgform_get_plugin_options() ;
?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label><b><i>wpGForm</i></b> Shortcode</label></th>
            <td><fieldset>
            <label for="gform_sc_posts">
            <input name="wpgform_options[sc_posts]" type="checkbox" id="gform_sc_posts" value="1" <?php checked('1', $wpgform_options['sc_posts']) ; ?> />
            <?php _e('Enable shortcodes for posts and pages', WPGFORM_I18N_DOMAIN);?></label>
            <br />
            <label for="gform_sc_widgets">
            <input name="wpgform_options[sc_widgets]" type="checkbox" id="gform_sc_widgets" value="1" <?php checked('1', $wpgform_options['sc_widgets']) ; ?> />
            <?php _e('Enable shortcodes in text widget', WPGFORM_I18N_DOMAIN);?></label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><b><i>wpGForm</i></b> CSS</label></th>
            <td><fieldset>
            <label for="gform_default_css">
            <input name="wpgform_options[default_css]" type="checkbox" id="gform_default_css" value="1" <?php checked('1', $wpgform_options['default_css']) ; ?> />
            <?php _e('Enable default WordPress Google Form CSS', WPGFORM_I18N_DOMAIN);?></label>
            <br />
            <label for="gform_custom_css">
            <input name="wpgform_options[custom_css]" type="checkbox" id="gform_custom_css" value="1" <?php checked('1', $wpgform_options['custom_css']) ; ?> />
            <?php _e('Enable custom WordPress Google Form CSS', WPGFORM_I18N_DOMAIN);?></label>
            </fieldset></td>
        </tr>
        <tr valign="top">
        <th scope="row"><label><?php printf(__('Custom %s CSS', WPGFORM_I18N_DOMAIN), 'wpGForm');?></label><br/><small><i><?php _e('Optional CSS styles to control the appearance of the Google Form.', WPGFORM_I18N_DOMAIN);?></i></small></th>
            <td>
            <textarea class="regular-text code" name="wpgform_options[custom_css_styles]" rows="15" cols="80"  id="gform_custom_css_styles"><?php echo $wpgform_options['custom_css_styles']; ?></textarea>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php _e('Confirmation Email Format', WPGFORM_I18N_DOMAIN);?></label></th>
            <td><fieldset>
            <label for="gform_email_format">
            <input name="wpgform_options[email_format]" type="radio" id="gform_email_format" value="<?php echo WPGFORM_EMAIL_FORMAT_HTML ;?>" <?php checked(WPGFORM_EMAIL_FORMAT_HTML, $wpgform_options['email_format']) ; ?> />
            Send confirmation email (when used) in HTML format.</label>
            <br/>
            <input name="wpgform_options[email_format]" type="radio" id="gform_email_format" value="<?php echo WPGFORM_EMAIL_FORMAT_PLAIN ;?>" <?php checked(WPGFORM_EMAIL_FORMAT_PLAIN, $wpgform_options['email_format']) ; ?> />
            <?php _e('Send confirmation email (when used) in Plain Text format.', WPGFORM_I18N_DOMAIN);?></label>
            <br />
            <label for="bcc_blog_admin">
            <input name="wpgform_options[bcc_blog_admin]" type="checkbox" id="gform_bcc_blog_admin" value="1" <?php checked('1', $wpgform_options['bcc_blog_admin']) ; ?> />
            <?php _e('Bcc Blog Admin on Confirmation Email (when used)', WPGFORM_I18N_DOMAIN);?></label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php _e('HTTP API Timeout', WPGFORM_I18N_DOMAIN);?></label></th>
            <td><fieldset>
            <label for="gform_http_api_timeout">
            <select style="width: 150px;" name="wpgform_options[http_api_timeout]" id="gform_http_api_timeout">
            <option value="5" <?php selected($wpgform_options['http_api_timeout'], 5); ?>>5 Seconds</option>
            <option value="10" <?php selected($wpgform_options['http_api_timeout'], 10); ?>>10 Seconds</option>
            <option value="15" <?php selected($wpgform_options['http_api_timeout'], 15); ?>>15 Seconds</option>
            <option value="25" <?php selected($wpgform_options['http_api_timeout'], 25); ?>>25 Seconds</option>
            <option value="30" <?php selected($wpgform_options['http_api_timeout'], 30); ?>>30 Seconds</option>
            <option value="45" <?php selected($wpgform_options['http_api_timeout'], 45); ?>>45 Seconds</option>
            <option value="60" <?php selected($wpgform_options['http_api_timeout'], 60); ?>>60 Seconds</option>
            </select>
            <br />
            <small><?php _e('Change the default HTTP API Timeout setting (default is 5 seconds).', WPGFORM_I18N_DOMAIN);?></small></label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php _e('Browser Check', WPGFORM_I18N_DOMAIN);?></label></th>
            <td><fieldset>
            <label for="gform_browser_check">
            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
            <tr>
            <td style="padding: 5px 0px; vertical-align: top;">
            <input name="wpgform_options[browser_check]" type="checkbox" id="gform_browser_check" value="1" <?php checked('1', $wpgform_options['browser_check']) ; ?> />
            </td>
            <td style="padding: 5px;">
            <?php _e('Check browser compatibility?<br/><small>The WordPress Google Form plugin may not work as expected with older browser versions (e.g. IE6, IE7, etc.).</small>', WPGFORM_I18N_DOMAIN);?>
            </td>
            </tr>
            </table>
            </label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php _e('Donation Request', WPGFORM_I18N_DOMAIN);?></label></th>
            <td><fieldset>
            <label for="gform_donation_message">
            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
            <tr>
            <td style="padding: 5px 0px; vertical-align: top;">
            <input name="wpgform_options[donation_message]" type="checkbox" id="gform_donation_message" value="1" <?php checked('1', $wpgform_options['donation_message']) ; ?> />
            </td>
            <td style="padding: 5px;">
            <?php _e('Hide the request for donation at the top of this page.<br/><small>The donation request will remain on the <b>About</b> tab.</small>', WPGFORM_I18N_DOMAIN);?>
            </td>
            </tr>
            </table>
            </label>
            </fieldset></td>
        </tr>
    </table>
    <br /><br />
    <input name="wpgform_options[enable_debug]" type="hidden" id="wpgform_enable_debug" value="<?php echo $wpgform_options['enable_debug'] ; ?>" />
    <input name="wpgform_options[fsockopen_transport]" type="hidden" id="wpgform_fsockopen_transport" value="<?php echo $wpgform_options['fsockopen_transport'] ; ?>" />
    <input name="wpgform_options[streams_transport]" type="hidden" id="wpgform_streams_transport" value="<?php echo $wpgform_options['streams_transport'] ; ?>" />
    <input name="wpgform_options[curl_transport]" type="hidden" id="wpgform_curl_transport" value="<?php echo $wpgform_options['curl_transport'] ; ?>" />
    <input name="wpgform_options[ssl_verify]" type="hidden" id="wpgform_ssl_verify" value="<?php echo $wpgform_options['ssl_verify'] ; ?>" />
    <input name="wpgform_options[local_ssl_verify]" type="hidden" id="wpgform_local_ssl_verify" value="<?php echo $wpgform_options['local_ssl_verify'] ; ?>" />
    <input name="wpgform_options[http_request_timeout]" type="hidden" id="wpgform_http_request_timeout" value="<?php echo $wpgform_options['http_request_timeout'] ; ?>" />
    <input name="wpgform_options[http_request_timeout_value]" type="hidden" id="wpgform_http_request_timeout_value" value="<?php echo $wpgform_options['http_request_timeout_value'] ; ?>" />
<?php
}

/**
 * wpgform_settings_debug()
 *
 * Build the form content and populate with any current plugin settings.
 *
 * @return none
 */
function wpgform_settings_debug()
{
    $wpgform_options = wpgform_get_plugin_options() ;
?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><label><?php _e('Enable Debug', WPGFORM_I18N_DOMAIN);?></label></th>
            <td><fieldset>
            <label for="gform_enable_debug">
            <table style="padding: 0px;" border="0" cellpadding="0" cellspacing="0">
            <tr>
            <td style="padding: 5px 0px; vertical-align: top;">
            <input name="wpgform_options[enable_debug]" type="checkbox" id="gform_enable_debug" value="1" <?php checked('1', $wpgform_options['enable_debug']) ; ?> />
            </td>
            <td style="padding: 5px;">
            <?php printf(__('Enabling debug will collect data during the form rendering and processing process.<p>The data is added to the page footer but hidden with a link appearing above the form which can toggle the display of the debug data.  This data is useful when trying to understand why the plugin isn\'t operating as expected.</p><p>When debugging is enabled, specific transports employed by the <a href="%s">WordPress HTTP API</a> can optionally be disabled.  While rarely required, disabling transports can be useful when the plugin is not communcating correctly with the Google Docs API.  <i>Extra care should be taken when disabling transports as other aspects of WordPress may not work correctly.</i>  The <a href="%s">WordPress Core Control</a> plugin is recommended for advanced debugging of <a href="%s">WordPress HTTP API issues.</a></p>', WPGFORM_I18N_DOMAIN), 'http://codex.wordpress.org/HTTP_API', 'http://wordpress.org/extend/plugins/core-control/', 'http://codex.wordpress.org/HTTP_API');?>
            </td>
            </tr>
            </table>
            </label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php _e('WordPress HTTP API<br/>Transport Control', WPGFORM_I18N_DOMAIN);?></label></th>
            <td><fieldset>
            <label for="gform_fsockopen_transport">
            <input name="wpgform_options[fsockopen_transport]" type="checkbox" id="gform_fsockopen_transport" value="1" <?php checked('1', $wpgform_options['fsockopen_transport']) ; ?> />
            <?php _e('Disable <i><b>FSockOpen</b></i> Transport', WPGFORM_I18N_DOMAIN);?></label>
            <br />
            <label for="gform_streams_transport">
            <input name="wpgform_options[streams_transport]" type="checkbox" id="gform_streams_transport" value="1" <?php checked('1', $wpgform_options['streams_transport']) ; ?> />
            <?php _e('Disable <i><b>Streams</b></i> Transport', WPGFORM_I18N_DOMAIN);?></label>
            <br />
            <label for="gform_curl_transport">
            <input name="wpgform_options[curl_transport]" type="checkbox" id="gform_curl_transport" value="1" <?php checked('1', $wpgform_options['curl_transport']) ; ?> />
            <?php _e('Disable <i><b>cURL</b></i> Transport', WPGFORM_I18N_DOMAIN);?></label>
            <br />
            <label for="gform_ssl_verify">
            <input name="wpgform_options[ssl_verify]" type="checkbox" id="gform_ssl_verify" value="1" <?php checked('1', $wpgform_options['ssl_verify']) ; ?> />
            <?php _e('Disable <i><b>SSL Verify</b></i>', WPGFORM_I18N_DOMAIN);?></label>
            <br />
            <label for="gform_local_ssl_verify">
            <input name="wpgform_options[local_ssl_verify]" type="checkbox" id="gform_local_ssl_verify" value="1" <?php checked('1', $wpgform_options['local_ssl_verify']) ; ?> />
            <?php _e('Disable <i><b>Local SSL Verify</b></i>', WPGFORM_I18N_DOMAIN);?></label>
            </fieldset></td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php _e('HTTP Request Timeout', WPGFORM_I18N_DOMAIN);?></label></th>
            <td><fieldset>
            <label for="gform_http_request_timeout">
            <input name="wpgform_options[http_request_timeout]" type="checkbox" id="gform_http_request_timeout" value="1" <?php checked('1', $wpgform_options['http_request_timeout']) ; ?> />
            <?php _e('Change <i><b>HTTP Request Timeout</b></i>', WPGFORM_I18N_DOMAIN);?></label>
            <br />
            <label for="gform_http_request_timeout_value">
            <input name="wpgform_options[http_request_timeout_value]" type="text" id="wpgform_http_request_timeout_value" value="<?php echo $wpgform_options['http_request_timeout_value'] ; ?>" /><br />
           <small><?php _e('(in seconds)', WPGFORM_I18N_DOMAIN);?></small></label>
            </fieldset></td>
        </tr>
    </table>
    <br /><br />
    <input name="wpgform_options[sc_posts]" type="hidden" id="wpgform_sc_posts" value="<?php echo $wpgform_options['sc_posts'] ; ?>" />
    <input name="wpgform_options[sc_widgets]" type="hidden" id="wpgform_sc_widgets" value="<?php echo $wpgform_options['sc_widgets'] ; ?>" />
    <input name="wpgform_options[default_css]" type="hidden" id="wpgform_default_css" value="<?php echo $wpgform_options['default_css'] ; ?>" />
    <input name="wpgform_options[custom_css]" type="hidden" id="wpgform_custom_css" value="<?php echo $wpgform_options['custom_css'] ; ?>" />
    <input name="wpgform_options[custom_css_styles]" type="hidden" id="wpgform_custom_css_styles" value="<?php echo $wpgform_options['custom_css_styles'] ; ?>" />
    <input name="wpgform_options[donation_message]" type="hidden" id="wpgform_donation_message" value="<?php echo $wpgform_options['donation_message'] ; ?>" />
    <input name="wpgform_options[email_format]" type="hidden" id="wpgform_email_format" value="<?php echo $wpgform_options['email_format'] ; ?>" />
    <input name="wpgform_options[browser_check]" type="hidden" id="wpgform_browser_check" value="<?php echo $wpgform_options['browser_check'] ; ?>" />
    <input name="wpgform_options[serialize_post_vars]" type="hidden" id="wpgform_serialize_post_vars" value="<?php echo $wpgform_options['serialize_post_vars'] ; ?>" />
    <input name="wpgform_options[bcc_blog_admin]" type="hidden" id="wpgform_bcc_blog_admin" value="<?php echo $wpgform_options['bcc_blog_admin'] ; ?>" />
<?php
}
?>
