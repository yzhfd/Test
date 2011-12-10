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
	
	if ($('#issue_form').length > 0) {
		$('#issue_cover').click( function (e) {
			e.stopPropagation();
			e.preventDefault();
			
			// @todo show large
		});
		
		var issueForm = $('#issue_form');
		issueForm.fileupload({
			// maxFilesize, minFileSize
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			dropZone: $('#issue_cover')
		}).bind('fileuploaddrop', function (e, data) {
			var files = data.files;
			var imgFile = files[0];
			 
			var acceptFileTypes = $(this).fileupload('option', 'acceptFileTypes');
			if (!(acceptFileTypes.test(imgFile.type) ||
                    acceptFileTypes.test(imgFile.name))) {
				alert('请上传有效的图片文件');
                return;
            }
			
			// @todo validate image size and dimension
            var reader = new FileReader();
            reader.onload = function (e) {
            	$('#issue_cover').find('img').attr({
        			'src': e.target.result
        		});
            };
            $(this).data('cover', imgFile);
            reader.readAsDataURL(imgFile);
		}).bind('fileuploadsubmit', function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		});
		
		$('#issue_next').click( function (e) {
			var href = $(this).attr('href');
			if (href == '#') {
				alert('请先提交期刊信息');
				return false;
			}
		});
		
		issueForm.submit( function(e) {
			var cover = issueForm.data('cover');
			var coverSrc = $('#issue_cover').find('img').attr('src');
			if (!cover) {
				if (coverSrc == '') {
					alert('请上传封面');
					return false;
				} else {
					return true;
				}
			}
			
			var submitBtn = $(this).find('button[type="submit"]');
			submitBtn.button('loading');
			
			$('<div/>').fileupload({
				paramName: $('#cover_input').find('input').attr('name'),
				url: $(this).attr('action'),
				formData: $(this).serializeArray(),
				success: function (result) {
					submitBtn.button('reset');
					if (!result.id) {
						alert('提交失败');
						return;
					}
					
					alert('提交成功');
					$('#issue_next').attr('href', result.editorUrl);
					// $('#issue_next').removeClass('disabled');
				},
				error: null
			}).fileupload('send', { files:[cover] }); // only send one file
			
			return false;
		});
	}
	
	if ($('#page_canvas').length) {
		Backbone.sync = Backbone.localSync;
		window.pageCanvas = new PageCanvas;
	}
	
	
	if ($('#article_pages').length) {
		var articleId = $('#article_pages').find('#article_id');
		var article = new Article({ id:articleId.text() });
		var articleView = new ArticleView({ model:article, el:$('#article_pages') });
		article.fetch();
	}
	
	
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
	
	// editarea.render();
	
	// Backbone.emulateJSON = true
	
	// @todo which request is the last one, observe it!
	var issue, issueView;
	if ($('#issue_editor .articles').length) {
		issue = new Issue({ id:$('#issue_id').text() });
		issueView = new IssueView({ model:issue, el:$('#issue_editor .articles') } );
		
		//issue.fetch();
	}
	
	$('#flushbtn').click(function(e){
		e.stopPropagation();
		e.preventDefault();
		
		$.ajax({
			url: $(this).attr('href')
		});
	});
	
	$('#loadremote').click(function () {
		issue.fetch();
	});
	
	$('#saveremote').click(function () {
		console.log(issue.getNbTasks());
		$('#saveAlert').modal({show:true, backdrop:true});
		$.when(issue.save()).done(function () {
			$('#saveremote').button('reset');
			$('#saveAlert').modal('hide');
		}).fail(function () {
			$('#saveremote').button('reset');
			$('#saveAlert').find('.modal-body p').text('出现错误');
		});
		//editarea.uploadImages();
	});
});