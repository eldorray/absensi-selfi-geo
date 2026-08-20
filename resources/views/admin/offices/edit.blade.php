<x-layouts.app>
    <div class="mx-auto max-w-2xl space-y-6">
        <x-admin.page-header kicker="Master Data" title="Edit Kantor" description="Perbarui detail lokasi kantor">
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
            <form method="POST" action="{{ route('admin.offices.update', $office) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Name -->
                <div>
                    <label for="name" class="admin-label">Nama Kantor</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $office->name) }}"
                        class="admin-field p-2.5">
                    @error('name')
                        <p class="admin-hint admin-text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coordinates -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="admin-label">Latitude</label>
                        <input type="text" name="latitude" id="latitude"
                            value="{{ old('latitude', $office->latitude) }}" class="admin-field p-2.5">
                        @error('latitude')
                            <p class="admin-hint admin-text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="longitude" class="admin-label">Longitude</label>
                        <input type="text" name="longitude" id="longitude"
                            value="{{ old('longitude', $office->longitude) }}" class="admin-field p-2.5">
                        @error('longitude')
                            <p class="admin-hint admin-text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="school_level" class="admin-label">Jenjang Sekolah</label>
                    <select name="school_level" id="school_level" class="admin-field p-2.5">
                        <option value="">Belum dipetakan</option>
                        <option value="mi" @selected(old('school_level', $office->school_level) === 'mi')>MI</option>
                        <option value="smp" @selected(old('school_level', $office->school_level) === 'smp')>SMP</option>
                    </select>
                </div>

                <!-- Radius -->
                <div>
                    <label for="radius_meters" class="admin-label">Radius (meter)</label>
                    <input type="number" name="radius_meters" id="radius_meters"
                        value="{{ old('radius_meters', $office->radius_meters) }}" class="admin-field p-2.5"
                        min="10" max="5000">
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
                        Perbarui
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
