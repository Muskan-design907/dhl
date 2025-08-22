<?php
// article.php
require_once 'db.php';
 
function getImageOrPlaceholder($imgPath) {
    if ($imgPath && file_exists(__DIR__ . $imgPath)) {
        return htmlspecialchars($imgPath);
    }
    return "https://via.placeholder.com/900x360?text=No+Image";
}
 
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
 
// fetch article
$stmt = $mysqli->prepare("SELECT a.*, c.name as category_name, c.slug as category_slug FROM articles a JOIN categories c ON a.category_id=c.id WHERE a.slug = ?");
$stmt->bind_param('s', $slug);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();
if (!$article) { header("HTTP/1.0 404 Not Found"); echo "Article not found"; exit; }
 
// increment views (best-effort)
$inc = $mysqli->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
$inc->bind_param('i', $article['id']);
$inc->execute();
 
// fetch comments
$cstmt = $mysqli->prepare("SELECT * FROM comments WHERE article_id = ? ORDER BY created_at DESC");
$cstmt->bind_param('i', $article['id']);
$cstmt->execute();
$comments = $cstmt->get_result();
 
// related articles
$rstmt = $mysqli->prepare("SELECT id, title, slug FROM articles WHERE category_id = ? AND id <> ? ORDER BY published_at DESC LIMIT 4");
$rstmt->bind_param('ii', $article['category_id'], $article['id']);
$rstmt->execute();
$related = $rstmt->get_result();
 
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title><?php echo htmlspecialchars($article['title']);?> — NewsPress</title>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    body{font-family:Arial;margin:0;background:#fff;color:#111}
    .container{max-width:900px;margin:12px auto;padding:12px}
    .breadcrumbs{color:#666;margin-bottom:6px}
    .hero img{width:100%;height:360px;object-fit:cover;border-radius:6px}
    .meta{color:#666;font-size:14px;margin-bottom:12px}
    .content{line-height:1.8}
    .comments{margin-top:22px}
    .comment{border-top:1px solid #eee;padding:10px 0}
    form input, form textarea{width:100%;padding:8px;margin-bottom:8px;border:1px solid #ddd;border-radius:4px}
    form button{padding:8px 12px;border:none;background:#c00;color:#fff;border-radius:4px}
    aside{background:#f7f7f7;padding:12px;border-radius:6px;margin-top:12px}
    .related li{margin-bottom:6px}
  </style>
</head>
<body>
  <div class="container">
    <div class="breadcrumbs"><a href="index.php">Home</a> › <a href="category.php?slug=<?php echo urlencode($article['category_slug']);?>"><?php echo htmlspecialchars($article['category_name']);?></a></div>
    <h1><?php echo htmlspecialchars($article['title']);?></h1>
    <div class="meta">By <?php echo htmlspecialchars($article['author']);?> • <?php echo date('F j, Y g:ia', strtotime($article['published_at']));?> • Views: <?php echo intval($article['views']); ?></div>
 
    <div class="hero">
      <img src="<?php echo getImageOrPlaceholder($article['image']); ?>" alt="">
    </div>
 
    <div class="content"><?php echo $article['content']; ?></div>
 
    <aside>
      <h3>Related</h3>
      <ul class="related">
        <?php while($r = $related->fetch_assoc()): ?>
          <li><a href="article.php?slug=<?php echo urlencode($r['slug']);?>"><?php echo htmlspecialchars($r['title']);?></a></li>
        <?php endwhile; ?>
      </ul>
    </aside>
 
    <section class="comments" id="comments">
      <h3>Comments</h3>
      <?php if($comments->num_rows == 0): ?>
        <p class="small">No comments yet. Be the first to comment.</p>
      <?php else: ?>
        <?php while($c = $comments->fetch_assoc()): ?>
          <div class="comment">
            <div style="font-weight:600"><?php echo htmlspecialchars($c['name']);?></div>
            <div class="small"><?php echo date('M j, Y g:ia', strtotime($c['created_at']));?></div>
            <div><?php echo nl2br(htmlspecialchars($c['comment']));?></div>
          </div>
        <?php endwhile; ?>
      <?php endif; ?>
 
      <h4>Leave a comment</h4>
      <form method="post" action="post_comment.php">
        <input type="hidden" name="article_id" value="<?php echo intval($article['id']); ?>"/>
        <input name="name" placeholder="Your name" required/>
        <textarea name="comment" placeholder="Your comment" rows="4" required></textarea>
        <button type="submit">Post comment</button>
      </form>
    </section>
  </div>
</body>
</html>
 
