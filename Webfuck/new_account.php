<?php require(__DIR__ . '/page_header.php');

	  $invalid_email = FALSE;
	  $connection_failed = FALSE;
	  $user_exists = FALSE;
	  $password_mismatch = FALSE;
	  $invalid_password = FALSE;

	  function validate_email() : bool {
		  $email = $_REQUEST['user_email'];

		  $email_ok = preg_match_all('/^\S+?@\S+?\.\S+?$/', $email) === 1;

		  if (!$email_ok) {
			  global $invalid_email;
			  $invalid_email = true;
		  }
		  return $email_ok;
	  }

	  function validate_password() {
		  $password = $_REQUEST['user_password'];
		  $again = $_REQUEST['password_again'];

		  if (strlen($password) < 8 || preg_match('/^[^0-9]*$/', $password)) {
			  global $invalid_password;
			  $invalid_password = true;
			  return false;
		  }

		  if ($password !== $again) {
			  global $password_mismatch;
			  $password_mismatch = true;
			  return false;
		  }
		  return true;
	  }

	  require(__DIR__ . '/read_password.php');

	  function handle_new_user() {
		  if (!validate_email() || !validate_password())
			  return;
		  $connection = new mysqli('localhost', 'root', read_mysql_password(), 'webfuck');

		  global $connection_failed;
		  if ($connection->connect_error) {
			  ////echo $connection->connect_error . PHP_EOL;
			  $connection_failed = TRUE;
			  return;
		  }

		  if ($observer_statement = $connection->prepare("SELECT id FROM users WHERE email = ?")) {
			  $observer_statement->bind_param('s', $_REQUEST['user_email']);
			  $observer_statement->execute();
			  $observer_statement->bind_result($password_hash);
			  $observer_statement->fetch();


			  if (is_null($password_hash)) { //no similar email,
				  if ($insert_statement = $connection->prepare("INSERT INTO users(email, password_hash) VALUES (?, ?)")) {
					  $hash = password_hash($_REQUEST['user_password'], PASSWORD_DEFAULT);
            $insert_statement->bind_param('ss', $_REQUEST['user_email'], $hash);
            $insert_statement->execute();
					  $insert_statement->close();
					  $query_statement = <<<MYSQL
INSERT INTO leaderboards(user_id, instructions, errors) VALUES(
(SELECT id FROM users WHERE email = ?)
,0,0)
MYSQL;
					  if ($insert_statement = $connection->prepare($query_statement)) {
						  $insert_statement->bind_param('s', $_REQUEST['user_email']);
						  $insert_statement->execute();
						  $insert_statement->close();

					  }
					  else {
						  ////echo $observer_statement->error . PHP_EOL;
						  $connection_failed = TRUE;
					  }
				  }
				  else {
					  ////echo $observer_statement->error . PHP_EOL;
					  $connection_failed = TRUE;
				  }
			  }
			  else {
				  global $user_exists;
				  $user_exists = true;
			  }
			  $observer_statement->close();
		  }
		  else {
			  ////echo $observer_statement->error . PHP_EOL;
			  $connection_failed = TRUE;
		  }
		  $connection->close();
	  }

	  function print_results() {
		  global $invalid_email;
		  global $connection_failed;
		  global $user_exists;
		  global $password_mismatch;
		  global $invalid_password;

		  $result = '';

		  $result .= $invalid_email ? '<p>Invalid email format!</p>' : '';
		  $result .= $connection_failed ? '<p>Connection to DB failed.</p>' : '';
		  $result .= $user_exists ? '<p>This email address already exists!</p>' : '';
		  $result .= $password_mismatch ? '<p>Passwords do differ.</p>' : '';
		  $result .= $invalid_password ? '<p>Password does not have correct format. Digit may be missing or it\'s less than 8 chars long.</p>' : '';

		  return $result;
	  }




	  $form_submitted = isset($_REQUEST['form_submitted']) ? $_REQUEST['form_submitted'] : die('Missing argument');
	  $dummy_email = 'me.example@server.com';

	  if ($form_submitted) {
		  handle_new_user();
		  $dummy_email = $_REQUEST['user_email'];
	  }

	  $errors = print_results();
	  $show_form = !$form_submitted || $errors;
?>

<h1 align="center">Create new account</h1>
<div align="center">
	<?= $errors ?>

</div>
<table align="center" width="50%" border="1">
	<tr>
		<td>
			<?php if ($show_form): ?>

			<form action="new_account.php" method="post" align="center">
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
				<label>Password again</label>
				<br />
				<input type="password" name="password_again" />
				<br />
				<br />
				<input type="submit" value="Proceed" />
			</form>

			<?php
				  else:
					  echo 'New account ' . $_REQUEST['user_email'] . ' created. You may <a href="login_page.php">log in</a> now.' . PHP_EOL;
				  endif;
            ?>
		</td>
	</tr>
</table>



<?php require(__DIR__ . '/page_footer.php'); ?>