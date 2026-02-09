console.log('invoice_script loaded');

/* =======================
   FORMAT RUPIAH
======================= */
function rupiah(num) {
  return Math.round(num).toLocaleString('id-ID');
}

/* =======================
   RECALC
======================= */
function recalc() {
  let grossSubtotal = 0;

  // HITUNG ITEM
  document.querySelectorAll('.item-row').forEach(row => {
    const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
    const price = parseFloat(row.querySelector('.price')?.value) || 0;

    const total = qty * price;
    grossSubtotal += total;

    const subtotalInput = row.querySelector('.subtotal');
    if (subtotalInput) subtotalInput.value = rupiah(total);
  });

  /* =======================
     DISKON
  ======================= */
  const discountType = document.getElementById('discountType').value;
  const discountValue = parseFloat(document.getElementById('discountValue').value) || 0;

  let discountAmount = 0;
  if (discountType === 'persen') {
    discountAmount = grossSubtotal * (discountValue / 100);
  } else {
    discountAmount = discountValue;
  }

  if (discountAmount > grossSubtotal) {
    discountAmount = grossSubtotal;
  }

  const netSubtotal = grossSubtotal - discountAmount;

  /* =======================
     PAJAK
  ======================= */
  const taxes = $('#taxSelect').select2('data') || [];

  let totalPPN = 0;
  let totalPPH = 0;

  taxes.forEach(tax => {
    const rate = parseFloat(tax.id) || 0;
    const type = tax.element.dataset.type;

    const amount = netSubtotal * (rate / 100);

    if (type === 'pph') {
      totalPPH += amount;
    } else {
      totalPPN += amount;
    }
  });

  /* =======================
     FINAL TOTAL
     subtotal - diskon + ppn - pph
  ======================= */
  const finalTotal = netSubtotal + totalPPN - totalPPH;

  /* =======================
     RENDER UI
  ======================= */
  document.getElementById('subtotal').innerText = rupiah(netSubtotal);
  document.getElementById('discountAmount').innerText = rupiah(discountAmount);
  document.getElementById('finalTotal').innerText = rupiah(finalTotal);
  document.getElementById('grandTotal').innerText = rupiah(finalTotal);

  /* =======================
     SET HIDDEN INPUT (INI PENTING!)
  ======================= */
  document.getElementById('subtotalInput').value = Math.round(netSubtotal);
  document.getElementById('totalInput').value = Math.round(finalTotal);
  document.getElementById('discountTypeInput').value = discountType;
  document.getElementById('discountValueInput').value = discountValue;

  console.log('SUBMIT DATA', {
    subtotal: netSubtotal,
    total: finalTotal,
    diskon: discountAmount,
    ppn: totalPPN,
    pph: totalPPH
  });
}

/* =======================
   EVENTS
======================= */
document.addEventListener('input', e => {
  if (
    e.target.classList.contains('qty') ||
    e.target.classList.contains('price') ||
    e.target.id === 'discountValue' ||
    e.target.id === 'discountType'
  ) recalc();
});

$(document).ready(function () {
  $('#taxSelect').select2({
    placeholder: 'Pilih Pajak',
    closeOnSelect: false,
    width: '100%'
  });

  $('#taxSelect').on('change', recalc);
  recalc();
});
document.getElementById('invoiceForm').addEventListener('submit', function () {
  recalc();

  console.log('SUBMIT FINAL', {
    subtotal: document.getElementById('subtotalInput').value,
    total: document.getElementById('totalInput').value,
    tipe_diskon: document.getElementById('discountTypeInput').value,
    nilai_diskon: document.getElementById('discountValueInput').value
  });
});

$(document).ready(function () {
  recalc();
});
