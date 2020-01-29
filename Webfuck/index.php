
<?php

//define('halt', 'yes');

if (defined('halt')) {
	require(__DIR__. '/page_header.php');
	echo '<h1 align="center">Soon enough!</h1>';
}
else {
	if (isset($_REQUEST['logout'])) {
		setcookie('account', '', time()-3600);
		unset($_COOKIE['account']);
	}
	require(__DIR__ . '/advanced_page_header.php');

	require(__DIR__ . '/page_footer.php');
}
require(__DIR__ . '/page_footer.php');
?>

