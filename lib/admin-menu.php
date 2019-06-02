<?php
/**
 * 管理画面にプラグインページを表示
 *
 * 送信履歴一覧 - mainpage
 * 新規作成
 * ユーザー一覧
 * 設定
 **/
function mailmag_page() {
	$icon     = 'dashicons-email'; // mallアイコンをアイコン画像に指定
	$position = 26; //  投稿があるエリアの一番下
	// プラグインページメニュー
	add_menu_page( 'メールマガジン', 'メールマガジン', 'edit_users', 'mailmag-sent-items', 'mailmag_sent_items', $icon, $position );
	// 親メニューが生成するサブメニューのタイトルを送信履歴に変更
	add_submenu_page( 'mailmag-sent-items', 'メールマガジン', '送信履歴', 'edit_users', 'mailmag-sent-items', 'mailmag_sent_items' );
	// サブメニュー・新規作成
	add_submenu_page( 'mailmag-sent-items', '新規作成', '新規作成', 'edit_users', 'mailmag-new', 'mailmag_new' );
	// サブメニュー・ユーザー
	add_submenu_page( 'mailmag-sent-items', 'ユーザー', 'ユーザー', 'edit_users', 'mailmag-users', 'mailmag_users' );
	// サブメニュー・設定
	add_submenu_page( 'mailmag-sent-items', '設定', '設定', 'edit_users', 'mailmag-settings', 'mailmag_settings' );
}
add_action( 'admin_menu', 'mailmag_page' );

/**
 * 新規作成ページ
 **/
