/**
 * timeLeftjQuery
 * @since 1.0.0
 */

jQuery(function(){
	// .mailmag-boxがある毎に処理をループ
	jQuery('.mailmag-box').find('.time-left').each(function(){

		// ターゲットの時間を取得しておく
		var t = jQuery(this).parents('.mailmag-box').find('.target').text();

		// この時点の$(this)を変数に格納しておく
		var elm = jQuery(this);

		// 1秒毎に予約時間と現在時間を比較
		setInterval(function(){
			var now = new Date(); // 現在時間
			var target = new Date( t * 1000 ); // 送信予約時間
			var left = target - now; // 残り時間
			var a_day = 24 * 60 * 60 * 1000;

			// 送信予約時間の前であれば
			if ( target > now ) {

				// 期限から現在までの残り時間の'日'の部分
				var d = Math.floor(left / a_day);

				// 期限から現在までの残り時間の'時間'の部分
				var h = Math.floor((left % a_day) / (60 * 60 * 1000));

				// 残時間を秒で割って残分数を出す
				// 残分数を60で割ることで、残時間の「時」の余りとして、『残時間の分の部分』を出す
				var m = Math.floor((left % a_day) / (60 * 1000)) % 60;

				// 残時間をミリ秒で割って、残秒数を出す。
				// 残秒数を60で割った余りとして、「秒」の余りとしての残「ミリ秒」を出す。
				// 更にそれを60で割った余りとして、「分」で割った余りとしての『残時間の秒の部分』を出す
				var s = Math.floor((left % a_day) / 1000) % 60 % 60;

				// 残り日数が0より大きい場合は
				if ( 0 < d ) {
					// 日付のみ表示
					var timeLeft = '送信まで ' + d + '日';

				//残り日数が0の場合は
				} else {
					// 時間以下を表示
					var timeLeft = '送信まで ' + h + '時間' + m + '分' + s + '秒';
				}

				// 変数にセットしてあった$('this')のテキストを書き換える
				elm.text(timeLeft);
			}
		}, 1000); // 1秒毎
	});
});
