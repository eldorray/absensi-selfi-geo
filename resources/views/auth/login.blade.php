<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk - Absensi</title>
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
                <h1 class="text-xl font-bold text-white">Masuk</h1>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-8">
            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div
                    class="w-20 h-20 rounded-full bg-sky-100 border-4 border-white shadow-lg flex items-center justify-center">
                    <svg class="w-10 h-10 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>

            <h2 class="text-xl font-bold text-gray-800 text-center mb-2">Selamat Datang!</h2>
            <p class="text-gray-500 text-center mb-8">Masuk ke akun Anda untuk melanjutkan</p>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" autofocus
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
                            placeholder="Masukkan password">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember"
                                class="rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                            <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-sky-600 hover:text-sky-800">
                                Lupa password?
                            </a>
                        @endif
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl transition-colors">
                        Masuk
                    </button>
                </form>
            </div>

            <!-- Register Link -->
            <p class="text-center text-gray-500">
                Belum punya akun? Silahkan hubungi admin.
            </p>
        </div>
    </div>
</body>

</html>
