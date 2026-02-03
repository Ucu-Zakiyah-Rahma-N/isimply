@extends('app.template')

@section('content')

@if(session('login_success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil Masuk',
        text: '{{ session('login_success') }}',
        showConfirmButton: false,
        timer: 2000
    });
</script>
@endif


<style>
.stats-card {
    border-radius: 10px;
    padding: 16px 18px;
    border: 1px solid #e6e6e6;
    background: #fff;
    display: flex;
    align-items: center;
    gap: 12px;
}

.stats-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
    flex-shrink: 0;
}

.stats-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 3px;
}

.stats-value {
    font-size: 12px;
    font-weight: 600;
    margin: 0;
}

.dashboard-header {
    margin-bottom: 1rem;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

</style>

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-0">
            Selamat Datang <b>{{ $user->username }}</b>
        </p>
    </div>
</div>

@if(in_array(strtolower($user->role), ['superadmin', 'admin marketing', 'ceo',  'direktur', 'manager marketing', 'manager project', 'manager finance']))
<div class="dashboard-header d-flex justify-content-between align-items-center">
    <span>Rekap Bulan Ini</span>

    <form method="GET" class="d-flex align-items-center">
        {{-- Filter Bulan --}}
        <select name="bulan" class="form-select me-2" style="width:180px;" onchange="this.form.submit()">
            <option value="">-Semua Bulan-</option>
            @foreach(range(1,12) as $m)
                @php $namaBulan = \Carbon\Carbon::create()->month($m)->format('F'); @endphp
                <option value="{{ $m }}" {{ ($bulanFilter == $m) ? 'selected' : '' }}>
                    {{ $namaBulan }}
                </option>
            @endforeach
        </select>

        {{-- Filter Tahun --}}
        <select name="tahun" class="form-select" style="width:100px;" onchange="this.form.submit()">
            @for($y = date('Y'); $y >= 2020; $y--)
                <option value="{{ $y }}" {{ ($tahunFilter == $y) ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
    </form>
</div>

<div class="row g-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon" style="background:#4a74ff;"><i class="ti ti-file-invoice"></i></div>
            <div>
                <div class="stats-label">Jumlah PO</div>
                <div class="stats-value">{{ $jumlahPO ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon" style="background:#1abc9c;"><i class="ti ti-cash"></i></div>
            <div>
                <div class="stats-label">Nilai PO</div>
                <div class="stats-value">Rp {{ number_format($nilaiPO ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

@if (!(auth()->user()->role === 'admin marketing' && auth()->user()->cabang_id != 1))
    <div class="col-12 col-md-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon" style="background:#f1c40f;"><i class="ti ti-chart-pie"></i></div>
            <div>
                <div class="stats-label">Achievement {{ $bulanFilter ? 'Bulan Ini' : 'Tahun Ini' }}</div>
                <div class="stats-value">{{ $persentaseAchieve ?? 0 }}%</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stats-card">
            <div class="stats-icon" style="background:#e67e22;"><i class="ti ti-target"></i></div>
            <div>
                <div class="stats-label">{{ $bulanFilter ? 'Target Bulan Ini' : 'Target Tahun Ini' }}</div>
                <div class="stats-value">Rp {{ number_format($targetBulanIni ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
@endif
</div>

<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white">
        <h6 class="mb-0 fw-semibold">Grafik Nilai PO per Bulan ({{ $tahunFilter }})</h6>
    </div>
    <div class="card-body">
        <canvas id="chartNilaiPO" height="90"></canvas>
    </div>
</div>
@endif


@if(in_array(strtolower($user->role), ['superadmin', 'admin 1', 'admin 2', 'ceo', 'direktur', 'manager marketing', 'manager projek', 'manager finance']))
        {{-- REKAP PROJECT --}}
        <div class="dashboard-header">
    Rekap Project
</div>

    <div class="card-body">
        <div class="d-flex justify-content-center mb-1"> {{-- jarak lebih kecil ke tabel --}}
            <div class="d-flex flex-wrap justify-content-center gap-2 flex-nowrap" style="max-width: 1000px;">
    
                {{-- Belum Mulai --}}
                <a href="{{ route('projects.index', ['status' => 'Belum Mulai']) }}"
                class="text-decoration-none text-dark">
                <div class="card shadow border-0" style="width: 220px; height: 100px;">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="ti ti-clock fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted">Belum Mulai</h6>
                            <h4 class="fw-bold mb-0">{{ $rekap['belum_mulai'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                </a>
                
                {{-- On Progress --}}
                <a href="{{ route('projects.index', ['status' => 'On Progress']) }}"
                class="text-decoration-none text-dark">
                <div class="card shadow border-0" style="width: 220px; height: 100px;">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="ti ti-loader fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted">On Progress</h6>
                            <h4 class="fw-bold mb-0">{{ $rekap['on_progress'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                </a>

                {{-- Selesai --}}
                <a href="{{ route('projects.index', ['status' => 'Selesai']) }}"
                class="text-decoration-none text-dark">
                <div class="card shadow border-0" style="width: 220px; height: 100px;">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="ti ti-check fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted">Selesai</h6>
                            <h4 class="fw-bold mb-0">{{ $rekap['selesai'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
                </a>

                {{-- Total --}}
                <div class="card shadow border-0" style="width: 220px; height: 100px;">
                    <div class="card-body d-flex align-items-center">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="ti ti-list fs-4"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="text-muted">Total Project</h6>
                            <h4 class="fw-bold mb-0">{{ $rekap['total'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>

@endif


{{-- ================= SCRIPT UNTUK CHART ================= --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        // Data dari Controller
        const bulan = @json($bulan);                   // ['Jan', 'Feb', ...]
        const nilaiPerBulan = @json($nilaiPerBulan);   // [54000000, 72000000, ...]

        const ctx = document.getElementById('chartNilaiPO').getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($bulan),
                datasets: [{
                    label: 'Nilai PO',
                    data: @json($nilaiPerBulan),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.15)',
                    borderWidth: 3,
                    tension: 0.3,
                    pointBackgroundColor: '#0d6efd',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + (context.raw || 0).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 100000000, // interval 100 juta
                            callback: function(value){
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        },
                    //   max: 500000000 
                    }
                }
            }
        });
    });
</script>


@endsection