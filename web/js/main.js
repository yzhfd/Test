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
	
	$('.tabs').tabs();
	$('.pills').pills();
	
	if ($('#newPages').length > 0) {
		$('#newPagesTab').click(function (e) {
			var articleId = $('#newPagesTab').attr('rel');
			if (articleId) {
				return true;
			} else {
				alert('请先提交基本信息创建文章');
				return false;
			}
		});
		
		$('#architectsel, #keywordsel').change(function(){
			var tag = $(this).find('option:selected').text();
			// make sure html structured
			var tagit = $(this).closest('div').find('.taggable');
			tagit.tagit('createTag', tag);
		});
		
		$('#article_form').submit(function(e){
			var articleId = $('#newPagesTab').attr('rel');
			console.log(articleId);
			var submitBtn = $(this).find(':submit');
			submitBtn.attr('data-loading-text', '提交中...');
			submitBtn.button('loading');
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: $(this).serializeArray(),
				success: function (response) {
					submitBtn.button('reset');
					$('#newPagesTab').attr('rel', response);
					if (!articleId && confirm('前往上传页面')) {
						$('#newPagesTab').click();
					}
				}
			});
			
			return false;
		});
		
		
		var pageDel = function(e){
			var href = $(this).attr('href');
			if (href != '#') {
				$.get(href);
			}
			$(this).parent().remove();
			return false;
		};
		$('a.pagedel').click(pageDel);
		
		var pages = $('#newPages').find('ol.pages');
		pages.sortable({
			
		});
		pages.fileupload({
			url: pages.attr('rel'),
			paramName: 'file',
			sequentialUploads: true
		}).bind('fileuploaddrop', function (e, data) {
			var count = data.files.length;
			for (var i = 0; i < count; ++i) {
				(function (file) {
		            var reader = new FileReader();
		            reader.onload = function (e) {
		            	var page = $('<li class="page unsynced"><a href="#" class="pagedel"></a><a href="#" title="' + file.name + '"><img width="128" height="96" src="' + e.target.result + '" /></a></li>');
		            	page.appendTo(pages);
		            	page.data('file', file);
		            	page.find('a.pagedel').click(pageDel);
		            };
		            
		            reader.readAsDataURL(file);
				})(data.files[i]);
			}
		}).bind('fileuploadsubmit', function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		});
		
		
		var savePages = function () {
			var dfd = $.Deferred();
			
			var when = $.when({});
			var lipages = pages.find('li.page');
			// pipe will pass arguments!
			var articleId = $('#newPagesTab').attr('rel');
			var formData = articleId ? { articleId:articleId }: null;
			// @todo pass page id for images in other modes
			
			lipages.each(function (index, lipage) {
				lipage = $(lipage);
				var file = lipage.data('file');
				if (!file) return;
				
				when = when.pipe(function(){
					lipage.overlay({ loading:true });
					
					var uploader = $('<div/>');
					return uploader.fileupload({
						paramName: 'file',
						formData: formData,
						url: pages.attr('rel'),
						success: function (result) {
							if (!result.id) {
								return;
							}
							lipage.overlay('hide').removeClass('unsynced', 'fast');
							lipage.find('img').attr('src', result.page);
							lipage.removeData('file');
							lipage.find('.pagedel').attr('href', result.delUrl);
						},
						error: function (result) {
							lipage.addClass('syncfail', 'fast');
							lipage.overlay('hide');
							lipage.removeData('file');
						}
					}).bind('fileuploadsubmit', function (e, data) {
						// no upload immediately
						e.stopPropagation();
						e.preventDefault();
					}).fileupload('send', { files:[file] }); // only send one file
				});
			});
			when.done( dfd.resolve ).fail( dfd.reject );
			
			return dfd.promise();
		};
		
		$('#submit_pages').click(function(){
			$(this).button('loading');
			savePages().always(function(){
				$('#submit_pages').button('reset');
			});
		});
	}
	
	if ($('#articles_layout').length > 0) {
		var articles = $('#articles_layout').find('ol.articles');
		articles.sortable({
			axis: 'x',
			helper: 'clone',
			containment: articles,
			handle: 'h5',
			cursor: 'crosshair',
			tolerance: 'pointer',
			delay: 100,
			start: function (e, ui) {
				$(ui.helper).addClass('highlighted');
			}
		});
		
		$('#articles_layout').find('.pages').sortable({
			axis: 'y',
			opacity: 0.6,
			containment: 'parent',
			helper: 'clone',
			tolerance: 'pointer'
		});
		
		$('#layout_save').click( function () {
			var contentBox = $(this).closest('.content-box').find('.content-box-content');
			contentBox.overlay();
			
			
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