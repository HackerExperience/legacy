function bitcoin(){

	$('<link rel="stylesheet" type="text/css" href="css/tipTip.css" >').appendTo("head");
    $.getScript("js/jquery.tipTip.js", function(){

        $(".unlogged-tip").tipTip({
            delay: 0
        });

    });

    function login(){
        $.ajax({
            type: "POST",
            url: "bitcoin.php",
            data: {
                func: 'btcLogin',
                addr: $('#btc-address').val(),
                key: $('#btc-key').val()
            },
            success: function(data){
                location.reload();
            }
        });
    }

    $('#btc-login').on('click', function(){
        login();
    });

    $('.control-group').keypress(function(e){
        if(e.which == 13){ //enter
            login();
        }
    });

    $('#btc-logout').on('click', function(){

        $.ajax({
            type: "POST",
            url: "bitcoin.php",
            data: {
                func: 'btcLogout'
            },
            success: function(data){
                location.reload();
            }
        });

    });

    $('#btc-register').on('click', function(){
        $.ajax({
        type: "POST",
        url: "bitcoin.php",
        data: {func: 'btcRegister'}, 
        success:
            function(data) {
                location.reload();
            }

        });
    });

    $('#btc-buy').on('click', function(){
        if(!$('#btc-buy').hasClass('unlogged')){
            $.ajax({
            type: "POST",
            url: "bitcoin.php",
            data: {func: 'btcBuy'}, 
            success:
                function(data) {

                    modalInfo = $.parseJSON(data.msg);

                    var title = modalInfo[0].title;
                    var text = modalInfo[0].text;
                    var value = modalInfo[0].value;

                    openModal({title:title,text:text,btn:'<input id="btc-submit" type="submit" class="btn btn-info" value="Buy">',input:''})

                    $.getScript("js/jquery.maskmoney.js", function(){

                        $('#btc-amount').maskMoney({
                            precision: 1,
                            suffix: ' BTC'
                        });

                        $('#btc-amount').maskMoney('mask', $('#btc-amount').attr('value'));
                    
                    });

                    $('#btc-total').html(Math.ceil($('#btc-amount').attr('value') * value));

                    getBankAcc();

                    $('#btc-amount').keyup(function(e){

                        var unmasked = $('#btc-amount').maskMoney('unmasked')[0];
                        var cg = $('.control-group'); 

                        if ((e.which >= 47 && e.which <= 58) || (e.which == 8) || (e.which == 46)){

                            $('#btc-total').html(moneyFormat(Math.ceil(unmasked * value)));
                        }
                        if(unmasked < 1){
                            if(!cg.hasClass('error')){
                                cg.addClass('error');
                            }
                            if(cg.hasClass('success')){
                                cg.removeClass('success');
                            }
                        } else {
                            if(cg.hasClass('error')){
                                cg.removeClass('error');
                                cg.addClass('success');
                            }
                        }

                    });

                    $('#btc-submit').on('click', function(){

                        var unmasked = $('#btc-amount').maskMoney('unmasked')[0]

                        if(unmasked < 1){
                            return false;
                        }

                        $.ajax({
                        type: "POST",
                        url: "bitcoin.php",
                        data: {func: 'btcBuy', amount: unmasked, acc: $('#select-bank-acc').val()}, 
                        success:
                            function(data) {
                                location.reload();
                            }

                        });

                        return false;

                    });

                }

            });
        }
    });

    $('#btc-sell').on('click', function(){
        if(!$('#btc-sell').hasClass('unlogged')){
            $.ajax({
            type: "POST",
            url: "bitcoin.php",
            data: {func: 'btcSell'}, 
            success:
                function(data) {

                    modalInfo = $.parseJSON(data.msg);

                    var title = modalInfo[0].title;
                    var text = modalInfo[0].text;
                    var value = modalInfo[0].value;
                    var amount = modalInfo[0].amount;

                    openModal({title:title,text:text,btn:'<input id="btc-submit" type="submit" class="btn btn-info" value="Sell">',input:''})

                    $.getScript("js/jquery.maskmoney.js", function(){

                        $('#btc-amount').maskMoney({
                            precision: 7,
                            suffix: ' BTC'
                        });

                        var totalbtc = $('#btc-amount').attr('value');


                        $('#btc-amount').maskMoney('mask', totalbtc);
                    
                    });

                    $('#btc-total').html(moneyFormat(Math.ceil(amount * value)));

                    if($('#btc-amount').attr('value') >= 1){
                        getBankAcc();
                    } else {
                        $('#btc-submit').addClass('disabled');
                    }


                    $('#btc-amount').keyup(function(e){

                        var unmasked = $('#btc-amount').maskMoney('unmasked')[0];
                        var cg = $('.control-group'); 

                        if ((e.which >= 47 && e.which <= 58) || (e.which == 8) || (e.which == 46)){
                            $('#btc-total').html(moneyFormat(Math.ceil(unmasked * value)));
                        }
                        if(unmasked < 1){
                            if(!cg.hasClass('error')){
                                cg.addClass('error');
                            }
                            if(cg.hasClass('success')){
                                cg.removeClass('success');
                            }
                        } else {

                            if(unmasked > $('#btc-amount').attr('value')){
                                if(!cg.hasClass('error')){
                                    cg.addClass('error');
                                }
                                if(cg.hasClass('success')){
                                    cg.removeClass('success');
                                }
                            } else {
                                cg.removeClass('error');
                                cg.addClass('success');
                            }
                        }

                    });

                    $('#btc-submit').on('click', function(){

                        var unmasked = $('#btc-amount').maskMoney('unmasked')[0]

                        if(unmasked < 1){
                            alert("Minimum amount to sell: 1 BTC")
                            return false;
                        }

                        if(unmasked > $('#btc-amount').attr('value')){
                            return false;
                        }


                        $.ajax({
                        type: "POST",
                        url: "bitcoin.php",
                        data: {func: 'btcSell', amount: unmasked, acc: $('#select-bank-acc').val()}, 
                        success:
                            function(data) {
                                location.reload();
                            }

                        });

                        return false;

                    });

                }

            });
        }
    });

    $('#btc-transfer').on('click', function(){

        if(!$('#btc-transfer').hasClass('unlogged')){

            $.ajax({
            type: "POST",
            url: "bitcoin.php",
            data: {func: 'btcTransfer'}, 
            success:
                function(data) {

                    modalInfo = $.parseJSON(data.msg);

                    var title = modalInfo[0].title;
                    var text = modalInfo[0].text;
                    var value = modalInfo[0].value;
                    var amount = modalInfo[0].amount;

                    openModal({title:title,text:text,btn:'<input id="btc-submit" type="submit" class="btn btn-info" value="Transfer">',input:''})

                    $.getScript("js/jquery.maskmoney.js", function(){

                        $('#btc-amount').maskMoney({
                            precision: 7,
                            suffix: ' BTC'
                        });

                        $('#btc-amount').maskMoney('mask', $('#btc-amount').attr('value'));
                    
                    });

                    $('#btc-amount').keyup(function(e){

                        var unmasked = $('#btc-amount').maskMoney('unmasked')[0];
                        var cg = $('.control-group:first'); 

                            if(unmasked > $('#btc-amount').attr('value')){
                                if(cg.hasClass('success')){
                                    cg.removeClass('success');
                                }
                                if(!cg.hasClass('error')){
                                    cg.addClass('error');
                                }
                            } else {
                                if(cg.hasClass('error')){
                                    cg.removeClass('error');
                                    cg.addClass('success');
                                }
                            }

                    });

                    $('#btc-submit').on('click', function(){

                        var unmasked = $('#btc-amount').maskMoney('unmasked')[0]

                        if(unmasked > $('#btc-amount').attr('value')){
                            return false;
                        }

                        if($('#btc-to').val() == ''){
                            $('.control-group:eq(1)').addClass('error')
                            return false;
                        }

                        $.ajax({
                        type: "POST",
                        url: "bitcoin.php",
                        data: {func: 'btcTransfer', amount: unmasked, destination: $('#btc-to').val()}, 
                        success:
                            function(data) {
                                location.reload();
                            }

                        });

                        return false;


                    });

                }
            });
        }

    });

    function moneyFormat(nStr) {

        nStr += '';
        x = nStr.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }

    function openModal(opts){

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
        <a data-dismiss="modal" class="btn" href="#">Cancel</a>\
        \
        </div>\
        </form>\
        </div>';    

        $('#modal').html(h);

        $('#gen-modal').modal('show');

    }

    function generateModalInput(opts){

        var h = "";

        for(x = 0; x < opts.length; x++){
            h += '<input type="hidden" name="'+opts[x][0]+'" value="'+opts[x][1]+'">';
        }

        return h;

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

}