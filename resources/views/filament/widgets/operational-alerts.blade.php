<x-filament-widgets::widget>
    <x-filament::section>
        <div class="mb-4">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">تنبيهات المخالفات</h2>
            <p class="mt-1 text-sm text-gray-500">تصعيد وشيك، مطالبات متأخرة، وعينات بانتظار التقييم</p>
        </div>

        @if ($alerts === [])
            <div class="py-6 text-center">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">لا توجد تنبيهات تشغيلية</p>
                <p class="mt-1 text-sm text-gray-500">الحالة الحالية مستقرة ولا توجد عناصر تتطلب تدخلاً عاجلاً.</p>
            </div>
        @else
            <div>
                @foreach ($alerts as $alert)
                    <a href="{{ $alert['url'] }}" class="ops-alert hover:bg-gray-50 dark:hover:bg-white/5 rounded-lg px-1 -mx-1">
                        <span class="alert-chip alert-chip--{{ $alert['tone'] }}">{{ $alert['tone_label'] }}</span>
                        <span>
                            <span class="block text-sm font-semibold text-gray-900 dark:text-white">{{ $alert['title'] }}</span>
                            <span class="ops-alert__meta">{{ $alert['meta'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
