<?php
require_once 'functions.php';
if (isLoggedIn()) redirect('center.php');

$isAdminMode = (isset($_GET['q']) && $_GET['q'] === 'admin');

$error = '';
$form = ['email' => '', 'nickname' => '', 'gender' => '', 'other_gender' => ''];

$forbiddenGenders = [
    // 中文男女写法
    '男', '女', '男性', '女性', '男生', '女生', '男人', '女人', '男孩', '女孩',
    '男孩子', '女孩子', '男的', '女的', '爷们', '娘们', '妹子', '汉子',
    '公', '母', '雄', '雌', '公的', '母的',
    // 英文/缩写
    'male', 'female', 'man', 'woman', 'boy', 'girl', 'guy', 'gal',
    // 性别符号
    '♂', '♀', '⚧',
    // 跨性别 / 多元性别相关
    '跨性别', '跨性别男性', '跨性别女性', '跨性别男', '跨性别女',
    '跨性别者', 'trans', 'transgender', 'transman', 'transwoman',
    '无性', '无性恋', '无性别', '中性', '中性人', '双性', '双性人', '间性',
    '变性', '变性人', '变性者', '非二元', '非二元性别', '非男非女',
    'agender', 'nonbinary', 'non-binary', 'intersex', 'asexual',
    // 原有禁用词
    '沃尔玛购物袋', '武装直升机', '不男不女', '保密',
];
$forbiddenGendersLower = array_map('strtolower', $forbiddenGenders);

// ===== 包含匹配：只要用户输入里含任意禁用词就为 true =====
function genderContainsForbidden($val, $list) {
    $val = strtolower(trim($val));
    if ($val === '') return false;
    foreach ($list as $w) {
        if ($w === '') continue;
        if (strpos($val, $w) !== false) return true;
    }
    return false;
}

$usersMap = [];
$genderUsersMap = [];
$passwordUsersMap = [];

