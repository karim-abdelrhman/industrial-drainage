<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>مطالبة صرف صناعي - {{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @php
            $fontRegular = 'data:font/truetype;base64,' . base64_encode(file_get_contents(public_path('fonts/Amiri-Regular.ttf')));
            $fontBold    = 'data:font/truetype;base64,' . base64_encode(file_get_contents(public_path('fonts/Amiri-Bold.ttf')));
        @endphp
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: 400;
            src: url('{{ $fontRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'Amiri';
            font-style: normal;
            font-weight: 700;
            src: url('{{ $fontBold }}') format('truetype');
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Amiri', 'Traditional Arabic', Arial, sans-serif;
            font-size: 9pt;
            color: #000000;
            background: #ffffff;
            direction: rtl;
            text-align: right;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 8mm 12mm 8mm 12mm;
            background: #ffffff;
        }

        /* ═══════════════════════════════════ HEADER ════════════════════════════════════ */

        .header-outer {
            border-bottom: 3px double #000000;
            padding-bottom: 7px;
            margin-bottom: 8px;
        }

        .header-layout {
            width: 100%;
            border-collapse: collapse;
        }

        .header-layout td {
            border: none;
            vertical-align: middle;
            padding: 0;
        }

        .header-text-cell {
            text-align: right;
        }

        .header-logo-cell {
            width: 82px;
            text-align: center;
        }

        .header-logo-cell img {
            width: 72px;
            height: 72px;
        }

        .hdr-line-1 { font-size: 12pt; font-weight: 700; margin-bottom: 2px; }
        .hdr-line-2 { font-size: 10pt; font-weight: 700; margin-bottom: 2px; }
        .hdr-line-3 { font-size: 9pt;  font-weight: 700; margin-bottom: 1px; }
        .hdr-line-4 { font-size: 9pt;  font-weight: 700; }

        /* ══════════════════════════════ MAIN STATEMENT ══════════════════════════════════ */

        .statement-heading {
            text-align: center;
            margin-bottom: 7px;
        }

        .statement-decree {
            font-size: 10pt;
            font-weight: 700;
            line-height: 1.55;
            margin-bottom: 3px;
        }

        .statement-month {
            font-size: 12pt;
            font-weight: 700;
        }

        /* ══════════════════════════════ CUSTOMER INFO ═══════════════════════════════════ */

        .customer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 8.5pt;
        }

        .customer-table th {
            background: #e6e6e6;
            border: 1px solid #000000;
            padding: 3px 5px;
            font-weight: 700;
            text-align: right;
            white-space: nowrap;
        }

        .customer-table td {
            border: 1px solid #000000;
            padding: 3px 5px;
            text-align: right;
        }

        /* ══════════════════════════════ BILLING TABLE ═══════════════════════════════════ */

        .billing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7pt;
        }

        .billing-table th {
            background: #d2d2d2;
            border: 1px solid #000000;
            padding: 3px 1px;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
            line-height: 1.35;
        }

        .billing-table td {
            border: 1px solid #000000;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
        }

        .billing-table td.col-name {
            text-align: right;
            padding-right: 3px;
        }

        .tbl-caption th {
            background: #555555;
            color: #ffffff;
            font-size: 8pt;
            letter-spacing: 0.3px;
            padding: 4px 3px;
        }

        .row-subtotal td {
            background: #e2e2e2;
            font-weight: 700;
            border-top: 2px solid #000000;
        }

        .row-subtotal td.lbl {
            text-align: right;
            padding-right: 5px;
        }

        .row-grand-total td {
            background: #b8b8b8;
            font-weight: 700;
            font-size: 8pt;
            border-top: 2.5px solid #000000;
        }

        .row-grand-total td.lbl {
            text-align: right;
            padding-right: 5px;
        }

        .rounding-note td {
            font-size: 6.5pt;
            color: #444444;
            background: #f7f7f7;
            text-align: right;
            padding: 2px 5px;
            border-top: none;
        }

        /* ══════════════════════════════ SIGNATURES ══════════════════════════════════════ */

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 8px;
            border: none;
        }

        .sig-title {
            font-weight: 700;
            font-size: 9pt;
            border-bottom: 1.5px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .sig-name {
            font-size: 8.5pt;
            min-height: 16px;
        }

        .sig-space {
            height: 55px;
            border: 1px dashed #888888;
            margin-top: 8px;
        }

        /* ══════════════════════════════ FOOTER ══════════════════════════════════════════ */

        .footer {
            border-top: 1px solid #888888;
            margin-top: 14px;
            padding-top: 4px;
            font-size: 7pt;
            color: #555555;
            text-align: center;
        }

        /* ══════════════════════════════ PRINT / PAGE ════════════════════════════════════ */

        @media print {
            body { margin: 0; background: #fff; }
            .page { padding: 6mm 10mm 6mm 10mm; }
            .no-print { display: none !important; }
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ════════════════════ PRINT BUTTON (browser only) ════════════════════ --}}
    <div class="no-print" style="text-align: left; margin-bottom: 8px;">
        <button onclick="window.print()"
                style="padding: 6px 18px; font-family: Amiri, sans-serif; font-size: 9pt;
                       cursor: pointer; border: 1px solid #333; background: #f0f0f0;">
            طباعة
        </button>
    </div>

    {{-- ════════════════════ 1. HEADER ════════════════════ --}}
    @php
        $logoPath = public_path('images/c14.png');
        $logoSrc  = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : '';
    @endphp

    <div class="header-outer">
        <table class="header-layout">
            <tr>
                <td class="header-text-cell">
                    <div class="hdr-line-1">الشركة القابضة لمياة الشرب والصرف الصحي</div>
                    <div class="hdr-line-2">شركة مياة الشرب والصرف الصحي بالمنوفية</div>
                    <div class="hdr-line-3">الادارة العامة للمعامل - القطاع التجاري</div>
                    <div class="hdr-line-4">إدارة الصرف الصناعي</div>
                </td>
                @if ($logoSrc)
                    <td class="header-logo-cell">
                        <img src="{{ $logoSrc }}" alt="شعار الشركة">
                    </td>
                @endif
            </tr>
        </table>
    </div>

    {{-- ════════════════════ 2. MAIN STATEMENT (before customer info, no box) ════════════════════ --}}
    @php
        /** @var \App\Models\Invoice $invoice */
        /** @var \App\Models\Establishment $establishment */
        $billingDate   = $invoice->issued_at ?? $invoice->created_at;
        $sampleNumber  = $invoice->sample?->sample_number ?? '—';
        $contactPerson = $establishment->contact_person ?? '—';
        $address       = $establishment->address ?? '—';
        $activityLabel = $establishment->activity_type?->getLabel() ?? '—';
        $invoiceNumber = str_pad($invoice->id, 6, '0', STR_PAD_LEFT);
    @endphp

    <div class="statement-heading">
        <div class="statement-decree">
            مقابل أعباء معالجة صرف المنشآت الصناعية طبقًا لمعايير القرار الوزاري رقم 44 لسنة 2000
        </div>
        <div class="statement-month">
            مطالبة شهر &nbsp;
            {{ \Carbon\Carbon::parse($invoice->billing_month)->locale('ar')->translatedFormat('F') }}
            &nbsp;
            {{ \Carbon\Carbon::parse($invoice->billing_month)->format('Y') }}
        </div>
    </div>

    {{-- ════════════════════ 3. CUSTOMER INFO ════════════════════ --}}
    <table class="customer-table">
        <tbody>
            <tr>
                <th style="width:14%;">اسم المنشأة</th>
                <td colspan="3" style="width:30%;">{{ $establishment->name }}</td>
                <th style="width:14%;">رقم الملف</th>
                <td style="width:13%;">{{ $establishment->id }}</td>
            </tr>
            <tr>
                <th>اسم المالك / المسئول</th>
                <td>{{ $contactPerson }}</td>
                <th>رقم الاشتراك</th>
                <td>{{ $sampleNumber }}</td>
                <th>رقم المطالبة</th>
                <td>{{ $invoiceNumber }}</td>
            </tr>
            <tr>
                <th>النشاط</th>
                <td>{{ $activityLabel }}</td>
                <th>تاريخ المطالبة</th>
                <td colspan="3">{{ $billingDate->format('Y/m/d') }}</td>
            </tr>
            <tr>
                <th>العنوان</th>
                <td colspan="5">{{ $address }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ════════════════════ 4. UNIFIED BILLING TABLE (15 COLS) ════════════════════ --}}
    @php
        $pollutantItems = $invoice->items
            ->filter(fn ($i) => $i->item_type === \App\Enums\InvoiceItemType::PollutantCharge)
            ->values();

        $waterUsage     = (float) ($invoice->sample?->water_usage ?? 0);
        $waterUsage80   = round($waterUsage * 0.8, 2);
        $pollutantTotal = $pollutantItems->sum(fn ($i) => (float) $i->amount);

        $collectionFee = $invoice->items->first(fn ($i) => $i->item_type === \App\Enums\InvoiceItemType::CollectionFee);
        $adminFee      = $invoice->items->first(fn ($i) => $i->item_type === \App\Enums\InvoiceItemType::AdminFee);
        $analysisFee   = $invoice->items->first(fn ($i) => $i->item_type === \App\Enums\InvoiceItemType::AnalysisFee);
        $issuanceFee   = $invoice->items->first(fn ($i) => $i->item_type === \App\Enums\InvoiceItemType::IssuanceFee);
        $vatItem       = $invoice->items->first(fn ($i) => $i->item_type === \App\Enums\InvoiceItemType::Vat);
        $roundingItem  = $invoice->items->first(fn ($i) => $i->item_type === \App\Enums\InvoiceItemType::Rounding);

        $vatPct = \App\Models\SystemSetting::get('vat_percentage', 14);
        $fmt    = fn ($item) => $item ? number_format((float) $item->amount, 2) : '—';
    @endphp

    {{--
        Column map (15 total):
         1  م
         2  اسم الملوث
         3  التركيز المقاس
         4  حالة التوفيق
         5  كمية الاستهلاك م³
         6  نسبة 80% م³
         7  الفئة
         8  سعر الوحدة
         9  قيمة المعالجة
        10  رسم الجمع
        11  رسوم إدارية
        12  تحليل العينة
        13  رسوم الإصدار
        14  ض.ق.م
        15  الإجمالي الكلي
    --}}
    <table class="billing-table">
        <thead>
            <tr class="tbl-caption">
                <th colspan="15">
                    بيان رسوم معالجة الصرف الصناعي وفقًا للقرار الوزاري رقم 44 لسنة 2000
                </th>
            </tr>
            <tr>
                <th style="width:3%;">م</th>
                <th style="width:11%;">اسم<br>الملوث</th>
                <th style="width:6%;">التركيز<br>المقاس</th>
                <th style="width:7%;">حالة<br>التوفيق</th>
                <th style="width:6%;">كمية<br>الاستهلاك<br>م³</th>
                <th style="width:5%;">نسبة<br>80%<br>م³</th>
                <th style="width:4%;">الفئة</th>
                <th style="width:6%;">سعر<br>الوحدة</th>
                <th style="width:8%;">قيمة<br>المعالجة</th>
                <th style="width:6%;">رسم<br>الجمع</th>
                <th style="width:6%;">رسوم<br>إدارية</th>
                <th style="width:7%;">تحليل<br>العينة</th>
                <th style="width:6%;">رسوم<br>الإصدار</th>
                <th style="width:7%;">ض.ق.م<br>{{ $vatPct }}%</th>
                <th style="width:12%;">الإجمالي<br>الكلي</th>
            </tr>
        </thead>
        <tbody>

            {{-- ── Pollutant rows ── --}}
            @forelse ($pollutantItems as $idx => $item)
                @php
                    $pollutantName = $item->pollutant?->name ?? '—';
                    $detectedVal   = number_format((float) $item->detected_value, 2);
                    $tierOrder     = $item->tier_order ?? '—';
                    $pricePerUnit  = number_format((float) $item->price_per_unit, 2);
                    $amount        = number_format((float) $item->amount, 2);
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="col-name">{{ $pollutantName }}</td>
                    <td>{{ $detectedVal }}</td>
                    <td>غير مطابق</td>
                    <td>{{ number_format($waterUsage, 2) }}</td>
                    <td>{{ number_format($waterUsage80, 2) }}</td>
                    <td>{{ $tierOrder }}</td>
                    <td>{{ $pricePerUnit }}</td>
                    <td>{{ $amount }}</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td>—</td>
                    <td>{{ $amount }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" style="text-align:center; padding:8px; font-style:italic; color:#555;">
                        لا توجد بنود ملوثات مسجلة
                    </td>
                </tr>
            @endforelse

            {{-- ── Unified summary row (all 15 columns filled) ── --}}
            <tr class="row-subtotal">
                <td colspan="8" class="lbl">إجمالي المطالبة</td>
                <td>{{ number_format($pollutantTotal, 2) }}</td>
                <td>{{ $fmt($collectionFee) }}</td>
                <td>{{ $fmt($adminFee) }}</td>
                <td>{{ $fmt($analysisFee) }}</td>
                <td>{{ $fmt($issuanceFee) }}</td>
                <td>{{ $fmt($vatItem) }}</td>
                <td>{{ number_format((float) $invoice->total_amount, 2) }}</td>
            </tr>

            {{-- ── Rounding footnote (shown only when present) ── --}}
            @if ($roundingItem && (float) $roundingItem->amount > 0)
                <tr class="rounding-note">
                    <td colspan="15">
                        * يشمل الإجمالي الكلي تسوية تقريب مقدارها
                        {{ number_format((float) $roundingItem->amount, 2) }} جنيه
                    </td>
                </tr>
            @endif

        </tbody>
    </table>

    {{-- ════════════════════ 5. SIGNATURES ════════════════════ --}}
    <table class="signatures-table">
        <tbody>
            <tr>
                <td>
                    <div class="sig-title">رئيس قطاع الصرف الصناعي</div>
                    <div class="sig-name">{{ $industrialManager }}</div>
                    <div class="sig-space"></div>
                </td>
                <td>
                    <div class="sig-title">رئيس القطاع التجاري</div>
                    <div class="sig-name">{{ $commercialManager }}</div>
                    <div class="sig-space"></div>
                </td>
                <td>
                    <div class="sig-title">مدير المعامل</div>
                    <div class="sig-name">{{ $labManager }}</div>
                    <div class="sig-space"></div>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- ════════════════════ 6. FOOTER ════════════════════ --}}
    <div class="footer">
        تاريخ الطباعة: {{ now()->format('Y/m/d H:i') }}
        &nbsp;|&nbsp;
        رقم النظام: INV-{{ $invoiceNumber }}
        &nbsp;|&nbsp;
        نسخة إلكترونية — جميع الأرقام بالجنيه المصري
    </div>

</div>
</body>
</html>
