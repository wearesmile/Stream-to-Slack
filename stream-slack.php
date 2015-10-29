<?php
/**
 * Plugin Name: Stream to Slack
 * Plugin URI: https://github.com/wearesmile/Stream-to-Slack
 * Description: Send Stream logs to Slack.
 * Author: SMILE
 * Version: 0.0.1
 * Author URI: http://wearesmile.com/
 */

require_once dirname( __FILE__ ) . '/inc/class-stream-slack-api.php';

function register_stream_slack() {
	$stream_slack = new Stream_Slack_API();
}
add_action( 'init', 'register_stream_slack' );
