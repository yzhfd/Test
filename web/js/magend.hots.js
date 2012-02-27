
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
		type: 0,
		x: 0,
		y: 0,
		width: 40,
		height: 40
	},
	isEdited: false, // true if confirm on edit dialog
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
	    	left: parseInt(this.model.get('x')),
	    	top: parseInt(this.model.get('y')),
	    	width: parseInt(this.model.get('width')),
	    	height: parseInt(this.model.get('height'))
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
    			// this.model.save();
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
				// this.model.save();
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
    	// indicate selection
    	if (this.model.selected) {
    		$(this.el).addClass('hotsel');
    	} else {
    		$(this.el).removeClass('hotsel');
    	}
    },
    submitDialog: function () {
    	
    },
    // @todo edit is too complex, refactor
    edit: function () {
    	var hottype = this.model.get('type');
    	var title = $('#hot_' + hottype).text();
    	$('#hot_dialog').find('.dlgcontent').hide();
    	var typeDlg = $('#hot_' + hottype + '_dialog');
    	var hotModel = this.model;
    	typeDlg.data('hot', hotModel);
    	typeDlg.html(typeDlg.data('resetTo').clone(true, true));
    	
    	// populate the form with extra attrs
    	// link
    	if (hotModel.extraAttrs) {
	    	$.each(hotModel.extraAttrs, function(name, value) {
	    		var input = $(":input[name='" + name + "']:not(:button,:reset,:submit,:image)", typeDlg );
	            input.val( ( !$.isArray( value ) && ( input.is(':checkbox') || input.is(':radio') ) ) ? [ value ] : value );
	    	});
    	}
    	// video
    	if (hottype == 1 || hottype == 4) {
    		var uploadArea = hottype == 1 ? $('#video-upload-area') : $('#audio-upload-area');
    		if (hotModel.uploads) {
    			var file = hotModel.uploads[0];
    			uploadArea
    			.removeClass('synced')
    			.addClass('unsynced')
    			.html(file.name + '<br/>' + parseSize(file.size));
    		} else if (hotModel.assets) {
	    		var filePath = hotModel.assets[0]['file'];
	    		var fileName = hotModel.assets[0]['name'];
	    		uploadArea
	    		.addClass('synced')
	    		.html('<a target="_blank" href="' + basePath + '/uploads/' + filePath + '">' + fileName + '</a>');
    		}
    	} else if (hottype == 0) {// images
    		// @todo DRY
    		if (hotModel.assets) {
    			$(hotModel.assets).each(function(index, asset){
    				if (asset.file) {
		            	var hotimg = $('<li class="hotimg synced"><a href="#" class="pagedel"></a><a class="imgwrapper" href="#" rel="' + asset.id + '" title="'
		            			+ asset.name + '"><img width="128" height="96" src="' + basePath + '/uploads/' + asset.file + '" /></a></li>');
		            	hotimg.appendTo($('#hotimgs'));
    				} else { // asset is DOM element
    					$(asset).clone(true, true).appendTo($('#hotimgs'));
    				}
    			});
    		}
    		
        	$('a.pagedel').live('click', function(e){
        		$(this).parent().remove();
        		return false;
        	});
        	
        	if ($('#hotimgs li.hotimg').length > 0) {
        		$('#hotimgs').width($('#hotimgs li.hotimg').length * 150 + 20);
        	}
    		$('#hotimgs').sortable({containment:$('#hotimgs')});
    	} else if (hottype == 3) {
    		// single image
    		if (hotModel.uploads) {
    			var imgFile = hotModel.uploads[0];
                var reader = new FileReader();
                reader.onload = function (e) {
                	$('#image-upload-area').html('<img class="unsynced" style="width:120px;" title="' + imgFile.name + '" src="' + e.target.result + '" />');
                };
                reader.readAsDataURL(imgFile);
    		} else if (hotModel.assets) {
	    		var filePath = hotModel.assets[0]['file'];
	    		var fileName = hotModel.assets[0]['name'];
	    		var imgUrl = basePath + '/uploads/' + filePath;
	    		$('#image-upload-area').html('<a rel="facebox" href="' + imgUrl + '"><img class="synced" alt="' + fileName + '" style="width:120px;" title="' + fileName + '" src="' + imgUrl + '" /></a>');
	    		$('#image-upload-area a[rel*=facebox]').facebox();
    		}
    	}
    	
    	// set hot essential information
    	var inputX = $('#hot_essential input[name="x"]');
    	var inputY = $('#hot_essential input[name="y"]');
    	var inputW = $('#hot_essential input[name="w"]');
    	var inputH = $('#hot_essential input[name="h"]');
    	inputX.val(this.model.get('x'));
    	inputY.val(this.model.get('y'));
    	inputW.val(this.model.get('width'));
    	inputH.val(this.model.get('height'));
    	
    	typeDlg.show();
    	
    	// $('<div/>').html('<h1>eample</h1>').dialog({autoOpen:true});
    	
    	$('#hot_dialog').dialog({
    		show:'fade', zIndex:2000, title:title,
    		width: 'auto', height: 'auto',
    		// dunno why jquery ui button not styled
			close: function () {
				
			},
    		buttons: { 
    			"Cancel": {
    				class: 'btn',
    				text: '取消',
    				click: function() {
    					hotModel.addUploads = null;
    					
    					$('#hot_dialog').dialog('close');
    				}
    			},
    			"Ok": {
    				class: 'btn primary',
    				text: '确认',
    				click: function() {
    					var x = parseInt(inputX.val());
    					var y = parseInt(inputY.val());
    					var w = parseInt(inputW.val());
    					var h = parseInt(inputH.val());
    					
    					hotModel.set({
    						x: x,
    						y: y,
    						width: w,
    						height: h
    					});
    					
    					if ($('form', typeDlg).length > 0) {
	    					var formObj = $('form', typeDlg).serializeObject();
	    					hotModel.extraAttrs = formObj;
    					}
    					if (hotModel.addUploads) {
    						if (!hotModel.uploads) hotModel.uploads = [];
    						$.merge(hotModel.uploads, hotModel.addUploads);
    						hotModel.addUploads = null;
    					}
    					if (hotModel.get('type') == 0) {
    						hotModel.uploads = [];
    						hotModel.assets = [];
    						$('#hotimgs').find('li.hotimg').each(function(index, hotimg){
    							hotimg = $(hotimg);
    							var imgFile = hotimg.data('file');
    							hotModel.assets.push(hotimg.clone(true, true)); // not file but element to avoid file read delay
    							if (imgFile) {
    								hotModel.uploads.push(imgFile);
    							}
    						});
    					}
    					
    					hotModel.isEdited = true;
    					$('#hot_dialog').dialog('close');
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
		//"mousemove": "mousemove",
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
		
		//this.hots.fetch();
		
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
			} else if ($(e.target).parent().is('.hot')) { // mousedown may be on drag handles
				onHot = $(e.target).parent();
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
		this.canvasImgEl.focus();
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