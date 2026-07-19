<x-layouts.app>
    <div class="mx-auto max-w-2xl space-y-6">
        <x-admin.page-header kicker="Master Data" title="Tambah Kantor Baru"
            description="Masukkan detail lokasi kantor untuk geofencing">
            <a href="{{ route('admin.offices.index') }}"
                class="admin-button-secondary inline-flex items-center gap-1.5 px-4 py-2 text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
        </x-admin.page-header>

        <!-- Form -->
        <div class="admin-glass-panel p-6 md:p-8">
            <form method="POST" action="{{ route('admin.offices.store') }}" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="admin-label">Nama Kantor</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="admin-field p-2.5" placeholder="Contoh: Kantor Pusat Jakarta">
                    @error('name')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coordinates -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="admin-label">Latitude</label>
                        <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}"
                            class="admin-field p-2.5" placeholder="-6.200000">
                        @error('latitude')
                            <p class="admin-hint admin-text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="longitude" class="admin-label">Longitude</label>
                        <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}"
                            class="admin-field p-2.5" placeholder="106.816667">
                        @error('longitude')
                            <p class="admin-hint admin-text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Get Current Location Button -->
                <div>
                    <button type="button" onclick="getCurrentLocation()"
                        class="admin-button-secondary inline-flex items-center gap-2 px-4 py-2 text-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                            </path>
                        </svg>
                        Gunakan Lokasi Saat Ini
                    </button>
                </div>

                <!-- Radius -->
                <div>
                    <label for="radius_meters" class="admin-label">Radius (meter)</label>
                    <input type="number" name="radius_meters" id="radius_meters"
                        value="{{ old('radius_meters', 100) }}" class="admin-field p-2.5" min="10" max="5000">
                    <p class="admin-hint">Jarak maksimal karyawan dari titik kantor untuk absensi (10-5000 meter)</p>
                    @error('radius_meters')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.offices.index') }}" class="admin-button-secondary px-4 py-2 text-sm">
                        Batal
                    </a>
                    <button type="submit" class="admin-button-primary px-6 py-2 text-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById('latitude').value = position.coords.latitude.toFixed(8);
                    document.getElementById('longitude').value = position.coords.longitude.toFixed(8);
                }, function(error) {
                    alert('Gagal mendapatkan lokasi: ' + error.message);
                });
            } else {
                alert('Geolocation tidak didukung oleh browser Anda.');
            }
        }
    </script>
</x-layouts.app>
