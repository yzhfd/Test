
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
		x: 0,
		y: 0,
		width: 40,
		height: 40,
        locked: 0,   // if element's position is locked or not, persistent
        ratioLocked: 0 // if element's aspect ratio is locked or not (when resizing), persistent
	},
	isEdited: false, // true if confirm on edit dialog
	layoutChanged: false, // true if being moved/resized/deleted
	rendered: false,
	selected: false, // should not be attribute, as it should be persistent
    hasSaved: function () {
        return !(this.isNew() || this.isEdited || this.layoutChanged);
    },
    examineContentImage: function () {
        var dfd  = jQuery.Deferred();
        var self = this;
        var examineImageAtUrl = function (url) {
            if (!url || url.length<=0) {
                dfd.reject();
                return;
            }
            var image = new Image();
            image.onload = function () {
                dfd.resolve(url, image.width, image.height);
            };
            image.onerror = dfd.reject;
            image.src = url;
        };
        if (this.assets && this.assets[0]) {
            var asset = this.assets[0];
            var imgPath;
            if (asset.file) {
                imgPath = basePath + '/uploads/' + asset.file;
            } else {
                imgPath = $(asset).find('.imgwrapper>img').attr('src');    // see edit, dialog section
            }
            examineImageAtUrl(imgPath);
        } else if (this.uploads && this.uploads[0]) {
            var imgFile = this.uploads[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                examineImageAtUrl(e.target.result);
            };
            reader.readAsDataURL(imgFile);
        } else {
            dfd.reject();
        }
        return dfd.promise();
    },
	select: function () {
		this.selected = true;
		this.trigger('select');
	},
	deselect: function () {
		this.selected = false;
		this.trigger('deselect');
	},
	edit: function () {
		this.trigger('edit');
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
      // "mouseup": "onMouseup",
      "dblclick": "dblclick"
    },
    _modelBinder:undefined,
    initialize: function () {
    	this._modelBinder = new Backbone.ModelBinder();
    	
    	this.model.rendered = true;
    	
    	this.model.bind('change:width', this.resize, this);
    	this.model.bind('change:height', this.resize, this);
    	this.model.bind('change:x', this.pos, this);
    	this.model.bind('change:y', this.pos, this);
        this.model.bind('change:locked', this.updatePositionLock, this);
        this.model.bind('change:ratioLocked', this.updateRatioLock, this);
    	this.model.bind('select', this.toggle, this);
    	this.model.bind('deselect', this.toggle, this);
    	this.model.bind('remove', this.remove, this);
    	this.model.bind('edit', this.edit, this);
    	// this.model.bind('destroy', this.remove, this); // loop as destroy on remove
    	
    	var hotel = $(this.el);
    	hotel.addClass('hot');
	    hotel.css({
	    	position: 'absolute', // important for chrome(maybe some other browsers as well)
	    	left: parseInt(this.model.get('x')),
	    	top: parseInt(this.model.get('y')),
	    	width: parseInt(this.model.get('width')),
	    	height: parseInt(this.model.get('height'))
	    });
	    hotel.data('cid', this.model.cid);
	    // hotel.append('<div class="size-indicator"><div class="size-indicator-text"></div></div>'); // size indicator element
	    hotel.draggable({
			// snap: true,
			containment: 'parent',
			drag: function () {
	    	},
			stop: _.bind(function () {
    			this.model.set({
    				x: $(this.el).position().left,
    				y: $(this.el).position().top
    			});
    			// this.model.save();
			}, this)
		}).resizable({
			minWidth: this.model.minWidth | 10,
			minHeight: this.model.minHeight | 10,
			containment: 'parent',
			handles: 'n, e, s, w, ne, se, sw, nw',
			aspectRatio: !!parseInt(this.model.get('ratioLocked'), 10),
			// support shift fixed aspectRatio
			start: function (e) {
				hotel.css('overflow', 'hidden');
				/*if (e.shiftKey) {
					$(this).resizable('option', 'aspectRatio', true);
				}*/
			},
            resize: function (event, ui) {
                // 实时更新
                // hotel.find('.size-indicator-text').text([Math.round(ui.size.width), Math.round(ui.size.height)].join('x'));
            },
			stop: _.bind(function () {
				// $(this.el).resizable('option', 'aspectRatio', false);
				this.model.set({
					width: $(this.el).width(),
					height: $(this.el).height()
				});
				hotel.css('overflow', 'visible');
			}, this)
		});
    },
    onMousedown: function (e) {
    	this.model.select();
    },
    toggle: function () {
    	// indicate selection
    	if (this.model.selected) {
    		$(this.el).addClass('hotsel');
    	} else {
    		$(this.el).removeClass('hotsel');
    	}
    },
    dblclick: function (e) {
        if ($(e.target).parents('.hot-toolbar').length > 0) return false;
        return this.edit();
    },
    edit: function () {
    	var hotModel = this.model;
    	
    	hotView = this; // used to render
    	var hotForm = $('#' + hotModel.cid + '_form');
    	var hotFormParent = hotForm.parent();
    	hotForm.extractor({
    		show:'fade', zIndex:2000, title:'',
    		width: 'auto', height: 'auto',
    		// dunno why jquery ui button not styled
			close: function () {
			},
    		buttons: { 
    			/*"Cancel": {
    				class: 'btn',
    				text: '取消',
    				click: function() {
    					hotForm.dialog('close');
    				}
    			},*/
    			"Ok": {
    				class: 'btn btn-primary',
    				text: '确认',
    				click: function() {
    					hotForm.dialog('close');
    				}
    			}
    		}
    	});
    },
    resize: function () {
    	$(this.el).css({
    		width: this.model.get('width'),
    		height: this.model.get('height')
    	});
    	this.model.layoutChanged = true;
    	this.render();
    },
    pos: function () {
    	$(this.el).css({
    		left: this.model.get('x'),
    		top: this.model.get('y')
    	});
    	this.model.layoutChanged = true;
    },
    updatePositionLock: function () {
        $(this.el).draggable('option', 'disabled', !!parseInt(this.model.get('locked'), 10));
    },
    updateRatioLock: function () {
        $(this.el).resizable('option', 'aspectRatio', !!parseInt(this.model.get('ratioLocked'), 10));
    },
    remove: function (e) {
    	// this.model.destroy(); // not really destroyed but garbaged, so can be undone
    	$(this.el).fadeOut('fast', function () {
    		$(this).remove(); // use detach to support undo/redo etc
    	});
    	this.model.rendered = false;
    	this.model.layoutChanged = true;
    },
    render: function () {
        var self = this;
        // preview image
        this.model.examineContentImage().done(function (imgPath, width, height) {
            var imgElm = $(self.el).find('img');
            if (!imgElm || !imgElm.length) {
                imgElm = $('<img />').appendTo($(self.el));
            }
            imgElm.attr('src', imgPath).css({
                width: self.model.attributes.width,
                height: self.model.attributes.height
            });
        }).fail(function () {
            $(self.el).find('img').remove();
        });
        // PH: locked indicator
        if (parseInt(this.model.get('locked'), 10)) {
            $(this.el).addClass('drag-locked');
        } else {
            $(this.el).removeClass('drag-locked');
        }
        if (parseInt(this.model.get('ratioLocked'), 10)) {
            $(this.el).addClass('ratio-locked');
        } else {
            $(this.el).removeClass('ratio-locked');
        }
        
        var rounder = function(direction, value){
        	return Math.round(value);
        };
        
        var bindings = {
            x: { selector: 'input.hot_x', converter: rounder },
            y: { selector: 'input.hot_y', converter: rounder },
            width: { selector: 'input.hot_w', converter: rounder },
            height: { selector: 'input.hot_h', converter: rounder }
        };
        
        this._modelBinder.bind(this.model, $('#' + this.model.cid + '_form'), bindings);
        
        return this;
    }
});

