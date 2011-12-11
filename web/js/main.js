jQuery.event.props.push("dataTransfer");

Backbone.sync = Backbone.ajaxSync;

$(function () {
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
	
	if ($('#magzine_form').length > 0) {
		var magForm = $('#magzine_form');
		magForm.fileupload({
			// maxFilesize, minFileSize
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
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
	}
	
	
	if ($('#article_pages').length) {
		$('#article_pages').fileupload();
		
		var articleId = $('#article_id').text();
		var article = new Article({ id:articleId });
		var articleView = new ArticleView({ model:article, el:$('#article_pages') });
		article.fetch();
		articleView.initPages();
		
		$('#save_pages').click( function (e) {
			article.save();
		});
	}
	
	
	/*$('#selenable').change(function () {
		if ($(this).attr('checked')) {
			$(pageCanvas.el).selectable({disabled:false});
		} else {
			$(pageCanvas.el).selectable({disabled:true});
		}
	});*/
	$('#flushall').click(function () {
		localStorage.clear();
	});
	
	$('#undo').click(function () {
		undomanager.undo();
	});
	$('#redo').click(function () {
		undomanager.redo();
	});
	
	$(".alert-message").alert();
	
	$('.taggable').tagit({
		allowSpaces: true,
		caseSensitive: false,
		//fieldName: "tags",
		//tagSource: function
		availableTags: ['sexy', 'girl']
	});
	
	//Backbone.sync = Backbone.ajaxSync;
	//window.editarea = new EditArea(new Articles);
	
	// editarea.render();
	
	// Backbone.emulateJSON = true
	
	// @todo which request is the last one, observe it!
	var issue, issueView;
	if ($('#issue_editor .articles').length) {
		issue = new Issue({ id:$('#issue_id').text() });
		issueView = new IssueView({ model:issue, el:$('#issue_editor .articles') } );
		
		//issue.fetch();
	}
	
	$('#flushbtn').click(function(e){
		e.stopPropagation();
		e.preventDefault();
		
		$.ajax({
			url: $(this).attr('href')
		});
	});
	
	$('#loadremote').click(function () {
		issue.fetch();
	});
	
	$('#saveremote').click(function () {
		console.log(issue.getNbTasks());
		$('#saveAlert').modal({show:true, backdrop:true});
		$.when(issue.save()).done(function () {
			$('#saveremote').button('reset');
			$('#saveAlert').modal('hide');
		}).fail(function () {
			$('#saveremote').button('reset');
			$('#saveAlert').find('.modal-body p').text('出现错误');
		});
		//editarea.uploadImages();
	});
});