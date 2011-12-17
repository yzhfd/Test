var issue_new = function () {
	// basic
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
	
	// pages
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
	pages.sortable({});
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
};