<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<h4>A PHP Error was encountered</h4>

<p>Severity: <?php echo htmlspecialchars((string) $severity, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', TRUE); ?></p>
<p>Message:  <?php echo htmlspecialchars((string) $message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', TRUE); ?></p>
<p>Filename: <?php echo htmlspecialchars((string) $filepath, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', TRUE); ?></p>
<p>Line Number: <?php echo (int) $line; ?></p>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>

	<p>Backtrace:</p>
	<?php foreach (debug_backtrace() as $error): ?>

		<?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0): ?>

			<p style="margin-left:10px">
			File: <?php echo htmlspecialchars((string) $error['file'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', TRUE); ?><br />
			Line: <?php echo (int) $error['line']; ?><br />
			Function: <?php echo htmlspecialchars((string) $error['function'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', TRUE); ?>
			</p>

		<?php endif ?>

	<?php endforeach ?>

<?php endif ?>

</div>
