<div>
    <!-- Top Action Bar -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Stok</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola inventaris barang, pantau jumlah stok, dan optimalkan pengadaan</p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-wrap gap-2">
            <!-- AI Generator Button -->
            <button 
                wire:click="openAiModal"
                type="button" 
                class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 transition shadow-sm"
            >
                <svg class="w-4 h-4 mr-1.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Generate dengan AI
            </button>

            <!-- Export Excel Button -->
            <button 
                wire:click="exportExcel"
                type="button" 
                class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition shadow-sm"
            >
                <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Excel
            </button>

            <!-- Import Excel Trigger -->
            <label class="inline-flex items-center px-3.5 py-2 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 transition shadow-sm cursor-pointer">
                <svg class="w-4 h-4 mr-1.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l4-4m0 0l4 4m-4-4v12" />
                </svg>
                Import Excel
                <input type="file" wire:model="excelFile" wire:change="importExcel" class="hidden" accept=".xlsx, .xls">
            </label>

            <!-- Add Stock Button -->
            <button 
                wire:click="openCreateModal"
                type="button" 
                class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Stok
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="w-full md:w-96 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Cari nama, SKU, atau deskripsi..."
                class="w-full pl-10 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
        </div>

        <div class="w-full md:w-auto flex items-center gap-2">
            <label for="status-filter" class="text-sm font-medium text-slate-600 whitespace-nowrap">Filter Status:</label>
            <select 
                id="status-filter"
                wire:model.live="statusFilter" 
                class="text-sm border border-slate-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
                <option value="">Semua Status</option>
                <option value="available">Tersedia</option>
                <option value="low_stock">Stok Menipis</option>
                <option value="out_of_stock">Habis</option>
            </select>
        </div>
    </div>

    <!-- Stock Cards Grid -->
    @if ($stocks->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach ($stocks as $stock)
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col hover:border-slate-300 transition">
                    @if ($stock->cover_image_path)
                        <img src="{{ Storage::url($stock->cover_image_path) }}" alt="{{ $stock->name }}" class="h-44 w-full object-cover">
                    @endif

                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div>
                                    <h3 class="font-semibold text-slate-900 text-base leading-snug">
                                        <a href="{{ route('stocks.show', $stock->id) }}" class="hover:text-indigo-600 transition">
                                            {{ $stock->name }}
                                        </a>
                                    </h3>
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
                                    <span class="text-slate-400 ml-1">(Min: {{ $stock->min_stock }})</span>
                                </div>
                                @if ($stock->price !== null)
                                    <div>
                                        <span class="text-slate-500">Harga:</span>
                                        <span class="font-bold text-slate-900 ml-1">Rp {{ number_format((float) $stock->price, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>

                            @if ($stock->description)
                                <p class="text-sm text-slate-600 line-clamp-3 mb-4">
                                    {{ Str::limit(strip_tags(\Illuminate\Support\Str::markdown($stock->description)), 120) }}
                                </p>
                            @else
                                <p class="text-sm text-slate-400 italic mb-4">Tidak ada deskripsi</p>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <!-- Quick Adjust Quantity Buttons -->
                            <div class="flex items-center space-x-1.5 bg-slate-100 rounded-lg p-1">
                                <button 
                                    wire:click="adjustQuantity({{ $stock->id }}, -1)" 
                                    type="button"
                                    class="w-7 h-7 rounded bg-white text-slate-700 hover:bg-slate-200 border border-slate-300 font-bold text-sm flex items-center justify-center transition disabled:opacity-40"
                                    title="Kurangi stok"
                                    @disabled($stock->quantity <= 0)
                                >
                                    -
                                </button>
                                <span class="px-2 font-semibold text-xs text-slate-800">{{ $stock->quantity }}</span>
                                <button 
                                    wire:click="adjustQuantity({{ $stock->id }}, 1)" 
                                    type="button"
                                    class="w-7 h-7 rounded bg-white text-slate-700 hover:bg-slate-200 border border-slate-300 font-bold text-sm flex items-center justify-center transition"
                                    title="Tambah stok"
                                >
                                    +
                                </button>
                            </div>

                            <div class="flex items-center space-x-2">
                                <a 
                                    href="{{ route('stocks.show', $stock->id) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-900 font-medium px-2 py-1 rounded hover:bg-indigo-50"
                                >
                                    Detail
                                </a>
                                <button 
                                    wire:click="openEditModal({{ $stock->id }})" 
                                    type="button" 
                                    class="text-xs text-slate-600 hover:text-slate-900 font-medium px-2 py-1 rounded hover:bg-slate-100"
                                >
                                    Ubah
                                </button>
                                <button 
                                    wire:click="deleteStock({{ $stock->id }})" 
                                    wire:confirm="Apakah Anda yakin ingin menghapus stok ini?"
                                    type="button" 
                                    class="text-xs text-red-600 hover:text-red-900 font-medium px-2 py-1 rounded hover:bg-red-50"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div>
            {{ $stocks->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-slate-900">Belum ada stok barang</h3>
            <p class="mt-1 text-sm text-slate-500">Mulai tambah stok barang baru, impor file Excel, atau gunakan AI generator.</p>
            <div class="mt-6 flex justify-center gap-3">
                <button 
                    wire:click="openCreateModal" 
                    type="button" 
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700"
                >
                    Tambah Stok Baru
                </button>
            </div>
        </div>
    @endif

    <!-- Create / Edit Modal -->
    @if ($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ $editingStockId ? 'Ubah Stok Barang' : 'Tambah Stok Barang Baru' }}
                    </h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="saveStock" class="mt-4 space-y-4">
                    <div>
                        <label for="stock-name" class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                        <input 
                            type="text" 
                            id="stock-name" 
                            wire:model="name" 
                            class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none {{ $errors->has('name') ? 'border-red-300' : 'border-slate-300' }}"
                            placeholder="Contoh: Kertas HVS A4 80gr"
                        />
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="stock-sku" class="block text-sm font-medium text-slate-700 mb-1">Kode SKU</label>
                            <input 
                                type="text" 
                                id="stock-sku" 
                                wire:model="sku" 
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                placeholder="Contoh: SKU-HVS-0001"
                            />
                            @error('sku') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="stock-unit" class="block text-sm font-medium text-slate-700 mb-1">Satuan</label>
                            <input 
                                type="text" 
                                id="stock-unit" 
                                wire:model="unit" 
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                placeholder="pcs, box, unit, rim..."
                            />
                            @error('unit') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label for="stock-qty" class="block text-sm font-medium text-slate-700 mb-1">Jumlah</label>
                            <input 
                                type="number" 
                                id="stock-qty" 
                                wire:model="quantity" 
                                min="0"
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                            @error('quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="stock-min" class="block text-sm font-medium text-slate-700 mb-1">Min. Stok</label>
                            <input 
                                type="number" 
                                id="stock-min" 
                                wire:model="min_stock" 
                                min="0"
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                            @error('min_stock') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="stock-price" class="block text-sm font-medium text-slate-700 mb-1">Harga (Rp)</label>
                            <input 
                                type="number" 
                                id="stock-price" 
                                wire:model="price" 
                                min="0"
                                step="0.01"
                                placeholder="0"
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                            @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="stock-desc" class="block text-sm font-medium text-slate-700 mb-1">Deskripsi (Markdown)</label>
                        <textarea 
                            id="stock-desc" 
                            wire:model="description" 
                            rows="4" 
                            class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            placeholder="Spesifikasi barang dalam format Markdown..."
                        ></textarea>
                    </div>

                    <div>
                        <label for="stock-cover" class="block text-sm font-medium text-slate-700 mb-1">Gambar Sampul (Opsional)</label>
                        <input 
                            type="file" 
                            id="stock-cover" 
                            wire:model="coverImage" 
                            accept="image/*"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        @error('coverImage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        @if ($coverImage)
                            <div class="mt-2">
                                <p class="text-xs text-slate-500 mb-1">Pratinjau cover:</p>
                                <img src="{{ $coverImage->temporaryUrl() }}" class="h-28 rounded-lg object-cover">
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                        <button 
                            type="button" 
                            wire:click="closeModal" 
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500"
                        >
                            {{ $editingStockId ? 'Simpan Perubahan' : 'Buat Stok' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- AI Generator Modal -->
    @if ($isAiModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-xl max-w-xl w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <div class="p-2 rounded-lg bg-purple-100 text-purple-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900">Generate Stok dengan AI</h3>
                    </div>
                    <button wire:click="closeAiModal" class="text-slate-400 hover:text-slate-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label for="ai-prompt" class="block text-sm font-medium text-slate-700 mb-1">
                            Topik atau Kategori Inventaris
                        </label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                id="ai-prompt" 
                                wire:model="aiPrompt" 
                                placeholder="Contoh: Alat Tulis Kantor ATK atau Peralatan Jaringan Komputer"
                                class="flex-1 px-3 py-2 text-sm border rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none {{ $errors->has('aiPrompt') ? 'border-red-300' : 'border-slate-300' }}"
                                wire:keydown.enter="generateWithAi"
                            />
                            <button 
                                wire:click="generateWithAi" 
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="generateWithAi">Generate</span>
                                <span wire:loading wire:target="generateWithAi">Memproses...</span>
                            </button>
                        </div>
                        @error('aiPrompt') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if (!empty($aiGeneratedStocks))
                        <div class="mt-4">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilih Item Stok yang Ingin Disimpan:</p>
                            <div class="max-h-64 overflow-y-auto space-y-2 border border-slate-200 rounded-lg p-2 bg-slate-50">
                                @foreach ($aiGeneratedStocks as $index => $item)
                                    <label class="flex items-start p-2 bg-white rounded border border-slate-200 space-x-3 cursor-pointer hover:bg-indigo-50/30">
                                        <input 
                                            type="checkbox" 
                                            wire:model="aiGeneratedStocks.{{ $index }}.selected"
                                            class="mt-1 rounded border-slate-300 text-purple-600 focus:ring-purple-500"
                                        />
                                        <div class="text-sm flex-1">
                                            <div class="flex items-center justify-between">
                                                <p class="font-medium text-slate-800">{{ $item['name'] }}</p>
                                                @if (!empty($item['sku']))
                                                    <span class="text-[10px] font-mono text-slate-500 bg-slate-100 px-1 py-0.5 rounded">{{ $item['sku'] }}</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-2">
                                                <span>Qty: {{ $item['quantity'] }} {{ $item['unit'] }}</span>
                                                <span>Min: {{ $item['min_stock'] }}</span>
                                                @if ($item['price'] !== null)
                                                    <span>Rp {{ number_format((float) $item['price'], 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                            @if ($item['description'])
                                                <p class="text-xs text-slate-500 mt-1">{{ $item['description'] }}</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                        <button 
                            type="button" 
                            wire:click="closeAiModal" 
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
                        >
                            Tutup
                        </button>
                        @if (!empty($aiGeneratedStocks))
                            <button 
                                type="button" 
                                wire:click="saveAiStocks" 
                                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500"
                            >
                                Simpan Stok Terpilih
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
