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
			_.each(obj, function (page) {
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
	template: '<h5>文章 {{title}}</h5><ol class="pages"></ol><span class="footer">{{pages}}</span>',
	tagName: 'li',
	className: 'article',
    events: {
        //"click": ""
		'dragenter': 'dragEnter',
		'dragexit': 'dragExit',
		'drop': 'drop',
		'mouseover': 'mouseOver',
		'mouseout': 'mouseOut'
    },
    initialize: function () {
    	var pages = this.model.get('pages');
    	
    	pages.bind('add', this.addPage, this);
    	pages.bind('reset', this.addPages, this);
    	pages.bind('all', this.render, this);
    	
    	this.model.bind('change:index', this.render, this);
    	
    	this.el = $(this.el);
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
    dragEnter: function (e) {
		e.stopPropagation();
		e.preventDefault();
		//var hoverClass = $(this).droppable('option', 'hoverClass');
		this.el.addClass('highlighted');
    },
    dragExit: function (e) {
    	this.el.removeClass('highlighted');
    },
    drop: function (e, ui) {
		e.stopPropagation();
		e.preventDefault();
		
		this.el.switchClass('highlighted', 'very-highlighted', 'fast').removeClass('very-highlighted', 'fast');
		
		// ui is from droppable's drop
		if (ui == undefined) {
			var files = e.dataTransfer.files;
			var count = files.length;
			for (var i = 0; i < count; ++i) {
				this.model.add(new Page({'file':files[i]}));
			};
		} else {
			// @todo delete amd merge
		}
		// @todo create a page and put it into the article
	},
    addPage: function (page) {
    	var pv = new PageView({model:page});
    	this.el.find('.pages').append(pv.el);
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