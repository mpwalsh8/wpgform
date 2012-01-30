=== Plugin Name ===
Contributors: mpwalsh8
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=DK4MS3AA983CC
Tags: Google Forms, Google Docs, Google, Spreadsheet, shortcode, forms
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 0.17

Embeds a published, public Google Form in a WordPress post, page, or widget.

== Description ==

Fetches a published Google Form using a `[gform form='']` WordPress shortcode, removes the Gooogle wrapper HTML and then renders it as an HTML form embedded in your blog post or page. The only required parameter is `form`, which specifies the form you'd like to retrieve. Recommended but optional, you can also pass a URL for a confirmation page.  The confirmation page will override the default Google`Thank You` page and offers better integration with your WordPress site.  You can also supply a customized `class` value for styling the form.

For example, suppose you want to integrate the form at `https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0`, (not a real URL) use the following shortcode in your WordPress post or page:

    [gform form='https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0']

Currently, this plugin only supports Google Forms that are "Published as a web page" and therefore public. Private Google Forms are not supported.

[Demo](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/)

== Installation ==

1. Install using the WordPress Pluin Installer (search for `WordPress Google Form`) or download `WordPress Google Form`, extract the `wpgforms` folder and upload `wpgforms` to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Configure `WP Goolge Forms` from the `Settings` menu as appropriate.
1. Use the `[gform form='<full_url_to_form>']` shortcode wherever you'd like to insert the Google Form.

== Usage ==

The WordPress Google Form shortcode `gform` supports a number of attributes that allow further control and customization of the Google Form.

`[gform form='<full_url_to_Google_Form>' confirm='<full_url_to_confirmation_page>' class='<value>' legal='on|off' br='on|off' prefix='<value>' suffix='<value>']`

* __form__:  The full URL to the published Google Form.  You must be able to open this URL successfully from a browser for the __gform__ shortcode to work properly.
* __confirm__:  A full URL to the confirmation (e.g. _Thanks for your submission!_) page.  Be default Google displays a very basic confirmation page which cannot be integrated easily with your WordPress site.  The _confirm_ attribute allows the form submission to land on a page of your choosing.  **It is strongly encouraged that you make use of a confirmation page.**  It will make the form submission process cleaner and clearer to the end user.
* __class__:  Google Forms are full of classes but the WordPress Google Form plugin does not bring their definitions into page when importing the form.  The _class_ attribute allows the addition of one or more CSS classes to the DIV which wraps the Google Form.  To add multiple classes, simply separate the class names with spaces.
* __legal__:  By default Google Forms have a _Powered by Google Docs_ section at the bottom of the form which includes links to Google TOS and other Google information.  If you do not want to see this information as part of the form, add `legal='off'` to your shortcode usage.  The content remains in the form, it is simply hidden from the end user using CSS.
* __br__:  For a <br> tag to be inserted between the form label and the input text box by setting the *br* attribute to *on*.  This will result in the form label and the input box being stacked on top of one another.
* __prefix__:  Google Forms make use 20+ CSS classes.  If you use multiple forms and want to style them each differently, you can add a _prefix_ which will be added to beginning of each class name used in the Google Form.
* __suffix__:  Append a character string to the end of each form label.  This can also be accomplished using CSS, refer to the CSS section.
* __title__:  By default Google Forms have title wrapped in a <h1> tag.  If you do not want to include this form title as part of the form, add `title='off'` to your shortcode usage.  The <h1> content is removed from the form.
* __maph1h2:  By default Google Forms have title wrapped in a <h1> tag.  If you want the form title but not as an <h1> element, add `maph1h2='on'` to your shortcode usage.  The <h1> elements will be mapped to <h2> elements.  The CSS class attributes remain unchanged.

`[gform form='https://docs.google.com/spreadsheet/viewform?hl=en_US&pli=1&formkey=ABCDEFGHIJKLMNOPQRSTUVWXYZ12345678#gid=0' confirm='http://www.example.com/thankyou/' class='mygform' legal='off' prefix='mygform-' br='on' title='on' maph1h2='on']`

== Frequently Asked Questions ==

= The default style is ugly. Can I change it? =
Yes, there are two ways to change the style (aka apearance) of the form.

1. By adding the necessary CSS to your theme's style sheet.
1. Through the WordPress Google Form custom CSS setting.

Google Forms include plenty of [CSS](http://en.wikipedia.org/wiki/Cascading_Style_Sheets) hooks. Refer to the **CSS** section for further details on styling the form.  There are also some CSS solutions posted to questions users have raised in the Tips and Tricks section of [this page](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/tips-and-tricks/).

= Do you have a demo running? =
Yes, see a demo here:  [Demo of WordPress Google Form plugin](http://michaelwalsh.org/wordpress/wordpress-plugins/wpgform/)

Feel free to submit a response and then view other responses as well.

= Content appears, but it's not my form and it looks odd! =
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

= No matter what I do, I always get the *Unable to retrieve Google Form.  Please try reloading this page.* error message.  Why is this?
Validate that the WordPress HTTP API is working correctly.  If you are seeing HTTP API errors on the WordPress Dashboard or when you attempt to access the plugin repository through the Dashboard, the WordPress Google Form will likely fail too.  It requires the WordPress HTTP API to be working.  With some free hosting plans, ISPs disable the ability to access remote content.

== CSS ==

As of 2011-09-21, Google Forms make use of 20+ CSS class definitions.  By default, the WordPress Google Form plugin includes CSS declarations for all of the classes however the bulk of them are empty.  The default CSS sets the font and makes the entry boxes wider.  The default CSS that ships with WordPress Google Form can optionally be turned off via the WordPress Google Form settings.

= Customizing Google Form CSS =

There are two ways to customize the Google Form CSS.

1.  The WordPress Google Form plugin includes a setting to include custom CSS and a field where custom CSS can be entered.  This CSS will be preserved across themes.
1.  Add custom CSS declarations to your WordPress theme.

= Default Google Form CSS =

As of 2012-01-07, the following is are the CSS classes which Google Forms make use of.  The CSS below represents the default CSS provided by WordPress Google Form.  These CSS definitions can be copied and pasted into your theme CSS or the WordPress Google Form custom CSS setting and changed as desired.

`
label.gform-error {
    float: right;
    color: red;
    font-weight: bold;
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
input.ss-q-short {
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

= Version 0.17 =
* Fixed regular expression bug which prevented complex Google Forms from working correctly.  Any form which had more than 9 fields or had enough edits such that the form ids contained more than one digit which have been affected by this bug.

= Version 0.16 =
* Fixed bug with *select* input tags.  Selected value was not being retained on a multipage form.
* Fixed bug with passing checkbox values.  Only one value, the last selected, was being passed for a multiple choice question.
* Rearchitected process for passing parameters to the Google Form with wp_remote_post().

= Version 0.15
* Fixed bug with default options which manifested itself always loading the default options for any setting which is on by default even when turned off by user.
* Removed loading of jQuery-Validate as it is no longer used.
* Removed debug and other deprecated code (e.g. wpgform_footer()).

= Version 0.14
* Fixed minor bug with default options which manifested itself as an array index warning on the Options page.

= Version 0.13
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
