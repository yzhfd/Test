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
	
	if ($('#issue_form').length > 0) {
		$('#issue_cover').click( function (e) {
			e.stopPropagation();
			e.preventDefault();
			
			// @todo show large
		});
		
		// @todo use dropZone's dimension as restriction
		var issueForm = $('#issue_form');
		issueForm.fileupload({
			// maxFilesize, minFileSize
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			dropZone: $('#issue_cover')
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
            	$('#issue_cover').find('img').attr({
        			'src': e.target.result
        		});
            };
            $(this).data('cover', imgFile);
            reader.readAsDataURL(imgFile);
		}).bind('fileuploadsubmit', function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		});
		
		$('#issue_next').click( function (e) {
			var href = $(this).attr('href');
			if (href == '#') {
				alert('请先提交期刊信息');
				return false;
			}
		});
		
		issueForm.submit( function(e) {
			var cover = issueForm.data('cover');
			var coverSrc = $('#issue_cover').find('img').attr('src');
			if (!cover) {
				if (coverSrc == '') {
					alert('请上传封面');
					return false;
				} else {
					return true;
				}
			}
			
			var submitBtn = $(this).find('button[type="submit"]');
			submitBtn.button('loading');
			
			$('<div/>').fileupload({
				paramName: $('#cover_input').find('input').attr('name'),
				url: $(this).attr('action'),
				formData: $(this).serializeArray(),
				success: function (result) {
					submitBtn.button('reset');
					if (!result.id) {
						alert('提交失败');
						return;
					}
					
					alert('提交成功');
					$('#issue_next').attr('href', result.editorUrl);
					// $('#issue_next').removeClass('disabled');
				},
				error: null
			}).fileupload('send', { files:[cover] }); // only send one file
			
			return false;
		});
	}
	
	if ($('#page_canvas').length) {
		Backbone.sync = Backbone.localSync;
		window.pageCanvas = new PageCanvas;
		
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