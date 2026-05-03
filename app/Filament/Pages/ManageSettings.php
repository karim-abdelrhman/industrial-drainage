<?php

namespace App\Filament\Pages;

use App\Models\SystemSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSettings extends Page
{
    protected string $view = 'filament.pages.manage-settings';

    protected static ?string $navigationLabel = 'الإعدادات';

    protected static ?string $title = 'إعدادات الرسوم';

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    // protected static ?string $navigationIcon = 'o-cog-6-tooth';

    protected static ?int $navigationSort = 99;

    public float $collection_fee_inside_city = 0;

    public float $collection_fee_outside_city = 0;

    public float $collection_fee_composite = 0;

    public float $admin_fee_percentage = 0;

    public float $analysis_fee = 0;

    public float $issuance_fee = 0;

    public float $vat_percentage = 0;

    public function mount(): void
    {
        $this->collection_fee_inside_city = SystemSetting::get('collection_fee_inside_city', 250);
        $this->collection_fee_outside_city = SystemSetting::get('collection_fee_outside_city', 450);
        $this->collection_fee_composite = SystemSetting::get('collection_fee_composite', 1400);
        $this->admin_fee_percentage = SystemSetting::get('admin_fee_percentage', 20);
        $this->analysis_fee = SystemSetting::get('analysis_fee', 355);
        $this->issuance_fee = SystemSetting::get('issuance_fee', 0.50);
        $this->vat_percentage = SystemSetting::get('vat_percentage', 14);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('رسوم جمع العينة')
                    ->description('تُحدد رسوم الجمع بناءً على موقع المنشأة أو نوع العينة.')
                    ->icon(Heroicon::OutlinedBeaker)
                    ->columns(3)
                    ->schema([
                        TextInput::make('collection_fee_inside_city')
                            ->label('داخل النطاق العمراني (ج.م)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required()
                            ->suffix('ج.م'),
                        TextInput::make('collection_fee_outside_city')
                            ->label('خارج النطاق العمراني (ج.م)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required()
                            ->suffix('ج.م'),
                        TextInput::make('collection_fee_composite')
                            ->label('العينة المركبة (ج.م)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required()
                            ->suffix('ج.م')
                            ->helperText('تحل محل رسوم الجمع العادية'),
                    ]),

                Section::make('الرسوم الثابتة')
                    ->description('رسوم تضاف على كل فاتورة بصرف النظر عن نتيجة التقييم.')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->columns(2)
                    ->schema([
                        TextInput::make('analysis_fee')
                            ->label('رسوم التحليل (ج.م)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required()
                            ->suffix('ج.م'),
                        TextInput::make('issuance_fee')
                            ->label('رسوم الإصدار (ج.م)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->required()
                            ->suffix('ج.م'),
                    ]),

                Section::make('النسب المئوية')
                    ->description('نسب تُحسب على الإجمالي.')
                    // ->icon(Heroicon::OutlinedPercent)
                    ->columns(2)
                    ->schema([
                        TextInput::make('admin_fee_percentage')
                            ->label('الرسوم الإدارية (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->required()
                            ->suffix('%')
                            ->helperText('نسبة من رسوم جمع العينة'),
                        TextInput::make('vat_percentage')
                            ->label('ضريبة القيمة المضافة (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->required()
                            ->suffix('%')
                            ->helperText('تُطبق على إجمالي الفاتورة'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ الإعدادات')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('primary')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $keys = [
            'collection_fee_inside_city',
            'collection_fee_outside_city',
            'collection_fee_composite',
            'admin_fee_percentage',
            'analysis_fee',
            'issuance_fee',
            'vat_percentage',
        ];

        foreach ($keys as $key) {
            SystemSetting::where('key', $key)->update(['value' => (string) $this->$key]);
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
