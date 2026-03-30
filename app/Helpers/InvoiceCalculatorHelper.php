<?php

namespace App\Helpers;

use App\Models\Invoice;

class InvoiceCalculatorHelper
{
    protected Invoice $invoice;

    protected float $subtotal = 0;
    protected float $diskonPo = 0;
    protected float $afterDiskonPo = 0;

    protected float $nominalInvoice = 0;

    protected float $diskonInvoice = 0;
    protected float $afterDiskonInvoice = 0;

    protected float $ppn = 0;
    protected float $pph = 0;

    protected float $totalAkhir = 0;

    public static function from(Invoice $invoice): self
    {
        return new self($invoice);
    }

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function calculate(): array
    {
        $this->calculateSubtotal();
        $this->calculateDiskonPo();
        $this->calculateNominalInvoice();
        $this->calculateDiskonInvoice();
        $this->calculatePajak();
        $this->calculateTotalAkhir();

        return [
            'subtotal' => $this->subtotal,

            'diskon_po' => $this->diskonPo,
            'after_diskon_po' => $this->afterDiskonPo,

            'nominalInvoice' => $this->nominalInvoice,

            'diskon_invoice' => $this->diskonInvoice,
            'after_diskon_invoice' => $this->afterDiskonInvoice,

            'ppn' => $this->ppn,
            'pph' => $this->pph,
            'totalAkhir' => $this->totalAkhir,
        ];
    }

    protected function calculateSubtotal()
    {
        $quotation = $this->invoice->po->quotation ?? null;

        if ($quotation && $quotation->harga_tipe === 'gabungan') {
            $this->subtotal = (float) $quotation->harga_gabungan;
            return;
        }

        $subtotal = 0;
        foreach ($this->invoice->produk as $item) {
            $subtotal += ($item->qty ?? 0) * ($item->harga_satuan ?? 0);
        }

        $this->subtotal = $subtotal;
    }

    protected function calculateDiskonPo()
    {
        $quotation = $this->invoice->po->quotation ?? null;

        if (!$quotation) return;

        $jenis = $quotation->diskon_tipe ?? null;
        $nilai = $quotation->diskon_nilai ?? 0;

        if ($nilai > 0) {
            $this->diskonPo = $jenis === 'persen'
                ? $this->subtotal * $nilai / 100
                : $nilai;
        }

        if ($this->diskonPo > $this->subtotal) {
            $this->diskonPo = $this->subtotal;
        }

        $this->afterDiskonPo = $this->subtotal - $this->diskonPo;
    }

    protected function calculateNominalInvoice()
    {
        $base = $this->afterDiskonPo > 0
            ? $this->afterDiskonPo
            : $this->subtotal;

        $persen = $this->invoice->persentase_termin ?? 0;

        $this->nominalInvoice = $base * $persen / 100;
        $this->afterDiskonInvoice = $this->nominalInvoice;
    }

    protected function calculateDiskonInvoice()
    {
        $jenis = $this->invoice->tipe_diskon ?? null;
        $nilai = $this->invoice->nilai_diskon ?? 0;

        if ($nilai > 0) {
            $this->diskonInvoice = $jenis === 'persen'
                ? $this->nominalInvoice * $nilai / 100
                : $nilai;
        }

        if ($this->diskonInvoice > $this->nominalInvoice) {
            $this->diskonInvoice = $this->nominalInvoice;
        }

        $this->afterDiskonInvoice = $this->nominalInvoice - $this->diskonInvoice;
    }

    protected function calculatePajak()
    {
        $this->ppn = (float) ($this->invoice->ppn ?? 0);
        $this->pph = 0;
    }

    protected function calculateTotalAkhir()
    {
        $base = $this->afterDiskonInvoice > 0
            ? $this->afterDiskonInvoice
            : $this->nominalInvoice;

        $this->totalAkhir = $base + $this->ppn - $this->pph;
    }
}
// namespace App\Helpers;

// use App\Models\Invoice;

