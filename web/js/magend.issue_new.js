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
		minDate: 0,
		onSelect: function(dateText, inst) {
			/*var from = dateText == today ? now.getHours()+1 : 0;
			feedHours(from);*/
		}
	});
	
	// covers and preview
	$('#landscape-cover, #portrait-cover, #preview').each(function(index, img){
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
	
	// audio
	$('#attachedAudio').click(function(){
		if ($(this).attr('href') == '#') {
			return false;
		}
	});
	$('#attachAudio').fileupload({
		url: Routing.generate('issue_audioUpload'),
		paramName: 'file',
		acceptFileTypes: /(\.|\/)(mp3|wav)$/i,
		dropZone: $('#attachAudio'),
		limitMultiFileUploads: 1,
		success: function (result) {
			$('#attachAudio').text('拖拽音频文件到这里');
			$('#attachAudio').overlay('hide');
			
			$('#attachedAudio').attr('href', result.audio).text(result.name);
		},
		fail: function () {
			$('#attachAudio').text('拖拽音频文件到这里');
			$('#attachAudio').overlay('hide');
			alert('上传失败');
		}
	}).bind('fileuploaddrop', function (e, data) {
		var issueId = $('#attachedAudio').attr('rel');
		if (issueId == '#') {
			alert('请先提交期刊基本信息');
			return false;
		}
		
		var audioFile = data.files[0];
		var acceptFileTypes = $(this).fileupload('option', 'acceptFileTypes');
		if (!(acceptFileTypes.test(audioFile.type) ||
              acceptFileTypes.test(audioFile.name))) {
			alert('请上传MP3格式的音频文件');
            return false;
        }
		
		$('#attachAudio').text(audioFile.name);
		$('#attachAudio').overlay('loading');
		
		var audioFormData = {id:issueId};
		$('#attachAudio').fileupload('option', 'formData', audioFormData);
		return true;
	});
};