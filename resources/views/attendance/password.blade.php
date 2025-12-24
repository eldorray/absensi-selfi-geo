<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ganti Password - Absensi</title>
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
                <a href="{{ route('attendance.dashboard') }}" class="text-white/80 hover:text-white mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-white">Ganti Password</h1>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">
            <!-- Icon -->
            <div class="flex justify-center mb-6">
                <div
                    class="w-20 h-20 rounded-full bg-amber-100 border-4 border-white shadow-lg flex items-center justify-center">
                    <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('attendance.password.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
                        <input type="password" name="current_password"
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            placeholder="Masukkan password saat ini">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                        <input type="password" name="password"
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            placeholder="Minimal 8 karakter">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation"
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                            placeholder="Ulangi password baru">
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition-colors">
                        Ganti Password
                    </button>
                </form>
            </div>

            <!-- Tips -->
            <div class="bg-white rounded-2xl shadow-lg p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Tips Password Aman</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Gunakan minimal 8 karakter
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Kombinasikan huruf besar dan kecil
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Tambahkan angka dan simbol
                    </li>
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Jangan gunakan informasi pribadi
                    </li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>
