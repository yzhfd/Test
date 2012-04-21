jQuery.event.props.push("dataTransfer");
$.datepicker.setDefaults({dateFormat: 'yy-mm-dd'});
Backbone.sync = Backbone.ajaxSync;

$(function () {
	if ($('#issue_form').length > 0) {
		issue_new();
	}
	
	if ($('#newPages').length > 0) {
		article_new();
	}
	
	if ($('#articles_layout').length > 0) {
		articles_layout();
	}
	
	if ($('#magissues').length > 0) {
		$('.publishbtn').click(function(e){
			if (confirm('确定发布吗？')) {
				var publishBtn = $(this);
				$.ajax({
					url: $(this).attr('href'),
					success: function (result) {
						var tr = publishBtn.parent().parent();
						var publishedAt = tr.find('.publishedAt');
						if (publishedAt.text() == '') {
							publishedAt.text(result.publishedAt);
						}
						tr.find('.editbtn').remove();
						publishBtn.replaceWith('已发布');
						alert('成功发布');
					}
				});
			}
			return false;
		});
		$('.previewbtn').click(function(e){
			if (confirm('确定预览吗？')) {
				var previewBtn = $(this);
				$.ajax({
					url: $(this).attr('href'),
					success: function (result) {
						// @todo do something
						alert('已成功生成期刊压缩包');
					}
				});
			}
			return false;
		});
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
		page_edit();
	}
});