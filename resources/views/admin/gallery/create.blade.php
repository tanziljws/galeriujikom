@extends('layouts.admin')
@section('content')
<section class="section-fullscreen mb-4 section-alt py-3">
  <div class="container section-soft accented decor-gradient-top">
    <div class="text-center py-1">
        <h2 class="vm-title-center mb-1">Upload Foto</h2>
        <div class="vm-subtitle">Tambahkan dokumentasi kegiatan ke galeri sekolah</div>
    </div>
  </div>
</section>
<div class="card-elevated p-3">
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
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <form action="{{ route('admin.gallery.store') }}" method="POST" enctype="multipart/form-data" id="gallery-upload-form">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
<<<<<<< HEAD
                <label class="form-label">Judul Album</label>
                <input type="text" name="title" class="form-control" placeholder="Misal: Pensi" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <select name="category" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    @php
                        $categoriesPath = resource_path('data/umbrella_categories.json');
                        $cats = file_exists($categoriesPath) ? json_decode(file_get_contents($categoriesPath), true) : [];
                    @endphp
                    @foreach($cats as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label d-flex justify-content-between align-items-center">
                  <span>Foto (maksimal 15 foto per album)</span>
                  <button type="button" id="addMoreBtn" class="btn btn-sm btn-outline-primary">Tambah Foto</button>
                </label>
                <input id="photosInput" type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                <small class="text-muted">Pilih beberapa kali pun bisa. Pilihan akan digabung hingga 15 foto.</small>

                <div id="previewGrid" class="row g-2 mt-2"></div>
                <div class="mt-1 small"><strong>Total dipilih:</strong> <span id="countLabel">0</span>/15</div>
=======
                <label class="form-label">Judul (opsional, untuk semua foto)</label>
                <input type="text" name="title" class="form-control" placeholder="Misal: Pensi">
            </div>
            <div class="col-md-6">
                <label class="form-label">Kategori</label>
                <input type="text" name="category" class="form-control" placeholder="Misal: Transforkrab / Workshop Guru / Pensi" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Foto (maks. 50 file, total 200MB)</label>
                <input type="file" name="images[]" class="form-control" accept="image/*" multiple required>
                <small class="text-muted">Pilih hingga 50 foto. Total ukuran semua file tidak boleh melebihi 200MB.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Keterangan (opsional, untuk semua foto)</label>
                <input type="text" name="caption" class="form-control" placeholder="Keterangan foto">
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b
            </div>
            
        </div>
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.gallery.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
    @push('scripts')
    <script>
      (function(){
        const form = document.getElementById('gallery-upload-form');
        if(!form) return;
<<<<<<< HEAD
        const input = document.getElementById('photosInput');
        const addBtn = document.getElementById('addMoreBtn');
        const preview = document.getElementById('previewGrid');
        const countLabel = document.getElementById('countLabel');
        
        // Array untuk menyimpan file objects
        let selectedFiles = [];

        // helper: render previews
        function renderPreviews(){
          preview.innerHTML = '';
          selectedFiles.forEach((f, idx) => {
            const col = document.createElement('div');
            col.className = 'col-4';
            const wrap = document.createElement('div');
            wrap.className = 'position-relative border rounded overflow-hidden';
            const img = document.createElement('img');
            img.className = 'w-100';
            img.style.aspectRatio = '1/1';
            img.style.objectFit = 'cover';
            img.src = URL.createObjectURL(f);
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'btn btn-sm btn-danger position-absolute top-0 end-0 m-1';
            del.textContent = '×';
            del.onclick = () => { removeAt(idx); };
            wrap.appendChild(img); wrap.appendChild(del); col.appendChild(wrap); preview.appendChild(col);
          });
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

        // When user selects files (first time or after clicking Tambah Foto)
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

        // Submit guard
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
=======
        form.addEventListener('submit', function(ev){
          const input = form.querySelector('input[name="images[]"]');
          if(!input || !input.files) return;
          const files = Array.from(input.files);
          if(files.length > 50){
            ev.preventDefault();
            alert('Maksimal 50 foto sekaligus.');
            return;
          }
          const total = files.reduce((s,f)=>s+f.size,0);
          const maxTotal = 200 * 1024 * 1024; // 200MB total
          if(total > maxTotal){
            ev.preventDefault();
            const mb = (total/1024/1024).toFixed(1);
            alert('Total ukuran file '+mb+'MB melebihi batas 200MB. Kurangi jumlah atau kompres foto.');
            return;
          }
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b
        });
      })();
    </script>
    @endpush
</div>
@endsection
