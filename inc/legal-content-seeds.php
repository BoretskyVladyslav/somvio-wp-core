<?php
/**
 * Legal / policy page seed HTML (client Legal Pack).
 *
 * @package Somvio_Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Effective date shown on legal pages.
 *
 * @return string
 */
function somvio_get_legal_effective_date() {
	return '27 July 2026';
}

/**
 * Registry of legal pages (slug => meta).
 *
 * @return array<string, array{title:string,lead:string}>
 */
function somvio_get_legal_pages_registry() {
	$date = somvio_get_legal_effective_date();
	$lead = sprintf(
		/* translators: %s: effective date */
		__( 'Effective Date: %s', 'somvio' ),
		$date
	);

	return array(
		'privacy-policy'          => array(
			'title' => __( 'Privacy Policy', 'somvio' ),
			'lead'  => $lead,
		),
		'terms-conditions'        => array(
			'title' => __( 'Terms & Conditions', 'somvio' ),
			'lead'  => $lead,
		),
		'cookie-policy'           => array(
			'title' => __( 'Cookie Policy', 'somvio' ),
			'lead'  => $lead,
		),
		'cancellation-policy'     => array(
			'title' => __( 'Cancellation & Refund Policy', 'somvio' ),
			'lead'  => $lead,
		),
		'disclaimer'              => array(
			'title' => __( 'Website Disclaimer', 'somvio' ),
			'lead'  => $lead,
		),
		'accessibility-statement' => array(
			'title' => __( 'Accessibility Statement', 'somvio' ),
			'lead'  => $lead,
		),
		'booking-terms'           => array(
			'title' => __( 'Online Booking Terms', 'somvio' ),
			'lead'  => $lead,
		),
		'satisfaction-guarantee'  => array(
			'title' => __( 'Customer Satisfaction Guarantee', 'somvio' ),
			'lead'  => $lead,
		),
		'complaints-procedure'    => array(
			'title' => __( 'Complaints Procedure', 'somvio' ),
			'lead'  => $lead,
		),
		'service-checklist'       => array(
			'title' => __( 'Service Checklist', 'somvio' ),
			'lead'  => $lead,
		),
	);
}

/**
 * Seed HTML for a legal page slug.
 *
 * @param string $slug Page slug.
 * @return string
 */
function somvio_get_legal_page_seed_content( $slug ) {
	$slug = sanitize_title( (string) $slug );
	$date = esc_html( somvio_get_legal_effective_date() );

	switch ( $slug ) {
		case 'privacy-policy':
			return somvio_get_privacy_policy_seed_content( $date );
		case 'terms-conditions':
		case 'terms-of-use':
			return somvio_get_terms_conditions_seed_content( $date );
		case 'cookie-policy':
			return somvio_get_cookie_policy_seed_content( $date );
		case 'cancellation-policy':
			return somvio_get_cancellation_policy_seed_content( $date );
		case 'disclaimer':
			return somvio_get_disclaimer_seed_content( $date );
		case 'accessibility-statement':
			return somvio_get_accessibility_statement_seed_content( $date );
		case 'booking-terms':
			return somvio_get_booking_terms_seed_content( $date );
		case 'satisfaction-guarantee':
			return somvio_get_satisfaction_guarantee_seed_content( $date );
		case 'complaints-procedure':
			return somvio_get_complaints_procedure_seed_content( $date );
		case 'service-checklist':
			return somvio_get_service_checklist_seed_content( $date );
		case 'legal':
			return somvio_get_legal_index_seed_content( $date );
		default:
			return '';
	}
}

