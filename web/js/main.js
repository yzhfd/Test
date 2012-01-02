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
		Backbone.sync = Backbone.localSync;
		window.pageCanvas = new PageCanvas;
	
		$('li', '#hotlib').draggable({
			revert: "invalid", // when not dropped, the item will revert back to its initial position
			containment: 'document',
			helper: "clone",
			cursor: "move"
		});
		$('#page_canvas').droppable({
			accept: '#hotlib > li',
			drop: function(event, ui) {
				// @todo set position, type etc
				console.log(ui.offset.left - $(this).offset().left);
				pageCanvas.hots.add(new Hot());
			}
		});
		
		$('#saveall').click(function () {
			// @todo save
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