<?php
require_once 'functions.php';
requireAdmin();

$notice = '';
$noticeType = 'success';
$tab = $_GET['tab'] ?? 'users';

// ===== 删除用户 =====
if (isset($_GET['delete'])) {
    $users = getUsers();
    $new = [];
    foreach ($users as $u) {
        if (($u['id'] ?? '') !== $_GET['delete']) $new[] = $u;
    }
    saveUsers($new);
    redirect('admin.php?msg=deleted');
}

// ===== 保存系统设置 =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $newName = trim($_POST['site_name'] ?? '');
    if ($newName === '') {
        $notice = '系统名称不能为空。';
        $noticeType = 'error';
    } else {
        $cfg = getConfig();
        $cfg['site_name'] = $newName;
        saveConfig($cfg);
        $notice = '系统名称已更新为：' . $newName;
    }
}

// ===== 保存用户编辑（无任何校验，想填啥填啥） =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $originalId = $_POST['original_id'] ?? '';
    $users = getUsers();
    $found = false;
    foreach ($users as $i => $u) {
        if (($u['id'] ?? '') === $originalId) {
            $users[$i]['id']         = trim($_POST['edit_id'] ?? '');
            $users[$i]['email']      = trim($_POST['edit_email'] ?? '');
            $users[$i]['nickname']   = trim($_POST['edit_nickname'] ?? '');
            $users[$i]['gender']     = trim($_POST['edit_gender'] ?? '');
            $users[$i]['password']   = $_POST['edit_password'] ?? '';
            $users[$i]['created_at'] = trim($_POST['edit_created_at'] ?? '');
            $found = true;
            break;
        }
    }
    if ($found) {
        saveUsers($users);
        redirect('admin.php?msg=updated');
    } else {
        $notice = '未找到该用户。';
        $noticeType = 'error';
    }
}

// ===== 退出登录 =====
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['is_admin']);
    redirect('admin_login.php');
}

$users = getUsers();
$config = getConfig();
$currentSiteName = $config['site_name'];
$currentAdminUser = adminUsername();

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') $notice = '用户已删除。';
    if ($_GET['msg'] === 'updated') $notice = '用户信息已更新。';
}

