function parseSize(size) {
	var suffix = ["字节", "KB", "MB", "GB", "TB", "PB"],
		tier = 0;

	while(size >= 1024) {
		size = size / 1024;
		tier++;
	}
	
	return Math.round(size * 10) / 10 + " " + suffix[tier];
}

var fileUploadable = function(panel) {
    panel = $(panel);
    
    var fileFormats = panel.attr('file_formats');
    var fileFormatsPattern = new RegExp('(\\.|\\/)(' + fileFormats.replace(/,/g, '|') + ')$', 'i');
    var prototype = panel.attr('data-prototype');
	panel.fileupload({
		paramName: 'file',
		acceptFileTypes: fileFormatsPattern,
		dropZone: panel,
		sequentialUploads: true,
		success: function (result) {
		},
		fail: function () {
			alert('上传失败');
		},
		drop: function (e, data) {
			var count = data.files.length;
			if (count == 0) return false;
			
			var nbMax = panel.attr('nb_max');
			var nbExist = panel.find('li').length;
			if (nbMax && count + nbExist> nbMax) {
				alert('最多允许添加' + nbMax + '个文件');
				return;
			}
			
			var nbValid = 0;
			for (var i=0; i<count; ++i) {
				(function (file) {
					if (!fileFormatsPattern.test(file.type) && !fileFormatsPattern.test(file.name)) {
						return;
					}
					
					++nbValid;
					
		            var reader = new FileReader();
		            reader.onload = function (e) {
                        var index = panel.data('index');
                        if (index == null) {
                            index = panel.children().length;
                        }
		                
		                var assetTpl = $(prototype.replace(/\$\$asset_name\$\$/g, index));
		                var asset = $(assetTpl);
		                asset.find('a.imgwrapper').attr('title', file.name);
		                asset.find('img').attr('src', e.target.result);
		                asset.find('input.asset_tag').val(file.name);
		                asset.addClass('unsynced');
		            	asset.appendTo(panel);
		            	
						panel.fileupload('option', 'success', function(result){
							asset.find('.pagedel').attr('href', result.delUrl);
							asset.find('img').attr('src', result.asset);
							asset.find('input.asset_resource').val(result.resource);
							asset.removeClass('unsynced');
						});
						panel.fileupload('option', 'url', Routing.generate('asset_upload'));
						panel.fileupload('send', { files:[file] });
						
						panel.data('index', index+1);
		            };
		            
		            reader.readAsDataURL(file);
				})(data.files[i]);
			}
			if (nbValid == 0) {
				alert('请上传' + panel.attr('file_note'));
			}
			
			return false;
		}
	}).bind('fileuploadsubmit', function (e, data) {
		// no upload immediately
		e.stopPropagation();
		e.preventDefault();
	});
};

var initPanel = function(panel){
	fileUploadable(panel);
	var panel = $(panel);
	var nbMax = panel.attr('nb_max');
	if (!nbMax || nbMax > 1) {
		panel.find('li').each(function(index, li){
			$(li).find('input.asset_seq').val(index);
		});
		
		$(panel).sortable({
			// axis: 'x',
			// helper: 'clone',
			opacity: 0.6,
			containment: panel,
			cursor: 'crosshair',
			tolerance: 'pointer',
			delay: 100,
			start: function (e, ui) {
				
			},
			update: function(e, ui) {
				panel.find('li').each(function(index, li){
					$(li).find('input.asset_seq').val(index);
				});
			}
		});
	}
};

var page_edit = function () {

	// Backbone.sync = Backbone.localSync;
	
	$('html').on('click', 'a.hot_del', function(e){
		$(this).parent().parent().remove(); // hot_form -> xxxHots
		return false;
	});

	$('html').on('click', 'a.pagedel', function(e){
		// @todo if there is url, then request it by ajax
		$(this).parent().remove();
		return false;
	});

	// upload
	$('.assets-panel').each(function(index, panel){
		initPanel(panel);
	});
	
	$('#page_canvas').fileupload({
		url: Routing.generate('page_replace', { id:$('#page_editor').attr('rel') }),
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
	
	window.pageCanvas = new PageCanvas({ el:$('#page_canvas') });
	
	$('#HotContainer').find('.hot_form').each(function(index, form){
		form = $(form);
		var hot = new Hot({
			x: form.find('input.hot_x').val(),
			y: form.find('input.hot_y').val(),
			width: form.find('input.hot_w').val(),
			height: form.find('input.hot_h').val()
		});
		form.attr('id', hot.cid + '_form');
		form.attr('title', form.closest('.hots_group').parent().find('label.hots_group').text());
		
		// last step
		pageCanvas.hots.add(hot);
	});
	
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
			// use ui.draggable to determine what type it is
			
			var rel = ui.draggable.attr('rel');
			var holder = $('#HotContainer_' + rel);
			var index = holder.data('index');
			if (index == null) {
				index = holder.children().length;
			}
			var prototype = holder.attr('data-prototype');
			var newForm = $(prototype.replace(/\$\$name\$\$/g, index));
			holder.append(newForm);
			holder.data('index', index+1);
			newForm.attr('class', rel);
			newForm.find('.assets-panel').each(function(index, panel) {
				initPanel(panel);
			});
			
			var hot = new Hot({
				x: Math.max(ui.offset.left - $(this).offset().left, 0),
				y: Math.max(ui.offset.top - $(this).offset().top, 0),
				width: $(ui.helper).width(),
				height: $(ui.helper).height()
			});
			var hotForm = newForm.find('.hot_form');
			hotForm.attr('id', hot.cid + '_form');
			hotForm.attr('title', ui.draggable.attr('title'));
			
			// last step
			pageCanvas.hots.add(hot);
		}
	});
	
	// @todo landscape or portrait
	$('#saveall').click(function () {
		$('#page_editor').overlay('loading');
	});
	
	$('#flushall').click(function () {
		localStorage.clear();
	});
};