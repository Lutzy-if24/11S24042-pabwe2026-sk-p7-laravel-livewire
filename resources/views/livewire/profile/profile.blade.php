<div class="max-w-4xl mx-auto space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pengaturan Profil</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui informasi akun, foto profil, dan kata sandi Anda</p>
    </div>

    <!-- Section: Informasi Profil -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 md:p-8">
        <h2 class="text-lg font-semibold text-slate-900 mb-1">Informasi Akun</h2>
        <p class="text-sm text-slate-500 mb-6">Ubah nama lengkap, username, alamat email, atau foto profil Anda.</p>

        @if (session()->has('success_profile'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-sm font-medium text-green-800">
                {{ session('success_profile') }}
            </div>
        @endif

        <form wire:submit="updateProfileInformation" class="space-y-6">
            <!-- Foto Profil -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Foto Profil</label>
                <div class="flex items-center space-x-6">
                    <div class="shrink-0">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-full ring-2 ring-indigo-500">
                        @elseif ($user->profile_photo_path)
                            <img src="{{ Storage::url($user->profile_photo_path) }}" alt="{{ $user->name }}" class="h-20 w-20 object-cover rounded-full ring-2 ring-slate-200">
                        @else
                            <div class="h-20 w-20 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xl ring-2 ring-slate-200">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                    </div>
                    <div class="space-y-2">
                        <input 
                            type="file" 
                            wire:model="photo" 
                            accept="image/*"
                            class="text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                        />
                        @if ($user->profile_photo_path)
                            <button 
                                type="button" 
                                wire:click="removeProfilePhoto" 
                                class="text-xs text-red-600 hover:text-red-800 font-medium block"
                            >
                                Hapus Foto Profil
                            </button>
                        @endif
                        @error('photo') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="profile-name" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="profile-name" 
                        wire:model="name" 
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none {{ $errors->has('name') ? 'border-red-300' : 'border-slate-300' }}"
                        required
                    />
                    @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="profile-username" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input 
                        type="text" 
                        id="profile-username" 
                        wire:model="username" 
                        class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none {{ $errors->has('username') ? 'border-red-300' : 'border-slate-300' }}"
                        required
                    />
                    @error('username') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="profile-email" class="block text-sm font-medium text-slate-700 mb-1">Alamat Email</label>
                <input 
                    type="email" 
                    id="profile-email" 
                    wire:model="email" 
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none {{ $errors->has('email') ? 'border-red-300' : 'border-slate-300' }}"
                    required
                />
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button 
                    type="submit" 
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition"
                >
                    Simpan Informasi
                </button>
            </div>
        </form>
    </div>

    <!-- Section: Ganti Kata Sandi -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 md:p-8">
        <h2 class="text-lg font-semibold text-slate-900 mb-1">Ganti Kata Sandi</h2>
        <p class="text-sm text-slate-500 mb-6">Pastikan akun Anda menggunakan kata sandi yang panjang dan aman.</p>

        @if (session()->has('success_password'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-sm font-medium text-green-800">
                {{ session('success_password') }}
            </div>
        @endif

        <form wire:submit="updatePassword" class="space-y-5 max-w-lg">
            <div>
                <label for="current_password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi Saat Ini</label>
                <input 
                    type="password" 
                    id="current_password" 
                    wire:model="current_password" 
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none {{ $errors->has('current_password') ? 'border-red-300' : 'border-slate-300' }}"
                    required
                />
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi Baru</label>
                <input 
                    type="password" 
                    id="new_password" 
                    wire:model="new_password" 
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none {{ $errors->has('new_password') ? 'border-red-300' : 'border-slate-300' }}"
                    required
                />
                @error('new_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                <input 
                    type="password" 
                    id="new_password_confirmation" 
                    wire:model="new_password_confirmation" 
                    class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none {{ $errors->has('new_password_confirmation') ? 'border-red-300' : 'border-slate-300' }}"
                    required
                />
                @error('new_password_confirmation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button 
                    type="submit" 
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition"
                >
                    Perbarui Kata Sandi
                </button>
            </div>
        </form>
    </div>
</div>
