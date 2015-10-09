<?php
$this->attendees_table->prepare_items();

$event_id = isset( $_GET['event_id'] ) ? intval( $_GET['event_id'] ) : 0;
$event = get_post( $event_id );
$tickets = Tribe__Tickets__Tickets::get_event_tickets( $event_id );

$total_sold = 0;
$total_pending = 0;

foreach ( $tickets as $ticket ) {
	$sold = ! empty ( $ticket->qty_sold ) ? $ticket->qty_sold : 0;

	$total_sold += absint( $sold );
	$total_pending += absint( $ticket->qty_pending );
	$total_completed = $total_sold - $total_pending;
}//end foreach

if ( function_exists( 'tribe_has_venue' ) && tribe_has_venue( $event_id ) ) {
	$venue_id = tribe_get_venue_id( $event_id );

	$url = get_post_meta( $venue_id, '_VenueURL', true );
	if ( $url ) {
		$display_url = parse_url( $url, PHP_URL_HOST );
		$display_url .= parse_url( $url, PHP_URL_PATH ) ? '/&hellip;' : '';
		$display_url = apply_filters( 'tribe_venue_display_url', $display_url, $url, $venue_id );
	}
}
?>

<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-tickets-attendees"><br></div>
	<h1><?php esc_html_e( 'Attendees', 'tribe-tickets' ); ?></h1>

	<h1><?php echo apply_filters( 'tribe_events_tickets_attendees_event_title', $event->post_title, $event->ID ); ?></h1>

	<div id="tribe-filters" class="metabox-holder">
		<div id="filters-wrap" class="postbox">
			<h3 title="Click to toggle"><?php esc_html_e( 'Event Summary', 'tribe-tickets' ); ?></h3>

			<?php do_action( 'tribe_events_tickets_attendees_event_summary_table_before', $event_id ); ?>

			<table class="eventtable ticket_list">
				<tr>
					<td width="33%" valign="top">
						<?php do_action( 'tribe_events_tickets_attendees_event_details_top', $event_id ); ?>

						<h4><?php esc_html_e( 'Event Details', 'tribe-tickets' ); ?></h4>

						<?php if ( function_exists( 'tribe_get_start_date' ) ): ?>
							<strong><?php esc_html_e( 'Start Date / Time:', 'tribe-tickets' ) ?></strong>
							<?php echo tribe_get_start_date( $event_id, false, tribe_get_datetime_format( true ) ) ?>
							<br/>

							<strong><?php esc_html_e( 'End Date / Time:', 'tribe-tickets' ) ?></strong>
							<?php
							echo tribe_get_end_date( $event_id, false, tribe_get_datetime_format( true ) );
						endif;

						if ( function_exists('tribe_has_venue') && tribe_has_venue( $event_id ) ) {
							?>

							<div class="venue-name">
								<strong><?php echo tribe_get_venue_label_singular(); ?>: </strong>
								<?php echo tribe_get_venue( $event_id ) ?>
							</div>

							<div class="venue-address">
								<strong><?php _e( 'Address:', 'tribe-tickets' ); ?> </strong>
								<?php echo tribe_get_full_address( $venue_id ); ?>
							</div>

							<?php
							if ( $phone = tribe_get_phone( $venue_id ) ) {
								?>
								<div class="venue-phone">
									<strong><?php echo esc_html( __( 'Phone:', 'tribe-tickets' ) ); ?> </strong>
									<?php echo esc_html( $phone ); ?>
								</div>
								<?php
							}//end if

							if ( $url ) {
								?>
								<div class="venue-url">
									<strong><?php echo esc_html( __( 'Website:', 'tribe-tickets' ) ); ?> </strong>
									<a target="_blank" href="<?php echo esc_url( $url ); ?>">
										<?php echo esc_html( $display_url ); ?>
									</a>
								</div>
								<?php
							}//end if
						}//end if venue

						do_action( 'tribe_events_tickets_attendees_event_details_bottom', $event_id );
						?>
					</td>
					<td width="33%" valign="top">
						<?php do_action( 'tribe_events_tickets_attendees_ticket_sales_top', $event_id ); ?>

						<h4><?php esc_html_e( 'Ticket Sales', 'tribe-tickets' ); ?></h4>

						<?php

						foreach ( $tickets as $ticket ) {
							?>
							<strong><?php echo esc_html( $ticket->name ) ?>: </strong>
							<?php echo tribe_tickets_get_ticket_stock_message( $ticket ); ?>
							<br/>
							<?php
						}//end foreach

						do_action( 'tribe_events_tickets_attendees_ticket_sales_bottom', $event_id );
						?>
					</td>
					<td width="33%" valign="middle">
						<div class="totals">
							<?php
							do_action( 'tribe_events_tickets_attendees_totals_top', $event_id );

							$checkedin = Tribe__Tickets__Tickets::get_event_checkedin_attendees_count( $event_id ); ?>

							<span id="total_tickets_sold_wrapper">
								<?php esc_html_e( 'Tickets sold:', 'tribe-tickets' ) ?>
								<span id="total_tickets_sold"><?php echo $total_sold ?></span>
							</span>

							<?php
							if ( $total_pending > 0 ) {
								?>
								<span id="sales_breakdown_wrapper">
								<br />
									<?php esc_html_e( 'Finalized:', 'tribe-tickets' ); ?>
									<span id="total_issued"><?php echo $total_completed ?></span>

									<?php esc_html_e( 'Awaiting review:', 'tribe-tickets' ); ?>
									<span id="total_pending"><?php echo $total_pending ?></span>
								</span>
								<?php
							}//end if
							?>

							<span id="total_checkedin_wrapper">
								<br />
								<?php esc_html_e( 'Checked in:', 'tribe-tickets' ); ?>
								<span id="total_checkedin"><?php echo $checkedin ?></span>
							</span>

							<?php do_action( 'tribe_events_tickets_attendees_totals_bottom', $event_id ); ?>
						</div>
					</td>
				</tr>
			</table>

			<?php do_action( 'tribe_events_tickets_attendees_event_summary_table_after', $event_id ); ?>

		</div>
	</div>

	<form id="topics-filter" method="post">
		<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
		<input type="hidden" name="event_id" id="event_id" value="<?php echo esc_attr( $event_id ); ?>" />
		<input type="hidden" name="post_type" value="<?php echo esc_attr( $event->post_type ); ?>" />
		<?php $this->attendees_table->display() ?>
	</form>

	<div id="attendees_email_wrapper" title="<?php esc_html_e( 'Send the attendee list by email', 'tribe-tickets' ); ?>">
		<div id="email_errors"></div>
		<div id="email_send">
			<label for="email_to_user">
				<span><?php esc_html_e( 'Select a User:', 'tribe-tickets' ); ?></span>
				<?php wp_dropdown_users(
					array(
						'name'             => 'email_to_user',
						'id'               => 'email_to_user',
						'show_option_none' => esc_html__( 'Select...', 'tribe-tickets' ),
						'selected'         => '',
					)
				); ?>
			</label>
			<span class="attendees_or"><?php esc_html_e( 'or', 'tribe-tickets' ); ?></span>
			<label for="email_to_address">
				<span><?php esc_html_e( 'Email Address:', 'tribe-tickets' ); ?></span>
				<input type="text" name="email_to_address" id="email_to_address" value="">
			</label>
		</div>
		<div id="email_response"></div>
	</div>
</div>