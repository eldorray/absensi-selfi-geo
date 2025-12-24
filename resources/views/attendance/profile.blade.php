<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profil - Absensi</title>
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
                <h1 class="text-xl font-bold text-white">Edit Profil</h1>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">
            <!-- Profile Photo -->
            <div class="flex justify-center mb-6">
                <div
                    class="w-24 h-24 rounded-full bg-sky-100 border-4 border-white shadow-lg flex items-center justify-center">
                    <span class="text-3xl font-bold text-sky-600">{{ auth()->user()->initials() }}</span>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded-xl text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('settings.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl transition-colors">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            <!-- Info Card -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                <h3 class="font-semibold text-gray-800 mb-3">Informasi Akun</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Role</span>
                        <span class="font-medium text-gray-800">{{ auth()->user()->role?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Kantor</span>
                        <span class="font-medium text-gray-800">{{ auth()->user()->office?->name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bergabung</span>
                        <span
                            class="font-medium text-gray-800">{{ auth()->user()->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Change Password Link -->
            <a href="{{ route('attendance.password') }}"
                class="flex items-center justify-between bg-white rounded-2xl shadow-lg p-5 mb-5 hover:bg-gray-50 transition-colors">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <span class="font-medium text-gray-800">Ganti Password</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </div>
</body>

</html>
