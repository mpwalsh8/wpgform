=== Google Forms ===
Contributors: mpwalsh8
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC
Tags: Google Forms, Google Docs, Google, Spreadsheet, shortcode, forms
Requires at least: 4.0
Tested up to: 4.9.8
Stable tag: 0.95
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Embeds a published, public Google Form in a WordPress post, page, or widget.

== Description ==

Fetches a published Google Form using a WordPress custom post or shortcode, removes the Gooogle wrapper HTML and then renders it as an HTML form embedded in your blog post or page. When using Google Form post type, the *wpgform* shortcode accepts one parameter, *id*, which is the post id of the form.  When using the __deprecated__ *gform* shortcode, the only required parameter is `form` which is set to the URL to the Google Form URL.  Recommended but optional, you can also provide a custom URL for a confirmation page if you don't care for the default Google text.  The confirmation page will override the default Google `Thank You` page and offers better integration with your WordPress site.  There are a number of other options, refer to the documentation for further details.

For example, suppose you want to integrate the form at `https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0`, (not a real URL) use the following shortcode in your WordPress post or page:

    [wpgform id='861']

    [gform form='https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0']

__Deprecated:__ use of the *gform* shortcode is __deprecated__ - please use the *wpgform* shortcode.

Currently, this plugin only supports Google Forms that are "Published as a web page" and therefore public. Private Google Forms are not supported.

[Demo](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/)

== Installation ==

1. Install using the WordPress Pluin Installer (search for `WordPress Google Form`) or download `WordPress Google Form`, extract the `wpgforms` folder and upload `wpgforms` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure `WP Goolge Forms` from the `Settings` menu as appropriate.
1. Recommended:  Create a Google Form Custom Post Type and then use the `[wpgform id='<Google Form CPT Id>']` shortcode wherever you'd like to insert the Google Form or simply publish the form and use it's permalink URL.
1. Alternatively:  Use the `[gform form='<full_url_to_form>']` shortcode wherever you'd like to insert the Google Form.

== Usage ==

As features have been added, usage of the `gform` shortcode has grown increasing complex (the `gform` shortcode is now *deprecated*).  Begining with v0.46, a second shortcode, `wpgform` has been introduced in conjunction with a Custom Post Type to define forms deprecating usage of the `gform` shortcode. Usage of the new shortcode and Custom Post Type is much, much easier than the complexities of the original `gform` shortcode.  Users are strong encouraged to migrate usage to the new shortcode and Custom Post Type.  New features will only be added to the Custom Post Type usage model.

The WordPress Google Form shortcode `wpgform` supports a single attribute.  The rest of the controls are derived from the information stored with the Custom Post Type.

`[wpgform id='<Google Form CPT Id>' uid='<unique text string>']`

*NOTE:*  In the above syntax, values enclosed in angle brackets <>, indicate a string you need to replace with an appropriate value.  Do not include the angle brackets in your string!

* __id__:  The numeric id of the Google Form Custom Post Type.
* __uid__:  A unique string (e.g. 'A-') used to ensure form element ID attributes are unique when a form appears on a page multiple times. (optional)

The Google Form *deprecated* shortcode `gform` supports a number of attributes that allow further control and customization of the Google Form.

`[gform form='<full_url_to_Google_Form>' confirm='<full_url_to_confirmation_page>' class='<value>' legal='on|off' br='on|off' prefix='<value>' suffix='<value>' email='on|off' sendto='<email address>' style='redirect|ajax' spreadsheet='<full_url_to_Google_Spreadsheet>' unitethemehack='on|off']`

*NOTE:*  In the above syntax, values enclosed in angle brackets <>, indicate a string you need to replace with an appropriate value.  Do not include the angle brackets in your string!

