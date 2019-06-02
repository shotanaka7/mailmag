<?php
/**
* メールIDの取得
**/
function mailmag_get_id() {
	require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );
	global $wpdb;
	$table_name = $wpdb->prefix . 'mailmag_sent_message';
	$results    = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table_name ) ); // テーブルのデータを取得
	foreach ( $results as $result ) {
		$getdata[] = $result;
	}
	// @codingStandardsIgnoreStart
	return $result->Auto_increment;
	// @codingStandardsIgnoreEnd
}
