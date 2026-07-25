<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Kredensial Guru</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 24px;
            color: #1f2937;
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
            margin: 4px 0 0;
            color: #666;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 7px 8px;
            text-align: left;
            font-size: 10px;
        }

        th {
            background: #4f46e5;
            color: #fff;
            font-size: 10px;
        }

        tr:nth-child(even) td {
            background: #f5f6fa;
        }

        td.no {
            text-align: center;
            width: 36px;
        }

        .mono {
            font-family: DejaVu Sans Mono, monospace;
        }

        .muted {
            color: #9ca3af;
            font-style: italic;
        }

        .note {
            margin-top: 16px;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Daftar Kredensial Guru</h1>
        @if ($selectedOffice)
            <p><strong>Kantor: {{ $selectedOffice->name }}</strong></p>
        @endif
        <p>Username &amp; password untuk login. Dokumen rahasia — simpan dengan aman.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Nama Guru</th>
                <th>Username</th>
                <th>Password</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    <td>{{ $user->name }}</td>
                    <td class="mono">{{ $user->email }}</td>
                    <td class="mono">
                        @if ($user->visible_password)
                            {{ $user->visible_password }}
                        @else
                            <span class="muted">belum di-set</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px;">Belum ada data guru.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="note">Dicetak {{ now()->translatedFormat('d F Y H:i') }} · {{ $users->count() }} guru</p>
</body>

</html>
