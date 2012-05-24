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
	                	height: 'auto'
	                });
	            }
	        );
	    //prevent the browser to follow the link
	    return false;
	});
	
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