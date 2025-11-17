@extends('layouts.admin')

@push('styles')
<style>
/* Modal backdrop fix */
.modal-backdrop {
    display: none !important;
}
.modal {
    background-color: rgba(0, 0, 0, 0.5) !important;
    z-index: 9999 !important;
}
.modal-dialog {
    z-index: 10000 !important;
    pointer-events: all !important;
}
.modal-content {
    background-color: #ffffff !important;
    pointer-events: all !important;
    border: 1px solid #dee2e6;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}
.modal-body {
    background-color: #ffffff !important;
}
.modal-body input,
.modal-body textarea,
.modal-body select {
    background-color: #ffffff !important;
    color: #212529 !important;
    border: 1px solid #ced4da !important;
    opacity: 1 !important;
}
.modal-body input:focus {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}
.modal-footer .btn {
    pointer-events: auto !important;
    cursor: pointer !important;
    opacity: 1 !important;
}
.modal-footer .btn-primary {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
}
.modal-footer .btn-primary:hover {
    background-color: #0b5ed7 !important;
    border-color: #0a58ca !important;
}
.modal-footer .btn-secondary {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: #ffffff !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: white !important;">
    <div class="card-body py-4 px-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-center flex-grow-1">
          <h2 class="mb-2" style="font-weight: 700; color: #3b6ea5;">Kategori Galeri</h2>
          <p class="mb-0" style="color: #6c757d;">Kelola kategori untuk galeri foto</p>
        </div>
        <div>
          <a href="{{ route('admin.gallery.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
          </a>
        </div>
      </div>
    </div>
  </div>

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

@if (session('status'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<!-- Card Tambah Kategori Baru -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
  <div class="card-body p-4">
    <h5 class="mb-3" style="color: #3b6ea5; font-weight: 600;">Tambah Kategori Baru</h5>
    <form method="POST" action="{{ route('admin.gallery.categories.store') }}" class="row g-3">
      @csrf
      <div class="col-md-8">
        <div class="input-group">
          <span class="input-group-text bg-light"><i class="ri-add-line"></i></span>
          <input type="text" name="name" class="form-control" placeholder="Masukkan nama kategori" required>
        </div>
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-primary w-100 rounded-pill">
          <i class="ri-add-circle-line me-1"></i> Tambah Kategori
        </button>
      </div>
    </form>
  </div>
</div>



<!-- Card Daftar Kategori -->
<div class="card">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="card-title mb-0">Daftar Kategori</h5>
      <span class="text-muted small">{{ count($categories ?? []) }} kategori tersedia</span>
    </div>
    
    @if(count($categories ?? []) > 0)
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 50px">No</th>
              <th>Nama Kategori</th>
              <th class="text-end" style="width: 200px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categories as $i => $category)
            <tr>
              <td class="text-muted">{{ $i + 1 }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="bg-primary bg-opacity-10 text-primary p-2 rounded me-3">
                    <i class="ri-folder-2-line fs-5"></i>
                  </div>
                  <div>
                    <h6 class="mb-0">{{ $category }}</h6>
                    <small class="text-muted">{{ $photosByCategory[$category] ?? 0 }} foto</small>
                  </div>
                </div>
              </td>
              <td class="text-end">
                <div class="btn-group" role="group">
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailCategory{{ $i }}">
                    <i class="ri-eye-line"></i> Detail
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editCategory{{ $i }}">
                    <i class="ri-edit-line"></i> Edit
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCategory{{ $i }}">
                    <i class="ri-delete-bin-line"></i> Hapus
                  </button>
                </div>
                
                <!-- Modal Detail -->
                <div class="modal fade" id="detailCategory{{ $i }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
                  <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                      <div class="modal-header bg-info bg-opacity-10">
                        <h5 class="modal-title text-info">
                          <i class="ri-folder-2-line me-2"></i>Detail Kategori
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <div class="row mb-3">
                          <div class="col-md-4 fw-bold">Nama Kategori:</div>
                          <div class="col-md-8">{{ $category }}</div>
                        </div>
                        <div class="row mb-3">
                          <div class="col-md-4 fw-bold">Jumlah Foto:</div>
                          <div class="col-md-8">
                            <span class="badge bg-primary">{{ $photosByCategory[$category] ?? 0 }} foto</span>
                          </div>
                        </div>
                        <hr>
                        <h6 class="mb-3">Foto dalam kategori ini:</h6>
                        @php
                          $manifestPath = public_path('uploads/gallery/manifest.json');
                          $categoryPhotos = [];
                          if (file_exists($manifestPath)) {
                            $allPhotos = json_decode(file_get_contents($manifestPath), true) ?: [];
                            foreach ($allPhotos as $photo) {
                              if (($photo['category'] ?? '') === $category) {
                                $categoryPhotos[] = $photo;
                              }
                            }
                          }
                        @endphp
                        
                        @if(count($categoryPhotos) > 0)
                          <div class="row g-2">
                            @foreach($categoryPhotos as $photo)
                              <div class="col-md-3 col-6">
                                <div class="card">
                                  <img src="{{ $photo['url'] ?? '' }}" class="card-img-top" alt="{{ $photo['title'] ?? 'Foto' }}" style="height: 120px; object-fit: cover;">
                                  <div class="card-body p-2">
                                    <p class="card-text small text-truncate mb-0" title="{{ $photo['title'] ?? 'Tanpa judul' }}">
                                      {{ $photo['title'] ?? 'Tanpa judul' }}
                                    </p>
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @else
                          <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>Belum ada foto dalam kategori ini
                          </div>
                        @endif
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Modal Edit -->
                <div class="modal fade" id="editCategory{{ $i }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <form method="POST" action="{{ route('admin.gallery.categories.rename') }}" id="editForm{{ $i }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="old" value="{{ $category }}">
                        <div class="modal-body">
                          <div class="mb-3">
                            <label for="newName{{ $i }}" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" id="newName{{ $i }}" name="new" value="{{ $category }}" required autofocus>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                          <button type="submit" class="btn btn-primary" onclick="console.log('Button clicked!'); return true;">
                            <i class="ri-save-line me-1"></i> Simpan Perubahan
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
                
                <!-- Modal Hapus -->
                <div class="modal fade" id="deleteCategory{{ $i }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="false">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title text-danger">Hapus Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <form method="POST" action="{{ route('admin.gallery.categories.delete') }}">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="name" value="{{ $category }}">
                        <div class="modal-body">
                          <p>Apakah Anda yakin ingin menghapus kategori <strong>{{ $category }}</strong>?</p>
                          <p class="text-danger small mb-0">Pastikan tidak ada foto yang menggunakan kategori ini.</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                          <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="text-center py-5">
        <div class="mb-3">
          <i class="ri-folder-open-line text-muted" style="font-size: 4rem;"></i>
        </div>
        <h5 class="text-muted">Belum ada kategori</h5>
        <p class="text-muted">Mulai dengan menambahkan kategori baru menggunakan form di atas</p>
      </div>
    @endif
  </div>
</div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Categories page loaded');
    
    // Debug: Log when modal is shown
    document.querySelectorAll('[id^="editCategory"]').forEach(function(modal) {
        modal.addEventListener('shown.bs.modal', function() {
            console.log('Modal opened:', this.id);
            // Focus pada input field
            const input = this.querySelector('input[name="new"]');
            if(input) {
                setTimeout(() => {
                    input.focus();
                    input.select();
                }, 100);
            }
        });
    });

    // Debug: Log form submissions
    document.querySelectorAll('[id^="editForm"]').forEach(function(form) {
        console.log('Form found:', form.id);
        
        form.addEventListener('submit', function(e) {
            console.log('Form submitted:', this.id);
            console.log('Action:', this.action);
            console.log('Old:', this.querySelector('input[name="old"]').value);
            console.log('New:', this.querySelector('input[name="new"]').value);
            // Form akan submit secara normal - tidak preventDefault
        });
        
        // Tambahkan event listener untuk tombol submit
        const submitBtn = form.querySelector('button[type="submit"]');
        if(submitBtn) {
            console.log('Submit button found for form:', form.id);
            submitBtn.addEventListener('click', function(e) {
                console.log('Submit button clicked!');
                e.stopPropagation(); // Stop event dari bubble
                
                // Validate form
                if(form.checkValidity()) {
                    console.log('Form is valid, submitting...');
                    form.submit();
                } else {
                    console.log('Form is invalid');
                    form.reportValidity();
                }
            }, true); // Use capture phase
        }
    });
    
    // Handle tombol Batal
    document.querySelectorAll('button[data-bs-dismiss="modal"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            console.log('Batal button clicked');
        });
    });
});
</script>
@endpush
