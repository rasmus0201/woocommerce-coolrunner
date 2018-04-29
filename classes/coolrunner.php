<?php


class Coolrunner
{
    protected $name;
    protected $tabs;
    protected $pluginUrl = '';

	public function __construct($name = 'coolrunner')
	{
        $this->name = $name;
        $this->tabs = ['prices', 'sizes', 'settings', 'bulk'];
        $this->pluginUrl = plugin_dir_url($this->name);
		add_action('plugins_loaded', array($this, 'init'), 0);
		add_action('admin_menu', array($this, 'menu') );
        add_action('admin_enqueue_scripts', array($this, 'add_admin_scripts'));
        add_action('admin_init', array($this, 'display_setting_fields') );
        add_action('admin_init', array($this, 'admin_init') );
        add_filter('plugin_action_links_'.$this->name, array($this, 'settings_link') );

        add_action('init', array($this, 'init'));
	}

	public function init()
    {
        //Init plugin
        add_action('wp_ajax_coolrunner_create_bulk_labels', array($this, 'coolrunner_create_bulk_labels'), 0);
        add_action('wp_ajax_coolrunner_create_label', array($this, 'coolrunner_create_label'), 0);
        add_action('wp_ajax_coolrunner_bulk_view_pdf', array($this, 'coolrunner_bulk_view_pdf'), 0);
    }

    public function admin_init()
    {
        //Init admin plugin
        global $pagenow;

        if ($pagenow == 'admin.php' && $_GET['tab'] == 'prices') {
            add_action('admin_enqueue_scripts', array($this, 'add_jquery_dialog'));
        }

        if ($pagenow == 'post.php') {
            new Coolrunner_Metabox;
        }

        add_action('admin_footer-edit.php', array($this, 'add_bulk_method_to_select'));
        add_action('load-edit.php', array($this, 'add_bulk_method_to_select_action'), 10);

        $this->hasPost();
	}

	public function settings_link($links)
    {
		$links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=coolrunner&tab=settings') ) .'">'.__('Indstillinger', 'coolrunner').'</a>';

        return $links;
	}

	public function menu()
    {
		$page = add_submenu_page( 'woocommerce', 'Coolrunner', 'Coolrunner', 'manage_options', 'coolrunner', array($this, 'pages'));

		//add_action( 'admin_print_styles-' . $page, 'coolrunner_rbs_styles' );
		//add_action( 'load-' . $page , 'coolrunner_app_output_buffer');
	}

	public function pages()
    {
        $active_tab = isset($_GET[ 'tab' ]) ? $_GET[ 'tab' ] : 'sizes';
        //$active_tab = !array_key_exists($active_tab, $this->tabs) ? 'sizes' : $active_tab;
        ?>
        <div class="wrap">
            <h2>Coolrunner</h2>
            <?php settings_errors(); ?>

            <h2 class="nav-tab-wrapper">
                <a href="?page=coolrunner&tab=prices" class="nav-tab <?php echo $active_tab == 'prices' ? 'nav-tab-active' : ''; ?>">Fragtpriser</a>
                <a href="?page=coolrunner&tab=sizes" class="nav-tab <?php echo $active_tab == 'sizes' ? 'nav-tab-active' : ''; ?>">Pakkestørrelser</a>
                <a href="?page=coolrunner&tab=settings" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>">Indstillinger</a>
            </h2>


            <?php
                switch ($active_tab) {
                    case 'prices':
                        $this->prices_tab();
                        break;
                    case 'sizes':
                        $this->sizes_tab();
                        break;
                    case 'settings':
                        $this->settings_tab();
                        break;
                    case 'bulk':
                        $this->bulk_tab();
                        break;
                    default:
                        $this->sizes_tab();
                        break;
                }
            ?>
        </div>
        <?php
	}

    public function add_admin_scripts()
    {
        wp_enqueue_script('coolrunner-admin', $this->pluginUrl.'assets/js/backend.js', array('jquery'), null, false);

        wp_localize_script(
            'coolrunner-admin',
            'coolrunner_l18n',
            array(
                'ajax_url'              => admin_url( 'admin-ajax.php' ),
                'no_droppoints'         => _x('Der blev ikke fundet nogle pakkeshops', 'js', 'coolrunner'),
                'error_occurred'        => _x('Der skete en fejl', 'js', 'coolrunner'),
                'closest_droppoint'     => _x('Tætteste afhentningssted / Valgt afhentningssted', 'js', 'coolrunner'),

                'length_to_big'         => _x('Max længde er %scm', 'js', 'coolrunner'),
                'length_to_small'       => _x('Længde skal være >= 1 cm', 'js', 'coolrunner'),

                'width_to_big'          => _x('Max bredde er %scm', 'js', 'coolrunner'),
                'width_to_small'        => _x('Bredde skal være >= 1 cm', 'js', 'coolrunner'),

                'height_to_big'         => _x('Max højde er %scm', 'js', 'coolrunner'),
                'height_to_small'       => _x('Højde skal være >= 1 cm', 'js', 'coolrunner'),

                'circumference'     => _x('Længde + omkreds må ikke overskride: %scm', 'js', 'coolrunner'),
                'parcel_to_big'     => _x('Forsendelsen er for stor. Volumevægt: %skg', 'js', 'coolrunner'),
                'volumeweight_notify'  => _x('OBS: Pakkens volumevægt er: %skg, du burde vælge en anden mulighed.', 'js', 'coolrunner'),

                'no_weight_chosen'      => _x('Du skal vælge vægt', 'js', 'coolrunner'),
                'no_weight_available'   => _x('Pakkens vægt er for stor, vælg en anden leveringsmetode.', 'js', 'coolrunner'),
            )
        );

        wp_enqueue_style('coolrunner-admin', $this->pluginUrl.'assets/css/backend.css');
    }

    public function add_jquery_dialog()
    {
        wp_deregister_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-core', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), null);

        wp_register_style('jquery-ui', 'http://jquery-ui-bootstrap.github.io/jquery-ui-bootstrap/css/custom-theme/jquery-ui-1.10.3.custom.css');
        wp_enqueue_style( 'jquery-ui' );
    }

