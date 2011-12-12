/*
 * A bunch of basic javascript methods 
 *
 */
(function($) {
	// overlay
	$.fn.overlay = function(options) {
		var overlay = $('<div class="overlay" style="position: absolute;"></div>');
		overlay.css({
			width: this.outerWidth(),
			height: this.outerHeight(),
			left: this.offset().left,
			top: this.offset().top,
			backgroundColor: 'white',
			opacity: 0.0
		});
		overlay.appendTo('body');
		overlay.fadeTo('fast', 0.6);
  };
})(jQuery);
