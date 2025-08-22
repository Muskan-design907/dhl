<?php
// category.php
require_once 'db.php';
 
function getImageOrPlaceholder($imgPath) {
    if ($imgPath && file_exists(__DIR__ . $imgPath)) {
        return htmlspecialchars($imgPath);
    }
    return "https://via.placeholder.com/160x100?text=No+Image";
}
 
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$catStmt = $mysqli->prepare("SELECT * FROM categories WHERE slug = ?");
$catStmt->bind_param('s', $slug);
$catStmt->execute();
$cat = $catStmt->get_result()->fetch_assoc();
if (!$cat) { header("HTTP/1.0 404 Not Found"); echo "Category not found"; exit; }
 
$artStmt = $mysqli->prepare("SELECT * FROM articles WHERE category_id = ? ORDER BY published_at DESC");
$artStmt->bind_param('i', $cat['id']);
$artStmt->execute();
$arts = $artStmt->get_result();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title><?php echo htmlspecialchars($cat['name']);?> — NewsPress</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    body{font-family:Arial;margin:0;background:#f8f8f8;color:#222;padding:12px}
    .container{max-width:1000px;margin:0 auto}
    .card{background:#fff;padding:12px;margin-bottom:12px;border-radius:6px;display:flex;gap:12px}
    .thumb{width:160px;height:100px;object-fit:cover;border-radius:4px}
    .breadcrumbs{margin-bottom:8px;color:#666}
    .small{color:#666;font-size:13px}
    a{color:inherit}
  </style>
</head>
<body>
  <div class="container">
    <div class="breadcrumbs"><a href="index.php">Home</a> › <?php echo htmlspecialchars($cat['name']);?></div>
    <h1><?php echo htmlspecialchars($cat['name']);?></h1>
    <?php while($a = $arts->fetch_assoc()): ?>
      <article class="card">
        <img class="thumb" src="<?php echo getImageOrPlaceholder($a['image']);?>" alt=""/>
        <div>
          <div class="small"><?php echo date('M j, Y', strtotime($a['published_at']));?> • <?php echo htmlspecialchars($a['author']);?></div>
          <h2><a href="article.php?slug=<?php echo urlencode($a['slug']);?>"><?php echo htmlspecialchars($a['title']);?></a></h2>
          <p class="small"><?php echo htmlspecialchars($a['summary']);?></p>
        </div>
      </article>
    <?php endwhile; ?>
  </div>
</body>
</html>
 
