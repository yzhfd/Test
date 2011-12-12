/**
 * jQuery.ajaxQueue - A queue for ajax requests
 * 
 * (c) 2011 Corey Frang
 * Dual licensed under the MIT and GPL licenses.
 *
 * Requires jQuery 1.5+
 */ 
(function($) {

	// jQuery on an empty object, we are going to use this as our Queue
	var ajaxQueue = $({});
	
	$.ajaxQueue = function( ajaxOpts ) {
	    var jqXHR,
	        dfd = $.Deferred(),
	        promise = dfd.promise();
	    
	    // queue our ajax request
	    ajaxQueue.queue( doRequest );
	    
	    // add the abort method
	    promise.abort = function( statusText ) {
	        // proxy abort to the jqXHR if it is active
	        if ( jqXHR ) {
	            return jqXHR.abort( statusText );
	        }
	
	        // if there wasn't already a jqXHR we need to remove from queue
	        var queue = ajaxQueue.queue(),
	            index = $.inArray( doRequest, queue );
	
	        if ( index > -1 ) {
	            queue.splice( index, 1 );
	        }
	        
	        // and then reject the deferred
	        dfd.rejectWith( ajaxOpts.context || ajaxOpts,
	            [ promise, statusText, "" ] );
	
	        return promise;
	    };
	    
	    // run the actual query
	    function doRequest( next ) {
	    	if (ajaxOpts.fileupload == true) {
	    		// paramName, url, and file, [success], [error]
				var uploader = $('<div/>');
				
				jqXHR = uploader.fileupload({
					paramName: ajaxOpts.paramName,
					url: ajaxOpts.url,
					success: ajaxOpts.success,
					error: ajaxOpts.error
				}).bind('fileuploadsubmit', function (e, data) {
					// no upload immediately
					e.stopPropagation();
					e.preventDefault();
				}).fileupload('send', { files:[ajaxOpts.file] }); // only send one file
	    	} else {
	    		jqXHR = $.ajax( ajaxOpts );
	    	}
	    	
	        jqXHR.then( next, next )
	            .done( dfd.resolve )
	            .fail( dfd.reject );
	    }
	    
	    return promise;
	};
})(jQuery);