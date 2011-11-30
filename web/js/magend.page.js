/**
 * Page
 */

var Page = Backbone.Model.extend({
	uploadUrl: '/Magend/web/app_dev.php/page/upload',
	url: '/Magend/web/app_dev.php/page', // @todo used to fetch model
	index: -1,
	file: null, //File
	defaults: {
		articleId: null,
		landscapeImg: null,
		portraitImg: null,
		portraitHots: null,
		label: null
	},
	initialize: function () {
		// make sure there is cid
		if (!this.cid && this.id) {
			this.cid = 'page_' + this.id;
		}
	},
	getNbTasks: function () {
		var nbTasks = 0;
		if (this.file) ++nbTasks;
		if (this.isNew() || this.hasChanged()) ++nbTasks;
		
		return nbTasks;
	},
	save: function (attrs, opts) {
		if (this.file) {
			this.uploadImage(true, attrs, opts);
			return;
		}
		
		if (!(this.isNew() || this.hasChanged())) {
			return;
		}
		
		if (!opts) opts = {};
		var success = opts.success;
		opts.success = function (model, response) {
			if (!model.id) {
				model.id = response.id;
			}
			
			if (success) success(model, response);
		};
		
		Backbone.Model.prototype.save.call(this, attrs, opts);
	},
	// @todo landscape or portrait
	uploadImage: function (isFromSave, attrs, opts) {
		if (!this.file) {
			return false;
		}
		
		var uploader = $('<div/>');
		uploader.fileupload({
			paramName: 'file',
			url: this.uploadUrl,
		    add: _.bind(function (e, data) {
		        var jqXHR = data.submit()
		            .success(_.bind(function (result, textStatus, jqXHR) {
		    			this.set({landscapeImg:result});
		    			this.trigger('uploaded', this);
		    			this.file = null;
		    			
		    			if (isFromSave == true) {
		    				this.save(attrs, opts);
		    			}
		            }, this))
		            .error(function (jqXHR, textStatus, errorThrown) {
		            	// @tood what to do
		            });
		    }, this)
		}).fileupload('add', { files:[this.file] });
		
		return true;
	}
});

var Pages = Backbone.Collection.extend({
	model: Page,
	localStorage: new Store('pages'),
	initialize: function (pages) {
		if (pages instanceof FileList) {
			_.each(pages, function (file) {
				this.create({'file':file});
			});
		}
	},
	comparator: function (page) {
		return page.index;
	},
	saveToRemote: function () {
		// switch to ajax
		Backbone.sync = Backbone.ajaxSync;
		this.each(function (page) {
			page.saveToRemote();
		});
		Backbone.sync = Backbone.localSync;
	}
});

var PageView = Backbone.View.extend({
	tagName: 'li',
	className: 'page',
	template: '<div class="close"></div><a href="#1" title={{label}}><img width="128" height="96" src="{{img}}" /></a>',
    events: {
      //"click": ""
    },
    initialize: function () {
    	//this.model.bind('change:index', this.render, this);
    	this.el = $(this.el);
    	this.el.data('cid', this.model.cid);
    	
    	this.render();
    	
    	this.model.bind('change:label', this.render, this);
    	this.model.bind('change:landscapeImg', this.render, this);
    	this.model.bind('change:portraitImg', this.render, this);
    	
		var file = this.model.file;
		if (file instanceof File) {
            var reader = new FileReader();
            reader.onload = _.bind(function (e) {
            	this.model.set({ label:file.name });
        		this.el.find('img').attr({
        			'src': e.target.result
        		});
            }, this);
            reader.readAsDataURL(file);
		}
    },
    add: function (model) {
    	console.log(model.collection);
    },
    render: function () {
    	var label = this.model.get('label');
    	var landscapeImg = this.model.get('landscapeImg');
    	if (!landscapeImg) {
    		landscapeImg = 'http://placehold.it/128x96';
    	} else {
    		landscapeImg = '../media/cache/landscapeThumb/uploads/' + landscapeImg;
    	}
    	
    	var html = $.mustache(this.template, {label:label, img:landscapeImg});
    	this.el.html(html);
        return this;
    }
});