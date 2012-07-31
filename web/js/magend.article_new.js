var article_new = function () {
	// basic
	$('.pills a:not(#newBasicTab)').click(function (e) {
		var articleId = $('#newPagesTab').attr('rel');
		if (articleId) {
			return true;
		} else {
			alert('请先提交基本信息创建文章');
			return false;
		}
	});
	
	$('#magsel').change(function(){
		var opt = $(this).find('option:selected');
		$('#issuesel').load(opt.val());
	});
	if ($('#issuesel').find('option').length == 0) {
		$('#magsel').change();
	}
	
	$('#architectsel, #keywordsel').change(function(){
		var tag = $(this).find('option:selected').text();
		// make sure html structured
		var tagit = $(this).closest('div').find('.taggable');
		tagit.tagit('createTag', tag);
	});
	
	// attachment
	$('#attachedAudio').click(function(){
		if ($(this).attr('href') == '#') {
			return false;
		}
	});
	$('#attachAudio').fileupload({
		url: Routing.generate('article_audioUpload'),
		paramName: 'file',
		acceptFileTypes: /(\.|\/)(mp3|wav)$/i,
		dropZone: $('#attachAudio'),
		limitMultiFileUploads: 1
	}).bind('fileuploaddrop', function (e, data) {
		var audioFile = data.files[0];
		var acceptFileTypes = $(this).fileupload('option', 'acceptFileTypes');
		if (!(acceptFileTypes.test(audioFile.type) ||
              acceptFileTypes.test(audioFile.name))) {
			alert('请上传MP3格式的音频文件');
			for (var i=0; i<100; ++i) {}
            return false;
        }
		
		$('#attachAudio').text(audioFile.name);
		$('#article_form').data('audio', audioFile);
		/*$('#attachAudio').overlay('loading');
		
		return true;*/
	}).bind('fileuploadsubmit', function (e, data) {
		// no upload immediately
		e.stopPropagation();
		e.preventDefault();
	});
	
	$('#article_form').submit(function(e){
		var articleId = $('#newPagesTab').attr('rel');
		var submitBtn = $(this).find(':submit');
		submitBtn.attr('data-loading-text', '提交中...');
		submitBtn.button('loading');
		
		var existentArticleId = $('#newPagesTab').attr('rel');
		var audioFile = $(this).data('audio');
		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			data: $(this).serializeArray(),
			success: function (response) {
				var articleId = response;
				$('#newPagesTab, #attachmentsTab').attr('rel', articleId);
				
				if (!audioFile) {
					// @todo DRY
					submitBtn.button('reset');
					if (!existentArticleId && confirm('前往上传页面')) {
						$('#newPagesTab').click();
					}
					return;
				}
				
				var audioFormData = {id:articleId};
				$('#attachAudio').fileupload('option', 'formData', audioFormData)
							     .fileupload('send', { files:[audioFile] })
								 .success(function (result, textStatus, jqXHR) {
										$('#attachAudio').text('拖拽音频文件到这里');
										$('#attachAudio').overlay('hide');
										$('#attachedAudio').attr('href', result.audio).text(result.name);
										
										submitBtn.button('reset');
										if (!existentArticleId && confirm('前往上传页面')) {
											$('#newPagesTab').click();
										}
								 })
								 .error(function (result, textStatus, jqXHR) {
									 console.log(textStatus);
									 $('#attachAudio').text('拖拽音频文件到这里');
									 $('#attachAudio').overlay('hide');
									 alert('上传背景音乐失败');
									 
									 submitBtn.button('reset');
								 });
			}
		});
		
		return false;
	});
	
	$('#article_typesel').change(function(){
		$('.article-uncommon').hide();
		$('.article-type' + $(this).val()).show();
	});
	
	// pages
	$('a.pagedel').live('click', function(e){
		var href = $(this).attr('href');
		if (href != '#') {
			$.get(href);
		}
		$(this).parent().remove();
		return false;
	});
	
	$('.newPages').find('ol.pages').each(function(index, pages){
		pages = $(pages);
		pages.sortable({});
		pages.fileupload({
			url: pages.attr('rel'),
			paramName: 'file',
			dropZone: pages.parent(),
			sequentialUploads: true
		}).bind('fileuploaddrop', function (e, data) {
			var count = data.files.length;
			for (var i = 0; i < count; ++i) {
				(function (file) {
		            var reader = new FileReader();
		            reader.onload = function (e) {
		            	var page = $('<li class="page unsynced"><a href="#" class="pagedel"></a><a href="#" title="'
		            			+ file.name + '"><img width="128" height="96" src="' + e.target.result + '" /></a></li>');
		            	page.appendTo(pages);
		            	page.data('file', file);
		            };
		            
		            reader.readAsDataURL(file);
				})(data.files[i]);
			}
		}).bind('fileuploadsubmit', function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		});
	});

	$('li.page a:not(.pagedel)').live('click', function(){
		return false;
	});
	$('li.page').live('dblclick', function(){
		var pageId = $(this).attr('rel');
		if (pageId) {
			var editUrl = Routing.generate('page_edit', {id:pageId});
			document.location = editUrl;
		}
	});
	
	var savePages = function (pages) {
		var dfd = $.Deferred();
		
		var when = $.when({});
		var lipages = pages.find('li.page');
		var articleId = $('#newPagesTab').attr('rel');
		var formData = articleId ? { articleId:articleId }: null;
		// @todo pass page id for images in other modes
		
		lipages.each(function (index, lipage) {
			lipage = $(lipage);
			var file = lipage.data('file');
			if (!file) return;
			
			when = when.pipe(function(){
				lipage.overlay('loading');
				
				var uploader = $('<div/>');
				return uploader.fileupload({
					paramName: 'file',
					formData: formData,
					url: pages.attr('rel'),
					success: function (result) {
						if (!result.id) {
							// some error
							lipage.addClass('syncfail', 'fast');
							lipage.overlay('hide');
							return;
						}
						
						lipage.overlay('hide').removeClass('unsynced', 'fast');
						lipage.find('img').attr('src', result.page);
						lipage.removeData('file');
						lipage.attr('rel', result.id);
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
	
	// old pageIds
	$('.newPages').find('ol.pages').each(function(index, pages){
		pages = $(pages);
		var pageIds = [];
		pages.find('li.page').each(function(index, lipage){
			var pageId = $(lipage).attr('rel');
			if (pageId) pageIds.push(pageId);
		});
		pages.data('pageIds', pageIds);
	});
	
	$('.submit_pages').click(function(){
		var submitBtn = $(this);
		submitBtn.button('loading');
		var pages = submitBtn.parent().parent().find('ol.pages');
		var pageIds = pages.data('pageIds');
		
		// upload pages then order them (if there is change)
		savePages(pages).pipe(function(){
			var _pageIds = [];
			pages.find('li.page').each(function(index, lipage){
				var pageId = $(lipage).attr('rel');
				if (pageId) _pageIds.push(pageId);
			});
			var strPageIds = _pageIds.join(',');
			if (strPageIds != pageIds.join(',')) {
				var articleId = $('#newPagesTab').attr('rel');
				return $.ajax({
					url: Routing.generate('article_orderpages', {'type':submitBtn.attr('rel')}),
					data: {
						id: articleId,
						pageIds: strPageIds
					}
				});
			}
			return {};
		}).always(function(){
			submitBtn.button('reset');
		});
	});
};