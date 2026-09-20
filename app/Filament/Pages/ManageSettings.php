<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Models\SystemSetting;
use BackedEnum;
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

    protected static ?string $navigationLabel = 'إعدادات الرسوم';

    protected static ?string $title = 'إعدادات الرسوم والضرائب';

    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Settings;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 1;

    public float $collection_fee_inside_city = 0;

    public float $collection_fee_outside_city = 0;

    public float $collection_fee_composite = 0;

    public float $admin_fee_percentage = 0;

    public float $analysis_fee = 0;

    public float $issuance_fee = 0;

    public float $vat_percentage = 0;

    public float $cod_discount_when_bod_violation_percent = 40;

    public function mount(): void
    {
        $this->collection_fee_inside_city = SystemSetting::get('collection_fee_inside_city', 250);
        $this->collection_fee_outside_city = SystemSetting::get('collection_fee_outside_city', 450);
        $this->collection_fee_composite = SystemSetting::get('collection_fee_composite', 1400);
        $this->admin_fee_percentage = SystemSetting::get('admin_fee_percentage', 20);
        $this->analysis_fee = SystemSetting::get('analysis_fee', 355);
        $this->issuance_fee = SystemSetting::get('issuance_fee', 0.50);
        $this->vat_percentage = SystemSetting::get('vat_percentage', 14);
        $this->cod_discount_when_bod_violation_percent = SystemSetting::get('cod_discount_when_bod_violation_percent', 40);
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
                            ->helperText('تُطبق على مجموع الملوثات ورسوم الجمع والإدارية والتحليل والإصدار قبل التسوية'),
                        TextInput::make('cod_discount_when_bod_violation_percent')
                            ->label('خصم COD عند مخالفة BOD (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->required()
                            ->suffix('%')
                            ->helperText('يُطبق على سعر وحدة COD فقط إذا كانت نفس العينة فيها مخالفة BOD'),
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
            'cod_discount_when_bod_violation_percent',
        ];

        $labels = [
            'collection_fee_inside_city' => 'رسوم جمع العينة داخل النطاق العمراني',
            'collection_fee_outside_city' => 'رسوم جمع العينة خارج النطاق العمراني',
            'collection_fee_composite' => 'رسوم جمع العينة المركبة',
            'admin_fee_percentage' => 'نسبة الرسوم الإدارية',
            'analysis_fee' => 'رسوم التحليل',
            'issuance_fee' => 'رسوم الإصدار',
            'vat_percentage' => 'نسبة ضريبة القيمة المضافة',
            'cod_discount_when_bod_violation_percent' => 'خصم COD عند مخالفة BOD',
        ];

        foreach ($keys as $key) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => (string) $this->$key,
                    'type' => 'decimal',
                    'label' => $labels[$key] ?? $key,
                ]
            );
        }

        Notification::make()
            ->title('تم حفظ الإعدادات بنجاح')
            ->success()
            ->send();
    }
}
