<style>
    /* === Sidebar utama === */
    .left-sidebar {
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: #fff;
        border-right: 1px solid #f0f0f0;
    }

    /* Logo */
    .brand-logo {
        flex-shrink: 0;
        padding: 16px 0;
        text-align: center;
        border-bottom: 1px solid #f0f0f0;
    }

    /* Scrollable menu */
    .scroll-sidebar {
        /* flex: 1; */
        /* overflow-y: auto; */
        overflow: visible;
    }

    /* Item menu */
    .sidebar-item a {
        display: flex;
        align-items: center;
        padding: 8px 16px;
        gap: 8px;
        color: #333;
        text-decoration: none;
        border-radius: 6px;
        transition: background 0.2s;
    }

    .sidebar-item a:hover {
        background-color: #f3f6ff;
        color: #2952a3;
    }

    /* Submenu flyout */
    .sidebar-item {
        position: relative;
    }

    .sidebar-submenu {
        display: none;
        position: absolute;
        top: 0;
        left: calc(100% + 12px);
        /* paksa ke kanan */
        min-width: 180px;
        background: #fff;
        border: 1px solid #e5e5e5;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        z-index: 99999;
    }

    .sidebar-item:hover>.sidebar-submenu,
    .sidebar-submenu:hover {
        display: block;
    }

    .sidebar-submenu::before {
        content: "";
        position: absolute;
        top: 0;
        left: -12px;
        /* jembatan ke menu utama */
        width: 12px;
        height: 100%;
    }

    /* Submenu item */
    .sidebar-submenu .sidebar-item a {
        padding: 8px 12px;
        color: #555;
    }

    .sidebar-submenu .sidebar-item a:hover {
        background-color: #e8eefc;
        color: #1e3a8a;
    }

    .scroll-sidebar {
        /* overflow: visible; */
        /* agar flyout tidak terpotong */

        flex: 1;
        overflow-y: auto;
        /* aktifkan scroll vertikal */
        overflow-x: visible;
        /* supaya flyout tetap muncul */
    }

    /* kalo ini di koment di customer sub menu ketindihan content. tp kalo ga di komen, ga bisa scroll sidebar*/
    .page-wrapper,
    .body-wrapper {
        overflow: visible;
    }

    .left-sidebar,
    .sidebar-nav,
    .scroll-sidebar,
    #sidebarnav {
        overflow: visible !important;
    }
</style>

