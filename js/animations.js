$(function() {

	var slowdown = 1.5;

	var win = $(window);
	// todo why are not callback locale?
	var scroll, move, distance, source;
	++slowdown;
	var blocks = [
		$('header'), $('#info'), $('#photo'), $('#occupancy'), $('#reservation'), $('#contact')
	];
	win.on('scroll', function(event){
		scroll = win.scrollTop();
		distance = 0;

		for (var i = 0; i < blocks.length; ++i) {
			if (i) {
				source = i == 1 ? blocks[i-1] : $('.content', blocks[i-1]);
				distance += parseInt(source.css('height'));
			}
			move = scroll > distance ? (scroll - distance) /slowdown : 0;
			blocks[i].css('top', move);
		}
	});

	$('a[href*=#]').on('click', function(event){
		event.preventDefault();
		var anchor = $(this).attr('href').substring(1);
		var target = $('a[name=' + anchor + ']');
		$('html').animate({scrollTop: target.offset().top}, 1000);
	});
});