    private function hasPost()
    {
        if (isset($_POST['new_size'])) {
            $this->handlePostPatchSize();
        } else if (isset($_POST['update_size'])) {
            $this->handlePostPatchSize();
        } else if (isset($_POST['primary_size'])) {
            $this->handleChangePrimarySize();
        } else if (isset($_POST['delete_size'])) {
            $this->handleDeleteSize();
        }

        /*
        $url_parameters = isset($_GET['tab']) ? 'updated=true&tab='.$_GET['tab'] : 'updated=true';
        if ( isset($_POST['coolrunner-settings-submit']) ) {
            if ( $_POST['coolrunner-settings-submit'] == 'Y' ) {
                $this->coolrunner_rbs_store_settings();

                wp_redirect(admin_url('admin.php?page=coolrunner&'.$url_parameters));
                exit;
            } else {
                return $this->coolrunner_rbs_warning_notice(_x('Noget gik galt, prøv igen.', 'settings', 'woocommerce-coolrunner-rbs'));
            }
        } else if ( isset($_POST['coolrunner-freight-method-submit']) ) {
            if ( $_POST['coolrunner-freight-method-submit'] == 'Y' ) {
                $store_freight_method = $this->coolrunner_rbs_store_freight_method_on_shop();

                if ($store_freight_method === true) {
                    wp_redirect(admin_url('admin.php?page=coolrunner&'.$url_parameters));
                    exit;
                } else {
                    return $this->coolrunner_rbs_warning_notice($store_freight_method);
                }
            } else {
                return $this->coolrunner_rbs_warning_notice('Noget gik galt, prøv igen.');
            }
        } else if ( isset($_POST['coolrunner-add-phone-email-field-submit']) ) {
            if ( $_POST['coolrunner-add-phone-email-field-submit'] == 'Y' ) {
                $store_phone_email_field = $this->coolrunner_rbs_store_custom_fields_settings();

                if ($store_phone_email_field === true) {
                    wp_redirect(admin_url('admin.php?page=coolrunner&'.$url_parameters));
                    exit;
                } else {
                    return $this->coolrunner_rbs_warning_notice($store_phone_email_field);
                }
            } else {
                return $this->coolrunner_rbs_warning_notice(_x('Noget gik galt, prøv igen.', 'settings', 'woocommerce-coolrunner-rbs'));
            }
        } else if ( isset($_POST['coolrunner-add-company-fields-submit'])) {
            if ( $_POST['coolrunner-add-company-fields-submit'] == 'Y' ) {
                $store_company_fields = $this->coolrunner_rbs_store_company_fields_settings();

                if ($store_company_fields === true) {
                    wp_redirect(admin_url('admin.php?page=coolrunner&'.$url_parameters));
                    exit;
                } else {
                    return $this->coolrunner_rbs_warning_notice($store_company_fields);
                }
            }
        }*/
    }

    private function handlePostPatchSize()
    {
        $error = false;

        if (empty($_POST['name'])) {
            $error = _x('Du skal udylde alle felter.', 'settings', 'coolrunner');

        } else if(strlen($_POST['size_name']) > 30){
            $error = _x('Navnet må max være 30 tegn.', 'settings', 'coolrunner');

        } else if (empty($_POST['sizes']['l'])) {
            $error = _x('Du skal udylde alle felter.', 'settings', 'coolrunner');

        } else if (empty($_POST['sizes']['w'])) {
            $error = _x('Du skal udylde alle felter.', 'settings', 'coolrunner');

        } else if (empty($_POST['sizes']['h'])) {
            $error = _x('Du skal udylde alle felter.', 'settings', 'coolrunner');

        } else if (
                !is_numeric($_POST['sizes']['l']) ||
                !is_numeric($_POST['sizes']['w']) ||
                !is_numeric($_POST['sizes']['h'])
            )
        {
            $error = _x('Pakkestørrelse skal være et tal.', 'settings', 'coolrunner');
        }

        $isPrimary = (isset($_POST['is_primary']) && $_POST['is_primary'] == 'y') ? true : false;
        $sizeId = isset($_POST['id']) ? (int)$_POST['id'] : Coolrunner_Parcel_Size::getNextSizeId();
        $successNotice = isset($_POST['id']) ? _x('Pakkestørrelse opdateret!', 'settings', 'coolrunner') : _x('Ny pakkestørrelse tilføjet!', 'settings', 'coolrunner') ;


        if ($error) {
            return Coolrunner_Helper::displayWarningNotice($error);
        }

        $length = abs($_POST['sizes']['l']);
        $width = abs($_POST['sizes']['w']);
        $height = abs($_POST['sizes']['h']);

        $size = [
            'id'                => $sizeId,
            'name'              => sanitize_text_field($_POST['name']),
            'length'            => $length,
            'width'             => $width,
            'height'            => $height,
            'is_measure'        => $isPrimary
        ];

        Coolrunner_Parcel_Size::saveSize($size);

        return Coolrunner_Helper::displaySuccessNotice($successNotice);
    }

    private function handleChangePrimarySize()
    {
        if (empty($_POST['id']) && $_POST['id'] !== '0') {
            return Coolrunner_Helper::displayWarningNotice(_x('ID\'et skal være sat.', 'settings', 'coolrunner'));
        }

        Coolrunner_Parcel_Size::changePrimarySize($_POST['id']);

        return Coolrunner_Helper::displaySuccessNotice(_x('Primær pakkestørrelse blev ændret.', 'settings', 'coolrunner'));
    }

    private function handleDeleteSize()
    {
        if (empty($_POST['id']) && $_POST['id'] !== '0') {
            return Coolrunner_Helper::displayWarningNotice(_x('ID\'et skal være sat.', 'settings', 'coolrunner'));
        }

        Coolrunner_Parcel_Size::deleteSize($_POST['id']);

        return Coolrunner_Helper::displaySuccessNotice(_x('Pakkestørrelsen blev slettet.', 'settings', 'coolrunner'));
    }

