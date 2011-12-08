jQuery.event.props.push("dataTransfer");

// @todo how many requests need be issued



// @todo Add a 'new' mark!
// @todo Prompt not stored on server

/**
 * EditArea
 * 
 * Articles will be created by drag & drop image files here
 */
var EditArea = Backbone.View.extend({
	initialize: function (articles) {
		this.el = $('#editarea');
		
		this.articles = articles;
		
		articles.bind('add', this.addOne, this);
		articles.bind('remove', this.removeOne, this);
		articles.bind('reset', this.reset, this);
		
		// @todo remove, update index
		articles.fetch({
			success: function (collection, response) {
			}
		});
		
		articles.add(new Article);
		//articles.add(new Article);
		//articles.add(new Article);
		
		
		// @todo if editarea is empty, then sortable will misbehave
		//this.articles.create();
		
		/* HTML5 file DnD */
		window.editarea = this.el;
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
		this.articles.each(function (article) {
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
			
			var article = this.articles.getByCid(cid);
			article.set({ index:index });
			
			++index;
		}
	},
	_createArticlePlaceHolder: function () {
		var articleholder = $('<li class="article-placeholder"><ol class="pages"></ol></li>');
		var articles = this.articles;
		articleholder.find('.pages').sortable({
    		distance: 3,
    		containment: $('#editarea'),
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
	addOne: function (article) {
		this.el = $(this.el);
	    
		if (this.el.children().length == 0) {
			this.el.append(this._createArticlePlaceHolder());
		}
		
	    var at = new ArticleView({model:article});
	    var atel = $(at.render().el);
	    var index = article.get('index');
	    if (index < 0) {
	    	this.el.append(atel);
	    } else {
	    	atel.insertAfter(this.el.find('li.article-placeholder')[index]);
	    }
		
		this.el.css({width:this.articles.length*160});
		
		var placeholder = this._createArticlePlaceHolder();
		atel.data('placeholder', placeholder);
		this.el.append(placeholder);
		
		this.updateIndex();

	},
	removeOne: function (article) {
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
	reset: function (good) {
		$(this.el).empty();
		
		var count = this.articles.length;
		for (var i = 0; i < count; ++i) {
			var article = this.articles.at(count - i - 1); // @todo set index according to issue's
			article.set({ index:i });
			article.synced(); // @todo
			this.addOne(article);
		}
	},
	render: function () {
		// @todo render all articles, like update
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
	// now just save articles
	save: function () {	
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
	}
});

Backbone.sync = Backbone.ajaxSync;

$(function () {
	$('#addpage').click(function () {
		pages.create({index:5});
	});
	//Backbone.sync = Backbone.localSync;
	//window.pageCanvas = new PageCanvas;
	/*$('#selenable').change(function () {
		if ($(this).attr('checked')) {
			$(pageCanvas.el).selectable({disabled:false});
		} else {
			$(pageCanvas.el).selectable({disabled:true});
		}
	});*/
	$('#flushall').click(function () {
		localStorage.clear();
	});
	
	$('#undo').click(function () {
		undomanager.undo();
	});
	$('#redo').click(function () {
		undomanager.redo();
	});
	
	$(".alert-message").alert();
	
	$('.taggable').tagit({
		allowSpaces: true,
		caseSensitive: false,
		//fieldName: "tags",
		//tagSource: function
		availableTags: ['sexy', 'girl']
	});
	//Backbone.sync = Backbone.ajaxSync;
	//window.editarea = new EditArea(new Articles);
	var issue = new Issue({ id:1 });
	var issueView = new IssueView({ model:issue} );
	
	// editarea.render();
	
	// Backbone.emulateJSON = true
	
	// @todo which request is the last one, observe it!
	
	$('#flushbtn').click(function(e){
		e.stopPropagation();
		e.preventDefault();
		
		$.ajax({
			url: $(this).attr('href')
		});
	});
	
	$('#saveremote').click(function () {
		console.log(issueView.getNbTasks());
		$('#saveAlert').modal({show:true, backdrop:true});
		$.when(issueView.save()).done(function () {
			$('#saveremote').button('reset');
			$('#saveAlert').modal('hide');
		}).fail(function () {
			$('#saveremote').button('reset');
			$('#saveAlert').find('.modal-body p').text('出现错误');
		});
		//editarea.uploadImages();
	});
});