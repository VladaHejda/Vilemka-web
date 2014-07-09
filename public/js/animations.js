$(function() {

	var doc = $(document);
	var win = $(window);


	/*********** slow bottom block scrolling ***********/
	var scrollSlowdown = 1.5;

	++scrollSlowdown;
	var blocks = [
		$('header'), $('#info'), $('#photo'), $('#occupancy'), $('#reservation'), $('#contact')
	];
	var scroller = function(){
		var scroll = win.scrollTop();
		distance = 0;

		var move, distance, source;
		for (var i = 0; i < blocks.length; ++i) {
			if (i) {
				source = i == 1 ? blocks[i-1] : $('.content', blocks[i-1]);
				distance += parseInt(source.css('height'));
			}
			move = scroll > distance ? (scroll - distance) /scrollSlowdown : 0;
			blocks[i].css('top', move);
		}
	};
	scroller();
	win.on('scroll', scroller);


	/*********** scroll to anchor ***********/
	$('a[href^=#]').on('click', function(event){
		event.preventDefault();
		var anchor = $(this).attr('href').substring(1);
		var scroll;
		if (anchor == 'top') {
			scroll = 0;
		} else {
			scroll = $('a[name=' + anchor + ']').offset().top;
		}
		$('html').animate({scrollTop: scroll}, 1000);
	});


	/*********** calendar select tour ***********/
	var toggleHover = function(a, add) {
		var classes = a.parent().attr('class').split(' ');
		var weekNumber = 0;
		$.each(classes, function (i, classname) {
			var matches = classname.match(/^week-([0-9]+)/);
			if (matches && matches[1] > weekNumber) {
				weekNumber = matches[1];
			}
		});
		var others = $('.week-' + weekNumber + ' a, .week-' + weekNumber + ' span');
		if (add) {
			others.addClass('hover');
		} else {
			others.removeClass('hover');
		}
	};
	var daysSelector = '.calendar tbody a';

	doc.on('mouseover', daysSelector, function() {
		toggleHover($(this), true);
	});
	doc.on('mouseout', daysSelector, function() {
		toggleHover($(this), false);
	});


	/*********** calendar move ***********/
	var occupancy = $('#occupancy');
	var calendarMoveDisabled = false;
	var baseMonth = 0;
	var calendarCache = [];
	// cache already loaded months
	occupancy.find('li').each(function(i, li) {
		calendarCache[i] = $(li).html();
	});

	occupancy.find('.arrow a').on('click', function(event) {
		event.preventDefault();
		if (calendarMoveDisabled) {
			return;
		}
		calendarMoveDisabled = true;
		var parent = $(this).closest('.arrow');
		var newLi = $('<li></li>').css({width: 0, marginRight: 0});
		var liToRemove;
		var move = parent.hasClass('arrow-left') ? -1 : 1;
		if (move == -1) {
			liToRemove = occupancy.find('li:last');
			occupancy.find('ul').prepend(newLi);
		} else {
			liToRemove = occupancy.find('li:first');
			occupancy.find('ul').append(newLi);
		}
		var completed = function() {
			liToRemove.remove();
			calendarMoveDisabled = false;
		};
		var duration = 500;
		liToRemove.animate({width: 0, marginRight: 0}, duration, completed);
		newLi.animate({width: 211, marginRight: 15}, duration +50, function() {
			var loading = $('#loading img').clone();
			newLi.html($('<div class="loading"></div>').append(loading));
			baseMonth += move;
			var loadMonth = (move == 1) ? baseMonth +2 : baseMonth;

			if (typeof calendarCache[loadMonth] != 'undefined') {
				newLi.html(calendarCache[loadMonth]);
			} else {
				$.get('', {loadMonth: loadMonth}, function(data) {
					newLi.html(data);
					calendarCache[loadMonth] = data;
				});
			}
		});
	});
});
