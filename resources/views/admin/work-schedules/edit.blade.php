<x-layouts.app>
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.work-schedules.index') }}"
                class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Jadwal Kerja</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $user->name }} -
                {{ $user->office?->name ?? 'Tidak ada kantor' }}</p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
            <form method="POST" action="{{ route('admin.work-schedules.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    @foreach ($days as $key => $label)
                        @php $schedule = $schedules[$key] ?? null; @endphp
                        <div class="flex items-center gap-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-700/50">
                            <!-- Day Name -->
                            <div class="w-24 font-semibold text-gray-900 dark:text-white">{{ $label }}</div>

                            <!-- Check In Time -->
                            <div class="flex-1">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Jam Masuk</label>
                                <input type="time" name="schedules[{{ $key }}][check_in_time]"
                                    value="{{ $schedule ? \Carbon\Carbon::parse($schedule->check_in_time)->format('H:i') : '07:00' }}"
                                    class="w-full p-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            </div>

                            <!-- Check Out Time -->
                            <div class="flex-1">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Jam Pulang</label>
                                <input type="time" name="schedules[{{ $key }}][check_out_time]"
                                    value="{{ $schedule ? \Carbon\Carbon::parse($schedule->check_out_time)->format('H:i') : '16:00' }}"
                                    class="w-full p-2 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-sky-500 focus:ring-sky-500">
                            </div>

                            <!-- Active Toggle -->
                            <div class="flex flex-col items-center">
                                <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Aktif</label>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="schedules[{{ $key }}][is_active]"
                                        value="1" {{ !$schedule || $schedule->is_active ? 'checked' : '' }}
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-sky-300 dark:peer-focus:ring-sky-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-sky-600">
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($errors->any())
                    <div
                        class="mt-6 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-xl p-4">
                        <ul class="text-red-700 dark:text-red-300 text-sm list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Submit -->
                <div class="flex items-center justify-end space-x-3 mt-6">
                    <a href="{{ route('admin.work-schedules.index') }}"
                        class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-xl transition-colors">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white font-medium rounded-xl transition-colors">
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
