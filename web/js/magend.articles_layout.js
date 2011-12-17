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
	
	$('#layout_save').click( function () {
		if (!$('#articles_layout').is(':visible')) {
			return false;
		}
		
		//var contentBox = $(this).closest('.content-box').find('.content-box-content');
		$('#articles_layout').overlay('loading');
		var articles = {};
		$('#articles_layout li.article').each(function(index, liarticle){
			var pageIds = [];
			$(liarticle).find('li.page').each(function(i, lipage){
				pageIds.push($(lipage).attr('rel'));
			});
			articles[$(liarticle).attr('rel')] = pageIds;
		});
		
		$.ajax({
			type: 'POST',
			url: $(this).attr('href'),
			data: {
				articles: articles
			}
		}).always(function(){
			$('#articles_layout').overlay('hide');
		});
		
		return false;
	});
};