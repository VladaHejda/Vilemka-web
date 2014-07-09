$(function() {

	// scroll
	var slowdown = 1.5;

	var win = $(window);

	++slowdown;
	var blocks = [
		$('header'), $('#info'), $('#photo'), $('#occupancy'), $('#reservation'), $('#contact')
	];
	win.on('scroll', function(event){
		var scroll = win.scrollTop();
		distance = 0;

		var move, distance, source;
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


	// calendar
	var toggleHover = function(a, add) {
		var classes = a.parent().attr('class').split(' ');
		var weekNumber = 0;
		$.each(classes, function (i, classname) {
			var matches = classname.match(/^week-([0-9]+)/);
			if (matches && matches[1] > weekNumber) {
				weekNumber = matches[1];
			}
		});
		var others = $('.week-' + weekNumber + ' a');
		if (add) {
			others.addClass('hover');
		} else {
			others.removeClass('hover');
		}
	};
	var calendarAnchors = $('.calendar tbody a');
	calendarAnchors.on('mouseover', function() {
		toggleHover($(this), true);
	});
	calendarAnchors.on(' mouseout', function() {
		toggleHover($(this), false);
	});
});
