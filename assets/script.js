const mainframe = {
    notify: function(text, title, type){

        var self = this;

        type = (type===true);

        title = (title===undefined) ? '' : title;

        var block = $('.a-alert');

        if(!block.length){
            $('body').append('<div class="a-alert"></div>');

            block = $('.a-alert');
        }

        var id = Math.random();

        block.append('<div data-id="'+id+'" class="alert alert-id '+(type?'alert-success':'alert-danger')+' alert-dismissible" role="alert">'
            +'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><i class="fa fa-times"></i></button>'
            +(title?'<strong>'+title+'</strong> ':'')
            +text
            +'</div>');

        block.find('.alert-id[data-id="'+id+'"]').fadeIn('fast');

        setTimeout(function(){
            self.notify_close(id);
        }, 3000);

        return type;
    },

    notify_close: function(id){

        if(id!==undefined){
            var alert = $('.a-alert > .alert-id[data-id="'+id+'"]');

            if(alert.hasClass('confirm')){
                return;
            }

            var closer = alert.find('.close-trigger');

            if(closer.attr('data-disabled')==='true'){
                return false;
            }

            if(!alert.length){
                return false;
            }

            alert.fadeOut('fast', function(){
                $(this).remove();
            });

            return false;
        }

        var alerts = $('.a-alert > .alert-id:not(.confirm)');

        if(!alerts.length){
            return false;
        }

        alerts.fadeOut('fast', function(){
            $(this).remove();
        });

        return false;
    },

    getPrice: function() {
        const login = $('#login').val();
        const goodName = $('#good').val();
        const amount = $('#amount').val();
        const price = $('#price').val();
        const promo = $('#promo').val() || '';

        $("#submitBtn").attr('disabled', 1);
        $("#submitBtn").html('Загрузка...');

        setTimeout(function() {
            $.ajax({
                url: 'library/checkPrice.php?username=' + login + '&good=' + goodName + "&amount=" + amount + "&promo=" + promo,
                type: 'GET',
                async: true,
                cache: false,
                success: function(data) {
                    const res = JSON.parse(data);
                    if (res.status == 'failed') {
                        $("#submitBtn").html('Ошибка!');
                        mainframe.notify(res.message, "Ошибка!", false);
                    } else {
                        $("#submitBtn").removeAttr('disabled');
                        if ($('.modal[data-id=paymodal]').attr('data-withSurcharge') == "1") {
                            $("#submitBtn").html(price == res.message ? "Купить за " + res.message + " <i class=\"fa fa-ruble col-black\"></i>" : "Доплатить " + res.message + " <i class=\"fa fa-ruble col-black\"></i>");
                        } else {
                            $("#submitBtn").html("Купить за " + res.message + " <i class=\"fa fa-ruble col-black\"></i>");
                        }

                    }
                }
            })
        }, 1e3);
    }
};

$(function(){
    setTimeout(function(){
        mainframe.notify_close();
    }, 3500);

    $('body').fadeIn().on('click', '.select-style-render > .select-style-selected', function(e){
        e.preventDefault();

        var that = $(this);

        that.closest('.select-style-render').toggleClass('open');
    }).on('click', '.a-alert > .alert-id:not(.confirm) .close-trigger', function(e){
        e.preventDefault();

        mainframe.notify_close($(this).closest('.alert-id').attr('data-id'));

    }).on('click', '.tabs .tab-links > li > a', function(e){
        e.preventDefault();

        var that = $(this);

        var tabs = that.closest('.tabs');

        var li = that.closest('li');

        if(li.hasClass('active')){ return; }

        tabs.find('.tab-list > .tab-id').removeClass('active');
        that.closest('.tab-links').children('li').removeClass('active');

        tabs.find('.tab-list > .tab-id[data-id="'+li.attr('data-id')+'"]').addClass('active');
        li.addClass('active');
    }).on('click', '.tabs-alt > .nav-tabs > li > a', function(e){
        e.preventDefault();

        var that = $(this);

        var tabs = that.closest('.tabs-alt');

        var li = that.closest('li');

        if(li.hasClass('active')){ return; }

        tabs.find('.tab-content > .tab-pane.active').removeClass('active');
        that.closest('.nav-tabs').children('li').removeClass('active');

        $(that.attr('href')).addClass('active');
        li.addClass('active');
    }).on('click', '.copy-clipboard', function(e){
        e.preventDefault();

        var that = $(this);

        that.text('Скопировано!');

        setTimeout(function(){
            that.text(that.attr('data-clipboard-text'));
        }, 1000);
    }).on('click', '.navbar .navbar-mobile', function(e){
        e.preventDefault();

        $(this).closest('.navbar').toggleClass('active');
    }).on('click', '#restores-accept', function(e){
        e.preventDefault();
    });

    new ClipboardJS('.copy-clipboard');
});

