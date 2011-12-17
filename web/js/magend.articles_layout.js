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
		$('#articles_layout').overlay({ loading:true });
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
		});
		
		return false;
	});
};