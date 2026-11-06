<div>
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar semua pengguna terdaftar dan ringkasan aktivitas stok mereka</p>
        </div>
        <div class="mt-4 sm:mt-0 w-full sm:w-72">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Cari pengguna..."
                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-medium">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Pengguna</th>
                        <th scope="col" class="px-6 py-3.5">Username</th>
                        <th scope="col" class="px-6 py-3.5">Email</th>
                        <th scope="col" class="px-6 py-3.5">Peran (Role)</th>
                        <th scope="col" class="px-6 py-3.5 text-center">Jumlah Stok</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse ($users as $u)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    @if ($u->profile_photo_path)
                                        <img src="{{ Storage::url($u->profile_photo_path) }}" alt="{{ $u->name }}" class="h-9 w-9 rounded-full object-cover">
                                    @else
                                        <div class="h-9 w-9 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="font-medium text-slate-900">{{ $u->name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                {{ '@' . $u->username }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                {{ $u->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $u->isAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-800' }}">
                                    {{ $u->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $u->stocks_count }} item
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <a 
                                    href="{{ route('admin.users.stocks', $u->id) }}" 
                                    class="inline-flex items-center px-3 py-1.5 border border-slate-300 shadow-xs text-xs font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition"
                                >
                                    Lihat Stok
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                Tidak ada data pengguna yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
