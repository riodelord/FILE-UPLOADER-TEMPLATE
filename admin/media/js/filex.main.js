$('document').ready(function() {
	$('button.navbar-toggle').click(function(evt) {
		evt.preventDefault();
		evt.stopPropagation();
		
		if($('.sidebar-left').hasClass('shown'))
			$('.sidebar-left').removeClass('shown');
		else
			$('.sidebar-left').addClass('shown');
	});
	
	$('.content').click(function() {
		if($('.sidebar-left').hasClass('shown'))
			$('.sidebar-left').removeClass('shown');
	});
});