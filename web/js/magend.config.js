$(document).ready(function(){
	$('a[rel*=dialog]').live('click', function() {
	    var url = this.href;
	    var dialog = $("#dialog");
	    if ($("#dialog").length == 0) {
	        dialog = $('<div id="dialog" style="display:hidden"></div>').appendTo('body');
	    }
	    
	    // load remote content
	    dialog.load(
	            url,
	            // {} then POST
	            function(responseText, textStatus, XMLHttpRequest) {
	                dialog.dialog({
	                	modal: true,
	                	position: [265, 115],
	                	width: 'auto',
	                	height: 'auto',
	                	open: function(event, ui){
	                		// for dialog
		                    if (jQuery().chosen) {
		                    	$('select.chosen').chosen({ 'no_results_text':'没有结果能匹配' });
		                    }
	                	}
	                });
	            }
	        );
	    //prevent the browser to follow the link
	    return false;
	});
	
    if (jQuery().chosen) {
    	$('select.chosen').chosen({ 'no_results_text':'没有结果能匹配' });
    }
	
	$('.tabs').tabs();
	$('.pills').pills();
	
	$('.taggable').tagit({
		allowSpaces: true,
		caseSensitive: false,
		//fieldName: "tags",
		//tagSource: function
		availableTags: ['sexy', 'girl']
	});
	
	$('.alert-message').alert();
	
    $('a[rel^=colorbox]').each(function(index, a){
    	a = $(a);
    	a.colorbox({
    		loop:false,
    		current:'',
    		inline:_(a.attr('href')).startsWith('#'),
    		maxWidth: '800px',
    		maxHeight: '600px'
    	});
    });
	
	$('select.urlSelect').live('change', function(e){
		window.location = $(this).val();
	});
	
	//$('a[rel*=tipsy]').twipsy();
	$('a[rel*=confirm]').live('click', function(e){		
		var title = $(this).attr('title');
		if (!title) title = $(this).attr('data-original-title');
		return confirm('确定' + title + '吗？');
	});
	$('a.dblclick').click( function(e){
		return false;
	});
	$('a.dblclick').dblclick( function(e){
		window.location.href = this.href;
	});
});