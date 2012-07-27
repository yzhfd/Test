$(function () {
	
	var fileUploadable = function(panel) {
	    panel = $(panel);
	    
		var index = panel.data('index');
		if (index == null) {
			index = panel.children().length;
		}
	    
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
			                var assetTpl = $(prototype.replace(/\$\$asset_name\$\$/g, index));
			                var asset = $(assetTpl);
			                asset.find('a.imgwrapper').attr('title', file.name);
			                asset.find('img').attr('src', e.target.result);
			                asset.find('input.asset_tag').val(file.name);
			            	asset.appendTo(panel);
			            	
							panel.fileupload('option', 'success', function(result){
								asset.find('.pagedel').attr('href', result.delUrl);
								asset.find('img').attr('src', result.asset);
								asset.find('input.asset_resource').val(result.resource);
							});
							panel.fileupload('option', 'url', Routing.generate('asset_upload'));
							panel.fileupload('send', { files:[file] });
			            	// panel.width($('li.hotimg', panel).length * asset.outerWidth(true) + 20);
							
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
	
	$('.hot_add').click(function(e){
		var rel = $(this).attr('rel');
		var holder = $('#HotContainer_' + rel);
		var index = holder.data('index');
		if (index == null) {
			index = holder.children().length;
		}
		var prototype = holder.attr('data-prototype');
		var newForm = $(prototype.replace(/\$\$name\$\$/g, index));
		holder.append(newForm);
		newForm.attr('class', rel);
		
		holder.data('index', index+1);
		
		// for test
		/*
		var delLink = $('<a class="hot_del" href="#">-删除</a>');
		newForm.append(delLink);*/
		
		newForm.find('.upload_panel').each(function(index, panel) {
			fileUploadable(panel);
		});
	});
	
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
	$('.upload_panel').each(function(index, panel){
		fileUploadable(panel);
	});
});