<?php

namespace App\Helpers;

use App\Models\Invoice;

class TotalInvoiceHelper
{
    /**
     * Hitung total invoice sesuai termin, diskon, dan pajak
     */
public static function calculateTotal(Invoice $invoice)
{
    // 1️⃣ Subtotal dari item
    $subtotal = $invoice->produk->sum(function ($item) {
        return ($item->qty ?? 0) * ($item->harga_satuan ?? 0);
    });

    // 2️⃣ Cek gabungan
    if ($subtotal == 0 && isset($invoice->po->quotation->harga_gabungan)) {
        $subtotal = $invoice->po->quotation->harga_gabungan;
    }

    // 3️⃣ Nominal sesuai termin
    $nominalTermin = $subtotal * ($invoice->persentase_termin / 100);

    // 4️⃣ Diskon diterapkan setelah nominal termin
    $diskon = 0;
    if ($invoice->tipe_diskon && $invoice->nilai_diskon) {
        if ($invoice->tipe_diskon === 'nominal') {
            $diskon = $invoice->nilai_diskon;
        } else {
            $diskon = $nominalTermin * $invoice->nilai_diskon / 100;
        }

        if ($diskon > $nominalTermin) $diskon = $nominalTermin;
    }

    $afterDiscount = $nominalTermin - $diskon;

    // 5️⃣ Hitung pajak
    $totalPPN = 0;
    $totalPPH = 0;
    foreach ($invoice->pajak as $tax) {
        $rate = $tax->coa->nilai_coa ?? 0;
        $name = strtolower($tax->coa->nama_akun ?? '');
        $amount = $afterDiscount * $rate / 100;

        if (str_contains($name, 'pph')) $totalPPH += $amount;
        else $totalPPN += $amount;
    }

    // 6️⃣ Total akhir
    $total = $afterDiscount + $totalPPN - $totalPPH;

    return $total;
}

public static function calculateTotalDebug(Invoice $invoice)
{
    $debug = [];

    // 1️⃣ Subtotal dari item
    $subtotal = $invoice->produk->sum(function ($item) {
        return ($item->qty ?? 0) * ($item->harga_satuan ?? 0);
    });

    // 2️⃣ Cek gabungan
    if ($subtotal == 0 && isset($invoice->po->quotation->harga_gabungan)) {
        $subtotal = $invoice->po->quotation->harga_gabungan;
    }

    $debug['subtotal'] = $subtotal;

    // 3️⃣ Nominal sesuai termin
    $persentaseTermin = $invoice->persentase_termin ?? 100;
    $nominalTermin = $subtotal * ($persentaseTermin / 100);
    $debug['persentase_termin'] = $persentaseTermin;
    $debug['nominal_termin'] = $nominalTermin;

    // 4️⃣ Diskon diterapkan setelah nominal termin
    $diskon = 0;
    if ($invoice->tipe_diskon && $invoice->nilai_diskon) {
        if ($invoice->tipe_diskon === 'nominal') {
            $diskon = $invoice->nilai_diskon;
        } else {
            $diskon = $nominalTermin * $invoice->nilai_diskon / 100;
        }

        if ($diskon > $nominalTermin) $diskon = $nominalTermin;
    }

    $afterDiscount = $nominalTermin - $diskon;

    $debug['diskon'] = $diskon;
    $debug['after_discount'] = $afterDiscount;

    // 5️⃣ Hitung pajak
    $totalPPN = 0;
    $totalPPH = 0;
    $taxesDetail = [];

    foreach ($invoice->pajak as $tax) {
        $rate = $tax->coa->nilai_coa ?? 0;
        $name = $tax->coa->nama_akun ?? '';
        $amount = $afterDiscount * $rate / 100;

        $taxesDetail[] = [
            'name' => $name,
            'rate' => $rate,
            'amount' => $amount
        ];

        if (str_contains(strtolower($name), 'pph')) $totalPPH += $amount;
        else $totalPPN += $amount;
    }

    $debug['taxes_detail'] = $taxesDetail;
    $debug['total_ppn'] = $totalPPN;
    $debug['total_pph'] = $totalPPH;

    // 6️⃣ Total akhir
    $totalAkhir = $afterDiscount + $totalPPN - $totalPPH;
    $debug['total_akhir'] = $totalAkhir;

    // 7️⃣ Sertakan list produk juga
    $debug['produk'] = $invoice->produk->map(function($item){
        return [
            'perizinan_id' => $item->perizinan_id,
            'qty' => $item->qty,
            'harga_satuan' => $item->harga_satuan,
            'jumlah' => ($item->qty ?? 0) * ($item->harga_satuan ?? 0),
        ];
    });

    return $debug;
}
public static function calculateTerminBreakdown(Invoice $invoice)
{
    // 1️⃣ Subtotal PO
    $subtotal = $invoice->produk->sum(function ($item) {
        return ($item->qty ?? 0) * ($item->harga_satuan ?? 0);
    });

    if ($subtotal == 0 && isset($invoice->po->quotation->harga_gabungan)) {
        $subtotal = $invoice->po->quotation->harga_gabungan;
    }

    // 2️⃣ Nominal Termin
    $persentaseTermin = $invoice->persentase_termin ?? 100;
    $nominalTermin = $subtotal * ($persentaseTermin / 100);

    // 3️⃣ DPP (karena harga include PPN 12%)
    $dpp = $nominalTermin * 11 / 12;

    // 4️⃣ PPN = 12% dari DPP
    $ppn = $dpp * 12 / 100;

    // 5️⃣ PPh tetap dari DPP (ambil dari COA)
    $pph = 0;
    foreach ($invoice->pajak as $tax) {
        $name = strtolower($tax->coa->nama_akun ?? '');
        $rate = $tax->coa->nilai_coa ?? 0;

        if (str_contains($name, 'pph')) {
            $pph += $nominalTermin * $rate / 100;
        }
    }

    return [
        'nominal_termin' => $nominalTermin,
        'dpp' => $dpp,
        'ppn' => $ppn,
        'pph' => $pph,
    ];
}


}
