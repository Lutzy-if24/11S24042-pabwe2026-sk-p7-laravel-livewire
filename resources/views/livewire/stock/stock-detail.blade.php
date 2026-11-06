@push('styles')
    <link rel="stylesheet" href="https://uicdn.toast.com/editor/latest/toastui-editor.min.css" />
@endpush

<div>
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('stocks.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-slate-700">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar Stok
        </a>

        <div class="flex items-center space-x-3">
            <!-- Quick Adjust Quantity Buttons -->
            <div class="flex items-center space-x-1.5 bg-slate-100 rounded-lg p-1 border border-slate-200">
                <button 
                    wire:click="adjustQuantity(-1)" 
                    type="button"
                    class="w-7 h-7 rounded bg-white text-slate-700 hover:bg-slate-200 border border-slate-300 font-bold text-sm flex items-center justify-center transition disabled:opacity-40"
                    title="Kurangi stok"
                    @disabled($stock->quantity <= 0)
                >
                    -
                </button>
                <span class="px-2 font-semibold text-xs text-slate-800">{{ $stock->quantity }} {{ $stock->unit }}</span>
                <button 
                    wire:click="adjustQuantity(1)" 
                    type="button"
                    class="w-7 h-7 rounded bg-white text-slate-700 hover:bg-slate-200 border border-slate-300 font-bold text-sm flex items-center justify-center transition"
                    title="Tambah stok"
                >
                    +
                </button>
            </div>

            <button 
                wire:click="toggleEdit" 
                type="button"
                class="px-3.5 py-1.5 text-xs font-medium rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition"
            >
                {{ $isEditing ? 'Batal Edit' : 'Edit Stok' }}
            </button>
        </div>
    </div>

    <!-- Main Stock Content -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        @if ($currentCoverPath)
            <div class="relative h-64 w-full bg-slate-100">
                <img src="{{ Storage::url($currentCoverPath) }}" alt="{{ $stock->name }}" class="w-full h-full object-cover">
                @if ($isEditing)
                    <button 
                        type="button" 
                        wire:click="removeCover"
                        class="absolute top-4 right-4 bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg shadow font-medium"
                    >
                        Hapus Sampul
                    </button>
                @endif
            </div>
        @endif

        <div class="p-6 md:p-8">
            @if ($isEditing)
                <!-- Edit Form -->
                <form wire:submit="save" class="space-y-6">
                    <div>
                        <label for="detail-name" class="block text-sm font-medium text-slate-700 mb-1">Nama Barang</label>
                        <input 
                            type="text" 
                            id="detail-name"
                            wire:model="name" 
                            class="w-full px-3 py-2 border rounded-lg text-base focus:ring-2 focus:ring-indigo-500 focus:outline-none {{ $errors->has('name') ? 'border-red-300' : 'border-slate-300' }}"
                            required
                        />
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="detail-sku" class="block text-sm font-medium text-slate-700 mb-1">Kode SKU</label>
                            <input 
                                type="text" 
                                id="detail-sku"
                                wire:model="sku" 
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                            @error('sku') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="detail-unit" class="block text-sm font-medium text-slate-700 mb-1">Satuan</label>
                            <input 
                                type="text" 
                                id="detail-unit"
                                wire:model="unit" 
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                required
                            />
                            @error('unit') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="detail-qty" class="block text-sm font-medium text-slate-700 mb-1">Jumlah</label>
                            <input 
                                type="number" 
                                id="detail-qty"
                                wire:model="quantity" 
                                min="0"
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                required
                            />
                            @error('quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="detail-min" class="block text-sm font-medium text-slate-700 mb-1">Min. Stok</label>
                            <input 
                                type="number" 
                                id="detail-min"
                                wire:model="min_stock" 
                                min="0"
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                required
                            />
                            @error('min_stock') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="detail-price" class="block text-sm font-medium text-slate-700 mb-1">Harga (Rp)</label>
                            <input 
                                type="number" 
                                id="detail-price"
                                wire:model="price" 
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 border rounded-lg text-sm border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            />
                            @error('price') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="detail-cover" class="block text-sm font-medium text-slate-700 mb-1">Ganti / Unggah Sampul Baru</label>
                        <input 
                            type="file" 
                            id="detail-cover"
                            wire:model="newCoverImage" 
                            accept="image/*"
                            class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        @error('newCoverImage') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Toast UI Editor -->
                    <div wire:ignore>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi Markdown (Toast UI Editor)</label>
                        <div id="toast-ui-editor" class="border border-slate-300 rounded-lg overflow-hidden"></div>
                    </div>
                    <textarea wire:model="description" id="hidden-description" class="hidden"></textarea>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="toggleEdit" 
                            class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            @else
                <!-- View Mode -->
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900 tracking-tight">
                            {{ $stock->name }}
                        </h1>
                        @if ($stock->sku)
                            <p class="text-sm font-mono text-slate-500 mt-1">SKU: {{ $stock->sku }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $stock->status->badgeClass() }}">
                        {{ $stock->status->label() }}
                    </span>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200 mb-6">
                    <div>
                        <span class="text-xs text-slate-500">Jumlah Stok</span>
                        <p class="text-lg font-bold text-slate-900 mt-0.5">{{ $stock->quantity }} {{ $stock->unit }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500">Batas Min. Stok</span>
                        <p class="text-lg font-bold text-slate-900 mt-0.5">{{ $stock->min_stock }} {{ $stock->unit }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500">Status</span>
                        <p class="text-sm font-semibold mt-1 {{ $stock->status->value === 'available' ? 'text-green-700' : ($stock->status->value === 'low_stock' ? 'text-yellow-700' : 'text-red-700') }}">
                            {{ $stock->status->label() }}
                        </p>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500">Harga Satuan</span>
                        <p class="text-lg font-bold text-slate-900 mt-0.5">
                            {{ $stock->price !== null ? 'Rp ' . number_format((float) $stock->price, 0, ',', '.') : '-' }}
                        </p>
                    </div>
                </div>

                <div class="text-xs text-slate-400 mb-8 flex items-center space-x-4">
                    <span>Dibuat: {{ $stock->created_at->format('d M Y, H:i') }}</span>
                    <span>•</span>
                    <span>Diperbarui: {{ $stock->updated_at->format('d M Y, H:i') }}</span>
                </div>

                <div class="prose max-w-none text-slate-700 border-t border-slate-100 pt-6">
                    @if ($stock->description)
                        {!! \Illuminate\Support\Str::markdown($stock->description) !!}
                    @else
                        <p class="text-slate-400 italic">Tidak ada deskripsi yang ditambahkan untuk stok barang ini.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://uicdn.toast.com/editor/latest/toastui-editor-all.min.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let editor = null;

            function initEditor() {
                const editorEl = document.querySelector('#toast-ui-editor');
                if (editorEl && !editor) {
                    const initialContent = @this.get('description') || '';
                    editor = new toastui.Editor({
                        el: editorEl,
                        height: '350px',
                        initialEditType: 'markdown',
                        previewStyle: 'vertical',
                        initialValue: initialContent,
                        events: {
                            change: () => {
                                @this.set('description', editor.getMarkdown());
                            }
                        }
                    });
                }
            }

            initEditor();

            Livewire.hook('morph.updated', () => {
                const editorEl = document.querySelector('#toast-ui-editor');
                if (editorEl && (!editor || !editorEl.children.length)) {
                    editor = null;
                    initEditor();
                }
            });
        });
    </script>
@endpush
