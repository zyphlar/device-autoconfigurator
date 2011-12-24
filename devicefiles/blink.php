<?php
// Device blink.php code, goes in /var/www/html
// You may need to disable selinux in order for this to work (system calls)
//   try setenforce 0

include("/etc/led-control.inc.php");

identify_unit();
?>
<html><head>
</head><body>
  <a href="#" onclick="location.reload(); return false;">Blink Again</a>
</body></html>
