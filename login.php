<?php
require_once 'functions.php';
if (isLoggedIn()) redirect('center.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $nickname = trim($_POST['nickname'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha  = trim($_POST['captcha'] ?? '');

    if (isSensitiveCaptcha($captcha)) {
        $error = '当前输入的是敏感词，请重新输入。';
    } else {
        $user = findUserByNickname($nickname);
        if ($user && $user['password'] === $password) {
            $_SESSION['user'] = $user;
            redirect('center.php');
        } elseif (strlen($nickname) > 0 && strlen($nickname) < 10) {
            $_SESSION['pending_nickname'] = $nickname;
            redirect('secondary.php');
        } else {
            $error = '账号或密码错误。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>用户登录 - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="index.php" class="brand"><?= SITE_NAME ?></a>
        <nav class="site-nav">
            <a href="register.php">注册</a>
            <a href="login.php">登录</a>
            <a href="docs.php">文档中心</a>
        </nav>
    </div>
</header>

<div class="page-body">
    <div class="card">
        <h2>登录</h2>
        <p class="subtitle">欢迎回到 <?= SITE_NAME ?></p>

        <?php if (isset($_GET['registered'])): ?>
            <div class="notice" style="background:#e6f4ea;border-color:#ceead6;color:#188038;">注册成功，请使用您的账号登录。</div>
        <?php endif; ?>

        <?php if ($error): ?><div class="notice"><?= e($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>用户名 <span class="required">*</span></label>
                <input type="text" name="nickname" placeholder="请输入用户名" autocomplete="off" required>
            </div>
            <div class="form-group">
                <label>密码 <span class="required">*</span></label>
                <input type="password" name="password" placeholder="请输入密码" autocomplete="off" required>
            </div>
            <div class="form-group">
                <label>验证码 <span class="required">*</span></label>
                <div class="captcha-row">
                    <input type="text" name="captcha" id="login_captcha" placeholder="请输入验证码" autocomplete="off">
                    <canvas id="login_captcha_canvas" width="110" height="42" title="点击刷新" style="border:1px solid #d1d5db; border-radius:6px; cursor:pointer; background:#f8f9fa;"></canvas>
                </div>
                <span class="error-text" id="login_captcha_error"></span>
            </div>
            <button type="submit" name="login" class="btn">登录</button>
            <a href="register.php" class="link-text">没有账号？立即注册</a>
        </form>
    </div>
</div>

<div class="footer">© <?= date('Y') ?> <?= SITE_NAME ?> 版权所有</div>

<script src="captcha.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    // 首次进入页面生成验证码（不计数）
    Captcha.generate('login_captcha_canvas', 'login_captcha', 'login_captcha_error', true);

    // 输入实时检测敏感词
    const input = document.getElementById('login_captcha');
    if (input) {
        input.addEventListener('input', () => {
            Captcha.check('login_captcha', 'login_captcha_error');
        });
    }

    // 点击 canvas 刷新（会 +1 计数）
    document.getElementById('login_captcha_canvas').addEventListener('click', () => {
        Captcha.generate('login_captcha_canvas', 'login_captcha', 'login_captcha_error', false);
    });
});
</script>
</body>
</html>