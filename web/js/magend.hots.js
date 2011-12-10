/**
 * Hot
 * 
 * use UndoManager
 */
// @todo extend Hot for link, video, gallery, etc
var Hot = Backbone.Model.extend({
	minWidth: 10,
	minHeight: 10,
	defaults: {
		// index: 0,
		// stackIndex: 1,
		x: 0,
		y: 0,
		width: 40,
		height: 40
	},
	rendered: false,
	selected: false, // should not be attribute, as it should be persistent
	select: function () {
		this.selected = true;
		this.trigger('select');
	},
	deselect: function () {
		this.selected = false;
		this.trigger('deselect');
	},
	validate: function (attrs) {
		if (attrs) {
			if (attrs.width < this.minWidth || attrs.height < this.minHeight) {
				return 'minsize is limited';
			}
			
			// @todo use a config object to switch these settings
			var wlimit = $('#page_canvas').width();
			var hlimit = $('#page_canvas').height();
			if (attrs.x < 0 || attrs.x > wlimit || attrs.y < 0 || attrs.y > hlimit) {
				return 'no beyond border';
			}
			
			// @todo type and its data or dialog cannot be closed, nor can be published
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
      "mouseup": "onMouseup",
      "dblclick": "edit"
    },
    initialize: function () {
    	this.model.rendered = true;
    	
    	this.model.bind('change:width', this.resize, this);
    	this.model.bind('change:height', this.resize, this);
    	this.model.bind('change:x', this.pos, this);
    	this.model.bind('change:y', this.pos, this);
    	this.model.bind('select', this.toggle, this);
    	this.model.bind('deselect', this.toggle, this);
    	this.model.bind('remove', this.remove, this);
    	// this.model.bind('destroy', this.remove, this); // loop as destroy on remove
    	
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
			drag: function () {
	    		// @todo use a config object to switch these settings
				/*var wlimit = $('#page_canvas').width();
				var hlimit = $('#page_canvas').height();
				var x = $(this).position().left;
				var y = $(this).position().top;
				var w = $(this).width();
				var h = $(this).height();
				if (x < 0 || x + w > wlimit || y < 0 || y + h > hlimit) {
					return false;
				}
				return true;*/
	    	},
			stop: _.bind(function () {
    			this.model.set({
    				x: $(this.el).position().left,
    				y: $(this.el).position().top
    			});
    			this.model.save();
			}, this)
		}).resizable({
			minWidth: this.model.minWidth | 10,
			minHeight: this.model.minHeight | 10,
			containment: 'parent',
			handles: 'n, e, s, w, ne, se, sw, nw',
			// support shift fixed aspectRatio
			start: function (e) {
				if (e.shiftKey) {
					$(this).resizable('option', 'aspectRatio', true);
				}
			},
			stop: _.bind(function () {
				$(this.el).resizable('option', 'aspectRatio', false);
				this.model.set({
					width: $(this.el).width(),
					height: $(this.el).height()
				});
				this.model.save();
			}, this)
		});
    },
    onMousedown: function (e) {
    	this.model.select();
    },
    onMouseup: function (e) {
    	// not support deselect by clicking on selected element
    	// if want to, then need check whether its from drag or resize
    	/*if (!this.model.selected) {
    		this.model.select();
    	} else {
    		this.model.deselect();
    	}*/
    },
    toggle: function () {
    	if (this.model.selected) {
    		$(this.el).css({backgroundColor: "rgba(0, 125, 255, 0.5)"});
    	} else {
    		$(this.el).css({backgroundColor: "rgba(255, 255, 255, 0.5)"});
    	}
    },
    edit: function () {
    	$('#hot_dialog').dialog({show:'fade'});
    },
    resize: function () {
    	$(this.el).css({
    		width: this.model.get('width'),
    		height: this.model.get('height')
    	});
    },
    pos: function () {
    	$(this.el).css({
    		left: this.model.get('x'),
    		top: this.model.get('y')
    	});
    },
    remove: function (e) {
    	// this.model.destroy(); // not really destroyed but garbaged, so can be undone
    	$(this.el).fadeOut('fast', function () {
    		$(this).remove(); // use detach to support undo/redo etc
    	});
    	this.model.rendered = false;
    },
    render: function () {
        return this;
    }
});

