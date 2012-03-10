var articles_layout = function () {
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
		},
		update: function(e, ui) {
			// update indexes
			$(this).find('.article').each(function (index, li) {
				$(li).find('.article-index').text(index + 1);
			});
			
			var articleId = $(ui.item).attr('rel');
			var prevArticleId = $(ui.item).prev().attr('rel');
			if (prevArticleId) {
				$('#tr'+articleId).insertAfter($('#tr'+prevArticleId));
			} else {
				$('#tr'+articleId).prependTo($('#tr'+articleId).parent());
			}
		}
	});
	
	$('#articles_layout').find('.pages').sortable({
		axis: 'y',
		opacity: 0.6,
		containment: 'parent',
		helper: 'clone',
		tolerance: 'pointer'
	});
	
	$('li.page').each(function(index, lipage){
		lipage = $(lipage);
		
		var pageId = lipage.attr('rel');
		lipage.fileupload({
			url: Routing.generate('page_change_thumbnail', { id:pageId }),
			paramName: 'file',
			acceptFileTypes: /(\.|\/)(jpg|jpeg|png)$/i,
			dropZone: lipage,
			limitMultiFileUploads: 1,
			success: function (result) {
				$('#articles_layout').overlay('hide');
			},
			fail: function () {
				$('#articles_layout').overlay('hide');
				alert('上传失败');
			}
		}).bind('fileuploaddrop', function (e, data) {
			var imgFile = data.files[0];
			var acceptFileTypes = $(this).fileupload('option', 'acceptFileTypes');
			if (!(acceptFileTypes.test(imgFile.type) ||
		          acceptFileTypes.test(imgFile.name))) {
				alert('请上传JPG或者PNG格式的图片');
		        return false;
		    }
			
            var reader = new FileReader();
            reader.onload = function (e) {
            	lipage.find('img').attr('src', e.target.result).css({ width:128, height:96 });
            };
            reader.readAsDataURL(imgFile);
			
            $('#articles_layout').overlay('loading');
			return true;
		});
	});
	
	$('#layout_save').click( function () {
		if (!$('#articles_layout').is(':visible')) {
			return false;
		}
		
		//var contentBox = $(this).closest('.content-box').find('.content-box-content');
		$('#articles_layout').overlay('loading');
		var articles = {};
		var articleIds = []; // articles cannot maintain order(except firefox...)
		$('#articles_layout li.article').each(function(index, liarticle){
			var pageIds = [];
			$(liarticle).find('li.page').each(function(i, lipage){
				pageIds.push($(lipage).attr('rel'));
			});
			var articleId = $(liarticle).attr('rel');
			articles[articleId] = pageIds;
			articleIds.push(articleId);
		});
		
		$.ajax({
			type: 'POST',
			url: $(this).attr('href'),
			data: {
				articles: articles,
				articleIds: articleIds
			}
		}).always(function(){
			$('#articles_layout').overlay('hide');
		});
		
		return false;
	});
};