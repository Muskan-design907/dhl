<?php
// search.php
require_once 'db.php';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
if ($q !== '') {
    // basic fulltext-like search using LIKE for simplicity
    $like = "%".$q."%";
    $s = $mysqli->prepare("SELECT a.*, c.name as category_name FROM articles a JOIN categories c ON a.category_id=c.id WHERE a.title LIKE ? OR a.summary LIKE ? OR a.content LIKE ? ORDER BY published_at DESC");
    $s->bind_param('sss',$like,$like,$like);
    $s->execute();
    $results = $s->get_result();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Search results for "<?php echo htmlspecialchars($q);?>" — NewsPress</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    body{font-family:Arial;background:#f8f8f8;padding:12px}
    .container{max-width:900px;margin:0 auto}
    .card{background:#fff;padding:12px;margin-bottom:12px;border-radius:6px}
    .small{color:#666}
  </style>
</head>
<body>
  <div class="container">
    <h1>Search results for "<?php echo htmlspecialchars($q);?>"</h1>
    <form method="get" action="search.php" style="margin-bottom:12px">
      <input type="text" name="q" value="<?php echo htmlspecialchars($q);?>" style="padding:8px;width:70%" />
      <button type="submit" style="padding:8px">Search</button>
    </form>
 
    <?php if($q === ''): ?>
      <p>Type a keyword to search articles.</p>
    <?php else: ?>
      <?php if ($results && $results->num_rows > 0): ?>
        <?php while($r = $results->fetch_assoc()): ?>
          <div class="card">
            <div class="small"><?php echo htmlspecialchars($r['category_name']);?> • <?php echo date('M j, Y', strtotime($r['published_at']));?></div>
            <h3><a href="article.php?slug=<?php echo urlencode($r['slug']);?>"><?php echo htmlspecialchars($r['title']);?></a></h3>
            <p class="small"><?php echo htmlspecialchars($r['summary']);?></p>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p>No results found for "<?php echo htmlspecialchars($q); ?>".</p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</body>
</html>
 