function mailmag_new() {
	global $wpdb;
	// ユーザー数の取得
	$all_users = get_users(
		array(
			'orderby' => 'ID',
			'order'   => 'ASC',
		)
	);
	$a         = count( $all_users ); // WordPressの全てのユーザー数を変数にセット
	$f         = 0;
	foreach ( (array) $all_users as $user ) :
		if ( '1' == get_user_meta( $user->ID, 'mailmag_subscription_checkbox', true ) ) {
			$f ++; // WordPressのユーザーのうち、メールマガジン登録者数を変数にセット
		}
	endforeach;
	$table_name = 'mailmag_admin_users';
	$prep_sql   = 'SELECT email FROM ' . $wpdb->prefix . $table_name . ' ORDER BY email ASC;';
	// @codingStandardsIgnoreStart
	$emails = $wpdb->get_results( $prep_sql );
	// @codingStandardsIgnoreEnd
	$d = count( $emails ); // 開発者の数を変数にセット
	?>
	<div class="wrap">
		<h1>mailmag - メールマガジン送信フォーム</h1>
		<hr class="wp-header-end">
		<form action="<?php echo plugins_url( 'send-mail.php', __FILE__ ); ?>" method="post" onsubmit="return check()">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">送信のタイミング</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span>送信のタイミング</span>
								</legend>
								<p>
									<label>
										<input type="radio" id="send-immediately" name="mailmag_timing" value="1" class="tog">即時配信
									</label>
								</p>
								<p>
									<label>
										<input type="radio" id="send-reservation
" name="mailmag_timing" value="2" class="tog" checked>予約配信
									</label>
								</p>
								<div id="mailmag-time-select-wrapper" class="mailmag-time-select-wrapper">
									<?php
									/**
									 * 日付の取得
									 **/
									date_default_timezone_set( 'Asia/Tokyo' );
									$year[] = date( 'Y' ); // 今年
									$year[] = date( 'Y', strtotime( '+1 year' ) ); // 来年
									$month  = range( 1, 12 ); // 月
									$day    = range( 1, 31 ); // 日
									$hour   = range( 0, 23 ); // 時
									$minute = range( 0, 55, 5 ); // 分
									?>
									<div class="mailmag-time-select-row">
										<p>
											<!-- 年 -->
											<select id="send-year" name="send_year" aria-describedby="send-year-description">
												<?php
												foreach ( $year as $y ) :
													?>
													<option value="<?php echo $y; ?>"<?php echo date( 'Y' ) == $y ? ' selected' : ''; ?>><?php echo $y; ?></option>
												<?php endforeach; ?>
											</select>年
											<!-- 月 -->
											<select id="send-month" name="send_month" aria-describedby="send-month-description">
												<?php
												foreach ( $month as $m ) :
													?>
													<option value="<?php echo $m; ?>"<?php echo date( 'm' ) == $m ? ' selected' : ''; ?>><?php echo $m; ?></option>
												<?php endforeach; ?>
											</select>月
											<!-- 日 -->
											<select id="send-day" name="send_day" aria-describedby="send-day-description">
												<!-- date.jsでoption書き出し -->
											</select>日
										</p>
									</div>
									<div class="mailmag-time-select-row">
										<p>
											<!-- 時 -->
											<select id="send-hour" name="send_hour" aria-describedby="send-hour-description">
												<?php
												foreach ( $hour as $h ) :
													?>
													<option value="<?php echo $h; ?>"<?php echo date( 'H', strtotime( '+1 hour' ) ) == $h ? ' selected' : ''; // 現在時間の1時間後を選択するように設定 ?>><?php echo $h; ?></option>
												<?php endforeach; ?>
											</select>時
											<!-- 分 -->
											<select id="send-minute" name="send_minute" aria-describedby="send-minute-description">
												<?php
												foreach ( $minute as $mi ) :
													?>
													<option value="<?php echo $mi; ?>"><?php echo $mi; ?></option>
												<?php endforeach; ?>
											</select>分
										</p>
									</div>
								</div>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row">メール形式</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span>メール形式</span>
								</legend>
								<p>
									<label>
										<input type="radio" id="format-text" name="format" value="text" class="tog" checked>テキスト形式
									</label>
								</p>
								<p>
									<label>
										<input type="radio" id="format-html" name="format" value="HTML" class="tog">HTML形式
									</label>
								</p>
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="mail-group">メールの送信先</label>
						</th>
						<td>
							<select id="mail-group" name="mail_group" aria-describedby="mail-group-description">
								<option value="dev">開発者( <?php echo $d; ?> )</option>
								<option value="subscribers">メールマガジン購読者( <?php echo $f; ?> )</option>
								<option value="wpusers">全てのユーザー( <?php echo $a; ?> )</option>
							</select>
							<p class="description" id="mail-group-description">メールマガジンの送信先リストを選択します。項目の後ろの括弧の中にメール送信する件数を表示しています。件数が0件であることに注意してください。この場合、メールは送信されません。また、テストメールを送信したい場合は任意のメールアドレスに送信することが可能です。<a href="<?php echo admin_url( 'admin.php?page=mailmag-settings', 'https' ); ?>">こちら</a>から送信したいメールアドレスを登録してください。</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="subject">件名</label>
						</th>
						<td>
							<input type="text" id="subject" name="subject" class="large-text" required>
							<p class="description" id="subject-and-message-description">メールの件名や本文にユーザーのニックネームを表示したい場合は、%name%と入力することで個別のニックネームに変換します。開発者向けに送信する場合のみ、メールアドレスに変換されます。</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="message">本文</label>
						</th>
						<td>
							<textarea type="text" id="message" name="message" rows="30" cols="50" class="large-text code" required></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="submit" value="メール送信" class="button button-primary">
		</form>
	</div>
	<script src="<?php echo plugins_url() . '/mailmag/assets/js/alert.js'; ?>"></script>
	<?php
}

/**
 * メール送信履歴一覧ページ
 **/