// class InvoiceCalculatorHelper
// {
    // protected Invoice $invoice;
    // protected float $subtotal = 0;
    // protected float $nominalInvoice = 0;
    // protected float $diskon = 0;
    // protected float $totalAfterDiscount = 0;
    // protected float $ppn = 0;
    // protected float $pph = 0;
    // protected float $totalAkhir = 0;

    // public static function from(Invoice $invoice): self
    // {
    //     return new self($invoice);
    // }

    // public function __construct(Invoice $invoice)
    // {
    //     $this->invoice = $invoice;
    // }

    // public function calculate(): array
    // {
    //     $this->calculateSubtotal();
    //     $this->calculateNominalInvoice();
    //     $this->calculateDiskon();
    //     $this->calculatePajak();
//         $this->calculateTotalAkhir();

//         return [
//             'subtotal' => $this->subtotal,
//             'nominalInvoice' => $this->nominalInvoice,
//             'diskon' => $this->diskon,
//             'totalAfterDiscount' => $this->totalAfterDiscount,
//             'ppn' => $this->ppn,
//             'pph' => $this->pph,
//             'totalAkhir' => $this->totalAkhir,
//         ];
//     }

// protected function calculateSubtotal()
// {
//     $quotation = $this->invoice->po->quotation;

//     // 🔥 JIKA HARGA GABUNGAN
//     if ($quotation && $quotation->harga_tipe === 'gabungan') {
//         $this->subtotal = (float) $quotation->harga_gabungan;
//         return;
//     }

    // 🔽 JIKA HARGA SATUAN
    // $subtotal = 0;
    // foreach ($this->invoice->produk as $item) {
    //     $subtotal += ($item->qty ?? 0) * ($item->harga_satuan ?? 0);
    // }

    // $this->subtotal = $subtotal;

        // dd([
        //     'subtotal' => $this->subtotal,
        //     'harga_gabungan' => $this->invoice->po->quotation->harga_gabungan,
        //     'produk' => $this->invoice->produk,
        // ]);
    // }

    // protected function calculateNominalInvoice()
    // {
        // $persen = $this->invoice->persentase_termin ?? 0;
        // $this->nominalInvoice = $this->subtotal * $persen / 100;
        // $this->nominalInvoice = (float) $this->invoice->nominal_invoice;
    //     $this->totalAfterDiscount = (float) ($this->invoice->total_after_diskon_inv ?? $this->nominalInvoice);
    //     $this->diskon = (float) ($this->invoice->nilai_diskon ?? 0);    
    //     $this->totalAfterDiscount = $this->nominalInvoice; // sementara sebelum diskon
    // }

    // protected function calculateDiskon()
    // {
    //     $jenis = $this->invoice->tipe_diskon ?? null; // 'persen' / 'nominal'
    //     $nilai = $this->invoice->nilai_diskon ?? 0;

    //     $diskon = 0;
    //     if ($nilai > 0) {
    //         $diskon = $jenis === 'persen'
    //             ? $this->nominalInvoice * $nilai / 100
    //             : $nilai;
    //     }

    //     if ($diskon > $this->nominalInvoice) {
    //         $diskon = $this->nominalInvoice;
    //     }

    //     $this->diskon = $diskon;
    //     $this->totalAfterDiscount = $this->nominalInvoice - $diskon;

        // dd([
        //     'tipe_diskon'  => $this->invoice->tipe_diskon,
        //     'nilai_diskon' => $this->invoice->nilai_diskon,
        //     'nominalInvoice' => $this->nominalInvoice,
        // ]);
    // }

    // protected function calculatePajak()
    // {
    //     $this->ppn = (float) $this->invoice->ppn;
    //     $this->pph = 0; // atau ambil dari relasi kalau ada
    // }

    // protected function calculatePajak()
    // {
    //     $base = $this->totalAfterDiscount;

    //     $ppn = 0;
    //     $pph = 0;

    //     foreach ($this->invoice->pajak as $tax) {
    //         if (!$tax->coa) continue;

    //         $rate = $tax->coa->nilai_coa ?? 0;
    //         $name = strtolower($tax->coa->nama_akun ?? '');

    //         $amount = $base * $rate / 100;

    //         if (str_contains($name, 'pph')) {
    //             $pph += $amount;
    //         } else {
    //             $ppn += $amount;
    //         }
    //     }

    //     $this->ppn = $ppn;
    //     $this->pph = $pph;
    // }

    // protected function calculateTotalAkhir()
    // {
    //     $this->totalAkhir = $this->totalAfterDiscount + $this->ppn - $this->pph;
    // }
// }
