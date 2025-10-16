<?php
session_start();
$config = require __DIR__.'/config.php';
$dataFile = $config['data_file'];
if (!file_exists($dataFile)) file_put_contents($dataFile, json_encode(['profile'=>[], 'links'=>[], 'posts'=>[]]));
$data = json_decode(file_get_contents($dataFile), true);

if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['user'] === $config['admin_user'] && $_POST['pass'] === $config['admin_pass']) {
            $_SESSION['logged_in'] = true;
            header("Location: admin.php");
            exit;
        } else $error = "Sai tài khoản hoặc mật khẩu!";
    }
    ?>
    <!doctype html><html><head><meta charset="utf-8"><title>Admin Login</title>
    <style>
    body{font-family:sans-serif;background:#f0f2f5;display:flex;justify-content:center;align-items:center;height:100vh}
    form{background:#fff;padding:30px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,.1)}
    input{display:block;margin-bottom:10px;padding:10px;width:220px}
    button{padding:10px 20px;background:#4f46e5;color:#fff;border:none;border-radius:6px;cursor:pointer}
    </style></head><body>
    <form method="post">
        <h3>Đăng nhập Admin</h3>
        <input name="user" placeholder="Username">
        <input name="pass" type="password" placeholder="Password">
        <button>Đăng nhập</button>
        <?php if(!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
    </form>
    </body></html>
    <?php
    exit;
}

function saveData($dataFile, $data){
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
}

// xử lý logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Xử lý upload ảnh đại diện (avatar)
function handleAvatarUpload($uploadDir) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $name = 'avatar_' . uniqid() . "." . $ext;
        $dest = $uploadDir . '/' . $name;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
            return 'uploads/' . $name; // Trả về đường dẫn web
        }
    }
    return null;
}

// Xử lý upload ảnh bài viết
function handlePostImageUpload($uploadDir) {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $name = 'post_' . uniqid() . "." . $ext;
        $dest = $uploadDir . '/' . $name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            return 'uploads/' . $name; // Trả về đường dẫn web
        }
    }
    return null;
}

// xử lý cập nhật profile
if (isset($_POST['action']) && $_POST['action'] === 'save_profile') {
    $data['profile']['name'] = $_POST['name'];
    $data['profile']['description'] = $_POST['description'];
    $data['profile']['username'] = $_POST['username'];
    if($newAvatar = handleAvatarUpload($config['upload_dir'])) $data['profile']['avatar'] = $newAvatar;
    saveData($dataFile, $data);
    header("Location: admin.php?tab=profile");
    exit;
}


// xử lý link
if(isset($_POST['action']) && $_POST['action']==='save_link'){
    $i = $_POST['id'] ?? '';
    $link = ['title'=>$_POST['title'],'url'=>$_POST['url'],'active'=>!empty($_POST['active'])];
    if($i==='') $data['links'][]=$link; else $data['links'][$i]=$link;
    saveData($dataFile, $data);
    header("Location: admin.php?tab=links");
    exit;
}
if(isset($_GET['del_link'])){
    array_splice($data['links'], $_GET['del_link'], 1);
    saveData($dataFile, $data);
    header("Location: admin.php?tab=links");
    exit;
}

// xử lý bài viết
if(isset($_POST['action']) && $_POST['action']==='save_post'){
    $i = $_POST['id'] ?? '';
    $post = [
        'title'=>$_POST['title'],
        'desc'=>$_POST['desc'],
        'url'=>$_POST['url'],
    ];
    if($newImage = handlePostImageUpload($config['upload_dir'])) $post['image'] = $newImage;
    else if(!empty($_POST['old_image'])) $post['image']=$_POST['old_image'];
    if($i === '') $data['posts'][]=$post; else $data['posts'][(int)$i]=$post;
    saveData($dataFile, $data);
    header("Location: admin.php?tab=posts");
    exit;
}
if(isset($_GET['del_post'])){
    array_splice($data['posts'], $_GET['del_post'], 1);
    saveData($dataFile, $data);
    header("Location: admin.php?tab=posts");
    exit;
}

