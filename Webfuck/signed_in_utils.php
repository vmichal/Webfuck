<?php


$account = isset($_COOKIE['account']) ? $_COOKIE['account'] : FALSE;

function is_signed_in() {
	global $account;
	return boolval($account);
}

function signed_in_user() {
	global $account;
	return $account;
}


require(__DIR__ . '/read_password.php');

function get_user_stats() {
	////debug_print_backtrace();
	if (!is_signed_in()) {
		////echo 'Nothing signed in' . PHP_EOL;
		return null;
	}
	$mysqli = new mysqli('localhost', 'root', read_mysql_password(), 'webfuck') or die("Connection failed" . __FUNCTION__);

	if ($mysqli->connect_error)
		die("Connection failed" . __FUNCTION__);

	$query_statement = <<<MYSQL
SELECT user_id AS id, instructions, errors FROM leaderboards
JOIN users ON users.id = user_id WHERE email = ?
MYSQL;

	$stats = array('id' => 0, 'instructions' => 0, 'errors' => 0);
	if ($prepared = $mysqli->prepare($query_statement) or die('Failed to prepare ' . __FUNCTION__)) {
		global $account;
		$prepared->bind_param('s', $account);
		$prepared->execute();
		$prepared->bind_result($stats['id'], $stats['instructions'], $stats['errors']);
		$prepared->fetch();
		$prepared->close();
	}
	////var_dump($stats);
	$mysqli->close();
	return $stats;
}


?>