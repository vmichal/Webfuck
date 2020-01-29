<?php
require(__DIR__ . '/advanced_page_header.php');

$stats = get_user_stats();

?>


<h2 align="center">Your records:</h2>
<table width="50%" border="1" align="center">
	<tr>
		<td align="center" valign="middle">
			Compiled instructions:<br />
			<?= $stats['instructions']?>
		</td>
		<td align="center" valign="middle">
			Syntax errors: <br />
			<?= $stats['errors']?>
		</td>
	</tr>
</table>




<?php
require(__DIR__ . '/page_footer.php');

?>