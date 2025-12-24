<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Rekap Absensi - {{ $startDate }} s/d {{ $endDate }}</title>
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

        .info-box {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
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

        .text-green {
            color: #065f46;
        }

        .text-amber {
            color: #92400e;
        }

        .text-red {
            color: #dc2626;
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
            background-color: #fee2e2;
            color: #dc2626;
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
        <h1>REKAP KEHADIRAN</h1>
        <p>{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} -
            {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</p>
        @if ($activeYear)
            <p>Tahun Ajaran {{ $activeYear->name }}</p>
        @endif
    </div>

    <div class="info-box">
        <strong>Total Hari Kerja: {{ $workDays }} Hari</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No.</th>
                <th>Nama</th>
                <th class="text-center">Hari Kerja</th>
                <th class="text-center">Total Hadir</th>
                <th class="text-center">Tepat Waktu</th>
                <th class="text-center">Terlambat</th>
                <th class="text-center">Alpha</th>
                <th class="text-center">Persentase</th>
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
                    <td class="text-center">{{ $data['work_days'] }}</td>
                    <td class="text-center text-green">
                        <strong>{{ $data['total_present'] }}</strong>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-success">{{ $data['total_on_time'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-warning">{{ $data['total_late'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-danger">{{ max(0, $data['total_alpha']) }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $rate = $data['attendance_rate'];
                        @endphp
                        <strong class="{{ $rate >= 90 ? 'text-green' : ($rate >= 75 ? 'text-amber' : 'text-red') }}">
                            {{ $rate }}%
                        </strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data pegawai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>

</html>
