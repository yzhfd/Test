jQuery.event.props.push("dataTransfer");


// @todo Add a 'new' mark!
// @todo Prompt not stored on server

/**
 * EditArea
 * 
 * Articles will be created by drag & drop image files here
 */
var EditArea = Backbone.View.extend({
	initialize: function (articles) {
		this.articles = articles;
		
		articles.bind('add', this.addOne, this);
		articles.bind('remove', this.removeOne, this);
		articles.bind('reset', this.addAll, this);
		
		// @todo remove, update index
		// articles.fetch();
		
		this.el = $('#editarea');
		
		
		
		// area to show pages of one article
		$('#article-pages .pages').sortable({
			//connectWith: this.el
			containment: $('#article-pages')
		});
		
		
		
		// @todo if editarea is empty, then sortable will misbehave
		this.articles.add(new Article);
		
		/* HTML5 file DnD */
		window.editarea = this.el;
		this.el.fileupload().bind('fileuploadadd', function (e, data) {
			var files = data.files;
			var count = files.length;
			for (var i = 0; i < count; ++i) {
				var article = articles.create();
				article.add(new Page({'file':files[i]}));
			}
		}).bind('fileuploadsubmit', function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		});
		
		this.el.sortable({
			opacity: 0.6,
			axis: 'y',
			helper: 'clone',
			containment: '#editarea',
			//appendTo: 'body',
			tolerance: 'pointer',
			start: function (event, ui) {
				var cid = $(ui.item).data('cid');
				var article = articles.getByCid(cid);
			},
			stop: function (e, ui) {
				// ui.item
				// restore article's expanded state
				console.log('stop');
			},
			update: function (e, ui) {
				// update index
			}
		});
	},
	updateIndex: function () {
		var atlis = this.el.find('.article');
		var count = atlis.length;
		for (var i = 0, index = 0; i < count; ++i) {
			var atli = $(atlis[i]);
			var cid = atli.data('cid');
			if (cid == undefined) {
				continue;
			}
			
			++index;
			var article = this.articles.getByCid(cid);
			article.set({'index':index});
		}
	},
	addOne: function (article) {
	    var at = new ArticleView({model:article});
	    var atel = $(at.render().el);
		var index = article.get('index');
		if (index == 0) {
			index = this.articles.length;
			article.set({'index': index});
			this.el.append(atel);
		} else {
			var pagelis = this.el.find('li.article:not(.ui-sortable-placeholder)');
			if (pagelis.length >= index) {
				$(pagelis[index-1]).before(atel);
			} else {
				this.el.append(atel);
			}
		}
	},
	removeOne: function (article) {
		var cid = article.cid;
		var atels = this.el.find('li.article');
		for (var i = 0, c = atels.length; i < c; ++i) {
			var atel = $(atels[i]);
			if (atel.data('cid') == cid) {
				atel.remove();
				break;
			}
		}
		
		this.updateIndex();
	},
	addAll: function () {		
		this.articles.each(this.addOne, this);
	},
	render: function () {
		// @todo move to initialize but if empty, sortable will be wrong
	},
	saveToRemote: function () {
		this.articles.saveToRemote();
	}
});



$(function () {
	$('#addpage').click(function () {
		pages.create({index:5});
	});
	window.pageCanvas = new PageCanvas;
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
		availableTags: ['sex', 'girl']
	});
	
	window.editarea = new EditArea(new Articles);
	// editarea.render();
	
	$('#saveremote').click(function () {
		console.log(localStorage.getItem('articles').length);
		// editarea.saveToRemote();
	});
});