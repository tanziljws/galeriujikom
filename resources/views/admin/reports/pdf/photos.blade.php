<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Foto Galeri</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
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
            padding: 10px;
            border-left: 4px solid #667eea;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .info-box strong {
            color: #667eea;
            font-size: 15px;
        }
        .summary {
            background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            border: 1px solid #667eea30;
        }
        .summary strong {
            color: #667eea;
            font-size: 16px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            background: white;
            border-radius: 4px;
            margin: 0 5px;
        }
        .summary-item .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
        }
        th {
            background: #3b6ea5;
            color: white;
            padding: 16px 12px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            border: none;
        }
        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
            font-size: 14px;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f0f0f0;
        }
        .photo-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #e0e0e0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .no-photo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-size: 32px;
            border: 2px solid #667eea30;
        }
        .stats {
            text-align: center;
            font-weight: bold;
        }
        .stat-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
        }
        .badge-like {
            background: #ffe0e0;
            color: #dc3545;
        }
        .badge-dislike {
            background: #fff3cd;
            color: #ffc107;
        }
        .badge-download {
            background: #d1ecf1;
            color: #17a2b8;
        }
        .badge-total {
            background: #e7f3ff;
            color: #667eea;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 2px solid #667eea;
            padding-top: 10px;
        }
        .footer p {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        {{-- Logo dinonaktifkan sementara karena memerlukan GD extension --}}
        {{-- Aktifkan extension=gd di php.ini untuk menampilkan logo --}}
        <h1>LAPORAN FOTO GALERI</h1>
        <p>SMKN 4 Bogor - Statistik Interaksi Foto</p>
        <p class="subtitle">Laporan Likes, Dislikes, dan Unduhan</p>
    </div>

    <div class="info-box">
        <strong>Informasi Laporan</strong><br>
        Tanggal Dibuat: {{ $generatedAt }}<br>
        Total Foto: {{ count($photoReports) }} foto
    </div>

    @php
        $totalLikes = array_sum(array_column(array_column($photoReports, 'stats'), 'likes'));
        $totalDislikes = array_sum(array_column(array_column($photoReports, 'stats'), 'dislikes'));
        $totalDownloads = array_sum(array_column(array_column($photoReports, 'stats'), 'downloads'));
    @endphp

    <div class="summary">
        <strong>Ringkasan Statistik</strong>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Likes</div>
                <div class="value">{{ $totalLikes }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Dislikes</div>
                <div class="value">{{ $totalDislikes }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Unduhan</div>
                <div class="value">{{ $totalDownloads }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="10%">Foto</th>
                <th width="25%">Judul</th>
                <th width="20%">Kategori</th>
                <th width="12%">Tanggal</th>
                <th width="8%">Likes</th>
                <th width="8%">Dislikes</th>
                <th width="8%">Unduh</th>
                <th width="4%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($photoReports as $index => $report)
                @php
                    $photo = $report['photo'];
                    $stats = $report['stats'];
                    $totalInteractions = $stats['likes'] + $stats['dislikes'] + $stats['downloads'];
                    
                    // Get image URL
                    if ($photo->filename) {
                        $imagePath = public_path('uploads/gallery/' . $photo->filename);
                    } elseif ($photo->image_path) {
                        $imagePath = storage_path('app/public/' . $photo->image_path);
                    } else {
                        $imagePath = null;
                    }
                    
                    // Convert to base64 for PDF
                    $imageData = null;
                    if ($imagePath && file_exists($imagePath)) {
                        $imageData = base64_encode(file_get_contents($imagePath));
                        $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td style="text-align: center;">
                        @if($imageData)
                            <img src="data:image/{{ $imageType }};base64,{{ $imageData }}" class="photo-thumb" alt="foto">
                        @else
                            <div class="no-photo">
                                <span style="font-size: 20px;">&#128247;</span>
                            </div>
                        @endif
                    </td>
                    <td>{{ Str::limit($photo->title, 35) }}</td>
                    <td>{{ $photo->category }}</td>
                    <td style="text-align: center;">{{ $photo->created_at->format('d M Y') }}</td>
                    <td class="stats">
                        <span class="stat-badge badge-like">{{ $stats['likes'] }}</span>
                    </td>
                    <td class="stats">
                        <span class="stat-badge badge-dislike">{{ $stats['dislikes'] }}</span>
                    </td>
                    <td class="stats">
                        <span class="stat-badge badge-download">{{ $stats['downloads'] }}</span>
                    </td>
                    <td class="stats">
                        <span class="stat-badge badge-total">{{ $totalInteractions }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #999;">Belum ada data foto</td>
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
