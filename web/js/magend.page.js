/**
 * Page
 */

var Page = Backbone.Model.extend({
	url: '/Magend/web/app_dev.php/page/new', // @todo used to fetch model
	defaults: {
		index: 0,
		// img: 'http://placehold.it/128x96',
		file: null // the HTML5 local file object
	},
	initialize: function () {
		
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
    	
    	this.render();
    	
		var file = this.model.get('file');
		if (file) {
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
    render: function () {
    	// ../../images/thumb
    	var html = $.mustache(this.template, {img:'http://placehold.it/128x96'});
    	this.el.html(html);
        return this;
    }
});