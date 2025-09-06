<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function attestation(Inventory $inventory)
    {
        // Charger aussi qty_theoretical du produit pour le fallback
        //$items = $inventory->items()
        //    ->with(['product:id,name,qty_theoretical'])
        //    ->get(['id','inventory_id','product_id','theoretical_qty','real_qty','notes']);

        $items = $inventory
            ->items()
            ->with(['product:id,name,qty_theoretical'])
            ->get(['id', 'inventory_id', 'product_id', 'theoretical_qty_at_snapshot as theoretical_qty', 'real_qty', 'notes']);

        $data = [
            'inventory' => $inventory->fresh(),
            'items' => $items,
            'company' => config('app.name'),
            'generated_at' => now(),
        ];

        $pdf = Pdf::loadView('pdf.attestation', $data)->setPaper('a4', 'portrait')->set_option('isHtml5ParserEnabled', true)->set_option('isRemoteEnabled', true)->set_option('defaultFont', 'DejaVu Sans');

        return $pdf->download("attestation_{$inventory->id}.pdf");
    }
}
