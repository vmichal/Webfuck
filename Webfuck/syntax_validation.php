<?php require(__DIR__ . '/advanced_page_header.php'); ?>

<?php

require(__DIR__ . '/bf_pipe.php');

$compilation_successful = null;

function compilation_summary() {
	global $compilation_successful;

	if (is_null($compilation_successful))
		return 'CPU ready.';
	is_bool($compilation_successful) or die("Sanity check; compilation_successful must be bool at this point.");

	return $compilation_successful ? 'Compilation OK' : 'Compilation failed';
}

function adjust_highest_value($emulator_output, $success) {
	if (!is_signed_in())
		return $emulator_output;

	////var_dump($emulator_output);
	preg_match('/[0-9]+/', $emulator_output, $matches);
	////var_dump($matches);
	$value = intval($matches[0]);
	////echo 'VALUE';
	////var_dump($value);

	$stats = get_user_stats() or die("Null returned! " . __FUNCTION__);
	////var_dump($stats);

	if (intval($stats[$success ? 'instructions' : 'errors']) < $value) { //update highest score
		////echo 'IF OK' . PHP_EOL;
		$mysqli = new mysqli('localhost', 'root', read_mysql_password(), 'webfuck');

		if (!isset($mysqli) || $mysqli->connect_error)
			return $emulator_output . " That would be a new record if the database was working.\n"; //we try to set highscore only if it works...
		$prepared = $mysqli->prepare('UPDATE leaderboards SET ' . ($success ? 'instructions' : 'errors') .' = ? WHERE user_id = ?');
		$prepared->bind_param('ii', $value, intval($stats['id']));
		$prepared->execute();
		$prepared->close();
		$mysqli->close();
		$emulator_output .= " That is a new record!\n";
	}
	////echo 'IF ended' . PHP_EOL;
	return $emulator_output;

}

function parse_error_message(&$message, $key) {
		////var_dump($message);

		$message = preg_replace('/\{/', '', $message);

		////var_dump($message);
	}

function process_errors($emulator_output, $emulator) {
	global $compilation_successful;
	$compilation_successful = false;

	$emulator_output = adjust_highest_value($emulator_output, false);

	$emulator->execute_command('errors full');

	$errors = preg_split('/\}/', $emulator->read_output());
	////var_dump($errors);
	while ($errors && !preg_match('/\(b\-fuck\)/',array_pop($errors)));
	////var_dump($errors);

	array_walk($errors, 'parse_error_message');
	////var_dump($errors);


	return $emulator_output . PHP_EOL . implode('', $errors);
}

function process_success($emulator_output) {
	global $compilation_successful;
	$compilation_successful = true;
	return adjust_highest_value($emulator_output, true);
}

function validate_syntax($source_code) {
	$tmpfile_name = __DIR__ . uniqid('/emulator/bf-tmpfile');
	file_put_contents($tmpfile_name, $source_code);

	$emulator = new bf_pipe();
	$emulator->clear_pipes();
	$emulator->execute_command('compile file "' . $tmpfile_name . '"');

	$response = explode("\n",$emulator->read_output());
	unlink($tmpfile_name);

	$output = '';
	while (true) {
		$line = array_shift($response);
		if (preg_match('/Found [0-9]+? errors?\./', $line, $matches)) {
			$output = process_errors($matches[0], $emulator);
			break;
		}
		elseif (preg_match('/Successfully compiled [0-9]+? instructions?\./', $line, $matches)) {
			$output = process_success($matches[0]);
			break;
		}
	}
	$emulator->close();
	return $output ? $output : die('Array empty!');
}

function read_emulator_data() {

	$emulator = new bf_pipe();

	$valuable_data = array();
	foreach (explode("\n",  $emulator->read_output()) as $line)
		foreach (['Version', 'Compiled', 'bit mode', 'address space'] as $searched_term)
			if (preg_match('/'.$searched_term.'/i', $line))
				$valuable_data[$searched_term] = $line;
	$emulator->close();

	return implode("\n", $valuable_data);
}


$form_submitted = isset($_REQUEST['form_submitted']) ? $_REQUEST['form_submitted'] : FALSE;

$source_code = 'Type your Brainfuck code here.';
$output = '';
if ($form_submitted) {
	$source_code = $_REQUEST['source_code'];
	$output = validate_syntax($source_code);
}
else
	$output = read_emulator_data();

?>



<link rel="stylesheet" href="styles.css" type="text/css" />

<p>Source code:</p>
<form action="syntax_validation.php" method="post">
	<div class="container">
		<textarea class="text" rows="20" name="source_code"><?=$source_code ?></textarea>
	</div>
	<input type="hidden" name="form_submitted" value="1" />
	<br />
	<div align="center">
		<input type="submit" value="Check syntax" />
	</div>
</form>
<p>Output: <?= compilation_summary()?></p>
<div class="container">
	<textarea class="text" readonly="readonly" rows="10"><?=$output?></textarea>
</div>



<?php require(__DIR__ . '/page_footer.php');?>