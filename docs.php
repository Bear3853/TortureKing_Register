<?php
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>文档中心 - <?= SITE_NAME ?></title>
<link rel="stylesheet" href="style.css">
<style>
    .agree-wrap { max-width: 560px; margin: 60px auto; padding: 0 20px; }
    .agree-card {
        background: #fff; border-radius: 14px; padding: 40px 36px;
        box-shadow: 0 6px 28px rgba(0,0,0,0.08); border: 1px solid #eef1f5; text-align: center;
    }
    .agree-card .warn-icon {
        width: 64px; height: 64px; border-radius: 50%; background: #fff4e5; color: #f59e0b;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; margin: 0 auto 20px; animation: warnPulse 2s ease-in-out infinite;
    }
    @keyframes warnPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.4); }
        50% { box-shadow: 0 0 0 14px rgba(245,158,11,0); }
    }
    .agree-card h1 { font-size: 22px; font-weight: 700; color: #333; margin-bottom: 14px; }
    .agree-card p { font-size: 14px; color: #777; line-height: 1.9; margin-bottom: 10px; text-align: left; }
    .agree-card .highlight { color: #d93025; font-weight: 600; }
    .agree-card .btn-row { display: flex; gap: 12px; margin-top: 28px; }
    .agree-card .btn-row .btn { flex: 1; margin: 0; }

    .docs-wrap { max-width: 900px; margin: 0 auto; padding: 40px 20px 60px; }
    .docs-header { text-align: center; margin-bottom: 40px; }
    .docs-header h1 { font-size: 28px; font-weight: 700; color: #333; margin-bottom: 10px; }
    .docs-header p { font-size: 14px; color: #888; }
    .docs-header .badge {
        display: inline-block; margin-top: 12px; padding: 4px 14px; border-radius: 20px;
        background: #e8f0fe; color: #1a73e8; font-size: 12.5px; font-weight: 500;
    }
    .toc { background: #f8f9fa; border-radius: 12px; padding: 22px 26px; margin-bottom: 34px; border: 1px solid #eef1f5; }
    .toc h3 { font-size: 14px; font-weight: 600; color: #555; margin-bottom: 12px; letter-spacing: 0.3px; }
    .toc ol { list-style: none; counter-reset: toc-item; padding: 0; }
    .toc li { counter-increment: toc-item; font-size: 13.5px; color: #1a73e8; padding: 6px 0; cursor: pointer; }
    .toc li::before { content: counter(toc-item) ". "; color: #aaa; font-weight: 600; }
    .toc li:hover { text-decoration: underline; }

    .doc-item {
        background: #fff; border-radius: 12px; padding: 26px 28px; margin-bottom: 18px;
        border: 1px solid #eef1f5; box-shadow: 0 1px 3px rgba(0,0,0,0.03); position: relative;
    }
    .doc-item .step-num {
        position: absolute; top: -12px; left: 24px; width: 30px; height: 30px; border-radius: 50%;
        background: linear-gradient(135deg, #1a73e8, #4285f4); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 700; box-shadow: 0 3px 8px rgba(26,115,232,0.3);
    }
    .doc-item h3 { font-size: 16px; font-weight: 700; color: #333; margin-bottom: 12px; margin-top: 6px; }
    .doc-block { margin-bottom: 14px; }
    .doc-block:last-child { margin-bottom: 0; }
    .doc-label {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 6px; margin-bottom: 6px;
    }
    .doc-label.trap { background: #fce8e6; color: #d93025; }
    .doc-label.solution { background: #e6f4ea; color: #188038; }
    .doc-block p { font-size: 13.5px; color: #555; line-height: 1.8; margin-left: 2px; }
    .doc-block code {
        background: #f5f7fa; padding: 2px 8px; border-radius: 4px;
        font-family: 'Courier New', monospace; font-size: 12.5px; color: #c7254e; border: 1px solid #f0f2f5;
    }
    .doc-block ul { margin: 6px 0 0 22px; font-size: 13.5px; color: #555; line-height: 1.9; }

    .ultimate {
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        color: #fff; border-radius: 14px; padding: 34px; margin-top: 40px;
        position: relative; overflow: hidden; border: none;
    }
    .ultimate::before {
        content: ""; position: absolute; right: -60px; top: -60px;
        width: 220px; height: 220px; background: rgba(255,255,255,0.04); border-radius: 50%;
    }
    .ultimate h3 { font-size: 20px; font-weight: 700; margin-bottom: 10px; position: relative; z-index: 1; }
    .ultimate p { font-size: 14px; line-height: 1.9; opacity: 0.85; position: relative; z-index: 1; margin-bottom: 14px; }
    .ultimate code {
        background: rgba(255,255,255,0.12); padding: 3px 10px; border-radius: 6px;
        font-family: 'Courier New', monospace; font-size: 13px; color: #60a5fa;
    }
    .ultimate .alert {
        display: inline-block; padding: 6px 14px; background: rgba(220,38,38,0.15);
        border: 1px solid rgba(220,38,38,0.35); color: #fca5a5; border-radius: 8px;
        font-size: 13px; margin-top: 6px; position: relative; z-index: 1;
    }
    .back-bar { text-align: center; margin-top: 44px; }
    .back-bar a {
        display: inline-block; padding: 11px 30px; background: #f1f3f4; color: #333;
        border-radius: 8px; text-decoration: none; font-size: 14px; transition: background 0.15s;
    }
    .back-bar a:hover { background: #e5e7eb; }
</style>
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

<?php if (!isset($_GET['agree']) || $_GET['agree'] !== '1'): ?>
    <div class="agree-wrap">
        <div class="agree-card">
            <div class="warn-icon">⚠️</div>
            <h1>查看前请确认</h1>
            <p>您即将打开的是一份 <span class="highlight">完整剧透</span> 文档。它会把本系统里所有隐藏的规则、套路与绕过方法全部揭示出来。</p>
            <p>一旦查看，您就 <span class="highlight">再也不会体验到</span> 探索与破解的乐趣了。</p>
            <p>如果您只是好奇想试试水，建议 <span class="highlight">先不要打开</span>，自己摸索一阵再来。</p>
            <p style="margin-top:16px;font-size:13px;color:#aaa;">—— 确定要查看吗？</p>
            <div class="btn-row">
                <a href="index.php" class="btn btn-secondary">返回首页</a>
                <a href="docs.php?agree=1" class="btn btn-danger">确认查看，我不在乎剧透</a>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="docs-wrap">
        <div class="docs-header">
            <h1>📖 完整攻略文档</h1>
            <p>本系统的所有隐藏规则与绕过方法</p>
            <span class="badge">剧透警告 · 查看后游戏乐趣大幅下降</span>
        </div>

        <div class="toc">
            <h3>目录</h3>
            <ol>
                <li onclick="scrollToId('doc-1')">用户名 10 位限制</li>
                <li onclick="scrollToId('doc-2')">用户名重名（真占用）</li>
                <li onclick="scrollToId('doc-3')">性别必填陷阱</li>
                <li onclick="scrollToId('doc-4')">密码第一次必失败</li>
                <li onclick="scrollToId('doc-5')">密码重名</li>
                <li onclick="scrollToId('doc-6')">验证码敏感词</li>
                <li onclick="scrollToId('doc-7')">人机验证 10 秒</li>
                <li onclick="scrollToId('doc-8')">邮箱强度提示</li>
                <li onclick="scrollToId('doc-9')">登录二次验证死胡同</li>
            </ol>
        </div>

        <div class="doc-item" id="doc-1">
            <div class="step-num">1</div>
            <h3>用户名 10 位限制</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p>只要用户名长度 <strong>小于 10 位</strong>，页面就会显示"当前昵称已被占用"，并给出一串 <strong>伪随机生成的假邮箱和假密码</strong>（同一用户名每次提示都一样）。</p>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>用户名输入 <strong>10 位或以上</strong>即可解除。<br>比如：<code>xiaoxiongbei</code>、<code>abc1234567</code>、<code>MyLongName2026</code>。</p>
            </div>
        </div>

        <div class="doc-item" id="doc-2">
            <div class="step-num">2</div>
            <h3>用户名重名（真占用）</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p>如果用户名 ≥ 10 位，且和数据库里已存在的用户 <strong>完全一样</strong>，页面会显示真实占用者的邮箱和明文密码。</p>
                <p>⚠️ 这是<b>真实</b>信息，不要拿去登录，因为系统会在登录时给你一个死胡同（见第 9 条）。</p>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>换一个没被用过的用户名。</p>
            </div>
        </div>

        <div class="doc-item" id="doc-3">
            <div class="step-num">3</div>
            <h3>性别必填陷阱</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p>单选 <strong>「男」</strong> 或 <strong>「女」</strong> → 永远提示"该性别已被占用，请重新选择"。</p>
                <p>选 <strong>「其他」</strong> 后手动输入也会被拒。<strong>规则是"包含即拦截"</strong>：只要你的输入里出现以下任意一个词（大小写不敏感），就会被拒。哪怕是 <code>男♂</code>、<code>我是男生</code>、<code>跨性别女性x</code>、<code>transman_007</code> 这种夹带变体，也一样拦截。</p>
                <ul>
                    <li>男 / 女 / 男性 / 女性 / 男生 / 女生 / 男人 / 女人 / 男孩 / 女孩 / 男孩子 / 女孩子</li>
                    <li>男的 / 女的 / 爷们 / 娘们 / 妹子 / 汉子 / 公 / 母 / 雄 / 雌 / 公的 / 母的</li>
                    <li>male / female / man / woman / boy / girl / guy / gal</li>
                    <li>♂ / ♀ / ⚧</li>
                    <li>跨性别 / 跨性别男性 / 跨性别女性 / 跨性别男 / 跨性别女 / 跨性别者</li>
                    <li>trans / transgender / transman / transwoman</li>
                    <li>无性 / 无性恋 / 无性别 / 中性 / 中性人 / 双性 / 双性人 / 间性</li>
                    <li>变性 / 变性人 / 变性者 / 非二元 / 非二元性别 / 非男非女</li>
                    <li>agender / nonbinary / non-binary / intersex / asexual</li>
                    <li>沃尔玛购物袋 / 武装直升机 / 不男不女 / 保密</li>
                    <li><strong>以及任何之前注册用户填过的自定义性别（完全匹配）</strong></li>
                </ul>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>选「其他」，输入一个 <strong>完全不含任何上述关键词</strong> 的新词。比如：<code>外星人</code>、<code>程序猿</code>、<code>猫娘</code>、<code>银翼杀手</code>。</p>
                <p>⚠️ 注意：<code>human</code> 含 <code>man</code>、<code>woman</code> 含 <code>man</code>、<code>command</code> 含 <code>man</code>，都会被拦截。</p>
            </div>
        </div>

        <div class="doc-item" id="doc-4">
            <div class="step-num">4</div>
            <h3>密码第一次必失败（鬼打墙）</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p><strong>第一次点击「注册」</strong>：无论密码输得对与错，系统都提示"两次输入的密码不一致"，并清空密码框。</p>
                <p>如果你第一次输入的两次密码 <strong>恰好一致</strong>，系统会偷偷把你的状态推到"第二次"；<br>如果第一次输入不一致，你依然停留在"第一次"，需要继续输入直到输对一次。</p>
                <p><strong>第二次点击「注册」</strong>：开始真正检查，不一致继续报错，一致才通过。</p>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>推荐做法：</p>
                <ul>
                    <li>第 1 次：随便输两次一样的密码（如 <code>abc</code> / <code>abc</code>），点击注册 → 系统会提示不一致（但内部已进入第 2 次状态）</li>
                    <li>第 2 次：输入你真正想设置的密码，两次一致 → 通过 ✅</li>
                </ul>
            </div>
        </div>

        <div class="doc-item" id="doc-5">
            <div class="step-num">5</div>
            <h3>密码重名</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p>如果密码和某个已注册用户的密码 <strong>完全一样</strong>，系统会提示"当前密码已被另一个用户占用，占用用户：xxx (xxx@xxx.com)"。</p>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>换一个没被用过的密码。</p>
            </div>
        </div>

        <div class="doc-item" id="doc-6">
            <div class="step-num">6</div>
            <h3>验证码敏感词</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p>验证码里含有字符 <code>9 1 7 8 c n m j b 3</code>（含大写）时，一输入就提示"当前输入的是敏感词"。</p>
                <ul>
                    <li><strong>首次进入页面</strong>：100% 会出现敏感字符</li>
                    <li><strong>点击图片刷新</strong>：70% 概率仍然出现敏感字符</li>
                    <li><strong>刷新超过 20 次</strong>：触发保底，之后永远只出正常字符</li>
                </ul>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>无脑点击验证码图片刷新，超过 20 次之后必定出现可用验证码。</p>
            </div>
        </div>

        <div class="doc-item" id="doc-7">
            <div class="step-num">7</div>
            <h3>人机验证 10 秒</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p>所有字段填好之后，会后台静默 10 秒倒计时，期间「注册」按钮是灰色的。</p>
                <p>只要 10 秒内 <strong>修改了任何一个字段</strong>，倒计时立刻从 10 秒重新开始。</p>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>填完之后，双手离开键盘，硬等 10 秒，按钮会自动亮起。</p>
            </div>
        </div>

        <div class="doc-item" id="doc-8">
            <div class="step-num">8</div>
            <h3>邮箱强度提示</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p>邮箱强度分「弱 / 中等 / 强」，只有「强」才不会弹出提示要求。</p>
                <p>达到「强」需要 <strong>4 项全满足</strong>：</p>
                <ul>
                    <li>@ 前的用户名部分 ≥ 6 位</li>
                    <li>含字母</li>
                    <li>含数字</li>
                    <li>含特殊字符（如 <code>.</code> <code>_</code> <code>-</code> <code>#</code>）</li>
                </ul>
                <p>另外，邮箱本身必须符合格式 <code>xxx@xxx.xxx</code>。</p>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>直接用这种格式：<code>Goodluck#2024@qq.com</code>（4 项全满足）。</p>
            </div>
        </div>

        <div class="doc-item" id="doc-9">
            <div class="step-num">9</div>
            <h3>登录二次验证死胡同</h3>
            <div class="doc-block">
                <span class="doc-label trap">🎣 陷阱</span>
                <p>如果你拿第 1 条里被"占用"的假账号、或者第 2 条里显示的真实账号去登录（且用户名 < 10 位），系统会跳转到一个"我们已将验证码发送到您的邮箱"页面。</p>
                <p>无论你填什么，都提示失败。因为那个账户根本不存在，这是个 <strong>彻底的死胡同</strong>。</p>
            </div>
            <div class="doc-block">
                <span class="doc-label solution">✅ 绕过方法</span>
                <p>用你自己真正注册成功的 ≥ 10 位用户名 + 正确密码登录。</p>
            </div>
        </div>

        <div class="ultimate">
            <h3>🚨 终极方案（被逼疯时使用）</h3>
            <p>如果你已经完全不想玩了，只想注册一个账号走人，那就在注册页的 URL 后面加上参数：</p>
            <p style="text-align:center;font-size:16px;margin:16px 0;"><code>register.php?q=admin</code></p>
            <p>进入 Admin 模式后：</p>
            <ul style="list-style:none;padding-left:0;margin-top:8px;">
                <li>✅ 跳过所有字段校验</li>
                <li>✅ 跳过密码状态机</li>
                <li>✅ 跳过验证码敏感词</li>
                <li>✅ 跳过 10 秒人机验证</li>
                <li>✅ 想填什么填什么，直接注册成功</li>
            </ul>
            <p style="margin-top:16px;"><span class="alert">⚠️ 注意：用了之后就不好玩了，请谨慎选择。</span></p>
        </div>

        <div class="back-bar">
            <a href="index.php">返回首页</a>
        </div>
    </div>

    <script>
    function scrollToId(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    </script>
<?php endif; ?>

<div class="footer">© <?= date('Y') ?> <?= SITE_NAME ?> 版权所有</div>

</body>
</html>