    private function prices_tab()
    {
        $frieght_rates = new Coolrunner_Freight_Rate(Coolrunner_Setting::api_username(), Coolrunner_Setting::api_token());
        $zone_to = isset($_GET['zone_to']) ? strtoupper($_GET['zone_to']) : 'DK';

        if (!array_key_exists($zone_to, Coolrunner_Helper::countryCodes())) {
            $zone_to = 'DK';
        }

        ?>
        <div style="margin: 10px 0;">
           <label for="zone_to"><?php _e('Vælg land', 'coolrunner'); ?></label>
            <select name="zone_to" id="zone_to">
                <?php foreach(Coolrunner_Helper::countryCodes() as $code => $country) : ?>
                    <option value="<?php echo $code; ?>"<?php echo ($zone_to == $code) ? ' selected' : ''; ?>>
                        <?php echo $country; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <script>
                var select = document.getElementById('zone_to');

                select.addEventListener('change', function(e){
                    var url = new URL(window.location.href);
                    url.searchParams.set('zone_to', this.value);
                    window.location.replace(url.toString());
                });
            </script>
        </div>
        <?php
            $result = $frieght_rates->{$zone_to}();

            if ($result['error']) {
                echo Coolrunner_Helper::displayWarningNotice($result['message']);
            } else {
            ?>
                <h2><?php echo Coolrunner_Helper::countryCodes()[$zone_to]; ?></h2>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="manage-column column-type" scope="col" style="padding: 8px 10px;vertical-align: inherit;">Type</th>
                            <th class="manage-column column-price_excl_tax num" scope="col" style="padding: 8px 10px;vertical-align: inherit;">
                                Pris ekskl. moms
                            </th>
                            <th class="manage-column column-info check-column" scope="col" style="padding: 8px 10px;vertical-align: inherit;">
                                Info
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; foreach($result['result'] as $key => $price) : ?>
                            <tr<?php echo ($i % 2 == 0) ? ' class="alternate"' : '';?>>
                                <td class="column-type" scope="col" style="padding: 4px 10px;vertical-align: inherit;">
                                    <?php echo $price['title']; ?>
                                </td>
                                <td class="column-price_excl_tax num" scope="row" style="padding: 4px 10px;vertical-align: inherit;">
                                    <?php echo $price['price_excl_tax']; ?>
                                </td>
                                <td class="column-info check-column" scope="col" data-key="<?php echo $key; ?>" style="padding: 4px 10px;vertical-align: inherit;">
                                    <span class="dashicons dashicons-info"></span>
                                    <div class="coolrunner-more-info" id="dialog-coolrunner-info-<?php echo $key; ?>" title="Mere info">
                                        <div class="content">
                                            <?php foreach($price['max_size'] as $key => $size) : ?>
                                                Max <?php echo '<code>' . $key . '</code>: ' . $size; ?><br>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php $i++; endforeach; ?>
                    </tbody>
                </table>
                <script type="text/javascript">
                    jQuery('.coolrunner-more-info').dialog({
                        autoOpen: false,
                        width: 400,
                        closeText: '',
                        resizable: false,
                        draggable: false,
                        buttons: {
                            'Luk': function() {
                                jQuery(this).dialog('close');
                            },
                        }
                    });

                    jQuery('td.column-info.check-column .dashicons-info').on('click', function() {
                        jQuery('.ui-dialog-content').dialog('close');

                        var key = jQuery(this).parent().data('key');
                        jQuery('#dialog-coolrunner-info-'+key).dialog('open');
                    });
                </script>
            <?php
            }
    }

    private function sizes_tab()
    {
        ?>
        <form method="post" action="">
            <table class="widefat form-table fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th><?php _ex('Pakke navn', 'settings', 'coolrunner'); ?></th>
                        <th><?php _ex('Max. pakkestørrelse', 'settings', 'coolrunner'); ?></th>
                        <th><i><?php _ex('- (længde, bredde og højde - i cm)', 'settings', 'coolrunner'); ?></i></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input required placeholder="<?php _ex('Fx. Bobleplastkuvert', 'settings', 'coolrunner'); ?>" type="text" name="name" max="30">
                        </td>
                        <td>
                            <input required type="tel" name="sizes[l]" placeholder="<?php _ex('Længde', 'settings', 'coolrunner'); ?>" pattern="\d{1,3}">
                            <input required type="tel" name="sizes[w]" placeholder="<?php _ex('Bredde', 'settings', 'coolrunner'); ?>" pattern="\d{1,3}">
                            <input required type="tel" name="sizes[h]" placeholder="<?php _ex('Højde', 'settings', 'coolrunner'); ?>" pattern="\d{1,3}">
                        </td>
                        <td>
                            <input class="button button-primary" name="new_size" type="submit" value="<?php _ex('Tilføj', 'settings', 'coolrunner'); ?>">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <hr>
        <?php
            $Coolrunner_Parcel_Size = new Coolrunner_Parcel_Size();
            $sizes = $Coolrunner_Parcel_Size->getSizes();

            if(!empty($sizes)): ?>
                <?php foreach($sizes as $id => $size): ?>
                    <form method="post" action="">
                        <table class="form-table coolrunner-table-parcel-sizes">
                            <tr>
                                <td>
                                    <input required placeholder="<?php _ex('Fx. Bobleplastkuvert', 'settings', 'coolrunner'); ?>" type="text" name="name" max="30" value="<?php echo $size['name']; ?>">
                                </td>
                                <td>
                                    <input required type="tel" name="sizes[l]" placeholder="<?php _ex('Længde', 'settings', 'coolrunner'); ?>" pattern="\d{1,3}" value="<?php echo $size['length']; ?>">
                                    <input required type="tel" name="sizes[w]" placeholder="<?php _ex('Bredde', 'settings', 'coolrunner'); ?>" pattern="\d{1,3}" value="<?php echo $size['width']; ?>">
                                    <input required type="tel" name="sizes[h]" placeholder="<?php _ex('Højde', 'settings', 'coolrunner'); ?>" pattern="\d{1,3}" value="<?php echo $size['height']; ?>">
                                </td>
                                <td class="size_handler update_size">
                                    <input required type="hidden" name="id" value="<?php echo $size['id']; ?>">
                                    <input required type="hidden" name="is_primary" value="<?php echo ($size['is_primary'] == true) ? 'y' : 'n'; ?>">
                                    <input required class="button button-primary" type="submit" name="update_size" value="<?php _ex('Opdater', 'settings', 'coolrunner'); ?>">
                                    <input <?php echo ($size['is_primary'] == true) ? 'disabled ': ' '; ?> class="button button-warning" type="submit" name="primary_size" value="<?php _ex('Primær', 'settings', 'coolrunner'); ?>">
                                </td>
                                <td class="size_handler delete_size">
                                    <input class="button button-danger" type="submit" name="delete_size" value="<?php _ex('Slet', 'settings', 'coolrunner'); ?>">
                                </td>
                            </tr>
                        </table>
                    </form>
                <?php endforeach; ?>
            <?php endif;?>
        <?php
    }

