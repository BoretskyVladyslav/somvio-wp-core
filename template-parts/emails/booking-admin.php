<?php
/**
 * Admin booking notification email — HTML.
 *
 * Vars: $payload, $labels, $cost_rows, $site_name, $site_url, $admin_email
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$payload = isset( $payload ) && is_array( $payload ) ? $payload : array();
$labels  = isset( $labels ) && is_array( $labels ) ? $labels : array();
$cost_rows = isset( $cost_rows ) && is_array( $cost_rows ) ? $cost_rows : array();
$site_name = isset( $site_name ) ? (string) $site_name : 'Somvio';

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
	<title><?php echo esc_html__( 'New booking notification', 'somvio' ); ?></title>
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
								<?php echo esc_html__( 'New booking / quote request', 'somvio' ); ?>
							</h1>
						</td>
					</tr>
					<tr>
						<td style="padding:24px 28px;">
							<p style="margin:0 0 16px;font-size:15px;line-height:1.5;color:#334049;">
								<?php echo esc_html__( 'A new request was submitted on the website. Summary below.', 'somvio' ); ?>
							</p>

							<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8eef2;border-radius:6px;overflow:hidden;margin-bottom:20px;">
								<?php
								$somvio_email_service      = isset( $payload['service'] ) ? sanitize_key( (string) $payload['service'] ) : '';
								$somvio_main_rooms_label   = ( 'after-builders' === $somvio_email_service )
									? __( 'Rooms (Living, Bed, Dining)', 'somvio' )
									: __( 'Main rooms', 'somvio' );
								$somvio_bathrooms_label    = in_array( $somvio_email_service, array( 'deep-cleaning', 'end-of-tenancy', 'after-builders' ), true )
									? __( 'Bathrooms And Shower Rooms', 'somvio' )
									: __( 'Bathrooms', 'somvio' );

								echo $row( __( 'Source', 'somvio' ), $labels['source'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Customer', 'somvio' ), (string) ( $payload['name'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Email', 'somvio' ), (string) ( $payload['email'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Phone', 'somvio' ), (string) ( $payload['phone'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Address / Postcode', 'somvio' ), (string) ( $payload['address'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Service', 'somvio' ), $labels['service'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Property', 'somvio' ), $labels['property'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								if ( ! empty( $labels['frequency'] ) ) {
									echo $row( __( 'Frequency', 'somvio' ), $labels['frequency'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								echo $row( $somvio_main_rooms_label, (string) (int) ( $payload['main_rooms'] ?? 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Bedrooms', 'somvio' ), (string) (int) ( $payload['bedrooms'] ?? 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( $somvio_bathrooms_label, (string) (int) ( $payload['bathrooms'] ?? 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Toilets (without Baths/showers)', 'somvio' ), (string) (int) ( $payload['toilets'] ?? 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Kitchens', 'somvio' ), (string) (int) ( $payload['kitchens'] ?? 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Linen changes', 'somvio' ), (string) (int) ( $payload['linen_changes'] ?? 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Welcome pack', 'somvio' ), $labels['welcome_pack'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Extra services', 'somvio' ), $labels['addons'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								if ( ! empty( $labels['access_method'] ) ) {
									echo $row( __( 'How will we get in?', 'somvio' ), $labels['access_method'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								echo $row( __( 'Date', 'somvio' ), (string) ( $payload['date'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Time', 'somvio' ), (string) ( $payload['time'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $row( __( 'Payment', 'somvio' ), $labels['payment'] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								if ( ! empty( $payload['booking_id'] ) ) {
									echo $row( __( 'LatePoint booking ID', 'somvio' ), (string) (int) $payload['booking_id'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								if ( ! empty( $payload['comment'] ) ) {
									echo $row( __( 'Comment', 'somvio' ), (string) $payload['comment'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
								?>
							</table>

							<h2 style="margin:0 0 12px;font-size:16px;color:#091e2c;"><?php echo esc_html__( 'Cost breakdown', 'somvio' ); ?></h2>
							<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #e8eef2;border-radius:6px;overflow:hidden;">
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
						</td>
					</tr>
					<tr>
						<td style="padding:16px 28px 24px;background:#f8fbfc;color:#5a6b76;font-size:12px;line-height:1.5;">
							<?php echo esc_html__( 'This email was sent automatically from the Somvio website booking system.', 'somvio' ); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
