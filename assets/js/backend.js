jQuery(function($){
    var coolrunner_shipment = {
        init: function(form, zone_to, ordersCount, action = 'bulk'){
            this.form = $(form);
            this.action = action;
            this.isBulk = (action == 'bulk') ? true : false;
            this.hasDroppoints = (this.isBulk) ? false : true;
            this.ordersCount = (this.isBulk) ? ordersCount : 1;

            this.carrier = this.form.find('.coolrunner_carrier');
            this.carrierSelected = this.carrier.find('option:selected');
            this.carrierSelectedCarrier = this.carrierSelected.data('carrier');
            this.carrierSelectedIndex = this.carrierSelected.val();

            this.droppointsSelect = this.form.find('.coolrunner_droppoints');
            this.droppointSelected = this.droppointsSelect.find('option:selected');

            this.sizesSelect = this.form.find('.coolrunner_sizes');
            this.sizeSelected = this.sizesSelect.find('option:selected');

            this.weight = this.form.find('.coolrunner_weight');
            this.weightSelected = this.weight.find('option:selected');
            this.weightSelectedIndex = this.weightSelected.val();

            this.sizeLength = this.form.find('.coolrunner_size_length');
            this.sizeWidth = this.form.find('.coolrunner_size_width');
            this.sizeHeight = this.form.find('.coolrunner_size_height');

            this.loader = this.form.find('.coolrunner_loading');
            this.submit = this.form.find('#coolrunner_submit');

            this.price = this.form.find('.coolrunner_price');
            this.labelsCount = this.form.find('.coolrunner_labels_count');

            this.resultDiv = this.form.find('.coolrunner_result');
            this.errorDiv = this.form.find('.coolrunner_response');

            //Binds initialised variables with the functions
            this.start = this.start.bind(this);
            this.change_carrier = this.change_carrier.bind(this);
            this.change_weight = this.change_weight.bind(this);
            this.change_sizes = this.change_sizes.bind(this);
            this.load_droppoints = this.load_droppoints.bind(this);
            this.submit_form = this.submit_form.bind(this);
            this.block = this.block.bind(this);
            this.unblock = this.unblock.bind(this);

            this.start();

            $(document).on('change', '.coolrunner_carrier', this.change_carrier);
            $(document).on('change', '.coolrunner_weight', this.change_weight);
            $(document).on('change', '.coolrunner_sizes', this.change_sizes);
            $(document).on('click', '#coolrunner_submit', this.submit_form);
        },
        start: function(){
            this.loader.hide();
            this.resultDiv.hide();
            this.errorDiv.html('').hide();

            this.labelsCount.html(this.ordersCount);

            //this.price.html(carriers[this.carrierSelectedIndex].price_incl_tax * this.ordersCount);

            if (this.hasDroppoints) {
                if (droppoints['dao'].error || droppoints['pdk'].error || droppoints['gls'].error) {
                    this.errorDiv.html(coolrunner_l18n.no_droppoints).show();
                    this.droppointsSelect.html('');
                    this.droppointsSelect.append('<option selected value="-1">'+coolrunner_l18n.no_droppoints+'</option>');

                    this.submit.prop('disabled', true);
                }
            }

            if (this.sizeSelected.length) {
                this.sizeLength.val(this.sizeSelected.val().split(':', 3)[0]);
                this.sizeWidth.val(this.sizeSelected.val().split(':', 3)[1]);
                this.sizeHeight.val(this.sizeSelected.val().split(':', 3)[2]);
            } else {
                this.sizeLength.val('0');
                this.sizeWidth.val('0');
                this.sizeHeight.val('0');
            }
        },
        change_carrier: function(){
            this.block();

            /*this.carrierLastSelected = this.carrierSelected;
            this.carrierLastSelectedCarrier = this.carrierLastSelected.data('carrier');
            this.carrierLastIndex = this.carrierLastSelected.val();*/

            this.carrierSelected = this.carrier.find('option:selected');
            this.carrierSelectedCarrier = this.carrierSelected.data('carrier');
            this.carrierSelectedIndex = this.carrierSelected.val();

            //this.price.html(carriers[this.carrierSelectedIndex].price_incl_tax * this.ordersCount);

            if (carriers[this.carrierSelectedCarrier][this.carrierSelectedIndex].service != 'droppoint') {
                this.droppointsSelect.hide();
            } else {
                this.droppointsSelect.show();

                if (this.hasDroppoints) {
                    this.load_droppoints();
                }
            }

            this.unblock();

            if (this.hasDroppoints) {
                if (droppoints[carriers[this.carrierSelectedCarrier][this.carrierSelectedIndex].carrier].error) {
                    this.errorDiv.html(coolrunner_l18n.no_droppoints).show();
                    this.droppointsSelect.html('');
                    this.droppointsSelect.append('<option selected value="-1">'+coolrunner_l18n.no_droppoints+'</option>');
                    this.submit.prop('disabled', true);
                }
            }
        },
        change_sizes: function(){
            this.sizeSelected = this.sizesSelect.find('option:selected');

            this.sizeLength.val(this.sizeSelected.val().split(':', 3)[0]);
            this.sizeWidth.val(this.sizeSelected.val().split(':', 3)[1]);
            this.sizeHeight.val(this.sizeSelected.val().split(':', 3)[2]);
        },
        change_weight: function(){
            this.weightSelected = this.weight.find('option:selected');
            this.weightSelectedIndex = this.weightSelected.val();
        },
        load_droppoints: function(){
            this.block();

            if (carriers[this.carrierSelectedCarrier][this.carrierSelectedIndex].service == 'droppoint') {
                //change droppoint options
                var show = droppoints[carriers[this.carrierSelectedCarrier][this.carrierSelectedIndex].carrier];

                if (!show['error']) {
                    this.droppointsSelect.html('');

                    this.droppointsSelect.append('<option selected value="auto">'+coolrunner_l18n.closest_droppoint+'</option>');

                    var max = (show['result'].length > 5) ? 5 : show['result'].length;

                    for (var i = 0; i < max; i++) {
                        this.droppointsSelect.append('<option value="'+show['result'][i].droppoint_id+'">'+show['result'][i].name+' '+(show['result'][i].distance/1000).toLocaleString(undefined,{ maximumFractionDigits: 2, minimumFractionDigits: 2 })+'km</option>');
                    }

                    this.unblock();
                } else {
                    this.droppointsSelect.append('<option selected value="-1">'+coolrunner_l18n.error_occurred+'</option>');
                    this.errorDiv.append(coolrunner_l18n.error_occurred).show();
                    this.unblock();
                }

            } else {
                this.unblock();
            }
        },
        submit_form: function(e){
            e.preventDefault();

            this.block();

            this.errorDiv.html('').hide();

            var current_carrier = carriers[this.carrierSelectedCarrier][this.carrierSelectedIndex];
            var max_size = current_carrier.max_size;
            var max_weight = current_carrier.max_weight;
            var chosenWheight = parcel_weights[this.weightSelectedIndex].weight_to;

            if (chosenWheight === undefined) {
                this.errorDiv.append(coolrunner_l18n.no_weight_chosen).show();

                this.unblock();
                return;
            } else if (chosenWheight > max_weight) {
                this.errorDiv.append(coolrunner_l18n.no_weight_available).show();

                this.unblock();
                return;
            }

            if (max_size['L'] !== undefined) {
                if (this.sizeLength.val() > max_size['L']) {
                    this.errorDiv.append(coolrunner_l18n.length_to_big.replace('%s', max_size['L'])).show();

                    this.unblock();
                    return;
                } else if (this.sizeLength.val() < 1) {
                    this.errorDiv.append(coolrunner_l18n.length_to_small).show();

                    this.unblock();
                    return;
                }
            }

            if (max_size['W'] !== undefined) {
                if (this.sizeWidth.val() > max_size['W'] ) {
                    this.errorDiv.append(coolrunner_l18n.width_to_big.replace('%s', max_size['W'])).show();

                    this.unblock();
                    return;
                } else if (this.sizeWidth.val() < 1) {
                    this.errorDiv.append(coolrunner_l18n.width_to_small).show();

                    this.unblock();
                    return;
                }
            }

            if (max_size['H'] !== undefined) {
                if (this.sizeHeight.val() > max_size['H']) {
                    this.errorDiv.append(coolrunner_l18n.height_to_big.replace('%s', max_size['H'])).show();

                    this.unblock();
                    return;
                } else if (this.sizeHeight.val() < 1) {
                    this.errorDiv.append(coolrunner_l18n.height_to_small).show();

                    this.unblock();
                    return;
                }
            }

            if (max_size['LC'] !== undefined){
                if ( eval(+this.sizeLength.val() + +(2 * +this.sizeWidth.val()) + +(2 * +this.sizeHeight.val())) > max_size['LC'] ) {
                    this.errorDiv.append(coolrunner_l18n.circumference.replace('%s', max_size['LC'])).show();

                    this.unblock();
                    return;
                }
            }

            //Calculate volumevægt
            var volume_weight_pdk = (((this.sizeLength.val() * this.sizeWidth.val() * this.sizeHeight.val())*280)/1000);

            if (current_carrier.carrier == 'pdk' && volume_weight_pdk > chosenWheight) {
                var vw_kg = volume_weight_pdk/1000;
                if (volume_weight_pdk > max_weight) {
                    this.errorDiv.append(coolrunner_l18n.parcel_to_big.replace('%s', vw_kg)).show();
                } else {
                    this.errorDiv.append(coolrunner_l18n.volumeweight_notify.replace('%s', vw_kg)).show();
                }

                this.unblock();
                return;
            }

            coolrunnerCreateShipment();

            //$(document).ajaxStop(this.unblock());
        },
        block: function(){
            this.loader.show();
            this.submit.addClass('shrink').prop('disabled', true);
        },
        unblock: function(){
            this.loader.hide();
            this.submit.removeClass('shrink').prop('disabled', false);
        },
    }

    $(document).ready(function(){
        var exists = false;
        try { coolrunner_form; exists = true;} catch(e) {}

        if (exists) {
            coolrunner_shipment.init(coolrunner_form, coolrunner_zone_from, coolrunner_orders_count, coolrunner_page);
        }
    });
});
