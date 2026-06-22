<?php
$data = $page->structuredData();

if (!$data) {
  return;
}
?>

  <script type="application/ld+json">
  <?= json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
  
  </script>
