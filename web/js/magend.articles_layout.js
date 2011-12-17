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
		var contentBox = $(this).closest('.content-box').find('.content-box-content');
		contentBox.overlay();
		
		
	});
};