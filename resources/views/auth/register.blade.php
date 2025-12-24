<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Daftar - Absensi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 50%, #0369a1 100%);
            min-height: 100vh;
        }

        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="mobile-container min-h-screen pb-6">
        <!-- Header -->
        <div class="px-5 pt-8 pb-6">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="text-white/80 hover:text-white mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-white">Daftar Akun</h1>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-8">
            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div
                    class="w-20 h-20 rounded-full bg-green-100 border-4 border-white shadow-lg flex items-center justify-center">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                </div>
            </div>

            <h2 class="text-xl font-bold text-gray-800 text-center mb-2">Buat Akun Baru</h2>
            <p class="text-gray-500 text-center mb-8">Daftar untuk mulai menggunakan aplikasi</p>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                <form action="{{ route('register') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ old('name') }}" autofocus
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            placeholder="Masukkan nama lengkap">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            placeholder="Masukkan email Anda">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" name="password"
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            placeholder="Minimal 8 karakter">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            placeholder="Ulangi password">
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors">
                        Daftar Sekarang
                    </button>
                </form>
            </div>

            <!-- Login Link -->
            <p class="text-center text-gray-500">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-sky-600 font-medium hover:text-sky-800">Masuk</a>
            </p>
        </div>
    </div>
</body>

</html>
