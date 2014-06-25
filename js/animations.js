$(function() {

	var win = $(window);
	return;

	var blocks = {
		info: 290,
		photo: 658,
		occupancy: 1520
	};
	var top, limit, move;

	var placeBlock = function(block, basePosition) {
		top = win.scrollTop();
		limit = basePosition + 10;
		if (top <= limit) {
			move = basePosition + top;
		} else {
			move = basePosition + limit;
		}
		block.css('top', '-' + move + 'px');
	};

	$.each(blocks, function(id, basePosition) {
		placeBlock($('#' + id), basePosition);
	});

	win.on('scroll', function(event){
		placeBlock($('#info'), 290);
		placeBlock($('#photo'), 658);
	});
});
