function parseSize(size) {
	var suffix = ["字节", "KB", "MB", "GB", "TB", "PB"],
		tier = 0;

	while(size >= 1024) {
		size = size / 1024;
		tier++;
	}
	
	return Math.round(size * 10) / 10 + " " + suffix[tier];
}

var page_edit = function () {

	// Backbone.sync = Backbone.localSync;
	
	/*$(window).bind('beforeunload', function(){ 
		alert('dont leave me alone');
		return false;
	});*/
	
	// link
	$('#internRadio').click(function(){
		$('#linkInput').val('0');
	});
	$('#externRadio').click(function(){
		$('#linkInput').val('http://');
	});
	
	// video
	$('#video-upload-area').fileupload({
		paramName: 'file',
		acceptFileTypes: /(\.|\/)mp4$/i,
		dropZone: $('#video-upload-area'),
		limitMultiFileUploads: 1,
		success: function (result) {
			$('#video-upload-area').text('拖拽视频到这里');
			$('#video-upload-area').overlay('hide');
			
		},
		fail: function () {
			$('#video-upload-area').text('拖拽视频到这里');
			$('#video-upload-area').overlay('hide');
			alert('上传失败');
		},
		drop: function (e, data) {
			var videoFile = data.files[0];
			var acceptFileTypes = $('#video-upload-area').fileupload('option', 'acceptFileTypes');
			
			if (!(acceptFileTypes.test(videoFile.type) ||
	              acceptFileTypes.test(videoFile.name))) {
				alert('请上传MP4格式的视频文件');
				for (var i=0; i<100; ++i) {} // may freeze the page if return right away
	            return false;
	        }
			
			$('#video-upload-area')
			.removeClass('synced')
			.addClass('unsynced')
			.html(videoFile.name + '<br/>' + parseSize(videoFile.size));
			
			var hot = $('#hot_1_dialog').data('hot');
			hot.addUploads = [ videoFile ];
			
			return false;
		}
	}).bind('fileuploadsubmit', function (e, data) {
		// no upload immediately
		e.stopPropagation();
		e.preventDefault();
	});
	
	// images
	var hotimgs = $('#hotimgs');
	hotimgs.fileupload({
		url: '',
		paramName: 'file',
		dropZone: hotimgs,
		sequentialUploads: true,
		drop: function (e, data) {
			$('#hotimgs').sortable({containment:$('#hotimgs')});
		
			var count = data.files.length;
			for (var i = 0; i < count; ++i) {
				(function (file) {
		            var reader = new FileReader();
		            reader.onload = function (e) {
		            	var hotimg = $('<li class="hotimg unsynced"><a href="#" class="pagedel"></a><a href="#" title="'
		            			+ file.name + '"><img width="128" height="96" src="' + e.target.result + '" /></a></li>');
		            	hotimg.appendTo($('#hotimgs'));
		            	hotimg.data('file', file);
		            };
		            
		            reader.readAsDataURL(file);
				})(data.files[i]);
			}
			
			return false;
		},
		submit: function (e, data) {
			// no upload immediately
			e.stopPropagation();
			e.preventDefault();
		}
	});
	
	// store original dialog content
	$('.dlgcontent').each(function(index, dlg){
		dlg = $(dlg);
		$('form', dlg).submit(function(e){
			return false;
		});
		dlg.data('resetTo', dlg.children().clone(true, true));
	});
	
	window.pageCanvas = new PageCanvas;
	if (initHots.length) {
		window.pageCanvas.load(initHots);
	}

	$('li', '#hotlib').draggable({
		revert: "invalid", // when not dropped, the item will revert back to its initial position
		containment: 'document',
		helper: function (){
			return $('<div style="width:80px; height:80px; background-color:gray;" />');
		},
		cursor: "move"
	});
	$('#page_canvas').droppable({
		accept: '#hotlib > li',
		drop: function(event, ui) {
			var typeText = ui.draggable.attr('title');
			var typeId = ui.draggable.attr('id').split('_')[1];
			
			// use ui.draggable to determine what type it is
			var hot = new Hot({
				type: typeId,
				x: Math.max(ui.offset.left - $(this).offset().left, 0),
				y: Math.max(ui.offset.top - $(this).offset().top, 0),
				width: $(ui.helper).width(),
				height: $(ui.helper).height()
			});
			pageCanvas.hots.add(hot);
		}
	});
	
	// @todo landscape or portrait
	$('#saveall').click(function () {
		$('#page_editor').overlay('loading');
		
		var pageId = $('#pageid').text();
		
		var hots = [];
		pageCanvas.hots.each(function(hot){
			//delete hot.attributes['id'];
			var attrs = hot.attributes;
			if (hot.extraAttrs) {
				attrs['extras'] = hot.extraAttrs;
			}
			hots.push(attrs);
		});
		
		$.ajax({
			url: Routing.generate('page_hots_save'),
			type: 'POST',
			data: { 'hots':hots, 'id':pageId },
			success: function (response) {
				pageCanvas.hots.each(function(hot, index){
					if (!hot.id) {
						hot.set({id:response[index]});
					}
				});
				
				// upload hot's video or image
				var uploader = $('<div/>');
				uploader.fileupload({
					paramName: 'file'
				}).bind('fileuploadsubmit', function (e, data) {
					// no upload immediately
					e.stopPropagation();
					e.preventDefault();
				});
				pageCanvas.hots.each(function(hot, index){
					if (hot.uploads && hot.uploads.length > 0) {
						var when = $.when({}); 
						$(hot.uploads).each(function(index, file){
							when = when.pipe(function(){
								// uploader.fileupload('option', 'formData', { name:file.name });
								uploader.fileupload('option', 'success', function(result){
									if (hot.type == 1) {
										
									}
									hot.assets = result;
									hot.uploads = null;
								});
								uploader.fileupload('option', 'url', Routing.generate('hot_upload', { 'id':hot.id }));
								return uploader.fileupload('send', { files:[file] });
							});
						});
						when.done(function(){
							console.log('over');
						});
					}
				});
			}
		}).done(function(){
			$('#page_editor').overlay('hide');
		});
	});		
	
	$('#flushall').click(function () {
		localStorage.clear();
	});
	$('#undo').click(function () {
		undomanager.undo();
	});
	$('#redo').click(function () {
		undomanager.redo();
	});
};