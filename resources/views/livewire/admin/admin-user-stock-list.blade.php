<div>
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.users') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Manajemen Pengguna
        </a>

        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
            Mode Inspeksi (Hanya Baca / Read-Only)
        </span>
    </div>

    <!-- User Header Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-8 flex items-center space-x-4">
        @if ($targetUser->profile_photo_path)
            <img src="{{ Storage::url($targetUser->profile_photo_path) }}" alt="{{ $targetUser->name }}" class="h-16 w-16 rounded-full object-cover ring-2 ring-indigo-500">
        @else
            <div class="h-16 w-16 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xl ring-2 ring-indigo-200">
                {{ strtoupper(substr($targetUser->name, 0, 2)) }}
            </div>
        @endif
        <div>
            <h1 class="text-xl font-bold text-slate-900">{{ $targetUser->name }}</h1>
            <p class="text-sm text-slate-500">
                {{ '@' . $targetUser->username }} • {{ $targetUser->email }} • 
                <span class="font-medium text-slate-700">Total: {{ $targetUser->stocks->count() }} item stok</span>
            </p>
        </div>
    </div>

    <!-- Stocks List (Read Only - No Add/Edit/Delete/Adjust) -->
    @if ($targetUser->stocks->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($targetUser->stocks as $stock)
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                    @if ($stock->cover_image_path)
                        <img src="{{ Storage::url($stock->cover_image_path) }}" alt="{{ $stock->name }}" class="h-44 w-full object-cover">
                    @endif

                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div>
                                    <h2 class="font-semibold text-slate-900 text-base leading-snug">
                                        {{ $stock->name }}
                                    </h2>
                                    @if ($stock->sku)
                                        <span class="text-xs font-mono text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">
                                            {{ $stock->sku }}
                                        </span>
                                    @endif
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $stock->status->badgeClass() }}">
                                    {{ $stock->status->label() }}
                                </span>
                            </div>

                            <!-- Inventory Info -->
                            <div class="my-3 py-2 px-3 bg-slate-50 rounded-lg border border-slate-100 flex items-center justify-between text-xs text-slate-700">
                                <div>
                                    <span class="text-slate-500">Jumlah:</span>
                                    <span class="font-bold text-slate-900 ml-1">{{ $stock->quantity }} {{ $stock->unit }}</span>
                                    <span class="text-slate-600 ml-1">(Min: {{ $stock->min_stock }})</span>
                                </div>
                                @if ($stock->price !== null)
                                    <div>
                                        <span class="text-slate-500">Harga:</span>
                                        <span class="font-bold text-slate-900 ml-1">Rp {{ number_format((float) $stock->price, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>

                            @if ($stock->description)
                                <div class="text-sm text-slate-600 prose prose-sm max-w-none mt-2">
                                    {!! \Illuminate\Support\Str::markdown($stock->description) !!}
                                </div>
                            @else
                                <p class="text-sm text-slate-600 italic mt-2">Tidak ada deskripsi</p>
                            @endif
                        </div>

                        <div class="pt-4 mt-4 border-t border-slate-100 text-xs text-slate-600">
                            Dibuat: {{ $stock->created_at->format('d M Y, H:i') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center text-slate-500">
            Pengguna ini belum memiliki stok barang yang dibuat.
        </div>
    @endif
</div>
