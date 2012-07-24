$(function () {
	$('#furnitureDetail_add').click(function(e){
		var holder = $('#HotContainer_furnitureDetailHots');
		var prototype = holder.attr('data-prototype');
		var index = holder.children().length;
		var newForm = prototype.replace(/\$\$name\$\$/g, index);
		holder.append(newForm);
		
		$('#HotContainer_furnitureDetailHots_' + index + '_type').val(1);
	});
	
	$('#slideLayer_add').click(function(e){
		var holder = $('#HotContainer_slideLayerHots');
		var prototype = holder.attr('data-prototype');
		var index = holder.children().length;
		var newForm = prototype.replace(/\$\$name\$\$/g, index);
		holder.append(newForm);
		
		$('#HotContainer_slideLayerHots_' + index + '_type').val(2);
	});
	
	$('html').on('click', 'a.pagedel', function(e){
		// @todo if there is url, then request it by ajax
		$(this).parent().remove();
		return false;
	});
	
	// upload
	$('.upload_panel').each(function(index, panel){
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
				// @todo how many already exist
				if (nbMax && count > nbMax) {
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
								console.log(result);
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
	});
});