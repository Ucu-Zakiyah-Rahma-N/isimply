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
