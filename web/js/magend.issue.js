/**
 * Issue
 * 
 * Only used to edit articles/pages inside
 * 
 * @todo read or edit mode
 */
var Issue = Backbone.Model.extend({
	urlRoot: '/Magend/web/app_dev.php/issue',
	articleIdsUrl: '/Magend/web/app_dev.php/issue/update_articleIds',
	articles: null,
	defaults: {
		articleIds: '' // comma separated
	},
	initialize: function () {
		this.articles = new Articles;
		this.articles.issue = this;
		this.articles.url = this.urlRoot + '/' + this.id + '/articles';
	},
	getNbTasks: function () {
		var nbTasks = 0;
		// @todo issue itself
		var articles = this.articles;
		if (articles) {
			articles.each(function (article, index) {
				nbTasks += article.getNbTasks();
			});
		}
		
		return nbTasks;
	},
	// just save articles
	saveArticles: function () {	
		var dfd = $.Deferred();
		
		if (this.articles) {
			var when = $.when({});
			// pipe will pass arguments!
			this.articles.each(function (article) {
				when = when.pipe(function(){
					return article.save();
				});
			});
			when.done( dfd.resolve ).fail( dfd.reject );
		} else {
			dfd.resolve();
		}
		
		return dfd.promise();
	},
	save: function (attrs, opts) {	
		var dfd = $.Deferred();
		
		this.saveArticles()
			.pipe(_.bind(function () {
				var articleIds = [];
				this.articles.sort();
				this.articles.each(function (article) {
					articleIds.push(article.id);
				});
				
				articleIds = articleIds.join(',');
				var _articleIds = this.get('articleIds');
				if (!_.isEqual(_articleIds, articleIds)) {
					this.set({ articleIds:articleIds });
					// not use save, because of may-be side effect
					return $.ajaxQueue({
						type: 'POST',
						url: this.articleIdsUrl,
						data: { id: this.id, articleIds: articleIds },
						success: _.bind(this.synced, this)
					});	
				} else {
					return $.when({});
				}
			}, this))
			.done( dfd.resolve ).fail( dfd.reject );
		
		return dfd.promise();
	},
	fetch: function (opts) {
		return Backbone.Model.prototype.fetch.call(this, opts).pipe(_.bind( function() {
			this.articles.fetch();
		}, this));
	}
});

var IssueView = Backbone.View.extend({
	initialize: function () {
		var viewId = 'issue_' + this.model.id;
		if ($('#'+viewId) == undefined) return;
		
		this.el = $('#' + viewId + ' .articles');
		
		articles = this.model.articles;
		
		articles.bind('add', this.addArticle, this);
		articles.bind('remove', this.removeArticle, this);
		articles.bind('reset', this.resetArticles, this);
		
		articles.add(new Article);
		
		// @todo if editarea is empty, then sortable will misbehave
		//this.model.articles.create();
		
		/* HTML5 file DnD */
		this.el.fileupload().bind('fileuploaddrop', function (e, data) {
			var files = data.files;
			var count = files.length;
			for (var i = 0; i < count; ++i) {
				var article = new Article;
				article.set({ index:articles.length });
				var p = new Page;
				p.file = files[i];
				article.add(p);
				articles.add(article);
			}
			
			// $('#modal-from-dom').modal({backdrop:true, show:true});
		}).bind('fileuploadsubmit', function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		});
		
		this.el.sortable({
			opacity: 0.6,
			axis: 'x',
			helper: 'clone',
			items: 'li.article',
			//containment: '#editarea',
			//appendTo: 'body',
			tolerance: 'pointer',
			start: function (event, ui) {
				// collapse if need
			},
			stop: function (e, ui) {
				// ui.item
				// restore article's expanded state
			},
			update: _.bind(function (e, ui) {
				// adjust placeholders
				var placeholder = $($(ui.item).data('placeholder'));
				if ($(ui.item).prev().is('.article')) {
					$(ui.item).next().insertBefore($(ui.item));
				}
				placeholder.insertAfter($(ui.item));
				
				this.updateIndex();
			}, this)
		});
	},
	uploadImages: function () {
		var allPages = [];
		var allFiles = [];
		this.model.articles.each(function (article) {
			article.uploadImages();
		});
	},
	updateIndex: function () {
		var atlis = this.el.find('.article');
		var count = atlis.length;
		for (var i = 0, index = 0; i < count; ++i) {
			var atli = $(atlis[i]);
			var cid = atli.data('cid');
			if (cid == undefined) { // might be placeholder
				continue;
			}
			
			var article = this.model.articles.getByCid(cid);
			article.set({ index:index });
			
			++index;
		}
	},
	_createArticlePlaceHolder: function () {
		var articleholder = $('<li class="article-placeholder"><ol class="pages"></ol></li>');
		var articles = this.model.articles;
		articleholder.find('.pages').sortable({
			distance: 3,
			containment: this.el,
			connectWith:'ol.pages',
			axis: 'y',
			tolerance: 'pointer',
			over: function (e, ui) {
				$(this).parent().addClass('highlighted');
			},
			out:  function (e, ui) {
				$(this).parent().removeClass('highlighted');
			},
			receive: function (e, ui) {
				$(this).switchClass('highlighted', 'very-highlighted', 'fast')
					   .removeClass('very-highlighted', 'fast');
				
				var placeholder = $(this).parent();
				var index = placeholder.parent().find('li.article-placeholder').index(placeholder);
				var newArticle = new Article({ index:index });
				articles.add(newArticle);
				newArticle.add(window.editingPage);
				
				$(ui.item).remove();
			}
		});
		
		articleholder.bind({
			'dragenter': function (e, sorte) {
				e.stopPropagation();
				e.preventDefault();
				
				$(this).addClass('highlighted');
		    },
		    'dragover': function (e, sorte) {
		    	$(this).addClass('highlighted');
		    },
		    'dragleave': function (e) {
		    	//console.log(e.target);
		    	$(this).removeClass('highlighted');
		    },
		    'drop': function (e) {
		    	//console.log(e.target);
		    	console.log('xxx');
		    }
		});
		
	    return articleholder;
	},
	addArticle: function (article) {
		this.el = $(this.el);
	    
		if (this.el.children().length == 0) {
			this.el.append(this._createArticlePlaceHolder());
		}
		
		article.set({ issueId:this.model.id });
		
	    var at = new ArticleView({model:article});
	    var atel = $(at.render().el);
	    var index = article.get('index');
	    if (index < 0) {
	    	this.el.append(atel);
	    } else {
	    	atel.insertAfter(this.el.find('li.article-placeholder')[index]);
	    }
		
		this.el.css({width:this.model.articles.length*160});
		
		var placeholder = this._createArticlePlaceHolder();
		atel.data('placeholder', placeholder);
		this.el.append(placeholder);
		
		this.updateIndex();
	
	},
	removeArticle: function (article) {
		var cid = article.cid;
		var atels = this.el.find('li.article');
		for (var i = 0, c = atels.length; i < c; ++i) {
			var atel = $(atels[i]);
			if (atel.data('cid') == cid) {
				atel.remove();
				break;
			}
		}
		
		this.updateIndex();
	},
	// on fetch
	// sort will also call this
	resetArticles: function () {
		$(this.el).empty();
		
		this.model.articles.each(_.bind(function (article) {
			this.addArticle(article);
		}, this));
	},
	render: function () {
		// @todo render all articles, like update
	}
});