/*
 * A bunch of basic javascript methods 
 *
 */
(function($) {
	// overlay
	$.fn.overlay = function(options) {
		
		var overlay = this.data('overlay');
		if (overlay) {
			if (options == 'hide') {
				var overlayed = this;
				overlay.fadeOut('fast', function () {
					$(this).remove();
					overlayed.removeData('overlay');
				});
			}
			
			return this;
		} else if (options == 'hide') {
			return this;
		}
		
		overlay = $('<div class="overlay" style="position: absolute;"></div>');
		overlay.css({
			width: this.outerWidth(),
			height: this.outerHeight(),
			left: this.offset().left,
			top: this.offset().top,
			backgroundColor: 'white',
			opacity: 0.0
		});
		overlay.appendTo('body');
		
		if (options == 'loading') {
			var loadingEl = $('<img class="centered" width="32" height="32" src="/Magend/web/images/loading.gif" />');
			loadingEl.appendTo(overlay);
			loadingEl.css({
				marginTop: (overlay.height() - loadingEl.height()) / 2
			});
		}
		
		overlay.fadeTo('fast', 0.6);
		
		this.data('overlay', overlay);
		
		return this;
	};
	
	// serialize form to object
	$.fn.serializeObject = function()
	{
	    var o = {};
	    var a = this.serializeArray();
	    $.each(a, function() {
	        if (o[this.name] !== undefined) {
	            if (!o[this.name].push) {
	                o[this.name] = [o[this.name]];
	            }
	            o[this.name].push(this.value || '');
	        } else {
	            o[this.name] = this.value || '';
	        }
	    });
	    return o;
	};
})(jQuery);
