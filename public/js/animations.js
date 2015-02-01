$(function() {

	var doc = $(document);
	var win = $(window);
	var occupancy = $('#occupancy');
	var photo = $('#photo');


	/*********** slow bottom block scrolling ***********/
	var scrollSlowdown = 1.5;

	++scrollSlowdown;
	var blocks = [
		$('header'), $('#info'), photo, occupancy, $('#reservation'), $('#contact')
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
		$('html, body').animate({scrollTop: scroll}, 1000);
	});


	/*********** info expands ***********/
	$('.info-destination-container li').on('mouseover', function() {
		$(this).find('li').stop().fadeIn(250);
	}).on('mouseout', function() {
		$(this).find('li').stop().fadeOut(100);
		//var block = $(this).find('li');
		//setTimeout(function() {
		//	block.hide();
		//}, 500);
	}).find('li').css('display', 'none');


	/*********** photo move ***********/
	var photoMoveDisabled = false;
	photo.find('.arrow a').on('click', function(event) {
		event.preventDefault();
		if (photoMoveDisabled) {
			return;
		}
		photoMoveDisabled = true;
		var parent = $(this).closest('.arrow');
		var liToShow, liToHide;
		if (parent.hasClass('arrow-left')) {
			liToShow = photo.find('li.show:first').prev();
			if (!liToShow.length) {
				liToShow = photo.find('li:last');
				liToShow.detach().prependTo(photo.find('ul'));
			}
			liToHide = photo.find('li.show:last');
		} else {
			liToShow = photo.find('li.show:last').next();
			if (!liToShow.length) {
				liToShow = photo.find('li:first');
				liToShow.detach().appendTo(photo.find('ul'));
			}
			liToHide = photo.find('li.show:first');
		}
		var duration = 500;
		var width = parseInt(liToHide.css('width'));
		liToHide.animate({width: 0, marginRight: 0}, duration, function() {
			liToHide.removeClass('show');
		});
		liToShow.css({width: 0, marginRight: 0}).addClass('show');
		liToShow.animate({width: width, marginRight: 12}, duration +20, function() {
			photoMoveDisabled = false;
		});
	});


	/*********** photo show ***********/
	photo.find('li a').vanillabox();


	/*********** calendar select tour ***********/
	var findMonthNumber = function(el) {
		return parseInt(el.closest('.calendar').attr('class').match(/month-([0-9]+)/)[1]);
	};
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
		var last = others.last();

		var originalMonthNumber = findMonthNumber(a);
		var otherMonthNumber = findMonthNumber(last);
		if (otherMonthNumber == originalMonthNumber) {
			otherMonthNumber = findMonthNumber(others.first());
		}
		if (otherMonthNumber != originalMonthNumber) {
			var addToLast;
			if (otherMonthNumber == 11 || originalMonthNumber == 11) {
				addToLast = 11;
			} else {
				addToLast = otherMonthNumber > originalMonthNumber ? originalMonthNumber : otherMonthNumber;
			}
			last = last.add($('.month-' + addToLast + ' .week-' + weekNumber + ':last :first-child'));
		}

		if (add) {
			others.addClass('hover');
			last.addClass('last-week-day');
		} else {
			others.removeClass('hover');
			last.removeClass('last-week-day');
		}
	};

	var daysSelector = '.calendar tbody a';
	// todo commented out until it is not working correctly
	/*
	doc.on('mouseover', daysSelector, function() {
		toggleHover($(this), true);
	});
	doc.on('mouseout', daysSelector, function() {
		toggleHover($(this), false);
	});
	*/


	/*********** calendar move ***********/
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
		var newLi = $('<li></li>').css({width: 0, marginRight: 0});
		var liToRemove;
		var move = $(this).closest('.arrow').hasClass('arrow-left') ? -1 : 1;
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
		var width = parseInt(liToRemove.css('width'));
		liToRemove.animate({width: 0, marginRight: 0}, duration, completed);
		newLi.animate({width: width, marginRight: 15}, duration +50, function() {
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