    private function settings_tab()
    {
        ?>
        <form method="post" action="options.php">
            <?php
                settings_fields('coolrunner-settings');
                do_settings_sections('coolrunner-options');
                submit_button();
            ?>
        </form>
        <?php
    }

    public function setting_fields()
    {
        $settings = [
            [
                'id'            => 'section-1',
                'display_title' => 'API adgang',
                'fields'        => [
                    'coolrunner_api_username'   => ['id' => 'coolrunner_api_username', 'label' => 'API Username (e-mail)', 'type' => 'text'],
                    'coolrunner_api_token'      => ['id' => 'coolrunner_api_token', 'label' => 'API token', 'type' => 'text'],
                ],
            ],
            [
                'id'            => 'section-2',
                'display_title' => 'Indstillinger',
                'fields'        => [
                    'coolrunner_sender_name'        => [
                        'id'        => 'coolrunner_sender_name',
                        'label'     => 'Afsender firma',
                        'type'      => 'text',
                    ],
                    'coolrunner_sender_street1'     => [
                        'id'        => 'coolrunner_sender_street1',
                        'label'     => 'Afsender adresse',
                        'type'      => 'text',
                    ],
                    'coolrunner_sender_street2'     => [
                        'id'            => 'coolrunner_sender_street2',
                        'label'         => 'Afsender adresse 2',
                        'type'          => 'text',
                        'placeholder'   => 'Valgfri'
                    ],
                    'coolrunner_sender_postcode'    => [
                        'id'        => 'coolrunner_sender_postcode',
                        'label'     => 'Afsender postnummer',
                        'type'      => 'text',
                    ],
                    'coolrunner_sender_city'        => [
                        'id'        => 'coolrunner_sender_city',
                        'label'     => 'Afsender by',
                        'type'      => 'text',
                    ],
                    'coolrunner_sender_phone'        => [
                        'id'        => 'coolrunner_sender_phone',
                        'label'     => 'Afsender telefon',
                        'type'      => 'text',
                    ],
                    'coolrunner_sender_email'        => [
                        'id'        => 'coolrunner_sender_email',
                        'label'     => 'Afsender e-mail',
                        'type'      => 'text',
                    ],
                    'coolrunner_sender_attention'        => [
                        'id'        => 'coolrunner_sender_attention',
                        'label'     => 'Afsender att',
                        'type'      => 'text',
                    ],
                    'coolrunner_label_format'        => [
                        'id'        => 'coolrunner_label_format',
                        'label'     => 'Label format',
                        'type'      => 'radio',
                        'options'   => [
                            0 => [
                                'value'     => 'A4',
                                'desc'      => 'A4',
                                'default'   => true,
                            ],
                            1 => [
                                'value'     => 'LabelPrint',
                                'desc'      => 'LabelPrint',
                            ],
                        ]
                    ],
                    'coolrunner_sender_country'        => [
                        'id'        => 'coolrunner_sender_country',
                        'label'     => 'Afsender land',
                        'type'      => 'text',
                        'value'     => 'DK',
                        'readonly'  => true,
                        'dummy'     => true,
                    ],
                ]
            ],
        ];
        return $settings;
    }

    public function display_setting_fields()
    {
        $settings = $this->setting_fields();

        foreach ($settings as $setting) {
            add_settings_section($setting['id'], $setting['display_title'], null, 'coolrunner-options');

            foreach ($setting['fields'] as $field) {
                add_settings_field($field['id'], $field['label'], array($this, 'generate_fields'), 'coolrunner-options', $setting['id'], $field);
                register_setting('coolrunner-settings', $field['id']);
            }
        }
    }

    public function generate_fields(array $args)
    {
        switch ($args['type']) {
            case 'text':
                ?>
                <?php if (isset($args['dummy']) && isset($args['value'])) : ?>
                    <input type="text" id="<?php echo $args['id']; ?>" value="<?php echo $args['value'] ?>"<?php echo ( isset($args['readonly']) ) ? ' readonly' : ''; ?> placeholder="<?php echo isset($args['placeholder']) ? $args['placeholder'] : ''; ?>"/>
                <?php else: ?>
                    <input type="text" name="<?php echo $args['id']; ?>" id="<?php echo $args['id']; ?>" value="<?php echo get_option($args['id']); ?>"<?php echo ( isset($args['readonly']) ) ? ' readonly' : ''; ?> placeholder="<?php echo isset($args['placeholder']) ? $args['placeholder'] : ''; ?>"/>
                <?php endif;?>
                <small><?php echo $args['desc']; ?></small>
                <?php
                break;
            case 'number':
                ?>
                <input type="number" name="<?php echo $args['id']; ?>" id="<?php echo $args['id']; ?>" value="<?php echo get_option($args['id']); ?>"<?php echo ( isset($args['readonly']) ) ? ' readonly' : ''; ?>/>
                <small><?php echo $args['desc']; ?></small>
                <?php
                break;
            case 'checkbox':
                ?>
                <input type="checkbox" name="<?php echo $args['id']; ?>" value="1" <?php checked(1, get_option($args['id']), true); ?> />
                <small><?php echo $args['desc']; ?></small>
                <?php
                break;
            case 'radio':
                ?>
                <?php foreach ($args['options'] as $option): ?>
                    <?php $input_id = sanitize_title($args['id'].'_'.$option['value']); ?>
                    <input type="radio" name="<?php echo $args['id']; ?>" id="<?php echo $input_id; ?>" value="<?php echo $option['value']; ?>"
                    <?php if ( (get_option($args['id'], '') == '') && isset($option['default']) ) : ?>
                        checked
                    <?php elseif( get_option($args['id']) == $option['value'] ) : ?>
                        checked
                    <?php endif; ?>
                    />
                    <label for="<?php echo $input_id; ?>"><small><?php echo $option['desc']; ?></small></label>
                <?php endforeach; ?>
                <?php
                break;
        }
    }

