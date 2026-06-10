<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PAHID</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #1a2332; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 1rem; padding: 2.5rem; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .brand { text-align: center; margin-bottom: 2rem; }
        .brand h2 { font-weight: 800; color: #1a2332; }
        .brand small { color: #6c757d; font-size: 0.875rem; }
        .demo-accounts { background: #f8f9fa; border-radius: 0.5rem; padding: 0.75rem; font-size: 0.78rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">
        <div class="mb-2"><i class="bi bi-shield-check text-primary" style="font-size: 2.5rem;"></i></div>
        <h2>PAHID</h2>
        <small>AI-Assisted Health Insurance Prospect Verification</small>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-sm">
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="email@example.com">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
        </button>
    </form>

    <div class="demo-accounts mt-3">
        <div class="fw-semibold mb-1">Demo Accounts (password: <code>password</code>)</div>
        <table class="table table-sm mb-0" style="font-size:0.75rem;">
            <tr><td>Admin</td><td><code>admin@pahid.test</code></td></tr>
            <tr><td>Supervisor</td><td><code>supervisor@pahid.test</code></td></tr>
            <tr><td>BC Surabaya</td><td><code>bc.sby@pahid.test</code></td></tr>
            <tr><td>Marketing A</td><td><code>marketing.a@pahid.test</code></td></tr>
            <tr><td>Marketing B</td><td><code>marketing.b@pahid.test</code></td></tr>
        </table>
    </div>
</div>
</body>
</html>
