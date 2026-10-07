<?php
require_once 'functions.php';
requireLogin();

// ===== 从 users.json 实时拉取最新数据，覆盖 session 快照 =====
$freshUser = null;
foreach (getUsers() as $u) {
    // 优先用 nickname 匹配（用户名唯一），其次用 id
    if (($u['nickname'] ?? '') === ($_SESSION['user']['nickname'] ?? '')) {
        $freshUser = $u;
        break;
    }
}
if ($freshUser === null) {
    // 如果用户已被后台删除，直接踢下线
    unset($_SESSION['user']);
    redirect('login.php');
}
$_SESSION['user'] = $freshUser;

// ===== 注销账号 =====
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $users = getUsers();
    $new = [];
    foreach ($users as $u) {
        if (($u['nickname'] ?? '') !== $_SESSION['user']['nickname']) $new[] = $u;
    }
    saveUsers($new);
    unset($_SESSION['user']);
    redirect('register.php');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>用户中心 - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="style.css">
<style>
    /* 用户中心专属样式 */
    .uc-wrap {
        max-width: 960px;
        margin: 0 auto;
        padding: 40px 20px;
        width: 100%;
    }

    /* 顶部欢迎条 */
    .uc-hero {
        background: linear-gradient(135deg, #1a73e8 0%, #4285f4 100%);
        border-radius: 14px;
        padding: 32px 36px;
        color: #fff;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }
    .uc-hero::before {
        content: "";
        position: absolute;
        right: -60px;
        top: -60px;
        width: 220px;
        height: 220px;
        background: rgba(255,255,255,0.08);
        border-radius: 50%;
    }
    .uc-hero::after {
        content: "";
        position: absolute;
        right: 60px;
        bottom: -80px;
        width: 160px;
        height: 160px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .uc-hero h1 {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 8px;
        position: relative;
        z-index: 1;
    }
    .uc-hero p {
        font-size: 13.5px;
        opacity: 0.9;
        position: relative;
        z-index: 1;
    }
    .uc-hero .uc-badge {
        display: inline-block;
        padding: 3px 10px;
        background: rgba(255,255,255,0.2);
        border-radius: 20px;
        font-size: 12px;
        margin-top: 10px;
        position: relative;
        z-index: 1;
    }

    /* 两列布局 */
    .uc-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-bottom: 22px;
    }
    @media (max-width: 720px) {
        .uc-grid { grid-template-columns: 1fr; }
    }

    .uc-card {
        background: #fff;
        border-radius: 12px;
        padding: 22px 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.03);
        border: 1px solid #eef1f5;
    }
    .uc-card-title {
        font-size: 13px;
        font-weight: 600;
        color: #888;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .uc-card-title .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #1a73e8;
    }

    /* 账户信息 */
    .uc-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 11px 0;
        border-bottom: 1px dashed #f0f2f5;
        font-size: 14px;
    }
    .uc-info-row:last-child { border-bottom: none; }
    .uc-info-row .label { color: #888; font-size: 13px; }
    .uc-info-row .value { color: #333; font-weight: 500; word-break: break-all; text-align: right; max-width: 60%; }
    .uc-info-row .value.pwd {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: 12.5px;
        color: #555;
    }

    /* 头像区 */
    .uc-avatar-row {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 18px;
    }
    .uc-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #4285f4, #1a73e8);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 600;
        flex-shrink: 0;
    }
    .uc-avatar-name {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
    }
    .uc-avatar-sub {
        font-size: 12.5px;
        color: #999;
    }

    /* 功能占位卡片 */
    .uc-feature {
        background: #fff;
        border-radius: 12px;
        padding: 20px 22px;
        border: 1px dashed #dfe3e8;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: border-color 0.2s;
    }
    .uc-feature:hover { border-color: #c5d9f9; }
    .uc-feature-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f0f4f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        margin-bottom: 2px;
    }
    .uc-feature-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
    }
    .uc-feature-desc {
        font-size: 12.5px;
        color: #999;
        line-height: 1.6;
    }
    .uc-feature-tag {
        align-self: flex-start;
        font-size: 11px;
        color: #1a73e8;
        background: #e8f0fe;
        padding: 2px 8px;
        border-radius: 10px;
        margin-top: 4px;
    }

    /* 底部按钮 */
    .uc-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }
    .uc-actions .btn { flex: 1; margin: 0; }

    /* 底部开发提示条 */
    .uc-devbar {
        margin-top: 28px;
        text-align: center;
        font-size: 12.5px;
        color: #b0b6bd;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .uc-devbar .dot-anim {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #f4b400;
        animation: pulse 1.6s ease-in-out infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 0.3; transform: scale(0.9); }
        50% { opacity: 1; transform: scale(1.15); }
    }
