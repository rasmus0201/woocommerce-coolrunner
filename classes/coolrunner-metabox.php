<?php


class Coolrunner_Metabox
{

	public function __construct()
	{
		//add_action( 'add_meta_boxes_shop_order', array( $this, 'add_meta_box' ) );
		add_action('add_meta_boxes', array( $this, 'add' ));

		//Do we need this when we use AJAX??
		//add_action('save_post', array( $this, 'save' ));
	}

    //Do we need this when we use AJAX?? Maybe use this function when ajax is fired
    public function save($post_id)
    {
        return $post_id;
    }

	public function add($post_type)
	{
		if ( $post_type == 'shop_order' && isset($_GET['post']) && !empty($_GET['post'])) {
			add_meta_box(
				'coolrunner_metabox',
				'Coolrunner Levering',
				array($this, 'render'),
				'shop_order',
				'normal',
				'high'
			);
		}
	}

	public function render()
	{
		global $post, $post_id;

		// Use get_post_meta to retrieve an existing value from the database.
		$order_meta = get_post_meta($post_id, '_coolrunner_meta', true);

		//delete_post_meta($post_id, '_coolrunner_meta');

        $wc_order = new WC_Order($post_id);

        if (!empty($order_meta)) {
			//echo '<pre>';
			//var_dump($order_meta);
			//echo '</pre>';
            ?>
            <div class="shipment-details">
                <strong><?php _e('Info om forsendelsen', 'coolrunner'); ?></strong>

                <p class="created_at">
                    <?php _e('Oprettet: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['created_at']) ? $order_meta['created_at'] : '?'; ?>

                </p>
                <p class="carrier">
                    <?php _e('Fragtfirma: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['carrier']) ? Coolrunner_Helper::availableCarriers($order_meta['carrier']) : ''; ?>
                </p>
                <p class="carrier_service">
                    <?php _e('Modtager: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['carrier_service']) ? $order_meta['carrier_service'] : ''; ?>
                </p>
                <p class="carrier_product" style="margin-bottom: 10px;">
                    <?php _e('Levering: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['carrier_product']) ? $order_meta['carrier_product'] : ''; ?>
                </p>

                <p class="length">
                    <?php _e('Længde: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['length']) ? $order_meta['length'] : ''; ?>
                </p>
                <p class="width">
                    <?php _e('Bredde: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['width']) ? $order_meta['width'] : ''; ?>
                </p>
                <p class="height">
                    <?php _e('Højde: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['height']) ? $order_meta['height'] : ''; ?>
                </p>
                <p class="weight">
                    <?php _e('Vægt (g): ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['weight']) ? $order_meta['weight'] : ''; ?>
                </p>
                <p class="reference">
                    <?php _e('Reference: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['reference']) ? $order_meta['reference'] : ''; ?>
                </p>
                <p class="labelless_code">
                    <?php _e('Pakkekode: ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['labelless_code']) ? $order_meta['labelless_code'] : ''; ?>
                </p>
                <p class="package_number">
                    <?php _e('Pakkenummer (track&trace): ', 'coolrunner'); ?>
                    <?php echo isset($order_meta['package_number']) ? $order_meta['package_number'] : ''; ?>
                </p>
                <p class="pdf_link">
                    <?php _e('PDF Label: ', 'coolrunner'); ?>
                    <?php if (isset($order_meta['pdf_link'])) : ?>
                        <a href="<?php echo $order_meta['pdf_link']; ?>" target="_blank"><?php _e('Download', 'coolrunner'); ?></a>
                    <?php endif; ?>
                </p>
            </div>
            <?php
        } else {
    		/*$frieght_rates = new Coolrunner_Freight_Rate(Coolrunner_Setting::api_username(), Coolrunner_Setting::api_token());

            $rates = $frieght_rates->{$wc_order->get_shipping_country()}();

            if ($rates['error']) {
                echo Coolrunner_Helper::displayWarningNotice($rates['message']);

                return;
            }*/

            $droppoint_api = new Coolrunner_Droppoint(Coolrunner_Setting::api_username(), Coolrunner_Setting::api_token());

            $args = [
                'country_code'          => $wc_order->get_shipping_country(),
                'postcode'              => $wc_order->get_shipping_postcode(),
                'street'                => $wc_order->get_shipping_address_1(),
                'number_of_droppoints'  => 5,
            ];

            $droppoints = $droppoint_api->multipleCarriers(array_keys(Coolrunner_Helper::availableCarriers()), $args);

            if ($droppoints['error']) {
                echo Coolrunner_Helper::displayWarningNotice($droppoints['message']);

                return;
            }

            $parcel_sizes = Coolrunner_Parcel_Size::getSizes();
            ?>
                <div class="rbs-labels">
                    <div class="rbs-col-wrap col-12">
                        <div class="coolrunner_result" id="ajaxdone"></div>
                        <div class="coolrunner_form_fields">
                            <div class="rbs-input-row">
                                <select required class="coolrunner_carrier" name="carrier" id="carrier">
                                    <?php foreach(Coolrunner_Helper::carrierFreights() as $key => $rates): ?>
                                        <optgroup label="<?php echo Coolrunner_Helper::availableCarriers($key); ?>">
                                            <?php foreach($rates as $rate_id => $rate_name) : ?>
                                                <option data-carrier="<?php echo $key; ?>" value="<?php echo $rate_id; ?>"><?php echo $rate_name['name']; ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>

                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="rbs-input-row">
                                <select required class="coolrunner_weight" name="coolrunner_weight" id="coolrunner_weight">
                                    <option value="-1"><?php _e('Vælg vægt', 'coolrunner'); ?></option>
                                    <?php foreach(Coolrunner_Helper::parcelWeights() as $key => $weight): ?>
                                        <option value="<?php echo $key; ?>"<?php echo ($key == 0) ? ' selected': '';?>><?php echo $weight['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="rbs-input-row">
                                <input class="col-3 coolrunner_size_length" required id="size_length" size="6" type="number" step="1" min="1" placeholder="<?php _ex('Længde (cm)', 'shipment', 'coolrunner'); ?>" name="size_length">
                                <input class="col-3 coolrunner_size_width" required id="size_width" size="6" type="number" step="1" min="1" placeholder="<?php _ex('Bredde (cm)', 'shipment', 'coolrunner'); ?>" name="size_width">
                                <input class="col-3 coolrunner_size_height" required id="size_height" size="6" type="number" step="1" min="1" placeholder="<?php _ex('Højde (cm)', 'shipment', 'coolrunner'); ?>" name="size_height">
                                <?php if (!empty($parcel_sizes)) : ?>
                                    <label class="col-3" for="change_size"><?php _ex('Størrelser:', 'shipment', 'coolrunner'); ?></label>
                                    <select class="col-12 coolrunner_sizes" id="change_size">
                                        <?php $i = 0; foreach ($parcel_sizes as $size) : ?>
                                            <option<?php echo ($size['is_primary'] == true) ? ' selected' : ''; ?> value="<?php echo $size['width']. ':' . $size['length'] . ':' . $size['height']; ?>">
                                                <?php echo $size['name']; ?>
                                            </option>
                                        <?php $i++; endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            </div>

                            <div class="rbs-input-row" id="choose_droppoint">
                                <select required class="coolrunner_droppoints" name="droppoint" id="coolrunner_droppoints" class="rbs-input-select">
                                    <?php if ($droppoints['error']) : ?>
                                        <option value="-1"><?php _ex('Der skete en fejl', 'shipment', 'coolrunner'); ?></option>
                                    <?php else: ?>
                                        <option selected value="auto"><?php _ex('Tætteste afhentningssted / Valgt afhentningssted', 'shipment', 'coolrunner'); ?></option>
                                        <?php foreach ($droppoints['result']['dao']['result'] as $dao) : ?>
                                            <option value="<?php echo $dao['droppoint_id']; ?>">
                                                <?php echo $dao['name'].' ' . number_format($dao['distance']/1000, 2, ',', '.') . 'km';?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="rbs-input-row" id="shipment_price_wrapper">
                                <span class="price_tag"><?php _ex('Pris:', 'shipment', 'coolrunner'); ?></span>
                                <span class="coolrunner_price" id="shipment_price">
                                    <?php //echo $rates['result'][0]['price_incl_tax']; ?>
                                    ?
                                </span>
                                <span class="valuta"><?php _ex('DKK inkl. moms', 'shipment', 'coolrunner'); ?></span>
                                <span class="extra-text"><?php _ex('for', 'shipment', 'coolrunner'); ?></span>
                                <span class="coolrunner_labels_count" id="orders_count">1</span>
                                <span class="extra-text extra-text-plural">
                                    <?php _ex('label', 'shipment', 'coolrunner'); ?>
                                </span>
                            </div>

                            <div class="rbs-input-row">
                                <div class="coolrunner_response"></div>
                            </div>

                            <div class="rbs-input-row rbs-input-row-last">
                                <button id="coolrunner_submit" class="button button-primary coolrunner_submit"><?php _ex('Lav pakkelabel', 'shipment', 'coolrunner'); ?></button>
                                <div class="spinner-loader ajax_loader coolrunner_loading"><?php _ex('Loader...', 'shipment', 'coolrunner'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    var carriers = <?php echo json_encode(Coolrunner_Helper::carrierFreights()); ?>;
                    var parcel_weights = <?php echo json_encode(Coolrunner_Helper::parcelWeights()); ?>;
                    var droppoints = <?php echo json_encode($droppoints['result']); ?>;

                    <?php /*var carriers = <?php echo json_encode($rates['result']); ?>; */ ?>
                    var order_ids_imploded = '<?php echo $post_id; ?>';

                    var coolrunner_form = '.coolrunner_form_fields';
                    var coolrunner_zone_from = '<?php echo $wc_order->get_shipping_country(); ?>';
                    var coolrunner_orders_count = 1;
                    var coolrunner_page = 'metabox';

                    function coolrunnerCreateShipment() {
                        var carrierIndex = jQuery('.coolrunner_carrier option:selected').val();
                        var carrierName = jQuery('.coolrunner_carrier option:selected').data('carrier');

                        var droppointId = jQuery('.coolrunner_droppoints option:selected').val();

                        var weightIndex = jQuery('.coolrunner_weight option:selected').val();

                        var data = {
                            action: 'coolrunner_create_label',
                            carrier: carriers[carrierName][carrierIndex].carrier,
                            carrier_id: carriers[carrierName][carrierIndex].id,
                            carrier_product: carriers[carrierName][carrierIndex].product,
                            carrier_service: carriers[carrierName][carrierIndex].service,
                            droppoint_id: droppointId,

                            order_ids: order_ids_imploded,
                            width: jQuery('#size_width').val(),
                            length: jQuery('#size_length').val(),
                            height: jQuery('#size_height').val(),
                            weight: parcel_weights[weightIndex].weight_to,
                            country: coolrunner_zone_from
                        };


						jQuery.ajax({
							timeout: 15000,
							url: coolrunner_l18n.ajax_url,
							data: data,
							method: 'POST'
						}).always(function(data){
							if (data == 'success') {
                                location.reload();
                            } else {
                                jQuery('#coolrunner_submit').prop('disabled', false).removeClass('shrink');
                                jQuery('.coolrunner_loading').hide();

                                jQuery('.coolrunner_response').html(data).show();
                            }
						});

                        /*jQuery.post(coolrunner_l18n.ajax_url, data, function(data) {
                            if (data == 'success') {
                                location.reload();
                            } else {
                                jQuery('#coolrunner_submit').prop('disabled', false).removeClass('shrink');
                                jQuery('.coolrunner_loading').hide();

                                jQuery('.coolrunner_response').html(data).show();
                            }
                        });*/
                    }
                </script>
            <?php
        }
	}
}
