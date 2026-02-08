
function formatRupiah(number) {
    return number.toLocaleString('id-ID');
}

function recalc() {
    let grossSubtotal = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.price')?.value) || 0;

        const rowSubtotal = qty * price;
        row.querySelector('.subtotal').value = formatRupiah(rowSubtotal);

        grossSubtotal += rowSubtotal;
    });

    // DISKON
    const discountType = document.getElementById('discountType').value;
    const discountInput = parseFloat(document.getElementById('discountValue').value) || 0;

    let discountAmount = 0;

    if (discountType === 'percent') {
        discountAmount = grossSubtotal * (discountInput / 100);
    } else {
        discountAmount = discountInput;
    }

    if (discountAmount > grossSubtotal) {
        discountAmount = grossSubtotal;
    }

    let subtotalAfterDiscount = grossSubtotal - discountAmount;

    //PPN
    const taxRate = parseFloat($('#taxSelect').val()) || 0;
    const includeTax = document.getElementById('includeTax').checked;

    let dpp = subtotalAfterDiscount;
    let ppn = 0;
    let total = subtotalAfterDiscount;

    if (taxRate > 0) {
        document.getElementById('ppnRow').style.display = 'flex';
        document.getElementById('ppnRate').innerText = taxRate;

        if (includeTax) {
            ppn = subtotalAfterDiscount - (subtotalAfterDiscount / (1 + taxRate / 100));
            dpp = subtotalAfterDiscount - ppn;
            total = subtotalAfterDiscount;
        } else {
            ppn = subtotalAfterDiscount * (taxRate / 100);
            total = subtotalAfterDiscount + ppn;
        }

        document.getElementById('ppnAmount').innerText =
            formatRupiah(Math.round(ppn));
    } else {
        document.getElementById('ppnRow').style.display = 'none';
    }

    /* ===== RENDER ===== */
    document.getElementById('subtotal').innerText =
        formatRupiah(Math.round(dpp));

    document.getElementById('discountAmount').innerText =
        formatRupiah(Math.round(discountAmount));

    document.getElementById('finalTotal').innerText =
        formatRupiah(Math.round(total));
}

/* =======================
   EVENT GLOBAL
======================= */
document.addEventListener('input', function (e) {
    if (
        e.target.classList.contains('qty') ||
        e.target.classList.contains('price') ||
        e.target.id === 'discountValue' ||
        e.target.id === 'discountType'
    ) {
        recalc();
    }
});

document.getElementById('includeTax').addEventListener('change', recalc);

/* =======================
   SELECT2 & MODAL
======================= */
$(document).ready(function () {
    const $taxSelect = $('#taxSelect');
    const modalEl = document.getElementById('modalTambahPajak');
    const modal = new bootstrap.Modal(modalEl);

    /* INIT SELECT2 */
    $taxSelect.select2({
        width: '100%',
        placeholder: '-- Pilih Pajak --',
        allowClear: true,
        templateResult: function (data) {
            if (data.id === 'add_new') {
                return $(`
                    <div class="text-center fw-bold">
                        <i class="bi bi-plus-circle"></i> Tambah Pajak
                    </div>
                `);
            }
            return data.text;
        }
    });

    /* PILIH PAJAK */
    $taxSelect.on('select2:select', function (e) {
        if (e.params.data.id === 'add_new') {
            modal.show();
            $taxSelect.val(null).trigger('change');
            return;
        }

        recalc();
    });

    /* CLEAR / CHANGE */
    $taxSelect.on('change.select2', function () {
        recalc();
    });

    /* =======================
       SIMPAN PAJAK
    ======================= */
    $('#btnSimpanPajak').on('click', function () {
        const nama = $('#nama').val().trim();
        const nilai = $('#nilai').val().trim();

        if (!nama || !nilai) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi',
                text: 'Nama akun dan nilai Pajak wajib diisi'
            });
            return;
        }

        $.ajax({
            url: '/finance/akun/coa/store',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                nama: nama,
                nilai: nilai
            },
            success: function (res) {
                if (res.success) {
                    const newOption = new Option(
                        res.data.nama,
                        res.data.nilai,
                        true,
                        true
                    );

                    const addNewOption = $taxSelect.find('option[value="add_new"]');
                    $(newOption).insertBefore(addNewOption);

                    // refresh select2
                    $taxSelect.trigger('change');

                    $('#nama').val('');
                    $('#nilai').val('');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    modal.hide();
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                });
            }
        });
    });
});