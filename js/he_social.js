
$(document).ready(function(){

	var windowSize = $(window).height();

	$('.intro-header').css('min-height', windowSize+'px');

	$(window).resize(function() {

		windowSize = $(window).height();

		$('.intro-header').css('min-height', windowSize+'px');
		$('#terminal').typist({
			height: windowSize
		})

	});

	$('.userinput').on('focus', function(){

		$('#sendbtn').show();

	});

	$('.btn-firstname').on('click', function(){

		$('#predefined').append('<input type="hidden" name="predefined" value="fname">').submit();

	})

	$('.btn-lastname').on('click', function(){

		$('#predefined').append('<input type="hidden" name="predefined" value="lname">').submit();

	})

	$('.btn-username').on('click', function(){

		$('#predefined').append('<input type="hidden" name="predefined" value="uname">').submit();

	})

	$('#backindex').on('click', function(){

		window.location.replace('index?nologin=1');

	});

});