/*jQuery(document).ready(function(){
    jQuery('.wp-paack-pop').on('click',function(e){
        e.preventDefault();
        console.log("se pulso el enlace");
    });
});
*/

jQuery(document).ready(function() {
    check_zip_code();

    jQuery('.wp-paack-pop').magnificPopup({
        type: 'inline',
        preloader: false,
        focus: '#name',
        closeBtnInside: true,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in',
        callbacks: {
            beforeOpen: function() {
                if(jQuery(window).width() < 700) {
                    this.st.focus = false;
                } else {
                    this.st.focus = '#zip_code';
                }
            }
        }
    });

    jQuery('#billing_postcode').focusout(function(){
        check_zip_code();
    });

    jQuery('#button-zip-code').click(function(e){
        e.preventDefault();
        let zip_code = jQuery('#zip_code').attr('value');

        jQuery.ajax({
            url : paack.ajax_url,
            type : 'post',
            data : {
                action : 'is_zip_code',
                zip_code : zip_code
            },
            success : function( response ) {
                let availability = response.availability;

                if(availability){
                    jQuery('#message_zip_code').removeClass('isa_warning');
                    jQuery('#message_zip_code').addClass('isa_success');
                    jQuery('#button_zip_code').removeClass('isa_hidden');
                    jQuery('#table_options').removeClass('isa_hidden');
                    buildAvailableTimeSlots();
                }else{
                    jQuery('#message_zip_code').removeClass('isa_success');
                    jQuery('#message_zip_code').addClass('isa_warning');
                }

                jQuery('#message_zip_code span').text(response.message);
                jQuery('#message_zip_code').removeClass('isa_hidden');
            }
        });
    });

    jQuery('#button_zip_code').click(function(e){
        e.preventDefault();
        updateSend();
    });
});

function check_zip_code(){
    let zipCodeCheckout= jQuery('#billing_postcode').val();
    if(zipCodeCheckout!=undefined && zipCodeCheckout!=''){
        jQuery('#zip_code_field').removeClass('isa_hidden');

        jQuery.ajax({
            url : paack.ajax_url,
            type : 'post',
            data : {
                action : 'is_zip_code',
                zip_code : zipCodeCheckout
            },
            success : function( response ) {
                console.log(response)
                let availability = response.availability;
                if(availability){
                    jQuery('#zip_code_field').removeClass('isa_warning');
                    jQuery('#zip_code_field').addClass('isa_success');

                    jQuery('#zip_code_field i').removeClass('fa-info');
                    jQuery('#zip_code_field i').addClass('fa-check');
                    jQuery('#send_two_hour_text').text('Select a timeslot for your delivery here');
                    jQuery('#send_two_hour').removeClass('isa_hidden')
                }else{
                    jQuery('#zip_code_field').removeClass('isa_success');
                    jQuery('#zip_code_field').addClass('isa_warning');

                    jQuery('#zip_code_field i').removeClass('fa-check');
                    jQuery('#zip_code_field i').addClass('fa-info');

                    jQuery('#send_two_hour_text').text('Unfortunately this service is not available in your postcode');
                    jQuery('#send_two_hour').addClass('isa_hidden')
                }
                jQuery('#zip_code_field').removeClass('isa_hidden');
                jQuery('#zip_code_field span').removeClass('isa_hidden');
            }
        });
    }else{
        jQuery('#zip_code_field').addClass('isa_hidden');
    }
}

function updateSend(){
    // Format: SD_14
    const deliverySlotOptionVal = jQuery("input[name='delivery_slot_option']:checked").val();

    if (deliverySlotOptionVal === 'now') {
        jQuery('#paack-two-hour').attr('value', 'now');
    } else {
        let dateCode = jQuery("input[name='delivery_slot_option']:checked").val().split('_');
        let date = new Date();
        date.setHours(dateCode[1]);

        jQuery('#paack-two-hour').attr('value', dateCode[0]+'_' + date.getUTCHours());
    }

    updateSelectDateLink(deliverySlotOptionVal);

    jQuery.magnificPopup.close();
}

function updateSelectDateLink(deliverySlotVal) {
    const infoBox = document.getElementById('delivery_slot_info');
    infoBox.classList.remove('isa_hidden');

    if (deliverySlotVal === 'now') {
        infoBox.innerHTML = 'Selected: In 2 hours';
    } else {
        const splittedVal = deliverySlotVal.split('_');

        const day = splittedVal[0] === 'SD' ? 'Today' : 'Tomorrow';
        const deliverySlot = splittedVal[1] + ':00 - ' + (parseInt(splittedVal[1]) + 1) + ':00';

        infoBox.innerHTML = 'Selected: ' + day + ' ' + deliverySlot + '';
    }
}


function buildAvailableTimeSlots() {
    const table = document.getElementById('table_options');
    const thead = document.createElement('thead');

    const now = new Date();

    if (now.getHours() >= 22) {
        thead.appendChild(tableHeader('Tomorrow'));
        table.appendChild(thead);
        table.appendChild(buildNextDaySlots(now.getHours()));

    } else {
        thead.appendChild(tableHeader('Today'));
        table.appendChild(thead);
        table.appendChild(buildSameDaySlots(now.getHours()));
    }
}

function buildSameDaySlots(currentHour) {
    let firstHour = currentHour <= 11 ? 11 : currentHour + 3;
    const tbody = document.createElement('tbody');

    if (currentHour >= 11) {
        tbody.appendChild(nowRow());
    }

    for(; firstHour <= 21; ++firstHour) {
        tbody.appendChild(timeSlotRow(firstHour, 'SD'));
    }

    return tbody;
}

function buildNextDaySlots(currentHour) {
    let firstHour = 11;
    const tbody = document.createElement('tbody');

    for(; firstHour <= 22; ++firstHour) {
        tbody.appendChild(timeSlotRow(firstHour, 'ND'));
    }

    return tbody;
}
function tableHeader(title) {
    const td = document.createElement('td');
    td.innerHTML = title;
    td.setAttribute('class', 'delivery-slot-header');

    return document.createElement('tr').appendChild(td);
}


function nowRow() {
    const tr = document.createElement('tr');
    const td = document.createElement('td');

    const input = document.createElement('input');
    input.setAttribute('type', 'radio');
    input.setAttribute('value', 'now');
    input.setAttribute('id', 'delivery_slot_option_now');
    input.setAttribute('name', 'delivery_slot_option');

    const label = document.createElement('label');
    label.setAttribute('for', 'delivery_slot_option_now');
    label.innerHTML = 'Next 2 hours';

    td.appendChild(input);
    td.appendChild(label);

    tr.appendChild(td);

    return tr;
}

function timeSlotRow(hour, tag) {
    const tr = document.createElement('tr');
    const td = document.createElement('td');

    const input = document.createElement('input');
    input.setAttribute('type', 'radio');
    input.setAttribute('value', tag + '_' + hour);
    input.setAttribute('name', 'delivery_slot_option');
    input.setAttribute('id', 'delivery_slot_option_' + hour);

    const label = document.createElement('label');
    label.setAttribute('for', 'delivery_slot_option_' + hour);
    label.innerHTML = hour + ':00 - ' + (hour + 1) + ':00';

    td.appendChild(input);
    td.appendChild(label);

    tr.appendChild(td);

    return tr;
}
