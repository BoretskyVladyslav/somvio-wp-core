<?php
/**
 * ACF local field groups, options page, and bridges to theme helpers.
 *
 * Safe when ACF is inactive — registration and bridges are skipped.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

/**
 * Calculator service key => ACF options field name for multiplier.
 *
 * @return array<string, string>
 */
function somvio_acf_service_mult_field_map() {
	return array(
		'regular-cleaning' => 'somvio_rate_svc_regular_cleaning',
		'deep-cleaning'    => 'somvio_rate_svc_deep_cleaning',
		'end-of-tenancy'   => 'somvio_rate_svc_end_of_tenancy',
		'airbnb-cleaning'  => 'somvio_rate_svc_airbnb_cleaning',
		'after-builders'   => 'somvio_rate_svc_after_builders',
	);
}

/**
 * Calculator addon key => ACF options field name for price.
 *
 * @return array<string, string>
 */
function somvio_acf_addon_price_field_map() {
	return array(
		'deep-oven-clean'       => 'somvio_rate_addon_deep_oven_clean',
		'inside-fridge-freezer' => 'somvio_rate_addon_inside_fridge_freezer',
		'kitchen-cupboards'     => 'somvio_rate_addon_kitchen_cupboards',
		'kitchen-appliances'    => 'somvio_rate_addon_kitchen_appliances',
		'washing-machine'       => 'somvio_rate_addon_washing_machine',
		'dishwasher'            => 'somvio_rate_addon_dishwasher',
		'tumble-dryer'          => 'somvio_rate_addon_tumble_dryer',
		'microwave-air-fryer'   => 'somvio_rate_addon_microwave_air_fryer',
		'carpet-deep-clean'     => 'somvio_rate_addon_carpet_deep_clean',
		'venetian-blinds'       => 'somvio_rate_addon_venetian_blinds',
		'balcony-patio'         => 'somvio_rate_addon_balcony_patio',
	);
}

/**
 * Select choices for service keys (value => label).
 *
 * @return array<string, string>
 */
function somvio_acf_service_key_choices() {
	return array(
		'regular-cleaning' => __( 'Regular Cleaning', 'somvio' ),
		'deep-cleaning'    => __( 'Deep Cleaning', 'somvio' ),
		'end-of-tenancy'   => __( 'End of Tenancy', 'somvio' ),
		'airbnb-cleaning'  => __( 'Airbnb Cleaning', 'somvio' ),
		'after-builders'   => __( 'After Builders', 'somvio' ),
	);
}

/**
 * Select choices for addon keys (value => label).
 *
 * @return array<string, string>
 */
function somvio_acf_addon_key_choices() {
	return array(
		'deep-oven-clean'       => __( 'Deep Oven Clean', 'somvio' ),
		'inside-fridge-freezer' => __( 'Inside Fridge / Freezer', 'somvio' ),
		'kitchen-cupboards'     => __( 'Inside Kitchen Cupboards', 'somvio' ),
		'kitchen-appliances'    => __( 'Kitchen Appliances (Internal)', 'somvio' ),
		'washing-machine'       => __( 'Washing Machine', 'somvio' ),
		'dishwasher'            => __( 'Dishwasher', 'somvio' ),
		'tumble-dryer'          => __( 'Tumble Dryer', 'somvio' ),
		'microwave-air-fryer'   => __( 'Microwave / Air Fryer', 'somvio' ),
		'carpet-deep-clean'     => __( 'Carpet Deep Cleaning', 'somvio' ),
		'venetian-blinds'       => __( 'Venetian Blinds', 'somvio' ),
		'balcony-patio'         => __( 'Balcony / Patio', 'somvio' ),
	);
}

/**
 * Build tel: href from a display phone string.
 *
 * @param string $phone Display phone.
 * @return string
 */
function somvio_acf_phone_to_href( $phone ) {
	$phone = trim( (string) $phone );
	if ( '' === $phone ) {
		return '';
	}

	$has_plus = ( 0 === strpos( $phone, '+' ) );
	$digits   = preg_replace( '/\D+/', '', $phone );

	if ( '' === $digits ) {
		return '';
	}

	return 'tel:' . ( $has_plus ? '+' . $digits : $digits );
}

