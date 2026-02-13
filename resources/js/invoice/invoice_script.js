document.addEventListener('DOMContentLoaded', function () {

  const itemsContainer = document.getElementById('items');

  const subtotalEl = document.getElementById('subtotal');
  const subtotalInput = document.getElementById('subtotalInput');

  const discountType = document.getElementById('discountType');
  const discountValue = document.getElementById('discountValue');
  const discountAmountEl = document.getElementById('discountAmount');
  const discountTypeInput = document.getElementById('discountTypeInput');
  const discountValueInput = document.getElementById('discountValueInput');

  const taxSelect = document.getElementById('taxSelect');
  const ppnRow = document.getElementById('ppnRow');
  const ppnRateEl = document.getElementById('ppnRate');
  const ppnAmountEl = document.getElementById('ppnAmount');

  const includeTaxCheckbox = document.getElementById('includeTax');

  const finalTotalEl = document.getElementById('finalTotal');
  const totalInput = document.getElementById('totalInput');
  const grandTotalEl = document.getElementById('grandTotal');

  /* ===============================
   * FORMAT RUPIAH
   * =============================== */
  const formatRupiah = (angka) =>
    Math.round(angka).toLocaleString('id-ID');

  /* ===============================
   * HITUNG SUBTOTAL PER ITEM
   * =============================== */
  function calculateItemSubtotal(row) {
    const qty = parseFloat(row.querySelector('.qty')?.value) || 0;
    const price = parseFloat(row.querySelector('.price')?.value) || 0;
    const subtotal = qty * price;

    const subtotalInputItem = row.querySelector('.subtotal');
    if (subtotalInputItem) {
      subtotalInputItem.value = formatRupiah(subtotal);
      subtotalInputItem.dataset.raw = subtotal;
    }

    return subtotal;
  }

  /* ===============================
   * HITUNG SEMUA ITEM
   * =============================== */
  function calculateItemsSubtotal() {
    let subtotal = 0;

    document.querySelectorAll('.item-row').forEach(row => {
      subtotal += calculateItemSubtotal(row);
    });

    subtotalEl.textContent = formatRupiah(subtotal);
    subtotalInput.value = subtotal;

    return subtotal;
  }

  /* ===============================
   * HITUNG DISKON
   * =============================== */
  function calculateDiscount(subtotal) {
    const type = discountType.value;
    const value = parseFloat(discountValue.value) || 0;
    let discount = 0;

    if (type === 'persen') {
      discount = subtotal * (value / 100);
    } else {
      discount = value;
    }

    discountTypeInput.value = type;
    discountValueInput.value = value;

    discountAmountEl.textContent = formatRupiah(discount);
    return discount;
  }

  /* ===============================
   * HITUNG PAJAK
   * =============================== */
  function calculateTax(baseAmount) {
    let totalRate = 0;

    Array.from(taxSelect.selectedOptions).forEach(opt => {
      if (opt.value !== 'add_new') {
        totalRate += parseFloat(opt.dataset.nilai) || 0;
      }
    });

    if (totalRate > 0) {
      ppnRow.style.display = 'flex';
      ppnRateEl.textContent = totalRate;
    } else {
      ppnRow.style.display = 'none';
    }

    let taxAmount = 0;

    if (includeTaxCheckbox.checked && totalRate > 0) {
      taxAmount = baseAmount - (baseAmount / (1 + totalRate / 100));
    } else {
      taxAmount = baseAmount * (totalRate / 100);
    }

    ppnAmountEl.textContent = formatRupiah(taxAmount);
    return taxAmount;
  }

  /* ===============================
   * HITUNG GRAND TOTAL
   * =============================== */
  function calculateTotal() {
    const subtotal = calculateItemsSubtotal();
    const discount = calculateDiscount(subtotal);
    const afterDiscount = subtotal - discount;

    const tax = calculateTax(afterDiscount);

    let finalTotal = 0;

    if (includeTaxCheckbox.checked) {
      finalTotal = afterDiscount;
    } else {
      finalTotal = afterDiscount + tax;
    }

    finalTotalEl.textContent = formatRupiah(finalTotal);
    grandTotalEl.textContent = formatRupiah(finalTotal);
    totalInput.value = finalTotal;
  }

  /* ===============================
   * EVENT LISTENER
   * =============================== */
  itemsContainer.addEventListener('input', function (e) {
    if (
      e.target.classList.contains('qty') ||
      e.target.classList.contains('price')
    ) {
      calculateTotal();
    }
  });

  discountType.addEventListener('change', calculateTotal);
  discountValue.addEventListener('input', calculateTotal);
  taxSelect.addEventListener('change', calculateTotal);
  includeTaxCheckbox.addEventListener('change', calculateTotal);

  /* ===============================
   * INIT
   * =============================== */
  calculateTotal();

});
