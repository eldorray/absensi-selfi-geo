<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Absensi Harian - {{ $selectedDate->format('d-m-Y') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #4f46e5;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
        }

        .stat-box .number {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }

        .stat-box .label {
            font-size: 9px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background-color: #fce7f3;
            color: #9d174d;
        }

        .badge-gray {
            background-color: #e5e7eb;
            color: #374151;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>DAFTAR HADIR</h1>
        <p>{{ $selectedDate->translatedFormat('l, d F Y') }}</p>
        @if ($activeYear)
            <p>Tahun Ajaran {{ $activeYear->name }}</p>
        @endif
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="number">{{ $stats['total_employees'] }}</div>
            <div class="label">Jumlah Pegawai</div>
        </div>
        <div class="stat-box">
            <div class="number" style="color: #10b981;">{{ $stats['checked_in'] }}</div>
            <div class="label">Sudah Absen Masuk</div>
        </div>
        <div class="stat-box">
            <div class="number" style="color: #8b5cf6;">{{ $stats['checked_out'] }}</div>
            <div class="label">Sudah Absen Pulang</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No.</th>
                <th>Nama</th>
                <th>Jam Kerja</th>
                <th class="text-center">Jam Masuk</th>
                <th class="text-center">Jam Pulang</th>
                <th class="text-center">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportData as $index => $data)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        {{ $data['user']->name }}<br>
                        <small style="color: #666;">{{ $data['user']->role?->name ?? '-' }}</small>
                    </td>
                    <td>
                        @if ($data['work_schedule'])
                            {{ $data['work_schedule']->start_time }} - {{ $data['work_schedule']->end_time }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($data['attendance'])
                            {{ $data['attendance']->created_at->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($data['attendance'] && $data['attendance']->check_out_at)
                            {{ $data['attendance']->check_out_at->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @switch($data['status'])
                            @case('on_time')
                                <span class="badge badge-success">Hadir</span>
                            @break

                            @case('late')
                                <span class="badge badge-warning">Hadir Terlambat</span>
                            @break

                            @case('absent')
                                <span class="badge badge-danger">Alpha</span>
                            @break

                            @case('no_schedule')
                                <span class="badge badge-gray">Libur</span>
                            @break

                            @default
                                <span class="badge badge-gray">{{ $data['status'] }}</span>
                        @endswitch
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </body>

    </html>
