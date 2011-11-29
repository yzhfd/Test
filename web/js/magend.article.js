/**
 * Article
 * depends on cluster of Page classes
 */

var Article = Backbone.Model.extend({
	url: '/Magend/web/app_dev.php/article',
	index: -1,
	pages: null,
	defaults: {
		issueId: null,
		title: '',
		cover: 'http://placehold.it/128x96' // or thumbnail in navigation
	},
	initialize: function () {
		// make sure there is cid
		if (!this.cid && this.id) {
			this.cid = 'article_' + this.id;
		}
		
		pages = this.get('pages');
		if (pages) {
			this.pages = new Pages(pages);
		} else if (this.pages == null) {
			this.pages = new Pages;
		}
	},
	add: function (page) {
		this.pages.add(page);
	},
	remove: function (page) {	
		this.pages.remove(page);
	},
	setIndex: function (index) {
		this.index = index;
		this.change();
	},
	getPageByCid: function (cid) {
		return this.pages.getByCid(cid);
	},
	save: function (attrs, opts) {
		if (!opts) opts = {};
		var success = opts.success;
		opts.success = function (model, response) {
			if (!model.id) {
				model.id = response.id;
			}
			
			if (this.pages) {
				this.pages.each(function (page) {
					page.save({articleId:model.id});
				});
			}
			
			if (success) success(model, response);
		};
		
		Backbone.Model.prototype.save.call(this, attrs, opts);
	},
	uploadImages: function () {
		// @todo forbid update pages here
		
		this.pages.bind('uploaded', this.imgUploaded, this);
		
		this.uploadingIndex = 0;
		this._uploadImage();
	},
	imgUploaded: function (page) {
		++this.uploadingIndex;
		this._uploadImage();
	},
	_uploadImage: function () {
		var page = this.pages.at(this.uploadingIndex);
		if (page) {
			page.uploadImage();
		}
	}
});

var Articles = Backbone.Collection.extend({
	url: '/Magend/web/app_dev.php/issue/1/articles',
	model: Article,
	localStorage: new Store('articles'),
	comparator: function (article) {
		return article.index;
	}
	/*
	parse: function(response) {
		if (response) {
			_.each (response, function (obj) {
				delete obj.createdAt;
				delete obj.updatedAt;				
			});
		}
		console.log(response);
	}*/
});

var ArticleView = Backbone.View.extend({
	//<div class="cover"></div>
	template: '<h5>{{title}}</h5><ol class="pages"></ol><span class="footer" title="页数">{{pages}}</span>',
	tagName: 'li',
	className: 'article',
    events: {
        //"click": ""
		'dragenter': 'dragEnter',
		'dragexit': 'dragExit',
		'drop': 'drop',
		'dblclick': 'dblclick'
    },
    initialize: function () {
    	var pages = this.model.pages;
    	pages.bind('add', this.addPage, this);
    	pages.bind('remove', this.removePage, this);
    	pages.bind('reset', this.addPages, this);
    	pages.bind('all', this.updateNbPages, this);
    	
    	this.model.bind('all', this.render, this);
    	
    	// this.model.bind('change:index', this.render, this);
    	
    	this.el = $(this.el);
    	this.el.data('cid', this.model.cid);
    	
    	// this.model.view = this;
    },
    expand: function () {
    	this.el.addClass('expanded', 'fast');
    },
    collapse: function () {
    	this.el.removeClass('expanded', 'fast');
    },
    dblclick: function (e) {
    	this.el.toggleClass('expanded', 'fast');
    },
    dragEnter: function (e, sorte) {
		e.stopPropagation();
		e.preventDefault();
		
		this.el.addClass('highlighted');
    },
    dragExit: function (e) {
    	this.el.removeClass('highlighted');
    },
    drop: function (e, sorte) {
		e.stopPropagation();
		e.preventDefault();
		
		if (sorte == undefined) {
			// file drop
			var files = e.dataTransfer.files;
			var count = files.length;
			for (var i = 0; i < count; ++i) {
				var page = new Page;
				page.file = files[i];
				this.model.add(page);
			};
		} // else can be triggered during sort, not implemented yet
		
		this.el.switchClass('highlighted', 'very-highlighted', 'fast')
		       .removeClass('very-highlighted', 'fast');
	},
	// @todo specify index that the page will be added to
	initPages: function () {
		this.addPages(this.model.pages);
	},
	updateNbPages: function () {
		this.el.find('.footer').text(this.model.pages.length);
	},
    addPage: function (page) {
		var pagelis = this.el.find('.' + PageView.prototype.className);
		for (var i = 0, c = pagelis.length; i < c; ++i) {
			if ($(pagelis[i]).data('cid') == page.cid) {
				// already there
				return;
			}
		}
		
    	var pv = new PageView({model:page}); 
    	this.el.find('.pages').append(pv.el);
    },
    removePage: function (page) {
    	var pagelis = this.el.find('.pages li.page');
    	var pageli = null;
    	for (var i = 0, c = pagelis.length; i < c; ++i) {
    		if ($(pagelis[i]).data('cid') == page.cid) {
    			pageli = $(pagelis[i]);
    			break;
    		}    		
    	}
    	
    	if (pageli) {
	    	pageli.remove();
	    	
	    	// the article now has 0/1 page, so collapse it
	    	if (c <= 2 && this.el.hasClass('expanded')) {
	    		this.collapse();
	    	}
    	}
    },
    addPages: function (pages) {
    	pages.each(_.bind(function (page) {
    		this.addPage(page);
    	}, this));
    },
    render: function () {
    	this.el = $(this.el);
    	
    	var pages = this.model.pages;
    	var index = this.model.index + 1;
    	if (this.el.html() != '') {
    		var header = this.el.find('h5');
    		header.text(index);
    		var footer = this.el.find('.footer');
    		footer.text(pages.length);
    	} else {
        	var html = $.mustache(this.template, {title:index, pages:pages.length});
        	this.el.html(html);
        	
        	this.initPages();
        	
        	this.el.find('.pages').sortable({
        		distance: 3,
        		connectWith:'ol.pages',
        		axis: 'y',
        		tolerance: 'pointer',
        		start: _.bind(function (e, ui) {
					var pageli = $(ui.item);
					window.editingPage = this.model.getPageByCid(pageli.data('cid'));
        		}, this),
        		stop: _.bind(function (e, ui) {
        			window.editingPage = null;
        		}, this),
				over: function (e, ui) {
					$(this).parent().addClass('highlighted');
				},
				out:  function (e, ui) {
					$(this).parent().removeClass('highlighted');
				},
				receive: _.bind(function (e, ui) {
					this.el.switchClass('highlighted', 'very-highlighted', 'fast')
						   .removeClass('very-highlighted', 'fast');
				}, this),
				update: _.bind(function (e, ui) {					
					var pageli = $(ui.item);
					var pagelis = this.el.find('li.page');
					
					var index = pagelis.index(pageli);
					var page = this.model.getPageByCid(window.editingPage.cid);
					if (index >= 0) {
						if (!page) {
							this.model.add(window.editingPage);
						}
					} else if (page) {
						this.model.remove(page);
					}
					
					var count =  pagelis.length;
					for (var i = 0; i < count; ++i) {
						var page = this.model.getPageByCid($(pagelis[i]).data('cid'));
						page.index = i;
					}
				}, this)
				// beforeStop to alert user no-page article
        	});
    	}    	
        return this;
    }
});