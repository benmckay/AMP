<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Forgot Password</h4>
                    <p class="text-muted">Password reset flow can be configured here.</p>
                    <a href="{{ route('login') }}" class="btn btn-primary">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
