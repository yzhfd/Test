var article_new = function () {
	// basic
	$('.nav-tabs a:not(#newBasicTab)').click(function (e) {
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
	
	$('#article_form').submit(function(e){
		var articleId = $('#newPagesTab').attr('rel');
		var submitBtn = $(this).find(':submit');
		submitBtn.attr('data-loading-text', '提交中...');
		submitBtn.button('loading');
		
		var existentArticleId = $('#newPagesTab').attr('rel');
		$.ajax({
			url: $(this).attr('action'),
			type: 'POST',
			data: $(this).serializeArray(),
			success: function (response) {
				var articleId = response;
				$('#newPagesTab, #attachmentsTab').attr('rel', articleId);
				
				submitBtn.button('reset');
				if (!existentArticleId && confirm('前往上传页面')) {
					$('#newPagesTab').click();
				}
				return;
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
		            	var page = $('<li class="page unsynced"><a href="#" class="pagedel"></a><a class="imgwrapper" href="#" title="'
		            			+ file.name + '"><img alt="' + file.name + '" width="128" height="96" /></a></li>');
		            	page.appendTo(pages);
		            	page.find('img').attr('src', e.target.result);
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
			var editUrl = Routing.generate('page_edit', { id:pageId });
			document.location = editUrl;
		}
	});
	
	var savePages = function (pages) {
		var dfd = $.Deferred();
		
		var when = $.when({});
		var lipages = pages.find('li.page');
		var articleId = $('#newPagesTab').attr('rel');
		var formData = articleId ? { articleId:articleId }: {};
		// @todo pass page id for images in other modes
		
		lipages.each(function (index, lipage) {
			lipage = $(lipage);
			if (lipage.is(':hidden')) return;
			var file = lipage.data('file');
			if (!file) {
				var seq = lipage.attr('seq');
				if (index != seq) {
					when = when.pipe(function(){
						return $.ajax({
							url: Routing.generate('page_seq', { id:lipage.attr('rel'), 'seq':index })
						});
					});
				}
				return;
			}
			
			when = when.pipe(function(){
				lipage.overlay('loading');
				
				var uploader = $('<div/>');
				formData['seq'] = index;
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
						lipage.find('img').attr('src', result.page).show().css({'visibility':'visible'}); // fix chrome
						lipage.removeData('file');
						lipage.attr('rel', result.id);
						lipage.attr('seq', result.seq);
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
			}); // end of when.pipe
		});
		when.done( dfd.resolve ).fail( dfd.reject );
		
		return dfd.promise();
	};
	
	$('.submit_pages').click(function(){
		var submitBtn = $(this);
		submitBtn.button('loading');
		var pages = submitBtn.parent().parent().find('ol.pages');
		savePages(pages).always(function(){
			submitBtn.button('reset');
		});
	});
};