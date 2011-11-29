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
		this.el = $('#editarea');
		
		this.articles = articles;
		
		articles.bind('add', this.addOne, this);
		articles.bind('remove', this.removeOne, this);
		articles.bind('reset', this.reset, this);
		
		// @todo remove, update index
		// articles.fetch();
		
		articles.add(new Article);
		//articles.add(new Article);
		//articles.add(new Article);
		
		
		// @todo if editarea is empty, then sortable will misbehave
		//this.articles.create();
		
		/* HTML5 file DnD */
		window.editarea = this.el;
		this.el.fileupload().bind('fileuploaddrop', function (e, data) {
			var files = data.files;
			var count = files.length;
			for (var i = 0; i < count; ++i) {
				var article = new Article;
				var p = new Page;
				p.file = files[i];
				article.add(p);
				articles.add(article);
			}
			
			// $('#modal-from-dom').modal({backdrop:true, show:true});
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
				// collapse if need
			},
			stop: function (e, ui) {
				// ui.item
				// restore article's expanded state
			},
			update: _.bind(function (e, ui) {
				this.updateIndex();
			}, this)
		});
	},
	uploadImages: function () {
		var allPages = [];
		var allFiles = [];
		this.articles.each(function (article) {
			article.uploadImages();
		});
	},
	updateIndex: function () {
		var atlis = this.el.find('.article');
		var count = atlis.length;
		for (var i = 0, index = 0; i < count; ++i) {
			var atli = $(atlis[i]);
			var cid = atli.data('cid');
			if (cid == undefined) { // might be placeholder
				continue;
			}
			
			var article = this.articles.getByCid(cid);
			article.setIndex(index);
			
			++index;
		}
	},
	addOne: function (article) {
		this.el = $(this.el);
		
		if (article.index < 0) {
			article.setIndex(this.articles.length - 1);
		}
		
	    var at = new ArticleView({model:article});
	    var atel = $(at.render().el);			
		this.el.append(atel);
		/*
		var articleholder = $('<li class="article-placeholder"><ol class="pages"></ol></li>');
		this.el.append(articleholder)
		articleholder.find('.pages').sortable({
    		distance: 3,
    		containment: $('#editarea'),
    		connectWith:'ol.pages',
    		axis: 'y',
    		tolerance: 'pointer'
		});*/
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
	// on fetch
	reset: function () {
		$(this.el).empty();
		
		var count = this.articles.length;
		for (var i = 0; i < count; ++i) {
			var article = this.articles.at(i);
			article.index = i;
			this.addOne(article);
		}
	},
	render: function () {
		// @todo render all articles, like update
	},
	// now just save articles
	save: function () {
		var articles = this.articles;
		if (articles) {
			articles.each(function (article) {
				article.save();
			});
		}
	}
});

Backbone.sync = Backbone.ajaxSync;

$(function () {
	$('#addpage').click(function () {
		pages.create({index:5});
	});
	// window.pageCanvas = new PageCanvas;
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
	window.editarea = new EditArea(new Articles);
	// editarea.render();
	
	// Backbone.emulateJSON = true
	$('#saveremote').click(function () {
		editarea.articles.fetch();
		$.ajaxQueue({
			url: 'http://www.baidu.com'
		});
		editarea.save();
		$.ajaxQueue({
			url: 'http://www.baidu.com'
		});
		editarea.save();
		//editarea.uploadImages();
	});
});