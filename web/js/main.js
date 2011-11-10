/**
 * Page
 */
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
    initialize: function(){
    	this.model.bind('change:index', this.render, this);
    },
    render: function(){
    	$(this.el).html('<a href="#1"><img src="../../images/thumb' + this.model.get('img') + '.jpg" /></a><span>' + this.model.get('index') + '</span>');
        return this;
    }
});

var PagesView = Backbone.View.extend({
	el: $('#units'),
    initialize: function(pages) {
		this.pages = pages;
		pages.bind('add', this.addOne, this);
		pages.bind('reset', this.addAll, this);
		
		pages.fetch();
    },
    addOne: function(page){
	    var pv = new PageView({model:page});
	    var pvel = $(pv.render().el);
	    pvel.data('cid', page.cid);
		
	    $(this.el).append(pvel);
    },
    addAll: function(){		
    	this.pages.each(this.addOne, this);
    },
    render: function(){
    	// @todo move to initialize but if empty, sortable will be wrong
	    var pages = this.pages;
		$(this.el).sortable({
			opacity: 0.6,
			start: function(event, ui){
				var cid = $(ui.item).data('cid');
				var page = pages.getByCid(cid);
				//page.set('index', 2);
			},
			stop: function(event, ui){
			    $(this).find('li').each(function(index, li){
					var cid = $(li).data('cid');
					var page = pages.getByCid(cid);
					page.set({index:index+1});
				});
			}
		});
    }
});

/**
 * Hot
 */
// @todo extend Hot for link, video, gallery, etc
var Hot = Backbone.Model.extend({
	defaults: {
		index: 0,
		stackIndex: 1,
		x: 0,
		y: 0,
		width: 80,
		height: 80,
		selected: false
	},
	select: function(){
		this.set({selected:true});
		// deselect all others
	},
	deselect: function(){
		this.set({selected:false});
	}
});

var Hots = Backbone.Collection.extend({
	model: Hot,
	localStorage: new Store('hots')
});

var HotView = Backbone.View.extend({
	tagName: 'li',
    events: {
      "click": "select",
      "dblclick": "edit"
    },
    initialize: function(){
    	//this.model.bind('change:index', this.render, this);
    	
    	var hotel = $(this.el);
    	hotel.addClass('hot');
	    hotel.css({
	    	left: this.model.get('x'),
	    	top: this.model.get('y'),
	    	width: this.model.get('width'),
	    	height: this.model.get('height')
	    });
	    hotel.data('cid', this.model.cid);
	    hotel.draggable({
			// snap: true,
			containment: 'parent',
			stop: function(){
    			this.model.set({
    				x: $(this.el).position().left,
    				y: $(this.el).position().top
    			});
    			this.model.save();
			}.bind(this)
		}).resizable({
			containment: 'parent',
			handles: 'n, e, s, w, ne, se, sw, nw',
			stop: function(){
				this.model.set({
					width: $(this.el).width(),
					height: $(this.el).height()
				});
				this.model.save();
			}.bind(this)
		});
    },
    select: function(e){
		e.stopPropagation();
		$(this.el).css({backgroundColor: "rgba(0, 125, 255, 0.5)", zIndex: 100});
		// @todo ctrl/cmd+click
		this.model.select();
    },
    edit: function(){
    	$('#hot_dialog').dialog({show:'fade'});
    },
    remove: function(e){
    	$(this.el).fadeOut('fast', function(){
    		$(this).remove(); // use detach to support undo/redo etc
    	});
    	this.model.destroy();
    },
    render: function(){
        return this;
    }
});

var PageCanvas = Backbone.View.extend({
	events: {
		"click": "onClick"
	},
    initialize: function() {
		$(document).keypress(this.onKeypress);
		this.el = $('#page_canvas');
		this.delegateEvents(); // need be called after el is ready
		
		this.hots = new Hots;
		this.hots.bind('add', this.addOne, this);
		this.hots.bind('reset', this.addAll, this);
		
		this.hots.fetch();
	},
	addOne: function(hot){
	    var hv = new HotView({model:hot});
	    var hotel = $(hv.render().el);
		
	    $(this.el).append(hotel);
	},
	addAll: function(){		
		this.hots.each(this.addOne, this);
	},
	onClick: function(e){
		var hot = new Hot();
		var x = e.pageX - $(this.el).offset().left;
		var y = e.pageY - $(this.el).offset().top;
		var w = hot.get('width');
		var h = hot.get('height');
		hot.set({
			x: x-w/2,
			y: y-h/2
		});
		this.hots.add(hot);
		hot.save();
	},
	onKeypress: function(e){
		e.stopPropagation();
		console.log(e.which);
		// @todo on canvas or not
		return false;
	},
	render: function(){
		return this;
	}
});

$(function(){
	var pages = new Pages;
	var pagesView = new PagesView(pages);
	pagesView.render();
	
	/*
	pages.create({index:1});
	pages.create({index:2});
	pages.create({index:3});
	pages.create({index:4});
	*/

	$('#addpage').click(function(){
		pages.create({index:5});
	});
	window.pageCanvas = new PageCanvas;
	/*$('#selenable').change(function(){
		if ($(this).attr('checked')){
			$(pageCanvas.el).selectable({disabled:false});
		} else {
			$(pageCanvas.el).selectable({disabled:true});
		}
	});*/
	
	//window.console = $('#info_panel');
	/*$('#page_editor').click(function(e){
		var hotel = $('<li/>', {
			'class': 'hot'
		}).appendTo($(this));
		
		hotel.draggable({
			snap: true,
			containment: 'parent',
			drag: function(){
				//console.text($('#page_editor .hot').position().left);
			}
		}).resizable({
			containment: 'parent',
			handles: 'n, e, s, w, ne, se, sw, nw',
			resize: function(){
				console.text($(this).outerWidth());
			}
		}).click(function(e){
			e.stopPropagation();
			$(this).css({backgroundColor: "rgba(0, 125, 255, 0.5)", zIndex: 100});
		});
		//console.log(e.pageY, $(this).offset().top);
		hotel.css({
			left:e.pageX - $(this).offset().left,
			top:e.pageY - $(this).offset().top
		});
	});*/

});