<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Hello!</h2>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>
        <a href="{{ $url }}" style="background-color: #0d6efd; color: #fff; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Reset Password
        </a>
    </p>
    <p>This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.</p>
    <p>If you did not request a password reset, no further action is required.</p>
</body>
</html>