$('document').ready(function() {	
	var file = false;	/* Var that will contain the file to upload */
	var cont = $('section.first-container .over-container'); /* Drag/Drop container */
	var error = false;
	
	function prev_def(e) {
		e.preventDefault();
	}
	
	function stop_all(e) {
		e.stopPropagation();
		e.preventDefault();
	}
	
	function validate_extension(name) {
		if(all_extensions_allowed == true)
			return true;
			
		name = name.split('.');
		var ext = name[name.length-1];
		if(allowed_extensions.indexOf(ext) == -1)
			return false;
		return true;
	}
	
	function validate_only_numeric(selector) {
		var val = $(selector).val();
		var regexp = /^\d+$/;
		if(regexp.test(val) == false)
			$(selector).val(val.substr(0,(val.length - 1)));
	}
	
	$('.first-container .row label.over-container').click(function(evt) {
		if(error == true)
			stop_all(evt);
	});
	
	/* When the mouse enters dragging a file */
	cont.on('dragenter', function(evt) {
		stop_all(evt);
		
		$(this).addClass('dotted');
		$(this).parent().children('.left-icon').addClass('light');
		$(this).parent().children('.right-text').addClass('light');
		$('section.first-container .right-text p').html('Drop file here...');
	});
	
	/* When the mouse gets out of the container */
	cont.on('dragleave', function(evt) {
		stop_all(evt);
		
		$(this).removeClass('dotted');
		$(this).parent().children('.left-icon').removeClass('light');
		$(this).parent().children('.right-text').removeClass('light');
		$('section.first-container .right-text p').html('Click or drop here to upload a new file');
	});
	
	cont.on('dragover', function(evt) {
		stop_all(evt);
	});
	
	/* When the user drops the file */
	cont.on('drop', function(evt) {
		prev_def(evt);
		
		$(this).removeClass('dotted');
		$(this).parent().children('.left-icon').removeClass('light');
		
		// Count files
		if(evt.originalEvent.dataTransfer.files.length > 1) {
			alert('Multiple files are not supported');
			return false;
		}
		
		file = evt.originalEvent.dataTransfer.files[0];
		var filename = file.name;
		
		// Validate extension
		if(validate_extension(filename) == false) {
			alert('This extension is not allowed');
			return false;
		}
		
		$(this).parent().children('.left-icon').children('i').removeClass('fa-cloud-upload').addClass('fa-file-o');
		$(this).parent().children('.right-text').removeClass('light');
		$('section.first-container .right-text p').addClass('filename').html(filename);
		
		/* Remove previous warning */
		$('section.first-container .left-icon i').removeClass('fa-exclamation-triangle').removeClass('fa-cloud-upload').removeClass('glow').addClass('fa-file-o');
		$('section.first-container .right-text').removeClass('warning');
	});
	
	/* Selecting a file from the dialog box */
	$('input[name=fileinput]').change(function(evt) {
		var val = $(this).val();
		file = evt.target.files[0];
		var filename = file.name;
		
		// Validate extension
		if(validate_extension(filename) == false) {
			alert('This extension is not allowed');
			return false;
		}
		
		$(this).removeClass('dotted');
		$(this).parent().children('.left-icon').removeClass('light');
		$(this).parent().children('.left-icon').children('i').removeClass('fa-cloud-upload').addClass('fa-file-o');
		$(this).parent().children('.right-text').removeClass('light');
		$('section.first-container .right-text p').addClass('filename').html(filename);
		
		/* Remove previous warning */
		$('section.first-container .left-icon i').removeClass('fa-exclamation-triangle').removeClass('fa-cloud-upload').removeClass('glow').addClass('fa-file-o');
		$('section.first-container .right-text').removeClass('warning');
	});
	
	/* Only numeric values */
	$('input[name=days]').keyup(function() {
		validate_only_numeric('input[name=days]');
	});
	
	$('input[name=downloads]').keyup(function() {
		validate_only_numeric('input[name=downloads]');
	});
	
	$('form[name=upload]').submit(function(evt) {
		prev_def(evt);
		
		var days = $('input[name=days]').val();
		var downloads = $('input[name=downloads]').val();
		var password = $('input[name=password]').val();
		
		if(file == false) {
			select_file_error();
			return false;
		}
		
		var fd = new FormData();
		fd.append('days',days);
		fd.append('downloads',downloads);
		fd.append('password',password);
		fd.append('file',file);
		
		start_upload_frontend();
		
		var jqXHR = $.ajax({
			xhr: function() {
				var xhrobj = $.ajaxSettings.xhr();
				if(xhrobj.upload) {
					xhrobj.upload.addEventListener('progress', function(evt) {
						var percent = 0;
						var position = evt.loaded || evt.position;
						var total = evt.total;
						if(evt.lengthComputable)
							percent = Math.ceil(position / total * 100);
						bar_set_progress(percent);
					}, false);
				}
				return xhrobj;
			},
			url: 'upload.php',
			type: "POST",
			contentType: false,
			processData: false,
			cache: false,
			data: fd,
			success: function(data) {	
				var code = data.substr(0,1);
				
				if(code == '1') {
					id = data.substr(2, data.length);
					
					bar_set_progress(100);
					
					// All good, redirect :)
					location.href = 'uploaded/'+id;
				}else{
					if(code == 'm') {
						var maxsize = data.substr(2, data.length);
						error_max_size(maxsize);
					}else if(code == 'e') {
						var ext = data.substr(2, data.length);
						error_unsupported_ext(ext);
					}else{
						error_frontend();
					}
				}
			},
			error: function(data) {
				error_frontend();
			}
		});
	});
	
	
	function start_upload_frontend() {
		$('section.first-container .left-icon i').removeClass('fa-cloud-upload').addClass('fa-upload').addClass('glow');
		$('section.first-container .right-text p').after('<div class="loader"><div class="fill"></div></div>');
		
		/* Scroll to the top */
		var to = $('section.first-container').offset().top - 50;
		$('html,body').animate({
			'scrollTop':to
		}, 1000, 'swing');
	}
	
	function bar_set_progress(percent) {
		$('section.first-container .right-text .loader .fill').css('width',percent+'%');
	}
	
	function error_frontend() {
		error = true;
		$('section.first-container .left-icon i').removeClass('fa-upload').addClass('fa-exclamation-triangle').addClass('glow');
		$('section.first-container .right-text .loader').remove();
		$('section.first-container .right-text').addClass('warning');
		$('section.first-container .right-text p').removeClass('filename').html('Something went wrong. Please try again later');
	}
	
	function error_max_size(filesize) {
		error = true;
		$('section.first-container .left-icon i').removeClass('fa-upload').addClass('fa-exclamation-triangle').addClass('glow');
		$('section.first-container .right-text .loader').remove();
		$('section.first-container .right-text').addClass('warning');
		$('section.first-container .right-text p').removeClass('filename').html('File size cannot be greater than '+filesize+'MB');
	}
	
	function error_unsupported_ext(ext) {
		error = true;
		$('section.first-container .left-icon i').removeClass('fa-upload').addClass('fa-exclamation-triangle').addClass('glow');
		$('section.first-container .right-text .loader').remove();
		$('section.first-container .right-text').addClass('warning');
		$('section.first-container .right-text p').removeClass('filename').html('Extension not allowed (only '+ext+')');
	}
	
	function select_file_error() {
		/* Scroll to the top */
		var to = $('section.first-container').offset().top - 50;
		$('html,body').animate({
			'scrollTop':to
		}, 1000, 'swing');
		
		$('section.first-container .left-icon i').removeClass('fa-upload').removeClass('fa-cloud-upload').addClass('fa-exclamation-triangle').addClass('glow');
		$('section.first-container .right-text .loader').remove();
		$('section.first-container .right-text').addClass('warning');
		$('section.first-container .right-text p').removeClass('filename').html('You must select a file to upload. Click here or drag and drop one.');
	}
});