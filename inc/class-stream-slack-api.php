<?php
class Stream_Slack_API {

	public $stream;
	public $options;

	public function __construct() {

		if ( ! class_exists( 'WP_Stream\Plugin' ) ) {
			add_action( 'admin_notices', array( $this, 'stream_not_found_notice' ) );
			return false;
		}

		$this->stream = wp_stream_get_instance();
		$this->options = $this->stream->settings->options;

		add_filter( 'wp_stream_settings_option_fields', array( $this, 'options' ) );

		if ( empty( $this->options['slack_destination'] ) ) {
			add_action( 'admin_notices', array( $this, 'destination_undefined_notice' ) );
		}
		else {
			add_action( 'wp_stream_record_inserted', array( $this, 'log' ), 10, 2 );
		}
	}

	public function options( $fields ) {

		$settings = array(
			'title' => esc_html__( 'Slack', 'stream-slack' ),
			'fields' => array(
				array(
					'name'        => 'destination',
					'title'       => esc_html__( 'Webhook URL', 'stream-slack' ),
					'type'        => 'text',
					'desc'        => wp_kses_post( 'Find your Incoming Webhook URL in the "Integrations" section of your Slack settings.' ),
					'default'     => '',
				),
				array(
					'name'        => 'username',
					'title'       => esc_html__( 'Bot Name', 'stream-slack' ),
					'type'        => 'text',
					'desc'        => wp_kses_post( 'This allows you to define the name of the bot that posts your message' ),
					'default'     => '',
				),
				array(
					'name'        => 'channel',
					'title'       => esc_html__( 'Channel', 'stream-slack' ),
					'type'        => 'text',
					'desc'        => wp_kses_post( 'Event the name of the channel you\'d like to post to. This should include the #' ),
					'default'     => '',
				),
				array(
					'name'        => 'icon_emoji',
					'title'       => esc_html__( 'Icon Emoji', 'stream-slack' ),
					'type'        => 'text',
					'desc'        => wp_kses_post( 'Use an Emoji as an update icon!' ),
					'default'     => '',
				),
			),
		);

		$fields['slack'] = $settings;

		return $fields;

	}

	public function log( $record_id, $record_array ) {

		$record = $record_array;

		$this->send_remote_syslog( $record );
	}

	/**
	 * This sends data to Slack
	 */
	public function send_remote_syslog( $message ) {

		$url = $this->options['slack_destination'];
		$data = array(
				'channel'      => $this->options['slack_channel'],
				'username'     => $this->options['slack_username'],
				'text'         => $message['summary'],
				'icon_emoji'   => $this->options['slack_icon_emoji'],
			);
			$data_string = json_encode($data);

		wp_remote_post($url, array('body' => $data_string));
	}


	public function destination_undefined_notice() {

		$class = 'error';
		$message = 'To activate the "Stream to Slack" plugin, visit the Slack panel in <a href="' . admin_url( 'admin.php?page=wp_stream_settings' ) . '">Stream Settings</a> and set an Incoming Webhook URL.';
		echo '<div class="' . $class . '"><p>' . $message . '</p></div>';

	}

	public function stream_not_found_notice() {

		$class = 'error';
		$message = 'The "Stream to Slack" plugin requires the <a href="https://wordpress.org/plugins/stream/">Stream</a> plugin to be activated before it can log to Slack.';
		echo '<div class="' . $class . '"><p>' . $message . '</p></div>';

	}

}
