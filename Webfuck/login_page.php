<?php require(__DIR__ . '/page_header.php');
	  require(__DIR__ . '/read_password.php');

	  $connection_failed = FALSE;
	  $password_ok = FALSE;
	  $unknown_user = FALSE;


	  function print_errors() {
		  global $connection_failed;
		  global $password_ok;
		  global $unknown_user;

		  $result = '';
		  if ($connection_failed)
			  $result .='<p>Connection to server failed!</p>';
		  if ($unknown_user)
			  $result .= '<p>Unknown user!</p>';
		  elseif (!$password_ok)
			  $result .= '<p>Invalid combination of username and password.</p>';
		  return $result;
	  }


	  function check_password() {
		  $connection = new mysqli('localhost', 'root', read_mysql_password(), 'webfuck');

		  global $connection_failed;
		  if (!isset($connection) || $connection->connect_error) {
			  ////echo $connection->connect_error . PHP_EOL;
			  $connection_failed = TRUE;
			  return false;
		  }
		  $result = FALSE;
		  if ($prep_statement = $connection->prepare("SELECT password_hash FROM users WHERE email = ?")) {
			  $prep_statement->bind_param('s', $_REQUEST['user_email']);
			  $prep_statement->execute();
			  $prep_statement->bind_result($password_hash);
			  $prep_statement->fetch();

			  if (!is_null($password_hash))
				  $result = password_verify($_REQUEST['user_password'], $password_hash);
			  else {
				  global $unknown_user;
				  $unknown_user = true;
			  }
			  $prep_statement->close();
		  }
		  else {
			  ////echo $prep_statement->error . PHP_EOL;
			  $connection_failed = TRUE;
		  }
		  $connection->close();
		  return $result;
	  }

	  function handle_login() {
		  if (check_password()) {
			  global $password_ok;
			  $password_ok = TRUE;
			  setcookie('account', $_REQUEST['user_email'], time()+1800) or die('UNABLE to set cookie');
		  }
	  }



	  $form_submitted = isset($_REQUEST['form_submitted']) ? $_REQUEST['form_submitted'] : FALSE; //may be missing when redirecting from new_account

	  if ($form_submitted)
		  handle_login();

	  $show_form = !$form_submitted || $unknown_user || !$password_ok;

	  $dummy_email = $form_submitted ? $_REQUEST['user_email'] : 'me.example@server.com';

?>

<h1 align="center">Type in your credentials</h1>
<div align="center">
	<?= $form_submitted ? print_errors() : ''; ?>
</div>
<table align="center" width="50%" border="1">
	<tr>
		<td>

			<?php if ($show_form): ?>

			<form action="login_page.php" method="post" align="center">
				<input type="hidden" name="form_submitted" value="true" />
				<br />
				<label>Email</label>
				<br />
				<input type="text" name="user_email" value="<?= $dummy_email; ?>" />
				<br />
				<br />
				<label>Password</label>
				<br />
				<input type="password" name="user_password" />
				<br />
				<br />


				<input type="submit" value="Proceed" />
			</form>

			<?php  else: ?>

					You have been successfully logged in as <?=$_REQUEST['user_email'];?>
			<a href="index.php">Go back to the main page.</a>
			<?php endif; ?>
		</td>
	</tr>
</table>


<?php require(__DIR__ .'/page_footer.php');?>