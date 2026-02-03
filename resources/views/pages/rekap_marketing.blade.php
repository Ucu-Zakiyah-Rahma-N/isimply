@extends('app.template')

@section('content')
    <style>
        .bg-custom-blue {
            background-color: #1E87C4 !important;
            color: white;
        }

        .form-select {
            background-color: #f0f0f0 !important;
            color: #000 !important;
            border: 1px solid #ccc !important;
            border-radius: 6px;
            padding: 6px 32px 6px 10px;
            box-shadow: none !important;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M3 6l5 5 5-5H3z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.8rem center;
            background-size: 12px;
            transition: all 0.2s ease;
        }

        .form-select:hover {
            background-color: #e5e5e5 !important;
        }

        .form-select:focus {
            border-color: #999 !important;
            box-shadow: none !important;
        }

        select option {
            background-color: #ffffff;
            color: #000000;
        }
    </style>

    <div class="row">

        {{-- Tabel Rekap SPH & PO --}}
        <div class="col-12 mb-3">
            <div class="card shadow">
                <div class="card-header bg-custom-blue d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Rekapitulasi Quotation & PO per Bulan</h5>
                    <form method="GET" class="d-flex align-items-center">
                        <input type="hidden" name="tahun_achievement"
                            value="{{ request('tahun_achievement') ?? date('Y') }}">
                        <select name="tahun_rekap" class="form-select ms-3" style="min-width:100px;"
                            onchange="this.form.submit()">
                            @for ($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}"
                                    {{ (int) request('tahun_rekap') === $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        
                        @if (!(auth()->user()->role === 'admin marketing' && auth()->user()->cabang_id != 1))
                        {{-- Filter Cabang --}}
                        <select name="cabang_id" class="form-select ms-2" style="min-width:150px;" onchange="this.form.submit()">
                            <option value="">Semua Cabang</option>
                            @foreach ($cabang as $cabang)
                                <option value="{{ $cabang->id }}" {{ request('cabang_id') == $cabang->id ? 'selected' : '' }}>
                                    {{ $cabang->nama_cabang }}
                                </option>
                            @endforeach
                        </select>
                        @endif
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Waktu</th>
                                    <th>Jumlah SPH</th>
                                    <th>Nominal SPH</th>
                                    <th>Jumlah SPK</th>
                                    <th>Nominal SPK</th>
                                </tr>
                            </thead>
                            @php
                                $tahun = request('tahun_rekap') ?? date('Y');
                                $rekapTahun = $rekapGabungan->filter(fn($r) => $r['tahun'] == $tahun)->values();
                            @endphp
                            <tbody>
                                @php
                                    $total_sph = 0;
                                    $total_nominal_sph = 0;
                                    $total_spk = 0;
                                    $total_nominal_spk = 0;
                                @endphp
                                @forelse ($rekapGabungan as $index => $r)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $r['bulan'] }}</td>
                                            <td>{{ $r['jumlah_sph'] }}</td>
                                            <td>{{ number_format($r['nominal_sph'], 0, ',', '.') }}</td>
                                            <td>{{ $r['jumlah_spk'] }}</td>
                                            <td>{{ number_format($r['nominal_spk'], 0, ',', '.') }}</td>
                                        </tr>
                                        @php
                                            $total_sph += $r['jumlah_sph'];
                                            $total_nominal_sph += $r['nominal_sph'];
                                            $total_spk += $r['jumlah_spk'];
                                            $total_nominal_spk += $r['nominal_spk'];
                                        @endphp
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox"></i><br>
                                            <strong>Data belum tersedia</strong>
                                        </td>
                                    </tr>
                                @endforelse
                                
                                @if ($rekapTahun->count())
                                    <tr class="table-warning fw-bold">
                                        <td colspan="2">Total</td>
                                        <td>{{ $total_sph }}</td>
                                        <td>{{ number_format($total_nominal_sph, 0, ',', '.') }}</td>
                                        <td>{{ $total_spk }}</td>
                                        <td>{{ number_format($total_nominal_spk, 0, ',', '.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if (!(auth()->user()->role === 'admin marketing' && auth()->user()->cabang_id != 1))
        {{-- Tabel Achievement --}}
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-custom-blue d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white">Achievement PO per Bulan</h5>
                    <form method="GET" class="d-flex align-items-center">
                        <input type="hidden" name="tahun_rekap" value="{{ request('tahun_rekap') ?? date('Y') }}">
                        <select name="tahun_achievement" class="form-select ms-3" style="min-width:100px;"
                            onchange="this.form.submit()">
                            @for ($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}"
                                    {{ (int) request('tahun_achievement') === $y ? 'selected' : '' }}>{{ $y }}
                                </option>
                            @endfor
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center" id="achievementTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Bulan</th>
                                    <th>Target (Rp)</th>
                                    <th>Actual (Rp)</th>
                                    <th>Persentase (%)</th>
                                    <th>Minus (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalActual = 0; @endphp
                                @foreach ($rekapAchievement as $index => $r)
                                    @if ($r['tahun'] == (request('tahun_achievement') ?? date('Y')))
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $r['bulan'] }}</td>
                                            <td>
                                                <div class="d-flex">
                                                    <input type="text" class="form-control target-input me-2"
                                                        value="{{ number_format($r['target'], 0, ',', '.') }}" readonly
                                                        id="target-{{ $index }}" data-bulan="{{ $r['bulan'] }}"
                                                        data-tahun="{{ $r['tahun'] }}">
                                                    @if (in_array(auth()->user()->role, ['superadmin', 'admin marketing', 'manager marketing']))
                                                        <button type="button" class="btn btn-sm btn-primary"
                                                            id="btn-{{ $index }}"
                                                            onclick="toggleEditSave({{ $index }})">Edit</button>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    id="actual-{{ $index }}">{{ number_format($r['nominal_spk'], 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                <span id="persentase-{{ $index }}">0</span>
                                            </td>
                                            <td>
                                                <span id="minus-{{ $index }}">0</span>
                                            </td>
                                        </tr>
                                        @php $totalActual += $r['nominal_spk']; @endphp
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-warning fw-bold">
                                    <td colspan="2">Total {{ request('tahun_achievement') ?? date('Y') }}</td>
                                    <td><span id="total-target">0</span></td>
                                    <td>{{ number_format($totalActual, 0, ',', '.') }}</td>
                                    <td><span id="total-persentase">0</span></td>
                                    <td><span id="total-minus">0</span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <script>
            function toggleEditSave(index) {
                let input = document.getElementById('target-' + index);
                let button = document.getElementById('btn-' + index);
                let bulan = input.dataset.bulan;
                let tahun = input.dataset.tahun;

                if (input.hasAttribute('readonly')) {
                    input.removeAttribute('readonly');
                    input.focus();
                    button.textContent = 'Save';
                    button.classList.replace('btn-primary', 'btn-success');
                    formatInputRibuan(input);
                    input.addEventListener('input', onInputRibuan);
                } else {
                    input.setAttribute('readonly', true);
                    updateAchievement(index, input);
                    button.textContent = 'Edit';
                    button.classList.replace('btn-success', 'btn-primary');
                    input.removeEventListener('input', onInputRibuan);

                    // simpan ke server
                    let targetRaw = input.value.replace(/\D/g, '');
                    fetch("{{ route('achievement.save') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                bulan: bulan,
                                tahun: tahun,
                                target: targetRaw
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) updateTotalAchievement();
                        });
                }
            }

            function formatInputRibuan(el) {
                let val = el.value.replace(/\D/g, '');
                el.value = Number(val).toLocaleString('id-ID');
            }

            function onInputRibuan(e) {
                let el = e.target;
                let val = el.value.replace(/\D/g, '');
                el.value = Number(val).toLocaleString('id-ID');
                el.selectionStart = el.selectionEnd = el.value.length;
            }

            function updateAchievement(index, el) {
                let targetRaw = el.value.replace(/\D/g, '');
                let target = parseFloat(targetRaw) || 0;
                el.value = target.toLocaleString('id-ID');

                let actual = parseFloat(document.getElementById('actual-' + index).textContent.replace(/\./g, '')) || 0;
                let persentase = target > 0 ? (actual / target * 100).toFixed(1) : 0;
                let minus = target - actual;

                document.getElementById('persentase-' + index).textContent = persentase;
                document.getElementById('minus-' + index).textContent = minus.toLocaleString('id-ID');

                updateTotalAchievement();
            }

            function updateTotalAchievement() {
                let totalTarget = 0;
                let totalActual = 0;

                document.querySelectorAll('.target-input').forEach((input, index) => {
                    let targetVal = parseFloat(input.value.replace(/\D/g, '')) || 0;
                    totalTarget += targetVal;

                    let actualEl = document.getElementById('actual-' + index);
                    let actualVal = parseFloat(actualEl.textContent.replace(/\./g, '')) || 0;
                    totalActual += actualVal;
                });

                let totalPersentase = totalTarget > 0 ? (totalActual / totalTarget * 100).toFixed(1) : 0;
                let totalMinus = totalTarget - totalActual;

                document.getElementById('total-target').textContent = totalTarget.toLocaleString('id-ID');
                document.getElementById('total-persentase').textContent = totalPersentase;
                document.getElementById('total-minus').textContent = totalMinus.toLocaleString('id-ID');
            }

            function updateAllAchievementRows() {
                document.querySelectorAll('.target-input').forEach((input, index) => {
                    updateAchievement(index, input);
                });
            }


            // panggil saat page load
            updateAllAchievementRows();
        </script>
    @endsection
