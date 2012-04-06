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
	
	$('#page_canvas').fileupload({
		url: Routing.generate('page_replace', { id:$('#pageid').text() }),
		paramName: 'file',
		dropZone: $('#page_canvas'),
		acceptFileTypes: /(\.|\/)(jpg|jpeg|png)$/i,
		limitMultiFileUploads: 1,
		success: function(result) {			
			var oldImgUrl = $('#page_canvas_img>img').attr('src');
			var segs = oldImgUrl.split('/');
			segs.pop();
			segs.push(result.img);
			var newImgUrl = segs.join('/');
			$('#page_canvas_img>img').attr('src', newImgUrl);
			
			$('#page_editor').overlay('hide');
		},
		fail: function(result) {
			$('#page_editor').overlay('hide');
			alert('替换背景图片失败');
		}	
	}).bind('fileuploadsubmit', function(e, data){
		// no upload immediately
		e.stopPropagation();
		e.preventDefault();
	}).bind('fileuploaddrop', function(e, data){
		//if (confirm('确定替换当前页面背景图片吗？')) {
			var imgFile = data.files[0];
			if (!imgFile) return false; // may conflict with below dropping controls from toolbar
			var acceptFileTypes = $(this).fileupload('option', 'acceptFileTypes');
			
			if (!(acceptFileTypes.test(imgFile.type) ||
	              acceptFileTypes.test(imgFile.name))) {
				alert('请上传有效的图片文件');
				for (var i=0; i<100; ++i) {} // may freeze the page if return right away
	            return false;
	        }
			
			$('#page_editor').overlay('loading');
			$(this).fileupload('send', { files:[imgFile] });
		//}
		return false;
	});
	
	// link
	$('#internRadio').click(function(){
		$('#linkInput').val('0');
	});
	$('#externRadio').click(function(){
		$('#linkInput').val('http://');
	});
	
	// audio
	$('#audio-upload-area').fileupload({
		paramName: 'file',
		acceptFileTypes: /(\.|\/)(mp3|wav)$/i,
		dropZone: $('#audio-upload-area'),
		limitMultiFileUploads: 1,
		success: function (result) {
			$('#audio-upload-area').text('拖拽音频到这里');
			$('#audio-upload-area').overlay('hide');
		},
		fail: function () {
			$('#audio-upload-area').text('拖拽音频到这里');
			$('#audio-upload-area').overlay('hide');
			alert('上传失败');
		},
		drop: function (e, data) {
			var file = data.files[0];
			var acceptFileTypes = $('#audio-upload-area').fileupload('option', 'acceptFileTypes');
			
			if (!(acceptFileTypes.test(file.type) ||
	              acceptFileTypes.test(file.name))) {
				alert('请上传MP3、WAV格式的视频文件');
				for (var i=0; i<100; ++i) {} // may freeze the page if return right away
	            return false;
	        }
			
			$('#audio-upload-area')
			.removeClass('synced')
			.addClass('unsynced')
			.html(file.name + '<br/>' + parseSize(file.size));
			
			var hot = $('#hot_4_dialog').data('hot');
			hot.addUploads = [ file ];
			
			return false;
		}
	}).bind('fileuploadsubmit', function (e, data) {
		// no upload immediately
		e.stopPropagation();
		e.preventDefault();
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
			
			var hot = $('#hot_3_dialog').data('hot');
			hot.addUploads = [ videoFile ];
			
			return false;
		}
	}).bind('fileuploadsubmit', function (e, data) {
		// no upload immediately
		e.stopPropagation();
		e.preventDefault();
	});
	
	// images, image seq, map that support multiple images
	$('.has-assets-panel').each(function(index, dlg){
		dlg = $(dlg);
		var panel = dlg.find('.assets-panel');
		
		panel.fileupload({
			url: '',
			paramName: 'file',
			dropZone: panel,
			sequentialUploads: true,
			drop: function (e, data) {
				// dialog is cloned
				var panel = dlg.find('.assets-panel');
				var hot = dlg.data('hot');
				if (!hot.addUploads) hot.addUploads = [];
				var count = data.files.length;
				for (var i = 0; i < count; ++i) {
					(function (file) {
			            var reader = new FileReader();
			            reader.onload = function (e) {
			            	var hotimg = $('<li class="hotimg unsynced"><a href="#" class="pagedel"></a><a class="imgwrapper" href="#" title="'
			            			+ file.name + '"><img width="128" height="96" src="' + e.target.result + '" /></a></li>');
			            	hotimg.appendTo(panel);
			            	hotimg.data('file', file);
			            	
			            	$('a.pagedel').live('click', function(e){
			            		$(this).parent().remove();
			            		return false;
			            	});
			            	
			            	panel.width($('li.hotimg', panel).length * hotimg.outerWidth(true) + 20);
			            };
			            
			            reader.readAsDataURL(file);
					})(data.files[i]);
					hot.addUploads.push(data.files[i]);
				}
				
				return false;
			},
			submit: function (e, data) {
				// no upload immediately
				e.stopPropagation();
				e.preventDefault();
			}
		});
	});
	
	// single image
	$('#image-upload-area').fileupload({
		paramName: 'file',
		acceptFileTypes: /(\.|\/)(jpg|jpeg|png)$/i,
		dropZone: $('#image-upload-area'),
		limitMultiFileUploads: 1,
		success: function (result) {
			$('#image-upload-area').overlay('hide');
		},
		fail: function () {
			$('#image-upload-area').overlay('hide');
			alert('上传失败');
		},
		drop: function (e, data) {
			var imgFile = data.files[0];
			var acceptFileTypes = $('#image-upload-area').fileupload('option', 'acceptFileTypes');
			
			if (!(acceptFileTypes.test(imgFile.type) ||
	              acceptFileTypes.test(imgFile.name))) {
				alert('请上传有效的图片文件');
				for (var i=0; i<100; ++i) {} // may freeze the page if return right away
	            return false;
	        }
			
            var reader = new FileReader();
            reader.onload = function (e) {
            	$('#image-upload-area').html('<img class="unsynced" style="width:120px;" title="' + imgFile.name + '" src="' + e.target.result + '" />');
            };
            reader.readAsDataURL(imgFile);
			
			var hot = $('#hot_0_dialog').data('hot');
			hot.addUploads = [ imgFile ];
			
			return false;
		}
	}).bind('fileuploadsubmit', function (e, data) {
		// no upload immediately
		e.stopPropagation();
		e.preventDefault();
	});
	
	// store original dialog content
	$('.dlgcontent').each(function(index, dlg){
		dlg = $(dlg);
		$('form', dlg).submit(function(e){
			return false;
		});
		dlg.data('resetTo', dlg.children().clone(true, true));
	});
	
	window.pageCanvas = new PageCanvas({ el: $('#page_canvas') });
	if (initHots.length) {
		window.pageCanvas.load(initHots);
	}

	$('li', '#hotlib').draggable({
		revert: "invalid", // when not dropped, the item will revert back to its initial position
		containment: 'document',
		helper: function (){
			return $('<div style="width:80px; height:80px; background-color:gray; z-index:10;" />');
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
	
	var saveAll = function () {
		var dfd = $.Deferred();
		var promise = dfd.promise();
		
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
			fail: function () {
				dfd.reject();
			},
			success: function (response) {
				if (!pageCanvas.hots || pageCanvas.hots.length == 0) {
					dfd.resolve();
					return;
				}
				
				pageCanvas.hots.each(function(hot, index){
					if (!hot.id) {
						hot.set({ id:response[index] });
					}
				});
				
				// upload hot's video, audio or image
				var uploader = $('<div/>');
				uploader.fileupload({
					paramName: 'file'
				}).bind('fileuploadsubmit', function (e, data) {
					e.stopPropagation();
					e.preventDefault();
				});
				
				var when = $.when({});
				pageCanvas.hots.each(function(hot, index){
					if (!hot.uploads || hot.uploads.length == 0) {
						return;
					}
					$(hot.uploads).each(function(index, file){
						noUpload = false;
						when = when.pipe(function(){
							// uploader.fileupload('option', 'formData', { name:file.name });
							uploader.fileupload('option', 'success', function(result){
								// according setting is done on dialog open
								hot.assets = result;
								hot.uploads = null;
							});
							
							uploader.fileupload('option', 'url', Routing.generate('asset_upload', { 'id':hot.id }));
							// @todo on success, map id with li
							return uploader.fileupload('send', { files:[file] });
						});
					});
				});
				
				// To order pages must wait for complete of all uploads
				var when2 = $.when({});
				when.done(function(){
					pageCanvas.hots.each(function(hot, index){
						// @todo refactor
						if (!hot.assets || (hot.get('type') != 1 && hot.get('type') != 5 && hot.get('type') != 6) || !hot.isEdited) {
							return;
						}
						
						var assets = [];
						$(hot.assets).each(function(index, asset){
							if (asset.id) {
								assets.push(asset.id);
							} else {
								assets.push($('.imgwrapper', asset).attr('rel'));
							}
						});
					
						when2 = when2.pipe(function(){
							return $.ajax({
								url: Routing.generate('hot_order_assets', { 'id':hot.id }),
								type: 'POST',
								data: { assets:assets }
							});
						});
					});
					
					when2.done( dfd.resolve ).fail( dfd.reject );
				}).fail( dfd.reject );
			}
		});
		
		return promise;
	};
	
	// @todo landscape or portrait
	$('#saveall').click(function () {
		$('#page_editor').overlay('loading');
		saveAll().always(function(){
			$('#page_editor').overlay('hide');
			pageCanvas.hots.each(function(hot, index){
				hot.isEdited = false;
				hot.layoutChanged = false;
			});
			console.log('save done');
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