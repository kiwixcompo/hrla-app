<?php
// Partial: 6-digit code entry form
// $prefillEmail may be set by the parent page
$prefillEmail = $prefillEmail ?? '';
?>
<form id="codeForm" autocomplete="off">
    <label for="verifyEmail" style="display:block;text-align:left;font-size:0.88rem;font-weight:600;margin-bottom:6px;color:#374151;">
        Email address
    </label>
    <input
        type="email"
        id="verifyEmail"
        class="email-field"
        placeholder="you@example.com"
        value="<?php echo htmlspecialchars($prefillEmail); ?>"
        required
    >

    <label style="display:block;text-align:left;font-size:0.88rem;font-weight:600;margin-bottom:6px;color:#374151;">
        Verification code
    </label>
    <div class="code-inputs" id="codeInputs">
        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 1">
        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 2">
        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 3">
        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 4">
        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 5">
        <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]" aria-label="Digit 6">
    </div>

    <div id="formMsg" style="display:none;" class="msg"></div>

    <button type="submit" class="btn-verify" id="submitBtn">
        <i class="fas fa-check-circle"></i> Verify Account
    </button>
</form>

<div class="resend-row">
    Didn't receive a code?
    <button type="button" id="resendBtn">Resend code</button>
    <span id="resendTimer" style="display:none;"></span>
</div>

<div style="margin-top:16px;font-size:0.85rem;color:#666;">
    <a href="<?php echo appUrl('login.php'); ?>">← Back to Sign In</a>
</div>

<script>
(function () {
    const inputs  = Array.from(document.querySelectorAll('#codeInputs input'));
    const form    = document.getElementById('codeForm');
    const submitBtn = document.getElementById('submitBtn');
    const msgBox  = document.getElementById('formMsg');
    const resendBtn = document.getElementById('resendBtn');
    const resendTimer = document.getElementById('resendTimer');

    // Auto-advance & backspace
    inputs.forEach((inp, i) => {
        inp.addEventListener('input', () => {
            inp.value = inp.value.replace(/\D/g, '').slice(-1);
            inp.classList.toggle('filled', inp.value !== '');
            if (inp.value && i < inputs.length - 1) inputs[i + 1].focus();
        });
        inp.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !inp.value && i > 0) inputs[i - 1].focus();
        });
    });

    // Paste support
    inputs[0].addEventListener('paste', e => {
        e.preventDefault();
        const digits = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
        digits.split('').forEach((d, i) => {
            if (inputs[i]) { inputs[i].value = d; inputs[i].classList.add('filled'); }
        });
        const next = inputs[Math.min(digits.length, 5)];
        if (next) next.focus();
    });

    function getCode() { return inputs.map(i => i.value).join(''); }

    function showMsg(text, type) {
        msgBox.className = 'msg msg-' + type;
        msgBox.textContent = text;
        msgBox.style.display = 'block';
    }

    form.addEventListener('submit', async e => {
        e.preventDefault();
        const code  = getCode();
        const email = document.getElementById('verifyEmail').value.trim();

        if (code.length !== 6) { showMsg('Please enter all 6 digits.', 'error'); return; }
        if (!email)            { showMsg('Please enter your email address.', 'error'); return; }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        msgBox.style.display = 'none';

        try {
            const res  = await fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ action: 'verify_code', code, email })
            });
            const data = await res.json();

            if (data.success) {
                showMsg('✅ Email verified! Redirecting to sign in...', 'success');
                setTimeout(() => { location.href = '<?php echo appUrl('login.php?verified=true'); ?>'; }, 1500);
            } else {
                showMsg(data.error || 'Verification failed. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verify Account';
            }
        } catch (err) {
            showMsg('Network error. Please try again.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Verify Account';
        }
    });

    // Resend with 60-second cooldown
    let cooldown = 0;
    function startCooldown(secs) {
        cooldown = secs;
        resendBtn.style.display = 'none';
        resendTimer.style.display = 'inline';
        const tick = setInterval(() => {
            cooldown--;
            resendTimer.textContent = 'Resend in ' + cooldown + 's';
            if (cooldown <= 0) {
                clearInterval(tick);
                resendBtn.style.display = 'inline';
                resendTimer.style.display = 'none';
            }
        }, 1000);
    }

    resendBtn.addEventListener('click', async () => {
        const email = document.getElementById('verifyEmail').value.trim();
        if (!email) { showMsg('Please enter your email address first.', 'error'); return; }

        resendBtn.disabled = true;
        try {
            const res  = await fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'resend_verification',
                    email,
                    csrf_token: '<?php echo function_exists("generateCSRFToken") ? generateCSRFToken() : ""; ?>'
                })
            });
            const data = await res.json();
            if (data.success) {
                showMsg('A new code has been sent to your email.', 'success');
                startCooldown(60);
            } else {
                showMsg(data.error || 'Could not resend. Please try again.', 'error');
                resendBtn.disabled = false;
            }
        } catch (err) {
            showMsg('Network error. Please try again.', 'error');
            resendBtn.disabled = false;
        }
    });
})();
</script>
