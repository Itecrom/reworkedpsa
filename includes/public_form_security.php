<?php

function startPublicFormSession() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        if (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') {
            ini_set('session.cookie_secure', '1');
        }
        session_start();
    }
}

function publicFormToken($formName) {
    startPublicFormSession();

    if (empty($_SESSION['public_form_tokens'][$formName])) {
        $_SESSION['public_form_tokens'][$formName] = bin2hex(random_bytes(32));
        $_SESSION['public_form_issued'][$formName] = time();
    }

    return $_SESSION['public_form_tokens'][$formName];
}

function publicFormCaptchaQuestion($formName) {
    startPublicFormSession();

    $captcha = $_SESSION['public_form_captchas'][$formName] ?? null;
    if (!$captcha || $captcha['expires_at'] < time()) {
        $left = random_int(2, 9);
        $right = random_int(1, 9);
        $_SESSION['public_form_captchas'][$formName] = [
            'question' => $left . ' + ' . $right,
            'answer' => (string)($left + $right),
            'expires_at' => time() + 1800,
        ];
    }

    return $_SESSION['public_form_captchas'][$formName]['question'];
}

function publicFormCaptchaField($formName, $fieldId) {
    $question = htmlspecialchars(publicFormCaptchaQuestion($formName), ENT_QUOTES, 'UTF-8');
    $fieldId = htmlspecialchars($fieldId, ENT_QUOTES, 'UTF-8');

    return '<div class="form-group public-captcha">'
        . '<label for="' . $fieldId . '">Security check: what is ' . $question . '?</label>'
        . '<input id="' . $fieldId . '" type="number" name="captcha_answer" class="form-control" inputmode="numeric" min="0" max="18" autocomplete="off" required>'
        . '</div>';
}

function validatePublicForm($formName) {
    startPublicFormSession();

    $submittedToken = $_POST['csrf_token'] ?? '';
    $expectedToken = $_SESSION['public_form_tokens'][$formName] ?? '';
    if (!is_string($submittedToken) || $expectedToken === '' || !hash_equals($expectedToken, $submittedToken)) {
        return 'csrf';
    }

    if (!empty($_POST['website'])) {
        return 'spam';
    }

    $captcha = $_SESSION['public_form_captchas'][$formName] ?? null;
    $captchaAnswer = trim((string)($_POST['captcha_answer'] ?? ''));
    if (
        !$captcha ||
        $captcha['expires_at'] < time() ||
        $captchaAnswer === '' ||
        !hash_equals($captcha['answer'], $captchaAnswer)
    ) {
        return 'captcha';
    }

    $issuedAt = (int)($_SESSION['public_form_issued'][$formName] ?? 0);
    if ($issuedAt === 0 || time() - $issuedAt < 2) {
        return 'spam';
    }

    $now = time();
    $recentSubmissions = array_filter(
        $_SESSION['public_form_submissions'][$formName] ?? [],
        static function ($submittedAt) use ($now) {
            return $submittedAt > $now - 900;
        }
    );
    if (count($recentSubmissions) >= 3) {
        return 'rate';
    }

    $recentSubmissions[] = $now;
    $_SESSION['public_form_submissions'][$formName] = array_values($recentSubmissions);
    unset(
        $_SESSION['public_form_tokens'][$formName],
        $_SESSION['public_form_issued'][$formName],
        $_SESSION['public_form_captchas'][$formName]
    );

    return '';
}
