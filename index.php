<?php
// index.php
require_once 'db.php';
 
function getImageOrPlaceholder($imgPath) {
    if ($imgPath && file_exists(__DIR__ . $imgPath)) {
        return htmlspecialchars($imgPath);
    }
    return "https://via.placeholder.com/400x250?text=No+Image";
}
 
// Breaking headlines (latest 5)
$breakStmt = $mysqli->prepare("SELECT id, title, slug FROM articles ORDER BY published_at DESC LIMIT 5");
$breakStmt->execute();
$breakRes = $breakStmt->get_result();
 
// Featured (one big + a few grid)
$featStmt = $mysqli->prepare("SELECT * FROM articles WHERE featured=1 ORDER BY published_at DESC LIMIT 3");
$featStmt->execute();
$featRes = $featStmt->get_result();
 
// Latest articles (paginated simple)
$latestStmt = $mysqli->prepare("SELECT a.*, c.name as category_name, c.slug as category_slug FROM articles a JOIN categories c ON a.category_id=c.id ORDER BY published_at DESC LIMIT 12");
$latestStmt->execute();
$latestRes = $latestStmt->get_result();
 
// categories
$catStmt = $mysqli->prepare("SELECT * FROM categories");
$catStmt->execute();
$catRes = $catStmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>NewsPress — Home</title>
  <style>
    :root{--accent:#c00;--muted:#666;--maxw:1100px}
    body{font-family:Arial,Helvetica,sans-serif;margin:0;color:#111;background:#f5f5f5}
    .container{max-width:var(--maxw);margin:0 auto;padding:12px}
    header{background:#fff;padding:10px 0;border-bottom:4px solid #eee;position:sticky;top:0;z-index:20}
    .brand{font-weight:700;color:var(--accent);font-size:22px}
    nav .nav-list{display:flex;gap:12px;flex-wrap:wrap;margin-top:6px}
    .breaking{background:var(--accent);color:#fff;padding:8px 12px;overflow:hidden;white-space:nowrap}
    .hero{display:flex;gap:12px;margin:12px 0}
    .hero .big{flex:2;background:#fff;padding:10px;border-radius:6px;overflow:hidden}
    .hero .side{flex:1;display:flex;flex-direction:column;gap:12px}
    .card{background:#fff;padding:10px;border-radius:6px;display:flex;gap:12px;align-items:flex-start}
    .thumb{width:140px;height:90px;background:#ddd;border-radius:4px;flex:0 0 140px;object-fit:cover}
    h1,h2,h3{margin:6px 0}
    .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
    .category-row{margin:18px 0}
    .category-title{display:flex;justify-content:space-between;align-items:center}
    .small{font-size:13px;color:var(--muted)}
    .footer{padding:18px 0;text-align:center;color:var(--muted);font-size:14px}
    @media (max-width:900px){.grid{grid-template-columns:repeat(2,1fr)}.hero{flex-direction:column}.thumb{width:120px;height:80px}}
    @media (max-width:600px){.grid{grid-template-columns:1fr}.thumb{display:none}}
    .search {margin-top:8px}
    .search input[type="text"]{padding:8px;width:70%;border:1px solid #ddd;border-radius:4px}
    .search button{padding:8px 12px;border-radius:4px;border:none;background:var(--accent);color:#fff}
    a {color:inherit;text-decoration:none}
    a:hover {text-decoration:underline}
  </style>
</head>
<body>
  <header>
    <div class="container" style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div class="brand">NewsPress</div>
        <div class="small">Breaking news &amp; in-depth reporting</div>
      </div>
      <div>
        <form class="search" action="search.php" method="get" style="display:flex;align-items:center;gap:6px;">
          <input name="q" type="text" placeholder="Search news..." />
          <button type="submit">Search</button>
        </form>
      </div>
    </div>
    <div class="breaking">
      <?php
        $sep = '';
        while($b = $breakRes->fetch_assoc()){
          echo $sep.'<a style="color:#fff;margin-right:18px" href="article.php?slug='.urlencode($b['slug']).'">'.htmlspecialchars($b['title']).'</a>';
          $sep = ' | ';
        }
      ?>
    </div>
  </header>
 
  <main class="container">
    <!-- Featured hero -->
    <section class="hero">
      <div class="big">
        <?php if($f = $featRes->fetch_assoc()): ?>
          <img src="<?php echo getImageOrPlaceholder($f['image']);?>" alt="" style="width:100%;height:300px;object-fit:cover;border-radius:6px"/>
          <h1><a href="article.php?slug=<?php echo urlencode($f['slug']);?>"><?php echo htmlspecialchars($f['title']);?></a></h1>
          <div class="small">By <?php echo htmlspecialchars($f['author']);?> — <?php echo date('M j, Y', strtotime($f['published_at'])); ?></div>
          <p class="small"><?php echo htmlspecialchars($f['summary']);?></p>
        <?php else: ?>
          <div class="card">No featured stories yet.</div>
        <?php endif; ?>
      </div>
 
      <div class="side">
        <?php while($s = $featRes->fetch_assoc()): ?>
          <div class="card" style="align-items:center">
            <img class="thumb" src="<?php echo getImageOrPlaceholder($s['image']);?>" alt=""/>
            <div>
              <h3 style="margin:0"><a href="article.php?slug=<?php echo urlencode($s['slug']);?>"><?php echo htmlspecialchars($s['title']);?></a></h3>
              <div class="small"><?php echo htmlspecialchars($s['summary']);?></div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </section>
 
    <!-- Latest articles grid -->
    <section>
      <h2>Latest</h2>
      <div class="grid">
        <?php while($row = $latestRes->fetch_assoc()): ?>
          <article class="card">
            <img class="thumb" src="<?php echo getImageOrPlaceholder($row['image']);?>" alt="">
            <div>
              <div class="small"><?php echo htmlspecialchars($row['category_name']);?> • <?php echo date('M j', strtotime($row['published_at']));?></div>
              <h3 style="margin:6px 0"><a href="article.php?slug=<?php echo urlencode($row['slug']);?>"><?php echo htmlspecialchars($row['title']);?></a></h3>
              <p class="small"><?php echo htmlspecialchars($row['summary']);?></p>
            </div>
          </article>
        <?php endwhile; ?>
      </div>
    </section>
 
    <!-- Categories -->
    <section>
      <h2>Sections</h2>
      <?php while($c = $catRes->fetch_assoc()): ?>
        <div class="category-row">
          <div class="category-title">
            <h3><?php echo htmlspecialchars($c['name']);?></h3>
            <a href="category.php?slug=<?php echo urlencode($c['slug']);?>">See all</a>
          </div>
          <?php
            $cStmt = $mysqli->prepare("SELECT id, title, slug, summary, image FROM articles WHERE category_id=? ORDER BY published_at DESC LIMIT 3");
            $cStmt->bind_param('i',$c['id']);
            $cStmt->execute();
            $cRes2 = $cStmt->get_result();
          ?>
          <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px">
            <?php while($ca = $cRes2->fetch_assoc()): ?>
              <div class="card" style="flex-direction:column">
                <img src="<?php echo getImageOrPlaceholder($ca['image']);?>" style="width:100%;height:110px;object-fit:cover;border-radius:6px" />
                <h4 style="margin:8px 0"><a href="article.php?slug=<?php echo urlencode($ca['slug']);?>"><?php echo htmlspecialchars($ca['title']);?></a></h4>
                <div class="small"><?php echo htmlspecialchars($ca['summary']);?></div>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      <?php endwhile; ?>
    </section>
 
    <div class="footer">
      &copy; <?php echo date('Y'); ?> NewsPress — Built for learning and demonstration.
    </div>
  </main>
</body>
</html>
