<div class="modal fade" id="modalTambahAkun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('finance.akun.store') }}" method="POST">
                @csrf

                <!-- HEADER -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-journal-plus me-2"></i> Tambah Akun
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- KODE AKUN -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Akun</label>
                            <input type="text" name="kode_akun" class="form-control" placeholder="Contoh: 10101"
                                required>
                        </div>

                        <!-- NAMA AKUN -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Akun</label>
                            <input type="text" name="nama_akun" class="form-control" placeholder="Kas Besar"
                                required>
                        </div>

                        <!-- KATEGORI -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori Akun</label>
                            <select name="kategori_akun" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Kas & Bank">Kas & Bank</option>
                                <option value="Akun Piutang">Akun Piutang</option>
                                <option value="Persediaan">Persediaan</option>
                                <option value="Aktiva Lancar Lainnya">Aktiva Lancar Lainnya</option>
                                <option value="Aktiva Tetap">Aktiva Tetap</option>
                                <option value="Depresiasi & Amortisasi">Depresiasi & Amortisasi</option>
                                <option value="Akun Hutang">Akun Hutang</option>
                                <option value="Kewajiban Lancar Lainnya">Kewajiban Lancar Lainnya</option>
                                <option value="Ekuitas">Ekuitas</option>
                                <option value="Pendapatan">Pendapatan</option>
                                <option value="Harga Pokok Penjualan">Harga Pokok Penjualan</option>
                                <option value="Beban">Beban</option>
                                <option value="Pendapatan Lainnya">Pendapatan Lainnya</option>
                                <option value="Beban Lainnya">Beban Lainnya</option>
                            </select>
                        </div>
                        <!-- SALDO -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Saldo Awal</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="saldo_awal" class="form-control text-end" step="0.01"
                                    value="0">
                            </div>
                        </div>

                        <!-- TIPE AKUN -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipe Akun</label>

                            <div class="d-flex gap-3">
                                <div class="form-check border rounded px-3 py-2 w-50">
                                    <input class="form-check-input" type="checkbox" name="is_header_akun"
                                        id="is_header_akun" value="1">
                                    <label class="form-check-label fw-medium" for="is_header_akun">
                                        Akun Header
                                    </label>
                                </div>

                                <div class="form-check border rounded px-3 py-2 w-50">
                                    <input class="form-check-input" type="checkbox" name="is_sub_account"
                                        id="is_sub_account" value="1">
                                    <label class="form-check-label fw-medium" for="is_sub_account">
                                        Sub Akun
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- HEADER AKUN -->
                        <div class="col-md-6 d-none" id="headerAkunWrapper">
                            <label class="form-label fw-semibold">Header Akun</label>
                            <select name="parent_akun_id" class="form-select">
                                <option value="">-- Pilih Header Akun --</option>
                                @foreach ($akunHeader as $header)
                                    <option value="{{ $header->id }}">
                                        {{ $header->kode_akun }} - {{ $header->nama_akun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const subCheckbox = document.getElementById('is_sub_account');
        const headerWrapper = document.getElementById('headerAkunWrapper');

        subCheckbox.addEventListener('change', function() {
            headerWrapper.classList.toggle('d-none', !this.checked);
        });
    });
</script>
