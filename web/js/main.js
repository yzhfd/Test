jQuery.event.props.push("dataTransfer");


// @todo Add a 'new' mark!
// @todo Prompt not stored on server

/**
 * EditArea
 * 
 * Articles will be created by drag & drop image files here
 */
var EditArea = Backbone.View.extend({
	initialize: function (articles) {
		this.articles = articles;
		
		articles.bind('add', this.addOne, this);
		articles.bind('reset', this.addAll, this);
		
		// @todo remove, update index
		// articles.fetch();
		
		this.el = $('#editarea');
		
		
		
		// area to show pages of one article
		$('#article-pages .pages').sortable({
			//connectWith: this.el
			containment: $('#article-pages')
		});
		
		
		
		// @todo if editarea is empty, then sortable will misbehave
		this.articles.add(new Article);
		
		/* HTML5 file DnD */
		window.editarea = this.el;
		this.el.fileupload().bind('fileuploadadd', function (e, data) {
			var files = data.files;
			var count = files.length;
			for (var i = 0; i < count; ++i) {
				var article = articles.create();
				article.add(new Page({'file':files[i]}));
			}
		}).bind('fileuploadsubmit', function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		});
		
		this.el.sortable({
			opacity: 0.6,
			// helper: 'clone',
			// tolerance: 'pointer',
			start: function (event, ui) {
				var cid = $(ui.item).data('cid');
				var article = articles.getByCid(cid);
				//page.set('index', 2);
		    	if (window.expandedArticleView) {
		    		window.expandedArticleView.collapse();
		    	}
				
				// article.view.collapse();
			},
			stop: function (event, ui) {
			    /*$(this).find('li').each(function (index, li) {
					var cid = $(li).data('cid');
					var article = articles.getByCid(cid);
					article.set({index:index+1});
				});*/
			},
			// make article view droppable not work properly, so need hard check here
			// but this does have its own advantage - more flexible
			// @todo hover duration
			sort: function (e, ui) {
				var px = e.originalEvent.pageX;
				var py = e.originalEvent.pageY;
				var ats = $(this).find('.article');
				var c = ats.length;			
				for (var i = 0; i < c; ++i) {
					var at = $(ats[i]);
					if (at.is(ui.item) || at.is(ui.placeholder) || at.is(ui.helper)) {
						continue;
					}
					
					// @todo optimize this to only check articleviews near mouse
					if (( px > at.offset().left && px < at.offset().left + at.width() )
					&& ( py > at.offset().top && py < at.offset().top + at.height() )) {
						if (!at.is(this.overArticle)) {
							e.dragging = ui.item;
							at.trigger('dragenter', e);
							if (this.overArticle) {
								this.overArticle.trigger('dragexit');
							}
						}
						this.overArticle = at;
						break;
					}
				}
				
				// not inside any article view
				if (i == c && this.overArticle) {
					this.overArticle.trigger('dragexit');
					this.overArticle = null;
				}
			},
			stop: function (e, ui) {
				if (this.overArticle) {
					e.dropping = ui.item;
					this.overArticle.trigger('drop', e);
				}
			},
			change: _.bind(function (e, ui) {
				var atlis = this.el.find('.article').not(ui.item);
				var count = atlis.length;
				for (var i = 0, index = 0; i < count; ++i) {
					var atli = $(atlis[i]);
					if (atli.is(ui.placeholder)) {
						atli = $(ui.item);
					}
					
					var cid = atli.data('cid');
					if (cid == undefined) {
						continue;
					}
					
					++index;
					var article = this.articles.getByCid(cid);
					article.set({'index':index});
				}
			}, this)// update
		});
	},
	addOne: function (article) {
	    var at = new ArticleView({model:article});
	    var atel = $(at.render().el);
		
	    article.set({'index': this.articles.length});
	    
	    $(this.el).append(atel);
	},
	addAll: function () {		
		this.articles.each(this.addOne, this);
	},
	render: function () {
		// @todo move to initialize but if empty, sortable will be wrong
	},
	saveToRemote: function () {
		this.articles.saveToRemote();
	}
});

var PagesView = Backbone.View.extend({
    initialize: function (pages) {
		this.el = $('#units');
		
		this.pages = pages;
		pages.bind('add', this.addOne, this);
		pages.bind('reset', this.addAll, this);
		
		pages.fetch();
    },
    addOne: function (page) {
	    var pv = new PageView({model:page});
	    var pvel = $(pv.render().el);
	    pvel.data('cid', page.cid);
		
	    $(this.el).append(pvel);
    },
    addAll: function () {		
    	this.pages.each(this.addOne, this);
    },
    render: function () {
    	// @todo move to initialize but if empty, sortable will be wrong
	    var pages = this.pages;
		$(this.el).sortable({
			opacity: 0.6,
			start: function (event, ui) {
				var cid = $(ui.item).data('cid');
				var page = pages.getByCid(cid);
				//page.set('index', 2);
			},
			stop: function (event, ui) {
			    $(this).find('li').each(function (index, li) {
					var cid = $(li).data('cid');
					var page = pages.getByCid(cid);
					page.set({index:index+1});
				});
			}
		});
    }
});

$(function () {
	$('#addpage').click(function () {
		pages.create({index:5});
	});
	window.pageCanvas = new PageCanvas;
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
		availableTags: ['sex', 'girl']
	});
	
	window.editarea = new EditArea(new Articles);
	// editarea.render();
	
	$('#saveremote').click(function () {
		editarea.saveToRemote();
	});
});