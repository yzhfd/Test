$(function () {
	
	var fileUploadable = function(panel) {
		panel = $(panel);
		panel.fileupload({
			paramName: 'file',
			dropZone: panel,
			sequentialUploads: true,
			success: function (result) {
			},
			fail: function () {
				alert('上传失败');
			},
			drop: function (e, data) {
				var fileFormats = panel.attr('file_formats');
				var fileFormatsPattern = new RegExp('(\\.|\\/)(' + fileFormats.replace(/,/g, '|') + ')$', 'i');
				var count = data.files.length;
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
			            	var hotimg = $('<li class="hotimg unsynced"><a href="#" class="pagedel"></a><a class="imgwrapper" href="#" title="'
			            			+ file.name + '"><img width="128" height="96" src="' + e.target.result + '" /></a></li>');
			            	hotimg.appendTo(panel);
			            	
							panel.fileupload('option', 'success', function(result){
								hotimg.find('.pagedel').attr('href', result.delUrl);
								hotimg.find('img').attr('src', result.asset);
							});
							panel.fileupload('option', 'url', Routing.generate('asset_upload', { 'id':72 }));
							panel.fileupload('send', { files:[file] });
			            	// panel.width($('li.hotimg', panel).length * hotimg.outerWidth(true) + 20);
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
		var prototype = holder.attr('data-prototype');
		var index = holder.children().length;
		var newForm = $(prototype.replace(/\$\$name\$\$/g, index));
		holder.append(newForm);
		newForm.attr('class', rel);
		
		// for test
		var delLink = $('<a class="hot_del" href="#">-删除</a>');
		newForm.append(delLink);
		
		newForm.find('.upload_panel').each(function(index, panel) {
			fileUploadable(panel);
		});
	});
	
	$('html').on('click', 'a.hot_del', function(e){
		$(this).parent().remove();
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