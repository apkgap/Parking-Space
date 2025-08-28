jQuery(document).ready(function($) {
    // Main Tab Switching
    $('.wpt-tab').click(function() {
        $('.wpt-tab').removeClass('wpt-active');
        $(this).addClass('wpt-active');

        var tabId = $(this).data('tab');
        $('.wpt-tab-content').hide();
        $('#wpt-' + tabId).show();

        $('.wrap-booking').removeClass('vPrivate');
    });





    $('.parking-type').click(function (e) { 
        var type = $(this).attr('data-sub-tab');
        if(type){
            $('input[name="parking-type"]').val(type);

            $('.parking_duration_select select').prop('selectedIndex', 0);
            $('input[name="parking-duration"]').val('');

            if( type == 'daily' ){

                $('.parking_duration_day').removeClass('hide');
                $('.parking_duration_month').addClass('hide');
            }


            if( type == 'monthly' ){
                $('.parking_duration_month').removeClass('hide');
                $('.parking_duration_day').addClass('hide');
            }
        }
    });





    $('.parking_duration_select select').change(function (e) { 
        e.preventDefault();
        var data = $(this).val();
        if(data){
            $('input[name="parking-duration"]').val(data);
        }
    });

    $('.parking-location').change(function (e) { 
        e.preventDefault();
        var data = $(this).val();
        if(data){
            $('.wpt-form').attr('action','/parking/'+data);
        }
    });

    




    $('.wpt-private').click(function (e) { 
        e.preventDefault();
        $('.wrap-booking').addClass('vPrivate');
    });

    $('.wpt-date-picker').flatpickr({
        dateFormat: 'd/m/Y',
        locale: 'th',
        minDate: "today" // Set minimum date to today
    });

    // Sub Tab Switching (for both Parking and Private Car tabs)
    $('.wpt-sub-tab').click(function() {
        // var parentTab = $(this).closest('.wpt-tab-content');
        // parentTab.find('.wpt-sub-tab').removeClass('wpt-active');

        $('.wpt-sub-tab').removeClass('wpt-active');
        $(this).addClass('wpt-active');

        // var subTabId = $(this).data('sub-tab');
        // parentTab.find('.wpt-sub-tab-content').hide();
        // parentTab.find('#wpt-' + subTabId).show();
    });


});