var PageCanvas = Backbone.View.extend({
	events: {
		"mousedown": "mousedown",
		"mousemove": "mousemove",
		"mouseup": "mouseup"
	},
    initialize: function () {
		this.el = $('#page_canvas');
		
		$(document).mousemove(_.bind(function (e) {
			if (this.drawing) {
				this.mousemove(e);
			}
		}, this));
		$(document).mouseup(_.bind(function (e) {
			if (this.drawing) {
				this.mouseup(e);
			}
		}, this));
		
		// canvas img is focusable not canvas itself, for drag and resize handlers might go out of outline
		var canvasImgEl = $('#page_canvas_img');
		canvasImgEl.css({
			// outline: '1px solid red',
			width: this.el.width(),
			height: this.el.height()
			//backgroundImage: 'url(../../images/page.jpg)',
		});
		canvasImgEl.find('img').css({
			width: this.el.width(),
			height: this.el.height()
		});
		canvasImgEl.focus(function (e) {
			// if it was just off
			console.log('focus on');
		});
		canvasImgEl.blur(function (e) {
			console.log('focus off');
		});
		canvasImgEl.keydown(_.bind(function (e) {
			if ($(e.target).is('input') || $(e.target).is('textarea')) {
				// do
				return;
			}
			if (e.which == 8 || e.which == 46) {
				var delHots = [];
				this.hots.each(function (hot) {
					if (hot.selected) {
						delHots.push(hot);
					}
				});
				// destroyed in HotView
				this.hots.remove(delHots);
			}
			// @todo if esc, what to do, like cancel the creation of the hot
			e.stopPropagation();
			return false;
		}, this));
		this.canvasImgEl = canvasImgEl;
		
		this.delegateEvents(); // need be called after el is ready
		
		this.hots = new Hots;
		this.hots.bind('add', this.addOne, this);
		this.hots.bind('reset', this.addAll, this);
		// remove is bound in HotView
		
		this.hots.fetch();
		
		window.undomanager = new UndoManager(this.hots);
	},
	addOne: function (hot) {
		if (hot.rendered) {
			return;
		}
		// !important, model might be removed from collection but still stored
		hot.localStorage = this.hots.localStorage;
	    var hv = new HotView({model:hot});
	    var hotel = $(hv.render().el);
		
	    $(this.el).append(hotel);
	},
	addAll: function () {		
		this.hots.each(this.addOne, this);
	},
	mousedown: function (e) {
		var multi = e.metaKey || e.ctrlKey;
		if (!multi) {
			var onHot = null;
			// exclude the hot that mouse is on, as the hot itself will handle it
			if ($(e.target).is('.hot')) {
				onHot = $(e.target);
			} else if ($(e.target).parent().is('.hot')) { // mousedown may be on drag handles
				onHot = $(e.target).parent();
			}
			
			this.hots.each(function (hot) {
				if (!onHot || hot.cid != onHot.data('cid')) {
					hot.deselect();
				}
			});
		}
		
		this._beginDraw(e);
	},
	mousemove: function (e) {
		this._draw(e);
	},
	mouseup: function (e) {
		this.drawing = false;
		this._endDraw(e);
	},
	_beginDraw: function (e) {
		if (e.target.id == $(this.el).attr('id')) {
			this.drawing = true;
			this.began = true;
			this.startX = e.pageX;
			this.startY = e.pageY;
		}
	},
	_draw: function (e) {
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
			
			// not added to hots, as it's not truly created until mouseup
			// this also has great impact on undo/redo,
			// because from add to change attriutes, there are a series of changes
			this.hot = new Hot(attrs);
			this.addOne(this.hot);
			this.hot.select();
		} else if (this.hot) {
			var attrs = {
				x: this.hot.get('x'),
				y: this.hot.get('y')
			};
			var ns = e.pageY > this.startY ? 's' : 'n';
			var ew = e.pageX > this.startX ? 'e' : 'w';
			
			// no out of canvas border
			var mouseX = Math.max(e.pageX, this.el.offset().left);
			mouseX = Math.min(mouseX, this.el.offset().left + this.el.width());
			var mouseY = Math.max(e.pageY, this.el.offset().top);
			mouseY = Math.min(mouseY, this.el.offset().top + this.el.height());
			
			// cannot drag over the original direction
			if (ew == this.ew) attrs.width = Math.abs(mouseX - this.startX);
			if (ns == this.ns) attrs.height = Math.abs(mouseY - this.startY);
			if (this.ns == 'n' && ns == 'n') {
				attrs.y -= attrs.height - this.hot.get('height');
			}
			if (this.ew == 'w' && ew == 'w') {
				attrs.x -= attrs.width - this.hot.get('width');
			}
			
			this.hot.set(attrs);
		}
	},
	_endDraw: function (e) {
		this.began = false;
		this.canvasImgEl.focus();
		if (this.hot) {
			this.hots.add(this.hot);
			this.hot.save();
			this.hot = null;
		}
	},
	render: function () {
		return this;
	}
});