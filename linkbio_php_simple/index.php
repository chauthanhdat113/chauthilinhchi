<?php
$config = require __DIR__.'/config.php';
$dataFile = $config['data_file'];
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];

$profile = $data['profile'] ?? ['name'=>'Your Name','avatar'=>'','description'=>'Creative Digital Nomad & Content Creator'];
$links = $data['links'] ?? [];
$posts = $data['posts'] ?? [];
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($profile['name']); ?> - LinkBio</title>
<style>
:root{
  --bg-gradient: linear-gradient(180deg, #fbeaff, #e6f0ff);
  --card-bg:#fff;
  --accent:#f78da7;
  --shadow:0 4px 20px rgba(0,0,0,0.08);
  --radius:20px;
  --text:#222;
}
*{box-sizing:border-box}
body{
  font-family:'Inter',system-ui,Segoe UI,Roboto,'Helvetica Neue',Arial;
  background:var(--bg-gradient);
  color:var(--text);
  margin:0;
  display:flex;
  justify-content:center;
  padding:40px 16px;
}
.container{max-width:480px;width:100%;text-align:center;}
.avatar{
  width:110px;height:110px;border-radius:50%;
  object-fit:cover;border:4px solid rgba(255,255,255,0.9);
  box-shadow:var(--shadow);background:#fff;
}
h1{margin:16px 0 4px;font-size:22px;font-weight:700}
.desc{color:#555;margin-bottom:20px;font-size:15px;line-height:1.4}
.section{
  background:var(--card-bg);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding:20px;
  margin-bottom:24px;
}
.section-title {
    font-size: 1.5em;
    color: #d16ba5;
    text-align: center;
    margin-bottom: 15px;
    font-weight: bold;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}
.link-btn {
    display: block;
    width: 90%;
    max-width: 300px;
    margin: 10px auto;
    padding: 12px 20px;
    border-radius: 25px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1em;
    transition: all 0.3s ease;
    background-color: #fbc7d4;
    color: #8c0c45;
    border: 1px solid #f8a1bc;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.link-btn:hover {
    background-color: #f7a9bf;
    transform: translateY(-2px);
    box-shadow: 0 6px 10px rgba(0,0,0,0.15);
}
.posts{
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
  gap:15px;
}
.post-card{
  background:#fff;
  border-radius:12px;
  overflow:hidden;
  box-shadow:var(--shadow);
  text-align:left;
  transition:transform .2s ease;
}
.post-card:hover{transform:translateY(-3px)}
.post-card img {
  width: 100%;
  height: 200px;
  object-fit: contain;
  background: #fff;
}

.post-body{padding:10px 12px}
.post-title{font-weight:600;font-size:14px;margin:0 0 6px;color:#222}
.post-desc{font-size:13px;color:#666;margin:0}
.footer{
  text-align:center;
  font-size:13px;
  color:#999;
  margin-top:30px;
}
</style>
</head>
<body>
  <div class="container">
    <?php if(!empty($profile['avatar']) && file_exists($profile['avatar'])): ?>
      <img src="<?php echo htmlspecialchars($profile['avatar']); ?>" class="avatar" alt="Avatar">
    <?php else: ?>
      <div style="width:110px;height:110px;border-radius:50%;background:#ddd;display:inline-block"></div>
    <?php endif; ?>

    <h1><?php echo htmlspecialchars($profile['name']); ?></h1>
    <p class="desc"><?php echo nl2br(htmlspecialchars($profile['description'])); ?></p>

    <div class="section">
      <div class="section-title">🔗 Liên Hệ</div>
      <?php foreach($links as $link): if(empty($link['active'])) continue; ?>
        <a class="link-btn" href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank"><?php echo htmlspecialchars($link['title']); ?></a>
      <?php endforeach; ?>
    </div>

    <?php if(!empty($posts)): ?>
    <div class="section">
      <div class="section-title">📰 Sản Phẩm</div>
      <div class="posts">
        <?php foreach($posts as $p): ?>
          <a href="<?php echo htmlspecialchars($p['url']); ?>" target="_blank" class="post-card">
            <?php if(!empty($p['image']) && file_exists($p['image'])): ?>
              <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="">
            <?php else: ?>
              <div style="width:100%;height:120px;background:#eee;text-align:center;line-height:120px;color:#999;">No image</div>
            <?php endif; ?>
            <div class="post-body">
              <div class="post-title"><?php echo htmlspecialchars($p['title']); ?></div>
              <div class="post-desc"><?php echo htmlspecialchars($p['desc']); ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="footer">@<?php echo htmlspecialchars($profile['username'] ?? 'yourname'); ?></div>
  </div>
</body>
</html>
