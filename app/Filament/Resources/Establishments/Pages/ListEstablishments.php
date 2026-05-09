<?php

namespace App\Filament\Resources\Establishments\Pages;

use App\Filament\Resources\Establishments\EstablishmentResource;
use App\Imports\EstablishmentImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListEstablishments extends ListRecords
{
    protected static string $resource = EstablishmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importEstablishments')
                ->label('استيراد من Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('ملف Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $import = new EstablishmentImport;

                    Excel::import($import, storage_path('app/private/'.$data['file']));

                    $failures = $import->failures();

                    if ($failures->isNotEmpty()) {
                        $messages = $failures->map(fn ($f) => 'صف '.$f->row().': '.implode(', ', $f->errors()))->implode("\n");

                        Notification::make()
                            ->title('اكتمل الاستيراد مع أخطاء')
                            ->body($messages)
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('تم استيراد المنشآت بنجاح')
                        ->success()
                        ->send();
                }),

            CreateAction::make(),
        ];
    }
}
