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
	$('#landscape-cover, #portrait-cover').each(function(index, cover){
		cover = $(cover);
		cover.fileupload({
			url: Routing.generate('issue_coverUpload'),
			paramName: cover.attr('rel'),
			acceptFileTypes: /(\.|\/)(jpg|jpeg|png)$/i,
			dropZone: cover,
			limitMultiFileUploads: 1,
			success: function (result) {
				cover.find('a').attr('href', result.cover);
				cover.find('img').attr('src', result.cover);
				cover.overlay('hide');
			},
			fail: function () {
				cover.overlay('hide');
				alert('上传失败');
			}
		}).bind('fileuploaddrop', function (e, data) {
			var issueId = $('#attachedAudio').attr('rel');
			if (issueId == '#') {
				alert('请先提交期刊基本信息');
				return false;
			}
			
			var coverFile = data.files[0];
			var acceptFileTypes = $(this).fileupload('option', 'acceptFileTypes');
			if (!(acceptFileTypes.test(coverFile.type) ||
		          acceptFileTypes.test(coverFile.name))) {
				alert('请上传JPG或者PNG格式的图片');
		        return false;
		    }
			
            var reader = new FileReader();
            reader.onload = function (e) {
            	cover.find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(coverFile);
			
			cover.overlay('loading');
			
			var coverFormData = {id:issueId};
			cover.fileupload('option', 'formData', coverFormData);
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