function mailmag_sent_items() {
	global $wpdb;
	// 本文出力用に変数を用意
	$order   = array( '\r\n', '\n', '\r', "\r\n", "\n", "\r" );
	$replace = '<br />';
	// $_GETで値が渡されてなければ、一覧ページを作成(値がある場合は詳細ページを作成)
	if ( empty( $_GET['id'] ) ) {
		// mailmag_sent_messageから情報を取得しておく
		$table_name = 'mailmag_sent_message';
		$prep_sql   = 'SELECT * FROM ' . $wpdb->prefix . $table_name . ' ORDER BY ID DESC;';
     // @codingStandardsIgnoreStart
     $results = $wpdb->get_results( $prep_sql );
     // @codingStandardsIgnoreEnd
		// メール送信後のリダイレクトならフラグを立てる
		if ( isset( $_GET['sent_mail'] ) ) {
			$flag['sent_mail'] = $_GET['sent_mail'];
		}
     // @codingStandardsIgnoreStart
     $mailmag_item_count = $wpdb->get_var( $prep_sql ); // DB上でのアイテムの個数を取得
     // @codingStandardsIgnoreEnd
		$loop_count = count( $results ); // DBにある純粋なアイテムの個数を取得(0件なら0を取得する)
		if ( is_null( $mailmag_item_count ) ) { // DBにアイテムがなければ(ない場合はNULLが帰ってくる)
			$mailmag_item_count = 0; // 0を変数にセット
		}
		if ( $mailmag_item_count != $loop_count ) { // もしDBの記録とアイテムの個数に差がある場合は
			$flag['db_error'] = 1; // エラーフラグを立て
			// WP_Errorのセット
			$e = new WP_Error();
			$e->add( 'error', 'mailmag error : datebaseが不正に操作された可能性があります' );
			set_transient( 'mailmag-admin-errors', $e->get_error_messages(), 1 );
			$mailmag_item_count = $loop_count; // 純粋なアイテムの個数を使用
		}
		$user_page_url          = home_url() . '/wp-admin/user-edit.php'; // ユーザーページのURLをセット
		$mailmag_sent_items_url = home_url() . '/wp-admin/admin.php?page=mailmag-sent-items'; // 送信履歴のURLをセット
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">mailmag - メールマガジン送信履歴</h1>
			<a href="<?php echo home_url() . '/wp-admin/admin.php?page=mailmag-new'; ?>" class="page-title-action">新規作成</a>
			<hr class="wp-header-end">
			<h2 class="screen-reader-text">送信履歴の絞り込み</h2>
			<div class="table-nav top">
				<span class="displaying-num"><?php echo $mailmag_item_count; ?>個の項目</span>
				<span class="pagination-links"></span>
			</div>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-format">メールID</th>
						<th scope="col" class="manage-column column-primary">件名</th>
						<th scope="col" class="manage-column column-author author">作成者</th>
						<th scope="col" class="manage-column column-format format">送信先</th>
						<th scope="col" class="manage-column column-format format">送信成功数</th>
						<th scope="col" class="manage-column">本文抜粋</th>
						<th scope="col" class="manage-column column-date date">日付</th>
					</tr>
				</thead>
		<?php
		if ( 0 != $loop_count ) { // もしメール履歴があるなら
			echo '<tbody>';
			foreach ( $results as $result ) { // ループ開始
				?>
						<tr<?php echo 2 == $result->status ? ' class="mailmag-reservation"' : ''; ?>>
							<th scope="row">
						<?php echo $result->id; ?>
							</th>
							<td class="title column-title column-primary">
								<strong>
									<a href="<?php echo $mailmag_sent_items_url; ?>&id=<?php echo $result->id; ?>">
						 <?php echo $result->subject; ?></a>
								</strong>
								<div class="mailmag-box">
						 <?php
							$today  = strtotime( date( 'Y-m-d H:i:s' ) );
							$target = $result->send_time_timestamp;
							$status = $result->status;

							if ( is_null( $status ) ) { // 送信ステータスがnull
								// タイマー表示用のクラスをセット、文字列なし
								$span_class = ' class="mailmag-status-error"';
								$span_text  = 'DB初期保存エラー';

							} elseif ( 0 == $status ) {
								$span_class = '';
								$span_text  = '送信キャンセル済み';

							} elseif ( 1 == $status ) {
								$span_class = '';
								$span_text  = '送信済み';

							} elseif ( 2 == $status ) {
										$span_class = ' class="time-left"';
										$span_text  = '';

							} elseif ( 99 == $status ) {
								$span_class = ' class="mailmag-status-error"';
								$span_text  = '送信前エラー';

							} elseif ( 98 == $status ) {
								$span_class = ' class="mailmag-status-worn"';
								$span_text  = '送信成功数0件';
							}
							?>
									<span<?php echo $span_class; ?>><?php echo $span_text; ?></span>
									<span class="target hidden"><?php echo $result->send_time_timestamp; ?></span>
								</div>
								<button type="button" class="toggle-row"><span class="screen-reader-text">詳細を追加表示</span></button>
							</td>
							<td class="author column-author" data-colname="作成者">
								<a href="<?php echo $user_page_url . '?user_id=' . $result->author; ?>">
				<?php echo get_user_meta( $result->author, 'nickname', true ); ?>
								</a>
							</td>
							<td data-colname="送信先">
				<?php
				if ( 'dev' == $result->mail_group ) {
					$mail_group = '開発者';
				} elseif ( 'subscribers' == $result->mail_group ) {
					$mail_group = 'メールマガジン購読者';
				} elseif ( 'wpusers' == $result->mail_group ) {
					$mail_group = '全てのWPユーザー';
				}
								echo $mail_group;
				?>
							</td>
							<td data-colname="送信成功数">
						<?php echo $result->sent_count; ?>
							</td>
							<td data-colname="本文抜粋">
						<?php
						$str     = htmlspecialchars( $result->message );
						$message = str_replace( $order, $replace, $str );
						echo mb_substr( $message, 0, 70, 'utf8' );
						?>
							</td>
							<td class="send-time" data-colname="日付">
					 <?php
						if ( is_null( $result->status ) ) {
							$status = 'DB初期保存エラー';
						} elseif ( 0 == $result->status ) {
							$status = '送信キャンセル済み';
						} elseif ( 1 == $result->status ) {
							$status = '送信済み';
						} elseif ( 2 == $result->status ) {
									  $status = '送信予約中';
						} elseif ( 99 == $result->status ) {
								$status = '送信時エラー';
						} elseif ( 98 == $result->status ) {
							$status = '送信後エラー';
						}
								echo $status;
						?>
								<br>
								<abbr title="<?php echo $result->send_time; ?>"><?php echo $result->send_time; ?></abbr>
							</td>
				<?php
			} // End foreach().
			echo '</tbody>';
		}// End if().
		?>
			</table>
		</div>
		<?php
	} else {
		/**
		 * $_GETで値が帰って来てたら、詳細ページを表示
		 **/
		$id = $_GET['id'];
		// mailmag_sent_messageから情報を取得しておく
		$table_name = 'mailmag_sent_message';
		$prep_sql   = 'SELECT * FROM ' . $wpdb->prefix . $table_name . ' WHERE id=' . $id . ';';
     // @codingStandardsIgnoreStart
     $result = $wpdb->get_results( $prep_sql );
     // @codingStandardsIgnoreEnd
		// mailmag_sent_logからも情報を取得しておく
		$table_name = 'mailmag_sent_log';
		$prep_sql   = 'SELECT * FROM ' . $wpdb->prefix . $table_name . ' WHERE mail_id=' . $id . ';';
     // @codingStandardsIgnoreStart
     $result_logs = $wpdb->get_results( $prep_sql );
     // @codingStandardsIgnoreEnd

		//現在の時刻とメール送信時刻の取得
		$today  = strtotime( date( 'Y-m-d H:i:s' ) );
		$target = $result[0]->send_time_timestamp;
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">mailmag - メール詳細：件名 - <?php echo $result[0]->subject; ?></h1>
		<?php
		// 予約配信かつ未来の送信日時の時は
		if ( 2 == $result[0]->status && $today < $target ) :
			?>
			<form class="" action="<?php echo plugins_url( 'cron-cancel.php', __FILE__ ); ?>" method="post" onsubmit="return check()">
			<?php wp_nonce_field( 'cancel-mailmag_' . $result[0]->id ); ?>
				<input type="hidden" name="mail_id" value="<?php echo $result[0]->id; ?>">
				<input type="submit" class="page-title-action mailmag-page-title-action" value="送信予約の取り消し"></input>
			</form>
		<?php endif; ?>
			<hr class="wp-header-end">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content" style="position:relative;">
						<div id="mailmag-postbox" class="postbox">
							<h2 class="hndle ui-sortable-handle">メールマガジン本文</h2>
							<div class="inside">
								<div class="mailmag-flexcontainer">
									<div class="mailmag-flexbox-harf">
										<h3>送信本文</h3>
										<div class="mailmag-preview-wrapper">
											<span>
			   <?php
				if ( 'HTML' == $result[0]->format ) { // HTMLだったら
					$str = htmlspecialchars( $result[0]->message );
					echo str_replace( $order, $replace, $str );
				} else { // textだったら
					$str = $result[0]->message;
					echo str_replace( $order, $replace, $str );
				}
				?>
												</span>
										</div>
									</div>
			<?php
			if ( 'HTML' == $result[0]->format ) :
				?>
										<div class="mailmag-flexbox-harf">
											<h3>プレビュー</h3>
											<div class="mailmag-preview-wrapper">
												<span>
				<?php
				$str = $result[0]->message;
				echo str_replace( $order, $replace, $str );
				?>
												</span>
											</div>
										</div>
			<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-softabled ui-sortable">
							<div class="postbox">
								<h2 class="hndle ui-sortable-handle">送信ステータス</h2>
								<div class="inside">
									<div class="misc-pub-section">
										メールID：<?php echo $result[0]->id; ?>
									</div>
									<div class="misc-pub-section">
										送信者ID：<?php echo $result[0]->author; ?>
									</div>
									<div class="misc-pub-section">
										送信者ニックネーム：<?php echo get_user_meta( $result[0]->author, 'nickname', true ); ?>
									</div>
									<div class="misc-pub-section">
										送信日時：<?php echo $result[0]->send_time; ?>
									</div>
									<div class="misc-pub-section">
			 <?php
				if ( 'dev' == $result[0]->mail_group ) {
					$mail_group = '開発者';
				} elseif ( 'subscribers' == $result[0]->mail_group ) {
					$mail_group = 'メールマガジン購読者';
				} elseif ( 'wpusers' == $result[0]->mail_group ) {
					$mail_group = '全てのWPユーザー';
				}
				?>
										送信グループ：<?php echo $mail_group; ?>
									</div>
									<div class="misc-pub-section">
			 <?php
				if ( is_null( $result[0]->status ) ) {
					$status = 'DB初期保存エラー';
				} elseif ( 0 == $result[0]->status ) {
					$status = '送信キャンセル済み';
				} elseif ( 1 == $result[0]->status ) {
					$status = '送信済み';
				} elseif ( 2 == $result[0]->status ) {
					$status = '送信予約中';
				} elseif ( 99 == $result[0]->status ) {
					$status = '送信時エラー';
				} elseif ( 98 == $result[0]->status ) {
					$status = '送信後エラー';
				}
				?>
										送信ステータス：<?php echo $status; ?>
									</div>
									<div class="misc-pub-section">
										送信数：<?php echo '' == $result[0]->number_send ? '-' : $result[0]->number_send; ?>
									</div>
									<div class="misc-pub-section">
										送信成功：<?php echo '' == $result[0]->sent_count ? '-' : $result[0]->sent_count; ?>
									</div>
									<div class="misc-pub-section">
										送信失敗：<?php echo '' == $result[0]->error_count ? ' -' : $result[0]->error_count; ?>
									</div>
									<div class="misc-pub-section">
										送信操作日時：<?php echo $result[0]->time; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div class="postbox">
								<h2 class="hndle ui-sortable-handle">送信先詳細</h2>
								<div class="inside mailmag-scroll-box">
									<table>
										<tr>
											<th scope="col">
												UNIXタイムスタンプ
											</th>
											<th scope="col">
												日時
											</th>
											<th scope="col">
												送信結果
											</th>
											<th scope="col">
												メールアドレス
											</th>
										</tr>
			 <?php
				foreach ( $result_logs as $log ) :
					?>
										<tr>
											<td><?php echo $log->unix_timestamp; ?></td>
											<td><?php echo $log->time; ?></td>
					<?php
					if ( '1' == $log->success ) {
						echo '<td>';
						echo '成功';
						echo '</td>';
					} else {
						echo '<td class="mailmag-error-text">';
						echo '失敗';
						echo '</td>';
					}
					?>
											<td><?php echo $log->email; ?></td>
										</tr>
				<?php endforeach; ?>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script src="<?php echo plugins_url() . '/mailmag/assets/js/alert.js'; ?>"></script>
		<?php
	}// End if().
}

