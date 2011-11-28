/**
 * Page
 */

var Page = Backbone.Model.extend({
	uploadUrl: '/Magend/web/app_dev.php/page/upload',
	url: '/Magend/web/app_dev.php/page/new', // @todo used to fetch model
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
	// @todo landscape or portrait
	uploadImage: function () {
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
		            }, this))
		            .error(function (jqXHR, textStatus, errorThrown) {
		            	
		            });
		    }, this)
		}).fileupload('add', { files:[this.file] });
		
		return true;
	},
	saveToRemote: function (options) {
		// options success, error, complete, etc
		var file = this.get('file');
		var attrs = this.toJSON();
		delete attrs.file;
		editarea.fileupload({
			paramName: 'file',
			formData: attrs,
			url: this.url
		})
		.fileupload('send', { files:[file] })
		.success(function (result, textStatus, jqXHR) {
			console.log(result);
		});
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
	template: '<a href="#1"><img width="128" height="96" src="{{img}}" /></a>',
    events: {
      //"click": ""
    },
    initialize: function () {
    	//this.model.bind('change:index', this.render, this);
    	this.el = $(this.el);
    	this.el.data('cid', this.model.cid);
    	
    	this.render();
    	
    	this.model.bind('change', this.render, this);
    	
		var file = this.model.file;
		if (file instanceof File) {
            var reader = new FileReader();
            reader.onload = _.bind(function (e) {
            	// files[i].name
            	this.img = e.target.result;
            	this.imgName = file.name;
            	this.el.data('img', this.img);
        		this.el.find('img').attr({
        			'src': this.img,
        			'title': this.imgName
        		});
            }, this);
            reader.readAsDataURL(file);
		}
    },
    add: function (model) {
    	console.log(model.collection);
    },
    render: function () {
    	var landscapeImg = this.model.get('landscapeImg');
    	if (!landscapeImg) {
    		landscapeImg = 'http://placehold.it/128x96';
    	} else {
    		landscapeImg = '../media/cache/landscapeThumb/uploads/' + landscapeImg;
    	}
    	
    	var html = $.mustache(this.template, {img:landscapeImg});
    	this.el.html(html);
        return this;
    }
});