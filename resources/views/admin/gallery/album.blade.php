@extends('layouts.admin')
@section('content')
<section class="section-fullscreen mb-4 section-alt py-3">
  <div class="container section-soft accented decor-gradient-top">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="text-center w-100">
          <h2 class="vm-title-center mb-1">Kelola Foto: {{ $albumTitle }}</h2>
          <div class="vm-subtitle">Tambah atau hapus foto di album ini</div>
      </div>
      <div class="ms-3 d-none d-md-block">
          <a href="{{ route('admin.gallery.index') }}" class="btn btn-light">Kembali</a>
      </div>
    </div>
  </div>
</section>

<div class="card-elevated p-3 mb-3">
    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-danger mb-2">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" id="album-add-form">
        @csrf
        <input type="hidden" name="title" value="{{ $albumTitle }}">
        <input type="hidden" name="category" value="{{ optional($photos->first())->category }}">
        <div class="row g-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">
                        <strong>Foto</strong> <span class="text-muted">(maksimal 15 foto per album)</span>
                    </label>
                    <button type="button" id="addMoreBtn" class="btn btn-sm btn-outline-primary">
                        <i class="ri-add-line"></i> Pilih File
                    </button>
                </div>
                <input id="photosInput" type="file" name="photos[]" class="form-control d-none" accept="image/*" multiple>
                <div class="border rounded p-3 bg-light">
                    <div id="fileInfo" class="text-center text-muted py-2">
                        <i class="ri-image-line fs-3"></i>
                        <p class="mb-0 mt-2">Tidak ada file yang dipilih</p>
                        <small>Pilih beberapa kali pun bisa. Pilihan akan digabung hingga 15 foto.</small>
                    </div>
                    <div id="previewGrid" class="row g-2"></div>
                </div>
                <div class="mt-2">
                    <strong>Total dipilih:</strong> <span id="countLabel" class="badge bg-primary">0</span>/15
                </div>
            </div>
            <div class="col-12 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="ri-upload-line me-1"></i> Tambah
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card-elevated p-3">
  <div class="row g-3">
    @forelse($photos as $p)
      @php
        $url = $p->filename ? asset('uploads/gallery/'.$p->filename) : ($p->image_path ?? '');
      @endphp
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="card h-100 shadow-animate" style="overflow:hidden">
          <div class="ratio ratio-1x1">
            <img src="{{ $url }}" alt="foto" style="object-fit:cover;width:100%;height:100%">
          </div>
          <div class="p-2 d-flex justify-content-between align-items-center">
            <small class="text-muted">{{ optional($p->created_at)->format('Y-m-d') }}</small>
            <div class="d-flex gap-1">
              <button class="btn btn-sm btn-outline-primary btn-edit-photo" 
                      data-id="{{ $p->id }}" 
                      data-title="{{ $p->title }}" 
                      data-category="{{ $p->category }}" 
                      data-url="{{ $url }}">
                <i class="ri-edit-line"></i>
              </button>
              <form action="{{ route('admin.gallery.photo.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus foto ini?')" class="m-0 p-0">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
              </form>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12 text-center text-muted py-4">Belum ada foto pada album ini.</div>
    @endforelse
  </div>
</div>

