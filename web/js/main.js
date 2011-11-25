jQuery.event.props.push("dataTransfer");

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
		// articles.fetch();
		
		this.el = $('#editarea');
		
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
		
		// @todo if editarea is empty, then sortable will misbehave
		this.articles.add(new Article);
		
		this.el.sortable({
			opacity: 0.6,
			// helper: 'clone',
			// tolerance: 'pointer',
			start: function (event, ui) {
				var cid = $(ui.item).data('cid');
				var article = articles.getByCid(cid);
				//page.set('index', 2);
			},
			stop: function (event, ui) {
			    /*$(this).find('li').each(function (index, li) {
					var cid = $(li).data('cid');
					var article = articles.getByCid(cid);
					article.set({index:index+1});
				});*/
			}
		});
	},
	addOne: function (article) {
	    var at = new ArticleView({model:article});
	    var atel = $(at.render().el);
	    atel.data('cid', at.cid);
		
	    $(this.el).append(atel);
	},
	addAll: function () {		
		this.articles.each(this.addOne, this);
	},
	render: function () {
		// @todo move to initialize but if empty, sortable will be wrong
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
	
	var editarea = new EditArea(new Articles);
	// editarea.render();
	
	$('#saveremote').click(function () {
		pages.saveToRemote();
	});
});