    private function bulk_tab()
    {
        if (!isset($_GET['orders'])) {
            wp_redirect(get_admin_url().'edit.php?post_type=shop_order');
            exit;
        }

        $order_ids = explode(',', sanitize_text_field($_GET['orders']));

        if(empty($order_ids)) {
            wp_redirect(get_admin_url().'edit.php?post_type=shop_order');
            exit;
        }

        $shipping_countries = [];

        foreach ($order_ids as $key => $id) {
            try {
                $order = new WC_Order($id);
                $country = $order->get_shipping_country();

                if ($country != 'DK') {
                    unset($order_ids[$key]);
                }
            } catch (Exception $e) {
                echo Coolrunner_Helper::displayWarningNotice(__('En eller flere ordrer er ikke tilgængelig'));
                return;
            }
        }

        if ( empty($order_ids) ) {
            echo Coolrunner_Helper::displayWarningNotice(__('Der blev ikke fundet nogle ordrer, som skal sendes til Danmark'));
            return;
        }

        $count = count($order_ids);

        /*$frieght_rates = new Coolrunner_Freight_Rate(Coolrunner_Setting::api_username(), Coolrunner_Setting::api_token());

        $rates = $frieght_rates->dk();

        if ($rates['error']) {
            echo Coolrunner_Helper::displayWarningNotice($rates['message']);

            return;
        }*/

        $parcel_sizes = Coolrunner_Parcel_Size::getSizes();
        ?>
        <?php echo Coolrunner_Helper::displayWarningNotice(_x('Denne metode vil lave alle valgte ordre til ens: fragtfirma, størrelse & vægt. Denne metode fungerer kun med ordrer der sendes til DK.', 'bulk', 'coolrunner'));
        ?>
            <div class="rbs-labels">
                <div class="rbs-col-wrap col-12">
                    <div id="ajaxdone"></div>
                    <form method="post" action="" class="coolrunner_form">
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
                                <option selected value="auto"><?php _ex('Tætteste afhentningssted / Valgt afhentningssted', 'shipment', 'coolrunner'); ?></option>
                            </select>
                        </div>

                        <div class="rbs-input-row" id="shipment_price_wrapper">
                            <span class="price_tag"><?php _ex('Pris:', 'shipment', 'coolrunner'); ?></span>
                            <span class="coolrunner_price" id="shipment_price">
                                <?php //echo $rates['result'][0]['price_incl_tax'] * $count; ?>
                                ?
                            </span>
                            <span class="valuta"><?php _ex('DKK inkl. moms', 'shipment', 'coolrunner'); ?></span>
                            <span class="extra-text"><?php _ex('for', 'shipment', 'coolrunner'); ?></span>
                            <span class="coolrunner_labels_count" id="orders_count">
                                <?php echo $count; ?>
                            </span>
                            <span class="extra-text extra-text-plural">
                                <?php

                                if ($count > 1) {
                                     _ex('labels', 'shipment', 'coolrunner');
                                } else {
                                     _ex('label', 'shipment', 'coolrunner');
                                }

                                ?>
                            </span>
                        </div>

                        <div class="rbs-input-row">
                            <div class="coolrunner_response"></div>
                        </div>

                        <div class="rbs-input-row rbs-input-row-last">
                            <button id="coolrunner_submit" class="button button-primary coolrunner_submit">
                                <?php
                                if ($count > 1) {
                                     _ex('lav pakkelabels', 'shipment', 'coolrunner');
                                } else {
                                     _ex('lav pakkelabel', 'shipment', 'coolrunner');
                                }
                                ?>
                            </button>
                            <div class="spinner-loader ajax_loader coolrunner_loading"><?php _ex('Loader...', 'shipment', 'coolrunner'); ?></div>
                        </div>
                    </form>
                </div>
            </div>
            <script>
                //var droppoints = {};
                var carriers = <?php echo json_encode(Coolrunner_Helper::carrierFreights()); ?>;
                var parcel_weights = <?php echo json_encode(Coolrunner_Helper::parcelWeights()); ?>;
                var order_ids_imploded = '<?php echo trim(join(',', $order_ids), ','); ?>';

                var coolrunner_form = '.coolrunner_form';
                var coolrunner_zone_from = 'DK';
                var coolrunner_orders_count = <?php echo $count; ?>;
                var coolrunner_page = 'bulk';

                function coolrunnerCreateShipment() {
                    var carrierIndex = jQuery('.coolrunner_carrier option:selected').val();
                    var carrierName = jQuery('.coolrunner_carrier option:selected').data('carrier');

                    var droppointId = jQuery('.coolrunner_droppoints option:selected').val();

                    var weightIndex = jQuery('.coolrunner_weight option:selected').val();

                    var data = {
                        action: 'coolrunner_create_bulk_labels',
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

                    jQuery.post(coolrunner_l18n.ajax_url, data, function(data) {
                        jQuery('.coolrunner_form, .warning.notice.notice-warning').remove();

                        jQuery('#ajaxdone').html(data).show();
                    });
                }
            </script>
        <?php
    }

    public function add_bulk_method_to_select()
    {
        global $post_type;

        if($post_type == 'shop_order') {
            ?>
                <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery('<option>').val('coolrunner').text( "<?php _ex('Coolrunner Label', 'bulk', 'coolrunner'); ?>" ).appendTo("select[name='action']");
                        jQuery('<option>').val('coolrunner').text( "<?php _ex('Coolrunner Label', 'bulk', 'coolrunner'); ?>" ).appendTo("select[name='action2']");
                    });
                </script>
            <?php
        }
    }

    public function add_bulk_method_to_select_action()
    {
        $post_ids = (isset($_GET['post'])) ? $_GET['post'] : 0;

        //Get the action
        $wp_list_table = _get_list_table('WP_Posts_List_Table');
        $action = $wp_list_table->current_action();

        switch($action) {
            case 'coolrunner':
                wp_redirect(get_admin_url().'admin.php?page=coolrunner&tab=bulk&orders='.join(',', $post_ids));
                exit;
                break;
            default: return;
        }

        wp_redirect(get_admin_url().'edit.php?post_type=shop_order');
        exit;
    }

    public function coolrunner_create_label()
    {
        $required_keys = [
            'action',
            'carrier',
            'carrier_id',
            'carrier_product',
            'carrier_service',
            'droppoint_id',
            'order_ids',
            'width',
            'length',
            'height',
            'weight',
            'country',
        ];

        $missing_params = array_diff_key(array_flip($required_keys), $_POST);

        if (!empty($missing_params)) { // We are missing some parameters
            echo __('Mangler parametre: ', 'coolrunner'). json_encode($missing_params);

            wp_die();
        }

        $order_meta = get_post_meta($_POST['order_ids'], '_coolrunner_meta', true);

        if (empty($order_meta)) {
            $order = new WC_Order($_POST['order_ids']);

            if ($order->get_shipping_country() != 'DK') {
                _e('Der kan kun sendes til Danmark foreløbigt.', 'coolrunner');

                wp_die();
            }

            if (!isset(Coolrunner_Helper::carrierFreights($_POST['carrier'])[(int)$_POST['carrier_id']])) {
                _e('Fragtmuligheden blev ikke fundet.', 'coolrunner');

                wp_die();
            }

            $droppoint_id = sanitize_text_field($_POST['droppoint_id']);

            $api_username = Coolrunner_Setting::api_username();
            $api_token = Coolrunner_Setting::api_token();

            //Get correct droppoint data
            $droppoint_api = new Coolrunner_Droppoint($api_username, $api_token);
            $droppoints = $droppoint_api->{$_POST['carrier']}(
                $order->get_shipping_country(),
                $order->get_shipping_postcode(),
                $order->get_shipping_address_1()
            );

            if ($droppoints['error']) {
                _e('Der skete en fejl ved hentning af pakkeshops', 'coolrunner');

                wp_die();
            }

            $chosen_droppoint = [];

            //TODO:
            //Check if customer has specified droppoint
            $closest_droppoint = array_slice($droppoints['result'], 0, 1)[0];

            foreach ($droppoints['result'] as $droppoint) {
                if ($droppoint_id === 'auto') {
                    $chosen_droppoint = $closest_droppoint;
                    break;
                } else if ($droppoint['droppoint_id'] == $droppoint_id) {
                    $chosen_droppoint = $droppoint;
                }
            }

            if (empty($chosen_droppoint)) {
                _e('Der blev ikke fundet en pakkeshop', 'coolrunner');

                wp_die();
            }

            $receiver_name = $order->get_shipping_first_name().' '.$order->get_shipping_last_name();
            $receiver_attention = '';

            if ( !empty($order->get_shipping_company()) ) {
                $receiver_attention = $receiver_name;
                $receiver_name = $order->get_shipping_company();
            }


            $args = [
                "receiver_name"         => $receiver_name,
                "receiver_attention"    => $receiver_attention,
                "receiver_street1"      => $order->get_shipping_address_1(),
                "receiver_street2"      => $order->get_shipping_address_2(),
                "receiver_zipcode"      => $order->get_shipping_postcode(),
                "receiver_city"         => $order->get_shipping_city(),
                "receiver_country"      => $order->get_shipping_country(),
                "receiver_phone"        => $order->get_billing_phone(),
                "receiver_email"        => $order->get_billing_email(),
                "receiver_notify"       => true,
                "receiver_notify_sms"   => $order->get_billing_phone(),
                "receiver_notify_email" => $order->get_billing_email(),

                "sender_name"           => Coolrunner_Setting::sender_name(),
                "sender_attention"      => Coolrunner_Setting::sender_attention(),
                "sender_street1"        => Coolrunner_Setting::sender_street1(),
                "sender_street2"        => Coolrunner_Setting::sender_street2(),
                "sender_zipcode"        => Coolrunner_Setting::sender_postcode(),
                "sender_city"           => Coolrunner_Setting::sender_city(),
                "sender_country"        => Coolrunner_Setting::sender_country(),
                "sender_phone"          => Coolrunner_Setting::sender_phone(),
                "sender_email"          => Coolrunner_Setting::sender_email(),

                "droppoint"             => ($_POST['carrier_service'] == 'droppoint') ? true : false,
                "droppoint_id"          => $chosen_droppoint['droppoint_id'],
                "droppoint_name"        => $chosen_droppoint['name'],
                "droppoint_street1"     => $chosen_droppoint['address']['street'],
                "droppoint_zipcode"     => $chosen_droppoint['address']['postal_code'],
                "droppoint_city"        => $chosen_droppoint['address']['city'],
                "droppoint_country"     => $chosen_droppoint['address']['country_code'],

                "carrier"               => sanitize_text_field($_POST['carrier']),
                "carrier_product"       => sanitize_text_field($_POST['carrier_product']),
                "carrier_service"       => sanitize_text_field($_POST['carrier_service']),

                "length"                => sanitize_text_field($_POST['length']),
                "width"                 => sanitize_text_field($_POST['width']),
                "height"                => sanitize_text_field($_POST['height']),
                "weight"                => sanitize_text_field($_POST['weight']),

                "reference"             => $order->get_order_number(),
                "description"           => substr($order->customer_note, 0, 50),
                "comment"               => esc_html__('Generated by Bundsgaard plugin: ', 'coolrunner') . date('Y-m-d H:i:s'),
                "label_format"          => Coolrunner_Setting::label_format(),

                "insurance"             => false,
                "insurance_value"       => $order->total,
                "insurance_currency"    => $order->currency,

                "customs_value"         => $order->total,
                "customs_currency"      => $order->currency
            ];


            $shipment_api = new Coolrunner_Shipment($api_username, $api_token);

            $parcel = $shipment_api->create($args);

            if ($parcel['error']) {
                _e('Der skete en fejl. Label blev ikke købt', 'coolrunner');
                if (isset($parcel['result']['message'])) {
                    echo '<br>'.json_decode($parcel['result']['message']);
                    echo '<br><a href="'.$parcel['details']['error_link'].'" target="_blank" title="'.__('Åbner i ny fane', 'coolrunner').'">'.__('Fejlkode link', 'coolrunner').'</a>';
                }
            } else {
                update_post_meta($_POST['order_ids'], '_coolrunner_meta', [
                    'created_at'                => date('d/m/Y H:i:s'),
                    'carrier'                   => sanitize_text_field($_POST['carrier']),
                    'carrier_product'           => sanitize_text_field($_POST['carrier_product']),
                    'carrier_service'           => sanitize_text_field($_POST['carrier_service']),
                    'shipment_id'               => $parcel['result']['shipment_id'],
                    'grand_total_excl_tax'      => $parcel['result']['grand_total_excl_tax'],
                    'labelless_code'            => $parcel['result']['labelless_code'],
                    'pdf_link'                  => $parcel['result']['pdf_link'],
                    'order_id'                  => $parcel['result']['order_id'],
                    'unique_id'                 => Coolrunner_Helper::getLastURLSegment($parcel['result']['pdf_link']),
                    'package_number'            => $parcel['result']['package_number'],
                    'length'                    => sanitize_text_field($_POST['length']),
                    'width'                     => sanitize_text_field($_POST['width']),
                    'height'                    => sanitize_text_field($_POST['height']),
                    'weight'                    => sanitize_text_field($_POST['weight']),
                ]);

                echo 'success';

                wp_die();
            }
        } else {
            _e('Label allerede købt', 'coolrunner');
        }

        wp_die();
    }

    public function coolrunner_create_bulk_labels()
    {
        $required_keys = [
            'action',
            'carrier',
            'carrier_id',
            'carrier_product',
            'carrier_service',
            'droppoint_id',
            'order_ids',
            'width',
            'length',
            'height',
            'weight',
            'country',
        ];

        $missing_params = array_diff_key(array_flip($required_keys), $_POST);

        if (!empty($missing_params)) { // We are missing some parameters
            echo __('Mangler parametre: ', 'coolrunner'). json_encode($missing_params);

            wp_die();
        }

        $orders = explode(',', trim($_POST['order_ids'], ','));

        $results = [];

        $carrier = sanitize_text_field($_POST['carrier']);
        $carrier_id = sanitize_text_field($_POST['carrier_id']);
        $carrier_product = sanitize_text_field($_POST['carrier_product']);
        $carrier_service = sanitize_text_field($_POST['carrier_service']);

        $length = sanitize_text_field($_POST['length']);
        $width = sanitize_text_field($_POST['width']);
        $height = sanitize_text_field($_POST['height']);
        $weight = sanitize_text_field($_POST['weight']);

        $settings = Coolrunner_Setting::all();


        //Maybe just change to coolrunner bulk endpoint
        foreach ($orders as $key => $order_id) {
            $order = new WC_Order($order_id);

            $results[$order_id] = [
                'id'        => $order_id,
                'customer'  => $order_id.': '.$order->get_shipping_first_name(),
                'unique_id' => '',
                'message'   => '',
                'pdf_link'  => '',
                'pdf_base64'=> '',
            ];

            $order_meta = get_post_meta($order_id, '_coolrunner_meta', true);

            if (empty($order_meta)) {
                if ($order->get_shipping_country() != 'DK') {
                    $results[$order_id]['message'] = __('Der kan kun sendes til Danmark foreløbigt.', 'coolrunner');

                    continue;
                }

                if (!isset(Coolrunner_Helper::carrierFreights($_POST['carrier'])[(int)$_POST['carrier_id']])) {
                    $results[$order_id]['message'] = __('Fragtmuligheden blev ikke fundet.', 'coolrunner');

                    continue;
                }


                //Get correct droppoint data
                $droppoint_api = new Coolrunner_Droppoint($settings['api_username'], $settings['api_token']);
                $droppoints = $droppoint_api->{$carrier}(
                    $order->get_shipping_country(),
                    $order->get_shipping_postcode(),
                    $order->get_shipping_address_1()
                );

                if ($droppoints['error']) {
                    $results[$order_id]['message'] = __('Der skete en fejl ved hentning af pakkeshops', 'coolrunner');

                    continue;
                }

                //TODO:
                //Check if customer has specified droppoint
                $chosen_droppoint = $droppoints['result'][0];

                if (empty($chosen_droppoint)) {
                    $results[$order_id]['message'] = __('Der blev ikke fundet en pakkeshop', 'coolrunner');

                    continue;
                }

                $receiver_name = $order->get_shipping_first_name().' '.$order->get_shipping_last_name();
                $receiver_attention = '';

                if ( !empty($order->get_shipping_company()) ) {
                    $receiver_attention = $receiver_name;
                    $receiver_name = $order->get_shipping_company();
                }


                $args = [
                    "receiver_name"         => $receiver_name,
                    "receiver_attention"    => $receiver_attention,
                    "receiver_street1"      => $order->get_shipping_address_1(),
                    "receiver_street2"      => $order->get_shipping_address_2(),
                    "receiver_zipcode"      => $order->get_shipping_postcode(),
                    "receiver_city"         => $order->get_shipping_city(),
                    "receiver_country"      => $order->get_shipping_country(),
                    "receiver_phone"        => $order->get_billing_phone(),
                    "receiver_email"        => $order->get_billing_email(),
                    "receiver_notify"       => true,
                    "receiver_notify_sms"   => $order->get_billing_phone(),
                    "receiver_notify_email" => $order->get_billing_email(),

                    "sender_name"           => $settings['sender_name'],
                    "sender_attention"      => $settings['sender_attention'],
                    "sender_street1"        => $settings['sender_street1'],
                    "sender_street2"        => $settings['sender_street2'],
                    "sender_zipcode"        => $settings['sender_postcode'],
                    "sender_city"           => $settings['sender_city'],
                    "sender_country"        => $settings['sender_country'],
                    "sender_phone"          => $settings['sender_phone'],
                    "sender_email"          => $settings['sender_email'],

                    "droppoint"             => ($carrier_service == 'droppoint') ? true : false,
                    "droppoint_id"          => $chosen_droppoint['droppoint_id'],
                    "droppoint_name"        => $chosen_droppoint['name'],
                    "droppoint_street1"     => $chosen_droppoint['address']['street'],
                    "droppoint_zipcode"     => $chosen_droppoint['address']['postal_code'],
                    "droppoint_city"        => $chosen_droppoint['address']['city'],
                    "droppoint_country"     => $chosen_droppoint['address']['country_code'],

                    "carrier"               => $carrier,
                    "carrier_product"       => $carrier_product,
                    "carrier_service"       => $carrier_service,

                    "length"                => $length,
                    "width"                 => $width,
                    "height"                => $height,
                    "weight"                => $weight,

                    "reference"             => $order->get_order_number(),
                    "description"           => substr($order->customer_note, 0, 50),
                    "comment"               => esc_html__('Generated by Bundsgaard plugin: ', 'coolrunner') . date('Y-m-d H:i:s'),
                    "label_format"          => $settings['label_format'],

                    "insurance"             => false,
                    "insurance_value"       => $order->total,
                    "insurance_currency"    => $order->currency,

                    "customs_value"         => $order->total,
                    "customs_currency"      => $order->currency
                ];

                $shipment_api = new Coolrunner_Shipment($settings['api_username'], $settings['api_token']);

                $parcel = $shipment_api->create($args);

                if ($parcel['error']) {
                    $results[$order_id]['message'] = __('Der skete en fejl. Label blev ikke købt.', 'coolrunner');
                    if (isset($parcel['result']['message'])) {
                        $results[$order_id]['message'] .= '<br>'.$parcel['result']['message'];
                    }
                } else {
                    update_post_meta($order_id, '_coolrunner_meta', [
                        'created_at'                => date('d/m/Y H:i:s'),
                        'carrier'                   => $carrier,
                        'carrier_product'           => $carrier_product,
                        'carrier_service'           => $carrier_service,
                        'shipment_id'               => $parcel['result']['shipment_id'],
                        'grand_total_excl_tax'      => $parcel['result']['grand_total_excl_tax'],
                        'labelless_code'            => $parcel['result']['labelless_code'],
                        'pdf_link'                  => $parcel['result']['pdf_link'],
                        'order_id'                  => $parcel['result']['order_id'],
                        'unique_id'                 => Coolrunner_Helper::getLastURLSegment($parcel['result']['pdf_link']),
                        'package_number'            => $parcel['result']['package_number'],
                        'length'                    => $length,
                        'width'                     => $width,
                        'height'                    => $height,
                        'weight'                    => $weight,
                    ]);

                    $results[$order_id]['message'] = __('Label blev købt', 'coolrunner');
                    $results[$order_id]['pdf_link'] = $parcel['result']['pdf_link'];
                    $results[$order_id]['pdf_base64'] = $parcel['result']['pdf_base64'];
                }
            } else {
                $results[$order_id]['message'] = __('Label allerede købt', 'coolrunner');
                $results[$order_id]['pdf_link'] = $order_meta['pdf_link'];
            }
        }

        //GENERATE BULK LABELS
        if ($this->generate_bulk_pdf($results)) {
            ?>
            <h1>
                <a href="<?php echo admin_url('admin-ajax.php'); ?>?action=coolrunner_bulk_view_pdf" class="button button-primary" title="<?php _e('Åbner i ny fane', 'coolrunner'); ?>" target="_blank">
                <?php _e('Download bulk', 'coolrunner'); ?>
                </a>
                <small>
                    <?php _e('OBS: Filen gemmes kun indtil der bliver lavet nye bulk labels', 'coolrunner'); ?>
                </small>
            </h1>
            <?php
        }

        ?>

        <table class="widefat form-table bulk-orders-table">
            <tbody>
                <tr class="orders-bulk-table-row order-bulk-table-header">
                    <th>Ordre</th>
                    <th>Besked</th>
                    <th>Download Link</th>
                </tr>
                <?php foreach ($results as $order) : ?>
                <tr class="orders-bulk-table-row">
                    <td><?php echo $order['customer']; ?></td>
                    <td><?php echo $order['message']; ?></td>
                    <td>
                        <?php if ($order['pdf_link'] != '') : ?>
                        <a href="<?php echo $order['pdf_link']; ?>" title="<?php _e('Åbner i ny fane', 'coolrunner'); ?>" target="_blank"><?php _e('Download', 'coolrunner'); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php

        wp_die();
    }

    private function generate_bulk_pdf($orders){
        $cd = getcwd();

        chdir('../wp-content/plugins/coolrunner');

        require 'lib/PDFMerger/PDFMerger.php';
        $pdf = new PDFMerger; //A4

        foreach ($orders as $order) {
            if ($order['pdf_link'] != '') {
                $file = fopen('documents/'.$order['id'].'.pdf', 'w');

                if ( !empty($order['pdf_base64']) ) {
                    fwrite($file, base64_decode($order['pdf_base64']));
                } else {
                    $content = file_get_contents($order['pdf_link']);

                    fwrite($file, $content);
                }

                fclose($file);

                if (file_exists('documents/'.$order['id'].'.pdf')) {
                    $pdf->addPDF('documents/'.$order['id'].'.pdf');
                }
            }
        }

        if (count(glob('documents/*.pdf'))) {
            if (file_exists('documents/N9TT-9G0A-B7FQ-RANC.pdf')) {
                unlink('documents/N9TT-9G0A-B7FQ-RANC.pdf');
            }

            try {
                $pdf->merge('file', 'documents/N9TT-9G0A-B7FQ-RANC.pdf');
            } catch (Exception $e) {
                //Error
            }

            foreach ($orders as $order) {
                if ( file_exists('documents/'.$order['id'].'.pdf') ) {
                    unlink('documents/'.$order['id'].'.pdf');
                }
            }
        }

        chdir($cd);

        return true;
    }

    public static function coolrunner_bulk_view_pdf(){
        $file = file_get_contents($this->pluginUrl.'documents/N9TT-9G0A-B7FQ-RANC.pdf');

        if ($file) {
            header('Content-Type: application/pdf');
            header('Content-Length: ' . strlen($file));
            header('Content-Disposition: inline; filename="Bulk PDF.pdf"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: private');

            echo $file;
        } else {
            wp_safe_redirect(admin_url());
            exit;
        }

        wp_die(0);
    }
}
