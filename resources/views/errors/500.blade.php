<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Terjadi Kesalahan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CDN (aman, tanpa asset lokal) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: 
                linear-gradient(
                    rgba(0,0,0,0.55),
                    rgba(0,0,0,0.55)
                ),
                url('{{ asset('assets/images/error-500.jpg') }}') no-repeat center center;
            background-size: cover;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

<div class="text-center text-white">
    <h1 class="display-4 fw-bold">Oops!</h1>
    <p class="fs-5 mb-4">
        Terjadi kesalahan pada sistem kami.<br>
        Silakan coba beberapa saat lagi.
    </p>

    <a href="{{ url('/') }}" class="btn btn-light px-4">
        Kembali ke Beranda
    </a>
</div>

</body>
</html>
