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
 * wpgform_options_admin_head()
 *
 * Hook into Admin head when showing the options page
 * so the necessary jQuery script that controls the tabs
 * is executed.
 *
 * @return null
 */
function wpgform_options_admin_head()
{
?>
<!-- Setup jQuery Tabs -->
<script type="text/javascript">
    jQuery(document).ready(function() {
        jQuery("#gform-tabs").tabs() ;
    }) ;
</script>
<?php
} /* function wpgform_options_admin_head() */

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
    if (function_exists(screen_icon)) screen_icon() ;
?>
<h2><?php _e('WordPress Google Form Plugin Settings') ; ?></h2>
<?php
    $wpgform_options = get_option('wpgform_options') ;
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
            <li><a href="#gform-tabs-3">Usage Manual</a></li>
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
            <h4>FAQ</h4>
            <p>FAQ goes here.</p>
        </div>
        <div id="gform-tabs-3">
            <h4>Usage</h4>
            <p>Usage goes here.</p>
        </div>
        <div id="gform-tabs-4">
            <h4>About WordPress Google Form</h4>
	    <p>An easy to implement integration of a Google Form with WordPress. This plugin allows you to leverage the power of Google Docs Spreadsheets and Forms to collect data while retaining the look and feel of your WordPress based web site.  The forms can optionally be styled to better integrate with your WordPress theme.</p>
            <p>WordPress Google Form is based on the <a href="http://codex.wordpress.org/Function_API/wp_remote_get"><b>wp_remote_get()</b></a> function for retrieving the form and <a href="http://codex.wordpress.org/Function_Reference/wp_kses"><b>wp_kses()</b></a> function for processing HTML.  If you find this plugin useful and use it for commercial purposes, please consider <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC" target="_blank">making small donations towards this plugin</a> to help keep it up to date.</p>
        </div>
    </div>
</div>
<?php
} /* Function wpgform_options_page() */


/**
 * wpgform_settings_input()
 *
 * Build the form content and populate with any current plugin settings.
 *
 * @return none
 */
function wpgform_settings_input()
{
    $wpgform_options = get_option('wpgform_options') ; ?>
<?php var_dump($wpgform_options) ; ?>
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
            <th scope="row"><label>Donation Request</label></th>
            <td><fieldset>
            <label for="gform_donation_message">
            <input name="wpgform_options[donation_message]" type="checkbox" id="gform_donation_message" value="1" <?php checked('1', $wpgform_options['donation_message']) ; ?> />
            Hide the request for donation at the top of this page.  Donation request will remain on the <b>About</b> tab.</label>
            </fieldset></td>
        </tr>
    </table>
    <br /><br />
<?php
}
?>
