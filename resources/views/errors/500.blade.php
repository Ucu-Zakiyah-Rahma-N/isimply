<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>500 - System Error</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(rgba(10, 15, 30, 0.75), rgba(10, 15, 30, 0.85)),
            url("{{ asset('assets/images/error-500.jpg') }}") center/cover no-repeat;
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px;
            max-width: 520px;
        }

        .error-code {
            font-size: 72px;
            font-weight: 700;
            letter-spacing: 2px;
        }

        .btn-custom {
            border-radius: 10px;
            padding: 10px 22px;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center text-white">

    <div class="glass text-center shadow-lg">

        <div class="error-code mb-3">500</div>

        <h4 class="mb-3 fw-semibold">
            Internal Server Error
        </h4>

        <p class="mb-4 text-white-50">
            Terjadi gangguan pada sistem.<br>
            Tim kami sedang menanganinya.
        </p>

        <div class="d-flex justify-content-center gap-2">
            <a href="{{ url('/') }}" class="btn btn-light btn-custom">
                Dashboard
            </a>

            <button onclick="location.reload()" class="btn btn-outline-light btn-custom">
                Retry
            </button>
        </div>

    </div>

</body>

</html>