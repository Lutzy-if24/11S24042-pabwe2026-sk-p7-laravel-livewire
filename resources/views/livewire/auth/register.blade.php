<div class="max-w-md mx-auto mt-8 bg-white p-8 border border-slate-200 rounded-xl shadow-sm">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Akun Baru</h1>
        <p class="text-sm text-slate-500 mt-2">Mulai kelola inventaris stok barang Anda sekarang</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <div>
            <label for="register-name" class="block text-sm font-medium text-slate-700 mb-1">
                Nama Lengkap
            </label>
            <input 
                type="text" 
                id="register-name" 
                wire:model="name"
                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('name') ? 'border-red-300' : 'border-slate-300' }}"
                placeholder="Nama lengkap Anda"
                required
            />
            @error('name')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="register-username" class="block text-sm font-medium text-slate-700 mb-1">
                Username
            </label>
            <input 
                type="text" 
                id="register-username" 
                wire:model="username"
                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('username') ? 'border-red-300' : 'border-slate-300' }}"
                placeholder="username_anda"
                required
            />
            @error('username')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="register-email" class="block text-sm font-medium text-slate-700 mb-1">
                Alamat Email
            </label>
            <input 
                type="email" 
                id="register-email" 
                wire:model="email"
                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('email') ? 'border-red-300' : 'border-slate-300' }}"
                placeholder="nama@email.com"
                required
            />
            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="register-password" class="block text-sm font-medium text-slate-700 mb-1">
                Kata Sandi
            </label>
            <input 
                type="password" 
                id="register-password" 
                wire:model="password"
                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('password') ? 'border-red-300' : 'border-slate-300' }}"
                placeholder="Minimal 6 karakter"
                required
            />
            @error('password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="register-password-confirmation" class="block text-sm font-medium text-slate-700 mb-1">
                Konfirmasi Kata Sandi
            </label>
            <input 
                type="password" 
                id="register-password-confirmation" 
                wire:model="password_confirmation"
                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('password_confirmation') ? 'border-red-300' : 'border-slate-300' }}"
                placeholder="Ulangi kata sandi"
                required
            />
            @error('password_confirmation')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button 
            type="submit" 
            class="w-full py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50 mt-2"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="register">Daftar Akun</span>
            <span wire:loading wire:target="register">Memproses Pendaftaran...</span>
        </button>
    </form>

    <div class="mt-6 text-center text-sm text-slate-600">
        Sudah memiliki akun?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline">
            Masuk di sini
        </a>
    </div>
</div>