/**
 * Register Somvio Settings options page (ACF Pro).
 *
 * @return void
 */
function somvio_acf_register_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Somvio Settings', 'somvio' ),
			'menu_title' => __( 'Somvio Settings', 'somvio' ),
			'menu_slug'  => 'somvio-settings',
			'capability' => 'manage_options',
			'redirect'   => false,
			'position'   => 59,
			'icon_url'   => 'dashicons-admin-generic',
		)
	);
}
add_action( 'acf/init', 'somvio_acf_register_options_page' );

/**
 * Whether ACF Pro options pages are available.
 *
 * @return bool
 */
function somvio_acf_has_pro_options_page() {
	return function_exists( 'acf_add_options_page' );
}

/**
 * ACF Free fallback: top-level Somvio Settings menu via add_menu_page.
 *
 * @return void
 */
function somvio_acf_register_free_options_menu() {
	if ( somvio_acf_has_pro_options_page() ) {
		return;
	}

	add_menu_page(
		__( 'Somvio Settings', 'somvio' ),
		__( 'Somvio Settings', 'somvio' ),
		'manage_options',
		'somvio-settings',
		'somvio_acf_render_free_options_page',
		'dashicons-admin-generic',
		59
	);
}
add_action( 'admin_menu', 'somvio_acf_register_free_options_menu' );

/**
 * Load ACF form assets / process saves before admin HTML (Free options page).
 *
 * @return void
 */
function somvio_acf_free_options_form_head() {
	if ( somvio_acf_has_pro_options_page() || ! function_exists( 'acf_form_head' ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page slug check.
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( (string) $_GET['page'] ) ) : '';
	if ( 'somvio-settings' !== $page ) {
		return;
	}

	acf_form_head();
}
add_action( 'admin_init', 'somvio_acf_free_options_form_head' );

/**
 * Render Somvio Settings for ACF Free (acf_form → options post_id).
 *
 * @return void
 */
function somvio_acf_render_free_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! function_exists( 'acf_form' ) ) {
		echo '<div class="wrap"><h1>' . esc_html__( 'Somvio Settings', 'somvio' ) . '</h1>';
		echo '<div class="notice notice-error"><p>';
		esc_html_e( 'Advanced Custom Fields is required to edit these settings.', 'somvio' );
		echo '</p></div></div>';
		return;
	}

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Somvio Settings', 'somvio' ) . '</h1>';
	echo '<p class="description">' . esc_html__( 'Site options, Stripe keys, and quote calculator rates.', 'somvio' ) . '</p>';

	acf_form(
		array(
			'id'              => 'somvio-settings-form',
			'post_id'         => 'options',
			'new_post'        => false,
			'field_groups'    => array(
				'group_somvio_general_settings',
				'group_somvio_quote_rates',
			),
			'form'            => true,
			'return'          => admin_url( 'admin.php?page=somvio-settings&updated=true' ),
			'submit_value'    => __( 'Save Settings', 'somvio' ),
			'updated_message' => __( 'Settings saved.', 'somvio' ),
			'html_before_fields' => '',
			'html_after_fields'  => '',
		)
	);

	echo '</div>';
}

/**
 * Register all local field groups.
 *
 * @return void
 */
