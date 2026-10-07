// captcha.js —— 全站统一验证码逻辑
(function (global) {
    'use strict';

    // 敏感字符池
    const SENSITIVE_CHARS = ['9', '1', '7', '8', 'c', 'n', 'm', 'j', 'b', '3'];

    // 正常字符池：完全排除敏感字符（含大小写）
    // 排除字母: b c j m n（及大写 B C J M N）
    // 排除数字: 1 3 7 8 9
    const NORMAL_CHARS = 'adefghiklopqrstuvwxyzADEFGHIKLOPQRSTUVWXYZ02456';

    // 保底阈值：刷新超过 20 次 → 之后永远只出正常字符
    const THRESHOLD = 20;
    const STORAGE_KEY = 'captcha_refresh_count';

    function getCount() {
        try {
            const v = parseInt(sessionStorage.getItem(STORAGE_KEY) || '0', 10);
            return isNaN(v) ? 0 : v;
        } catch (e) { return 0; }
    }
    function incCount() {
        const v = getCount() + 1;
        try { sessionStorage.setItem(STORAGE_KEY, String(v)); } catch (e) {}
        return v;
    }
    function resetCount() {
        try { sessionStorage.removeItem(STORAGE_KEY); } catch (e) {}
    }

    function randomCode(isFirstTime) {
        const count = getCount();
        const forceNormal = count > THRESHOLD;
        let code = '';

        if (isFirstTime && !forceNormal) {
            // 首次进入页面：必然包含敏感字符
            for (let i = 0; i < 4; i++) {
                code += SENSITIVE_CHARS[Math.floor(Math.random() * SENSITIVE_CHARS.length)];
            }
        } else if (forceNormal) {
            // 保底：全部正常字符
            for (let i = 0; i < 4; i++) {
                code += NORMAL_CHARS[Math.floor(Math.random() * NORMAL_CHARS.length)];
            }
        } else {
            // 刷新时：70% 概率仍含敏感字符
            const sensitive = Math.random() < 0.7;
            for (let i = 0; i < 4; i++) {
                if (sensitive) {
                    code += SENSITIVE_CHARS[Math.floor(Math.random() * SENSITIVE_CHARS.length)];
                } else {
                    code += NORMAL_CHARS[Math.floor(Math.random() * NORMAL_CHARS.length)];
                }
            }
        }
        return code;
    }

    function draw(canvas, code) {
        const ctx = canvas.getContext('2d');
        const W = canvas.width, H = canvas.height;

        const grad = ctx.createLinearGradient(0, 0, W, H);
        grad.addColorStop(0, '#f0f4f9');
        grad.addColorStop(1, '#e8edf3');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, W, H);

        for (let i = 0; i < 80; i++) {
            ctx.fillStyle = `rgba(${Math.random()*255|0},${Math.random()*255|0},${Math.random()*255|0},0.5)`;
            ctx.fillRect(Math.random() * W, Math.random() * H, 1, 1);
        }
        for (let i = 0; i < 5; i++) {
            ctx.strokeStyle = `rgba(${Math.random()*200|0},${Math.random()*200|0},${Math.random()*200|0},0.7)`;
            ctx.lineWidth = 1 + Math.random();
            ctx.beginPath();
            ctx.moveTo(Math.random() * W, Math.random() * H);
            ctx.bezierCurveTo(Math.random()*W, Math.random()*H, Math.random()*W, Math.random()*H, Math.random()*W, Math.random()*H);
            ctx.stroke();
        }
        for (let i = 0; i < 12; i++) {
            ctx.beginPath();
            ctx.arc(Math.random()*W, Math.random()*H, 1 + Math.random()*2, 0, Math.PI*2);
            ctx.fillStyle = `rgba(${Math.random()*200|0},${Math.random()*200|0},${Math.random()*200|0},0.6)`;
            ctx.fill();
        }
        for (let i = 0; i < code.length; i++) {
            const ch = code[i];
            ctx.save();
            const x = 16 + i * 22 + Math.random() * 6;
            const y = H / 2 + (Math.random() - 0.5) * 10;
            ctx.translate(x, y);
            ctx.rotate((Math.random() - 0.5) * 0.7);
            ctx.font = `${18 + Math.floor(Math.random() * 6)}px 'Courier New', monospace`;
            ctx.fillStyle = `rgb(${Math.random()*100|0},${Math.random()*100|0},${Math.random()*100|0})`;
            ctx.textBaseline = 'middle';
            ctx.fillText(ch, 0, 0);
            ctx.restore();
        }
    }

    function hasSensitive(text) {
        const v = String(text || '').toLowerCase();
        for (const c of SENSITIVE_CHARS) {
            if (v.indexOf(c) !== -1) return true;
        }
        return false;
    }

    global.Captcha = {
        SENSITIVE_CHARS: SENSITIVE_CHARS,
        /**
         * 生成验证码
         * @param {string} canvasId   canvas 元素 id
         * @param {string} inputId    输入框 id
         * @param {string} errorId    错误提示元素 id
         * @param {boolean} isFirst   是否是页面首次生成（true 表示首次进入，不计数）
         */
        generate: function (canvasId, inputId, errorId, isFirst) {
            const canvas = document.getElementById(canvasId);
            const input  = document.getElementById(inputId);
            const error  = document.getElementById(errorId);
            if (!canvas) return;
            if (!isFirst) incCount();
            const code = randomCode(!!isFirst);
            draw(canvas, code);
            if (input) input.value = '';
            if (error) error.innerText = '';
        },
        /**
         * 校验输入框是否含有敏感字符
         */
        check: function (inputId, errorId) {
            const input = document.getElementById(inputId);
            const error = document.getElementById(errorId);
            if (!input || !error) return;
            error.innerText = hasSensitive(input.value) ? '当前输入的是敏感词，请重新输入' : '';
        },
        hasSensitive: hasSensitive,
        resetCounter: resetCount,
        getCount: getCount
    };
})(window);