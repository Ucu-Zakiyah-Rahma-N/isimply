@extends('app.template')

@section('content')
<div class="card shadow-sm">
  <div class="card-header bg-primary text-white">
    <h5 class="mb-0">Form Tahapan Dokumen</h5>
  </div>

  <div class="card-body">
      @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

  <form action="{{ route('projects.store') }}" method="POST">
      @csrf
        <input type="hidden" name="po_id" value="{{ $po_id }}">

      {{-- Pilih PIC --}}
      <div class="mb-3">
        <label for="marketing_id" >PIC Projek</label>
        <select name="marketing_id" id="marketing_id" class="form-select @error('marketing_id') is-invalid @enderror"required>
          <option value="">-- Pilih PIC --</option>
          @foreach($marketingInternal as $m)
            <option value="{{ $m->id }}">{{ $m->nama }}</option>
          @endforeach
        </select>
        @error('marketing_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <hr>

      {{-- COLLAPSIBLE: COLLECT DOKUMEN --}}
      <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center" 
             data-bs-toggle="collapse" data-bs-target="#collectCollapse" style="cursor:pointer;">
          <h6 class="fw-bold mb-0 text-primary">Pilih Jenis Collect Dokumen</h6>
          <i class="bi bi-chevron-down"></i>
        </div>

        <div id="collectCollapse" class="collapse show">
          <div class="card-body">
            <p class="text-muted small">Klik sesuai urutan pengerjaan. Nomor urutan akan muncul otomatis di kanan.</p>
            <div class="row">
              @foreach ($perizinan as $item)
                <div class="col-md-4 mb-2">
                  <div class="form-check d-flex align-items-center justify-content-between border rounded p-2">
                    <div>
                      <input type="checkbox" class="form-check-input me-2 collect-checkbox" id="collect_{{ $item->id }}" name="perizinan[]" value="{{ $item->id }}">
                      <label class="form-check-label" for="collect_{{ $item->id }}">{{ $item->jenis }}</label>
                    </div>
                    <span class="badge bg-secondary order-badge d-none">0</span>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      {{-- COLLAPSIBLE: TAHAPAN OPSIONAL --}}
      <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center" 
             data-bs-toggle="collapse" data-bs-target="#opsionalCollapse" style="cursor:pointer;">
          <h6 class="fw-bold mb-0 text-primary">Tahapan Lanjutan (Opsional)</h6>
          <i class="bi bi-chevron-down"></i>
        </div>

        <div id="opsionalCollapse" class="collapse show">
          <div class="card-body">
            <p class="text-muted small">Pilih tahapan yang berlaku dan tentukan rencana serta persentasenya.</p>

            <!--<div class="row">-->
            <!--  @foreach($tahapanOpsional as $tahap)-->
            <!--    <div class="col-md-6 mb-2">-->
            <!--      <div class="d-flex align-items-center justify-content-between border rounded p-2 shadow-sm bg-light">-->
            <!--        <div class="d-flex align-items-center">-->
            <!--          <input type="checkbox" -->
            <!--                 name="tahapan_opsional[]" -->
            <!--                 value="{{ $tahap->id }}" -->
            <!--                 data-nama="{{ $tahap->nama_tahapan }}" -->
            <!--                 id="tahap_{{ Str::slug($tahap->nama_tahapan, '_') }}" -->
            <!--                 class="form-check-input me-2 tahapan-checkbox">-->
            <!--          <label class="form-check-label fw-semibold" for="tahap_{{ Str::slug($tahap->nama_tahapan, '_') }}">-->
            <!--            {{ $tahap->nama_tahapan }}-->
            <!--          </label>-->
            <!--        </div>-->
            <!--      </div>-->
            <!--    </div>-->
            <!--  @endforeach-->
            <!--</div>-->

            @php
              $tahapanSorted = $tahapanOpsional->values();
              $total = $tahapanSorted->count();
              $half = ceil($total / 2);
            
              $kolomKiri  = $tahapanSorted->slice(0, $half);
              $kolomKanan = $tahapanSorted->slice($half);
            @endphp
            
            <div class="row">
              {{-- KOLOM KIRI --}}
              <div class="col-md-6">
                @foreach($kolomKiri as $tahap)
                  <div class="mb-2">
                    <div class="d-flex align-items-center justify-content-between border rounded p-2 shadow-sm bg-light">
                      <div class="d-flex align-items-center">
                        <input type="checkbox"
                               name="tahapan_opsional[]"
                               value="{{ $tahap->id }}"
                               data-nama="{{ $tahap->nama_tahapan }}"
                               id="tahap_{{ Str::slug($tahap->nama_tahapan, '_') }}"
                               class="form-check-input me-2 tahapan-checkbox">
                        <label class="form-check-label fw-semibold"
                               for="tahap_{{ Str::slug($tahap->nama_tahapan, '_') }}">
                          {{ $tahap->nama_tahapan }}
                        </label>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            
              {{-- KOLOM KANAN --}}
              <div class="col-md-6">
                @foreach($kolomKanan as $tahap)
                  <div class="mb-2">
                    <div class="d-flex align-items-center justify-content-between border rounded p-2 shadow-sm bg-light">
                      <div class="d-flex align-items-center">
                        <input type="checkbox"
                               name="tahapan_opsional[]"
                               value="{{ $tahap->id }}"
                               data-nama="{{ $tahap->nama_tahapan }}"
                               id="tahap_{{ Str::slug($tahap->nama_tahapan, '_') }}"
                               class="form-check-input me-2 tahapan-checkbox">
                        <label class="form-check-label fw-semibold"
                               for="tahap_{{ Str::slug($tahap->nama_tahapan, '_') }}">
                          {{ $tahap->nama_tahapan }}
                        </label>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>

            <div id="containerTahapanLanjutan" class="mt-4"></div>
          </div>
        </div>
      </div>

      <hr>

      {{-- Total Persentase --}}
      <div class="mt-4 text-end">
        <span class="fw-semibold me-2">Total Persentase:</span>
        <span id="totalPersen" class="fw-bold text-primary">0%</span>
      </div>

      <div class="mt-4 text-end">
        <button type="submit" class="btn btn-success">Buat</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

  // ===============================
  // DATA PERIZINAN DARI PO
  // ===============================
  const poPerizinan = @json($poPerizinan).map(Number);
  console.log('Perizinan dari PO:', poPerizinan);

  // ===============================
  // ELEMENT
  // ===============================
  const totalText = document.getElementById('totalPersen');
  const containerTahapan = document.getElementById('containerTahapanLanjutan');
  const collectCheckboxes = document.querySelectorAll('.collect-checkbox');
  const tahapanCheckboxes = document.querySelectorAll('.tahapan-checkbox');

  let orderPerizinan = 0;

  // ===============================
  // HITUNG TOTAL PERSENTASE
  // ===============================
  function updateTotal() {
    let total = 0;

    const surveyVal = parseFloat(
      document.querySelector('input[name="persentase_survey"]')?.value
    ) || 0;

    total += surveyVal;

    document.querySelectorAll('input[name^="persentase_opsional"]').forEach(el => {
      total += parseFloat(el.value) || 0;
    });

    totalText.textContent = total + '%';
    totalText.classList.toggle('text-danger', total !== 100);
  }

  // ===============================
  // SUB SURVEY
  // ===============================
  function updateSubSurvey() {
    const persenUtama = parseFloat(
      document.getElementById('persenSurveyUtama')?.value
    ) || 0;

    let totalSub = 0;
    document.querySelectorAll('.persen-sub').forEach(input => {
      totalSub += parseFloat(input.value) || 0;
    });

    const totalSubSurvey = document.getElementById('totalSubSurvey');
    if (totalSubSurvey) {
      totalSubSurvey.textContent = totalSub + '%';
      totalSubSurvey.classList.toggle('text-danger', totalSub !== 100);
    }

    document.querySelectorAll('.persen-sub').forEach(input => {
      const riil = ((parseFloat(input.value) || 0) * persenUtama / 100).toFixed(2);
      input.closest('tr').querySelector('.nilai-riil').textContent = riil + '%';
    });

    updateTotal();
  }

  // ===============================
  // TAHAPAN OPSIONAL (URUTAN KLIK)
  // ===============================
  tahapanCheckboxes.forEach(cb => {
    cb.addEventListener('change', function () {

      const tahapNama = this.dataset.nama;
      const slug = this.id.replace('tahap_', '');
      const formId = 'form_' + slug;
      const hiddenId = 'hidden_' + slug;

      // ===============================
      // CHECK
      // ===============================
      if (this.checked) {

        // cegah dobel
        if (document.getElementById(hiddenId)) return;

        // hidden input (URUTAN KLIK)
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'tahapan_input[]';
        hidden.value = slug;
        hidden.id = hiddenId;
        containerTahapan.appendChild(hidden);

        let html = '';

        if (tahapNama.toLowerCase() === 'survey') {
          html = `
            <div id="${formId}" class="card shadow-sm mb-3 border-start border-4 border-primary">
              <div class="card-body py-3">
                <h6 class="fw-bold text-primary mb-2">Survey</h6>
                <div class="row g-2">
                  <div class="col-md-5">
                    <label class="form-label small fw-semibold">Rencana</label><br>
                    <input type="date" name="rencana_mulai[survey]" class="form-control form-control-sm d-inline-block w-auto">
                    -
                    <input type="date" name="rencana_selesai[survey]" class="form-control form-control-sm d-inline-block w-auto">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Persentase</label>
                    <input type="number" id="persenSurveyUtama" name="persentase_survey"
                      class="form-control form-control-sm persen-input" min="0" max="100" value="0">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small fw-semibold">Petugas</label>
                    <textarea name="personil[survey]" class="form-control form-control-sm" rows="1"></textarea>
                  </div>
                </div>
              </div>
            </div>
          `;
        } else {
          html = `
            <div id="${formId}" class="card shadow-sm mb-3 border-start border-4 border-primary">
              <div class="card-body py-3">
                <h6 class="fw-bold text-primary mb-2">${tahapNama}</h6>
                <div class="row g-2">
                  <div class="col-md-5">
                    <label class="form-label small fw-semibold">Rencana</label><br>
                    <input type="date" name="rencana_mulai_opsional[${slug}]" class="form-control form-control-sm d-inline-block w-auto">
                    -
                    <input type="date" name="rencana_selesai_opsional[${slug}]" class="form-control form-control-sm d-inline-block w-auto">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold">Persentase</label>
                    <input type="number" name="persentase_opsional[${slug}]"
                      class="form-control form-control-sm persen-input" min="0" max="100" value="0">
                  </div>
                </div>
              </div>
            </div>
          `;
        }

        containerTahapan.insertAdjacentHTML('beforeend', html);

        // event
        document.getElementById('persenSurveyUtama')?.addEventListener('input', updateSubSurvey);
        document.querySelectorAll('.persen-sub').forEach(el => el.addEventListener('input', updateSubSurvey));
        document.querySelectorAll('.persen-input').forEach(el => el.addEventListener('input', updateTotal));

        updateTotal();

      // ===============================
      // UNCHECK
      // ===============================
      } else {
        document.getElementById(hiddenId)?.remove();
        document.getElementById(formId)?.remove();
        updateTotal();
      }
    });
  });

  // ===============================
  // COLLECT DOKUMEN (URUTAN + VALIDASI PO)
  // ===============================
  collectCheckboxes.forEach(cb => {
    cb.addEventListener('change', function () {

      const selectedId = parseInt(this.value);
      const badge = this.closest('.form-check').querySelector('.order-badge');
      const hiddenId = 'hidden_perizinan_' + selectedId;

      // VALIDASI PO
      if (this.checked && !poPerizinan.includes(selectedId)) {
        alert('⚠ Jenis perizinan tidak sesuai PO');
        this.checked = false;
        return;
      }

      // CHECK
      if (this.checked) {

        if (document.getElementById(hiddenId)) return;

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'perizinan_input[]';
        hidden.value = selectedId;
        hidden.id = hiddenId;
        containerTahapan.appendChild(hidden);

        orderPerizinan++;
        this.dataset.order = orderPerizinan;
        badge.textContent = orderPerizinan;
        badge.classList.remove('d-none', 'bg-secondary');
        badge.classList.add('bg-primary');

      // UNCHECK
      } else {

        document.getElementById(hiddenId)?.remove();

        const removedOrder = parseInt(this.dataset.order);
        this.dataset.order = '';

        badge.textContent = '';
        badge.classList.add('d-none', 'bg-secondary');
        badge.classList.remove('bg-primary');

        collectCheckboxes.forEach(other => {
          if (other.checked && parseInt(other.dataset.order) > removedOrder) {
            other.dataset.order--;
            other.closest('.form-check')
              .querySelector('.order-badge').textContent = other.dataset.order;
          }
        });

        orderPerizinan--;
      }
    });
  });

});
</script>

@endsection