/**
 * ユーザー情報ページ
 **/
function mailmag_users() {
	/* 絞り込みのためにフォームからの情報取得と処理 */
	if ( ! isset( $_GET['sort'] ) ) {
		$sort = '';
	} else {
		$sort = $_GET['sort'];
	}
	$sort_all   = empty( $sort ) ? ' class="current" aria-current="page"' : '';
	$sort_admin = 'administrator' == $sort ? ' class="current" aria-current="page"' : '';
	$sort_sub   = 'subscribing' == $sort ? ' class="current" aria-current="page"' : '';
	$sort_unsub = 'unsubscribe' == $sort ? ' class="current" aria-current="page"' : '';
	// echo $sort;
	/* データベースから情報を取得しておく */
	global $wpdb;
	$users = get_users(
		array(
			'orderby' => 'user_email',
			'order'   => 'ASC',
		)
	);
	/* 件数を取得 - サイト管理者/メルマガ購読者/メルマガ購読中止 */
	$sub   = 0;
	$unsub = 0;
	$role  = 0;
	foreach ( $users as $user ) {
		if ( '1' == get_user_meta( $user->ID, 'mailmag_subscription_checkbox', true ) ) {
			$sub++; // メルマガ購読者
		} else {
			$unsub++; // メルマガ購読中止
		}
		if ( 'administrator' == $user->roles[0] ) {
			$role++; // 管理者
		}
	}
	/* ソート、リンク用にurlをセットしておく */
	$mailmag_user_list_url = home_url() . '/wp-admin/admin.php?page=mailmag-users';
	$user_page_url         = home_url() . '/wp-admin/user-edit.php';
	?>
	<div class="wrap">
		<h1>mailmag - ユーザー一覧</h1>
		<hr class="wp-header-end">
		<ul class="subsubsub">
			<!-- $_GETで受け取った情報でclassとaria-currentを指定 -->
			<li class="all"><a href="<?php echo $mailmag_user_list_url; ?>"<?php echo $sort_all; ?>>すべて: <span class="count">(<?php echo count( $users ); ?>)</span></a> |</li>
			<li class="administrator"><a href="<?php echo $mailmag_user_list_url; ?>&sort=administrator"<?php echo $sort_admin; ?>>サイト管理者 <span class="count">(<?php echo $role; ?> 名)</span></a> |</li>
			<li class="administrator"><a href="<?php echo $mailmag_user_list_url; ?>&sort=subscribing"<?php echo $sort_sub; ?>>メールマガジン購読者 <span class="count">(<?php echo $sub; ?> 名)</span></a> |</li>
			<li class="subscriber"><a href="<?php echo $mailmag_user_list_url; ?>&sort=unsubscribe"<?php echo $sort_unsub; ?>>メールマガジン購読中止者 <span class="count">(<?php echo $unsub; ?> 名)</span></a> |</li>
		</ul>
		<form method="get">
			<div class="tablenav top">
				<div class="tablenav-pages">
					<span class="displaying-num"><?php echo count( $users ); ?>個の項目</span>
				</div>
			</div>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th scope="col" class="manage-column mailmag-id-column">ID</th>
						<th scope="col" class="manage-column">WP権限グループ</th>
						<th scope="col" class="manage-column">ニックネーム</th>
						<th scope="col" class="manage-column">氏名</th>
						<th scope="col" class="manage-column">メールアドレス</th>
						<th scope="col" class="manage-column">メールマガジン登録状況</th>
					</tr>
				</thead>
				<?php if ( 0 != count( $users ) ) : // もしユーザーがいたら ?>
				<tbody>
					<?php
					foreach ( (array) $users as $user ) : // ループ開始
						/* 管理画面での絞り込み */
						if ( ! empty( $sort ) ) { // $sortが空じゃなく
							if ( 'administrator' == $sort ) { // administratorだったら
								if ( 'administrator' == $user->roles[0] ) { // rolesの値を調べ
								} else { // WordPress管理者じゃなかったら
									continue; // 処理をスキップさせる
								}
							}
							if ( 'subscribing' == $sort ) { // subscribingだったら
								if ( '1' == get_user_meta( $user->ID, 'mailmag_subscription_checkbox', true ) ) { // メルマガ登録者かどうか調べて
								} else { // メルマガ登録者じゃない場合は
									continue; // 処理をスキップさせる
								}
							}
							if ( 'unsubscribe' == $sort ) { // unsubscribeだったら
								if ( '0' == get_user_meta( $user->ID, 'mailmag_subscription_checkbox', true ) || empty( get_user_meta( $user->ID, 'mailmag_subscription_checkbox', true ) ) ) { // メルマガ登録者かどうか調べて
								} else { // メルマガ登録者は
									continue; // 処理をスキップさせる
								}
							}
						} // End if().
						// $sortが空ならそのままループ
						?>
						<tr>
							<th scope="row">
						<?php echo $user->ID; ?>
							</th>
						<?php
						if ( 'administrator' == $user->roles[0] ) {
							$user_role = '管理者';
						} elseif ( 'subscriber' == $user->roles[0] ) {
							$user_role = '購読者';
						} elseif ( 'pending' == $user->roles[0] ) {
							$user_role = '承認待ち';
						} else {
							$user_role = $user->roles[0];
						}
						?>
							<td class="manage-column<?php echo 'administrator' == $user->roles[0] ? ' mailmag-admin-style' : ''; ?>"><?php echo $user_role; ?></td>
							<td class="manage-column"><a href="<?php echo $user_page_url . '?user_id=' . $user->ID; ?>"><?php echo get_user_meta( $user->ID, 'nickname', true ); ?></a></td>
							<td class="manage-column"><?php echo get_user_meta( $user->ID, 'user_last_name', true ) . ' ' . get_user_meta( $user->ID, 'first_name', true ); ?></td>
							<td class="manage-column"><?php echo $user->user_email; ?></td>
						<?php
						if ( '1' == get_user_meta( $user->ID, 'mailmag_subscription_checkbox', true ) ) {
							$style   = ' mailmag-subscribing';
							$display = '購読中';
						} else {
							$style   = ' mailmag-unsubscribe';
							$display = '購読中止';
						}
						?>
							<td class="manage-column<?php echo $style; ?>"><?php echo $display; ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
					<?php
				endif;
?>
			</table>
		</form>
	</div>
	<?php
}

