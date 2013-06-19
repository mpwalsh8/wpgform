/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
 * Localization of jQuery Validate error messages.
 *
 */
jQuery.extend(jQuery.validator.messages, {
    required: wpgform_script_vars.required,
    remote: wpgform_script_vars.remote,
    email: wpgform_script_vars.email,
    url: wpgform_script_vars.url,
    date: wpgform_script_vars.date,
    dateISO: wpgform_script_vars.dateISO,
    number: wpgform_script_vars.number,
    digits: wpgform_script_vars.digits,
    creditcard: wpgform_script_vars.creditcard,
    equalTo: wpgform_script_vars.equalTo,
    accept: wpgform_script_vars.accept,
    maxlength: jQuery.validator.format(wpgform_script_vars.maxlength),
    minlength: jQuery.validator.format(wpgform_script_vars.minlength),
    rangelength: jQuery.validator.format(wpgform_script_vars.rangelength),
    range: jQuery.validator.format(wpgform_script_vars.range),
    max: jQuery.validator.format(wpgform_script_vars.max),
    min: jQuery.validator.format(wpgform_script_vars.min),
});

