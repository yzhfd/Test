var page_edit = function () {

	// Backbone.sync = Backbone.localSync;
	
	/*$(window).bind('beforeunload', function(){ 
		alert('dont leave me alone');
		return false;
	});*/
	
	$('#internRadio').click(function(){
		$('#linkInput').val('0');
	});
	$('#externRadio').click(function(){
		$('#linkInput').val('http://');
	});
	
	window.pageCanvas = new PageCanvas;
	if (initHots.length) {
		window.pageCanvas.load(initHots);
	}

	$('li', '#hotlib').draggable({
		revert: "invalid", // when not dropped, the item will revert back to its initial position
		containment: 'document',
		helper: function (){
			return $('<div style="width:80px; height:80px; background-color:gray;" />');
		},
		cursor: "move"
	});
	$('#page_canvas').droppable({
		accept: '#hotlib > li',
		drop: function(event, ui) {
			var typeText = ui.draggable.attr('title');
			var typeId = ui.draggable.attr('id').split('_')[1];
			
			// use ui.draggable to determine what type it is
			var hot = new Hot({
				type: typeId,
				x: Math.max(ui.offset.left - $(this).offset().left, 0),
				y: Math.max(ui.offset.top - $(this).offset().top, 0),
				width: $(ui.helper).width(),
				height: $(ui.helper).height()
			});
			pageCanvas.hots.add(hot);
		}
	});
	
	// @todo landscape or portrait
	$('#saveall').click(function () {
		$('#page_editor').overlay('loading');
		
		var pageId = $('#pageid').text();
		
		var hots = [];
		pageCanvas.hots.each(function(hot){
			//delete hot.attributes['id'];
			hots.push(hot.attributes);
		});
		
		$.ajax({
			url: Routing.generate('page_hots_save'),
			type: 'POST',
			data: { 'hots':hots, 'id':pageId },
			success: function (response) {
				pageCanvas.hots.each(function(hot, index){
					if (!hot.id) {
						hot.set({id:response[index]});
					}
				});
			}
		}).done(function(){
			$('#page_editor').overlay('hide');
		});
	});		
	
	$('#flushall').click(function () {
		localStorage.clear();
	});
	$('#undo').click(function () {
		undomanager.undo();
	});
	$('#redo').click(function () {
		undomanager.redo();
	});
};