$tab = $_GET['tab'] ?? 'profile';
$edit_post_id = $_GET['edit_post'] ?? null;
$edit_post_data = null;
if ($edit_post_id !== null && isset($data['posts'][$edit_post_id])) {
    $edit_post_data = $data['posts'][$edit_post_id];
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin - LinkBio</title>
<style>
body{font-family:Inter,system-ui;background:#f7f7f9;margin:0;padding:0}
nav{background:#4f46e5;color:#fff;padding:10px 20px;display:flex;justify-content:space-between;align-items:center}
nav a{color:#fff;text-decoration:none;margin:0 10px}
.container{padding:20px;max-width:800px;margin:auto}
form input,form textarea{width:100%;padding:10px;margin:6px 0;border:1px solid #ccc;border-radius:8px}
button{background:#4f46e5;color:#fff;border:none;padding:10px 18px;border-radius:8px;cursor:pointer}
table{width:100%;border-collapse:collapse;margin-top:10px}
td,th{border:1px solid #ddd;padding:8px;text-align:left}
a.btn{background:#4f46e5;color:#fff;padding:4px 8px;border-radius:6px;text-decoration:none}
img.thumb{width:60px;height:40px;object-fit:cover;border-radius:4px}
</style>
</head>
<body>
<nav>
  <div>LinkBio Admin</div>
  <div>
    <a href="?tab=profile">Profile</a>
    <a href="?tab=links">Links</a>
    <a href="?tab=posts">Posts</a>
    <a href="?logout=1">Logout</a>
  </div>
</nav>
<div class="container">

<?php if($tab==='profile'): ?>
<h2>Hồ sơ cá nhân</h2>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="save_profile">
  <label>Tên hiển thị:</label>
  <input name="name" value="<?php echo htmlspecialchars($data['profile']['name'] ?? ''); ?>">
  <label>Tên người dùng (@username):</label>
  <input name="username" value="<?php echo htmlspecialchars($data['profile']['username'] ?? ''); ?>">
  <label>Mô tả:</label>
  <textarea name="description" rows="3"><?php echo htmlspecialchars($data['profile']['description'] ?? ''); ?></textarea>
  <label>Ảnh đại diện:</label>
  <?php if(!empty($data['profile']['avatar'])): ?>
    <img src="<?php echo $data['profile']['avatar']; ?>" width="80" style="border-radius:50%"><br>
  <?php endif; ?>
  <input type="file" name="avatar">
  <button>Lưu hồ sơ</button>
</form>

<?php elseif($tab==='links'): ?>
<h2>Liên kết</h2>
<form method="post">
  <input type="hidden" name="action" value="save_link">
  <label>Tiêu đề:</label>
  <input name="title" required>
  <label>URL:</label>
  <input name="url" required>
  <label><input type="checkbox" name="active" checked> Hiển thị</label>
  <button>Thêm liên kết</button>
</form>
<table>
<tr><th>#</th><th>Tiêu đề</th><th>URL</th><th>Hiển thị</th><th></th></tr>
<?php foreach($data['links'] as $i=>$l): ?>
<tr>
<td><?php echo $i+1; ?></td>
<td><?php echo htmlspecialchars($l['title']); ?></td>
<td><a href="<?php echo htmlspecialchars($l['url']); ?>" target="_blank"><?php echo htmlspecialchars($l['url']); ?></a></td>
<td><?php echo !empty($l['active'])?'✅':'❌'; ?></td>
<td><a class="btn" href="?del_link=<?php echo $i; ?>" onclick="return confirm('Xóa liên kết này?')">Xóa</a></td>
</tr>
<?php endforeach; ?>
</table>

<?php elseif($tab==='posts'): ?>
<h2>Bài viết</h2>
<?php if ($edit_post_data): ?>
<h3>Chỉnh sửa bài viết #<?php echo $edit_post_id + 1; ?></h3>
<?php endif; ?>
<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="action" value="save_post">
  <input type="hidden" name="id" value="<?php echo $edit_post_id; ?>">
  <label>Tiêu đề:</label>
  <input name="title" required value="<?php echo htmlspecialchars($edit_post_data['title'] ?? ''); ?>">
  <label>Mô tả ngắn:</label>
  <input name="desc" required value="<?php echo htmlspecialchars($edit_post_data['desc'] ?? ''); ?>">
  <label>URL bài viết:</label>
  <input name="url" required value="<?php echo htmlspecialchars($edit_post_data['url'] ?? ''); ?>">
  <label>Ảnh đại diện:</label>
  <?php if (!empty($edit_post_data['image'])): ?>
    <img src="<?php echo htmlspecialchars($edit_post_data['image']); ?>" class="thumb"><br>
    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($edit_post_data['image']); ?>">
  <?php endif; ?>
  <input type="file" name="image">
  <button><?php echo $edit_post_data ? 'Lưu bài viết' : 'Thêm bài viết'; ?></button>
</form>
<table>
<tr><th>#</th><th>Ảnh</th><th>Tiêu đề</th><th>Mô tả</th><th>URL</th><th></th></tr>
<?php foreach($data['posts'] as $i=>$p): ?>
<tr>
<td><?php echo $i+1; ?></td>
<td><?php if(!empty($p['image'])) echo "<img src='{$p['image']}' class='thumb'>"; ?></td>
<td><?php echo htmlspecialchars($p['title']); ?></td>
<td><?php echo htmlspecialchars($p['desc']); ?></td>
<td><a href="<?php echo htmlspecialchars($p['url']); ?>" target="_blank">Xem</a></td>
<td>
  <a class="btn" href="?tab=posts&edit_post=<?php echo $i; ?>">Sửa</a>
  <a class="btn" href="?tab=posts&del_post=<?php echo $i; ?>" onclick="return confirm('Xóa bài viết này?')" style="background-color:#dc3545">Xóa</a>
</td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

</div>
</body>
</html>