<aside class="left-sidebar">

    <div class="brand-logo d-flex align-items-center justify-content-center text-center py-3 position-relative">
        <!-- Logo + Tulisan -->
        <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none text-dark">
            <img src="{{ asset('assets/images/isimply.png') }}" width="150" alt="Logo Simply" class="img-fluid me-2">

        </a>

        <!-- Tombol Close (mobile) -->
        <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer position-absolute end-0 me-3"
            id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
        </div>
    </div>

    <nav class="sidebar-nav scroll-sidebar">
        <ul id="sidebarnav">

            @if (auth()->user()->role !== 'customer')
            <!-- HOME -->
            <li class="nav-small-cap">
                {{-- <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                    <span class="hide-menu">Home</span> --}}
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ url('dashboard') }}" aria-expanded="false">
                    <span><i class="ti ti-layout-dashboard"></i></span>
                    <span class="hide-menu">Dashboard</span>
                </a>
            </li>
            @endif

            {{-- super admin --}}
            @if (in_array(auth()->user()->role, ['superadmin']))
            <li class="sidebar-item position-relative">
                <a class="sidebar-link d-flex align-items-center justify-content-between" href="javascript:void(0)">
                    <span class="d-flex align-items-center">
                        <i class="bi bi-grid"></i>
                        <span class="hide-menu ms-2">Marketing</span>
                    </span>
                    <i class="ti ti-chevron-right arrow-icon"></i>
                </a>

                <ul class="sidebar-submenu">
                    <li class="sidebar-item">
                        <a href="{{ url('customer') }}" class="sidebar-link">
                            <i class="bi bi-person-lines-fill"></i>
                            <span class="hide-menu">Customer</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('quotation.index') }}" class="sidebar-link">
                            <i class="bi bi-file-text"></i>
                            <span class="hide-menu">Quotation</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ url('PO') }}" class="sidebar-link">
                            <i class="bi bi-file-spreadsheet"></i>
                            <span class="hide-menu">PO / SPK</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ url('rekap_marketing') }}" class="sidebar-link">
                            <i class="bi bi-clipboard-data"></i>
                            <span class="hide-menu">Rekap</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('projects.timeline') }}" class="sidebar-link">
                    <i class="bi bi-clock-history"></i>
                    <span class="hide-menu">Timeline</span>
                </a>
            </li>

            <li class="nav-small-cap">
                <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                <span class="hide-menu">DATA MASTER</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ url('marketing') }}" aria-expanded="false">
                    <span><i class="bi bi-bar-chart-line"></i></span>
                    <span class="hide-menu">Marketing</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ url('users') }}" aria-expanded="false">
                    <span><i class="ti ti-user"></i></span>
                    <span class="hide-menu">Users</span>
                </a>

            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ url('perizinan') }}" aria-expanded="false">
                    <span><i class="bi bi-card-list"></i></span>
                    <span class="hide-menu">Jenis Perizinan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ url('tahapan') }}" aria-expanded="false">
                    <span><i class="bi bi-card-list"></i></span>
                    <span class="hide-menu">Data Tahapan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('template.index') }}" aria-expanded="false">
                    <span><i class="bi bi-card-list"></i></span>
                    <span class="hide-menu">Template SPH</span>
                </a>

            </li>
            @endif

            {{-- admin  marketing --}}
            @if (in_array(auth()->user()->role, ['admin marketing']))
            <li class="sidebar-item position-relative">
                <a class="sidebar-link d-flex align-items-center justify-content-between" href="javascript:void(0)">
                    <span class="d-flex align-items-center">
                        <i class="bi bi-grid"></i>
                        <span class="hide-menu ms-2">Marketing</span>
                    </span>
                    <i class="ti ti-chevron-right arrow-icon"></i>
                </a>

                <ul class="sidebar-submenu">
                    <li class="sidebar-item">
                        <a href="{{ url('customer') }}" class="sidebar-link">
                            <i class="bi bi-person-lines-fill"></i>
                            <span class="hide-menu">Customer</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ route('quotation.index') }}" class="sidebar-link">
                            <i class="bi bi-file-text"></i>
                            <span class="hide-menu">Quotation</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ url('PO') }}" class="sidebar-link">
                            <i class="bi bi-file-spreadsheet"></i>
                            <span class="hide-menu">PO / SPK</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a href="{{ url('rekap_marketing') }}" class="sidebar-link">
                            <i class="bi bi-clipboard-data"></i>
                            <span class="hide-menu">Rekap</span>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('projects.timeline') }}" class="sidebar-link">
                    <i class="bi bi-clock-history"></i>
                    <span class="hide-menu">Timeline</span>
                </a>
            </li>
            @endif

            @if (in_array(auth()->user()->role, ['admin 1', 'admin 2']))
            <!-- PROJEK -->
            <li class="nav-small-cap mt-3">
                <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                <span class="hide-menu">Projek</span>
            </li>

            <li class="sidebar-item">
                <a href="{{ url('projects') }}" class="sidebar-link">
                    <i class="bi bi-kanban"></i>
                    <span class="hide-menu">Data Projek</span>
                </a>
            </li>

            <li class="sidebar-item">
                <a href="{{ route('projects.timeline') }}" class="sidebar-link">
                    <i class="bi bi-clock-history"></i>
                    <span class="hide-menu">Timeline</span>
                </a>
            </li>
            <li class="nav-small-cap mt-3">
                <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                <span class="hide-menu">Data Master</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ url('tahapan') }}" aria-expanded="false">
                    <span><i class="bi bi-card-list"></i></span>
                    <span class="hide-menu">Data Tahapan</span>
                </a>
            </li>
            @endif

            @if (in_array(auth()->user()->role, ['CEO', 'direktur', 'manager projek', 'manager marketing', 'manager finance']))

            @if (auth()->user()->role === 'manager marketing')
            <li class="sidebar-item">
                <a href="{{ route('quotation.index') }}" class="sidebar-link">
                    <i class="bi bi-file-text"></i>
                    <span class="hide-menu">Quotation</span>
                </a>
            </li>
            @endif

            <li class="sidebar-item">
                <a href="{{ url('PO') }}" class="sidebar-link">
                    <i class="bi bi-file-spreadsheet"></i>
                    <span class="hide-menu">PO / SPK</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ url('rekap_marketing') }}" class="sidebar-link">
                    <i class="bi bi-clipboard-data"></i>
                    <span class="hide-menu">Rekap Marketing</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ url('projects') }}" class="sidebar-link">
                    <i class="bi bi-kanban"></i>
                    <span class="hide-menu">Data Projek</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('projects.timeline') }}" class="sidebar-link">
                    <i class="bi bi-clock-history"></i>
                    <span class="hide-menu">Timeline Projek</span>
                </a>
            </li>
            @endif

            @if (in_array(auth()->user()->role, ['manager finance', 'admin finance']))

            <li class="sidebar-item">
                <a class="sidebar-link has-arrow"
                    data-bs-toggle="collapse"
                    href="#accountingMenu"
                    role="button"
                    aria-expanded="false">
                    <span><i class="ti ti-book-2"></i></span>
                    <span class="hide-menu">Accounting</span>
                </a>

                <ul class="collapse list-unstyled ps-3" id="accountingMenu">
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="#">
                            <span class="hide-menu">Saldo</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.bank_cash.index') }}">
                            <span class="hide-menu">Bank Cash</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="#">
                            <span class="hide-menu">Laporan Arus Kas</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link has-arrow"
                    data-bs-toggle="collapse"
                    href="#financeMenu"
                    role="button"
                    aria-expanded="false">
                    <span><i class="ti ti-wallet"></i></span>
                    <span class="hide-menu">Finance</span>
                </a>

                <ul class="collapse list-unstyled ps-3"
                    id="financeMenu"
                    data-bs-parent="#sidebarnav">

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ url('finance') }}">
                            <span><i class="bi bi-search"></i></span>
                            <span class="hide-menu">Data BAST</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.invoice_index') }}">
                            <span class="hide-menu">Data sudah Invoice</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.invoice.laporan_piutang') }}">
                            <span class="hide-menu">Data Piutang</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.invoice.laporan_outstanding') }}">
                            <span class="hide-menu">Data Outstanding</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="#">
                            <span class="hide-menu">Data Penerimaan Bulan Ini</span>
                        </a>
                    </li>

                </ul>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link has-arrow"
                    data-bs-toggle="collapse"
                    href="#operasionalMenu"
                    role="button"
                    aria-expanded="false">
                    <span><i class="ti ti-settings"></i></span>
                    <span class="hide-menu">Operasional</span>
                </a>

                <ul class="collapse list-unstyled ps-3"
                    id="operasionalMenu"
                    data-bs-parent="#sidebarnav">

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.biaya_index') }}">
                            <span><i class="bi bi-cash-stack"></i></span>
                            <span class="hide-menu">Biaya & Pengadaan</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.pembelian_index') }}">
                            <span><i class="bi bi-bag"></i></span>
                            <span class="hide-menu">Pembelian</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.pihak_ketiga_index') }}">
                            <span><i class="bi bi-briefcase"></i></span>
                            <span class="hide-menu">Pihak Ketiga</span>
                        </a>
                    </li>

                </ul>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link has-arrow"
                    data-bs-toggle="collapse"
                    href="#purchasingMenu"
                    role="button"
                    aria-expanded="false">
                    <span><i class="ti ti-shopping-cart"></i></span>
                    <span class="hide-menu">Purchasing</span>
                </a>

                <ul class="collapse list-unstyled ps-3"
                    id="purchasingMenu"
                    data-bs-parent="#sidebarnav">

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.purchasing_index') }}">
                            <span class="hide-menu">Purchasing</span>
                        </a>
                    </li>

                </ul>
            </li>

            <li class="sidebar-item">
                <a class="sidebar-link has-arrow"
                    data-bs-toggle="collapse"
                    href="#managerMenu"
                    role="button"
                    aria-expanded="false">
                    <span><i class="ti ti-user"></i></span>
                    <span class="hide-menu">Manager</span>
                </a>

                <ul class="collapse list-unstyled ps-3"
                    id="managerMenu"
                    data-bs-parent="#sidebarnav">

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.manager_index') }}">
                            <span class="hide-menu">Manager</span>
                        </a>
                    </li>

                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow"
                    data-bs-toggle="collapse"
                    href="#akunMenu"
                    role="button"
                    aria-expanded="false">
                    <span><i class="bi bi-credit-card"></i></span>
                    <span class="hide-menu">Akun</span>
                </a>

                <ul class="collapse list-unstyled ps-3"
                    id="akunMenu"
                    data-bs-parent="#sidebarnav">

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.akun_index') }}">
                            <span class="hide-menu">Data Akun</span>
                        </a>
                    </li>

                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow"
                    data-bs-toggle="collapse"
                    href="#supplierMenu"
                    role="button"
                    aria-expanded="false">
                    <span><i class="bi bi-briefcase"></i></span>
                    <span class="hide-menu">Supplier</span>
                </a>

                <ul class="collapse list-unstyled ps-3"
                    id="supplierMenu"
                    data-bs-parent="#sidebarnav">

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="{{ route('finance.kontak_index') }}">
                            <span class="hide-menu">Kontak</span>
                        </a>
                    </li>

                </ul>
            </li>
            @endif

            @if (auth()->user()->role === 'customer')
            <li class="nav-small-cap">
                <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                <span class="hide-menu">Menu Tracking</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ url('tracking') }}" aria-expanded="false">
                    <span><i class="bi bi-search"></i></span>
                    <span class="hide-menu">Tracking </span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('customer.timeline') }}" class="sidebar-link">
                    <i class="bi bi-clock-history"></i>
                    <span class="hide-menu">Timeline</span>
                </a>
            </li>
            <li class="nav-small-cap">
                <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
                <span class="hide-menu">Other</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link contact-admin-link" href="javascript:void(0)" aria-expanded="false">
                    <span><i class="ti ti-alert-circle"></i></span>
                    <span class="hide-menu">Bantuan</span>
                </a>
            </li>
            @endif
        </ul>
    </nav>
</aside>

<script>
    $(document).ready(function() {
        $('.contact-admin-link').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Bantuan',
                text: 'Silakan hubungi admin untuk bantuan lebih lanjut.',
                icon: 'info',
                confirmButtonText: 'Oke'
            });
        });
    });
</script>