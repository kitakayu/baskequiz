<?php

//セッションで管理すれば、その値が保持される
session_start();

function h($s){
	return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$quizList = array(
	array(
		'q' => 'レブロンなに？',
		'a' => array('ジェームズ', 'アレン', 'ポップ')
	),
	array(
		'q' => 'ヒートの本拠地は？',
		'a' => array('マイアミ', 'ロス', 'ニューヨーク')
	),
	array(
		'q' => 'バスケの神様と言えば？',
		'a' => array('ジョーダン', 'レイ・アレン', 'アレンアイバーソン', 'となりのおっちゃん')
	)
);

function resetSession() {
	$_SESSION['correct_count'] =0;
	$_SESSION['num'] = 0;
	//推測されにくい文字列をつくって、フォームから投稿させて、そのあと調べる
	$_SESSION['token'] = sha1(uniqid(mt_rand(), true));

}

function redirect() {
	header('Location: http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	exit;
}

//正誤判定のため、POSTされた（ボタンおされたら）ときの処理
if($_SERVER['REQUEST_METHOD'] === 'POST'){
	//CSRF対策 得られたトークンを調べる
	//もしポストのトークンの中のvalueの値が改ざんされて送られてきた場合不正だと述べ、exitする。
	if ($_POST['token'] !== $_SESSION['token']) {
		echo "不正な投稿です！1";
		exit;
	}

	if(isset($_POST['reset']) && $_POST['reset'] ==='1'){
		resetSession();
		redirect();
	}
	//issetはそこに値がセットしてあるかどうか
	//もしくはqnumがセットされていない、もしくは、　セッションとポストのqnumが異なっている場合
	if (!isset($_POST['qnum']) || $_SESSION['qnum'] !== $_POST['qnum']){
		echo "不正な投稿です!2";
		exit;

	}

	if (!isset($quizList[$_POST['qnum']])){
		echo "不正な投稿３";
		exit;
	}

	if ($_POST['answer'] === $quizList[$_POST['qnum']]['a'][0]){
		//echo "正解です。";
		//exit;
		//正解したときだけコレクトカウントが1ずつ増える
		$_SESSION['correct_count']++;
	}
	//解答がPOSTされたときなので、問題数であるnumを増やす
	$_SESSION['num']++;
	redirect();
}

//条件：セッションの中身がないとき
if (empty($_SESSION)){
	resetSession();
}

$qnum = mt_rand(0, count($quizList) -1);
$quiz = $quizList[$qnum];

$_SESSION['$num'] = (string)$qnum;

shuffle($quiz['a']);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<title>バスケクイズ</title>
</head>
<body>
	<div style="padding:7px; background:#eee;border:#ccc;">
		<?php echo h($_SESSION['num']); ?>問中
		<?php echo h($_SESSION['correct_count']); ?>問正解！
		<?php if ($_SESSION['num'] > 0 ) :?>
		正答率は<?php echo h(sprintf("%.2f" , $_SESSION['correct_count']/$_SESSION['num']* 100)); ?>％です。
		<?php endif; ?>
	</div>
	<p>Question. <?php echo h($quiz['q']); ?></p>
	<?php foreach ($quiz['a'] as $answer) :?>
	<form action="" method="POST">
		<input type="submit" name="answer" value="<?php echo h($answer); ?>">
		<input type="hidden" name="qnum" value="<?php echo h($_SESSION['qnum']); ?>">
		<!--すでにトークンに推測されにくい文字列が入っているので
		ここで送信させるそのあとPOSTされたあとの上のif文の中で調べる処理をする
		-->
		<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
	</form>
	<?php endforeach; ?>

	<hr>
	<form action="" method="POST">
		<input type="submit" name="answer" value="リセット">
		<input type="hidden" name="reset" value="1">
		<input type="hidden" name="token" value="<?php echo h($_SESSION['token']); ?>">
	</form>
</body>
</html>
