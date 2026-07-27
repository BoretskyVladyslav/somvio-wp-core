<?php
/**
 * Seed LatePoint defaults: Glasgow location, Somvio agent, service map + connections.
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Option key for cached LatePoint default IDs.
 *
 * @return string
 */
function somvio_latepoint_defaults_option_key() {
	return 'somvio_latepoint_defaults_v1';
}

/**
 * Canonical service definitions for LatePoint seeding / mapping.
 *
 * @return array<string, array{name:string,duration:int}>
 */
function somvio_latepoint_service_seed_definitions() {
	$labels = function_exists( 'somvio_get_quote_service_options' )
		? somvio_get_quote_service_options()
		: array(
			'regular-cleaning' => 'Regular Cleaning',
			'deep-cleaning'    => 'Deep Cleaning',
			'end-of-tenancy'   => 'End of Tenancy',
			'airbnb-cleaning'  => 'Airbnb Cleaning',
			'after-builders'   => 'After Builders',
		);

	$defs = array();
	foreach ( $labels as $key => $label ) {
		$defs[ sanitize_key( (string) $key ) ] = array(
			'name'     => (string) $label,
			'duration' => 60,
		);
	}

	/**
	 * Filter LatePoint service seed definitions.
	 *
	 * @param array<string, array{name:string,duration:int}> $defs Definitions.
	 */
	return apply_filters( 'somvio_latepoint_service_seed_definitions', $defs );
}

/**
 * Whether cached defaults still resolve to real LatePoint rows.
 *
 * @param array<string, mixed> $cached Cached option.
 * @return bool
 */
