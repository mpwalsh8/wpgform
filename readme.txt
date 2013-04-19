=== Plugin Name ===
Contributors: mpwalsh8
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC
Tags: Google Forms, Google Docs, Google, Spreadsheet, shortcode, forms
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 0.45

Embeds a published, public Google Form in a WordPress post, page, or widget.

== Description ==

Fetches a published Google Form using a WordPress shortcode, removes the Gooogle wrapper HTML and then renders it as an HTML form embedded in your blog post or page. The only required parameter is the URL to the Google `form`, which specifies the form you'd like to retrieve. Recommended but optional, you can also provide a URL for a confirmation page.  The confirmation page will override the default Google `Thank You` page and offers better integration with your WordPress site.  You can also supply a customized `class` value for styling the form.  There are a number of other options, refer to the documentation for further details.

For example, suppose you want to integrate the form at `https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0`, (not a real URL) use the following shortcode in your WordPress post or page:

    [gform form='https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0']

Currently, this plugin only supports Google Forms that are "Published as a web page" and therefore public. Private Google Forms are not supported.

[Demo](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/)

== Installation ==

1. Install using the WordPress Pluin Installer (search for `WordPress Google Form`) or download `WordPress Google Form`, extract the `wpgforms` folder and upload `wpgforms` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure `WP Goolge Forms` from the `Settings` menu as appropriate.
1. Recommended:  Create a Google Form Custom Post Type and then use the `[wpgform id='<Google Form CPT Id>']` shortcode wherever you'd like to insert the Google Form.
1. Alternatively:  Use the `[gform form='<full_url_to_form>']` shortcode wherever you'd like to insert the Google Form.

== Usage ==

As features have been added, usage of the `gform` shortcode has grown increasing complex.  Begining with v0.46, a second shortcode, `wpgform` has been introduced in conjunction with a Custom Post Type to define forms. Usage of the new shortcode and Custom Post Type is much, much easier than the complexities of the original `gform` shortcode.  Users are strong encouraged to migrate usage to the new shortcode and Custom Post Type.  New features will only be added to the Custom Post Type usage model.

The WordPress Google Form shortcode `wpgform` supports a single attribute.  The rest of the controls are derived from the information stored with the Custom Post Type.

`[wpgform id='<Google Form CPT Id>']`

*NOTE:*  In the above syntax, values enclosed in angle brackets <>, indicate a string you need to replace with an appropriate value.  Do not include the angle brackets in your string!

* __id__:  The numeric id of the Google Form Custom Post Type.

The WordPress Google Form shortcode `gform` supports a number of attributes that allow further control and customization of the Google Form.

`[gform form='<full_url_to_Google_Form>' confirm='<full_url_to_confirmation_page>' class='<value>' legal='on|off' br='on|off' prefix='<value>' suffix='<value>' email='on|off' sendto='<email address>' style='redirect|ajax' spreadsheet='<full_url_to_Google_Spreadsheet>' unitethemehack='on|off']`

*NOTE:*  In the above syntax, values enclosed in angle brackets <>, indicate a string you need to replace with an appropriate value.  Do not include the angle brackets in your string!

* __form__:  The full URL to the published Google Form.  You must be able to open this URL successfully from a browser for the __gform__ shortcode to work properly.
* __confirm__:  A full URL to the confirmation (e.g. _Thanks for your submission!_) page.  Be default Google displays a very basic confirmation page which cannot be integrated easily with your WordPress site.  The _confirm_ attribute allows the form submission to land on a page of your choosing.  **It is strongly encouraged that you make use of a confirmation page.**  It will make the form submission process cleaner and clearer to the end user.  The confirmation page will be displayed by a page redirect unless a different behavior is specified using the __style__ attribute.
* __style__:  Specify how the custom confirmation page should be presented.  Legal values for the __style__ attribute are __redirect__ and __ajax__ (e.g. __style='redirect'__ or __style='ajax'__).
* __alert__:  A message to display upon successful form submission in a Javascript Alert box (e.g. _Thanks for your submission!_).
* __class__:  Google Forms are full of classes but the WordPress Google Form plugin does not bring their definitions into page when importing the form.  The _class_ attribute allows the addition of one or more CSS classes to the DIV which wraps the Google Form.  To add multiple classes, simply separate the class names with spaces.
* __legal__:  By default Google Forms have a _Powered by Google Docs_ section at the bottom of the form which includes links to Google TOS and other Google information.  If you do not want to see this information as part of the form, add `legal='off'` to your shortcode usage.  The content remains in the form, it is simply hidden from the end user using CSS.
* __br__:  For a <br> tag to be inserted between the form label and the input text box by setting the *br* attribute to *on*.  This will result in the form label and the input box being stacked on top of one another.
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

== Frequently Asked Questions ==

= The default style is ugly. Can I change it? =
Yes, there are two ways to change the style (aka apearance) of the form.

1. By adding the necessary CSS to your theme's style sheet.
1. Through the WordPress Google Form custom CSS setting.

Google Forms include plenty of [CSS](http://en.wikipedia.org/wiki/Cascading_Style_Sheets) hooks. Refer to the **CSS** section for further details on styling the form.  There are also some CSS solutions posted to questions users have raised in the Tips and Tricks section of [this page](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/tips-and-tricks/).

= Why do I get a 403 error? =

There a number of reasons to get a 403 error but by far the most common one encountered so far is due to ModSecurity being installed by your web hosting provider.  Not all providers deploy ModSecurity but enough do that it comes up every once in a while.  If your provider is running ModSecurity and your version of the plugin is v0.30 or lower, you will likely see odd behavior where when the form is submitted, the page is simply rendered again and the data is never actually sent to Google.  There isn't any error message to indicate what might be wrong.

Version 0.31 fixes this problem for *most* cases but there is still a chance that it could crop up.  If your provider has enabled ModSecurity AND someone answers one of the questions on your form with a URL (e.g. http://www.example.com), then very likely ModSecurity will kick in an issue a 403 error.  The plugin is now smart enough to detect when the error is issued and let you know what is wrong.  Unfortunately there isn't currently a solution to allow URLs as responses when ModSecurity issues a 403 error.

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
Not at this time.

= Can I change the math operator the CAPTCHA is based on? =
Not at this time.

= How do specify which fields go in which columns when splitting a form across multiple columns? =
This isn't possible.  The process of splitting the form into columns is automatic and will try to balance the colummn content.  You can rearrange the fields on the Google side to have some affect on their placement but there isn't any control over the exact column layout or where splits are inserted.

== CSS ==

As of 2011-09-21, Google Forms make use of 20+ CSS class definitions.  By default, the WordPress Google Form plugin includes CSS declarations for all of the classes however the bulk of them are empty.  The default CSS sets the font and makes the entry boxes wider.  The default CSS that ships with WordPress Google Form can optionally be turned off via the WordPress Google Form settings.

= Customizing Google Form CSS =

There are two ways to customize the Google Form CSS.

1.  The WordPress Google Form plugin includes a setting to include custom CSS and a field where custom CSS can be entered.  This CSS will be preserved across themes.
1.  Add custom CSS declarations to your WordPress theme.

= Default Google Form CSS =

As of 2012-14-15, the following is are the CSS classes which Google Forms make use of.  The CSS below represents the default CSS provided by WordPress Google Form.  These CSS definitions can be copied and pasted into your theme CSS or the WordPress Google Form custom CSS setting and changed as desired.

`
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
`

== Screenshots ==

1. Standard Google Form
2. Same Google Form embedded in WordPress
3. Setting Page

== Upgrade Notice ==

No known upgrade issues.

== Changelog ==

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