/**
 * Master Legal Index hub seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_legal_index_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	$items = array();
	$n     = 0;

	foreach ( somvio_get_legal_pages_registry() as $slug => $meta ) {
		++$n;
		$title   = isset( $meta['title'] ) ? (string) $meta['title'] : $slug;
		$url     = esc_url( home_url( '/' . $slug . '/' ) );
		$label   = esc_html( $title );
		$num     = esc_html( sprintf( '%02d', $n ) );
		$items[] = '<li class="legal-index__item">'
			. '<a class="legal-index__card" href="' . $url . '">'
			. '<span class="legal-index__num" aria-hidden="true">' . $num . '</span>'
			. '<span class="legal-index__title">' . $label . '</span>'
			. '</a></li>';
	}

	$grid = implode( "\n", $items );

	return <<<HTML
<div class="legal-content__section legal-index__meta">
<p><strong>Version:</strong> 1.0</p>
<p><strong>Prepared for:</strong> Somvio (<a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | <a href="tel:+447402495410">+44 7402 495410</a>)</p>
<p><strong>Service Area:</strong> Glasgow &amp; Surrounding Areas</p>
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>Policy Directory</h2>
<nav class="legal-index" aria-label="Legal policies">
<ul class="legal-index__grid">
{$grid}
</ul>
</nav>
</div>
HTML;
}

/**
 * Shared contact markup.
 *
 * @return string
 */
function somvio_legal_contact_html() {
	return '<p>Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>';
}

