/**
 * Article
 * Article is composed of multiple pages
 */
var Article = Backbone.Model.extend({
	defaults: {
		index: 0,
		cover: ''
	}
});

var Page = Backbone.Model.extend({
	defaults: {
		index: 0,
		img: 1
	}
});

var Pages = Backbone.Collection.extend({
	model: Page,
	localStorage: new Store('pages')
});

var PageView = Backbone.View.extend({
	tagName: 'li',
    events: {
      //"click": ""
    },
    initialize: function() {
    	this.model.bind('change:index', this.render, this);
    },
    render: function() {
    	$(this.el).html('<a href="#1"><img src="../../images/thumb' + this.model.get('img') + '.jpg" /></a><span>' + this.model.get('index') + '</span>');
        return this;
    }
});

var PagesView = Backbone.View.extend({
    initialize: function(pages) {
		this.el = $('#units');
		
		this.pages = pages;
		pages.bind('add', this.addOne, this);
		pages.bind('reset', this.addAll, this);
		
		pages.fetch();
    },
    addOne: function(page) {
	    var pv = new PageView({model:page});
	    var pvel = $(pv.render().el);
	    pvel.data('cid', page.cid);
		
	    $(this.el).append(pvel);
    },
    addAll: function() {		
    	this.pages.each(this.addOne, this);
    },
    render: function() {
    	// @todo move to initialize but if empty, sortable will be wrong
	    var pages = this.pages;
		$(this.el).sortable({
			opacity: 0.6,
			start: function(event, ui) {
				var cid = $(ui.item).data('cid');
				var page = pages.getByCid(cid);
				//page.set('index', 2);
			},
			stop: function(event, ui) {
			    $(this).find('li').each(function(index, li) {
					var cid = $(li).data('cid');
					var page = pages.getByCid(cid);
					page.set({index:index+1});
				});
			}
		});
    }
});