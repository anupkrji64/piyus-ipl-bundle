<?php
// Cookie-based auth — works on Vercel serverless (no filesystem sessions needed)
define('AUTH_SECRET', 'ipl_bundle_secret_2025_xK9mP');
define('AUTH_COOKIE', 'admin_auth');

function auth_login(string $user): void {
    $token = hash_hmac('sha256', $user . '|logged_in', AUTH_SECRET);
    $val   = base64_encode($user . ':' . $token);
    setcookie(AUTH_COOKIE, $val, time() + 86400 * 7, '/', '', false, true);
}

function auth_check(): bool {
    $val = $_COOKIE[AUTH_COOKIE] ?? '';
    if (!$val) return false;
    $dec  = base64_decode($val);
    $parts = explode(':', $dec, 2);
    if (count($parts) !== 2) return false;
    [$user, $token] = $parts;
    return hash_equals(hash_hmac('sha256', $user . '|logged_in', AUTH_SECRET), $token);
}

function auth_user(): string {
    $val = $_COOKIE[AUTH_COOKIE] ?? '';
    if (!$val) return 'admin';
    $dec = base64_decode($val);
    return explode(':', $dec, 2)[0] ?? 'admin';
}

function auth_logout(): void {
    setcookie(AUTH_COOKIE, '', time() - 3600, '/', '', false, true);
}
