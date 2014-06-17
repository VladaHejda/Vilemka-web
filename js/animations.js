$(function() {

	var win = $(window);

	var infoBlock = $('#info');
	win.on('scroll', function(){
		var move = 290 + win.scrollTop();
		infoBlock.css('top', '-' + move + 'px');
	});
});