* __form__:  The full URL to the published Google Form.  You must be able to open this URL successfully from a browser for the __gform__ shortcode to work properly.
* __confirm__:  A full URL to the confirmation (e.g. _Thanks for your submission!_) page.  Be default Google displays a very basic confirmation page which cannot be integrated easily with your WordPress site.  The _confirm_ attribute allows the form submission to land on a page of your choosing.  **It is strongly encouraged that you make use of a confirmation page.**  It will make the form submission process cleaner and clearer to the end user.  The confirmation page will be displayed by a page redirect unless a different behavior is specified using the __style__ attribute.
* __style__:  Specify how the custom confirmation page should be presented.  Legal values for the __style__ attribute are __redirect__ and __ajax__ (e.g. __style='redirect'__ or __style='ajax'__).
* __alert__:  A message to display upon successful form submission in a Javascript Alert box (e.g. _Thanks for your submission!_).
* __class__:  Google Forms are full of classes but the WordPress Google Form plugin does not bring their definitions into page when importing the form.  The _class_ attribute allows the addition of one or more CSS classes to the DIV which wraps the Google Form.  To add multiple classes, simply separate the class names with spaces.
* __legal__:  By default Google Forms have a _Powered by Google Docs_ section at the bottom of the form which includes links to Google TOS and other Google information.  If you do not want to see this information as part of the form, add `legal='off'` to your shortcode usage.  The content remains in the form, it is simply hidden from the end user using CSS.
* __br__:  For a &lt;br&gt; tag to be inserted between the form label and the input text box by setting the *br* attribute to *on*.  This will result in the form label and the input box being stacked on top of one another.
* __prefix__:  Google Forms make use 20+ CSS classes.  If you use multiple forms and want to style them each differently, you can add a _prefix_ which will be added to beginning of each class name used in the Google Form.
* __suffix__:  Append a character string to the end of each form label.  This can also be accomplished using CSS, refer to the CSS section.
* __title__:  By default Google Forms have title wrapped in a &lt;h1&gt; tag.  If you do not want to include this form title as part of the form, add `title='off'` to your shortcode usage.  The &lt;h1&gt; content is removed from the form.
* __maph1h2__:  By default Google Forms have title wrapped in a &lt;h1&gt; tag.  If you want the form title but not as an &lt;h1&gt; element, add `maph1h2='on'` to your shortcode usage.  The &lt;h1&gt; elements will be mapped to &lt;h2&gt; elements.  The CSS class attributes remain unchanged.
* __email__:  Notify the site's WordPress administrator (or sendto email address) that a form has been submitted by setting the __email__ attribute to __on__.  This will result in an email being sent to the blog administrator (or sendto email address) letting them know a form was submitted with the URL of the form along with the date and time of submission.
* __sendto__:  Notify the "sendto" email address that a form has been submitted by setting the __email__ attribute to __on__.  This will result in an email being sent to the "sendto" letting them know a form was submitted with the URL of the form along with the date and time of submission.  The email message will always be sent to the blog administrator via Bcc.
* __spreadsheet__:  The full URL to the "Shared" Google Docs Spreadsheet which stores the form responses.  You must be able to open this URL successfully from a browser for the link in the email to work properly.  This attribute is used in conjunction with the __email__ attribute, it has no effect when the __email__ attribute is not turned __on__.
* __unitethemehack__:  Off by default, this attribute should be enabled, `unitethemehack='on'`, when using the [Unite theme from Paralleus](http://themeforest.net/item/unite-wordpress-business-magazine-theme/90959).  The Unite theme manipulates the submit button(s) on a page, the result of which is a button that prevents the Google form from being submitted.  Turning this attribute on enables a hack which inserts *class="noStyle"* to all submit buttons, preventing Unite from mucking with them.
* __validation__:  Off by default, this attribute can be enabled, `validation='on'`, to add jQuery based form validation support using the [jQuery Validate Plugin](http://bassistance.de/jquery-plugins/jquery-plugin-validation/).  Enabling this optional attribute will allow inline checking without form submission to Google (which also does checking for required fields).  Error messages can be styled using the *gform-error* CSS class.
* __captcha__:  Off by default, this attribute can be enabled, `captcha='on'`, to add a simple math based [CAPTCHA](http://en.wikipedia.org/wiki/CAPTCHA) to the Google Form.  The CAPTCHA only appears for the final submit on multi-page forms. The CAPTCHA error message can be styled using the *gform-error* CSS class.
* __columns__:  The number of columns the form should be split into.  By default the form appears in a single column the same way it is designed in Google Docs.

`[gform form='https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0' confirm='http://www.example.com/thankyou/' style='ajax' class='mygform' legal='off' prefix='mygform-' br='on' title='on' maph1h2='on' email='on' spreadsheet='https://docs.google.com/spreadsheet/ccc?key=0AgBHWDGsX0PUdE56R1ZldXo4a0N3VTNMNEpSemdGV3c' unitethemehack='off' validation='on' captcha='on' columns='1']`

== License ==

This plugin is available under the GPL license, which means that it's free. If you use it for a commercial web site, if you appreciate my efforts or if you want to encourage me to develop and maintain it, please consider making a donation using Paypal, a secured payment solution. You just need to click the donate button on the the [plugin overview page](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/) and follow the instructions.

== Frequently Asked Questions ==

= Why I do I have a "cURL transport missing" message on my Dashboard? =

There was a change to the WordPress HTTP API in version 3.7 which resulted in wp_remote_post() no longer working with the streams and fsockopen transports when posting form data back to Google Docs.  The cURL transport does work reliably so a check has been added and a notification is issued when the cURL transport is missing.  This notification can be hidden by selecting the proper checkbox on the plugin settings page.

= Will the plugin work without the cURL transport? =

Up until WordPress 3.6.1, the plugin worked with any of the supported transports (streams, fsockopen, curl).  Currently only cURL is known to work properly.  You may have some success with other transports IF your form does not include checkboxes.  Checkboxes definitely do not work with anything but the cURL transport.

= How can I add the cURL transport to WordPress? =

The cURL transport is enabled by WordPress when PHP support for cURL is detected.  It isn't something which can be added to WordPress, it needs to be present in the PHP version your web server is running.  Contact your hosting provider to inquire about adding cURL support to PHP for your WordPress site.

= The default style is ugly. Can I change it? =
Yes, there are two ways to change the style (aka apearance) of the form.

1. By adding the necessary CSS to your theme's style sheet.
1. Through the WordPress Google Form custom CSS setting.

Google Forms include plenty of [CSS](http://en.wikipedia.org/wiki/Cascading_Style_Sheets) hooks. Refer to the **CSS** section for further details on styling the form.  There are also some CSS solutions posted to questions users have raised in the Tips and Tricks section of [this page](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/tips-and-tricks/).

= Why are the buttons and some other text on form in Chinese (or some other language)? =

This problem occurred fairly infrequently with the older version of Google Forms but with the upgrade in early 2013, it seems to happen much more often.  The solution to this problem depends on which URL format your published Google Form takes on (old or new).

If your form URL looks like this, then you are using the older version of Google Forms:

`https://docs.google.com/spreadsheet/viewform?formkey=dE56R1ZldXo4a0N3VTNMNEpSemdGV3c6MQ#gid=0`

To force the language to English, you need to include the parameter **hl=en**.  Placement doesn't matter except it must appear before the #gid=0 (or similar syntax depending on which sheet you're using).  You will either need to prefix or append the & character to ensure the parameter is passed correctly.

`https://docs.google.com/spreadsheet/viewform?formkey=dE56R1ZldXo4a0N3VTNMNEpSemdGV3c6MQ&hl=en#gid=0`

If your form URL looks like this, then you are using the new version of Google Forms:

`https://docs.google.com/forms/d/1iQndtNhFFiLHPdTpvuYKifdsxN7XQSFa9D8CsTU8aTc/viewform`

To force the form to use English you would append **"?hl=en"** to the URL so it looks like this:

`https://docs.google.com/forms/d/1iQndtNhFFiLHPdTpvuYKifdsxN7XQSFa9D8CsTU8aTc/viewform?hl=en`

You can find an [example Google Form with French buttons](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/sample-form-in-french/) on the plugin web site.

= Why do I get a 403 error? =

There a number of reasons to get a 403 error but by far the most common one encountered so far is due to ModSecurity being installed by your web hosting provider.  Not all providers deploy ModSecurity but enough do that it comes up every once in a while.  If your provider is running ModSecurity and your version of the plugin is v0.30 or lower, you will likely see odd behavior where when the form is submitted, the page is simply rendered again and the data is never actually sent to Google.  There isn't any error message to indicate what might be wrong.

Version 0.31 fixes this problem for *most* cases but there is still a chance that it could crop up.  If your provider has enabled ModSecurity AND someone answers one of the questions on your form with a URL (e.g. http://www.example.com), then very likely ModSecurity will kick in an issue a 403 error.  The plugin is now smart enough to detect when the error is issued and let you know what is wrong.  Unfortunately there isn't currently a solution to allow URLs as responses when ModSecurity issues a 403 error.

Some themes filter page content which could potentially affect forms.  If the filter modifies the Google Form HTML in such a way (e.g. changing the value of a hidden form variable) such that it is different that what Google is expecting upon form submission, a 403 error may result.

= No matter what I do, I always get the "Unable to retrieve Google Form.  Please try reloading this page." error message.  Why is this? =

1. The most common reason for this error is from pasting the Google Form URL into the WordPress WYSIWYG Editor while in "Visual" mode.  When you paste the URL, the Visual Editor recognizes at a link and wraps the text in the apprpriate HTML tags so the link will work.   Visually you'll trypically see the URL in a different color than the rest of the short code text.  If this happens, simply click anywhere in the link and use the "Break Link" icon (broken chain) on the tool bar to remove the link.  The other alternative is to toggle to HTML mode and manually remove the HTML which is wrapped around the URL.

1. Validate that the WordPress HTTP API is working correctly.  If you are seeing HTTP API errors on the WordPress Dashboard or when you attempt to access the plugin repository through the Dashboard, the WordPress Google Form will likely fail too.  It requires the WordPress HTTP API to be working.  With some free hosting plans, ISPs disable the ability to access remote content.

= Do you have a demo running? =
Yes, see a demo here:  [Demo of WordPress Google Form plugin](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/)

Feel free to submit a response and then view other responses as well.

= Content appears, but it's not my form and it looks odd! Why? =
You should triple-check that you've published your Form. Google provides instructions for doing this. Be sure to follow steps 1 and 2 in [Google Spreadsheets Help: Publishing to the Web](http://docs.google.com/support/bin/answer.py?hl=en&answer=47134) as the same process applies to Forms and Spreadsheets.

= Why doesn't my form look the same as it does when I use the stand alone URL? =
Google Forms can have _Themes_ which are really nothing more than CSS defitinions to change the form's appearance.  None of the Google CSS is brought into WordPress, just the CSS class names and the HTML used to define the form.  Refer to the **CSS** section for more information on styling your form.

= Google supports embedding forms, why isn't that sufficient? =
For many uses the simple embedding of a Google Form in an `<iframe>` may be sufficient.  However, if you want your forms to take on the look and feel of your WordPress site while retaining the ability to collect information in a Google Spreadsheet, the WordPress Google Form plugin may be for you.

= I really like having a colon character after my form labels, is that possible? =
Sure.  You can use the following CSS to have the colon character appear after all of your form labels.

`
label.ss-q-title:after {
    content: ':';
}
`

= I don't like the redirection behavior of the custom confirmation, can you change it back to the way it worked in v0.10? =
Unfortunately not.  I understand that the older behavior is preferable as it looks cleaner for the end user however there is no way to support multi-page Google Forms using the old model.  The requirement to support multi-page Google Forms is a higher priority than the older confirmation model based on the overwhelming feedback received to support multi-page forms.  In v0.26 a new confirmation behavior was introduced which uses AJAX to update the page with the content from the custom confirmation page.  In v0.27 the redirection mechanism has returned to be the default behavior but if the AJAX methodology is preferred, it is available by setting the `style='ajax'` attribute within the shortcode.

= Can I change the range of values the CAPCTHA is based on? =
Not at this time.  However, you can choose between two (2) or three (3) terms for the CAPTCHA.

= Can I change the math operator the CAPTCHA is based on? =
Yes.  Beginning in v0.46 you can specify which operator(s) (+, -, *) the CAPTCHA will be based on.

= How do specify which fields go in which columns when splitting a form across multiple columns? =
This isn't possible.  The process of splitting the form into columns is automatic and will try to balance the colummn content.  You can rearrange the fields on the Google side to have some affect on their placement but there isn't any control over the exact column layout or where splits are inserted.

== CSS ==

Google Forms make use of 20+ CSS class definitions.  By default, the WordPress Google Form plugin includes CSS declarations for all of the classes however the bulk of them are empty.  The default CSS sets the font and makes the entry boxes wider.  The default CSS that ships with WordPress Google Form can optionally be turned off via the WordPress Google Form settings.

= Customizing Google Form CSS =

There are two ways to customize the Google Form CSS.

1.  The WordPress Google Form plugin includes a setting to include custom CSS and a field where custom CSS can be entered.  This CSS will be preserved across themes.
1.  Add custom CSS declarations to your WordPress theme.

= Default Google Form CSS =

As of 2013-12-04, the following is are the CSS classes which Google Forms make use of.  The CSS below represents the default CSS provided by WordPress Google Form.  These CSS definitions can be copied and pasted into your theme CSS or the WordPress Google Form custom CSS setting and changed as desired.  Some of the classes are redundant to account for both the new and old style of Google Forms.

`
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * CSS declarations for Google Docs Forms
 *
 * These can be copied and modified to fit the needs of
 * a theme.  By default the only change is to make all of
 * the fields wider than their default width and to set the
 * default font.
 */

label.gform-error,
label.wpgform-error {
    float: right;
    color: red;
    font-weight: bold;
}

div.gform-captcha,
div.wpgform-captcha {
    margin: 5px 0px 10px;
    display: none;
}

div.gform-browser-warning,
div.gform-browser-error,
div.wpgform-browser-warning,
div.wpgform-browser-error {
    -webkit-border-radius: 3px;
    border-radius: 3px;
    border-width: 1px;
    border-style: solid;
    padding: 0 .6em;
    margin: 5px 0 15px;
}

div.gform-browser-warning,
div.wpgform-browser-warning {
    background-color: #ffffe0;
    border-color: #e6db55;
}

div.gform-google-error,
div.gform-browser-error,
div.wpgform-google-error,
div.wpgform-browser-error {
    background-color: #ffebe8;
    border-color: #cc0000;
}

body.ss-base-body {}
div.errorbox-good {}
div.ss-attribution {}
div.ss-footer {}
div.ss-footer-txt, div.ss-logo {
    display: none;
}

div.ss-form {}
div.ss-form-container {
    font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
}
div.ss-form-desc {}
div.ss-form-entry {}
div.ss-form-entry>input {
    background-color: #e0e0e0;
    font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
}
div.ss-form-heading {}
div.ss-item {}
div.ss-legal {}
div.ss-navigate {}
div.ss-no-ignore-whitespace {
    white-space: pre-wrap;
}
div.ss-required-asterisk {
    color: red;
    font-weight: bold;
}
div.ss-scale {}
div.ss-text {}
form#ss-form {}
h1.ss-form-title {}
hr.ss-email-break {}
input.ss-q-short:text {
	width: 300px;
}
label.ss-q-help {
    display: block;
}
label.ss-q-radio {}
label.ss-q-title {
    font-weight: bold;
}
span.ss-powered-by {}
span.ss-terms {}
td.ss-gridnumber {}
td.ss-gridnumbers {}
td.ss-gridrow
td.ss-gridrow-leftlabel
td.ss-leftlabel {}
td.ss-rightlabel {}
td.ss-scalerow {}
td.ss-scalenumber {}
td.ss-scalenumbers {}
textarea.ss-q-long {
    background-color: #e0e0e0;
    font-family: "Trebuchet MS", Verdana, Arial, Helvetica, sans-serif;
}
tr.ss-gridrow {}

/**
 * New Google Forms CSS 2013-04-30
 */

div.ss-form-container div.disclaimer {
    display: none;
}

div.ss-q-help {
}

div.ss-secondary-text {
}

/*  This hides the "Never submit passwords through Google Forms." warning. */
td.ss-form-entry > div.ss-secondary-text {
    display: none;
}

div.password-warning {
    display: none;
}

div.ss-form-container li {
    list-style-type: none;
}

/*  2013-06-04:  Hide "Edit this Form" link */
a.ss-edit-link {
    display: none;
}

/*  2013-06-06:  Hide help text for scales */
div.aria-only-help {
    display: none;
}

/* 2013-10-30:  Hide default error messages */
div.error-message {
    display: none;
}

/* 2013-10-30:  Attempt to make text entry boxes a reasonable width */
input.ss-q-short, textarea.ss-q-long {
    width: auto;
}

/* 2013-11-15:  CSS to support using WordPress Google form to render spreadsheets */

/**  Hide the gunk that Google adds to make the table work **/
td.hd, td.headerEnd, tr.rShim, td.sortBar {
    display: none;
    width: 0px !important;
    padding: 0px !important;
}

/**  Empty selector but could be used to select all of the table cells **/
tr.rShim td, tr.rShim ~ tr td {
}

/**  Hide the "powered" and "listview" DIVs that Google adds **/
div div span.powered, div.listview {
    display: none;
}

/** Hide the "This is a required question" message **/
div.ss-form-container div.required-message {
    display: none;
}
`

== Screenshots ==

1. Standard Google Form
2. Same Google Form embedded in WordPress
3. Setting Page

== Upgrade Notice ==

No known upgrade issues.

== Change log ==

= Version 0.95 =
* Added warning/error regarding Google's plans to drop ability to downgrade form rendering the Google Forms plugin unusable.

= Version 0.94 =
* Added sanitization of user agent field in submission log.
* Replaced use of eval() in CAPTCHA calculation with alternate solution to address security concerns.
* Updated jQuery Validate to 1.17.0
* Updated jQuery UI Themes to 1.12.1
* Removed http prefix on jQuery UI themes enque to allow https support.

= Version 0.93 =
* Retagged to correct version number.

= Version 0.92 =
* Fixed collision with global $post variable resulting in media uploads being places in the wrong folder.
* Changed expression check to use parse_url() to ensure the form action comes from google.com and not a spoofed domain.

= Version 0.91 =
* Retagged to correct version number.

= Version 0.90 =
* Fixed issue with unused constructor impacting PHP7 support.

= Version 0.89 =
* Added additional security checking to ensure form action resolves to drive.google.com.
* Added additional sanitization and validation of submitted form data.
* Added additional debug information to allow review of form HTML before and after wp_kses().

= Version 0.88 =
* Added WordPress "nonce" to form.
* Added security concern with sanitize_text_field() and esc_attr() on hidden form fields.

= Version 0.87 =
* Added check upon Save to make sure Google Form contains expected HTML structure.  Error displayed when HTML is not as expected (e.g. with the new version of Google Forms).
* Change of error type when content cannot be retrieved from plugin repository.

= Version 0.86 =
* Retagged to correct version number.

= Version 0.85 =
* Development moved to GitHub.
* Replaced call to deprecated function mysql_real_escape_string().
* Fixed security issues with _REQUEST flagged by WordPress.org.

= Version 0.84 =
* Retagged to correct version number.

= Version 0.83 =
* Added ability to suppopress columnization of a form when the browser is smaller than some user specified width.
* Fixed bug with Google Default Text override which prevented form specific overrides from working correctly.

= Version 0.82 =
* Fixed bug with Google default text always being displayed regardless of global or form specific setting.  This caused some buttons to always appear in English.
* Added additional debug information to examine wp_remote_post() parameters.

= Version 0.81 =
* Removed leftover debug code which generated output into the error log.

= Version 0.80 =
* Resolved a number of PHP Strict Standard notices resulting from calling non-static functions statically.

= Version 0.79 =
*  Bug fix for required email validation field with optional message.
*  Added user-agent argument to HTTP API remote get and post requests, allows Google to return HTML5 inputs for browsers which support it (with some limitations).

= Version 0.78 =
*  Bug fix for required validation field with spaces in the optional message.

= Version 0.77 =
*  Minor fixes to eliminate PHP warnings when using deprecated gform shortcode.
*  Fixed bug with CSS prefix and suffix not being added to CSS classes.

= Version 0.76 =
*  Retagged to correct version number.

= Version 0.75 =
*  Added new setting to turn off HTML filtering which has been used to "clean" the HTML retrieved from Google.

= Version 0.74 =
*  More Debugging messages sent to PHP error log removed.

= Version 0.73 =
*  Debugging messages sent to PHP error log removed.

= Version 0.73 =
*  Added support for form specific button override text.
*  Updated jQuery Validate to 1.13.1.
*  Re-worked Javascript loading so only loaded when needed.
*  Fixed jQuery Script loading problem on Dashboard - script loaded for all Dashboard pages instead of just the Google Form editing page.
*  Add jQuery to map the "Submit another resonse" URL to the proper WordPress URL.
*  Fixed bug jQuery script where "Other" label text was not properly selected resulting in duplicate text.  Appears to be due to change in Google generated HTML.
* Added support for multiple email addresses, separated by semicolon, for email notification.
* Added support for Right to Left column ordering in multi-column forms.  Useful for right-to-left languages such as Hebrew.

= Version 0.72 =
*  Fixed call to non-existant function is_empty(), should be calling empty().

= Version 0.71 =
* Bumped version number due to mistagging v0.70 release.

= Version 0.70 =
* Added Serbo Croation translation (thank you Borisa Djuraskovic / www.webhostinghub.com)
* Added support for Regular Expression validation check.  Regular expressions can be tricky, YMMV!
* Fixed a bug which occurs when the Send Email option is enabled but the Send To address isn't specified (should default to admin email).

= Version 0.69 =
* Fixed bug in generated jQuery for Validation which caused syntax errors which could then interfere with redirect.
* Added support for embedded images in Google Forms.

= Version 0.68 =
* Fixed a bug due to missing named parameter passed to wp_update_post() which  manifested itself in a call to array_map() as part of core WordPress function.

= Version 0.67 =
* Fixed typo in internationalization mapping for one of the form buttons.
* Removed leftover debug code.

= Version 0.66 =
* Moved hygiene out of init hook into admin_init hook so it won't run on every page load.  Resolves conflict with WordPress SEO.
* Fixed hygiene to only update post content when it isn't what is expected.
* Fixed bug which caused all form submissions to be logged regardless of plugin setting.

= Version 0.65 =
* Implemented "save_post" for custom post type eliminating general purpose "save_post" (only option prior to WordPress 3.7) action which could potentially, if not handled correctly by another plugin, corrupt post data.
* Formally __deprecated__ the `gform` shortcode by updating README file.
* Added flush of rewrite rules upon plugin activation and deactivation.
* Implemented protocol relative URLs for loading jQuery script from Microsoft CDN to avoid mixed-content warnings when serving over https.
* Fixed layout of CAPTCHA options on settings page.
* Fixed bug with preset values as part of the URL which contain spaces.
* Fixed bug sending End User email upon form submission.
* Refactored construction of email headers based on experience with Email Users plugin.

= Version 0.64 =
* Fixed a number of strings which were missing translation wrapper functions.
* Reverted to manually constructed body parameter for wp_remote_post() to allow checkboxes to be properly passed to Google.
* Fixed warnings generated by calls to static functions which were not declared static.
* Added check for HTTP API cURL transport and issue a warning when not present.  There was a change between WordPress 3.6.1 and 3.7 to the WordPress HTTP API and the streams and fsockopen transports are unable to post form values back to Google using wp_remote_post().
* Added a setting to allow hiding the cURL transport missing message on the Dashboard.
* Added a check to ensure jQuery script isn't output more than once.
* Remove hook into "the_content" to reduce potential conflicts with other plugins (e.g. Wordpress SEO plugin by Yoast).
* Added placeholders for some of the form fields when defining a Form within the UI.

= Version 0.63 =
* Refactored code to which assembles arguments for wp_remote_post() to construct the body argument as an array as opposed to a URL formatted string of concatenated parameters.  The long string was causing problems with newer versions of PHP.  The array of arguments is much cleaner (thanks to David Högborg for providing the basics of a patch).

= Version 0.62 =
* Failed to update stable release tag preventing v0.61 from rolling out.

= Version 0.61 =
* Fixed some default CSS rules which were incorrect.
* Added support for multiple instances of the same form on a single page.

= Version 0.60 =
* Beta reference removed from version string.

= Version 0.59 =
* Added ability to preset values for Google form as part of WordPress URL.
* Added new CSS declarations to default plugin CSS to account for recent changes by Google to Forms.
* Added ability to define fields as "hidden" and preset with a user defined or system defined value.
* Fixed validation limitation which only allowed one validation rule per input.
* Added basic support (CSS, jQuery) to use WordPress Google Form to view a Google Spreadsheet within WordPress.

= Version 0.58 =
* Fixed bug when radio button and checkbox responses contained apostrophe characters.
* Fixed bug when text entry box content contained an ampersand which ended up encoded in the Google sheet.
* Fixed bug(s) with plugin settings which are controlled with checkboxes not being able to be unchecked.
* Added Reset button to return plugin settings to their default state.
* Fixed problem handling newlines (carriage returns) in textarea entries.

= Version 0.57 =
* Added ability to translate "What is" Captcha phrase.
* Updated language translation files.

= Version 0.56 =
* Incorporated es_ES language support from TBD.
* Incpororated Transient support patch from TBD.
* Added support for UTF-8 characters in Google Forms.
* Resolved bug with embedded tabs in form response values.
* Improved handling of default settings for new form creation.
* Added multi-site support for Transients.
* Added support for the placeholder attribute on input tags.

= Version 0.55 =
Incporated a patch version of jQuery Columnizer to fix problem which appears in WordPress 3.6 which includes jQuery 1.10.

= Version 0.54 =
* Added internationalization support for jQuery Validation messages.
* New language support files.
* New jQuery Validation based custom validation option.
* Fixed problem with escaped characters ending up in Google spreadsheet.
* Moved transport control out of debug module and into core code so it can be a permanent setting for some server environments.
* Fixed PHP warning messages which happen with Logging Enabled when some of the server variables don't exist.
* Fixed bug with Form Submission Log setting stickiness.
* Added an optional CAPTCHA message which will appear below the CAPTCHA input when set.

= Version 0.53 =
* Added CSS rule to hide Google's new Edit Link "feature".
* Added support for link (A) elements with class attributes when call wp_kses().
* Added CSS rule to suppress redudant information on Scale widgets.

= Version 0.52 =
* Fixed typos on Options page.
* Fixed long standing bug with Default Options sometimes not initializing or saving correctly.

= Version 0.51 =
* Added FAQ content for common questions.
* Updated CSS information to account for CSS changes in new Google Forms.

= Version 0.50 =
* Fixed jQuery syntax error which happens when validation is on but CAPTCHA and Email User is off.
* Added new CSS to hide the "Never submit passwords through Google Forms." message by default.

= Version 0.49 =
* Inadvertently made help text invisible with CSS, updated default CSS accordingly.

= Version 0.48 =
* Fixed a Javascript error which occurs when using Google Default text overrides AND the form didn't have an "Other" radio button choice.

= Version 0.47 =
* Fixed jQuery syntax errors introduced in certain combinations of form options which resulted in the confirmation page not working.

= Version 0.46 =
* Added support for columns!  New *columns='N'* short code attribute will split the form into columns.
* Fixed CSS for input fields so buttons are not 300px wide by default.
* Began process of deprecating "gform" prefix which will be replaced with "wpgform" prefix for CSS classes.
* Moved CAPTCHA (when in use) from below the submit button to above the submit button.
* Added WordPress Google Form Custom Post Type.
* Added new shortcode *wpgform* to support WordPress Google Form CPT.
* Added error checking on wp_remote_post() to prevent confirmation page redirect when data wasn't actually posted successfully.
* Added support for optional end user email field on forms.  When enabled, the user email is required and must be valid.  This feature is only available from the Google Forms CPT editor.
* Migrated scraping of WordPress Plugin Repository content from wp_remote_get() and HTML parsing to use WordPress Plugin API eliminating potential problems with preg_match_all() which was prone to crashing on some installations.
* Fixed jQuery syntax error in validation selector which caused CAPTCA jQuery not to run on some browsers (e.g. Chrome).
* Added URL of page where form was submitted from to confirmation email.
* Added support for logging form submissions as post meta data.
* Fixed problems with multi-page Google forms introduced with re-design of Google Forms.
* Added support for overriding the default Google Button and Required text.  This is useful when Google servers think the form should be rendered in a language that isn't the same as the rest of the form (e.g. Chinese).
* Rearranged Options page to support new options.  Debug tab is now Advanced Options.
* Fixed CAPCHA bug when validation wasn't specifically enabled.  CAPTCHA requires validation.
* Added default CSS to suppress new Google Forms footer disclaimer.
* Added more overrides for Google Default text (Hints for Radio Buttons and Check Boxes, the Other option for Radio Buttons).
* Fixed a bug which lost current state of Override Settings which disabled overrides.
* Added support for CAPTCHA operators (+, -, *)
* Added support for three (3) CAPTCHA terms
* Fixed Pagination bug when viewing the form submission log.
* Re-arranged Options tabs so most common options are the Options tab, less common on the Advanced tab.
* Added support for rendering forms on public CPT URLs.
* Fixed odd Javascript syntax error which is only an issue with IE7.

= Version 0.45 =
* Updated load of jQuery UI Tabs CSS to latest version.
* Moved generated jQuery script from part of the form to the WordPress wp_footer action.

= Version 0.44 =
* Fixed bug preventing options which are enabled by default from being turned off.

= Version 0.43 =
* Reimplemented shortcode attribute *br='on'* usinq jQuery instead of preg_replace().
* Reimplemented shortcode attribute *legal='off'* usinq jQuery instead of preg_replace().
* Fixed DEBUG mode so it will work with PHP 5.2 (which doesn't support anonymous functions).
* Fixed CSS prefix bugs which prevented CSS prefix from being applied to all Google CSS classes.

= Version 0.42 =
* Fixed typos in ReadMe file.

= Version 0.41 =
* Added simple math based CAPTCHA support.
* Reintroduced jQuery Validation for checking required fields.
* Improved support for multiple forms on one page.
* Fixed several bugs where CSS prefix, when used, was not output in some places.
* Moved Debug control to their own tab on the settings page.
* Added new Debug options to facilicate chasing down HTTP API issues.
* Fixed bug where the CSS prefix, when used, was not being applied properly to elements which had more than one class.  Only the first class was properly prefixed.
 
= Version 0.40 =
* Removed leftover debug code.  Again.  :-(

= Version 0.39 =
* Added new attribute *unitedthemehack='on|off', which defaults to 'off'.  This attribute allows WordPress Google Form to work correctly with Paralleus' Unite theme (which mucks with the submit button(s) on the Google Form preventing the form from being submitted).
 
= Version 0.38 =
* Removed debug code left in from working on problem fixed in v0.36.

= Version 0.37 =
* Fixed inacuracies in ReadMe.txt file which caused repository not to show available update.

= Version 0.36 =
* Fixed a bug which appears when the Browser Check option is enabled.  There was a conflict in the server response from Google and the server response from WordPress due to overwriting a variable.
* Fixed format of plain text email response when email is enabled for form submission.  The information in the email was being inserted into the template incorrectly.

= Version 0.35 =
* Changed format of email to use the title of the page/post instead of the permalink to the form.
* Added new shortcode attribute __spreadsheet__.  The value __spreadsheet__ attribute is a full URL to a shared Google Docs Spreadsheet which contains the responses to the form.  A link to the Spreadsheet is included in the email notification when enabled.

= Version 0.34 =
* Fixed syntax error which caused plugin to fail.  Whoops.

= Version 0.33 =
* Fixed inacuracies in ReadMe.txt file.

= Version 0.32 =
* New option to control Bcc to blog admin when using email notification.  By default this option is enabled to allow plugin to behave as it has in prior versions.
* Fixed bug in processing default plugin settings which are on by default.  New options which are on by default were not recognized.
* Fixed activation bug which didn't set all of the default settings correctly.

= Version 0.31 =
* Separation of rendering and processing of the Google Form to better work with sites that make multiple calls to `do_shortcode()`.
* Added a new option to enable "debug".  Enabling debug will add some hidden information to the page.  The visibility of this hidden information can be toggled on an off using a link which is inserted into the page above form.  This debug information is useful for chasing down odd behavior, in particular 403 errors which tend not to be real obvious.
* Significant change to better support servers which have Apache ModSecurity enabled.  Sites which employ ModSecurity may result in 403 errors which are hard to determine because in most cases, the page with the form on it will simply be displayed again.  The plugin now tries to detect 403 errors and when found, will issue a message as part of the form rendering.

= Version 0.30 =
* Changed generated CSS to limit the possibility that it is affected by 'the_content' or 'wpautop' filters resulting in CSS errors.  This rare situation would prevent the custom CSS from being applied correctly.
 
= Version 0.29 =
* Added ability to specify email address when email='on' attribute is in use via new attribute 'sendto'.
* Changed generated Javascript to limit the possibility that it is affected by 'the_content' or 'wpautop' filters resulting in Javascript errors.  This rare situation would prevent the page confirmation or redirection from loading correctly.

= Version 0.28 =
* Fixed bug with missing GetPageURL method which appears when email confirmation is enabled (email='on').

= Version 0.27 =
* Added ability to check and warn for old and/or unsupported browsers.  There is an option on the WordPress Google Form settings page to enable this check.  When an old or unsupported browser is detected, a message will be displayed on top of the form.  The browser check is based on the same functionality that WordPress uses on the Dashboard.
* Changed default custom confirmation behaviour has reverted back to using a javascript redirect as it did from v0.11 through v0.25.
* Added new shortcode attribute, __style__, to control how confirmation pages should be handled.  There are two options:  __style='redirect'__ which is the default and __style='ajax'__ which loads the page content via AJAX.
* Added new CSS classes to support errors and warnings for the browser check and the inability to load Google Forms.
* Cleaned up Options page GUI.

= Version 0.26 =
* Added new shortcode attribute __email='on|off'__, the default setting is 'off'.
* Changed confirmation page from a hard redirect to an AJAX page load.
* Added new email format choice on the Options page, default is HTML.
* Cleaned up some dead code and comments.

= Version 0.25 =
* Fixed problem with checkbox processing when using the prefix attribute.
* Fixed problem with hiding legal links when using the prefix attribute.
* Fixed problem with __legal='off'__ attribute not being processed correctly.

= Version 0.24 =
* Fixed minor typos and other assorted nits.

= Version 0.23 =
* Fixed problem with UTF-8 characters not being passed through the form correctly.

= Version 0.23 =
* Fixed problem where CSS declarations were emitted as plain text when they shouldn't be.

= Version 0.22 =
* Fixed bug with checkboxes not working because jQuery wasn't loaded.

= Version 0.21 =
* Added ability to display a Javascript alert box upon successful form submission.
* Fixed more syntax errors in the ReadMe.txt markdown.

= Version 0.20 =
* More documentation cleanup.

= Version 0.19 =
* Documentation updates in the READMME.txt file.
* Update information on About Tab to reflect new architecture.

= Version 0.18 =
* Fixed version numbering so the WordPress repository would work!

= Version 0.17 =
* Fixed regular expression bug which prevented complex Google Forms from working correctly.  Any form which had more than 9 fields or had enough edits such that the form ids contained more than one digit which have been affected by this bug.

= Version 0.16 =
* Fixed bug with *select* input tags.  Selected value was not being retained on a multipage form.
* Fixed bug with passing checkbox values.  Only one value, the last selected, was being passed for a multiple choice question.
* Rearchitected process for passing parameters to the Google Form with wp_remote_post().

= Version 0.15 =
* Fixed bug with default options which manifested itself always loading the default options for any setting which is on by default even when turned off by user.
* Removed loading of jQuery-Validate as it is no longer used.
* Removed debug and other deprecated code (e.g. wpgform_footer()).

= Version 0.14 =
* Fixed minor bug with default options which manifested itself as an array index warning on the Options page.

= Version 0.13 =
* Fixed bug where values for check boxes and radio buttons was not retained when going back on multi-page Google Forms.

= Version 0.12 =
* Debug code removed and typos fixed.

= Version 0.11 =
* Re-architected plugin to support multi-page Google Forms.
* Fixed bug which resulted in form being redisplayed when using default confirmation.
* Deprecated use of jQuery Validaor plugin, it is no longer needed as Google is now doing required field checking and validation as part of the new architecture.
* New CSS styles added to support new architecture and confirmation page rendering.
* Default CSS style for div.ss-q-help changed to `display: block;`.
* New attribute *title* added to allow supressing for form title.
* New attribute *maph1h2* added to map H1 elements to H2 elements.

= Version 0.10 =

* Added support for required fields using the jQuery Validate plugin.
* Added CSS classes to support jQuery required field validation.

= Version 0.9 =

* Added screenshots.

= Version 0.8 =

* Fixed formatting of CSS section in ReadMe.txt file.
* Added documention for *br* and *suffix* short code attributes.

= Version 0.7 =

* Added new shortcode attribute *br* which can be set to *on* or *off*.  The *br* attribute will insert <br> tags between the labels and the input fields causing them to render on top of one another much they appear on Google Forms.  The default setting is *off*.
* Fixed problem due to Javascript embedded in Google Form.  It isn't necessary so it is stripped out.
* Tweaked several default CSS settings, most notably the default width of text entry fields.
* Cleaned up a bunch of debug code.

= Version 0.6 =

* Fixed problem with CSS text from Google form theme appearing as text on the embedded form.  This text should have been stripped and in some cases it wasn't.
* Fixed typos in ReadMe.txt file.

= Version 0.5 =

* Fixed problem with tabs not working correctly on Settings page.
* Corrected default setting on donation link.

= Version 0.4 =

* Added support for missing Google Forms fields.
* Added more CSS styles based on new form fields.
* Added FAQs and Usage content by retrieving it from the WordPress repository.
* Removed some debug code.

= Version 0.3 =

* Added support for `<select>` and `<option>` tags.
* Fixed Plugin URI path
* Added links to demo content

= Version 0.2 =

* Cleaned up ReadMe.txt file.

= Version 0.1 =

* Initial release.
