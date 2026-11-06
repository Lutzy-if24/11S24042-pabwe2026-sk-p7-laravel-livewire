<div class="max-w-md mx-auto mt-8 bg-white p-8 border border-slate-200 rounded-xl shadow-sm">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Masuk ke Akun Anda</h1>
        <p class="text-sm text-slate-500 mt-2">Kelola inventaris stok barang Anda dengan mudah</p>
    </div>

    @if ($errors->has('login'))
        <div class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">
            {{ $errors->first('login') }}
        </div>
    @endif

    <form wire:submit="authenticate" class="space-y-5">
        <div>
            <label for="login-username" class="block text-sm font-medium text-slate-700 mb-1">
                Username atau Email
            </label>
            <input 
                type="text" 
                id="login-username" 
                wire:model="login"
                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('login') ? 'border-red-300' : 'border-slate-300' }}"
                placeholder="Masukkan username atau email"
                autofocus
                required
            />
        </div>

        <div>
            <label for="login-password" class="block text-sm font-medium text-slate-700 mb-1">
                Kata Sandi
            </label>
            <input 
                type="password" 
                id="login-password" 
                wire:model="password"
                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 {{ $errors->has('password') ? 'border-red-300' : 'border-slate-300' }}"
                placeholder="••••••••"
                required
            />
            @error('password')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="text-slate-600">Ingat saya</span>
            </label>
        </div>

        <button 
            type="submit" 
            id="login-submit"
            class="w-full py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-50"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="authenticate">Masuk</span>
            <span wire:loading wire:target="authenticate">Memproses...</span>
        </button>
    </form>

    <div class="mt-6 text-center text-sm text-slate-600">
        Belum memiliki akun?
        <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline">
            Daftar sekarang
        </a>
    </div>
</div>
