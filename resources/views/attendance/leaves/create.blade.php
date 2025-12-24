<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ajukan Perizinan - Absensi</title>
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
                <a href="{{ route('attendance.leaves.index') }}" class="text-white/80 hover:text-white mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-white">Ajukan Perizinan</h1>
            </div>
        </div>

        <!-- Content -->
        <div class="bg-gray-100 rounded-t-[32px] min-h-screen px-5 pt-6">
            <!-- Form -->
            <div class="bg-white rounded-2xl shadow-lg p-5 mb-5">
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded-xl text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('attendance.leaves.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Perizinan</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="relative">
                                <input type="radio" name="type" value="izin" class="peer sr-only"
                                    {{ old('type') == 'izin' ? 'checked' : '' }} required>
                                <div
                                    class="p-3 text-center rounded-xl border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 cursor-pointer transition-colors">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <span class="text-sm font-medium">Izin</span>
                                </div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="type" value="cuti" class="peer sr-only"
                                    {{ old('type') == 'cuti' ? 'checked' : '' }}>
                                <div
                                    class="p-3 text-center rounded-xl border-2 border-gray-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 cursor-pointer transition-colors">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-purple-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    <span class="text-sm font-medium">Cuti</span>
                                </div>
                            </label>
                            <label class="relative">
                                <input type="radio" name="type" value="sakit" class="peer sr-only"
                                    {{ old('type') == 'sakit' ? 'checked' : '' }}>
                                <div
                                    class="p-3 text-center rounded-xl border-2 border-gray-200 peer-checked:border-red-500 peer-checked:bg-red-50 cursor-pointer transition-colors">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                        </path>
                                    </svg>
                                    <span class="text-sm font-medium">Sakit</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}"
                                class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                            <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}"
                                class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alasan</label>
                        <textarea name="reason" rows="4" placeholder="Jelaskan alasan perizinan Anda..."
                            class="w-full p-3 rounded-xl border-gray-300 shadow-sm focus:border-sky-500 focus:ring-sky-500">{{ old('reason') }}</textarea>
                    </div>

                    <!-- Attachment -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran (Opsional)</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center" id="dropzone">
                            <input type="file" name="attachment" id="attachment" accept="image/*" class="hidden">
                            <div id="preview" class="hidden mb-3">
                                <img id="preview-image" class="w-full max-h-48 object-contain rounded-xl">
                            </div>
                            <label for="attachment" class="cursor-pointer">
                                <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                <p class="text-sm text-gray-500">Klik untuk upload foto bukti</p>
                                <p class="text-xs text-gray-400 mt-1">Maks. 5MB (JPG, PNG)</p>
                            </label>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3 bg-sky-600 hover:bg-sky-700 text-white font-semibold rounded-xl transition-colors">
                        Kirim Pengajuan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('attachment').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image').src = e.target.result;
                    document.getElementById('preview').classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>