/**
 * Privacy Policy seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_privacy_policy_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>1. Introduction</h2>
<p>Welcome to Somvio ("Somvio", "we", "our", "us"). This Privacy Policy explains how we collect, use, store and protect your personal information when you use our website or cleaning services. We process personal data in accordance with the UK GDPR and the Data Protection Act 2018.</p>
</div>
<div class="legal-content__section">
<h2>2. Contact Details</h2>
<p>Business Name: Somvio | Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Telephone: <a href="tel:+447402495410">+44 7402 495410</a> | Service Area: Glasgow &amp; Surrounding Areas</p>
</div>
<div class="legal-content__section">
<h2>3. Information We Collect</h2>
<p>We may collect your name, email address, telephone number, property address, booking details, communications with us, payment information (processed securely by Stripe), IP address, browser information, cookies and website analytics.</p>
</div>
<div class="legal-content__section">
<h2>4. How We Collect Information</h2>
<p>We collect information when you submit a booking request, request a quotation, contact us by phone, WhatsApp or email, or interact with our website.</p>
</div>
<div class="legal-content__section">
<h2>5. Legal Basis</h2>
<p>We process personal information to perform our contract with you, comply with legal obligations, protect our legitimate business interests and where you have given consent.</p>
</div>
<div class="legal-content__section">
<h2>6. How We Use Information</h2>
<p>We use your information to respond to enquiries, provide quotations, confirm bookings, deliver services, process payments, improve our website, prevent fraud and comply with legal obligations.</p>
</div>
<div class="legal-content__section">
<h2>7. Marketing</h2>
<p>We will only send marketing communications where permitted by law or where you have chosen to receive them. You can unsubscribe at any time.</p>
</div>
<div class="legal-content__section">
<h2>8. Sharing Information</h2>
<p>We may share data with Stripe, website hosting providers, booking software providers, accountants, legal advisers and government authorities where required by law. We never sell your personal information.</p>
</div>
<div class="legal-content__section">
<h2>9. International Transfers</h2>
<p>If information is processed outside the UK, appropriate safeguards will be applied in accordance with UK GDPR.</p>
</div>
<div class="legal-content__section">
<h2>10. Data Retention</h2>
<p>We retain information only for as long as necessary. Financial records may be retained where required by UK law.</p>
</div>
<div class="legal-content__section">
<h2>11. Cookies</h2>
<p>Our website uses essential cookies and may use analytics cookies. Please see our Cookie Policy for further details.</p>
</div>
<div class="legal-content__section">
<h2>12. Security</h2>
<p>We implement appropriate technical and organisational measures to protect personal information from unauthorised access, loss or misuse.</p>
</div>
<div class="legal-content__section">
<h2>13. Your Rights</h2>
<p>You may request access, correction, deletion, restriction of processing, data portability or object to processing in accordance with UK GDPR. Contact: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a>.</p>
</div>
<div class="legal-content__section">
<h2>14. Children's Privacy</h2>
<p>Our services are intended for adults. We do not knowingly collect personal information from children under 13.</p>
</div>
<div class="legal-content__section">
<h2>15. Changes</h2>
<p>We may update this Privacy Policy from time to time. The latest version will always be published on our website.</p>
</div>
<div class="legal-content__section">
<h2>16. Contact</h2>
<p>Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>
</div>
HTML;
}

/**
 * Terms & Conditions seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_terms_conditions_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>1. About Somvio</h2>
<p>Business Name: Somvio | Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a> | Service Area: Glasgow &amp; Surrounding Areas</p>
</div>
<div class="legal-content__section">
<h2>2. Services</h2>
<p>Somvio provides Regular Cleaning, Deep Cleaning, End of Tenancy Cleaning, Airbnb Cleaning and After Builders Cleaning. Additional services may be offered by agreement.</p>
</div>
<div class="legal-content__section">
<h2>3. Bookings</h2>
<p>Bookings may be made via our website, phone, WhatsApp or email. A booking is confirmed only after confirmation from Somvio.</p>
</div>
<div class="legal-content__section">
<h2>4. Quotes</h2>
<p>All quotes are estimates based on the information provided. Final pricing may change if the property's size, condition or requested services differ from the original booking.</p>
</div>
<div class="legal-content__section">
<h2>5. Payment</h2>
<p>Payment is due immediately after completion unless otherwise agreed. We accept Stripe and cash.</p>
</div>
<div class="legal-content__section">
<h2>6. Cancellations</h2>
<p>Customers may cancel free of charge up to 24 hours before the appointment. Cancellations made with less notice may incur a reasonable cancellation fee.</p>
</div>
<div class="legal-content__section">
<h2>7. Access to the Property</h2>
<p>The customer is responsible for providing safe access to the property, electricity, water and any information needed to complete the service.</p>
</div>
<div class="legal-content__section">
<h2>8. Customer Responsibilities</h2>
<p>Customers should secure valuables, disclose any hazards, advise us about alarms, pets or access restrictions, and provide accurate booking information.</p>
</div>
<div class="legal-content__section">
<h2>9. Satisfaction Guarantee</h2>
<p>If you are not satisfied with the service, please notify us within 24 hours. Where appropriate, Somvio will arrange a re-clean of the affected areas.</p>
</div>
<div class="legal-content__section">
<h2>10. Liability</h2>
<p>Somvio maintains appropriate insurance. We are not responsible for pre-existing damage, normal wear and tear, hidden defects, faulty fixtures or damage caused by circumstances beyond our reasonable control.</p>
</div>
<div class="legal-content__section">
<h2>11. After Builders Cleaning</h2>
<p>This service assumes that major construction work has been completed and that the property is safe to enter. Removal of heavy building waste is not included unless agreed separately.</p>
</div>
<div class="legal-content__section">
<h2>12. Airbnb Cleaning</h2>
<p>Airbnb Cleaning may include linen changes, towel replacement and agreed consumable replenishment where included in the booking.</p>
</div>
<div class="legal-content__section">
<h2>13. Privacy</h2>
<p>Personal information is processed in accordance with our Privacy Policy.</p>
</div>
<div class="legal-content__section">
<h2>14. Governing Law</h2>
<p>These Terms &amp; Conditions are governed by the laws of Scotland. Any dispute shall be subject to the jurisdiction of the Scottish courts.</p>
</div>
<div class="legal-content__section">
<h2>15. Contact</h2>
<p>Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>
</div>
HTML;
}

/**
 * Cookie Policy seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_cookie_policy_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>1. Introduction</h2>
<p>This Cookie Policy explains how Somvio uses cookies and similar technologies when you visit our website.</p>
</div>
<div class="legal-content__section">
<h2>2. What Are Cookies?</h2>
<p>Cookies are small text files stored on your device that help websites function properly, remember preferences and improve user experience.</p>
</div>
<div class="legal-content__section">
<h2>3. Types of Cookies We Use</h2>
<ul>
<li><strong>Essential Cookies:</strong> Required for website functionality, security and booking features.</li>
<li><strong>Analytics Cookies:</strong> Used to understand how visitors use our website (for example, Google Analytics).</li>
<li><strong>Functional Cookies:</strong> Remember your preferences, such as language or cookie settings.</li>
<li><strong>Marketing Cookies:</strong> May be used if we enable advertising services such as Google Ads or Meta Pixel. These cookies will only be used where required by law after your consent.</li>
</ul>
</div>
<div class="legal-content__section">
<h2>4. Why We Use Cookies</h2>
<p>We use cookies to operate the website, improve performance, analyse visitor behaviour, remember preferences and help maintain security.</p>
</div>
<div class="legal-content__section">
<h2>5. Managing Cookies</h2>
<p>You can accept or reject non-essential cookies through our cookie banner. Most browsers also allow you to block or delete cookies at any time.</p>
</div>
<div class="legal-content__section">
<h2>6. Third-Party Cookies</h2>
<p>Some trusted third parties, such as Stripe, Google Analytics or embedded services, may place cookies on your device when you use our website.</p>
</div>
<div class="legal-content__section">
<h2>7. Changes to this Policy</h2>
<p>We may update this Cookie Policy from time to time. The latest version will always be available on our website.</p>
</div>
<div class="legal-content__section">
<h2>8. Contact</h2>
<p>Somvio | Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>
</div>
HTML;
}

/**
 * Cancellation & Refund Policy seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_cancellation_policy_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>Cancellation &amp; Refund Policy</h2>
<p>Free cancellation is available up to 24 hours before the scheduled appointment. Cancellations made with less than 24 hours notice may incur a reasonable cancellation fee. If a service does not meet agreed standards, please notify us within 24 hours so we can arrange a complimentary re-clean or assess refund eligibility.</p>
</div>
<div class="legal-content__section">
<h2>Contact</h2>
<p>Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>
</div>
HTML;
}

/**
 * Website Disclaimer seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_disclaimer_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>1. General Information</h2>
<p>The information provided on the Somvio website is for general informational purposes only. While we make every effort to keep all content accurate and up to date, we do not guarantee that all information is complete, accurate or current at all times.</p>
</div>
<div class="legal-content__section">
<h2>2. Quotations and Pricing</h2>
<p>Any prices, estimates or quotations displayed on this website are provided as guidance only. Final pricing depends on the condition, size and specific requirements of the property and will be confirmed before work begins.</p>
</div>
<div class="legal-content__section">
<h2>3. No Professional Advice</h2>
<p>The content on this website should not be considered legal, financial or professional advice.</p>
</div>
<div class="legal-content__section">
<h2>4. External Links</h2>
<p>Our website may contain links to third-party websites. Somvio is not responsible for the content, availability, privacy practices or accuracy of information provided by external websites.</p>
</div>
<div class="legal-content__section">
<h2>5. Limitation of Liability</h2>
<p>To the fullest extent permitted by law, Somvio shall not be liable for any indirect, incidental or consequential loss arising from the use of this website.</p>
</div>
<div class="legal-content__section">
<h2>6. Contact</h2>
<p>Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>
</div>
HTML;
}

/**
 * Accessibility Statement seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_accessibility_statement_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>1. Our Commitment</h2>
<p>Somvio is committed to making our website accessible and usable for as many people as possible, including people with disabilities. We strive to meet WCAG 2.1 Level AA guidelines where practical.</p>
</div>
<div class="legal-content__section">
<h2>2. Feedback &amp; Contact</h2>
<p>If you experience any difficulty accessing information, please contact us: Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a>.</p>
</div>
HTML;
}

/**
 * Online Booking Terms seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_booking_terms_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>1. Acceptance of Bookings</h2>
<p>Bookings submitted through the Somvio website, WhatsApp, email or telephone are requests only until Somvio sends a formal confirmation.</p>
</div>
<div class="legal-content__section">
<h2>2. Pricing &amp; Access</h2>
<p>Prices are estimates. Final price may adjust if property details differ. Customers must provide safe access, electricity, and water.</p>
</div>
<div class="legal-content__section">
<h2>3. Contact</h2>
<p>Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>
</div>
HTML;
}

/**
 * Customer Satisfaction Guarantee seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_satisfaction_guarantee_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>Customer Satisfaction Guarantee</h2>
<p>If any agreed cleaning task is not completed to a satisfactory standard, please contact us within 24 hours (with photos if possible). We will arrange a complimentary re-clean of the affected areas.</p>
</div>
<div class="legal-content__section">
<h2>Contact</h2>
<p>Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>
</div>
HTML;
}

/**
 * Complaints Procedure seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_complaints_procedure_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>Complaints Procedure</h2>
<p>Complaints must be submitted within 24 hours of service via email, phone, or WhatsApp. We aim to acknowledge complaints within 1 business day and resolve them within 2 business days via re-clean, partial refund, or full refund.</p>
</div>
<div class="legal-content__section">
<h2>Contact</h2>
<p>Email: <a href="mailto:info@somvio.co.uk">info@somvio.co.uk</a> | Phone: <a href="tel:+447402495410">+44 7402 495410</a></p>
</div>
HTML;
}

/**
 * Service Checklist seed HTML.
 *
 * @param string $date Effective date (escaped).
 * @return string
 */
