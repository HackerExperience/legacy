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

      $('#btn-verify').on('click', function(){

          $.ajax({
          type: "POST",
          url: "welcome.php",
          data: {code: $('#code-input').val()}, 
          success:
              function(data) {

                  if(data.msg == ''){
                        $('#error-msg').hide();
                        $('#btn-verify').hide();
                        $('#code-input').hide();
                        $('#btn-start').show();
                  } else {
                        $('#error-msg').html(data.msg);
                        $('#error-msg').show(); 
                  }
              }

          });

      });

	$.getScript('js/typed.js', function(){

		$('#btn-start').on('click', function(){

			$('.intro-message').hide();

	      	$('#terminal').typist({
		        height: $('.intro-header').height(),
		        backgroundColor: '#000'
	      	});

	      	typetutorial(function(){
	      		console.log('oi');
	      	})	

		});


      function typetutorial(){

	      $('#terminal')
	      		.typist('print', 'Generating user information')
	      		.typist('promptecho')
	      		.typist('type', '............. ')
	      		.typist('println', ' [OK]')

	      		.typist('print', 'Creating virtual machine')
	      		.typist('promptecho')
	      		.typist('type', '................ ')
	      		.typist('println', '[OK]')

	      		.typist('print', 'Downloading operating system')
	      		.typist('promptecho')
	      		.typist('type', '............ ')
	      		.typist('println', ' [OK]')

	      		.typist('print', 'Installing operating system')
	      		.typist('promptecho')
	      		.typist('speed', 'veryslow')
	      		.typist('type', '............. ')
	      		.typist('println', ' [OK]')

	      		.typist('print', 'Booting up')
	      		.typist('promptecho')
	      		.typist('speed', 'fast')
	      		.typist('type', '.............................. ')
	      		.typist('println', ' [OK]')

	      		.typist('print', 'Starting tutorial')
	      		.typist('promptecho')
	      		.typist('speed', 'normal')
	      		.typist('type', '....................... ')
	      		.typist('println', ' [OK]');

	      	setTimeout(function(){
	      		window.location.href = 'university?opt=certification&learn=1';
	      	}, 8800);

      		

      }

      function typeconsole (){



     	$('#terminal')
            .typist('wait', '300')
      }

	});


});