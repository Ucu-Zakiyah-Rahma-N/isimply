<!doctype html>
<html lang="en">

@include('app.head')

<body>  
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    
    @include('app.sidebar')

    <div class="body-wrapper">
     
    @include('app.header')
    
      <div class="container-fluid">

        @yield('content')

      </div>

    </div>
  </div>
  
    @include('pages.finance.operasional.biaya.modal-detail-biaya')
  @include('pages.finance.operasional.biaya.modal-edit-biaya')

  @include('app.script')

  {{-- SweetAlert2 --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

{{-- Flash alert global --}}
<script>
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 2000
        });
    @endif

    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '{{ session('error') }}',
            showConfirmButton: true
        });
    @endif
</script>

</body>

</html>