@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 mb-3" style="background: white !important;">
    <div class="card-body py-3 px-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-center flex-grow-1">
          <h2 class="mb-2" style="font-weight: 700; color: #3b6ea5;">Laporan Interaktif</h2>
          <p class="mb-0" style="color: #6c757d;">Dashboard statistik pengguna dan galeri foto</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Statistics Cards -->
  <div class="row g-3 mb-3">
    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3">
                <i class="ri-user-line fs-3"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Total Pengguna</h6>
              <h3 class="mb-0">{{ $totalUsers }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3">
                <i class="ri-thumb-down-line fs-3"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Total Dislikes</h6>
              <h3 class="mb-0">{{ !empty($photoReports) ? array_sum(array_column(array_column($photoReports, 'stats'), 'dislikes')) : 0 }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-success bg-opacity-10 text-success rounded-circle p-3">
                <i class="ri-image-line fs-3"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Total Foto</h6>
              <h3 class="mb-0">{{ count($photoReports) }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-3">
                <i class="ri-heart-line fs-3"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Total Likes</h6>
              <h3 class="mb-0">{{ !empty($photoReports) ? array_sum(array_column(array_column($photoReports, 'stats'), 'likes')) : 0 }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
              <div class="bg-info bg-opacity-10 text-info rounded-circle p-3">
                <i class="ri-download-line fs-3"></i>
              </div>
            </div>
            <div class="flex-grow-1 ms-3">
              <h6 class="text-muted mb-1">Total Unduhan</h6>
              <h3 class="mb-0">{{ !empty($photoReports) ? array_sum(array_column(array_column($photoReports, 'stats'), 'downloads')) : 0 }}</h3>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-3">
            <i class="ri-user-line fs-3 text-primary me-3"></i>
            <h5 class="mb-0">Laporan Pengguna</h5>
          </div>
          <p class="text-muted mb-3">Lihat daftar lengkap pengguna yang sudah mendaftar dan login</p>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.users') }}" class="btn btn-primary">
              <i class="ri-eye-line me-1"></i> Lihat Detail
            </a>
            <a href="{{ route('admin.reports.users.pdf') }}" class="btn btn-outline-primary" target="_blank">
              <i class="ri-file-pdf-line me-1"></i> Export PDF
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-3">
            <i class="ri-image-line fs-3 text-success me-3"></i>
            <h5 class="mb-0">Laporan Foto Galeri</h5>
          </div>
          <p class="text-muted mb-3">Lihat statistik likes, dislikes, dan unduhan setiap foto</p>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.photos') }}" class="btn btn-success">
              <i class="ri-eye-line me-1"></i> Lihat Detail
            </a>
            <a href="{{ route('admin.reports.photos.pdf') }}" class="btn btn-outline-success" target="_blank">
              <i class="ri-file-pdf-line me-1"></i> Export PDF
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Users -->
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
      <h5 class="mb-3" style="color: #3b6ea5; font-weight: 600;">
        <i class="ri-user-add-line me-2"></i>Pengguna Terbaru
      </h5>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead class="table-light">
            <tr>
              <th>Nama</th>
              <th>Email</th>
              <th>Tanggal Daftar</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentUsers as $user)
              <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="3" class="text-center text-muted">Belum ada pengguna</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Top Photos -->
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      <h5 class="mb-3" style="color: #3b6ea5; font-weight: 600;">
        <i class="ri-star-line me-2"></i>Foto Paling Populer
      </h5>
      <div class="row g-3">
        @forelse(!empty($photoReports) ? array_slice($photoReports, 0, 6) : [] as $report)
          @php
            $photo = $report['photo'] ?? null;
            $stats = $report['stats'] ?? ['likes' => 0, 'dislikes' => 0, 'downloads' => 0];
            $url = ($photo && $photo->filename) ? asset('uploads/gallery/'.$photo->filename) : (($photo && $photo->image_path) ? asset('storage/'.$photo->image_path) : '');
          @endphp
          <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm">
              <div class="ratio ratio-16x9">
                <img src="{{ $url }}" alt="{{ $photo->title }}" style="object-fit: cover;">
              </div>
              <div class="card-body">
                <h6 class="card-title">{{ $photo->title ?? 'Tanpa Judul' }}</h6>
                <p class="card-text text-muted small">{{ $photo->category ?? 'Lainnya' }}</p>
                <div class="d-flex justify-content-between text-muted small">
                  <span><i class="ri-heart-fill text-danger"></i> {{ $stats['likes'] ?? 0 }}</span>
                  <span><i class="ri-dislike-fill text-warning"></i> {{ $stats['dislikes'] ?? 0 }}</span>
                  <span><i class="ri-download-fill text-info"></i> {{ $stats['downloads'] ?? 0 }}</span>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center text-muted py-4">Belum ada data foto</div>
        @endforelse
      </div>
    </div>
  </div>

</div>
@endsection
