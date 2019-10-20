$(document).ready(function(){ 

    if($('body').hasClass('mail')){

        $('.mail-reply').on('click', function(){

            $('.reply-area').show();

            $('.reply-area textarea').focus();

            $('.wysiwyg').on('click', function(){

                $.getScript("js/jquery.wysiwyg.js", function(){

                    if(toggleEditor($('.wysiwyg'))){

                        openEditor('reply');
                        
                    }

                })
                
            })

            $('.reply-rules').show();

        });

        $('.mail-delete').on('click', function(){

            var id = $(this).attr('value');
            var outbox = false;

            if($(this).hasClass('from-sent')){
                outbox = true;
            }

            openDeleteModal({id:id,outbox:outbox});

        });

        function openDeleteModal(opts){

            var title = "Delete Message";
            var text = "Are you sure you want to delete this message?<br/>This action can not be undone.";

            if(opts.outbox){
                text += "<br/><br/><font color='red'>Please note the recepient will be able to see this message even if you delete it.</font>";
            }

            openModal({title:title,text:text,btn:'<input type="submit" class="btn btn-primary" value="Delete">',input:generateModalInput([["act", 'delete'], ['id', opts.id]])});

        }

        if($('body').hasClass('new')){

            $.getScript("js/jquery.wysiwyg.js", function(){

                openEditor('new');

                $('.wysiwyg').on('click', function(){

                    if(toggleEditor($(this))){

                        openEditor('new-focus');

                    }

                })

            })
            
        }

    }

    if($('body').hasClass('index')){

        $('.header-ip').css('margin-top','10px');

        $('.header-info').html('<span class="small nomargin">Uptime: '+indexdata.up+'</span><br/>'+indexdata.pass+' <span class="small nomargin change-pwd link">[ '+indexdata.chg+' ]</span><div style="float: right;">&nbsp;<span class="online"></span></div>');
        
        $('.reputation').html(indexdata.rep+'<span class="small">Ranked #'+indexdata.rid+'</span>');

        $('.change-pwd').on('click', function(){

            $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {func: 'getPwdInfo'}, 
            success:
                function(data) {
                    if(data.status == 'OK'){                       

                        var pwdInfo = $.parseJSON(data.msg)

                        openModal({title:pwdInfo[0].title,text:pwdInfo[0].text,btn:pwdInfo[0].btn,input:generateModalInput([["act","changepwd"]])})

                        if(pwdInfo[0].select2){

                            getBankAcc();

                        }

                    }

                }                     
            });

        });

    }

    if($('body').hasClass('hardware')){

        $('.upgrade-part').on('click', function(){
            
            var power = $(this).attr('id');
            var part = $(this).attr('value');

            var id = $('#'+part+power).attr('class');
            var price = $('#'+part+power+' #price').text();

            openPartModal({id:id,part:part,power:power,price:price});
                    
        });

        function openPartModal(opts){

            opts.price = opts.price.replace(',', '');

            $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {func: 'getPartModal', opts:opts}, 
            success:
                function(data) {

                    var modalInfo = $.parseJSON(data.msg)

                    var internet = "";

                    if($('body').hasClass('internet')){
                        internet = '<input type="hidden" name="clan" value="1">';
                    }


                    openModal({title:modalInfo[0].title,text:modalInfo[0].text,btn:modalInfo[0].btn,input:generateModalInput([["act", opts.part], ["part-id", opts.id], ["price", opts.price]])+internet})

                    if(modalInfo[0].text.indexOf('desc-money') !== -1){
                        getBankAcc();
                    }

                }
            });




        }

    }

    if($('body').hasClass('hackeddb')){

        if($('body').hasClass('collect')){

            getBankAcc();

        } else {

            var updateInterval = 60000;

            function print(){

                for(x = 0; x < virTime.length; x++){
                    
                    $('#v'+x).html('<span id="time">'+toString(virTime[x])+'</span>');
                    
                    virTime[x] += updateInterval / 1000
                    
                }
                
                setTimeout(print, updateInterval);
                
            }
            
            function toString(ts){
                
                var interval;

                interval = Math.floor(ts / 2592000);
                if (interval > 1) {
                    return interval+" months";
                } else if(interval == 1){
                    return interval+" month";
                }

                interval = Math.floor(ts / 86400);
                if (interval > 1) {
                    return interval+" days";
                } else if(interval == 1){
                    return interval+" day";
                }

                interval = Math.floor(ts / 3600);
                if (interval > 1) {
                    return interval+" hours";
                } else if(interval == 1){
                    return interval+" hour";
                }

                interval = Math.floor(ts / 60);
                if (interval > 1) {
                    return interval+" minutes";
                } else if(interval == 1) {
                    return interval+" minute";
                }

                interval = Math.floor(ts);
                if(interval > 1){
                    return interval+" seconds";
                } else {
                    return "now";
                }
                
            }

            if($('#list').hasClass('ip')){

                if(virTime.length > 0){

                    print();

                }

            }

            $('.delete-acc').on('click', function(){
                
                var bid = $(this).attr('id');
                var b = $('#b'+bid+' #acc').text();
                        
                openDeleteDBModal({id:bid,ip:false,info:b,list:false});
                        
            });

            $('.delete-ip').on('click', function(){
                        
                var vid = $(this).attr('id');
                var vip = $('#l'+vid+' #ip').text();
                var v = $('#l'+vid+' #vname').text();

                openDeleteDBModal({id:vid,ip:vip,info:v,list:true});
                        
            });


            function openDeleteDBModal(opts){

                var title = "Remove ";
                var text = "Are you sure you want to remove ";
                var act;
                
                if(opts.list){
                    act = "deleteip";
                    title += "IP";
                    text += "IP <strong>"+opts.ip+"</strong> from the database?<br/>";
                    if(opts.info){
                        text += "Virus "+opts.info+" is active, working time will be nulled."
                    }
                } else {
                    act = "deleteacc";
                    title += "bank account";
                    text += "account <strong>#"+opts.info+"</strong> from the database?";
                }
                
                title += " from hacked DB";
                        
                openModal({title:title,text:text,btn:'<input type="submit" class="btn btn-primary" value="Remove">',input:generateModalInput([["act", act], ["id", opts.id]])});
                
            }

            $('.manage-ip').on('click', function(){
                        
                var vid = $(this).attr('id');
                var vip = $('#l'+vid+' #ip').text();
                var v = $('#l'+vid+' #vname').text();
                var wtime = $('#l'+vid+' .list-time').text();

                openManageModal({id:vid,ip:vip,v:v,time:wtime});
                        
            });

            function openManageModal(opts){
                
                var title = "Manage "+opts.ip 
                var text = "";
                var act = 'assign';
                var btn = '<span id="btn" class="btn btn-primary" data-dismiss="modal" class="close">Ok</span>';

                if(opts.v){

                    var ext = opts.v.substr(-5);
                    
                    text += "Active virus: <strong>"+opts.v+"</strong><br/>";
                    if(ext != 'vddos'){
                        text += "Working time: "+opts.time+"<br/>";
                    }
                    text += '<br/><div id="loading"><img src="img/ajax-virus.gif"> Loading...</div><input type="hidden" id="assignSelect"><input type="hidden" id="virus-id" name="vid">';

                    openModal({title:title,text:text,input:generateModalInput([["act", act], ['lid', opts.id]]),btn:btn});
                    
                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'manageViruses', id: opts.id, ip:opts.ip}, 
                    success:
                        function(data) {

                            if(data.status == 'OK'){          
                               
                               if(data.msg){
                            
                                    $('#btn').replaceWith('<input type="submit" class="btn btn-primary" value="Assign new virus">');

                                    $.getScript("js/select2.js", function(){

                                        $('#assignSelect').select2({

                                            placeholder: 'Assign new virus...',
                                            width: '300',
                                            data: $.parseJSON(data.msg)

                                        });

                                        $('#loading').hide();

                                    });

                                }
                                                      
                            } else {
                                $('#loading').replaceWith('<span class="red">Ops, an error happened! Please, try again in a few minutes.</span>')
                            }
            
                        }                     
                    });
                    
                    $('#assignSelect').on("change", function() {    
                 
                        $('#virus-id').prop('value', $(this).val());

                    });
                    
                }
                                        
            }

        }

    }

    if($('body').hasClass('file-actions')){

        function loadFileAct(type, action, id){

            id = id || '0';

            var remote = 0;

            if($('body').hasClass('internet')){
                remote = 1;
            }

            $.ajax({
                type: "POST",
                url: 'ajax.php',
                dataType: "json",
                data: {func: 'getFileActionsModal', type:type, remote:remote, action:action, id:id},
                 success:function(data) {
                     if(data.status == 'OK'){
                        
                        modalInfo = $.parseJSON(data.msg);

                        openModal({title:modalInfo[0].title,text:modalInfo[0].text,btn:modalInfo[0].btn,input:generateModalInput([["act", action+"-"+type],["id", id]])});

                        $('.name').focus();

                        $('#modal-form').validate({
                            rules:{
                                name: "required",
                                text: "required"
                            },
                            validClass: "success",
                            errorClass: "error",
                            errorPlacement: function(){},
                            highlight:function(element, errorClass, validClass) {
                                $(element).parents('.control-group').addClass(errorClass);
                            },
                            unhighlight: function(element, errorClass, validClass) {
                                if($(element).parents('.control-group').hasClass(errorClass)){
                                    $(element).parents('.control-group').removeClass(errorClass);
                                    $(element).parents('.control-group').addClass(validClass);
                                }
                            }
                        });

                        if(type == 'text'){
                            $('.text-show-editor').on('click', function(){
                                $.getScript("js/jquery.wysiwyg.js", function(){
                                    openEditor('text');
                                    $('textarea#wysiwyg').parent().css('margin-left','9%');
                                })
                            })
                        }

                    } 

                }     

            });

        }

        $('.create-txt').on('click', function(){

            loadFileAct('text', 'create');

        });

        $('.create-folder').on('click', function(){

            loadFileAct('folder', 'create');

        });

        $('.edit-txt').on('click', function(){

            loadFileAct('text', 'edit', $('#txt-id').attr('value'));

        });

        $('.edit-folder').on('click', function(){

            loadFileAct('folder', 'edit', $('#folder-id').attr('value'));

        });

        $('.delete-folder').on('click', function(){

            loadFileAct('folder', 'delete', $(this).attr('value'));

        });

        $('.delete-ddos').on('click', function(){

            $.ajax({
                type: "POST",
                url: 'ajax.php',
                dataType: "json",
                data: {func: 'deleteDdos'},
                 success:function(data) {
                     if(data.status == 'OK'){
                        
                        modalInfo = $.parseJSON(data.msg);

                        openModal({title:modalInfo[0].title,text:modalInfo[0].text,btn:modalInfo[0].btn,input:generateModalInput([["act", "delete-ddos"]])});

                    }

                }     

            });

        });

        if($('#softwarebar').length > 0){

            var barTop = $('#softwarebar').offset().top;
            var barLeft = $('#softwarebar').offset().left;

            if (barTop > 300) {

    	        $(window).scroll(function() {
    	            var currentScroll = $(window).scrollTop();
    	            if (currentScroll >= barTop) {
    	                $('#softwarebar').css({
    	                    position: 'fixed',
    	                    top: '0',
    	                    'text-align': 'center',
    	                    'margin-left': '2%',
                            'margin-top': '15px'
    	                });
    	            } else {
    	                $('#softwarebar').css({
    	                    position: 'static',
    	                    'margin-left': '0px'
    	                });
    	            }
    	        });        

        	}

        }

        $('<link rel="stylesheet" type="text/css" href="css/tipTip.css" >').appendTo("head");
        $.getScript("js/jquery.tipTip.js", function(){

            $(".tip-top").tipTip({
                delay: 0,
                maxWidth: "150px",
                edgeOffset: 10,
                defaultPosition: "top"
            });

        });
        
    }

    if($('body').hasClass('internet')){

        if($('body').hasClass('upload')){

            $('#link').on('click', function (){
            
                $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'gettext', id:'loadsoft'}, 
                success:
                    function(data) {                        

                        langInfo = $.parseJSON(data.msg);

                        $('#toBeHidden').children().replaceWith(langInfo[0].loading);


                        jQuery.ajax({
                            type: "POST",
                            url: 'ajax.php',
                            dataType: "json",
                            data: {func: 'loadSoftware'},
                             success:function(data) {
                                 if(data.status == 'OK'){
                                    
                                    $('#toBeHidden').hide();

                                    function format(item){
                                        return item.tag
                                    }

                                    $.getScript("js/select2.js", function(){

                                        $('#uploadSelect').select2({

                                            placeholder: langInfo[0].placeholder,
                                            data: $.parseJSON(data.msg),
                                            escapeMarkup: function (m) { return m; } //display html inside select2

                                        });
                                    
                                    });  

                                    $('#uploadForm').html('<br/><input type="submit" value="Upload" class="btn btn-primary">')
                                    
                                    $('#uploadSelect').on("change", function() {  

                                        $('#upload-id').prop('value', $(this).val());

                                    });
                                     
                                 } else {
                                    $('#toBeHidden').replaceWith('<span class="red">Ops, an error happened! Please, try again in a few minutes. </span>');
                                 }
                                 
                             }
                        });

                    }

                });
            
            });

        }

        if($('body').hasClass('history')){

            if(!$('body').hasClass('tutorial')){

                if(typeof(tellpz) !== 'undefined'){
                    gritterNotify({title:pztitle,text:pzdesc,img:'',sticky:true});
                }

                setTimeout(function(){
                    if(typeof(telldc) !== 'undefined'){
                        gritterNotify({title:dctitle,text:dcdesc,img:'',sticky:true});
                    }
                }, 1000);

            }

            function doLoad(callback) {
                jQuery.ajax({
                    type: "POST",
                    url: 'ajax.php',
                    dataType: "json",
                    data: {func: 'loadHistory'},
                     success:function(data) {
                         if(data.status == 'OK'){
                             callback(data.msg)
                         } else {
                             callback("[{ip:'Error loading history.'}]");
                         }
                     }
                })   
            };
            
            function load(){

                doLoad(
            
                    function(visited){
                        
                        list(visited);
                        
                    }

                );
                   
            }
            
            function list(visitedJSON){

                var visited = $.parseJSON(visitedJSON);
                
                var content = "";
                var now = new Date();
                var d, diff, x;

                for (x = visited.length; x != 0; x--) {

                    content += '<a href="internet?ip='+visited[x - 1].ip+'">'+visited[x - 1].ip+'</a><span id="visit'+x+'">';

                    d = new Date(visited[x - 1].time)
                    now.setTime(now.getTime() + (now.getTimezoneOffset())*60*1000);
                    diff = (now.getTime() - d.getTime())/1000;

                    content += timeString(diff);

                    content += "</span><br>";
                }

                $('#visited-ips').html(content);        
                        
                setInterval(
                    function(){


                        var now = new Date();
                        var content = "";
                        
                        for (var x = visited.length; x > 0; x--) {
                            
                            d = new Date(visited[x - 1].time)
                            now.setTime(now.getTime() + (now.getTimezoneOffset())*60*1000);
                            diff = (now.getTime() - d.getTime())/1000;
                                                
                            content = timeString(diff);

                            $('#visit'+x).html(content);

                        }

                    },
                    14000
                );
                
            }

            load();

            function timeString(ts){

                var htmlf = '<span class="small">';
                var hidef = '<span class="hide1024">';
                var htmle = '</span></span>';
                var interval;

                interval = Math.floor(ts / 2592000);
                if (interval > 1) {
                    return htmlf+interval+" mo"+hidef+"nths"+htmle;
                } else if(interval == 1){
                    return htmlf+interval+" mo"+hidef+"nth"+htmle;
                }

                interval = Math.floor(ts / 86400);
                if (interval > 1) {
                    return htmlf+interval+" d"+hidef+"ays"+htmle;
                } else if(interval == 1){
                    return htmlf+interval+" d"+hidef+"ay"+htmle;
                }

                interval = Math.floor(ts / 3600);
                if (interval > 1) {
                    return htmlf+interval+" h"+hidef+"ours"+htmle;
                } else if(interval == 1){
                    return htmlf+interval+" h"+hidef+"our"+htmle;
                }

                interval = Math.floor(ts / 60);
                if (interval > 1) {
                    return htmlf+interval+" m"+hidef+"inutes"+htmle;
                } else if(interval == 1) {
                    return htmlf+interval+" m"+hidef+"inute"+htmle;
                }

                interval = Math.floor(ts);
                if(interval > 1){
                    return htmlf+interval+" s"+hidef+"econds"+htmle;
                } else {
                    return htmlf+"now"+htmle;
                }

            }

        }

        if($('body').hasClass('money')){

	        $.getScript("js/jquery.maskmoney.js", function(){

				$('#money').maskMoney({
					precision: 0,
					prefix: '$'
				});

				$('#money').maskMoney('mask', $('#money').attr('value'));
	        
	        });

            $('#bchgpwd').on('click', function(){

                $.ajax({
                    type: "POST",
                    url: 'ajax.php',
                    dataType: "json",
                    data: {func: 'bankChangePass'},
                     success:function(data) {
                         if(data.status == 'OK'){
                            
                            modalInfo = $.parseJSON(data.msg);

                            openModal({title:modalInfo[0].title,text:modalInfo[0].text,btn:modalInfo[0].btn,input:generateModalInput([["int-act", 'changepass'],["id", id]])});

                        }

                    }     

                });

            });

            $('#bendacc').on('click', function(){

                $.ajax({
                    type: "POST",
                    url: 'ajax.php',
                    dataType: "json",
                    data: {func: 'bankCloseAcc'},
                     success:function(data) {
                         if(data.status == 'OK'){
                            
                            modalInfo = $.parseJSON(data.msg);

                            openModal({title:modalInfo[0].title,text:modalInfo[0].text,btn:modalInfo[0].btn,input:generateModalInput([["int-act", 'closeacc']])});

                        }

                    }     

                });

            });

        }

    }

    if($('body').hasClass('university')){

	    if($('body').hasClass('certification')){

	        if($('body').hasClass('learn')){

	        } else {
	            
	            function generateCertificationDiv(opts, lang){

	                var collapsible = '';
	                var collapseDivOpen = '';
	                var collapseDivClose = '';
	                var topHTML = '';
	                var botHTML = '';
	                var img = '';
	                var text = '';
	                var label = '';

	                $var = $('#cert'+opts.cert);

                    switch(opts.cert){
                        case '1':
                            img = 'img/ubuntu.png';
                            opts.name = lang.c1;
                            opts.desc = lang.d1;
                            break;
                        case '2':
                            img = 'img/debian.png';
                            opts.name = lang.c2;
                            opts.desc = lang.d2;
                            break;
                        case '3':
                            img = 'img/fedora.png';
                            opts.name = lang.c3;
                            opts.desc = lang.d3;
                            break;
                        case '4':
                            img = 'img/redhat.png';
                            topHTML = '<div class="row-fluid" style="text-align: center;"><div class="span12 center" style="text-align: left;"><div class="span2"></div>';
                            opts.name = lang.c4;
                            opts.desc = lang.d4;
                            break;
                        case '5':
                            img = 'img/slack.png';
                            botHTML = '</div></div>';
                            opts.name = lang.c5;
                            opts.desc = lang.d5;
                            break;     
                    }

	                if($var.hasClass('complete')){

	                    collapsible = 'collapsible'
	                    collapseDivOpen = '<div class="collapse" id="certf'+opts.cert+'"">';
	                    collapseDivClose = '</div>';
	                    text = lang.c;
	                    label = '<span class="label label-success hide1024">'+lang.c_label+'</span>';

	                    if(opts.cert == 5){
	                        $('.cert-complete').replaceWith(lang.c_all);
	                    }

	                } else {

	                    if($var.hasClass('buy')){

	                        var price = $var.attr('value');

	                        text = '<span class="btn btn-primary buycert" id="buy" value="'+opts.cert+'">';

	                        if(price == 0){
	                            text += lang.cert_free;
	                        } else {
	                            text += lang.cert_paid+price;
	                        }

	                        text += '</span>';

	                    } else if($var.hasClass('learning')) {

	                        text = '<span class="btn btn-success" id="learn" value="'+opts.cert+'">'+lang.take+'</span>';
	                        label = '<span class="label label-warning hide1024">'+lang.learning+'</span>';

	                    } else {
	                        text = lang.locked;
	                    }

	                }

	                var h = '\
	                '+topHTML+'\
	                <div class="span4 cert'+opts.cert+'">\
	                    <div class="widget-box" style="text-align: left;">\
	                        <div class="widget-title">\
	                            <a href="#certf'+opts.cert+'" data-toggle="collapse">\
	                                <span class="icon"><img src="'+img+'"></span>\
	                                <h5>'+opts.name+'</h5>\
	                                '+label+'\
	                            </a>\
	                        </div>\
	                        '+collapseDivOpen+'\
	                        <div class="widget-content padding">\
	                            '+opts.desc+'<br/><br/>\
	                            '+text+'\
	                        </div>\
	                        '+collapseDivClose+'\
	                    </div>\
	                </div>\
	                '+botHTML+'\
	                ';

	                return h;

	            };

                $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'gettext', id:'certdiv'}, 
                success:
                    function(data) {                        

                        langInfo = $.parseJSON(data.msg);

                        $.getJSON('json/certs.json', function(data){

                            var html = '';

                            $.each(data, function(key){

                                html += generateCertificationDiv({cert:key,name:data[key][0]['name'],desc:data[key][0]['desc'],price:data[key][0]['price']}, langInfo[0])

                            });

                            $('#certs').html(html);

                        });

                    }

                });

	            $('#certs').on('click', '.buycert', function(){
	                
                    var certid = $(this).attr('value');

                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'gettext', id:'certbuy', info:certid}, 
                    success:
                        function(data) {

                            langInfo = $.parseJSON(data.msg);

                            openModal({title:langInfo[0].title,text:langInfo[0].text,input:generateModalInput([["act", "buy"], ["id", certid]]),btn:langInfo[0].btn});

                            if (langInfo[0].p == 0){
                                $('#modal-form').submit();
                                return;
                            }

                            if(langInfo[0].text.indexOf('desc-money') !== -1){
                                getBankAcc();
                            }

                        }

                    });

	            });
	        
	            $('#certs').on('click', '#learn', function(){
	                
	                window.location.replace(window.location.href+'&learn='+$(this).attr('value'));

	            });

	        }

	    }

	    if($('body').hasClass('research')){

	    	if($('body').hasClass('selected')){

		    	$('#research').on('click', function(){

		    		$('#research-area').show();
		    		$('#research').addClass('disabled');

					$('<link rel="stylesheet" type="text/css" href="css/select2.css" >').appendTo("head");
		    		$.getScript('js/select2.js', function(){
		    			$('#select-bank-acc').select2();
		    		});

		    	});

	    	} else {

				$('<link rel="stylesheet" type="text/css" href="css/select2.css" >').appendTo("head");
	    		$.getScript('js/select2.js', function(){
	    			$('#research-list').select2({
	    				width: '80%'
	    			});
	    		});

			    $('#research-list').on('change', function(){

			    	window.location = "?id="+$(this).val();

			    });	    		

			    $('#research-switch').on('click', function(){

			    	if($('#research-switch').hasClass('all')){
			    		$('#research-switch').removeClass('all').addClass('cur');
			    		$('#research-switch').html('Current round');
			    		var round = 'cur';
			    	} else {
			    		$('#research-switch').addClass('all');
			    		$('#research-switch').html('All-time');
			    		var round = 'all';			    		
			    	}

	                jQuery.ajax({
	                    type: "POST",
	                    url: 'ajax.php',
	                    dataType: "json",
	                    data: {func: 'getResearchStats', round: round},
	                     success:function(data) {

	                     	if(data.status == 'OK'){
	                     		
	        					info = data.msg.split('#');

	                     		$('#research-side-money').html(info[0])
	                     		$('#research-side-count').html(info[1])
	                     		$('#research-side-rank').html(info[2])

	                     	}
	                     }
	                })

			    });

	    	}

	    }

	}

    if($('body').hasClass('software')){

        if($('body').hasClass('id')){

            $('#buy-license').on('click', function(){
                
                $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'buyLicense', id: $('#buy-license').attr('value')}, 
                success:
                    function(data) {
                        if(data.status == 'OK'){

                            modalInfo = $.parseJSON(data.msg);

                            openModal({title:modalInfo[0].title,text:modalInfo[0].text,btn:modalInfo[0].btn,input:generateModalInput([["act", "buy-license"],["id", $('#buy-license').attr('value')]])});

                            if(modalInfo[0].canBuy){

                                getBankAcc();

                            }

                        }

                    }
                });  

            });

        }

        if($('body').hasClass('external')){

            $('#link').on('click', function (){
            
                jQuery.ajax({
                    type: "POST",
                    url: 'ajax.php',
                    dataType: "json",
                    data: {func: 'loadSoftware', external: '1'},
                     success:function(data) {
                         if(data.status == 'OK'){
                            
                            $('#link').hide();

                            function format(item){
                                return item.tag
                            }

                            $.getScript("js/select2.js", function(){

                                $('#uploadSelect').select2({

                                    placeholder: 'Choose a software...',
                                    data: $.parseJSON(data.msg)

                                });            
                            
                            });  

                            $('#uploadForm').html('<br/><input type="submit" value="Upload" class="btn btn-primary">')
                            
                            $('#uploadSelect').on("change", function() {  

                                $('#upload-id').prop('value', $(this).val());

                            });
                             
                         } else {
                            alert("Error while loading the softwares. Please, try again.")
                         }
                         
                     }
                })
            
            });

        }

    }

    if($('body').hasClass('folder')){

        $('#link').on('click', function (){
        
            if($('body').hasClass('internet')){
                var remote = 1;
            } else {
                var remote = 0;
            }

            jQuery.ajax({
                type: "POST",
                url: 'ajax.php',
                dataType: "json",
                data: {func: 'loadSoftware', folder: '1', remote: remote},
                 success:function(data) {
                     if(data.status == 'OK'){
                        
                        $('#link').hide();

                        function format(item){
                            return item.tag
                        }

                        $.getScript("js/select2.js", function(){

                            $('#uploadSelect').select2({

                                placeholder: 'Choose a software...',
                                data: $.parseJSON(data.msg)

                            });
                        
                        });  

                        $('#uploadForm').html('<br/><input type="hidden" name="act" value="move-folder"><input type="submit" value="Move" class="btn btn-primary">')
                        
                        $('#uploadSelect').on("change", function() {
                            $('#upload-id').attr('value', $(this).val());
                        });
                         
                     } else {
                        alert("Error while loading the softwares. Please, try again.")
                     }
                     
                 }
            })
        
        });

    }

    if($('body').hasClass('tutorial')){

        if($('body').hasClass('internet') && !($('body').hasClass('history'))){

            if($('body').hasClass('remove-log')){

                $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'gettext', id:'tutorial_deletelog'}, 
                success:
                    function(data) {

                        modalInfo = $.parseJSON(data.msg);

                        var title = modalInfo[0].title;
                        var text = modalInfo[0].text;

                        gritterNotify({title:title,text:text,img:'',sticky:true});

                    }

                });

            }

            if(!($('body').hasClass('83')) && !($('body').hasClass('84'))){

                $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'getTutorialFirstVictim'}, 
                success:
                    function(data) {

                        if(data.status == 'OK'){

                            if($('body').hasClass('upload')){

                                if($('.browser-bar').attr('value') == data.msg){

                                    if($('body').hasClass('80') || $('body').hasClass('81')){
                                        
                                        $.ajax({
                                        type: "POST",
                                        url: "ajax.php",
                                        data: {func: 'gettext', id:'tutorial_80'}, 
                                        success:
                                            function(data) {

                                                modalInfo = $.parseJSON(data.msg);

                                                var title = modalInfo[0].title;
                                                var text = modalInfo[0].text;

                                                gritterNotify({title:title,text:text,img:'',sticky:true});

                                            }

                                        });

                                    }

                                    $('.nav-tabs li:nth-child(1)').children().css('color','red');

                                }

                            } else if($('body').hasClass('81')) {

                                if($('.browser-bar').attr('value') == data.msg){

                                    $.ajax({
                                    type: "POST",
                                    url: "ajax.php",
                                    data: {func: 'gettext', id:'tutorial_81'}, 
                                    success:
                                        function(data) {

                                            modalInfo = $.parseJSON(data.msg);

                                            var title = modalInfo[0].title;
                                            var text = modalInfo[0].text;

                                            gritterNotify({title:title,text:text,img:'',sticky:true});

                                        }

                                    });
                                    $('#menu-mission').children().append('<span class="label">!</span>');
                                    $('body').addClass('color menu-mission')

                                }

                            }

                        }

                    }                     
                });  

            } else {

                $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'getTutorialVirusID'}, 
                success:

                    function(data) {

                        if(data.status == 'OK'){

                            var vicInfo = $.parseJSON(data.msg)

                            if($('.browser-bar').attr('value') == vicInfo[0]['ip']){

                                if($('#'+vicInfo[0]['id']).length){

                                    if($('#'+vicInfo[0]['id']).hasClass('installed')){

                                        $.ajax({
                                        type: "POST",
                                        url: "ajax.php",
                                        data: {func: 'gettext', id:'tutorial_end'}, 
                                        success:
                                            function(data) {

                                                modalInfo = $.parseJSON(data.msg);

                                                var title = modalInfo[0].title;
                                                var text = modalInfo[0].text;

                                                gritterNotify({title:title,text:text,img:'',sticky:true});

                                            }

                                        });

                                        $('#menu-mission').children().append('<span class="label">$</span>');
                                        $('#menu-mission').addClass('shown');

                                    } else {

                                        if($('body').hasClass('upload')){

                                            $.ajax({
                                            type: "POST",
                                            url: "ajax.php",
                                            data: {func: 'gettext', id:'tutorial_upload2', info:vicInfo[0]['id']}, 
                                            success:
                                                function(data) {

                                                    modalInfo = $.parseJSON(data.msg);

                                                    var title = modalInfo[0].title;
                                                    var text = modalInfo[0].text;

                                                    gritterNotify({title:title,text:text,img:'',sticky:true});

                                                }

                                            });

                                        }
                                    
                                    }

                                } else {

                                    if($('body').hasClass('upload')){

                                        $.ajax({
                                        type: "POST",
                                        url: "ajax.php",
                                        data: {func: 'gettext', id:'tutorial_upload1'}, 
                                        success:
                                            function(data) {

                                                modalInfo = $.parseJSON(data.msg);

                                                var title = modalInfo[0].title;
                                                var text = modalInfo[0].text;

                                                gritterNotify({title:title,text:text,img:'',sticky:true});

                                            }

                                        });

                                    }

                                }

                            } else {

                                $('.nav-tabs li:nth-child(3)').children().css('color','red');

                                $.ajax({
                                type: "POST",
                                url: "ajax.php",
                                data: {func: 'gettext', id:'tutorial_logout'}, 
                                success:
                                    function(data) {

                                        modalInfo = $.parseJSON(data.msg);

                                        var title = modalInfo[0].title;
                                        var text = modalInfo[0].text;

                                        gritterNotify({title:title,text:text,img:'',sticky:true});

                                    }

                                });
                            }

                        }

                    }

                });

            }

        } else {

            if($('body').hasClass('navigate')){

                var ip = $('body').attr('value');

                $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'gettext', id:'tutorial_goto_vic', info: ip}, 
                success:
                    function(data) {

                        modalInfo = $.parseJSON(data.msg);

                        var title = modalInfo[0].title;
                        var text = modalInfo[0].text;

                        gritterNotify({title:title,text:text,img:'',sticky:true});

                    }

                });

            } else if($('body').hasClass('action')){

                if($('body').hasClass('tab-hack')){

                    $('.nav-tabs .active').next().next().children().css({'color':'red','font-weight':'bold'});

                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'gettext', id:'tutorial_hacktab', info: ip}, 
                    success:
                        function(data) {

                            modalInfo = $.parseJSON(data.msg);

                            var title = modalInfo[0].title;
                            var text = modalInfo[0].text;

                            gritterNotify({title:title,text:text,img:'',sticky:true});

                        }

                    });

                } else if($('body').hasClass('hack')) {

                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'gettext', id:'tutorial_hack', info: ip}, 
                    success:
                        function(data) {

                            modalInfo = $.parseJSON(data.msg);

                            var title = modalInfo[0].title;
                            var text = modalInfo[0].text;

                            gritterNotify({title:title,text:text,img:'',sticky:true});

                        }

                    });

                } else if($('body').hasClass('login')){

                    if($('body').hasClass('83')){

                        $.ajax({
                        type: "POST",
                        url: "ajax.php",
                        data: {func: 'gettext', id:'tutorial_login1', info: ip}, 
                        success:
                            function(data) {

                                modalInfo = $.parseJSON(data.msg);

                                var title = modalInfo[0].title;
                                var text = modalInfo[0].text;

                                gritterNotify({title:title,text:text,img:'',sticky:true});

                            }

                        });

                    } else {

                        $.ajax({
                        type: "POST",
                        url: "ajax.php",
                        data: {func: 'gettext', id:'tutorial_login1', info: ip}, 
                        success:
                            function(data) {

                                modalInfo = $.parseJSON(data.msg);

                                var title = modalInfo[0].title;
                                var text = modalInfo[0].text;

                                gritterNotify({title:title,text:text,img:'',sticky:true});

                            }

                        });

                    }

                } else if($('body').hasClass('tab-login')){

                    $('.nav-tabs .active').next().children().css({'color':'red','font-weight':'bold'});

                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'gettext', id:'tutorial_login1', info: ip}, 
                    success:
                        function(data) {

                            modalInfo = $.parseJSON(data.msg);

                            var title = modalInfo[0].title;
                            var text = modalInfo[0].text;

                            gritterNotify({title:title,text:text,img:'',sticky:true});

                        }

                    });

                } else if($('body').hasClass('software')){

                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'gettext', id:'tutorial_prepare'}, 
                    success:
                        function(data) {

                            modalInfo = $.parseJSON(data.msg);

                            var title = modalInfo[0].title;
                            var text = modalInfo[0].text;

                            gritterNotify({title:title,text:text,img:'',sticky:true});

                        }

                    });


                }

            } else if($('body').hasClass('missions')){

                if($('body').hasClass('84')){

                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'gettext', id:'tutorial_collect'}, 
                    success:
                        function(data) {

                            modalInfo = $.parseJSON(data.msg);

                            var title = modalInfo[0].title;
                            var text = modalInfo[0].text;

                            gritterNotify({title:title,text:text,img:'',sticky:true});

                        }

                    });

                }

            }
        }

        if($('body').hasClass('color')){

            function colorMenu(id){

                $('#'+id+' a').css('background-color','#7D3434');
                $('#'+id+' a').hover(function(){
                    $(this).css('background-color','#532424');
                },function(){
                    $(this).css('background-color','#7D3434');
                });

            }

            if($('body').hasClass('menu-mission')){

                colorMenu('menu-mission');

            } else if($('body').hasClass('menu-software')){

                colorMenu('menu-software')

            } else if($('body').hasClass('menu-internet')){

                colorMenu('menu-internet');

                if($('body').hasClass('software') && $('body').hasClass('80')){

                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'gettext', id:'tutorial_goto_vic_80'}, 
                    success:
                        function(data) {

                            modalInfo = $.parseJSON(data.msg);

                            var title = modalInfo[0].title;
                            var text = modalInfo[0].text;

                            gritterNotify({title:title,text:text,img:'',sticky:true});

                        }

                    });
                
                } else if($('body').hasClass('83')){
                    
                    $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: {func: 'gettext', id:'tutorial_goto_vic_83'}, 
                    success:
                        function(data) {

                            modalInfo = $.parseJSON(data.msg);

                            var title = modalInfo[0].title;
                            var text = modalInfo[0].text;

                            gritterNotify({title:title,text:text,img:'',sticky:true});

                        }

                    });

                }

            }

        }

        if($('body').hasClass('highlight')){

            function highlight(id){

                $t = $('#'+id).children().siblings();
                var css = {'font-weight':'bold','color':'red'};

                $t.css(css);
                $t.children().css(css);

            }

            var id = $('body').attr('value');

            highlight(id);

            $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {func: 'gettext', id:'tutorial_install_cracker', info: id}, 
            success:
                function(data) {

                    modalInfo = $.parseJSON(data.msg);

                    var title = modalInfo[0].title;
                    var text = modalInfo[0].text;

                    gritterNotify({title:title,text:text,img:'',sticky:true});


                }

            });

        }

    }

    if($('body').hasClass('missions')){

        $('.mission-abort').on('click', function(){

            $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {func: 'gettext', id:'abort'}, 
            success:
                function(data) {

                    modalInfo = $.parseJSON(data.msg);

                    var title = modalInfo[0].title;
                    var text = modalInfo[0].text;
                    var btn = modalInfo[0].btn;

                    openModal({title:title,text:text,input:generateModalInput([["act", "abort"],["mid", $('.mission-abort').attr('value')]]),btn:btn});


                }

            });


        });

        $('.mission-accept').on('click', function(){

            $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {func: 'gettext', id:'accept_m'}, 
            success:
                function(data) {

                    modalInfo = $.parseJSON(data.msg);

                    var title = modalInfo[0].title;
                    var text = modalInfo[0].text;
                    var btn = modalInfo[0].btn;

                    openModal({title:title,text:text,input:generateModalInput([["act", "accept"],["mid", $('.mission-accept').attr('value')]]),btn:btn});


                }

            });

        });

        $('.mission-complete').on('click', function(){

            $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {func: 'gettext', id:'m_completed_inform', info:$(this).attr('value')}, 
            success:
                function(data) {

                    modalInfo = $.parseJSON(data.msg);

                    var title = modalInfo[0].title;
                    var text = modalInfo[0].text;
                    var btn = modalInfo[0].btn;

                    if($('#m3-amount').length){
                        var input = generateModalInput([["act", 'complete'],["amount",$('#amount-input').val()]])
                    } else {
                        var input = generateModalInput([["act", 'complete']]);
                    }

                    openModal({title:title,text:text,btn:btn,input:input})

                    getBankAcc();

                }

            });
            
        });

        if($('#m3-amount').length){

            $.getScript("js/jquery.maskmoney.js", function(){

                $('#amount-input').maskMoney({
                    precision: 0,
                    prefix: '$'
                });
            
            });

            $('#m3-amount').submit(false);
        }

    }

    if($('body').hasClass('profile')){
        
        if($('body').hasClass('view')){        

            try {
                if(fr == 1){
                    $('.add-friend').hide()
                }
            } catch(e) {
                $('.add-friend').hide()
            }

            $('<link rel="stylesheet" type="text/css" href="css/tipTip.css" >').appendTo("head");
            $.getScript("js/jquery.tipTip.js", function(){

                $(".profile-tip").tipTip({
                    delay: 0,
                    maxWidth: "150px",
                    edgeOffset: 10
                });

            });

            $('.profile-tip').on('click', function(){

                $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {func: 'getBadge', badgeID: $(this).attr('value'), userID: uid}, 
                success:
                    function(data) {

                        modalInfo = $.parseJSON(data.msg);

                        var title = modalInfo[0].title;
                        var text = modalInfo[0].text;

                        openModal({title:title,text:text,btn:'<input type="submit" data-dismiss="modal" class="btn btn-info" value="Ok">',input:''})

                    }

                });
                
            });

        }

    }

    if($('body').hasClass('page-log')){
        $('#log').attr('spellcheck', 'false');
    }

    if($('body').hasClass('ranking')){

        if($('body').hasClass('r-user')){

            $('.r-premium').html('<span class="pull-right he16-premium" title="Premium user">')
            $('.r-online').html('<span style="margin-left: 10px;" class="pull-right he16-ranking_online" title="Online now"></span>')

        } else if($('body').hasClass('r-clan')){

            $('.r-war').html('<span class="label label-important pull-right" style="margin-right: 3px;">War</span>')

        }

    }

    if($('body').hasClass('pie')){
    	
        $.getScript("js/pie.js");

    }

    if($('#notify-mission').length && !($('body').hasClass('missions'))){

        $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {func: 'gettext', id:'m_completed_notify'}, 
        success:
            function(data) {

                modalInfo = $.parseJSON(data.msg);

                var title = modalInfo[0].title;
                var text = modalInfo[0].text;

                gritterNotify({title:title,text:text,img:'',sticky:true});


            }

        });

        
        $('#menu-mission').children().append('<span class="label">$</span>');
        $('#menu-mission').addClass('shown');

    }

    if($('body').hasClass('legal')){

        windowSize = $(window).height();
        if(windowSize < 600){
            windowSize = 600;
        }

        $('#legalframe').css('height',windowSize-450+'px');

    }

    if($('body').hasClass('clan')){

        if($('body').hasClass('admin')){

            $('.edit-clan-desc').on('click', function(){                

                $.getScript("js/jquery.wysiwyg.js", function(){

                    openEditor('clan');

                });

                $('#clan-desc').show();
                $('.edit-clan-desc').hide();
                $('#save-desc').show();

            });

        }

    }

    if($('body').hasClass('payment')){

        $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {func: 'getBRLvalue'}, 
        success:
            function(data) {

                $('#brl-value').html(data.msg);

            }

        });

        $('#ccform').validate({
            rules:{
                name: "required",
                ccnumber: {
                    required: true,
                    number: true,
                    creditcard: true,
                },
                cvv: {
                    required: true,
                    number: true,
                    rangelength: [3,3]
                },
                compliance: "required"
            },
            validClass: "success",
            errorClass: "error",
            errorPlacement: function(){},
            highlight:function(element, errorClass, validClass) {
                if($(element).parents('.control-group').hasClass(validClass)){
                    $(element).parents('.control-group').removeClass(validClass);
                }
                $(element).parents('.control-group').addClass(errorClass);
            },
            unhighlight: function(element, errorClass, validClass) {
                if($(element).parents('.control-group').hasClass(errorClass)){
                    $(element).parents('.control-group').removeClass(errorClass);
                }
                if(!$(element).hasClass('skip')){
                    $(element).parents('.control-group').addClass(validClass);
                }
            },
            submitHandler: function(form) {
                $('#ccsubmit').attr('disabled','disabled');
                $('#ccsubmit').text('Processing...');
                $("#ccform").submit();
            }
        });

    }

    if($('body').hasClass('settings')){

                                    $.getScript("js/select2.js", function(){

                                        $('#select-lang').select2({
                                        });

                                    });

    }

    //ALL-PAGE-COMMON

    function fixSidebar(){
        var scrolledDown = false;
        var currentScroll = $(window).scrollTop();
        if(currentScroll > 79){
            $('#sidebar').css({
                position: 'fixed',
                top: '0',
            });
        } else {
            $('#sidebar').css({
                position: 'relative'
            });
        }
    }

    function ismob() {
        if(window.innerWidth <= 800 && window.innerHeight <= 640) {
            return true;
        }
         
        if( navigator.userAgent.match(/Android/i)
            || navigator.userAgent.match(/webOS/i)
            || navigator.userAgent.match(/iPhone/i)
            || navigator.userAgent.match(/iPad/i)
            || navigator.userAgent.match(/iPod/i)
            || navigator.userAgent.match(/BlackBerry/i)
            || navigator.userAgent.match(/Windows Phone/i)
        ){
            return true;
        }

        return false;
       
    }

    var ismob = ismob()

    if(!ismob){
        fixSidebar(); //in case the reloaded page is already scrolled down
        $(window).scroll(function(){
            fixSidebar();
        });
    }

    //Remove annoying facebook #_=_ from end of uri
    if (window.location.hash == '#_=_'){
        
        if (String(window.location.hash).substring(0,1) == "#") {
            window.location.hash = "";
            window.location.href=window.location.href.slice(0, -1);
        }
        if (String(location.hash).substring(0,1) == "#") {
            location.hash = "";
            location.href=location.href.substring(0,location.href.length-3);
        }

    }

    var windowSize = $(window).height();
	if(windowSize < 600){
		windowSize = 600;
	}

    $('#content').css('min-height', windowSize-66+'px');

	$(window).resize(function() {

		windowSize = $(window).height();
		if(windowSize < 600){
			windowSize = 600;
		}

	  $('#content').css('min-height', windowSize-66+'px');

	});

    $.ajax({
    type: "POST",
    url: "ajax.php",
    data: {func: 'getStatic'}, 
    success:

        function(data) {

            if(data.status == 'OK'){

                var pinfo = $.parseJSON(data.msg);

                $('.btn-group li:nth-child(1) a span').html(pinfo[0].user);

                $('.header-ip-show').html(pinfo[0].ip);
                $('.header-ip').css('margin-top','10px');

                if(pinfo[0].rank == -1){
                    var rank = '';
                } else {
                    var rank = '<span class="small" title="'+pinfo[0].rank_title+'">(#'+pinfo[0].rank+')</span>';
                }

                if(!$('body').hasClass('index')){
                    $('.reputation-info').html('<div style="float: left;"><span class="small nomargin item" title="'+pinfo[0].rep_title+'">'+pinfo[0].reputation+'</span>'+rank+'</div>')
                }

            }

        }

    });

    function getCommons(timeout){

        $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {func: 'getCommon'}, 
        success:

            function(data) {

                if(data.status == 'OK'){

                    var common = $.parseJSON(data.msg);

                    if(common[0].unread > 0){
                        $('.mail-unread').addClass('label label-important').html(common[0].unread);
                        if(!$('body').hasClass('mail')){
                            if(!$('#notify').hasClass('shown')){
                                gritterNotify({title:common[0].unread_title,text:common[0].unread_text,img:'',sticky:false})
                                $('#notify').addClass('shown');
                            }
                        }
                    }

                    if(common[0].mission_complete == 1){
                        if(!($('#menu-mission').hasClass('shown'))){
                            $('#menu-mission').children().append('<span class="label">$</span>');
                            $('#menu-mission').addClass('shown');
                        }
                    }
        
                    $('.online').html('<span class="he16-online" title="'+common[0].online_title+'"></span> <span class="small nomargin">'+common[0].online+'</span>');

                    if(!$('body').hasClass('index')){
                        $('.finance-info').html('<div style="float: right;"><span class="small nomargin green header-finances" title="'+common[0].finances_title+'">$'+common[0].finances+'</span></div>')
                    }

                }

            }

        });

        $('*').bind('mousemove keydown scroll', function () {
        
            clearTimeout(idleTimer);
                    
            if (idleState == true) {     
                idleState = false;
                time = 30000;
                getCommons(-1);
            }
            
            idleState = false;
            
            idleTimer = setTimeout(function () { 
                idleState = true; 
                time = 60000 * 5;
            }, idleWait);

        });

        if(timeout > 0){
            setTimeout(getCommons, time);
        }

    }

    var stopCommons = false;

    var time = 30000;
    getCommons(time);

    idleTimer = null;
    idleState = false;
    idleWait = 60000;

    $("body").trigger("mousemove");

    $('#credits').on('click', function(){

        if($('#modal').length == 0){            
            $('body').append('<span id="modal"></span>');
        }

        $('#modal').css('text-align', 'center');

        $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {func: 'credits'}, 
        success:
            function(data) {

                modalInfo = $.parseJSON(data.msg);

                openModal({title:modalInfo[0].title,text:modalInfo[0].text,btn:modalInfo[0].btn,input:''})

            }

        });

    });

    $('#report-bug').on('click', function(){

        if ($('#bug-submit').hasClass('disabled')){
            return;
        }

        if($('#modal').length == 0){            
            $('body').append('<span id="modal"></span>');
        }

        $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {func: 'reportBug'}, 
        success:
            function(data) {

                modalInfo = $.parseJSON(data.msg);

                var title = modalInfo[0].title;
                var text = modalInfo[0].text;
                var btn = modalInfo[0].btn;

                openModal({title:title,text:text,btn:btn,input:''})

                $('#bug-content').focus();

                $('#modal-form').validate({
                    bugtext: "required",
                    validClass: "success",
                    errorClass: "error",
                    errorPlacement: function(){},
                    highlight:function(element, errorClass, validClass) {
                        $(element).parents('.control-group').addClass(errorClass);
                    },
                    unhighlight: function(element, errorClass, validClass) {
                        if($(element).parents('.control-group').hasClass(errorClass)){
                            $(element).parents('.control-group').removeClass(errorClass);
                            $(element).parents('.control-group').addClass(validClass);
                        }
                    }
                });

                $('#modal-form').submit(function(){

                    $.ajax({
                        type: "POST",
                        url: "ajax.php",
                        data: {
                            func: 'reportBug',
                            rlt: $('#bug-content').val(),
                            follow: $('#bug-follow').val()
                        },
                        success: function(data){
                            if(data.status == 'OK'){
                                var html2replace = '<div class="alert alert-success center">Thanks for your feedback! <a class="link" data-dismiss="modal">[close]</a></div>'
                            } else {
                                var html2replace = '<div class="alert alert-error center">Error while sending the report. Please, try again. <a class="link" data-dismiss="modal">[close]</a></div>'
                            }
                            
                            $('.modal-body').replaceWith('<div class="modal-body">'+html2replace+'</div>');
                            $('#bug-submit').addClass('disabled');

                        }
                    });

                    return false;

                });

            }

        });

    });

    function gritterNotify(opts){
        $('<link rel="stylesheet" type="text/css" href="css/jquery.gritter.css" >').appendTo("head");
        $.getScript("js/jquery.gritter.min.js", function(){
            $.gritter.add({
                title:  opts.title,
                text:   opts.text,
                image:  opts.img,
                sticky: opts.sticky
            });
        });
    }

    function getBankAcc(){

        $.ajax({
        type: "POST",
        url: "ajax.php",
        data: {func: 'getBankAccs'}, 
        success:
            function(data) {
                if(data.status == 'OK'){

                    $('<link rel="stylesheet" type="text/css" href="css/select2.css" >').appendTo("head");
                    $.getScript("js/select2.js", function(){

                        $('#desc-money').html(data.msg);
                        $('#desc-money #select-bank-acc').select2();

                        $('#loading').hide();
                        $('#modal-submit').removeAttr('disabled');
                        $('#modal-form').submit(true)

                    });

                }
            }
        });

    }

    function openModal(opts, show){

        if(typeof(show) === 'undefined'){
            show = true;
        }

        var h =
        '<div id="gen-modal" class="modal hide" tabindex="0">\
        <div class="modal-header">\
        <button data-dismiss="modal" class="close" type="button">×</button>\
        <h3>'+opts.title+'</h3>\
        </div>\
        <form action="" method="POST" id="modal-form">\
        <div class="modal-body">\
        <p>\
        '+opts.text+'\
        </p>\
        \
        </div>\
        <div class="modal-footer">\
        '+opts.input+'\
        '+opts.btn+'\
        \
        </div>\
        </form>\
        </div>';    

        $('#modal').html(h);

        if(!show) return false;

        $('#gen-modal').modal('show');

    }

    function generateModalInput(opts){

        var h = "";

        for(x = 0; x < opts.length; x++){
            h += '<input type="hidden" name="'+opts[x][0]+'" value="'+opts[x][1]+'">';
        }

        return h;

    }


    // === Sidebar navigation === //
    $('.submenu > a').click(function(e){
        e.preventDefault();
        var submenu = $(this).siblings('ul');
        var li = $(this).parents('li');
        var submenus = $('#sidebar li.submenu ul');
        var submenus_parents = $('#sidebar li.submenu');
        if(li.hasClass('open')) {
            if(($(window).width() > 768) || ($(window).width() < 479)) {
                submenu.slideUp();
            } else {
                submenu.fadeOut(250);
            }
            li.removeClass('open');
        } else {
            if(($(window).width() > 768) || ($(window).width() < 479)) {
                submenus.slideUp();         
                submenu.slideDown();
            } else {
                submenus.fadeOut(250);          
                submenu.fadeIn(250);
            }
            submenus_parents.removeClass('open');       
            li.addClass('open');    
        }
    });
    
    var ul = $('#sidebar > ul');
    
    $('#sidebar > a').click(function(e){
        e.preventDefault();
        var sidebar = $('#sidebar');
        if(sidebar.hasClass('open')){
            sidebar.removeClass('open');
            ul.slideUp(250);
        } else {
            sidebar.addClass('open');
            ul.slideDown(250);
        }
    });
    
    // === Resize window related === //
    $(window).resize(function(){
        if($(window).width() > 479){
            ul.css({'display':'block'});    
            $('#content-header .btn-group').css({width:'auto'});        
        }
        if($(window).width() < 479){
            ul.css({'display':'none'});
            fix_position();
        }
        if($(window).width() > 768){
            $('#user-nav > ul').css({width:'auto',margin:'0'});
            $('#content-header .btn-group').css({width:'auto'});
        }
    });
    
    if($(window).width() < 468){
        ul.css({'display':'none'});
        fix_position();
    }
    if($(window).width() > 479){
       $('#content-header .btn-group').css({width:'auto'});
       ul.css({'display':'block'});
    }
    
    // === Fixes the position of buttons group in content header and top user navigation === //
    function fix_position(){
        var uwidth = $('#user-nav > ul').width();
        $('#user-nav > ul').css({width:uwidth,'margin-left':'-' + uwidth / 2 + 'px'});
        
        var cwidth = $('#content-header .btn-group').width();
        $('#content-header .btn-group').css({width:cwidth,'margin-left':'-' + uwidth / 2 + 'px'});
    }

    if($('#lower-ad').length > 0){
        if($('#lower-ad').height() == 0){

            $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {func: 'gettext', id:'adb'}, 
            success:
                function(data) {

                    modalInfo = $.parseJSON(data.msg);

                    $('#lower-ad').html(modalInfo[0].title);

                }

            });

        }
    }

});