</style>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="center.php" class="brand"><?= SITE_NAME ?></a>
        <nav class="site-nav">
            <a href="center.php">用户中心</a>
            <a href="logout.php">退出登录</a>
            <a href="docs.php">文档中心</a>
        </nav>
    </div>
</header>

<div class="uc-wrap">
    <!-- 欢迎条 -->
    <div class="uc-hero">
        <h1>欢迎回来，<?= e($_SESSION['user']['nickname']) ?> 👋</h1>
        <p>这里是您的 <?= SITE_NAME ?> 个人空间</p>
        <span class="uc-badge">账号状态：正常</span>
    </div>

    <!-- 账户信息 + 基本信息 -->
    <div class="uc-grid">
        <!-- 卡片 1：账户信息 -->
        <div class="uc-card">
            <div class="uc-card-title"><span class="dot"></span>账户信息</div>
            <div class="uc-avatar-row">
                <div class="uc-avatar"><?= e(mb_substr($_SESSION['user']['nickname'], 0, 1)) ?></div>
                <div>
                    <div class="uc-avatar-name"><?= e($_SESSION['user']['nickname']) ?></div>
                    <div class="uc-avatar-sub">注册时间：<?= e($_SESSION['user']['created_at'] ?? '-') ?></div>
                </div>
            </div>
            <div class="uc-info-row">
                <span class="label">用户名</span>
                <span class="value"><?= e($_SESSION['user']['nickname']) ?></span>
            </div>
            <div class="uc-info-row">
                <span class="label">邮箱</span>
                <span class="value"><?= e($_SESSION['user']['email']) ?></span>
            </div>
        </div>

        <!-- 卡片 2：个人资料 -->
        <div class="uc-card">
            <div class="uc-card-title"><span class="dot"></span>个人资料</div>
            <div class="uc-info-row">
                <span class="label">性别</span>
                <span class="value"><?= e($_SESSION['user']['gender']) ?></span>
            </div>
            <div class="uc-info-row">
                <span class="label">密码</span>
                <span class="value pwd"><?= e($_SESSION['user']['password']) ?></span>
            </div>
            <div class="uc-info-row">
                <span class="label">账号 ID</span>
                <span class="value" style="font-family:'Courier New',monospace;font-size:12.5px;color:#888;"><?= e($_SESSION['user']['id']) ?></span>
            </div>
        </div>
    </div>

    <!-- 正在开发的功能占位 -->
    <div class="uc-card-title" style="margin-bottom:12px;">
        <span class="dot" style="background:#f4b400;"></span>更多功能（正在开发中）
    </div>
    <div class="uc-grid">
        <div class="uc-feature">
            <div class="uc-feature-icon">🔒</div>
            <div class="uc-feature-title">修改密码</div>
            <div class="uc-feature-desc">随时更换您的登录密码，保障账号安全。</div>
            <div class="uc-feature-tag">开发中</div>
        </div>
        <div class="uc-feature">
            <div class="uc-feature-icon">📧</div>
            <div class="uc-feature-title">绑定邮箱</div>
            <div class="uc-feature-desc">绑定常用邮箱，用于找回密码与接收通知。</div>
            <div class="uc-feature-tag">开发中</div>
        </div>
        <div class="uc-feature">
            <div class="uc-feature-icon">🖼️</div>
            <div class="uc-feature-title">更换头像</div>
            <div class="uc-feature-desc">上传个性化头像，让别人更容易记住您。</div>
            <div class="uc-feature-tag">开发中</div>
        </div>
        <div class="uc-feature">
            <div class="uc-feature-icon">📊</div>
            <div class="uc-feature-title">登录记录</div>
            <div class="uc-feature-desc">查看最近登录设备与地点，及时发现异常。</div>
            <div class="uc-feature-tag">开发中</div>
        </div>
    </div>

    <!-- 底部操作按钮 -->
    <div class="uc-actions">
        <a href="logout.php" class="btn btn-secondary">退出登录</a>
        <a href="center.php?action=delete" class="btn btn-danger"
           onclick="return confirm('确定要注销此账号吗？此操作不可恢复！');">注销账号</a>
    </div>

    <!-- 底部提示 -->
    <div class="uc-devbar">
        <span class="dot-anim"></span>
        我们正在努力开发更多功能，敬请期待...
    </div>
</div>

<div class="footer">© <?= date('Y') ?> <?= SITE_NAME ?> 版权所有</div>

</body>
</html>