<?php
/**
 * footer.php
 * ---------------------------------------------------------------------
 * Closes out what header.php opened (.ps-shell, <body>, <html>) and
 * loads the shared JS file at the very end of the page -- scripts go
 * last so the DOM they touch (sidebar links, etc.) already exists by
 * the time main.js runs, no DOMContentLoaded wrapper needed.
 * ---------------------------------------------------------------------
 */
?>
</div><!-- /.ps-shell -->
<script src="assets/js/main.js"></script>
</body>
</html>
