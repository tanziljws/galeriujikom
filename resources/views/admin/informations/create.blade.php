@extends('layouts.admin')
@section('content')
<section class="section-fullscreen mb-4 section-alt py-3">
  <div class="container section-soft accented decor-gradient-top">
    <div class="text-center py-1">
      <h2 class="vm-title-center mb-1">Tambah Informasi</h2>
      <div class="vm-subtitle">Buat pengumuman/berita baru</div>
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
  <form action="{{ route('admin.informations.store') }}" method="POST" enctype="multipart/form-data" id="info-form">
    @csrf
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Judul</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Kategori</label>
        <input type="text" name="category" class="form-control" placeholder="opsional">
      </div>
      <div class="col-md-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="date" class="form-control">
      </div>
      <div class="col-12">
        <label class="form-label">Deskripsi Singkat</label>
        <input type="text" name="description" class="form-control" maxlength="500" required>
      </div>
      <div class="col-12">
        <label class="form-label">Konten</label>
        <textarea name="content" rows="6" class="form-control" placeholder="opsional"></textarea>
      </div>
      <div class="col-12">
        <label class="form-label">Gambar</label>
        <input type="file" name="image_file" class="form-control" accept="image/*">
      </div>
    </div>
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Simpan</button>
      <a href="{{ route('admin.informations.index') }}" class="btn btn-light">Batal</a>
    </div>
  </form>
</div>
@endsection
