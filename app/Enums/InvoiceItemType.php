<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum InvoiceItemType: string implements HasLabel
{
    case PollutantCharge = 'pollutant_charge';
    case CollectionFee = 'collection_fee';
    case AdminFee = 'admin_fee';
    case AnalysisFee = 'analysis_fee';
    case IssuanceFee = 'issuance_fee';
    case Vat = 'vat';
    case Rounding = 'rounding';

    public function getLabel(): string
    {
        return match ($this) {
            InvoiceItemType::PollutantCharge => 'رسوم الملوثات',
            InvoiceItemType::CollectionFee => 'رسوم جمع العينة',
            InvoiceItemType::AdminFee => 'رسوم إدارية',
            InvoiceItemType::AnalysisFee => 'رسوم التحليل',
            InvoiceItemType::IssuanceFee => 'رسوم الإصدار',
            InvoiceItemType::Vat => 'ضريبة القيمة المضافة',
            InvoiceItemType::Rounding => 'تسوية',
        };
    }
}
