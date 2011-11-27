/**
 * Article
 * depends on cluster of Page classes
 */

var Article = Backbone.Model.extend({
	url: '/Magend/web/app_dev.php/article/new',
	defaults: {
		index: 0,
		cover: 'http://placehold.it/128x96', // or thumbnail in navigation
		pages: null // can't new Pages here, it'll be shared by all articles
	},
	initialize: function () {
		var pages = this.get('pages');
		if (pages == null) {
			pages = new Pages;
			this.set({'pages':pages});
		}
	},
	// can add Page, Pages, HTML5's files
	add: function (obj) {
		var pages = this.get('pages');		
		if (obj instanceof Page) {
			pages.add(obj);
		} else if (obj instanceof Pages) {
			obj.each(function (page) {
				pages.add(page);
			});
			delete obj;
		}
		
		// add pages finally
	},
	saveToRemote: function (options) {
		var pages = this.get('pages');
		if (pages) {
			pages.saveToRemote();
		}
	}
});

var Articles = Backbone.Collection.extend({
	model: Article,
	localStorage: new Store('articles'),
	saveToRemote: function () {
		_.each(this.models, function (article) {
			article.saveToRemote();
		});
	}
});

var ArticleView = Backbone.View.extend({
	template: '<h5>文章 {{title}}</h5><div class="cover"></div><ol class="pages"></ol><span class="footer" title="页数">{{pages}}</span>',
	tagName: 'li',
	className: 'article',
    events: {
        //"click": ""
		'dragenter': 'dragEnter',
		'dragexit': 'dragExit',
		'drop': 'drop',
		'mouseover': 'mouseOver',
		'mouseout': 'mouseOut',
		'dblclick': 'dblclick'
    },
    initialize: function () {
    	var pages = this.model.get('pages');
    	
    	pages.bind('add', this.addPage, this);
    	pages.bind('remove', this.removePage, this);
    	pages.bind('reset', this.addPages, this);
    	pages.bind('all', this.render, this);
    	
    	this.model.bind('change:index', this.render, this);
    	
    	this.el = $(this.el);
    	this.el.data('cid', this.model.cid);
    	
    	// this.model.view = this;
    	
    	// droppable not work well as placeholder will make position calculated wrong
    	/*this.el.droppable({
    		accept: '.page, .article',
    		activeClass: '',
    		hoverClass: 'highlighted',
    		over: function (e, ui) {
    			if (e.originalEvent.pageX > $(this).offset().left && e.originalEvent.pageX < $(this).offset().left + $(this).width()) {
    				//$(this).addClass('highlighted');
    			}
    		}
    	});*/
    },
    expand: function () {
    	if (window.expandedArticleView) {
    		window.expandedArticleView.collapse();
    	}
    	
    	this.el.addClass('expanded');
    	window.expandedArticleView = this;
    	
    	var pagelis = this.el.find('.pages li').addClass('editing-page');
    	this.el.after(pagelis);    	
    },
    collapse: function () {
    	if (!this.el.hasClass('expanded')) {
    		// already collapsed
    		return;
    	}
    	
    	this.el.removeClass('expanded');
    	window.expandedArticleView = null;
    	
    	var editingPages = this.el.siblings('.editing-page');
    	this.el.find('.pages').append(editingPages);
    	editingPages.removeClass('editing-page');
    },
    dblclick: function (e) {
    	if (this.el.hasClass('expanded')) {
    		this.collapse();
    	} else {
    		this.expand();
    	}
    },
    mouseOver: function (e) {
    	// return;
    	
		var firstpage = this.el.find('.page:first');
		var cover = firstpage.find('img');
		cover.attr('src', firstpage.data('img'));
	},
	mouseOut: function (e) {
		// return;
		
		var firstpage = this.el.find('.page:first');
		var cover = firstpage.find('img');
		cover.attr('src', 'http://placehold.it/128x96');	
	},
    dragEnter: function (e, sorte) {
		e.stopPropagation();
		e.preventDefault();
		
		if (sorte) {
			var dragging = sorte.dragging;
			// no page drop to its article
			if (dragging && $(dragging).is('.editing-page') && this.el.hasClass('expanded')) {
				return;
			} 
		}
		
		//var hoverClass = $(this).droppable('option', 'hoverClass');
		this.el.addClass('highlighted');
    },
    dragExit: function (e) {
    	this.el.removeClass('highlighted');
    },
    drop: function (e, sorte) {
		e.stopPropagation();
		e.preventDefault();
		
		// ui is from droppable's drop
		if (sorte == undefined) {
			var files = e.dataTransfer.files;
			var count = files.length;
			for (var i = 0; i < count; ++i) {
				this.model.add(new Page({'file':files[i]}));
			};
		} else {
			// @todo delete amd merge
			
			var dropping = sorte.dropping;
			// no page drop to its own article
			if (dropping && $(dropping).is('.editing-page') && this.el.hasClass('expanded')) {
				return;
			}
			
			var cid = dropping.data('cid');
			if ($(dropping).is('.editing-page')) {
				var pages = this.model.get('pages');
				var expandedArticle = window.expandedArticleView.model;
				var fromPages = expandedArticle.get('pages');
				var droppingPage = fromPages.getByCid(cid);
				pages.add(droppingPage);
				fromPages.remove(droppingPage);
			} else {
				var arts = this.model.collection;
				var drArticle = arts.getByCid(cid);
				this.model.add(drArticle.get('pages'));
				arts.remove(drArticle);
			}
			
			// dropping.remove();
		}
		
		this.el.switchClass('highlighted', 'very-highlighted', 'fast').removeClass('very-highlighted', 'fast');
		
		// @todo create a page and put it into the article
	},
	// @todo specify index that the page will be added to
    addPage: function (page) {
    	var pv = new PageView({model:page}); 
    	this.el.find('.pages').append(pv.el);
    },
    removePage: function (page) {
    	var pagelis = this.el.hasClass('expanded')
    				? this.el.siblings('.editing-page')
    				: this.el.find('.pages li.page');
    	
    	var pageli = null;
    	for (var i = 0, c = pagelis.length; i < c; ++i) {
    		pageli = $(pagelis[i]);
    		if (pageli.data('cid') == page.cid) {
    			break;
    		}    		
    	}
    	pageli.remove();
    },
    addPages: function (pages) {
    	_.each(pages, function (page) {
    		this.addPage(page);
    	});
    },
    render: function () {
    	// number of pages, index and cover
    	// get page list, set html and restore page list will have data lost
    	var index = this.model.get('index');
    	var pages = this.model.get('pages');
    	if (this.el.html() != '') {
    		var footer = this.el.find('.footer');
    		footer.text(pages.length);
    		var header = this.el.find('h5');
    		header.text('文章 ' + index);
    	} else {
        	var html = $.mustache(this.template, {title:index, pages:1});
        	this.el.html(html);    		
    	}    	
        return this;
    }
});