// ===== 编辑用户数据 =====
$editUser = null;
if ($tab === 'edit' && isset($_GET['id'])) {
    $editUser = findUserById($_GET['id']);
    if (!$editUser) {
        $notice = '未找到该用户。';
        $noticeType = 'error';
        $tab = 'users';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>管理后台 - <?= e($currentSiteName) ?></title>
<link rel="stylesheet" href="style.css">
<style>
    .adm-layout { display: flex; min-height: 100vh; background: #f5f7fa; }
    .adm-sidebar {
        width: 220px; background: #1f2937; color: #cbd5e1; flex-shrink: 0;
        display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh;
    }
    .adm-sidebar-brand {
        padding: 22px 20px; font-size: 16px; font-weight: 700; color: #fff;
        letter-spacing: 0.3px; border-bottom: 1px solid #374151;
        display: flex; align-items: center; gap: 8px; text-decoration: none;
        word-break: break-all;
    }
    .adm-sidebar-brand .logo-dot { width: 8px; height: 8px; border-radius: 50%; background: #60a5fa; flex-shrink: 0; }
    .adm-nav { padding: 16px 12px; flex: 1; display: flex; flex-direction: column; gap: 4px; }
    .adm-nav-item {
        display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        border-radius: 8px; font-size: 14px; color: #cbd5e1;
        text-decoration: none; transition: all 0.15s;
    }
    .adm-nav-item:hover { background: #374151; color: #fff; }
    .adm-nav-item.active { background: #1a73e8; color: #fff; font-weight: 500; }
    .adm-nav-item .icon { font-size: 15px; width: 20px; text-align: center; }

    .adm-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .adm-topbar {
        height: 62px; background: #fff; border-bottom: 1px solid #e5e7eb;
        padding: 0 28px; display: flex; align-items: center;
        justify-content: space-between; position: sticky; top: 0; z-index: 10;
    }
    .adm-topbar-title { font-size: 16px; font-weight: 600; color: #333; }
    .adm-topbar-right { display: flex; align-items: center; gap: 16px; }
    .adm-user {
        display: flex; align-items: center; gap: 10px; padding: 6px 12px;
        border-radius: 20px; background: #f3f4f6; font-size: 13.5px; color: #333;
    }
    .adm-user-avatar {
        width: 28px; height: 28px; border-radius: 50%;
        background: linear-gradient(135deg, #4285f4, #1a73e8); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; font-weight: 600;
    }
    .adm-logout-btn {
        font-size: 13.5px; color: #d93025; text-decoration: none;
        padding: 7px 14px; border-radius: 6px; transition: background 0.15s;
        border: 1px solid #fad2cf; background: #fce8e6;
    }
    .adm-logout-btn:hover { background: #f7d4d2; }

    .adm-content { padding: 28px; max-width: 1200px; width: 100%; box-sizing: border-box; }
    .adm-page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 22px; flex-wrap: wrap; gap: 10px; }
    .adm-page-header h1 { font-size: 20px; color: #333; margin-bottom: 4px; }
    .adm-page-header p { font-size: 13px; color: #888; }

    .adm-card {
        background: #fff; border-radius: 12px; border: 1px solid #eef1f5;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03); padding: 24px;
    }
    .adm-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 22px; }
    .adm-stat {
        background: #fff; border-radius: 12px; border: 1px solid #eef1f5;
        padding: 18px 20px; display: flex; justify-content: space-between; align-items: center;
    }
    .adm-stat .info .lbl { font-size: 12.5px; color: #888; margin-bottom: 6px; }
    .adm-stat .info .num { font-size: 24px; font-weight: 700; color: #333; }
    .adm-stat .icon-box {
        width: 42px; height: 42px; border-radius: 10px; background: #e8f0fe;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }

    .adm-toolbar { display: flex; gap: 8px; margin-bottom: 16px; }
    .adm-toolbar input {
        flex: 1; padding: 9px 14px; border: 1px solid #d1d5db;
        border-radius: 8px; font-size: 14px; transition: border-color 0.15s;
    }
    .adm-toolbar input:focus { border-color: #1a73e8; outline: none; box-shadow: 0 0 0 3px rgba(26,115,232,0.1); }
    .adm-toolbar button {
        padding: 9px 18px; background: #1a73e8; color: #fff;
        border: none; border-radius: 8px; font-size: 14px; cursor: pointer;
    }
    .adm-toolbar button:hover { background: #1557b0; }

    .adm-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .adm-table th, .adm-table td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #f0f2f5; }
    .adm-table th {
        background: #fafbfc; font-weight: 600; color: #666;
        font-size: 12.5px; letter-spacing: 0.3px; text-transform: uppercase;
    }
    .adm-table tr:hover td { background: #fafbfc; }
    .adm-table td.id-cell { font-family: 'Courier New', monospace; font-size: 12px; color: #888; }
    .adm-table .badge {
        display: inline-block; padding: 3px 10px; border-radius: 12px;
        font-size: 12px; background: #e8f0fe; color: #1a73e8;
    }
    .adm-table .btn-mini {
        padding: 5px 12px; font-size: 12px; border-radius: 6px;
        text-decoration: none; display: inline-block; transition: background 0.15s;
        border: none; cursor: pointer;
    }
    .adm-table .btn-edit { background: #e8f0fe; color: #1a73e8; margin-right: 4px; }
    .adm-table .btn-edit:hover { background: #d4e4fc; }
    .adm-table .btn-del { background: #fce8e6; color: #d93025; }
    .adm-table .btn-del:hover { background: #f7d4d2; }

    .empty-tip { text-align: center; color: #999; padding: 40px 0; font-size: 14px; }

    .adm-form .form-row { margin-bottom: 22px; }
    .adm-form label { display: block; font-size: 13.5px; font-weight: 500; color: #333; margin-bottom: 6px; }
    .adm-form input[type="text"], .adm-form input[type="password"] {
        width: 100%; max-width: 440px; padding: 11px 14px;
        border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px;
        transition: border-color 0.15s; box-sizing: border-box;
    }
    .adm-form input:focus { border-color: #1a73e8; outline: none; box-shadow: 0 0 0 3px rgba(26,115,232,0.1); }
    .adm-form .hint { display: block; font-size: 12.5px; color: #888; margin-top: 8px; }
    .adm-form .btn-save {
        padding: 11px 26px; background: #1a73e8; color: #fff;
        border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;
    }
    .adm-form .btn-save:hover { background: #1557b0; }
    .adm-form .btn-cancel {
        padding: 11px 26px; background: #f1f3f4; color: #333;
        border: none; border-radius: 8px; font-size: 14px; font-weight: 500;
        cursor: pointer; text-decoration: none; display: inline-block; margin-left: 8px;
    }
    .adm-form .btn-cancel:hover { background: #e5e7eb; }

    .adm-notice { padding: 12px 16px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; }
    .adm-notice.success { background: #e6f4ea; border: 1px solid #ceead6; color: #188038; }
    .adm-notice.error { background: #fce8e6; border: 1px solid #fad2cf; color: #d93025; }

    @media (max-width: 720px) {
        .adm-sidebar { width: 60px; }
        .adm-sidebar-brand { font-size: 0; padding: 22px 10px; justify-content: center; }
        .adm-nav-item { justify-content: center; padding: 12px 8px; font-size: 0; }
        .adm-nav-item .icon { font-size: 16px; }
        .adm-topbar { padding: 0 14px; }
        .adm-user span { display: none; }
        .adm-content { padding: 16px; }
    }
</style>
</head>
<body>

<div class="adm-layout">
    <aside class="adm-sidebar">
        <a href="admin.php" class="adm-sidebar-brand">
            <span class="logo-dot"></span>
            <?= e($currentSiteName) ?>
        </a>
        <nav class="adm-nav">
            <a href="admin.php?tab=users" class="adm-nav-item <?= $tab === 'users' ? 'active' : '' ?>">
                <span class="icon">👥</span>
                <span>用户管理</span>
            </a>
            <a href="admin.php?tab=settings" class="adm-nav-item <?= $tab === 'settings' ? 'active' : '' ?>">
                <span class="icon">⚙️</span>
                <span>系统设置</span>
            </a>
            <a href="index.php" target="_blank" class="adm-nav-item">
                <span class="icon">🌐</span>
                <span>查看前台</span>
            </a>
        </nav>
    </aside>

    <div class="adm-main">
        <header class="adm-topbar">
            <div class="adm-topbar-title">
                <?php
                    if ($tab === 'settings') echo '系统设置';
                    elseif ($tab === 'edit') echo '编辑用户';
                    else echo '用户管理';
                ?>
            </div>
            <div class="adm-topbar-right">
                <div class="adm-user">
                    <div class="adm-user-avatar"><?= e(mb_substr($currentAdminUser, 0, 1)) ?></div>
                    <span><?= e($currentAdminUser) ?></span>
                </div>
                <a href="admin.php?action=logout" class="adm-logout-btn">退出登录</a>
            </div>
        </header>

        <div class="adm-content">

            <?php if ($notice): ?>
                <div class="adm-notice <?= $noticeType ?>"><?= e($notice) ?></div>
            <?php endif; ?>

            <?php if ($tab === 'settings'): ?>
                <div class="adm-page-header">
                    <div>
                        <h1>系统设置</h1>
                        <p>修改网站品牌名称</p>
                    </div>
                </div>

                <div class="adm-card">
                    <form class="adm-form" method="POST">
                        <div class="form-row">
                            <label>系统名称</label>
                            <input type="text" name="site_name" value="<?= e($currentSiteName) ?>" maxlength="30" required>
                            <span class="hint">该名称会显示在首页、注册页、登录页、用户中心的标题和页头中。</span>
                        </div>
                        <button type="submit" name="save_settings" value="1" class="btn-save">保存设置</button>
                    </form>

                    <div style="margin-top:28px;padding-top:20px;border-top:1px solid #f0f2f5;">
                        <div style="font-size:13px;color:#888;line-height:1.8;">
                            当前管理员账号：<strong style="color:#333;"><?= e($currentAdminUser) ?></strong><br>
                            <span style="font-size:12px;color:#aaa;">管理员账号密码写在 <code style="background:#f5f7fa;padding:1px 6px;border-radius:3px;">functions.php</code> 顶部的常量里，如需修改请直接编辑该文件。</span>
                        </div>
                    </div>
                </div>

            <?php elseif ($tab === 'edit' && $editUser): ?>
                <div class="adm-page-header">
                    <div>
                        <h1>编辑用户</h1>
                        <p>修改此用户的全部信息（无任何校验限制）</p>
                    </div>
                </div>

                <div class="adm-card">
                    <form class="adm-form" method="POST">
                        <input type="hidden" name="original_id" value="<?= e($editUser['id'] ?? '') ?>">

                        <div class="form-row">
                            <label>用户 ID</label>
                            <input type="text" name="edit_id" value="<?= e($editUser['id'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <label>邮箱</label>
                            <input type="text" name="edit_email" value="<?= e($editUser['email'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <label>用户名</label>
                            <input type="text" name="edit_nickname" value="<?= e($editUser['nickname'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <label>性别</label>
                            <input type="text" name="edit_gender" value="<?= e($editUser['gender'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <label>密码</label>
                            <input type="text" name="edit_password" value="<?= e($editUser['password'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <label>注册时间</label>
                            <input type="text" name="edit_created_at" value="<?= e($editUser['created_at'] ?? '') ?>">
                        </div>

                        <button type="submit" name="save_user" value="1" class="btn-save">保存修改</button>
                        <a href="admin.php?tab=users" class="btn-cancel">返回列表</a>
                    </form>
                </div>

            <?php else: ?>
                <div class="adm-page-header">
                    <div>
                        <h1>用户管理</h1>
                        <p>查看、编辑、删除所有注册用户</p>
                    </div>
                </div>

                <div class="adm-stats">
                    <div class="adm-stat">
                        <div class="info">
                            <div class="lbl">注册用户总数</div>
                            <div class="num"><?= count($users) ?></div>
                        </div>
                        <div class="icon-box">👥</div>
                    </div>
                    <div class="adm-stat">
                        <div class="info">
                            <div class="lbl">今日新增</div>
                            <div class="num">
                                <?php
                                    $today = date('Y-m-d');
                                    $todayCount = 0;
                                    foreach ($users as $u) {
                                        if (isset($u['created_at']) && strpos($u['created_at'], $today) === 0) $todayCount++;
                                    }
                                    echo $todayCount;
                                ?>
                            </div>
                        </div>
                        <div class="icon-box" style="background:#e6f4ea;">📈</div>
                    </div>
                    <div class="adm-stat">
                        <div class="info">
                            <div class="lbl">当前系统名称</div>
                            <div class="num" style="font-size:15px;line-height:1.6;word-break:break-all;"><?= e($currentSiteName) ?></div>
                        </div>
                        <div class="icon-box" style="background:#fef7e0;">⚙️</div>
                    </div>
                </div>

                <div class="adm-card">
                    <div class="adm-toolbar">
                        <input type="text" id="searchInput" placeholder="按用户名或邮箱搜索..." oninput="filterUsers()">
                        <button type="button" onclick="filterUsers()">搜索</button>
                    </div>

                    <table class="adm-table" id="userTable">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th>ID</th>
                                <th>邮箱</th>
                                <th>用户名</th>
                                <th>性别</th>
                                <th>密码</th>
                                <th>注册时间</th>
                                <th style="width:140px;text-align:right;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr><td colspan="8" class="empty-tip">暂无注册用户</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $i => $u): ?>
                                    <tr>
                                        <td style="color:#aaa;"><?= $i + 1 ?></td>
                                        <td class="id-cell"><?= e($u['id'] ?? '-') ?></td>
                                        <td><?= e($u['email'] ?? '-') ?></td>
                                        <td><strong><?= e($u['nickname'] ?? '-') ?></strong></td>
                                        <td><span class="badge"><?= e($u['gender'] ?? '-') ?></span></td>
                                        <td><?= e($u['password'] ?? '-') ?></td>
                                        <td style="color:#888;font-size:12.5px;"><?= e($u['created_at'] ?? '-') ?></td>
                                        <td style="text-align:right;white-space:nowrap;">
                                            <a href="admin.php?tab=edit&id=<?= urlencode($u['id'] ?? '') ?>" class="btn-mini btn-edit">编辑</a>
                                            <a href="admin.php?delete=<?= urlencode($u['id'] ?? '') ?>" class="btn-mini btn-del"
                                               onclick="return confirm('确定要删除该用户吗？此操作不可恢复！');">删除</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function filterUsers() {
    const q = document.getElementById('searchInput').value.trim().toLowerCase();
    const rows = document.querySelectorAll('#userTable tbody tr');
    rows.forEach(row => {
        const nickCell = row.querySelector('td:nth-child(4)');
        const mailCell = row.querySelector('td:nth-child(3)');
        if (!nickCell || !mailCell) return;
        const text = (nickCell.innerText + ' ' + mailCell.innerText).toLowerCase();
        row.style.display = text.includes(q) ? '' : 'none';
    });
}
</script>

</body>
</html>