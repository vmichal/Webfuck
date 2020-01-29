<?php
require(__DIR__ . '/page_header.php');

require(__DIR__ . '/signed_in_utils.php');


?>



<table>
	<tr>
		<td>
			<h1>Brainfuck explorer</h1>
			<table>
				<tr>
					<td>
						<form action="syntax_validation.php" method="post">
							<input type="submit" value="Syntax validation" />
						</form>
						
					</td>
					
				</tr>
			</table>
			
		</td>
		<td style="width:10%">
			<table border="1">
				<tr>
					<td align="center">
						<?php if (!is_signed_in()): //User can log in ?>
						<form action="login_page.php" method="post">
							<input type="hidden" name="form_submitted" value="0" />
							<input type="submit" value="Log in" />
						</form>
						<form action="new_account.php" method="post">
							<input type="hidden" name="form_submitted" value="0" />
							<input type="submit" value="Create new account" />
						</form>
						<?php else: ?>
						<p>
							Logged&nbspin&nbspas <br /><b><?= signed_in_user() ?></b>
						</p>
						<form action="index.php" method="post">
							<input type="submit" name="logout" value="Log out" />
						</form>
						<form action="user_highscore.php" method="post">
							<input type="submit" value="Records" />
						</form>
						<?php endif; ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>