function somvio_acf_register_field_groups() {
	$svc_fields   = array();
	$addon_fields = array();

	foreach ( somvio_acf_service_mult_field_map() as $key => $field_name ) {
		$label       = somvio_acf_service_key_choices()[ $key ] ?? $key;
		$svc_fields[] = array(
			'key'           => 'field_' . $field_name,
			'label'         => sprintf(
				/* translators: %s: service name */
				__( '%s multiplier', 'somvio' ),
				$label
			),
			'name'          => $field_name,
			'type'          => 'number',
			'step'          => '0.01',
			'min'           => 0,
			'placeholder'   => '',
			'wrapper'       => array( 'width' => '33' ),
		);
	}

	foreach ( somvio_acf_addon_price_field_map() as $key => $field_name ) {
		$label          = somvio_acf_addon_key_choices()[ $key ] ?? $key;
		$addon_fields[] = array(
			'key'         => 'field_' . $field_name,
			'label'       => sprintf(
				/* translators: %s: addon name */
				__( '%s price (£)', 'somvio' ),
				$label
			),
			'name'        => $field_name,
			'type'        => 'number',
			'step'        => '0.01',
			'min'         => 0,
			'wrapper'     => array( 'width' => '33' ),
		);
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_somvio_general_settings',
			'title'                 => __( 'General Settings', 'somvio' ),
			'fields'                => array(
				array(
					'key'   => 'field_somvio_tab_stripe',
					'label' => __( 'Stripe', 'somvio' ),
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_somvio_stripe_secret_key',
					'label'        => __( 'Stripe Secret Key', 'somvio' ),
					'name'         => 'somvio_stripe_secret_key',
					'type'         => 'password',
					'instructions' => __( 'Server-only. Prefer SOMVIO_STRIPE_SECRET_KEY in wp-config when possible.', 'somvio' ),
				),
				array(
					'key'   => 'field_somvio_stripe_publishable_key',
					'label' => __( 'Stripe Publishable Key', 'somvio' ),
					'name'  => 'somvio_stripe_publishable_key',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_somvio_tab_company',
					'label' => __( 'Company', 'somvio' ),
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_somvio_company_phone',
					'label'         => __( 'Company Phone', 'somvio' ),
					'name'          => 'somvio_company_phone',
					'type'          => 'text',
					'default_value' => '+44 7402 495410',
					'placeholder'   => '+44 7402 495410',
				),
				array(
					'key'           => 'field_somvio_company_email',
					'label'         => __( 'Company Email', 'somvio' ),
					'name'          => 'somvio_company_email',
					'type'          => 'email',
					'default_value' => 'info@somvio.co.uk',
					'placeholder'   => 'info@somvio.co.uk',
				),
				array(
					'key'           => 'field_somvio_company_address',
					'label'         => __( 'Company Address', 'somvio' ),
					'name'          => 'somvio_company_address',
					'type'          => 'text',
					'default_value' => 'Glasgow, UK',
					'placeholder'   => 'Glasgow, UK',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'somvio-settings',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);

	$rate_fields = array(
		array(
			'key'   => 'field_somvio_tab_bedroom_rates',
			'label' => __( 'Bedroom base (£)', 'somvio' ),
			'type'  => 'tab',
		),
		array(
			'key'     => 'field_somvio_rate_bedroom_1',
			'label'   => __( '1 bedroom', 'somvio' ),
			'name'    => 'somvio_rate_bedroom_1',
			'type'    => 'number',
			'step'    => '0.01',
			'min'     => 0,
			'wrapper' => array( 'width' => '20' ),
		),
		array(
			'key'     => 'field_somvio_rate_bedroom_2',
			'label'   => __( '2 bedrooms', 'somvio' ),
			'name'    => 'somvio_rate_bedroom_2',
			'type'    => 'number',
			'step'    => '0.01',
			'min'     => 0,
			'wrapper' => array( 'width' => '20' ),
		),
		array(
			'key'     => 'field_somvio_rate_bedroom_3',
			'label'   => __( '3 bedrooms', 'somvio' ),
			'name'    => 'somvio_rate_bedroom_3',
			'type'    => 'number',
			'step'    => '0.01',
			'min'     => 0,
			'wrapper' => array( 'width' => '20' ),
		),
		array(
			'key'     => 'field_somvio_rate_bedroom_4',
			'label'   => __( '4 bedrooms', 'somvio' ),
			'name'    => 'somvio_rate_bedroom_4',
			'type'    => 'number',
			'step'    => '0.01',
			'min'     => 0,
			'wrapper' => array( 'width' => '20' ),
		),
		array(
			'key'     => 'field_somvio_rate_bedroom_5',
			'label'   => __( '5 bedrooms', 'somvio' ),
			'name'    => 'somvio_rate_bedroom_5',
			'type'    => 'number',
			'step'    => '0.01',
			'min'     => 0,
			'wrapper' => array( 'width' => '20' ),
		),
		array(
			'key'   => 'field_somvio_tab_extras_prop',
			'label' => __( 'Extras & property', 'somvio' ),
			'type'  => 'tab',
		),
		array(
			'key'     => 'field_somvio_rate_bathroom_extra',
			'label'   => __( 'Extra bathroom (£)', 'somvio' ),
			'name'    => 'somvio_rate_bathroom_extra',
			'type'    => 'number',
			'step'    => '0.01',
			'min'     => 0,
			'wrapper' => array( 'width' => '33' ),
		),
		array(
			'key'     => 'field_somvio_rate_prop_house',
			'label'   => __( 'House multiplier', 'somvio' ),
			'name'    => 'somvio_rate_prop_house',
			'type'    => 'number',
			'step'    => '0.01',
			'min'     => 0,
			'wrapper' => array( 'width' => '33' ),
		),
		array(
			'key'     => 'field_somvio_rate_prop_apartment',
			'label'   => __( 'Apartment multiplier', 'somvio' ),
			'name'    => 'somvio_rate_prop_apartment',
			'type'    => 'number',
			'step'    => '0.01',
			'min'     => 0,
			'wrapper' => array( 'width' => '33' ),
		),
		array(
			'key'   => 'field_somvio_tab_service_mult',
			'label' => __( 'Service multipliers', 'somvio' ),
			'type'  => 'tab',
		),
	);

	$rate_fields = array_merge( $rate_fields, $svc_fields );
	$rate_fields[] = array(
		'key'   => 'field_somvio_tab_addon_prices',
		'label' => __( 'Addon prices (£)', 'somvio' ),
		'type'  => 'tab',
	);
	$rate_fields = array_merge( $rate_fields, $addon_fields );

	acf_add_local_field_group(
		array(
			'key'                   => 'group_somvio_quote_rates',
			'title'                 => __( 'Quote Rates', 'somvio' ),
			'fields'                => $rate_fields,
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'somvio-settings',
					),
				),
			),
			'menu_order'            => 10,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_somvio_service_page',
			'title'                 => __( 'Service Page', 'somvio' ),
			'fields'                => array(
				array(
					'key'   => 'field_somvio_tab_service_marketing',
					'label' => __( 'Marketing', 'somvio' ),
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_somvio_service_story_badge',
					'label'         => __( 'Story badge', 'somvio' ),
					'name'          => 'somvio_service_story_badge',
					'type'          => 'text',
					'default_value' => '',
					'placeholder'   => __( 'Our Story', 'somvio' ),
				),
				array(
					'key'   => 'field_somvio_service_story_title',
					'label' => __( 'Story title', 'somvio' ),
					'name'  => 'somvio_service_story_title',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_somvio_service_story_description',
					'label'        => __( 'Story description', 'somvio' ),
					'name'         => 'somvio_service_story_description',
					'type'         => 'textarea',
					'rows'         => 6,
					'new_lines'    => 'wpautop',
					'instructions' => __( 'Replaces the default story paragraphs when set.', 'somvio' ),
				),
				array(
					'key'   => 'field_somvio_tab_service_pricing',
					'label' => __( 'Price overrides', 'somvio' ),
					'type'  => 'tab',
				),
				array(
					'key'           => 'field_somvio_service_key',
					'label'         => __( 'Calculator service key', 'somvio' ),
					'name'          => 'somvio_service_key',
					'type'          => 'select',
					'choices'       => somvio_acf_service_key_choices(),
					'allow_null'    => 1,
					'instructions'  => __( 'Links this page’s multiplier override to the quote calculator.', 'somvio' ),
				),
				array(
					'key'          => 'field_somvio_service_mult_override',
					'label'        => __( 'Service multiplier override', 'somvio' ),
					'name'         => 'somvio_service_mult_override',
					'type'         => 'number',
					'step'         => '0.01',
					'min'          => 0,
					'instructions' => __( 'Optional. Overrides the global multiplier for the selected service key.', 'somvio' ),
				),
				array(
					'key'          => 'field_somvio_addon_price_overrides',
					'label'        => __( 'Addon price overrides', 'somvio' ),
					'name'         => 'somvio_addon_price_overrides',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add addon override', 'somvio' ),
					'sub_fields'   => array(
						array(
							'key'     => 'field_somvio_addon_override_key',
							'label'   => __( 'Addon', 'somvio' ),
							'name'    => 'addon_key',
							'type'    => 'select',
							'choices' => somvio_acf_addon_key_choices(),
						),
						array(
							'key'   => 'field_somvio_addon_override_price',
							'label' => __( 'Price (£)', 'somvio' ),
							'name'  => 'price',
							'type'  => 'number',
							'step'  => '0.01',
							'min'   => 0,
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-single-service.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_somvio_legal_page',
			'title'                 => __( 'Legal Page Settings', 'somvio' ),
			'fields'                => array(
				array(
					'key'           => 'field_somvio_legal_effective_date',
					'label'         => __( 'Legal Effective / Updated Date', 'somvio' ),
					'name'          => 'somvio_legal_effective_date',
					'type'          => 'text',
					'default_value' => '27 July 2026',
					'placeholder'   => '27 July 2026',
					'instructions'  => __( 'Shown in the legal hero lead (Effective Date).', 'somvio' ),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-legal.php',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-privacy-policy.php',
					),
				),
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'page-terms-of-use.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'side',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'somvio_acf_register_field_groups' );

/**
 * Read a non-empty ACF options field as a float, or null if unset.
 *
 * @param string $name Field name.
 * @return float|null
 */
function somvio_acf_get_option_float( $name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$value = get_field( $name, 'option' );
	if ( null === $value || false === $value || '' === $value ) {
		return null;
	}

	return (float) $value;
}

/**
 * Rebuild cached per-service-page rate overrides.
 *
 * @return array{service_mult: array<string, float>, addons: array<string, float>}
 */
function somvio_acf_rebuild_service_rate_overrides() {
	$overrides = array(
		'service_mult' => array(),
		'addons'       => array(),
	);

	$query = new WP_Query(
		array(
			'post_type'              => 'page',
			'post_status'            => 'publish',
			'posts_per_page'         => 50,
			'no_found_rows'          => true,
			'update_post_meta_cache' => true,
			'update_post_term_cache' => false,
			'meta_key'               => '_wp_page_template',
			'meta_value'             => 'page-single-service.php',
		)
	);

	if ( $query->have_posts() && function_exists( 'get_field' ) ) {
		foreach ( $query->posts as $page ) {
			$page_id = (int) $page->ID;
			$key     = sanitize_key( (string) get_field( 'somvio_service_key', $page_id ) );
			$mult    = get_field( 'somvio_service_mult_override', $page_id );

			if ( '' !== $key && null !== $mult && false !== $mult && '' !== $mult ) {
				$overrides['service_mult'][ $key ] = (float) $mult;
			}

			$rows = get_field( 'somvio_addon_price_overrides', $page_id );
			if ( is_array( $rows ) ) {
				foreach ( $rows as $row ) {
					$addon_key = isset( $row['addon_key'] ) ? sanitize_key( (string) $row['addon_key'] ) : '';
					$price     = isset( $row['price'] ) ? $row['price'] : null;
					if ( '' === $addon_key || null === $price || false === $price || '' === $price ) {
						continue;
					}
					$overrides['addons'][ $addon_key ] = (float) $price;
				}
			}
		}
	}

	wp_reset_postdata();

	update_option( 'somvio_acf_service_rate_overrides', $overrides, false );

	return $overrides;
}

/**
 * Cached per-service-page rate overrides.
 *
 * @return array{service_mult: array<string, float>, addons: array<string, float>}
 */
function somvio_acf_get_service_rate_overrides() {
	$cached = get_option( 'somvio_acf_service_rate_overrides', null );
	if ( is_array( $cached ) && isset( $cached['service_mult'], $cached['addons'] ) ) {
		return $cached;
	}

	return somvio_acf_rebuild_service_rate_overrides();
}

/**
 * Bust quote rates transient and rebuild service override cache after ACF saves.
 *
 * @param int|string $post_id Post ID or options context.
 * @return void
 */
function somvio_acf_on_save_post( $post_id ) {
	delete_transient( 'somvio_quote_rates_v8' );

	$is_options = ( 'options' === $post_id || 'option' === $post_id );

	if ( $is_options && function_exists( 'get_field' ) ) {
		$secret = get_field( 'somvio_stripe_secret_key', 'option' );
		$pub    = get_field( 'somvio_stripe_publishable_key', 'option' );

		if ( is_string( $secret ) && '' !== trim( $secret ) ) {
			update_option( 'somvio_stripe_secret_key', sanitize_text_field( $secret ), false );
		}
		if ( is_string( $pub ) && '' !== trim( $pub ) ) {
			update_option( 'somvio_stripe_publishable_key', sanitize_text_field( $pub ), false );
		}
	}

	$should_rebuild = $is_options;
	if ( ! $should_rebuild && is_numeric( $post_id ) ) {
		$template = get_page_template_slug( (int) $post_id );
		$should_rebuild = ( 'page-single-service.php' === $template );
	}

	if ( $should_rebuild ) {
		somvio_acf_rebuild_service_rate_overrides();
	}
}
add_action( 'acf/save_post', 'somvio_acf_on_save_post', 20 );

/**
 * Bridge company phone from ACF options.
 *
 * @param array{display: string, href: string} $phone Default phone.
 * @return array{display: string, href: string}
 */
function somvio_acf_filter_phone( $phone ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $phone;
	}

	$display = get_field( 'somvio_company_phone', 'option' );
	if ( ! is_string( $display ) || '' === trim( $display ) ) {
		return $phone;
	}

	$display = sanitize_text_field( $display );
	$href    = somvio_acf_phone_to_href( $display );

	return array(
		'display' => $display,
		'href'    => '' !== $href ? $href : ( $phone['href'] ?? '' ),
	);
}
add_filter( 'somvio_phone', 'somvio_acf_filter_phone' );

/**
 * Bridge company email from ACF options.
 *
 * @param array{display: string, href: string} $email Default email.
 * @return array{display: string, href: string}
 */
function somvio_acf_filter_email( $email ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $email;
	}

	$value = get_field( 'somvio_company_email', 'option' );
	if ( ! is_string( $value ) || '' === trim( $value ) || ! is_email( $value ) ) {
		return $email;
	}

	$value = sanitize_email( $value );

	return array(
		'display' => $value,
		'href'    => 'mailto:' . $value,
	);
}
add_filter( 'somvio_email', 'somvio_acf_filter_email' );

/**
 * Bridge company address from ACF options.
 *
 * @param string $location Default location.
 * @return string
 */
function somvio_acf_filter_location( $location ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $location;
	}

	$address = get_field( 'somvio_company_address', 'option' );
	if ( ! is_string( $address ) || '' === trim( $address ) ) {
		return $location;
	}

	return sanitize_text_field( $address );
}
add_filter( 'somvio_location', 'somvio_acf_filter_location' );

/**
 * Prefer ACF Stripe secret when set (constants still win before this filter).
 *
 * @param string $key Current key.
 * @return string
 */
function somvio_acf_filter_stripe_secret( $key ) {
	if ( defined( 'SOMVIO_STRIPE_SECRET_KEY' ) && SOMVIO_STRIPE_SECRET_KEY ) {
		return $key;
	}

	if ( ! function_exists( 'get_field' ) ) {
		return $key;
	}

	$acf = get_field( 'somvio_stripe_secret_key', 'option' );
	if ( is_string( $acf ) && '' !== trim( $acf ) ) {
		return trim( $acf );
	}

	return $key;
}
add_filter( 'somvio_stripe_secret_key', 'somvio_acf_filter_stripe_secret' );

/**
 * Prefer ACF Stripe publishable key when set.
 *
 * @param string $key Current key.
 * @return string
 */
function somvio_acf_filter_stripe_publishable( $key ) {
	if ( defined( 'SOMVIO_STRIPE_PUBLISHABLE_KEY' ) && SOMVIO_STRIPE_PUBLISHABLE_KEY ) {
		return $key;
	}

	if ( ! function_exists( 'get_field' ) ) {
		return $key;
	}

	$acf = get_field( 'somvio_stripe_publishable_key', 'option' );
	if ( is_string( $acf ) && '' !== trim( $acf ) ) {
		return trim( $acf );
	}

	return $key;
}
add_filter( 'somvio_stripe_publishable_key', 'somvio_acf_filter_stripe_publishable' );

/**
 * Merge ACF options + per-page overrides into quote rates.
 *
 * @param array<string, mixed> $rates Rate table.
 * @return array<string, mixed>
 */
function somvio_acf_filter_quote_rates( $rates ) {
	if ( ! is_array( $rates ) || ! function_exists( 'get_field' ) ) {
		return $rates;
	}

	if ( ! isset( $rates['bedroom_base'] ) || ! is_array( $rates['bedroom_base'] ) ) {
		$rates['bedroom_base'] = array();
	}

	for ( $i = 1; $i <= 5; $i++ ) {
		$val = somvio_acf_get_option_float( 'somvio_rate_bedroom_' . $i );
		if ( null !== $val ) {
			$rates['bedroom_base'][ (string) $i ] = $val;
		}
	}

	$bath = somvio_acf_get_option_float( 'somvio_rate_bathroom_extra' );
	if ( null !== $bath ) {
		$rates['bathroom_extra'] = $bath;
	}

	if ( ! isset( $rates['property_mult'] ) || ! is_array( $rates['property_mult'] ) ) {
		$rates['property_mult'] = array();
	}

	$house = somvio_acf_get_option_float( 'somvio_rate_prop_house' );
	if ( null !== $house ) {
		$rates['property_mult']['house'] = $house;
	}

	$apt = somvio_acf_get_option_float( 'somvio_rate_prop_apartment' );
	if ( null !== $apt ) {
		$rates['property_mult']['apartment'] = $apt;
	}

	if ( ! isset( $rates['service_mult'] ) || ! is_array( $rates['service_mult'] ) ) {
		$rates['service_mult'] = array();
	}

	foreach ( somvio_acf_service_mult_field_map() as $svc_key => $field_name ) {
		$val = somvio_acf_get_option_float( $field_name );
		if ( null !== $val ) {
			$rates['service_mult'][ $svc_key ] = $val;
		}
	}

	if ( ! isset( $rates['addons'] ) || ! is_array( $rates['addons'] ) ) {
		$rates['addons'] = array();
	}

	foreach ( somvio_acf_addon_price_field_map() as $addon_key => $field_name ) {
		$val = somvio_acf_get_option_float( $field_name );
		if ( null !== $val ) {
			if ( ! isset( $rates['addons'][ $addon_key ] ) || ! is_array( $rates['addons'][ $addon_key ] ) ) {
				$rates['addons'][ $addon_key ] = array();
			}
			$rates['addons'][ $addon_key ]['price'] = $val;
		}
	}

	$page_overrides = somvio_acf_get_service_rate_overrides();

	foreach ( $page_overrides['service_mult'] as $svc_key => $mult ) {
		$rates['service_mult'][ $svc_key ] = (float) $mult;
	}

	foreach ( $page_overrides['addons'] as $addon_key => $price ) {
		if ( ! isset( $rates['addons'][ $addon_key ] ) || ! is_array( $rates['addons'][ $addon_key ] ) ) {
			$rates['addons'][ $addon_key ] = array();
		}
		$rates['addons'][ $addon_key ]['price'] = (float) $price;
	}

	return $rates;
}
add_filter( 'somvio_quote_rates', 'somvio_acf_filter_quote_rates', 20 );
