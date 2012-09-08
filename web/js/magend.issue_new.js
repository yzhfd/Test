var issue_new = function () {
	$('#magsel').change(function(){
		// get issueno 
		$.ajax({
			url: Routing.generate('issuenos'),
			data: { magzineId:$(this).val() },
			success: function (result) {
				if (!result) result = {};
				$('#yearIssueNo').val(result.yearIssueNo);
				$('#totalIssueNo').val(result.totalIssueNo);
			}
		});
	});
	
	$('#publishedAt').keydown(function(e){
		e.stopPropagation();
		return false;
	}).datepicker({
		gotoCurrent:true, 
		onSelect: function(dateText, inst) {
			/*var from = dateText == today ? now.getHours()+1 : 0;
			feedHours(from);*/
		}
	});
	
	// covers and preview
	$('.issueImg').each(function(index, img){
		img = $(img);
		
		$('.pagedel', img).click(function(){
			$('img', img).attr('src', null);
			img.addClass('noImg');
			
			var issueId = $('#attachedAudio').attr('rel'); // @todo what if no attchedAudio in issue
			$.get($(this).attr('href') + '?id=' + issueId +  '&img=' + img.attr('rel'));
			return false;
		});
		
		img.fileupload({
			url: Routing.generate('issue_imgUpload'),
			paramName: img.attr('rel'),
			acceptFileTypes: /(\.|\/)(jpg|jpeg|png)$/i,
			dropZone: img,
			limitMultiFileUploads: 1,
			success: function (result) {
				img.find('a').attr('href', result.img);
				img.find('img').attr('src', result.img);
				img.overlay('hide');
			},
			fail: function () {
				img.overlay('hide');
				alert('上传失败');
			}
		}).bind('fileuploaddrop', function (e, data) {
			var issueId = $('#attachedAudio').attr('rel'); // @todo what if no attchedAudio in issue
			if (issueId == '#') {
				alert('请先提交期刊基本信息');
				return false;
			}
			
			var imgFile = data.files[0];
			var acceptFileTypes = $(this).fileupload('option', 'acceptFileTypes');
			if (!(acceptFileTypes.test(imgFile.type) ||
		          acceptFileTypes.test(imgFile.name))) {
				alert('请上传JPG或者PNG格式的图片');
		        return false;
		    }
			
            var reader = new FileReader();
            reader.onload = function (e) {
            	img.find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(imgFile);
			
            img.overlay('loading');
			
			var imgFormData = {id:issueId};
			img.fileupload('option', 'formData', imgFormData);
			return true;
		});
	});
};