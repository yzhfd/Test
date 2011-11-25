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
		
	}
});

var Articles = Backbone.Collection.extend({
	model: Article,
	localStorage: new Store('articles')
});

var ArticleView = Backbone.View.extend({
	template: '<h5>{{title}}</h5><ol class="pages"></ol><span class="footer">{{pages}}</span>',
	tagName: 'li',
	className: 'article',
    events: {
        //"click": ""
    },
    initialize: function () {
    	var pages = this.model.get('pages');
    	
    	pages.bind('add', this.addPage, this);
    	pages.bind('reset', this.addPages, this);
    	
    	this.model.bind('change:index', this.render, this);
    	
    	this.el = $(this.el);
    	this.el.droppable({
    		accept: '.page, .article',
    		activeClass: '',
    		hoverClass: 'highlighted',
    		over: function (e, ui) {
    			//console.log($(this).hasClass('ui-sortable-placeholder'));
    		}
    	});
    	this.el.bind('dragenter', function (e) {
    		e.stopPropagation();
    		e.preventDefault();
    		var hoverClass = $(this).droppable('option', 'hoverClass');
    		$(this).addClass(hoverClass);
    	}).bind('dragexit', function (e) {
    		var hoverClass = $(this).droppable('option', 'hoverClass');
    		$(this).removeClass(hoverClass);
    	});
    	// droppable's or html5 file's
    	this.el.bind('drop', _.bind(function (e, ui) {
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
    			
    		}
    		// @todo create a page and put it into the article
    	}, this));
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
    drop: function (e) {
    	console.log(e);
    },
    render: function () {
    	// number of pages, index and cover
    	var html = $.mustache(this.template, {title:'文章', pages:10});
    	$(this.el).html(html);
    	
        return this;
    }
});