function somvio_latepoint_defaults_are_valid( array $cached ) {
	if ( empty( $cached['location_id'] ) || empty( $cached['agent_id'] ) || empty( $cached['services'] ) || ! is_array( $cached['services'] ) ) {
		return false;
	}

	if ( ! class_exists( 'OsLocationModel' ) || ! class_exists( 'OsAgentModel' ) || ! class_exists( 'OsServiceModel' ) ) {
		return false;
	}

	$location = new OsLocationModel( (int) $cached['location_id'] );
	if ( empty( $location->id ) ) {
		return false;
	}

	$agent = new OsAgentModel( (int) $cached['agent_id'] );
	if ( empty( $agent->id ) ) {
		return false;
	}

	foreach ( somvio_latepoint_service_seed_definitions() as $key => $def ) {
		$id = isset( $cached['services'][ $key ] ) ? (int) $cached['services'][ $key ] : 0;
		if ( $id < 1 ) {
			return false;
		}
		$service = new OsServiceModel( $id );
		if ( empty( $service->id ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Find a LatePoint location by exact name (case-insensitive).
 *
 * @param string $name Location name.
 * @return OsLocationModel|null
 */
function somvio_latepoint_find_location_by_name( $name ) {
	$name = trim( (string) $name );
	if ( '' === $name || ! class_exists( 'OsLocationModel' ) || ! class_exists( 'OsLocationHelper' ) ) {
		return null;
	}

	$locations = OsLocationHelper::get_locations( false );
	if ( ! is_array( $locations ) ) {
		return null;
	}

	$needle = strtolower( $name );
	foreach ( $locations as $location ) {
		if ( ! is_object( $location ) || empty( $location->id ) ) {
			continue;
		}
		$current = isset( $location->name ) ? strtolower( trim( (string) $location->name ) ) : '';
		if ( $current === $needle ) {
			return $location;
		}
	}

	return null;
}

/**
 * Find a LatePoint agent by display/full name.
 *
 * @param string $name Agent display name.
 * @return OsAgentModel|null
 */
function somvio_latepoint_find_agent_by_name( $name ) {
	$name = trim( (string) $name );
	if ( '' === $name || ! class_exists( 'OsAgentModel' ) || ! class_exists( 'OsAgentHelper' ) ) {
		return null;
	}

	$agents = OsAgentHelper::get_allowed_active_agents();
	if ( ! is_array( $agents ) ) {
		$agents = array();
	}

	// Also scan all agents if helper returns empty/disabled-filtered set.
	if ( ! $agents && class_exists( 'OsAgentModel' ) ) {
		$all = ( new OsAgentModel() )->get_results_as_models();
		$agents = is_array( $all ) ? $all : array();
	}

	$needle = strtolower( $name );
	foreach ( $agents as $agent ) {
		if ( ! is_object( $agent ) || empty( $agent->id ) ) {
			continue;
		}
		$display = isset( $agent->display_name ) ? strtolower( trim( (string) $agent->display_name ) ) : '';
		$full    = method_exists( $agent, 'get_full_name' )
			? strtolower( trim( (string) $agent->get_full_name() ) )
			: strtolower( trim( (string) ( $agent->first_name ?? '' ) . ' ' . (string) ( $agent->last_name ?? '' ) ) );
		if ( $display === $needle || $full === $needle ) {
			return $agent;
		}
	}

	return null;
}

/**
 * Find a LatePoint service by exact name (case-insensitive).
 *
 * @param string $name Service name.
 * @return OsServiceModel|null
 */
function somvio_latepoint_find_service_by_name( $name ) {
	$name = trim( (string) $name );
	if ( '' === $name || ! class_exists( 'OsServiceHelper' ) ) {
		return null;
	}

	$services = OsServiceHelper::get_services( false );
	if ( ! is_array( $services ) ) {
		return null;
	}

	$needle = strtolower( $name );
	foreach ( $services as $service ) {
		if ( ! is_object( $service ) || empty( $service->id ) ) {
			continue;
		}
		$current = isset( $service->name ) ? strtolower( trim( (string) $service->name ) ) : '';
		if ( $current === $needle ) {
			return $service;
		}
	}

	return null;
}

/**
 * Ensure Glasgow location exists.
 *
 * @return int Location ID or 0.
 */
function somvio_latepoint_ensure_location() {
	$name = (string) apply_filters( 'somvio_latepoint_default_location_name', 'Glasgow' );
	$existing = somvio_latepoint_find_location_by_name( $name );
	if ( $existing && ! empty( $existing->id ) ) {
		if ( defined( 'LATEPOINT_LOCATION_STATUS_ACTIVE' ) && isset( $existing->status ) && $existing->status !== LATEPOINT_LOCATION_STATUS_ACTIVE ) {
			$existing->status = LATEPOINT_LOCATION_STATUS_ACTIVE;
			$existing->save();
		}
		return (int) $existing->id;
	}

	if ( ! class_exists( 'OsLocationModel' ) ) {
		return 0;
	}

	$location = new OsLocationModel();
	$location->name         = $name;
	$location->full_address = (string) apply_filters( 'somvio_latepoint_default_location_address', 'Glasgow, United Kingdom' );
	$location->status       = defined( 'LATEPOINT_LOCATION_STATUS_ACTIVE' ) ? LATEPOINT_LOCATION_STATUS_ACTIVE : 'active';
	$location->category_id  = 0;
	$location->order_number = 1;

	if ( ! $location->save() ) {
		return 0;
	}

	return (int) $location->id;
}

/**
 * Ensure Somvio Cleaning Team agent exists.
 *
 * @return int Agent ID or 0.
 */
function somvio_latepoint_ensure_agent() {
	$display = (string) apply_filters( 'somvio_latepoint_default_agent_name', 'Somvio Cleaning Team' );
	$existing = somvio_latepoint_find_agent_by_name( $display );
	if ( $existing && ! empty( $existing->id ) ) {
		if ( defined( 'LATEPOINT_AGENT_STATUS_ACTIVE' ) && isset( $existing->status ) && $existing->status !== LATEPOINT_AGENT_STATUS_ACTIVE ) {
			$existing->status = LATEPOINT_AGENT_STATUS_ACTIVE;
			$existing->save();
		}
		return (int) $existing->id;
	}

	if ( ! class_exists( 'OsAgentModel' ) ) {
		return 0;
	}

	$email = function_exists( 'somvio_get_booking_admin_email' )
		? somvio_get_booking_admin_email()
		: 'info@somvio.co.uk';

	$agent = new OsAgentModel();
	$agent->first_name   = 'Somvio';
	$agent->last_name    = 'Cleaning Team';
	$agent->display_name = $display;
	$agent->email        = $email;
	$agent->phone        = (string) apply_filters( 'somvio_latepoint_default_agent_phone', '+44 7402 495410' );
	$agent->status       = defined( 'LATEPOINT_AGENT_STATUS_ACTIVE' ) ? LATEPOINT_AGENT_STATUS_ACTIVE : 'active';
	$agent->title        = __( 'Cleaning Team', 'somvio' );

	if ( ! $agent->save() ) {
		return 0;
	}

	return (int) $agent->id;
}

/**
 * Ensure theme services exist in LatePoint.
 *
 * @return array<string, int> service_key => service_id
 */
function somvio_latepoint_ensure_services() {
	$map = array();

	if ( ! class_exists( 'OsServiceModel' ) ) {
		return $map;
	}

	foreach ( somvio_latepoint_service_seed_definitions() as $key => $def ) {
		$name     = (string) ( $def['name'] ?? $key );
		$duration = absint( $def['duration'] ?? 60 );
		if ( $duration < 15 ) {
			$duration = 60;
		}

		$existing = somvio_latepoint_find_service_by_name( $name );
		if ( $existing && ! empty( $existing->id ) ) {
			if ( defined( 'LATEPOINT_SERVICE_STATUS_ACTIVE' ) && isset( $existing->status ) && $existing->status !== LATEPOINT_SERVICE_STATUS_ACTIVE ) {
				$existing->status = LATEPOINT_SERVICE_STATUS_ACTIVE;
				$existing->save();
			}
			$map[ $key ] = (int) $existing->id;
			continue;
		}

		$service = new OsServiceModel();
		$service->name         = $name;
		$service->duration     = $duration;
		$service->duration_name = __( 'Standard', 'somvio' );
		$service->status       = defined( 'LATEPOINT_SERVICE_STATUS_ACTIVE' ) ? LATEPOINT_SERVICE_STATUS_ACTIVE : 'active';
		$service->category_id  = 0;
		$service->buffer_before = 0;
		$service->buffer_after  = 0;
		$service->price_min     = 0;
		$service->price_max     = 0;
		$service->charge_amount = 0;
		$service->short_description = $name;

		if ( $service->save() ) {
			$map[ $key ] = (int) $service->id;
		}
	}

	return $map;
}

/**
 * Connect default agent + location to every seeded service.
 *
 * @param int                $agent_id    Agent ID.
 * @param int                $location_id Location ID.
 * @param array<string, int> $services    Service map.
 * @return void
 */
function somvio_latepoint_ensure_connections( $agent_id, $location_id, array $services ) {
	$agent_id    = absint( $agent_id );
	$location_id = absint( $location_id );
	if ( $agent_id < 1 || $location_id < 1 || ! class_exists( 'OsConnectorHelper' ) ) {
		return;
	}

	foreach ( $services as $service_id ) {
		$service_id = absint( $service_id );
		if ( $service_id < 1 ) {
			continue;
		}

		OsConnectorHelper::save_connection(
			array(
				'agent_id'    => $agent_id,
				'location_id' => $location_id,
				'service_id'  => $service_id,
			)
		);
	}
}

/**
 * Ensure LatePoint defaults exist; cache IDs in an option.
 *
 * @param bool $force Re-seed even if cache looks valid.
 * @return array{
 *   location_id:int,
 *   agent_id:int,
 *   services:array<string,int>
 * }
 */
function somvio_latepoint_ensure_defaults( $force = false ) {
	$empty = array(
		'location_id' => 0,
		'agent_id'    => 0,
		'services'    => array(),
	);

	if ( ! function_exists( 'somvio_latepoint_is_available' ) || ! somvio_latepoint_is_available() ) {
		return $empty;
	}

	$cached = get_option( somvio_latepoint_defaults_option_key(), array() );
	if ( ! $force && is_array( $cached ) && somvio_latepoint_defaults_are_valid( $cached ) ) {
		return array(
			'location_id' => (int) $cached['location_id'],
			'agent_id'    => (int) $cached['agent_id'],
			'services'    => array_map( 'absint', (array) $cached['services'] ),
		);
	}

	$location_id = somvio_latepoint_ensure_location();
	$agent_id    = somvio_latepoint_ensure_agent();
	$services    = somvio_latepoint_ensure_services();

	if ( $location_id > 0 && $agent_id > 0 && $services ) {
		somvio_latepoint_ensure_connections( $agent_id, $location_id, $services );
	}

	$defaults = array(
		'location_id' => $location_id,
		'agent_id'    => $agent_id,
		'services'    => $services,
		'updated_at'  => time(),
	);

	update_option( somvio_latepoint_defaults_option_key(), $defaults, false );

	/**
	 * After LatePoint defaults are seeded/verified.
	 *
	 * @param array<string, mixed> $defaults Defaults payload.
	 */
	do_action( 'somvio_latepoint_defaults_ensured', $defaults );

	return array(
		'location_id' => $location_id,
		'agent_id'    => $agent_id,
		'services'    => $services,
	);
}

/**
 * Boot LatePoint seeding once LatePoint classes are available.
 *
 * @return void
 */
function somvio_latepoint_maybe_seed_on_init() {
	if ( ! function_exists( 'somvio_latepoint_is_available' ) || ! somvio_latepoint_is_available() ) {
		return;
	}

	somvio_latepoint_ensure_defaults( false );
}
add_action( 'init', 'somvio_latepoint_maybe_seed_on_init', 40 );

/**
 * Inject seeded service IDs into the LatePoint service map.
 *
 * @param array<string, int> $map Existing map.
 * @return array<string, int>
 */
function somvio_latepoint_filter_service_map( $map ) {
	$map = is_array( $map ) ? $map : array();
	$defaults = somvio_latepoint_ensure_defaults( false );
	$services = isset( $defaults['services'] ) && is_array( $defaults['services'] ) ? $defaults['services'] : array();

	foreach ( $services as $key => $id ) {
		$key = sanitize_key( (string) $key );
		$id  = absint( $id );
		if ( $key && $id > 0 && empty( $map[ $key ] ) ) {
			$map[ $key ] = $id;
		}
	}

	return $map;
}
add_filter( 'somvio_latepoint_service_map', 'somvio_latepoint_filter_service_map', 5 );
