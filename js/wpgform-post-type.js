/* vim: set expandtab tabstop=4 shiftwidth=4: */
jQuery('.repeatable-add').click(function() {
	field = jQuery(this).closest('td').find('.custom_repeatable li:last').clone(true);
	fieldLocation = jQuery(this).closest('td').find('.custom_repeatable li:last');
	jQuery('input', field).val('').attr('name', function(index, name) {
		return name.replace(/(\d+)/, function(fullMatch, n) {
			return Number(n) + 1;
		});
	})
	jQuery('select', field).val('').attr('name', function(index, name) {
		return name.replace(/(\d+)/, function(fullMatch, n) {
			return Number(n) + 1;
		});
	})
	field.insertAfter(fieldLocation, jQuery(this).closest('td'))
	return false;
});

jQuery('.repeatable-remove').click(function(){
	jQuery(this).parent().remove();
	return false;
});
	
jQuery('.custom_repeatable').sortable({
	opacity: 0.6,
	revert: true,
	cursor: 'move',
	handle: '.sort'
});

