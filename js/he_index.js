window.fbAsyncInit = function() {
	FB.init({
	appId      : fbid,
	status     : true, // check login status
	cookie     : true, // enable cookies to allow the server to access the session
	xfbml      : true  // parse XFBML
	});

	// Load the SDK asynchronously
	(function(d){
	var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement('script'); js.id = id; js.async = true;
	js.src = "//connect.facebook.net/en_US/all.js";
	ref.parentNode.insertBefore(js, ref);
	}(document));

}

$(document).ready(function(){

	function ismob() {
	   if(window.innerWidth <= 800 && window.innerHeight <= 640) {
	     return true;
	   } else {
	     return false;
	   }
	}

	var ismob = ismob();
	var windowSize = $(window).height();

	$('.intro-header').css('min-height', windowSize+'px');

	$(window).resize(function() {

		windowSize = $(window).height();

		$('.intro-header').css('min-height', windowSize+'px');
		$('#terminal').typist({
			height: windowSize
		})

	});

	$( "#accordion" ).accordion({
		collapsible: true,
		active: false
	});

	$('.goto-login').on('click', function(){
		gotoLogin();
	});

	$('.goto-signup').click(function(){
		gotoSignUp();
	});

	$('.goto-about').click(function(){
		cur = 2;
		if(ismob){
			var loctop = $("#mAbout").offset().top;
		} else {
			var loctop = $("#About").offset().top;
			$('.about1').tooltip('show');
		}
	    $('html, body').animate({
	       scrollTop: loctop
	    }, 500);	    
	});	

	$('.goto-faq').click(function(){
		cur = 4;
	    $('html, body').animate({
	       scrollTop: $("#FAQ").offset().top
	    }, 500);
	});		


	$('#signup-form').on('mouseover', function(){
		validate('signup');
	})	

	$('#login-form').on('mouseover', function(){
		validate('login');
	})	

	var validatedLogin = validatedSignUp = false;

	function validate(form){

		setTimeout(function(){
			if(form == 'signup'){
				if(validatedSignUp){
					return;
				}
				validateSignUp();
			} else {
				if(validatedLogin){
					return;
				}
				validateLogin();
			}
		}, 300)

	}

	function validateSignUp(){

		$('#signup-form').validate({

            rules:{
                username: {
                	required: true,
                	rangelength: [3, 15],
					remote: {
						url: "ajax.php",
						type: "post",
						dataType:"json",
						data: {
							func: 'check-user'
						}
					}
                },
                password: {
                	required: true,
                	minlength: 6
                },
                email: {
                	required: true,
                	email: true,
					remote: {
						url: "ajax.php",
						type: "post",
						data: {
							func: 'check-mail'
						}
					}
                },
                terms: {
                	required: true
                }
            },
            errorPlacement: function(){},
		    highlight: function(element) {
		        $(element).closest('.form-group').addClass('has-error');
		        $(element).closest('.form-group').removeClass('has-success');
		    },
		    unhighlight: function(element) {
		        $(element).closest('.form-group').removeClass('has-error');
		        $(element).parents('.form-group').addClass('has-success');
		    },
		    errorElement: 'span',
		    errorClass: 'help-block',
		    validClass: 'has-success',
		    errorPlacement: function(error, element) {
		    	if($(element).attr('id') != 'terms'){
			        if(element.parent('.input-group').length) {
			            error.insertAfter(element.parent());
			        } else {
			            error.insertAfter(element);
			        }
		    	}
			},
			messages: {
				username: {
					remote: 'Username already taken.'
				},
				email: {
					remote: 'This email is in use.'
				}
			}

		});

	}

	function validateLogin(){

		$('#login-form').validate({

            rules:{
                username: {
                	required: true,
                },
                password: {
                	required: true,
                }
            },
            errorPlacement: function(){},
		    highlight: function() {},
		    unhighlight: function() {},
		    errorElement: 'span',
		    errorClass: 'error-input',
		    validClass: 'has-success',
		    errorPlacement: function() {},
			messages: {
				name: {
					remote: 'Username already taken.'
				}
			}

		});

	}

	var cur = 0;
	var lastAnimation = 0;

	if(typeof header != 'undefined'){

		if(header == 'login'){
			gotoLogin();
		} else {
			gotoSignUp();
		}

	}

	function gotoLogin(){

		if(ismob){
			var loctop = $("#mLogin").offset().top - 50;
		} else {
			var loctop = $("#Login").offset().top;
		}

		cur = 1;
	    $('html, body').animate({
	       scrollTop: loctop
	    }, 500);
		setTimeout(function(){
			$('#login-username').focus();
		}, 100);
		validate('login');

	}

	function gotoSignUp(){

		if(ismob){
			var loctop = $("#mSignUp").offset().top;
		} else {
			var loctop = $("#SignUp").offset().top;
		}

		cur = 3;
	    $('html, body').animate({
	       scrollTop: loctop
	    }, 500);
		setTimeout(function(){
			$('#signup-username').focus();
		}, 100);
		validate('signup');

	}

	if(!ismob){

		function move(cur){

		    if(cur == 0){
			    $('html, body').animate({
			       scrollTop: 0
			    }, 500);
			    $('#login-username').blur();
		    } else if(cur == 1){
			    $('html, body').animate({
			       scrollTop: $("#Login").offset().top
			    }, 500);
			    setTimeout(function(){
			    	$('#login-username').focus();
			    	validate('login');
			    }, 500)
			    $('.about1').tooltip('hide');
		    } else if(cur == 2){
			    $('html, body').animate({
			       scrollTop: $("#About").offset().top
			    }, 500);
			    $('#login-username').blur();
			    $('#signup-username').blur();
			    $('.about1').tooltip('show');
		    } else if(cur == 3){
			    $('html, body').animate({
			       scrollTop: $("#SignUp").offset().top
			    }, 500);
				setTimeout(function(){
					$('#signup-username').focus();
					validate('signup');
				}, 500);
				$('.about1').tooltip('hide');
		    } else if(cur == 4) {
			    $('html, body').animate({
			       scrollTop: $("#FAQ").offset().top
			    }, 500);
			    $('#signup-username').blur();
		    } else {
			    $('html, body').animate({
			       scrollTop: $("#footer").offset().top
			    }, 500);	
		    }

		}

		$(document).keydown(function(e) {

			var tag = e.target.tagName.toLowerCase();

	    	if (tag != 'input'){

				load = true;
				var timeNow = new Date().getTime();

				var quiet = 50;
				if(cur == 1){
					var quiet = 100;
				}

			    if(timeNow - lastAnimation < quiet + 400) {
			    	e.preventDefault();
			        return false;
			    }

		  		switch(e.which) {
		  			case 33: //pg up
		        	case 38: //up
			    		cur--;
				   		if(cur < 0){
				    		cur = 0;
				    		load = false;
				    	}              	
			        	break;
			    	case 34: //pg dw
			        case 40: //dw
				    	cur++;
				    	if(cur > 5){
				    		cur = 5;
				    		load = false;
				    	}
		        		break;
		        	case 36: //home
		        		cur = 0;
		        		break;
		        	case 35: //end
		        		cur = 5;
		        		break;
		        	default: 
		        		return;
		  		}

			  	if(!load){
				  	e.preventDefault();
				  	return false;
			  	}

		      	move(cur);
				lastAnimation = timeNow;

		        return false;

	    	}

	  	});

	}

	$('#fb-login').on('click', function(){
		window.location.replace($('#fb-login').attr('value'));
	});

	$('#tt-login').on('click', function(){
		window.location.replace($('#tt-login').attr('value'));
	});
	
	loadedTip = false;
	if(!loadedTip){
        loadedTip = true;
        setTimeout(function(){
			$('.ul-about').tooltip({
				selector: "li[data-toggle=tooltip]"
			})
		}, 500);
	}

	$('.about').mouseover(function(){
		if(!($(this).hasClass('about1'))){
			$('.about1').tooltip('hide');
		}
	});
	$('.about-more').mouseover(function(){
		$('.about1').tooltip('hide');
	});

	//flags

    $(".dropdown dt a").click(function() {
        $(".dropdown dd ul").toggle();
    });

    $(".dropdown dd ul li a").click(function() {
        var text = $(this).children()[0];
        $(".dropdown dt a span").html(text);
        $(".dropdown dd ul").hide();
    });

    //index codes

	if(ismob){
		$('.content-section-a').css({'padding-top':'25px','padding-bottom':'25px','text-align':'center'});
		$('.content-section-b').css({'padding-top':'25px','padding-bottom':'25px','text-align':'center'});
		$('#login-form').css('text-align','left');
		$('#signup-form').css('text-align','left');
		$('#accordion').css('text-align', 'left');
		$('.lead').css('text-align', 'left');
		$('.appico').addClass('fa-3x').removeClass('fa-4x');
		$('.section-heading-spacer').hide();
		$('#freq').css('margin-top', '0');
		$('.three').css('text-align', 'center');
		$('#contact').css('text-align', 'center');
		$('#neoart').css('text-align', 'center');
		$('#legal-disclaimer').css({'margin-top':'30px','margin-bottom':'30px'});
		$('#hand').css('margin-left', '0');
	}

	$('#terminal').typist({
	    height: $('.intro-header').height(),
	    backgroundColor: '#000'
  	});


	  function typeboot(){

	      $('#terminal')
	      		.typist('print', '[   16.388364] cfg80211: World regulatory domain updated:<br/> \
					[   16.388367] cfg80211:     (start_freq - end_freq @ bandwidth), (max_antenna_gain, max_eirp)<br/> \
					[   16.388370] cfg80211:     (2402000 KHz - 2472000 KHz @ 40000 KHz), (300 mBi, 2000 mBm)<br/> \
					[   16.388372] cfg80211:     (2457000 KHz - 2482000 KHz @ 20000 KHz), (300 mBi, 2000 mBm)<br/> \
					[   16.388374] cfg80211:     (2474000 KHz - 2494000 KHz @ 20000 KHz), (300 mBi, 2000 mBm)<br/> \
					[   16.388376] cfg80211:     (5170000 KHz - 5250000 KHz @ 40000 KHz), (300 mBi, 2000 mBm)<br/> \
					[   16.388378] cfg80211:     (5735000 KHz - 5835000 KHz @ 40000 KHz), (300 mBi, 2000 mBm)<br/> \
					[   16.388559] snd_hda_intel 0000:00:1b.0: irq 44 for MSI/MSI-X<br/> \
					[   16.388597] snd_hda_intel 0000:00:1b.0: setting latency timer to 64<br/> \
					[   17.166666] ATOM BIOS: Dell/Compal<br/> \
					[   17.166692] radeon 0000:01:00.0: VRAM: 2048M 0x0000000000000000 - 0x000000007FFFFFFF (2048M used)<br/> \
					[   17.166696] radeon 0000:01:00.0: GTT: 512M 0x0000000080000000 - 0x000000009FFFFFFF<br/> \
					[   17.166704] mtrr: no more MTRRs available<br/> \
					[   17.166706] [drm] Detected VRAM RAM=2048M, BAR=256M<br/> \
					[   17.166708] [drm] RAM width 128bits DDR<br/> \
					[   17.166828] [TTM] Zone  kernel: Available graphics memory: 4035274 kiB<br/> \
					[   17.166830] [TTM] Zone   dma32: Available graphics memory: 2097152 kiB<br/> \
					[   17.166832] [TTM] Initializing pool allocator<br/> \
					[   17.166836] [TTM] Initializing DMA pool allocator<br/> \
					[   17.166855] [drm] radeon: 2048M of VRAM memory ready<br/> \
					[   17.166857] [drm] radeon: 512M of GTT memory ready.<br/> \
					[   17.166871] [drm] GART: num cpu pages 131072, num gpu pages 131072<br/> \
					[   17.167188] [drm] radeon: ib pool ready.<br/> \
					[   17.167288] [drm] Loading VERDE Microcode<br/> \
					[   17.279003] hda_codec: CX20590: BIOS auto-probing.<br/> \
					[   17.279865] input: HDA Digital PCBeep as /devices/pci0000:00/0000:00:1b.0/input/input10<br/> \
					[   17.591411] HDMI status: Codec=3 Pin=5 Presence_Detect=0 ELD_Valid=0<br/> \
					[   17.591663] input: HDA Intel PCH HDMI/DP,pcm=3 as /devices/pci0000:00/0000:00:1b.0/sound/card0/input11<br/> \
					[   18.040246] platform radeon_cp.0: firmware: agent loaded radeon/VERDE_pfp.bin into memory<br/> \
					[   18.267020] platform radeon_cp.0: firmware: agent loaded radeon/VERDE_me.bin into memory<br/> \
					[   18.281659] platform radeon_cp.0: firmware: agent loaded radeon/VERDE_ce.bin into memory<br/> \
					[   18.285998] platform radeon_cp.0: firmware: agent loaded radeon/VERDE_rlc.bin into memory<br/> \
					[   18.291958] platform radeon_cp.0: firmware: agent loaded radeon/VERDE_mc.bin into memory<br/> \
					[   19.796422] [drm] PCIE GART of 512M enabled (table at 0x0000000000040000).<br/> \
					[   19.796542] radeon 0000:01:00.0: WB enabled<br/> \
					[   19.796547] [drm] fence driver on ring 0 use gpu addr 0x80000c00 and cpu addr 0xffff8802551d2c00<br/> \
					[   19.796551] [drm] fence driver on ring 1 use gpu addr 0x80000c04 and cpu addr 0xffff8802551d2c04<br/> \
					[   19.796555] [drm] fence driver on ring 2 use gpu addr 0x80000c08 and cpu addr 0xffff8802551d2c08<br/> \
					[   19.796561] [drm] Supports vblank timestamp caching Rev 1 (10.10.2010).<br/> \
					[   19.796563] [drm] Driver supports precise vblank timestamp query.<br/> \
					[   19.796617] radeon 0000:01:00.0: irq 45 for MSI/MSI-X<br/> \
					[   19.796624] radeon 0000:01:00.0: radeon: using MSI.<br/> \
					[   19.796668] [drm] radeon: irq initialized.<br/> \
					[   19.816613] [drm] ring test on 0 succeeded in 1 usecs<br/> \
					[   19.816619] [drm] ring test on 1 succeeded in 1 usecs<br/> \
					[   19.816625] [drm] ring test on 2 succeeded in 1 usecs<br/> \
					[   19.816967] [drm] ib test on ring 0 succeeded in 0 usecs<br/> \
					[   19.816992] [drm] ib test on ring 1 succeeded in 0 usecs<br/> \
					[   19.817020] [drm] ib test on ring 2 succeeded in 0 usecs<br/> \
					[   19.839264] [drm] Radeon Display Connectors<br/> \
					[   19.846962] [drm] Internal thermal controller with fan control<br/> \
					[   19.847070] [drm] radeon: power management initialized<br/> \
					[   19.847219] No connectors reported connected with modes<br/> \
					[   19.847223] [drm] Cannot find any crtc or sizes - going 1024x768<br/> \
					[   19.849095] [drm] fb mappable at 0xA1143000<br/> \
					[   19.849098] [drm] vram apper at 0xA0000000<br/> \
					[   19.849100] [drm] size 3145728<br/> \
					[   19.849102] [drm] fb depth is 24<br/> \
					[   19.849104] [drm]    pitch is 4096<br/> \
					[   19.856152] Console: switching to colour frame buffer device 128x48<br/> \
					[   19.861585] fb0: radeondrmfb frame buffer device<br/> \
					[   19.861587] drm: registered panic notifier<br/> \
					[   19.861607] [drm] Initialized radeon 2.16.0 20080528 for 0000:01:00.0 on minor 0<br/> \
					[   19.862002] i915 0000:00:02.0: setting latency timer to 64<br/> \
					[   19.929004] mtrr: no more MTRRs available<br/> \
					[   19.929008] [drm] MTRR allocation failed.  Graphics performance may suffer.<br/> \
					[   19.929307] i915 0000:00:02.0: irq 46 for MSI/MSI-X<br/> \
					[   19.929315] [drm] Supports vblank timestamp caching Rev 1 (10.10.2010).<br/> \
					[   19.929318] [drm] Driver supports precise vblank timestamp query.<br/> \
					[   19.929425] [drm:intel_dsm_platform_mux_info] *ERROR* MUX INFO call failed<br/> \
					[   19.929511] vga_switcheroo: enabled<br/> \
					[   19.929654] radeon atpx: version is 1<br/> \
					[   19.930205] vgaarb: device changed decodes: PCI:0000:01:00.0,olddecodes=io+mem,decodes=none:owns=none<br/> \
					[   19.930212] vgaarb: device changed decodes: PCI:0000:00:02.0,olddecodes=io+mem,decodes=none:owns=io+mem<br/> \
					')
	  }

	  function typeconsole (){

	  	setTimeout(function(){
	  		$('#terminal').empty()
	  	}, 1000);

	 	$('#terminal')
	        .typist('wait', '300')
	        .typist('print', '[ 0.000000] Linux version 3.2.0-4-amd64 (gcc version 4.6.3 (Debian 4.6.3-14) ) #1 SMP Debian 3.2.51-1')
	        .typist('wait', '100')
	        .typist('print', '[ 0.000024] Command line: BOOT_IMAGE=/boot/vmlinuz-3.2.0-4-amd64 root=UUID=abb4b6af-3442-4574-8790-16e6ca0a10ef ro quiet')
	        .typist('wait', '100')
	        .typist('print', '[ 0.000157] Booting paravirtualized kernel on bare hardware')
	        .typist('wait', '100')
	        .typist('print', '[ 0.000533] Dentry cache hash table entries: 1048576 (order: 11, 8388608 bytes)')
	        .typist('wait', '100')
	        .typist('print', '[ 0.004348] Starting apache2 web server')
	        .typist('wait', '100')
	        .typist('print', '[ 0.005398] PPP: version 2.3.3 (demand dialling)')
	        .typist('wait', '50')
	        .typist('print', '[ 0.005452] PPP line discipline registered.')
	        .typist('wait', '100')
	        .typist('print', '[ 0.005752] eth0: MACE at 00:0f:02:10:2a:6d, chip revision 9.64')
	        .typist('wait', '100')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh root@nsa.gov')
	        .typist('wait', '200')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	        .typist('prompt')
	        .typist('wait', '1000')
	        .typist('type', 'ssh fbi.gov')
	        .typist('print', 'Enter username:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', 'root')
	        .typist('wait', '150')
	        .typist('print', 'Enter password:')
	        .typist('promptecho')
	        .typist('wait', '200')
	        .typist('type', '********')
	        .typist('wait', '400')
	        .typist('print', 'Access denied!')
	  }

	  if(!ismob){
	  	typeconsole(typeboot());
	  }

});