var PageCanvas = Backbone.View.extend({
	events: {
		"mousedown": "mousedown",
		//"mousemove": "mousemove",
		"mouseup": "mouseup"
	},
    initialize: function () {
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
			width: $(this.el).width(),
			height: $(this.el).height()
			//backgroundImage: 'url(../../images/page.jpg)',
		});
		canvasImgEl.find('img').css({
			width: $(this.el).width(),
			height: $(this.el).height()
		});
		canvasImgEl.focus(function (e) {
			// if it was just off
			// console.log('focus on');
		});
		canvasImgEl.blur(function (e) {
			// console.log('focus off');
		});
		// canvas has no focus
		$('html').keydown(_.bind(function (e) {
			if ($(e.target).is('input') || $(e.target).is('textarea')) {
				// do
				return;
			}
			if (e.which == 46) { // delete
				var delHots = [];
				this.hots.each(function (hot) {
					if (hot.selected) {
						delHots.push(hot);
					}
				});
				// destroyed in HotView
				this.hots.remove(delHots);
			}
		}, this));
		this.canvasImgEl = canvasImgEl;
		
		this.delegateEvents(); // need be called after el is ready
		
		this.hots = new Hots;
		this.hots.bind('add', this.addOne, this);
		this.hots.bind('reset', this.addAll, this);
		// remove is bound in HotView
		
		//this.hots.fetch();
		var pageHots = this.hots;
		$.contextMenu({
			selector: 'li.hot',
			zIndex: 2000,
			items: {
				'delete': {
					name: "去除", icon:"delete", callback: function(key, opt){
						var hot = pageHots.getByCid(this.data('cid'));
						pageHots.remove(hot);
					}
				},
				edit: {
					name: "编辑", icon:"edit", callback: function(key, opt){
						var hot = pageHots.getByCid(this.data('cid'));
						hot.edit();
					}
				},
				separator1: '-----',
				save: {
					name: "关于", icon:"save", callback: function(key, opt){
						alert('感谢点击');
					}
				}
			}
		});
		
		window.undomanager = new UndoManager(this.hots);
	},
	// on load
	load: function (hotsData) {
		$(hotsData).each(_.bind(function (index, hotData) {
			var hot = new Hot(hotData.attrs);
			hot.extraAttrs = hotData.extras;
			hot.assets = hotData.assets;
			this.hots.add(hot);
		}, this));
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
            } else {
                onHot = $(e.target).parents('.hot');
                if (onHot.length <= 0) {
                    onHot = null;
                }
            }
			
			this.hots.each(function (hot) {
				if (!onHot || hot.cid != onHot.data('cid')) {
					hot.deselect();
				}
			});
		}
		
		// this._beginDraw(e);
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
		// this.canvasImgEl.focus();
		if (this.hot) {
			this.hots.add(this.hot);
			// this.hot.save();
			this.hot = null;
		}
	},
	render: function () {
		return this;
	}
});