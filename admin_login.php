<?php
require_once 'functions.php';
if (isAdmin()) redirect('admin.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    if ($u === adminUsername() && $p === adminPassword()) {
        $_SESSION['is_admin'] = true;
        redirect('admin.php');
    } else {
        $error = '管理员账号或密码错误。';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>管理员登录 - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="brand"><?= SITE_NAME ?></a>
        <nav class="site-nav">
            <a href="register.php">注册</a>
            <a href="login.php">登录</a>
            <a href="admin_login.php">管理后台</a>
            <a href="docs.php">文档中心</a>
        </nav>
    </div>
</header>

<div class="page-body">
    <div class="card">
        <h2>管理员登录</h2>
        <p class="subtitle">仅限内部管理员使用</p>

        <?php if ($error): ?><div class="notice"><?= e($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>管理员账号 <span class="required">*</span></label>
                <input type="text" name="username" placeholder="请输入管理员账号" autocomplete="off" required>
            </div>
            <div class="form-group">
                <label>密码 <span class="required">*</span></label>
                <input type="password" name="password" placeholder="请输入密码" autocomplete="off" required>
            </div>
            <button type="submit" name="admin_login" class="btn">登录</button>
            <a href="login.php" class="link-text">返回用户登录</a>
        </form>
    </div>
</div>

<div class="footer">© <?= date('Y') ?> <?= SITE_NAME ?> 版权所有</div>

</body>
</html>