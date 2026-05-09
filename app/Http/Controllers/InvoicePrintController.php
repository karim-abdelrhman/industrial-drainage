<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\SystemSetting;
use Illuminate\View\View;

class InvoicePrintController extends Controller
{
    public function html(Invoice $invoice): View
    {
        return view('invoices.print', $this->viewData($invoice));
    }

    /** @return array<string, mixed> */
    private function viewData(Invoice $invoice): array
    {
        $invoice->loadMissing([
            'establishment',
            'sample',
            'items.pollutant',
            'items.violationRule',
            'items.violation',
        ]);

        return [
            'invoice' => $invoice,
            'establishment' => $invoice->establishment,
            'industrialManager' => SystemSetting::getString(
                'industrial_manager_name',
                '................................'
            ),
            'commercialManager' => SystemSetting::getString(
                'commercial_manager_name',
                '................................'
            ),
            'labManager' => SystemSetting::getString(
                'lab_manager_name',
                '................................'
            ),
        ];
    }
}
