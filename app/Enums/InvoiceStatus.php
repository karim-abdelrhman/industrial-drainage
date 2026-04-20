<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InvoiceStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Paid = 'paid';
    case Overdue = 'overdue';

    public function getLabel(): string
    {
        return match ($this) {
            InvoiceStatus::Draft => 'مسودة',
            InvoiceStatus::Issued => 'صادرة',
            InvoiceStatus::Paid => 'مدفوعة',
            InvoiceStatus::Overdue => 'متأخرة',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            InvoiceStatus::Draft => 'gray',
            InvoiceStatus::Issued => 'info',
            InvoiceStatus::Paid => 'success',
            InvoiceStatus::Overdue => 'danger',
        };
    }
}
