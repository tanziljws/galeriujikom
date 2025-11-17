@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: white !important;">
    <div class="card-body py-4 px-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-center flex-grow-1">
          <h2 class="mb-2" style="font-weight: 700; color: #3b6ea5;">Laporan Foto Galeri</h2>
          <p class="mb-0" style="color: #6c757d;">Statistik likes, dislikes, dan unduhan setiap foto</p>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
          </a>
          <a href="{{ route('admin.reports.photos.pdf') }}" class="btn btn-danger" target="_blank">
            <i class="ri-file-pdf-line me-1"></i> Export PDF
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Filter -->
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
      <div class="row g-3">
        <div class="col-md-6">
          <input type="text" id="searchInput" class="form-control" placeholder="Cari judul atau kategori...">
        </div>
        <div class="col-md-3">
          <select id="sortBy" class="form-select">
            <option value="likes">Urutkan: Likes Terbanyak</option>
            <option value="dislikes">Urutkan: Dislikes Terbanyak</option>
            <option value="downloads">Urutkan: Unduhan Terbanyak</option>
            <option value="date">Urutkan: Terbaru</option>
          </select>
        </div>
        <div class="col-md-3">
          <select id="filterCategory" class="form-select">
            <option value="">Semua Kategori</option>
            @php
              $categories = array_unique(array_column(array_column($photoReports, 'photo'), 'category'));
            @endphp
            @foreach($categories as $cat)
              <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Photos Grid -->
  <div class="row g-4" id="photosGrid">
    @forelse($photoReports as $report)
      @php
        $photo = $report['photo'];
        $stats = $report['stats'];
        $url = $photo->filename ? asset('uploads/gallery/'.$photo->filename) : ($photo->image_path ? asset('storage/'.$photo->image_path) : '');
        $totalInteractions = $stats['likes'] + $stats['dislikes'] + $stats['downloads'];
      @endphp
      <div class="col-md-6 col-lg-4 col-xl-3 photo-item" 
           data-title="{{ strtolower($photo->title) }}" 
           data-category="{{ $photo->category }}"
           data-likes="{{ $stats['likes'] }}"
           data-dislikes="{{ $stats['dislikes'] }}"
           data-downloads="{{ $stats['downloads'] }}"
           data-date="{{ $photo->created_at->timestamp }}">
        <div class="card h-100 shadow-sm">
          <div class="ratio ratio-1x1">
            <img src="{{ $url }}" alt="{{ $photo->title }}" style="object-fit: cover;" loading="lazy">
          </div>
          <div class="card-body">
            <h6 class="card-title text-truncate" title="{{ $photo->title }}">{{ $photo->title }}</h6>
            <p class="card-text text-muted small mb-2">
              <i class="ri-folder-line"></i> {{ $photo->category }}
            </p>
            <p class="card-text text-muted small mb-3">
              <i class="ri-calendar-line"></i> {{ $photo->created_at->format('d M Y') }}
            </p>
            
            <!-- Statistics -->
            <div class="border-top pt-3">
              <div class="row g-2 text-center">
                <div class="col-4">
                  <div class="bg-danger bg-opacity-10 rounded p-2">
                    <i class="ri-heart-fill text-danger"></i>
                    <div class="fw-bold">{{ $stats['likes'] }}</div>
                    <small class="text-muted">Likes</small>
                  </div>
                </div>
                <div class="col-4">
                  <div class="bg-warning bg-opacity-10 rounded p-2">
                    <i class="ri-dislike-fill text-warning"></i>
                    <div class="fw-bold">{{ $stats['dislikes'] }}</div>
                    <small class="text-muted">Dislikes</small>
                  </div>
                </div>
                <div class="col-4">
                  <div class="bg-info bg-opacity-10 rounded p-2">
                    <i class="ri-download-fill text-info"></i>
                    <div class="fw-bold">{{ $stats['downloads'] }}</div>
                    <small class="text-muted">Unduh</small>
                  </div>
                </div>
              </div>
            </div>
            
            @if($totalInteractions > 0)
              <div class="mt-2 text-center">
                <span class="badge bg-primary">{{ $totalInteractions }} Total Interaksi</span>
              </div>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="col-12 text-center text-muted py-5">
        <i class="ri-image-line fs-1"></i>
        <p class="mt-2">Belum ada data foto</p>
      </div>
    @endforelse
  </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('searchInput');
  const sortBy = document.getElementById('sortBy');
  const filterCategory = document.getElementById('filterCategory');
  const photosGrid = document.getElementById('photosGrid');
  const photoItems = Array.from(document.querySelectorAll('.photo-item'));
  
  function filterAndSort() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedCategory = filterCategory.value;
    const sortType = sortBy.value;
    
    // Filter
    let filteredItems = photoItems.filter(item => {
      const title = item.dataset.title;
      const category = item.dataset.category;
      
      const matchesSearch = title.includes(searchTerm) || category.toLowerCase().includes(searchTerm);
      const matchesCategory = !selectedCategory || category === selectedCategory;
      
      return matchesSearch && matchesCategory;
    });
    
    // Sort
    filteredItems.sort((a, b) => {
      switch(sortType) {
        case 'likes':
          return parseInt(b.dataset.likes) - parseInt(a.dataset.likes);
        case 'dislikes':
          return parseInt(b.dataset.dislikes) - parseInt(a.dataset.dislikes);
        case 'downloads':
          return parseInt(b.dataset.downloads) - parseInt(a.dataset.downloads);
        case 'date':
          return parseInt(b.dataset.date) - parseInt(a.dataset.date);
        default:
          return 0;
      }
    });
    
    // Hide all items first
    photoItems.forEach(item => item.style.display = 'none');
    
    // Show filtered and sorted items
    filteredItems.forEach(item => {
      item.style.display = 'block';
      photosGrid.appendChild(item);
    });
    
    // Show no results message
    if (filteredItems.length === 0) {
      if (!document.getElementById('noResults')) {
        const noResults = document.createElement('div');
        noResults.id = 'noResults';
        noResults.className = 'col-12 text-center text-muted py-5';
        noResults.innerHTML = '<i class="ri-search-line fs-1"></i><p class="mt-2">Tidak ada foto yang sesuai</p>';
        photosGrid.appendChild(noResults);
      }
    } else {
      const noResults = document.getElementById('noResults');
      if (noResults) noResults.remove();
    }
  }
  
  searchInput.addEventListener('keyup', filterAndSort);
  sortBy.addEventListener('change', filterAndSort);
  filterCategory.addEventListener('change', filterAndSort);
});
</script>
@endpush
@endsection