/**
 * 設定画面
 **/
function mailmag_settings() {
	include_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';
	global $wpdb;
	$table_name = 'mailmag_admin_users';
	$prep_sql   = 'SELECT id,email FROM ' . $wpdb->prefix . $table_name . ' ORDER BY id ASC;';
	// @codingStandardsIgnoreStart
	$results = $wpdb->get_results( $prep_sql );
	// @codingStandardsIgnoreEnd
	if ( 0 != count( $results ) ) {
		foreach ( $results as $result ) {
			$emails[] = $result->email;
		}
		$emails = implode( ',', $emails );
	} else {
		$emails = '';
	}
	?>
	<div class="wrap">
		<h1>mailmag - 設定</h1>
		<hr class="wp-header-end">
	<?php
	if ( ! empty( $_GET['save'] ) && 1 == $_GET['save'] ) { // 保存されたら
		?>
			<div class="updated">
				<p>設定を保存しました</p>
			</div>
		<?php
	}
	?>
		<form class="" action="<?php echo plugins_url( 'save-settings.php', __FILE__ ); ?>" method="post">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">テストメールの送信先</th>
						<td>
							<input name="mailmag_admin_emails" id="mailmag_admin_emails" class="large-text" value="<?php echo $emails; ?>">
							</input>
							<p class="description" id="tagline-description">メール送信テストを行う際にメールを受け取りたいメールアドレスのリストを作成します。複数のメールアドレスにメッセージを送信できます。その場合、複数のメールアドレスをカンマ区切りで入力してください。例えば<span class="mailmag-linear">hoge@hoge.com,fuga@fuga.com,piyo@piyo.com</span>のように入力します。</span>
						</td>
					</tr>
				</tbody>
			</table>
	<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
?>
