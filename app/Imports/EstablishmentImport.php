<?php

namespace App\Imports;

use App\Models\Establishment;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

class EstablishmentImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithUpserts, WithValidation
{
    use SkipsFailures;

    public function model(array $row): Establishment
    {
        return new Establishment([
            'name' => $row['name'],
            'activity_type' => $row['activity_type'],
            'location_type' => $row['location_type'] ?? 'inside_city',
            'address' => $row['address'] ?? null,
            'contact_person' => $row['contact_person'] ?? null,
            'phone' => $row['phone'] ?? null,
            'email' => $row['email'] ?? null,
            'is_active' => filter_var($row['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'activity_type' => ['required', 'in:industrial,commercial'],
            'location_type' => ['nullable', 'in:inside_city,outside_city'],
            'address' => ['nullable', 'string', 'max:300'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'is_active' => ['nullable'],
        ];
    }

    public function uniqueBy(): string
    {
        return 'name';
    }
}