<!-- Single Global Edit Modal -->
<div class="modal fade" id="editPhotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Foto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="card-elevated p-3">
          <form id="editPhotoForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Judul Album</label>
                <input type="text" name="title" id="editTitle" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <input type="text" name="category" id="editCategory" class="form-control">
              </div>
              <div class="col-12">
                <label class="form-label">Ganti Foto (opsional)</label>
                <input type="file" name="photo" id="editPhotoInput" class="form-control" accept="image/*">
                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti gambar</small>
              </div>
              <div class="col-12 text-center">
                <img id="editPreview" src="" alt="preview" class="img-fluid rounded" style="max-height: 250px">
              </div>
            </div>
            <div class="mt-3 d-flex gap-2">
              <button type="submit" class="btn btn-primary">Update</button>
              <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kembali</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const form = document.getElementById('album-add-form');
  if(!form) return;
  
  const input = document.getElementById('photosInput');
  const addBtn = document.getElementById('addMoreBtn');
  const preview = document.getElementById('previewGrid');
  const countLabel = document.getElementById('countLabel');
  const fileInfo = document.getElementById('fileInfo');
  
  // Array untuk menyimpan file objects
  let selectedFiles = [];

  // helper: render previews
  function renderPreviews(){
    preview.innerHTML = '';
    
    if (selectedFiles.length === 0) {
      fileInfo.style.display = 'block';
    } else {
      fileInfo.style.display = 'none';
      
      selectedFiles.forEach((f, idx) => {
        const col = document.createElement('div');
        col.className = 'col-4 col-md-3 col-lg-2';
        const wrap = document.createElement('div');
        wrap.className = 'position-relative border rounded overflow-hidden bg-white';
        const img = document.createElement('img');
        img.className = 'w-100';
        img.style.aspectRatio = '1/1';
        img.style.objectFit = 'cover';
        img.src = URL.createObjectURL(f);
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1';
        del.innerHTML = '<i class="ri-close-line"></i>';
        del.onclick = () => { removeAt(idx); };
        wrap.appendChild(img); 
        wrap.appendChild(del); 
        col.appendChild(wrap); 
        preview.appendChild(col);
      });
    }
    
    countLabel.textContent = selectedFiles.length;
    
    // Update input.files dengan DataTransfer
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    input.files = dt.files;
    
    console.log('Files in array:', selectedFiles.length);
    console.log('Files in input:', input.files.length);
  }

  function removeAt(index){
    selectedFiles.splice(index, 1);
    renderPreviews();
  }

  // When user selects files
  input.addEventListener('change', function(){
    const picked = Array.from(input.files);
    console.log('Files picked:', picked.length);
    
    for(const f of picked){
      if(selectedFiles.length >= 15) { 
        console.log('Reached max 15 files');
        alert('Maksimal 15 foto per album.');
        break; 
      }
      // de-duplicate by name+size
      const dup = selectedFiles.some(x => x.name===f.name && x.size===f.size);
      if(!dup) {
        selectedFiles.push(f);
        console.log('Added file:', f.name);
      } else {
        console.log('Duplicate file skipped:', f.name);
      }
    }
    renderPreviews();
    // clear the native selection so user can reselect same files if needed
    input.value = '';
  });

  // Add more button triggers file dialog again
  addBtn.addEventListener('click', function(){ input.click(); });

  // Submit validation
  form.addEventListener('submit', function(ev){
    console.log('Submit triggered. Files count:', selectedFiles.length);
    
    if(selectedFiles.length === 0){
      ev.preventDefault();
      alert('Silakan pilih minimal 1 foto.');
      return false;
    }
    if(selectedFiles.length > 15){
      ev.preventDefault();
      alert('Maksimal 15 foto per album.');
      return false;
    }
    
    // Update input.files sebelum submit
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    input.files = dt.files;
    
    console.log('Submitting with', selectedFiles.length, 'files');
    console.log('Input.files.length:', input.files.length);
    
    // Allow form to submit normally
    return true;
  });

  // Edit photo modal handler
  const editModal = document.getElementById('editPhotoModal');
  const editForm = document.getElementById('editPhotoForm');
  const editPreview = document.getElementById('editPreview');
  const editPhotoInput = document.getElementById('editPhotoInput');
  let modalInstance = null;
  let originalImageUrl = '';
  
  // Handle file input change - preview new image
  if (editPhotoInput) {
    editPhotoInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          editPreview.src = event.target.result;
        };
        reader.readAsDataURL(file);
      } else {
        // If no file selected, restore original image
        editPreview.src = originalImageUrl;
      }
    });
  }
  
  // Reset when modal is closed
  if (editModal) {
    editModal.addEventListener('hidden.bs.modal', function() {
      if (editPhotoInput) editPhotoInput.value = '';
      editPreview.src = originalImageUrl;
    });
  }
  
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-edit-photo');
    if (!btn) return;
    
    e.preventDefault();
    e.stopPropagation();
    
    const id = btn.dataset.id;
    const title = btn.dataset.title;
    const category = btn.dataset.category;
    const url = btn.dataset.url;
    
    // Store original URL
    originalImageUrl = url;
    
    // Set form action
    editForm.action = `/admin/gallery/photo/${id}`;
    
    // Populate fields
    document.getElementById('editTitle').value = title || '';
    document.getElementById('editCategory').value = category || '';
    
    // Reset file input
    if (editPhotoInput) editPhotoInput.value = '';
    
    // Hide preview first to prevent flicker
    editPreview.style.display = 'none';
    
    // Load image then show
    const img = new Image();
    img.onload = function(){
      editPreview.src = url || '';
      editPreview.style.display = 'block';
    };
    img.src = url || '';
    
    // Show modal
    if (!modalInstance) {
      modalInstance = new bootstrap.Modal(editModal);
    }
    modalInstance.show();
  });
})();
</script>
@endpush
@endsection