$(function(){
    $('body').on('click', '[data-modal]', function(e){
        var that = $(this);

        var id = that.attr('data-modal');

        var modal = $('.modal[data-id="'+id+'"]');

        if(modal.length){

            if(id == 'paymodal'){
                modal.find('.modal-header').html('Покупка товара "'+that.children('.title').html()+'"');
                modal.find('[type="submit"]').html('Купить за '+that.children('.price').html());
                modal.find('[name="good"]').val(that.attr('data-name'));
                modal.find('[name="price"]').val(that.attr('data-price'));
                modal.attr('data-withSurcharge', that.attr('data-withSurcharge'));
                if(that.attr('data-amounted') == "1"){
                    $('#amounted').show();
                }else{
                    $('#amounted').hide();
                }
            }

            modal.fadeIn('fast', function(){
                $(this).addClass('active');
            });
        }
    }).on('input', '#login', mainframe.getPrice).on('input', '#amount', mainframe.getPrice).on('input', '#promo', mainframe.getPrice).on('click', '.modal [data-modal-close]', function(e){
        e.preventDefault();

        $('.modal').fadeOut('fast', function(){
            $(this).removeClass('active');
            $(this).removeAttr('data-withSurcharge');
            $("#login").val("");
            $("#amount").val(1);
            $("#promo").val("");
        });
    }).on('click', '.modal', function(e){
        var target = $(e.target);
        if(!target.closest('.modal-content').length){
            $('.modal').fadeOut('fast', function(){
                $(this).removeClass('active');
                $(this).removeAttr('data-withSurcharge');
                $("#login").val("");
                $("#amount").val(1);
                $("#promo").val("");
            });
        }
    });

    // Динамическая гирлянда под размер пазов на проводе для всех контейнеров
    function drawGarland() {
        var garlands = $('.garland-container');
        if (!garlands.length) return;
        
        garlands.each(function() {
            var garland = $(this);
            garland.empty();
            
            var tileWidth = 392 * (25 / 43); // ~227.9px
            var sockets = [
                { x: 42 * (25/43), y: 24 * (25/43) },
                { x: 130 * (25/43), y: 32 * (25/43) },
                { x: 194 * (25/43), y: 43 * (25/43) },
                { x: 258 * (25/43), y: 32 * (25/43) },
                { x: 348 * (25/43), y: 24 * (25/43) }
            ];
            
            var width = garland.width();
            var numTiles = Math.ceil(width / tileWidth);
            
            for (var i = 0; i < numTiles; i++) {
                sockets.forEach(function(s) {
                    var left = i * tileWidth + s.x;
                    var top = s.y - 2; // смещение на 2px вверх, чтобы лампочки входили точно в паз
                    
                    var bulb = $('<div class="lightbulb"></div>');
                    bulb.css({
                        left: left + 'px',
                        top: top + 'px'
                    });
                    garland.append(bulb);
                });
            }
        });
    }

    if ($('.garland-container').length) {
        drawGarland();
        $(window).on('resize', drawGarland);
    }
});