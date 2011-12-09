/**
 * Page
 */

var Page = Backbone.Model.extend({
	uploadUrl: '/Magend/web/app_dev.php/page/upload',
	urlRoot: '/Magend/web/app_dev.php/page', // @todo used to fetch model
	file: null, //File
	defaults: {
		index: -1,
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
		
		this.synced();
	},
	getNbTasks: function () {
		var nbTasks = 0;
		if (this.file) ++nbTasks;
		if (this.isNew() || this.hasChanged()) ++nbTasks;
		
		return nbTasks;
	},
	// Disallow change image in this mode
	// Page's image can be changed in its own edit mode
	save: function (attrs, opts) {
		var dfd = $.Deferred();
		var promise = dfd.promise();
		
		if (!(this.isNew() || this.isOutOfSync())) {
			dfd.resolve();
			return promise;
		}
		
		this.uploadImage().then(_.bind(function(){
			return Backbone.Model.prototype.save.call(this, attrs, opts).done(_.bind(function(){
				if (!this.id) {
					this.id = response.id;
				}
				
				this.synced();
				dfd.resolve();
			},this)).fail( dfd.reject );
		}, this)).fail( dfd.reject );
		
		return promise;
	},
	// @todo landscape or portrait
	uploadImage: function () {
		var dfd = $.Deferred();
		var promise = dfd.promise();
		if (!this.file) {
			dfd.resolve();
			return promise;
		}
		
		$.ajaxQueue({
			fileupload: true,
			paramName: 'file',
			url: this.uploadUrl,
			file: this.file,
			success: _.bind(function (result) {
				this.set({ landscapeImg:result });
				this.trigger('uploaded', this);
				this.file = null;
	        }, this),
			error: _.bind(function (jqXHR, textStatus, errorThrown) {
				
			})
		}).done( dfd.resolve ).fail( dfd.reject );
		
		return promise;
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
		return page.get('index');
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
	template: '<a href="#" class="pagedel"></a><a href="#1" title={{label}}><img width="128" height="96" src="{{img}}" /></a>',
    events: {
      //"click": ""
    },
    initialize: function () {
    	//this.model.bind('change:index', this.render, this);
    	this.el = $(this.el);
    	this.el.data('cid', this.model.cid);
    	
    	if (this.el.find('img').length == 0) {
    		this.render();
    	}
    	
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
    		landscapeImg = '../../media/cache/landscapeThumb/uploads/' + landscapeImg;
    	}
    	
    	var html = $.mustache(this.template, {label:label, img:landscapeImg});
    	this.el.html(html);
    	this.el.find('.pagedel').click(_.bind( function(e) {
    		e.stopPropagation();
    		e.preventDefault();
    		
    		if (confirm('删除本页也将删除其所有的热点')) {
    			this.model.destroy();
    		}
    	}, this));
        return this;
    }
});