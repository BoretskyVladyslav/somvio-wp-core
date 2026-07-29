<?php
/**
 * Customer booking confirmation email — HTML.
 *
 * Vars: $payload, $labels, $cost_rows, $site_name, $site_url, $admin_email
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$payload     = isset( $payload ) && is_array( $payload ) ? $payload : array();
$labels      = isset( $labels ) && is_array( $labels ) ? $labels : array();
$cost_rows   = isset( $cost_rows ) && is_array( $cost_rows ) ? $cost_rows : array();
$site_name   = isset( $site_name ) ? (string) $site_name : 'Somvio';
$admin_email = isset( $admin_email ) ? (string) $admin_email : '';
if ( '' === $admin_email && function_exists( 'somvio_get_booking_admin_email' ) ) {
	$admin_email = somvio_get_booking_admin_email();
}
$is_booking    = isset( $payload['source'] ) && 'booking' === $payload['source'];
$first         = trim( (string) ( $payload['first_name'] ?? '' ) );
$greeting_name = $first ? $first : (string) ( $payload['name'] ?? '' );
$bedrooms      = (int) ( $payload['bedrooms'] ?? 0 );
$bathrooms     = (int) ( $payload['bathrooms'] ?? 0 );
$main_rooms    = (int) ( $payload['main_rooms'] ?? 0 );
$toilets       = (int) ( $payload['toilets'] ?? 0 );
$kitchens      = (int) ( $payload['kitchens'] ?? 0 );
$service_key   = isset( $payload['service'] ) ? sanitize_key( (string) $payload['service'] ) : '';
$main_rooms_label = ( 'after-builders' === $service_key )
	? __( 'Rooms (Living, Bed, Dining)', 'somvio' )
	: __( 'Main rooms', 'somvio' );
$bathrooms_label  = in_array( $service_key, array( 'deep-cleaning', 'end-of-tenancy', 'after-builders' ), true )
	? __( 'Bathrooms And Shower Rooms', 'somvio' )
	: __( 'Bathrooms', 'somvio' );

$row = static function ( $label, $value ) {
	$label = (string) $label;
	$value = (string) $value;
	if ( '' === trim( $value ) ) {
		$value = '—';
	}
	return '<tr><td style="padding:8px 12px;border-bottom:1px solid #e8eef2;color:#5a6b76;font-size:14px;width:40%;">'
		. esc_html( $label )
		. '</td><td style="padding:8px 12px;border-bottom:1px solid #e8eef2;color:#00050e;font-size:14px;font-weight:600;">'
		. esc_html( $value )
		. '</td></tr>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html__( 'Booking confirmation', 'somvio' ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f7f9;font-family:Arial,Helvetica,sans-serif;color:#00050e;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f7f9;padding:24px 12px;">
		<tr>
			<td align="center">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:8px;overflow:hidden;">
					<tr>
						<td style="background:#091e2c;padding:24px 28px;">
							<p style="margin:0;color:#40d7d0;font-size:13px;letter-spacing:0.04em;text-transform:uppercase;"><?php echo esc_html( $site_name ); ?></p>
							<h1 style="margin:8px 0 0;color:#ffffff;font-size:22px;line-height:1.3;">
								<?php
								echo $is_booking
									? esc_html__( 'We’ve received your booking request', 'somvio' )
									: esc_html__( 'We’ve received your quote request', 'somvio' );
								?>
							</h1>
						</td>
					</tr>
					<tr>
						<td style="padding:24px 28px;">
							<p style="margin:0 0 16px;font-size:15px;line-height:1.5;color:#334049;">
								<?php
								if ( $greeting_name ) {
									printf(
										/* translators: %s: customer first name */
										esc_html__( 'Hi %s,', 'somvio' ),
										esc_html( $greeting_name )
									);
								} else {
									echo esc_html__( 'Hi,', 'somvio' );
								}
								?>
							</p>
							<p style="margin:0 0 20px;font-size:15px;line-height:1.5;color:#334049;">
								<?php
								echo $is_booking
									? esc_html__( 'Thank you for booking with Somvio. We’ve received your request and will confirm the details shortly. Here’s a summary of what you submitted:', 'somvio' )
									: esc_html__( 'Thank you for requesting a quote. Our team will review your details and get back to you shortly.', 'somvio' );
								?>
							</p>

							<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8eef2;border-radius:6px;overflow:hidden;margin-bottom:20px;">
								<?php
								echo $row( __( 'Service', 'somvio' ), $labels['service'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								if ( ! empty( $labels['property'] ) ) {
									echo $row( __( 'Property', 'somvio' ), $labels['property'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								if ( ! empty( $labels['frequency'] ) ) {
									echo $row( __( 'Frequency', 'somvio' ), $labels['frequency'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								if ( $main_rooms > 0 ) {
									echo $row( $main_rooms_label, (string) $main_rooms ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								if ( $bedrooms > 0 ) {
									echo $row( __( 'Bedrooms', 'somvio' ), (string) $bedrooms ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								if ( $bathrooms > 0 ) {
									echo $row( $bathrooms_label, (string) $bathrooms ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								if ( $toilets > 0 ) {
									echo $row( __( 'Toilets (without Baths/showers)', 'somvio' ), (string) $toilets ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								if ( $kitchens > 0 ) {
									echo $row( __( 'Kitchens', 'somvio' ), (string) $kitchens ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								echo $row( __( 'Date', 'somvio' ), (string) ( $payload['date'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Time', 'somvio' ), (string) ( $payload['time'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Phone', 'somvio' ), (string) ( $payload['phone'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Address', 'somvio' ), (string) ( $payload['address'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								if ( ! empty( $labels['access_method'] ) ) {
									echo $row( __( 'How will we get in?', 'somvio' ), $labels['access_method'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								echo $row( __( 'Estimated total', 'somvio' ), $labels['total_formatted'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Payment', 'somvio' ), $labels['payment'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								if ( ! empty( $labels['addons'] ) && __( 'None', 'somvio' ) !== $labels['addons'] ) {
									echo $row( __( 'Extra services', 'somvio' ), $labels['addons'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
							</table>

							<?php if ( ! empty( $cost_rows ) ) : ?>
								<h2 style="margin:0 0 12px;font-size:16px;color:#091e2c;"><?php echo esc_html__( 'Cost breakdown', 'somvio' ); ?></h2>
								<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8eef2;border-radius:6px;overflow:hidden;margin-bottom:20px;">
									<?php foreach ( $cost_rows as $cost_row ) : ?>
										<tr>
											<td style="padding:8px 12px;border-bottom:1px solid #e8eef2;color:#5a6b76;font-size:14px;">
												<?php echo esc_html( (string) ( $cost_row['label'] ?? '' ) ); ?>
											</td>
											<td style="padding:8px 12px;border-bottom:1px solid #e8eef2;color:#00050e;font-size:14px;font-weight:600;text-align:right;">
												<?php echo esc_html( (string) ( $cost_row['value'] ?? '' ) ); ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</table>
							<?php endif; ?>

							<h2 style="margin:0 0 8px;font-size:16px;color:#091e2c;"><?php echo esc_html__( 'Next steps', 'somvio' ); ?></h2>
							<ol style="margin:0 0 20px;padding-left:20px;color:#334049;font-size:14px;line-height:1.6;">
								<li><?php echo esc_html__( 'We’ll review your request and confirm availability.', 'somvio' ); ?></li>
								<li>
									<?php
									$payment_method = isset( $payload['payment_method'] ) ? sanitize_key( (string) $payload['payment_method'] ) : 'cash';
									if ( $is_booking && 'online' === $payment_method ) {
										echo esc_html__( 'If you completed card payment, your booking is confirmed once payment succeeds — we’ll be in touch with final details.', 'somvio' );
									} elseif ( $is_booking ) {
										echo esc_html__( 'If you chose pay on completion, no card charge is taken now — you’ll pay after the clean.', 'somvio' );
									} else {
										echo esc_html__( 'We’ll send a confirmed quote or follow up if we need more details.', 'somvio' );
									}
									?>
								</li>
								<li>
									<?php
									printf(
										/* translators: %s: admin email */
										esc_html__( 'Questions? Email us at %s or reply to this message.', 'somvio' ),
										esc_html( $admin_email )
									);
									?>
								</li>
							</ol>

							<p style="margin:0;font-size:14px;line-height:1.5;color:#5a6b76;">
								<?php echo esc_html__( 'Estimated totals are preview figures — final price is confirmed by our team.', 'somvio' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<td style="padding:16px 28px 24px;background:#f8fbfc;color:#5a6b76;font-size:12px;line-height:1.5;">
							<?php echo esc_html( $site_name ); ?> · Glasgow &amp; surrounding areas · <?php echo esc_html( $admin_email ); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
