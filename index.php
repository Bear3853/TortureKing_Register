<?php
require_once 'functions.php';
if (isLoggedIn()) redirect('center.php');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= SITE_NAME ?> - 安全、便捷的统一账号系统</title>
<link rel="stylesheet" href="style.css">
<style>
    /* ========== 首页专属样式 ========== */
    body { background: #f5f7fa; }

    /* Hero 区 */
    .hero {
        background: linear-gradient(135deg, #1a73e8 0%, #4285f4 60%, #60a5fa 100%);
        padding: 90px 20px 100px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .hero::before {
        content: "";
        position: absolute;
        right: -120px;
        top: -120px;
        width: 420px;
        height: 420px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .hero::after {
        content: "";
        position: absolute;
        right: 200px;
        bottom: -180px;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .hero-inner {
        max-width: 860px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    .hero h1 {
        font-size: 40px;
        font-weight: 700;
        letter-spacing: -0.5px;
        margin-bottom: 18px;
        line-height: 1.3;
    }
    .hero p {
        font-size: 16px;
        opacity: 0.92;
        margin-bottom: 36px;
        line-height: 1.7;
    }
    .hero-btns {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .hero-btns .btn {
        display: inline-block;
        width: auto;
        padding: 13px 36px;
        font-size: 15px;
        font-weight: 500;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s;
        margin: 0;
    }
    .hero-btns .btn-primary {
        background: #fff;
        color: #1a73e8;
    }
    .hero-btns .btn-primary:hover { background: #f0f4f9; transform: translateY(-1px); }
    .hero-btns .btn-outline {
        background: rgba(255,255,255,0.12);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.4);
    }
    .hero-btns .btn-outline:hover { background: rgba(255,255,255,0.2); }

    .hero-stats {
        display: flex;
        gap: 48px;
        justify-content: center;
        margin-top: 60px;
        flex-wrap: wrap;
        position: relative;
        z-index: 1;
    }
    .hero-stat-item {
        text-align: center;
    }
    .hero-stat-item .num {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .hero-stat-item .lbl {
        font-size: 13px;
        opacity: 0.85;
    }

    /* 主容器 */
    .main-container {
        max-width: 1080px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* 卡片区（上浮） */
    .float-cards {
        margin-top: -50px;
        margin-bottom: 60px;
        position: relative;
        z-index: 2;
    }
    .float-cards-inner {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
    }
    .float-card {
        background: #fff;
        border-radius: 14px;
        padding: 26px 24px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.08);
        border: 1px solid #eef1f5;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .float-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 32px rgba(0,0,0,0.1);
    }
    .float-card .icon-circle {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: #e8f0fe;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 14px;
    }
    .float-card h3 {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    .float-card p {
        font-size: 13.5px;
        color: #888;
        line-height: 1.7;
    }

    /* 功能区标题 */
    .section-title {
        text-align: center;
        margin-bottom: 40px;
    }
    .section-title h2 {
        font-size: 24px;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
    }
    .section-title p {
        font-size: 14px;
        color: #888;
    }

    /* 特性区 */
    .features-section {
        padding: 20px 0 60px;
    }
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 18px;
    }
    .feature-item {
        background: #fff;
        border-radius: 12px;
        padding: 22px;
        border: 1px solid #eef1f5;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .feature-item:hover {
        border-color: #c5d9f9;
        box-shadow: 0 4px 16px rgba(26,115,232,0.06);
    }
    .feature-item .icon-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }
    .feature-item .icon-row .icon {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #f0f4f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .feature-item h3 {
        font-size: 14.5px;
        font-weight: 600;
        color: #333;
    }
    .feature-item p {
        font-size: 13px;
        color: #888;
        line-height: 1.7;
    }

    /* CTA 区 */
    .cta-section {
        background: #fff;
        border-radius: 16px;
        padding: 40px;
        text-align: center;
        border: 1px solid #eef1f5;
        box-shadow: 0 2px 12px rgba(0,0,0,0.03);
        margin-bottom: 60px;
    }
    .cta-section h2 {
        font-size: 22px;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
    }
    .cta-section p {
        font-size: 14px;
        color: #888;
        margin-bottom: 24px;
    }
    .cta-section .btn {
        display: inline-block;
        width: auto;
        padding: 12px 34px;
        font-size: 14.5px;
        background: #1a73e8;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        margin: 0;
    }
    .cta-section .btn:hover { background: #1557b0; }

    /* 响应式 */
    @media (max-width: 640px) {
        .hero { padding: 60px 16px 80px; }
        .hero h1 { font-size: 26px; }
        .hero p { font-size: 14px; }
        .hero-stats { gap: 30px; margin-top: 40px; }
        .hero-stat-item .num { font-size: 22px; }
        .float-cards { margin-top: -40px; }
        .cta-section { padding: 28px 20px; }
    }
</style>
</head>
<body>

<!-- 顶部导航 -->
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

<!-- Hero 区 -->
<section class="hero">
    <div class="hero-inner">
        <h1>安全、便捷的<br>统一账号系统</h1>
        <p>一个账号，畅享全站服务。<?= SITE_NAME ?> 为您提供安全可靠的身份认证与账号管理体验。</p>
        <div class="hero-btns">
            <a href="register.php" class="btn btn-primary">免费注册</a>
            <a href="login.php" class="btn btn-outline">已有账号，去登录</a>
        </div>
    </div>

    <div class="hero-stats">
        <div class="hero-stat-item">
            <div class="num">99.9%</div>
            <div class="lbl">服务可用性</div>
        </div>
        <div class="hero-stat-item">
            <div class="num">24/7</div>
            <div class="lbl">全天候运行</div>
        </div>
        <div class="hero-stat-item">
            <div class="num">256-bit</div>
            <div class="lbl">加密保护</div>
        </div>
    </div>
</section>

<!-- 上浮卡片 -->
<div class="main-container">
    <div class="float-cards">
        <div class="float-cards-inner">
            <div class="float-card">
                <div class="icon-circle">⚡</div>
                <h3>极速注册</h3>
                <p>简单几步即可完成账号创建，立即开始使用我们的服务。</p>
            </div>
            <div class="float-card">
                <div class="icon-circle">🔐</div>
                <h3>安全可靠</h3>
                <p>采用行业标准的加密与验证机制，全面保障您的账号安全。</p>
            </div>
            <div class="float-card">
                <div class="icon-circle">🌐</div>
                <h3>全站通用</h3>
                <p>一次注册，即可登录所有接入 <?= SITE_NAME ?> 的服务。</p>
            </div>
        </div>
    </div>

    <!-- 特性区 -->
    <div class="features-section">
        <div class="section-title">
            <h2>为什么选择我们</h2>
            <p>为您提供稳定、安全、易用的账号服务</p>
        </div>
        <div class="features-grid">
            <div class="feature-item">
                <div class="icon-row">
                    <div class="icon">🛡️</div>
                    <h3>多重防护</h3>
                </div>
                <p>融合图片验证码、人机识别等多重机制，有效抵御恶意注册。</p>
            </div>
            <div class="feature-item">
                <div class="icon-row">
                    <div class="icon">📱</div>
                    <h3>跨端支持</h3>
                </div>
                <p>兼容桌面、平板与手机浏览器，随时随地访问您的账号。</p>
            </div>
            <div class="feature-item">
                <div class="icon-row">
                    <div class="icon">⚙️</div>
                    <h3>灵活管理</h3>
                </div>
                <p>管理员可随时管理用户信息，配置系统名称等个性化选项。</p>
            </div>
            <div class="feature-item">
                <div class="icon-row">
                    <div class="icon">📊</div>
                    <h3>数据可视</h3>
                </div>
                <p>后台提供实时用户统计，帮助您掌握系统运营状况。</p>
            </div>
        </div>
    </div>

    <!-- CTA 区 -->
    <div class="cta-section">
        <h2>准备好开始了吗？</h2>
        <p>立即注册 <?= SITE_NAME ?> 账号，体验完整的服务</p>
        <a href="register.php" class="btn">立即注册</a>
    </div>
</div>

<!-- 页脚 -->
<div class="footer">
    © <?= date('Y') ?> <?= SITE_NAME ?> 版权所有 · 保留所有权利
</div>

</body>
</html>
