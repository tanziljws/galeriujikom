<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pengguna</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 15px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px 20px 15px 20px;
            background: white;
            color: #333;
            border-bottom: 4px solid #3b6ea5;
            position: relative;
        }
        .header-logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 10px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #3b6ea5;
        }
        .header p {
            margin: 2px 0;
            font-size: 13px;
            color: #666;
        }
        .header .subtitle {
            font-size: 12px;
            color: #999;
            font-style: italic;
        }
        .info-box {
            background: #f8f9fa;
            padding: 12px;
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box strong {
            color: #667eea;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
        }
        th {
            background: #3b6ea5;
            color: white;
            padding: 16px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 15px;
            border: none;
        }
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 15px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 2px solid #667eea;
            padding-top: 12px;
        }
        .footer p {
            margin: 3px 0;
        }
        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }
    </style>
</head>
<body>
    <div class="header">
        {{-- Logo dinonaktifkan sementara karena memerlukan GD extension --}}
        {{-- Aktifkan extension=gd di php.ini untuk menampilkan logo --}}
        <h1>LAPORAN PENGGUNA</h1>
        <p>SMKN 4 Bogor - Galeri Foto</p>
        <p class="subtitle">Daftar Pengguna yang Terdaftar di Sistem</p>
    </div>

    <div class="info-box">
        <strong>Informasi Laporan</strong><br>
        Tanggal Dibuat: {{ $generatedAt }}<br>
        Total Pengguna: {{ count($users) }} orang
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama</th>
                <th width="30%">Email</th>
                <th width="20%">Tanggal Daftar</th>
                <th width="20%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                    <td>
                        @if($user->created_at->diffInDays(now()) < 7)
                            <span class="badge badge-success">Baru</span>
                        @else
                            <span class="badge badge-secondary">Aktif</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">Belum ada pengguna terdaftar</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh sistem SMKN 4 Bogor</p>
        <p>&copy; {{ date('Y') }} SMKN 4 Bogor. All rights reserved.</p>
    </div>
</body>
</html>
