<?php
require_once 'functions.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    // 无论用户填什么，永远失败（因为账号根本不存在）
    $error = '验证码错误，请重新输入。';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>安全验证 - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="register.php" class="brand"><?= SITE_NAME ?></a>
        <nav class="site-nav">
            <a href="register.php">注册</a>
            <a href="login.php">登录</a>
            <a href="docs.php">文档中心</a>
        </nav>
    </div>
</header>

<div class="page-body">
    <div class="card">
        <h2>安全验证</h2>
        <p class="subtitle">我们已将验证码发送到您的邮箱，请在下方填写</p>

        <?php if ($error): ?><div class="notice"><?= e($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>邮箱验证码 <span class="required">*</span></label>
                <input type="text" name="code" placeholder="请输入邮件中收到的验证码" autocomplete="off" required>
            </div>
            <button type="submit" name="verify" class="btn">验证</button>
            <a href="login.php" class="link-text">返回登录</a>
        </form>
    </div>
</div>

<div class="footer">© <?= date('Y') ?> <?= SITE_NAME ?> 版权所有</div>

</body>
</html>