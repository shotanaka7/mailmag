<?php
$mail_id = $_POST['mail_id']; // Get the mail_id.

require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );

check_admin_referer( 'cancel-mailmag_' . $mail_id ); // nonce check.

if ( ! empty( $mail_id ) ) { // error check.
	mailmag_cron_cancel( $mail_id );
} else {
	echo 'mailmag error : 正しくない処理です';
}

function mailmag_cron_cancel( $mail_id ) {
	// スケジュールの削除
	wp_clear_scheduled_hook( MAILMAG_CRON_HOOK, array( $mail_id ) );

	// 削除が終わったらステータスを書き換え
	global $wpdb;
	$mailmag_sent_message_update_data = array(
		'status' => 0, // キャンセル用の数値
	);
	$where                            = array(
		'id' => $mail_id,
	);
	$format                           = array(
		'%d',
	);
	$where_format                     = array( '%d' );
	$wpdb->update( $wpdb->prefix . 'mailmag_sent_message', $mailmag_sent_message_update_data, $where, $format, $where_format );
	// WP_Errorのセット
	$e = new WP_Error();
	$e->add( 'updated', 'mailmag : メールID ' . $mail_id . ' の送信予約をキャンセルしました' );
	set_transient( 'mailmag-admin-updates', $e->get_error_messages(), 1 );
	wp_redirect( home_url() . '/wp-admin/admin.php?page=mailmag-sent-items&cancel_id=' . $mail_id );
}
