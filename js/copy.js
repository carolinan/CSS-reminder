/**
 *  Copy the Custom CSS to the clip board.
 * 
 */

jQuery(document).ready(function ($) {

	var clipboard = new ClipboardJS('.css-reminder-button');
	// Debug information copy section.
	clipboard.on('success', function (e) {

	var $wrapper = $(e.trigger).closest('div');
	$('.success', $wrapper).addClass('visible');
	wp.a11y.speak( __(' The CSS has been added to your clipboard.', 'css-reminder' ) );
	});

} );
