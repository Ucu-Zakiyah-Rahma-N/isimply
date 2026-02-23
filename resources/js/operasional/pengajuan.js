document.addEventListener('DOMContentLoaded', function () {

    /* ================== ELEMENT ================== */
    const modalPengajuanEl = document.getElementById('modalPengajuanBiaya');
    const modalKontakEl    = document.getElementById('modalTambahKontak');
    const btnOpenKontak    = document.getElementById('btnOpenKontak');
    const formPengajuan    = document.getElementById('formPengajuan');
    const formTambahKontak = document.getElementById('formTambahKontak');
    const csrfToken        = document.querySelector('meta[name="csrf-token"]')?.content;

    const storeKontakUrl   = window.appRoutes?.storeKontak;
    const storePengajuanUrl= window.appRoutes?.storePengajuan;

    /* ================== INIT MODAL ================== */
    const modalPengajuan = modalPengajuanEl
        ? bootstrap.Modal.getOrCreateInstance(modalPengajuanEl)
        : null;

    const modalKontak = modalKontakEl
        ? bootstrap.Modal.getOrCreateInstance(modalKontakEl)
        : null;

    /* ================== OPEN MODAL KONTAK ================== */
    if (btnOpenKontak && modalKontak) {
        btnOpenKontak.addEventListener('click', function () {
            btnOpenKontak.blur();
            modalKontak.show();
        });
    }

    /* ==========================================================
       SUBMIT FORM TAMBAH KONTAK
    ========================================================== */
    if (formTambahKontak && storeKontakUrl) {

        formTambahKontak.addEventListener("submit", async function (e) {

            e.preventDefault();

            const button   = document.getElementById("btnSimpanKontak");
            const formData = new FormData(this);

            if (button) button.disabled = true;

            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {

                const response = await fetch(storeKontakUrl, {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) throw data;

                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Kontak berhasil ditambahkan',
                    timer: 1500,
                    showConfirmButton: false
                });

                this.reset();
                if (modalKontak) modalKontak.hide();

                if (typeof loadKontak === "function") {
                    loadKontak(data.id);
                }

            } catch (err) {

                let message = 'Terjadi kesalahan';

                if (err.errors) {
                    message = Object.values(err.errors).join('<br>');
                } else if (err.message) {
                    message = err.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: message
                });

                console.error(err);

            } finally {
                if (button) button.disabled = false;
            }

        });
    }

    /* ==========================================================
       SUBMIT FORM PENGAJUAN
    ========================================================== */
    if (formPengajuan && storePengajuanUrl) {

        formPengajuan.addEventListener('submit', async function (e) {

            e.preventDefault();

            const submitBtn = formPengajuan.querySelector('button[type="submit"]');
            const formData  = new FormData(this);

            if (submitBtn) submitBtn.disabled = true;

            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            try {

                const response = await fetch(storePengajuanUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) throw data;

                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Pengajuan biaya berhasil disimpan'
                });

                this.reset();
                modalPengajuan.hide();

            } catch (error) {

                let message = 'Terjadi kesalahan server';

                if (error.errors) {
                    message = Object.values(error.errors).join('<br>');
                } else if (error.message) {
                    message = error.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: message
                });

                console.error(error);

            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }

        });
    }

});