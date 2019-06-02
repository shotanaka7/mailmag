<?php
/**
 * Plugin Name:mailmag
 * Description:メールマガジン機能を追加するプラグインです。HTML及びテキストメールに対応し、SMTP経由でメールを送信します。
 * Version:1.1.0
 * Author:Sho Tanaka
 *
 * @package mailmag
 * @version 1.1.0
 */


/**
 * 定数
 */
define( 'MAILMAG_VERSION', '1.1.0' );
define( 'MAILMAG_DB_VERSION', '1.1' );
define( 'MAILMAG_CRON_HOOK', 'mailmag_sent_function' );

// css,js読み込み
add_action( 'admin_enqueue_scripts', 'enqueue_mailmag_admin_scripts' );
function enqueue_mailmag_admin_scripts( $hook ) {
	wp_enqueue_style( 'mailmag-admin', plugins_url( 'mailmag/assets/css/admin.css' ), __FILE__ );
	wp_enqueue_script( 'mailmag-date', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/js/date.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'mailmag-time-limit', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'assets/js/timeLeft.js', array( 'jquery' ), '', true );
}

/**
 * WP_Errorの設定
 **/
function mailmag_admin_notices() {
	$messages = get_transient( 'mailmag-admin-errors' );
	if ( $messages ) { ?>
		<div class="error">
		<?php if ( 1 < count( $messages ) ) : ?>
				<ul>
			<?php
			foreach ( $messages as $message ) {
				echo '<li>' . $message . '</li>';
			}
			?>
				</ul>
	<?php else : ?>
				<p><?php echo $messages[0]; ?></p>
	<?php endif; ?>
		</div>
		<?php
	}
}
function mailmag_admin_updates() {
	$messages = get_transient( 'mailmag-admin-updates' );
	if ( $messages ) {
		?>
		<div class="updated">
		<?php if ( 1 < count( $messages ) ) : ?>
				<ul>
			<?php
			foreach ( $messages as $message ) {
				echo '<li>' . $message . '</li>';
			}
			?>
				</ul>
	<?php else : ?>
				<p><?php echo $messages[0]; ?></p>
	<?php endif; ?>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'mailmag_admin_notices' );
add_action( 'admin_notices', 'mailmag_admin_updates' );

/**
 * ユーザープロフィール情報に項目を追加
 * mailmag - メールマガジン購読
 **/
function mailmag_set_user_profile( $bool ) {
	global $profileuser;
	$val = $profileuser->mailmag_subscription_checkbox; // ユーザー情報を変数にセット
	// 初期値を変数にセット
	$check_1 = ' checked';
	$check_0 = '';
	if ( ! isset( $val ) ) {
		// WP_Errorのセット
		$e = new WP_Error();
		$e->add( 'error', 'mailmag error : mailmag_subscription_checkboxの変数がNULLもしくは空です' );
		set_transient( 'mailmag-admin-errors', $e->get_error_messages(), 1 );
	}
	if ( '0' == $val ) {
		$check_1 = '';
		$check_0 = ' checked';
	}
	?>
	<!-- 管理画面のHTML -->
	<tr>
		<th scope="row">mailmag - メールマガジン購読</th>
		<td>
			<p>
				<label>
					<input type="radio" id="mailmag_subscription_checkbox_1" name="mailmag_subscription_checkbox" value="1" class="tog" <?php echo $check_1; ?>>購読する
				</label>
			</p>
			<p>
				<label>
					<input type="radio" id="mailmag_subscription_checkbox_0" name="mailmag_subscription_checkbox" value="0" class="tog" <?php echo $check_0; ?>>購読しない
				</label>
			</p>
		</td>
	</tr>
	<?php
	return $bool;
}
add_action( 'show_password_fields', 'mailmag_set_user_profile' );

/**
 * ユーザープロフィール情報のアップデート
 **/
function mailmag_update_user_profile( $user_id, $old_user_data ) {
	if ( isset( $_POST['mailmag_subscription_checkbox'] ) ) {
		update_user_meta( $user_id, 'mailmag_subscription_checkbox', $_POST['mailmag_subscription_checkbox'] );
	}
}
add_action( 'profile_update', 'mailmag_update_user_profile', 10, 2 );

/**
* ファイルの読み込み
**/
$files = array(
	'db.php',
	'admin-menu.php',
	'get-db.php',
);
foreach ( $files as $file ) {
	$path = plugin_dir_path( __FILE__ ) . 'lib/';
	if ( file_exists( $path . $file ) ) {
		include_once $path . $file;
	}
}

/**
 * プラグインが有効になったら実行
 * DBの作成
 **/
register_activation_hook( __FILE__, 'mailmag_create_table' );

/**
 * メール送信用の関数とアクションフック
 **/
add_action( MAILMAG_CRON_HOOK, 'mailmag_sent_function' );

function mailmag_sent_function( $mail_id ) {
	include_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php'; // wp-load.phpを読み込む
	include_once plugin_dir_path( __FILE__ ) . 'lib/get-unixtime-millsecond.php';
	// データベースから情報を呼び出しておく
	global $wpdb;
	$msg = array();
	usleep( 500000 );
	// mailmag_sent_messageから呼び出し
	$prep_sql = 'SELECT * FROM ' . $wpdb->prefix . 'mailmag_sent_message WHERE id=' . $mail_id . ';'; // mysqlからデータを呼び出す
	// @codingStandardsIgnoreStart
	$results = $wpdb->get_results( $prep_sql ); // 呼び出した情報を$results[0]に入れる
	// @codingStandardsIgnoreEnd

	usleep( 500000 );

	if ( 'dev' == $results[0]->mail_group ) {
		/**
		 * 送信先セレクトボックスで開発者が選択されていたら
		 * 開発者emailリストを変数にセット
		 **/
		$table_name = 'mailmag_admin_users';
		$prep_sql   = 'SELECT * FROM ' . $wpdb->prefix . $table_name . ' ORDER BY id ASC;';
     // @codingStandardsIgnoreStart
     $admin_mails = $wpdb->get_results( $prep_sql );
     // @codingStandardsIgnoreEnd
		if ( 0 != count( $admin_mails ) ) {
			$number_send = 0;
			foreach ( $admin_mails as $result ) {
				$recipients[] = $result->email;
				$number_send ++;
				$user_nicknames[] = $result->email;
			}
		} else {
			$recipients = array();
		}
	} elseif ( 'wpusers' == $results[0]->mail_group ) {
		/**
		 * 送信先セレクトボックスで全ユーザーが選択されていたら
		 * 全ユーザーのemailリストを変数にセット
		 **/
		$users       = get_users(
			array(
				'orderby' => ID,
				'order'   => ASC,
			)
		);
		$number_send = 0;
		foreach ( $users as $user ) :
			$number_send ++;
			$recipients[]     = $user->user_email;
			$user_nicknames[] = get_user_meta( $user->ID, 'nickname', true );
		endforeach;
	} elseif ( 'subscribers' == $results[0]->mail_group ) {
		/**
		 * 送信先セレクトボックスでメルマガ購読者が選択されていたら
		 * メルマガ購読者のemailリストを変数にセット
		 **/
		$users       = get_users(
			array(
				'orderby' => ID,
				'order'   => ASC,
			)
		);
		$number_send = 0;
		foreach ( $users as $user ) :
			if ( '1' == get_user_meta( $user->ID, 'mailmag_subscription_checkbox', true ) ) {
				$number_send ++;
				$recipients[]     = $user->user_email;
				$user_nicknames[] = get_user_meta( $user->ID, 'nickname', true );
			}
		endforeach;
	}// End if().

	// 送信前のデータベース登録処理
	$mailmag_sent_message_update_data = array(
		'number_send' => count( $recipients ),
	);
	$where                            = array(
		'id' => $mail_id,
	);
	$format                           = array(
		'%d',
	);
	$where_format                     = array( '%d' );
	$res                              = $wpdb->update( $wpdb->prefix . 'mailmag_sent_message', $mailmag_sent_message_update_data, $where, $format, $where_format );
	if ( false === $res ) {
		$e = new WP_Error();
		$e->add( 'error', 'mailmag error : 送信件数がアップロードできませんでした' );
		set_transient( 'mailmag-admin-errors', $e->get_error_messages(), 1 );
	}

	/**
	* メール送信前のチェック
	**/

	if ( ! isset( $recipients ) ) {
		$msg[] = 'mailmag error : 送信先がありません';
	}
	if ( ! isset( $results[0]->subject ) ) {
		$msg[] = 'mailmag error : 件名がセットされていません';
	}
	if ( ! isset( $results[0]->message ) ) {
		$msg[] = 'mailmag error : 本文がセットされていません';
	}
	if ( 0 == count( $msg ) ) { // $msgにエラー文が入っていなければ

		$sent_count  = 0;
		$error_count = 0;
		// メールヘッダーの設定
		$headers[] = 'From: ふるさとひろばメールマガジン <news@furusato-hiroba.jp>';
		$headers[] = 'Reply-To: ふるさとひろばお問い合わせ <info@furusato-hiroba.jp>';

		// 送信する件名と本文の%name%をニックネームに変更する為の準備
		$loop    = 0; // ニックネームの配列呼び出し用にループカウントをセット
		$subject = $results[0]->subject; // 件名を変数にセットしておく
		$message = str_replace( array( "\n", '\n', "\r", '\r' ), "\r\n", $results[0]->message ); // 本文の改行コードをメール標準のCRLFに変換し、変数にセットしておく

		if ( 'text' === $results[0]->format ) {
			/**
			* テキストメールの場合の処理
			**/
			$message = html_entity_decode( $message, ENT_NOQUOTES, 'UTF-8' ); // エンティティーをデコード
			// メール件数分送信処理を回す
			foreach ( $recipients as $recipient ) {
				/**
				 * 件名と本文の%name%をニックネームに書き換える
				 **/
				$user_nickname   = $user_nicknames[ $loop ]; // ユーザーのニックネームを個別に取り出して変数にセット
				$replace_subject = str_replace( '%name%', $user_nickname, $subject );
				$replace_message = str_replace( '%name%', $user_nickname, $message );

				trim( $recipient ); // 文字列の先頭および末尾にあるホワイトスペースを取り除く
				// メール送信
				$return = wp_mail( $recipient, $replace_subject, $replace_message, $headers );
				// データベースに情報登録
				if ( false === $return ) { // 送信エラーの場合
					$error_count ++;
					$mailmag_sent_log_data = array(
						'time'           => current_time( 'mysql' ),
						'unix_timestamp' => get_unixtime_millsecond(),
						'mail_id'        => $mail_id,
						'success'        => false,
						'email'          => $recipient,
					);
					$wpdb->insert( $wpdb->prefix . 'mailmag_sent_log', $mailmag_sent_log_data );
				} else { // 送信成功の場合
					$sent_count ++;
					$mailmag_sent_log_data = array(
						'time'           => current_time( 'mysql' ),
						'unix_timestamp' => get_unixtime_millsecond(),
						'mail_id'        => $mail_id,
						'success'        => true,
						'email'          => $recipient,
					);
					$wpdb->insert( $wpdb->prefix . 'mailmag_sent_log', $mailmag_sent_log_data );
				}
				$loop++;
			}
		} elseif ( 'HTML' === $format ) {
			/**
			* HTMLメールの場合の処理
			**/
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			foreach ( $recipients as $recipient ) {
				/**
				 * 件名と本文の%name%をニックネームに書き換える
				 **/
				$user_nickname   = $user_nicknames[ $loop ]; // ユーザーのニックネームを個別に取り出して変数にセット
				$replace_subject = str_replace( '%name%', $user_nickname, $subject );
				$replace_message = str_replace( '%name%', $user_nickname, $message );

				trim( $recipient ); // 文字列の先頭および末尾にあるホワイトスペースを取り除く
				// メール送信
				$return = wp_mail( $recipient, $replace_subject, $replace_message, $headers );
				// データベースに情報登録
				if ( false === $return ) {
					$error_count ++;
					$mailmag_error_log[]   = $recipient;
					$mailmag_sent_log_data = array(
						'time'           => current_time( 'mysql' ),
						'unix_timestamp' => get_unixtime_millsecond(),
						'mail_id'        => $mail_id,
						'success'        => false,
						'email'          => $recipient,
					);
					$wpdb->insert( $wpdb->prefix . 'mailmag_sent_log', $mailmag_sent_log_data );
				} else {
					$sent_count ++;
					$mailmag_sent_log_data = array(
						'time'           => current_time( 'mysql' ),
						'unix_timestamp' => get_unixtime_millsecond(),
						'mail_id'        => $mail_id,
						'success'        => true,
						'email'          => $recipient,
					);
					$wpdb->insert( $wpdb->prefix . 'mailmag_sent_log', $mailmag_sent_log_data );
				}
			}
		}// End if().
		if ( 0 == $sent_count ) {
			$status = 98; // 送信件数が0
		} else {
			$status = 1; // 送信を1件でもしている
		}
		// 送信後のデータベース登録処理
		$mailmag_sent_message_update_data = array(
			'sent_count'  => $sent_count,
			'error_count' => $error_count,
			'status'      => $status,
		);
		$where                            = array(
			'id' => $mail_id,
		);
		$format                           = array(
			'%d',
			'%d',
			'%d',
		);
		$where_format                     = array( '%d' );
		$wpdb->update( $wpdb->prefix . 'mailmag_sent_message', $mailmag_sent_message_update_data, $where, $format, $where_format );
	} else {
		foreach ( $msg as $m ) {
			echo  $m . '<br>';
		}
	}// End if().
}
