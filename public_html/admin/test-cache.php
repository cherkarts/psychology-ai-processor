<?php
// Тест кэширования
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>

<head>
  <title>Cache Test</title>
</head>

<body>
  <h1>Cache Test v<?php echo time(); ?></h1>
  <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
  <p>If you see the same time after refresh, cache is working.</p>
  <p>If you see different time, cache is disabled.</p>

  <script>
    console.log('Cache test script loaded at:', new Date().toISOString());
  </script>
</body>

</html>