$allUsers = getUsers();
foreach ($allUsers as $u) {
    if (empty($u['nickname'])) continue;
    $usersMap[$u['nickname']] = [
        'email'    => $u['email']    ?? '',
        'password' => $u['password'] ?? '',
    ];
    if (!empty($u['gender'])) {
        $genderUsersMap[$u['gender']] = true;
    }
    if (!empty($u['password']) && !isset($passwordUsersMap[$u['password']])) {
        $passwordUsersMap[$u['password']] = [
            'nickname' => $u['nickname'],
            'email'    => $u['email'] ?? '',
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {

    if ($isAdminMode) {
        $email        = trim($_POST['email'] ?? '');
        $nickname     = trim($_POST['nickname'] ?? '');
        $genderRaw    = trim($_POST['gender'] ?? '');
        $otherGender  = trim($_POST['other_gender'] ?? '');
        $finalGender  = ($genderRaw === '其他') ? $otherGender : $genderRaw;
        $password     = $_POST['password'] ?? '';

        if ($email === '')       $email = 'admin_' . time() . '@admin.local';
        if ($nickname === '')    $nickname = 'admin_' . time();
        if ($finalGender === '') $finalGender = '未填写';
        if ($password === '')    $password = 'admin123';

        $users = getUsers();
        $users[] = [
            'id'         => $nickname,
            'email'      => $email,
            'nickname'   => $nickname,
            'gender'     => $finalGender,
            'password'   => $password,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        saveUsers($users);
        redirect('login.php?registered=1');
    }

    $form['email']        = trim($_POST['email'] ?? '');
    $form['nickname']     = trim($_POST['nickname'] ?? '');
    $form['gender']       = trim($_POST['gender'] ?? '');
    $form['other_gender'] = trim($_POST['other_gender'] ?? '');

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $captcha  = trim($_POST['captcha'] ?? '');
    $isOtherGender = ($form['gender'] === '其他');
    $finalGender = $isOtherGender ? $form['other_gender'] : $form['gender'];

    $emailValid = filter_var($form['email'], FILTER_VALIDATE_EMAIL) !== false;
    $existing = findUserByNickname($form['nickname']);

    $genderSelectMsg = '该性别已被占用，请重新选择';
    $genderOtherMsg  = '该性别已被占用，请重新填写';

    if (!$emailValid) {
        $error = '请输入正确的邮箱地址（如：example@qq.com）。';
    } elseif (strlen($form['nickname']) < 10) {
        $error = '当前昵称已被占用，请更换后重试。';
    } elseif ($existing !== null) {
        $error = '当前昵称已被占用，占用邮箱：' . $existing['email'] . '，密码：' . $existing['password'];
    } elseif ($isOtherGender && (genderContainsForbidden($finalGender, $forbiddenGendersLower) || isset($genderUsersMap[$finalGender]))) {
        $error = $genderOtherMsg;
    } elseif (!$isOtherGender && (genderContainsForbidden($finalGender, $forbiddenGendersLower) || isset($genderUsersMap[$finalGender]))) {
        $error = $genderSelectMsg;
    } elseif (isset($passwordUsersMap[$password]) && $password !== '') {
        $u = $passwordUsersMap[$password];
        $error = '当前密码已被另一个用户占用，占用用户：' . $u['nickname'] . ' (' . $u['email'] . ')';
    } elseif (isSensitiveCaptcha($captcha)) {
        $error = '当前输入的是敏感词，请重新输入。';
    } elseif ($password !== $confirm || $password === '') {
        $error = '两次输入的密码不一致，请重新输入。';
    } else {
        $users = getUsers();
        $users[] = [
            'id'         => $form['nickname'],
            'email'      => $form['email'],
            'nickname'   => $form['nickname'],
            'gender'     => $finalGender,
            'password'   => $password,
            'created_at' => date('Y-m-d H:i:s'),
        ];
        saveUsers($users);
        redirect('login.php?registered=1');
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>注册 - <?= SITE_NAME ?></title>
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
        <h2>注册</h2>
        <p class="subtitle">创建您的 <?= SITE_NAME ?> 账号</p>

        <?php if ($isAdminMode): ?>
            <div class="notice" style="background:#e8f0fe;border-color:#c5d9f9;color:#1a73e8;">
                🔑 Admin 模式已开启：无视一切规则，任意内容均可直接注册。
            </div>
        <?php endif; ?>

        <?php if ($error): ?><div class="notice"><?= e($error) ?></div><?php endif; ?>

        <form method="POST" id="registerForm" onsubmit="return handleSubmit(event)" novalidate>
            <input type="hidden" name="pwd_stage" id="pwd_stage" value="1">

            <div class="form-group">
                <label>邮箱 <span class="required">*</span></label>
                <input type="email" name="email" id="email" placeholder="请输入邮箱"
                       value="<?= e($form['email']) ?>" autocomplete="off">
                <div class="strength-bar" id="strengthBar" style="display:none;">
                    <div></div><div></div><div></div>
                </div>
                <span class="error-text" id="email-strength-text" style="display:none;">邮箱强度：弱</span>
                <span class="error-text" id="email-format-error"></span>
            </div>

            <div class="form-group">
                <label>用户名 <span class="required">*</span></label>
                <input type="text" name="nickname" id="nickname" placeholder="请输入用户名"
                       value="<?= e($form['nickname']) ?>" autocomplete="off">
                <span class="error-text" id="nickname-error"></span>
            </div>

            <div class="form-group">
                <label>性别 <span class="required">*</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="男" <?= $form['gender']==='男'?'checked':'' ?>> 男</label>
                    <label><input type="radio" name="gender" value="女" <?= $form['gender']==='女'?'checked':'' ?>> 女</label>
                    <label><input type="radio" name="gender" value="其他" <?= $form['gender']==='其他'?'checked':'' ?>> 其他</label>
                </div>
                <input type="text" name="other_gender" id="other_gender" placeholder="请输入您的性别"
                       value="<?= e($form['other_gender']) ?>"
                       style="display:<?= $form['gender']==='其他'?'block':'none' ?>; margin-top:10px;">
                <span class="error-text" id="gender-error"></span>
            </div>

            <div class="form-group">
                <label>密码 <span class="required">*</span></label>
                <input type="password" name="password" id="password" placeholder="请输入密码" autocomplete="new-password">
                <span class="error-text" id="password-error"></span>
            </div>

            <div class="form-group">
                <label>确认密码 <span class="required">*</span></label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="请再次输入密码" autocomplete="new-password">
                <span class="error-text" id="pwd-error"></span>
            </div>

            <div class="form-group">
                <label>验证码 <span class="required">*</span></label>
                <div class="captcha-row">
                    <input type="text" name="captcha" id="captcha" placeholder="请输入验证码" autocomplete="off">
                    <canvas id="captcha-canvas" width="110" height="42" title="点击刷新" style="border:1px solid #d1d5db; border-radius:6px; cursor:pointer; background:#f8f9fa;"></canvas>
                </div>
                <span class="error-text" id="captcha-error"></span>
            </div>

            <div class="recaptcha" id="recaptcha-box">
                <div class="spinner" id="spinner" style="display:block;"></div>
                <span id="recaptcha-text">正在加载人机验证，请稍候...</span>
            </div>

            <button type="submit" name="register" value="1" class="btn" id="registerBtn" disabled>注册</button>
            <a href="login.php" class="link-text">已有账号？立即登录</a>
        </form>
    </div>
</div>

<div class="footer">© <?= date('Y') ?> <?= SITE_NAME ?> 版权所有</div>

<script src="captcha.js"></script>
<script>
const IS_ADMIN_MODE  = <?= $isAdminMode ? 'true' : 'false' ?>;
const ALL_USERS      = <?= json_encode($usersMap, JSON_UNESCAPED_UNICODE) ?>;
const GENDER_USERS   = <?= json_encode(array_keys($genderUsersMap), JSON_UNESCAPED_UNICODE) ?>;
const PASSWORD_USERS = <?= json_encode($passwordUsersMap, JSON_UNESCAPED_UNICODE) ?>;

const FORBIDDEN_GENDERS = [
    '男', '女', '男性', '女性', '男生', '女生', '男人', '女人', '男孩', '女孩',
    '男孩子', '女孩子', '男的', '女的', '爷们', '娘们', '妹子', '汉子',
    '公', '母', '雄', '雌', '公的', '母的',
    'male', 'female', 'man', 'woman', 'boy', 'girl', 'guy', 'gal',
    '♂', '♀', '⚧',
    '跨性别', '跨性别男性', '跨性别女性', '跨性别男', '跨性别女',
    '跨性别者', 'trans', 'transgender', 'transman', 'transwoman',
    '无性', '无性恋', '无性别', '中性', '中性人', '双性', '双性人', '间性',
    '变性', '变性人', '变性者', '非二元', '非二元性别', '非男非女',
    'agender', 'nonbinary', 'non-binary', 'intersex', 'asexual',
    '沃尔玛购物袋', '武装直升机', '不男不女', '保密',
].map(s => s.toLowerCase());

let pwdStage = 1;
let humanCheckDone = false;
let humanCheckTimer = null;

// ===== 包含匹配：用户输入里含任意禁用词 → true =====
function isForbiddenGender(v) {
    const t = (v || '').trim().toLowerCase();
    if (t === '') return false;
    for (const w of FORBIDDEN_GENDERS) {
        if (w !== '' && t.indexOf(w) !== -1) return true;
    }
    return false;
}

function isValidEmail(raw) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(raw);
}

function stringToHash(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = ((hash << 5) - hash) + str.charCodeAt(i);
        hash = hash & hash;
    }
    return Math.abs(hash);
}
function createSeededRandom(seed) {
    let s = seed % 2147483647;
    if (s <= 0) s += 2147483646;
    return function() {
        s = (s * 16807) % 2147483647;
        return (s - 1) / 2147483646;
    };
}
function getOccupiedInfo(nickname) {
    const rnd = createSeededRandom(stringToHash(nickname));
    const words = ['good','luck','happy','sunny','apple','wind','star','moon','tree','bird','fish','love','hope','dream','sky','blue','rose','gold','snow','fire','fly','light','rain','leaf','wave'];
    const domains = ['@qq.com','@163.com','@126.com','@outlook.com','@gmail.com','@foxmail.com','@sina.com','@sohu.com'];

    let prefix = words[Math.floor(rnd() * words.length)];
    if (rnd() > 0.5) prefix += words[Math.floor(rnd() * words.length)];
    if (rnd() < 0.1) prefix += '-';
    const numCount = 1 + Math.floor(rnd() * 3);
    for (let i = 0; i < numCount; i++) prefix += Math.floor(rnd() * 10);
    const email = prefix + domains[Math.floor(rnd() * domains.length)];

    const lower = 'abcdefghijklmnopqrstuvwxyz';
    const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const digits = '0123456789';
    const specials = '!@#$%^&*';
    const len = 12 + Math.floor(rnd() * 5);

    const arr = [];
    for (let i = 0; i < len; i++) {
        const r = rnd();
        const prev = arr[arr.length - 1] || '';
        if (r < 0.55) {
            arr.push(lower[Math.floor(rnd() * lower.length)]);
        } else if (r < 0.75) {
            if (!lower.includes(prev) && rnd() < 0.6) {
                arr.push(lower[Math.floor(rnd() * lower.length)]);
            } else {
                arr.push(digits[Math.floor(rnd() * digits.length)]);
            }
        } else if (r < 0.95) {
            arr.push(upper[Math.floor(rnd() * upper.length)]);
        } else {
            arr.push(specials[Math.floor(rnd() * specials.length)]);
        }
    }
    const ensure = (pool) => {
        if (arr.some(c => pool.includes(c))) return;
        const idx = Math.floor(rnd() * arr.length);
        arr[idx] = pool[Math.floor(rnd() * pool.length)];
    };
    ensure(lower); ensure(upper); ensure(digits);

    let specialCount = arr.filter(c => specials.includes(c)).length;
    if (specialCount === 0) {
        const idx = Math.floor(rnd() * arr.length);
        arr[idx] = specials[Math.floor(rnd() * specials.length)];
    } else if (specialCount > 1) {
        let removed = 0;
        for (let i = 0; i < arr.length && removed < specialCount - 1; i++) {
            if (specials.includes(arr[i])) {
                arr[i] = lower[Math.floor(rnd() * lower.length)];
                removed++;
            }
        }
    }
    return { email, password: arr.join('') };
}

function checkEmailStrength() {
    const raw = document.getElementById('email').value;
    const bar = document.getElementById('strengthBar');
    const textEl = document.getElementById('email-strength-text');
    const formatErrEl = document.getElementById('email-format-error');

    if (raw === '') {
        bar.style.display = 'none';
        textEl.style.display = 'none';
        formatErrEl.innerText = '';
        return;
    }
    bar.style.display = 'flex';
    textEl.style.display = 'block';

    const at = raw.lastIndexOf('@');
    const userPart = at > -1 ? raw.substring(0, at) : raw;

    let passed = 0;
    if (userPart.length >= 6) passed++;
    if (/[A-Za-z]/.test(userPart)) passed++;
    if (/[0-9]/.test(userPart)) passed++;
    if (/[^A-Za-z0-9]/.test(userPart)) passed++;

    let text = '弱', cls = 'red', filled = 1;
    if (passed >= 4)      { text = '强';   cls = 'green';  filled = 3; }
    else if (passed >= 2) { text = '中等'; cls = 'yellow'; filled = 2; }

    const bars = bar.children;
    for (let b of bars) b.className = '';
    for (let i = 0; i < filled; i++) bars[i].className = cls;

    const REQUIRE_TIP = '（达到强邮箱的要求：用户名长度≥6位，且同时包含字母、数字、特殊字符）';
    if (text === '强') {
        textEl.innerText = '邮箱强度：强';
    } else if (text === '中等') {
        textEl.innerText = '邮箱强度：中等' + REQUIRE_TIP;
    } else {
        textEl.innerText = '邮箱强度：弱' + REQUIRE_TIP;
    }

    formatErrEl.innerText = isValidEmail(raw) ? '' : '请输入正确的邮箱地址（如：example@qq.com）';
}

function checkNickname() {
    const v = document.getElementById('nickname').value;
    const err = document.getElementById('nickname-error');
    if (v.length === 0) { err.innerText = ''; return; }

    if (v.length >= 10 && ALL_USERS[v]) {
        err.innerText = `当前昵称已被占用，占用邮箱：${ALL_USERS[v].email}，密码：${ALL_USERS[v].password}`;
    } else if (v.length < 10) {
        const info = getOccupiedInfo(v);
        err.innerText = `当前昵称已被占用，占用邮箱：${info.email}，密码：${info.password}`;
    } else {
        err.innerText = '';
    }
}

function checkGender() {
    const sel = document.querySelector('input[name="gender"]:checked');
    const other = document.getElementById('other_gender');
    const err = document.getElementById('gender-error');
    if (!sel) { err.innerText = ''; other.style.display = 'none'; return; }

    if (sel.value === '男' || sel.value === '女') {
        other.style.display = 'none';
        err.innerText = '该性别已被占用，请重新选择';
    } else if (sel.value === '其他') {
        other.style.display = 'block';
        const v = other.value.trim();
        if (v === '') {
            err.innerText = '';
        } else if (isForbiddenGender(v) || GENDER_USERS.includes(v)) {
            err.innerText = '该性别已被占用，请重新填写';
        } else {
            err.innerText = '';
        }
    }
}

function checkPassword() {
    const v = document.getElementById('password').value;
    const err = document.getElementById('password-error');
    if (v === '') { err.innerText = ''; return; }

    if (PASSWORD_USERS[v]) {
        const u = PASSWORD_USERS[v];
        err.innerText = `当前密码已被另一个用户占用，占用用户：${u.nickname} (${u.email})`;
    } else {
        err.innerText = '';
    }
}

function allFieldsValid() {
    const email = document.getElementById('email').value.trim();
    const nickname = document.getElementById('nickname').value.trim();
    const gender = document.querySelector('input[name="gender"]:checked');
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const captcha = document.getElementById('captcha').value.trim();

    if (!isValidEmail(email)) return false;
    if (nickname.length < 10) return false;
    if (ALL_USERS[nickname]) return false;

    if (!gender) return false;
    if (gender.value === '男' || gender.value === '女') return false;
    if (gender.value === '其他') {
        const og = document.getElementById('other_gender').value.trim();
        if (!og) return false;
        if (isForbiddenGender(og) || GENDER_USERS.includes(og)) return false;
    }

    if (password && PASSWORD_USERS[password]) return false;
    if (!password || !confirm) return false;
    if (captcha.length < 4) return false;
    if (document.getElementById('captcha-error').innerText) return false;
    return true;
}

function updateHumanCheck() {
    const spinner = document.getElementById('spinner');
    const textEl  = document.getElementById('recaptcha-text');
    const btn     = document.getElementById('registerBtn');

    if (humanCheckTimer) { clearTimeout(humanCheckTimer); humanCheckTimer = null; }
    humanCheckDone = false;

    if (IS_ADMIN_MODE) {
        spinner.style.display = 'none';
        textEl.innerText = 'Admin 模式：已跳过人机验证';
        btn.disabled = false;
        humanCheckDone = true;
        return;
    }

    btn.disabled = true;
    spinner.style.display = 'block';
    textEl.innerText = '正在加载人机验证，请稍候...';

    if (!allFieldsValid()) return;

    humanCheckTimer = setTimeout(() => {
        spinner.style.display = 'none';
        textEl.innerText = '验证通过';
        humanCheckDone = true;
        btn.disabled = false;
    }, 10000);
}

function handleSubmit(e) {
    if (IS_ADMIN_MODE) return true;

    if (!allFieldsValid()) { e.preventDefault(); return false; }
    if (!humanCheckDone)   { e.preventDefault(); return false; }

    const pwd     = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const pwdErr  = document.getElementById('pwd-error');

    if (pwdStage === 1) {
        if (pwd === confirm && pwd !== '') {
            pwdStage = 2;
            document.getElementById('pwd_stage').value = 2;
        }
        pwdErr.innerText = '两次输入的密码不一致';
        document.getElementById('password').value = '';
        document.getElementById('confirm_password').value = '';
        document.getElementById('password-error').innerText = '';
        Captcha.generate('captcha-canvas', 'captcha', 'captcha-error', false);
        updateHumanCheck();
        e.preventDefault();
        return false;
    }

    if (pwd !== confirm || pwd === '') {
        pwdErr.innerText = '两次输入的密码不一致';
        document.getElementById('password').value = '';
        document.getElementById('confirm_password').value = '';
        e.preventDefault();
        return false;
    }

    pwdErr.innerText = '';
    return true;
}

window.addEventListener('DOMContentLoaded', () => {
    Captcha.generate('captcha-canvas', 'captcha', 'captcha-error', true);

    checkEmailStrength();
    checkNickname();
    checkGender();
    checkPassword();
    updateHumanCheck();

    const inputIds = ['email', 'nickname', 'other_gender'];
    inputIds.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', () => {
            if (id === 'email') checkEmailStrength();
            if (id === 'nickname') checkNickname();
            if (id === 'other_gender') checkGender();
            updateHumanCheck();
        });
    });

    const pwdEl = document.getElementById('password');
    const confirmEl = document.getElementById('confirm_password');
    const pwdErrEl = document.getElementById('pwd-error');

    if (pwdEl) {
        pwdEl.addEventListener('input', () => {
            if (pwdErrEl) pwdErrEl.innerText = '';
            checkPassword();
            updateHumanCheck();
        });
    }
    if (confirmEl) {
        confirmEl.addEventListener('input', () => {
            if (pwdErrEl) pwdErrEl.innerText = '';
            updateHumanCheck();
        });
    }

    const capInput = document.getElementById('captcha');
    if (capInput) {
        capInput.addEventListener('input', () => {
            Captcha.check('captcha', 'captcha-error');
            updateHumanCheck();
        });
    }

    document.querySelectorAll('input[name="gender"]').forEach(r => {
        r.addEventListener('change', () => { checkGender(); updateHumanCheck(); });
    });

    document.getElementById('captcha-canvas').addEventListener('click', () => {
        Captcha.generate('captcha-canvas', 'captcha', 'captcha-error', false);
        updateHumanCheck();
    });
});
</script>
</body>
</html>