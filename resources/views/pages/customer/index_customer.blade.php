@extends('app.template')

@section('content')
@if(session('login_success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Selamat Datang',
        text: '{{ session('login_success') }}',
        showConfirmButton: false,
        timer: 2000
    });
</script>
@endif

<div class="card">
    <div class="card-header">
        <h5>Data Projek {{ $customer->nama_perusahaan }}</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No PO</th>
                    <th>Jenis Perizinan</th>
                    <th>Lokasi</th>
                    <th>Catatan Terakhir</th>
                    <th>Status</th>
                    <th>Lama Pekerjaan</th> 
                    <th>Sisa Waktu</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $key => $project)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $project->po->no_po ?? '-' }}</td>
                        <td>{{ $project->jenis_perizinan ?: '-' }}</td>
                        <td>{{ $project->po->quotation->detail_alamat ?? '-' }}</td>
                        <td>{{ $project->catatan_terakhir }}</td>
                        <td>
                        <span class="badge 
                            @if($project->status_project == 'Belum Mulai') bg-secondary
                            @elseif($project->status_project == 'On Progress') bg-warning
                            @elseif($project->status_project == 'Selesai') bg-success
                            @else bg-dark @endif">
                            {{ $project->status_project }}
                        </span>
                    </td>
                    <td>{{ $project->lama_pekerjaan ? $project->lama_pekerjaan . ' hari' : '-' }}</td>
                        <td>                      
                            @if(!is_null($project->sisaHari))   
                                @if($project->sisaHari > 0)
                                    {{ $project->sisaHari }} hari lagi
                                @elseif($project->sisaHari == 0)
                                    Deadline hari ini
                                @else
                                    Deadline sudah lewat {{ abs($project->sisaHari) }} hari
                                @endif
                            @else
                                {{-- Collect Dokumen belum selesai, deadline belum dihitung --}}
                                Dimulai setelah dokumen lengkap
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('show_customer', $project->id) }}" class="btn btn-primary btn-sm">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada projek untuk {{ $customer->nama_perusahaan }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
