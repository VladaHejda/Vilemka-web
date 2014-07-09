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

	$('a[href^=#]').on('click', function(event){
		event.preventDefault();
		var anchor = $(this).attr('href').substring(1);
		var target = $('a[name=' + anchor + ']');
		$('html').animate({scrollTop: target.offset().top}, 1000);
	});


	// calendar select tour
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


	// calendar move
	var occupancy = $('#occupancy');
	var anchorDisabled = false;
	occupancy.find('.arrow a').on('click', function(event) {
		event.preventDefault();
		if (anchorDisabled) {
			return;
		}
		anchorDisabled = true;
		var parent = $(this).closest('.arrow');
		var newLi = $('<li></li>');
		newLi.css({width: 0, marginRight: 0});
		var liToRemove;
		if (parent.hasClass('arrow-left')) {
			liToRemove = occupancy.find('ul li:last');
			occupancy.find('ul').prepend(newLi);
		} else {
			liToRemove = occupancy.find('ul li:first');
			occupancy.find('ul').append(newLi);
		}
		var completed = function() {
			liToRemove.remove();
			anchorDisabled = false;
		};
		var duration = 500;
		liToRemove.animate({width: 0, marginRight: 0}, duration, completed);
		newLi.animate({width: 211, marginRight: 15}, duration +50);
	});
});
