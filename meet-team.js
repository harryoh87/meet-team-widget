function bananas(parent) {
	var widget = jQuery(parent).closest('.widget');
	jQuery('.widget-control-save', widget).trigger('click');
}