function somvio_get_service_checklist_seed_content( $date = '' ) {
	if ( '' === $date ) {
		$date = esc_html( somvio_get_legal_effective_date() );
	}

	return <<<HTML
<div class="legal-content__section">
<p><strong>Effective Date:</strong> {$date}</p>
</div>
<div class="legal-content__section">
<h2>Regular Cleaning</h2>
<ul>
<li>Dust surfaces</li>
<li>Vacuum floors</li>
<li>Mop hard floors</li>
<li>Clean kitchen worktops/sink</li>
<li>Wipe appliance exteriors</li>
<li>Clean toilets/baths/showers</li>
<li>Empty bins</li>
</ul>
</div>
<div class="legal-content__section">
<h2>Deep Cleaning</h2>
<p>Includes Regular Cleaning plus:</p>
<ul>
<li>Detailed skirtings</li>
<li>Doors/frames</li>
<li>Limescale removal</li>
<li>Light switches</li>
<li>Detailed kitchen/bathroom clean</li>
</ul>
</div>
<div class="legal-content__section">
<h2>End of Tenancy</h2>
<p>Includes Deep Cleaning plus:</p>
<ul>
<li>Inside kitchen cupboards</li>
<li>Inside oven (if booked)</li>
<li>Inside windows</li>
<li>Detailed appliance cleaning</li>
</ul>
</div>
<div class="legal-content__section">
<h2>Airbnb Cleaning</h2>
<ul>
<li>General clean</li>
<li>Change bed linen</li>
<li>Replace towels</li>
<li>Restock agreed consumables</li>
<li>Visual property check</li>
</ul>
</div>
<div class="legal-content__section">
<h2>After Builders</h2>
<ul>
<li>Construction dust removal</li>
<li>Internal surfaces/windows</li>
<li>Paint splash removal where safe</li>
</ul>
</div>
HTML;
}

/**
 * Legacy alias used by older setup helpers.
 *
 * @return string
 */
function somvio_get_terms_of_use_seed_content() {
	return somvio_get_terms_conditions_seed_content();
}
