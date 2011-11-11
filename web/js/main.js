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
    initialize: function() {
    	this.model.bind('change:index', this.render, this);
    },
    render: function() {
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

/**
 * Hot
 */
// @todo extend Hot for link, video, gallery, etc
var Hot = Backbone.Model.extend({
	minWidth: 10,
	minHeight: 10,
	defaults: {
		index: 0,
		stackIndex: 1,
		x: 0,
		y: 0,
		width: 40,
		height: 40,
		selected: false
	},
	select: function() {
		this.set({selected:true});
	},
	deselect: function() {
		this.set({selected:false});
	},
	validate: function(attrs) {
		if (attrs) {
			if (attrs.width < this.minWidth || attrs.height < this.minHeight) {
				return 'minsize is limited';
			}
			if (attrs.x < 0 || attrs.x > 1024 || attrs.y < 0 || attrs.y > 768) {
				return 'no beyond border';
			}
		}
	}
});

var Hots = Backbone.Collection.extend({
	model: Hot,
	localStorage: new Store('hots')
});

var HotView = Backbone.View.extend({
	tagName: 'li',
    events: {
      "mousedown": "onMousedown",
      "dblclick": "edit"
    },
    initialize: function() {
    	this.model.bind('change:width', this.resize, this);
    	this.model.bind('change:height', this.resize, this);
    	this.model.bind('change:x', this.pos, this);
    	this.model.bind('change:y', this.pos, this);
    	this.model.bind('change:selected', this.toggle, this);
    	this.model.bind('destroy', this.remove, this);
    	
    	var hotel = $(this.el);
    	hotel.addClass('hot');
	    hotel.css({
	    	position: 'absolute', // important for chrome(maybe some other browsers as well)
	    	left: this.model.get('x'),
	    	top: this.model.get('y'),
	    	width: this.model.get('width'),
	    	height: this.model.get('height')
	    });
	    hotel.data('cid', this.model.cid);
	    hotel.draggable({
			// snap: true,
			containment: 'parent',
			stop: function() {
    			this.model.set({
    				x: $(this.el).position().left,
    				y: $(this.el).position().top
    			});
    			this.model.save();
			}.bind(this)
		}).resizable({
			minWidth: this.model.minWidth | 10,
			minHeight: this.model.minHeight | 10,
			containment: 'parent',
			handles: 'n, e, s, w, ne, se, sw, nw',
			stop: function() {
				this.model.set({
					width: $(this.el).width(),
					height: $(this.el).height()
				});
				this.model.save();
			}.bind(this)
		});
    },
    onMousedown: function(e) {
		//e.stopPropagation();
		
		// @todo ctrl/cmd+click
    	if (!this.model.get('selected')) {
    		this.model.select();
    		// @todo deselect all others
    	} else {
    		this.model.deselect();
    	}
    	this.model.save();
    },
    toggle: function(){
    	if (this.model.get('selected')) {
    		$(this.el).css({backgroundColor: "rgba(0, 125, 255, 0.5)"});
    	} else {
    		$(this.el).css({backgroundColor: "rgba(255, 255, 255, 0.5)"});
    	}
    },
    edit: function() {
    	$('#hot_dialog').dialog({show:'fade'});
    },
    resize: function(){
    	$(this.el).css({
    		width: this.model.get('width'),
    		height: this.model.get('height')
    	});
    },
    pos: function(){
    	$(this.el).css({
    		left: this.model.get('x'),
    		top: this.model.get('y')
    	});
    },
    remove: function(e) {
    	$(this.el).fadeOut('fast', function() {
    		$(this).remove(); // use detach to support undo/redo etc
    	});
    },
    render: function() {
        return this;
    }
});

var PageCanvas = Backbone.View.extend({
	events: {
		"mousedown": "mousedown",
		"mousemove": "mousemove",
		"mouseup": "mouseup"
	},
    initialize: function() {
		$(document).keypress(this.onKeypress);
		this.el = $('#page_canvas');
		
		// canvas img is focusable not canvas itself, for drag and resize handlers might go out of outline
		var canvasImgEl = $('<div id="page_canvas_img" tabIndex="1"></div');
		canvasImgEl.insertBefore(this.el);
		canvasImgEl.css({
			position: 'absolute',
			// outline: '1px solid red',
			width: this.el.width(),
			height: this.el.height(),
			backgroundImage: 'url(../../images/page.jpg)'
		});
		canvasImgEl.keydown(function(e){
			if ($(e.target).is('input') || $(e.target).is('textarea')) {
				// do
				return;
			}
			if (e.which == 8 || e.which == 46) {
				var delHots = [];
				this.hots.each(function(hot){
					if (hot.get('selected')) {
						delHots.push(hot);
					}
				});
				while (delHots.length) {
					var hot = delHots.pop();
					hot.destroy();
				}
			}
			e.stopPropagation();
			return false;
		}.bind(this));
		this.canvasImgEl = canvasImgEl;
		
		this.delegateEvents(); // need be called after el is ready
		
		this.hots = new Hots;
		this.hots.bind('add', this.addOne, this);
		this.hots.bind('reset', this.addAll, this);
		
		this.hots.fetch();
		this.hots.each(function(hot){
			hot.deselect();
		});
	},
	addOne: function(hot) {
	    var hv = new HotView({model:hot});
	    var hotel = $(hv.render().el);
		
	    $(this.el).append(hotel);
	},
	addAll: function() {		
		this.hots.each(this.addOne, this);
	},
	mousedown: function(e){		
		var multi = e.metaKey || e.ctrlKey;
		if (!multi) {
			var onHot = null;
			if ($(e.target).is('.hot')) {
				onHot = $(e.target);
			}
			this.hots.each(function(hot){
				if (!onHot || hot.cid != onHot.data('cid')) {
					hot.deselect();
				}
			});
		}
		
		this._beginDraw(e);
	},
	mousemove: function(e){
		this._draw(e);
	},
	mouseup: function(e){
		this._endDraw(e);
	},
	_beginDraw: function(e) {
		//console.log(e.metaKey);
		
		if (e.target.id == $(this.el).attr('id')) {
			this.began = true;
			this.startX = e.pageX;
			this.startY = e.pageY;
		}
	},
	_draw: function(e) {
		if (this.began) {
			if (Math.abs(e.pageX - this.startX) < 10 || Math.abs(e.pageY - this.startY) < 10) {
				return;
			}
			
			this.began = false;
			
			var attrs = {
				x: this.startX - $(this.el).offset().left,
				y: this.startY - $(this.el).offset().top,
				width: 10,
				height: 10
			};
			
			this.ns = e.pageY > this.startY ? 's' : 'n';
			this.ew = e.pageX > this.startX ? 'e' : 'w';
			if (this.ns == 'n') {
				attrs.y -= 10;
			}
			if (this.ew == 'w') {
				attrs.x -= 10;
			}
			
			this.hot = this.hots.create(attrs);
			this.hot.select();
		} else if (this.hot) {
			var attrs = {
				x: this.hot.get('x'),
				y: this.hot.get('y')
			};
			var ns = e.pageY > this.startY ? 's' : 'n';
			var ew = e.pageX > this.startX ? 'e' : 'w';
			// cannot drag over the original direction
			if (ew == this.ew) attrs.width = Math.abs(e.pageX - this.startX);
			if (ns == this.ns) attrs.height = Math.abs(e.pageY - this.startY);
			if (this.ns == 'n') {
				attrs.y -= attrs.height - this.hot.get('height');
			}
			if (this.ew == 'w') {
				attrs.x -= attrs.width - this.hot.get('width');
			}
			this.hot.set(attrs);
		}
	},
	_endDraw: function(e) {
		this.began = false;
		this.canvasImgEl.focus();
		if (this.hot) {
			this.hot.save();
			this.hot = null;
		}
	},
	render: function() {
		return this;
	}
});

$(function() {
	var pages = new Pages;
	var pagesView = new PagesView(pages);
	pagesView.render();
	
	/*
	pages.create({index:1});
	pages.create({index:2});
	pages.create({index:3});
	pages.create({index:4});
	*/

	$('#addpage').click(function() {
		pages.create({index:5});
	});
	window.pageCanvas = new PageCanvas;
	/*$('#selenable').change(function() {
		if ($(this).attr('checked')) {
			$(pageCanvas.el).selectable({disabled:false});
		} else {
			$(pageCanvas.el).selectable({disabled:true});
		}
	});*/
	$('#flushall').click(function(){
		localStorage.clear();
	});
});