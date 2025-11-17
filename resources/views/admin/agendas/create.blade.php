@extends('layouts.admin')
@section('content')
<<<<<<< HEAD
<section class="section-fullscreen mb-4 section-alt py-3">
  <div class="container section-soft accented decor-gradient-top">
    <div class="text-center py-1">
      <h2 class="vm-title-center mb-1">Tambah Agenda</h2>
      <div class="vm-subtitle">Buat jadwal kegiatan baru</div>
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
  <form action="{{ route('admin.agendas.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama Kegiatan</label>
        <input type="text" name="title" class="form-control" placeholder="Nama kegiatan" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="date" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Tempat</label>
        <input type="text" name="place" class="form-control" placeholder="Lokasi">
      </div>
      <div class="col-12">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="5" placeholder="Deskripsi kegiatan..."></textarea>
      </div>
    </div>
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary">Simpan</button>
      <a href="{{ route('admin.agendas.index') }}" class="btn btn-light">Batal</a>
    </div>
  </form>
=======
<h4 class="mb-3">Tambah Agenda</h4>
<div class="card-elevated p-3">
    <form action="{{ route('admin.agendas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Kegiatan</label>
                <input type="text" name="title" class="form-control" placeholder="Nama kegiatan" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tempat</label>
                <input type="text" name="place" class="form-control" placeholder="Lokasi">
            </div>
            <div class="col-12">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="5" placeholder="Deskripsi kegiatan..."></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">Poster (opsional)</label>
                <input type="file" name="poster" class="form-control">
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.agendas.index') }}" class="btn btn-light">Batal</a>
        </div>
    </form>
>>>>>>> 5a40c5ea8397b32a372b6c524bd6421ff676df4b
</div>
@endsection

