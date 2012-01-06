jQuery.event.props.push("dataTransfer");
Backbone.sync = Backbone.ajaxSync;

$(function () {
	if ($('#newPages').length > 0) {
		issue_new();
	}
	
	if ($('#articles_layout').length > 0) {
		articles_layout();
	}
	
	if ($('#magzine_form').length > 0) {
		var magForm = $('#magzine_form');
		magForm.fileupload({
			// maxFilesize, minFileSize
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i
			//dropZone: $('#issue_cover')
		}).bind('fileuploaddrop', function (e, data) {
			var files = data.files;
			var imgFile = files[0];
			 
			var acceptFileTypes = $(this).fileupload('option', 'acceptFileTypes');
			if (!(acceptFileTypes.test(imgFile.type) ||
                    acceptFileTypes.test(imgFile.name))) {
				alert('请上传有效的图片文件');
                return;
            }
			
			// @todo validate image size and dimension
            var reader = new FileReader();
            reader.onload = function (e) {
            	$('#form_landscapeCoverImage').val(e.target.result);
            };
            reader.readAsDataURL(imgFile);
		}).bind('fileuploadsubmit', function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		});
	}
	
	if ($('#page_canvas').length) {
		
		/*$(window).bind('beforeunload', function(){ 
			alert('dont leave me alone');
			return false;
		});*/
		
		Backbone.sync = Backbone.localSync;
		window.pageCanvas = new PageCanvas;
		if (initHots.length) {
			window.pageCanvas.load(initHots);
		}
	
		$('li', '#hotlib').draggable({
			revert: "invalid", // when not dropped, the item will revert back to its initial position
			containment: 'document',
			helper: function (){
				return $('<div style="width:80px; height:80px; background-color:gray;" />')
			},
			cursor: "move"
		});
		$('#page_canvas').droppable({
			accept: '#hotlib > li',
			drop: function(event, ui) {
				// use ui.draggable to determine what type it is
				pageCanvas.hots.add(new Hot({
					x: Math.max(ui.offset.left - $(this).offset().left, 0),
					y: Math.max(ui.offset.top - $(this).offset().top, 0),
					width: $(ui.helper).width(),
					height: $(ui.helper).height()
				}));
			}
		});
		
		// @todo landscape or portrait
		$('#saveall').click(function () {
			$('#page_editor').overlay('loading');
			
			var pageId = $('#pageid').text();
			
			var hots = [];
			pageCanvas.hots.each(function(hot){
				delete hot.attributes['id'];
				hots.push(hot.attributes);
			});
			
			$.ajax({
				url: Routing.generate('page_hots_save'),
				type: 'POST',
				data: { 'hots':hots, 'id':pageId },
				success: function (response) {
					console.log(response);
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
	}
});