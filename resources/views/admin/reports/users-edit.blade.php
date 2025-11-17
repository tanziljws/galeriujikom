@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
  <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: white !important;">
    <div class="card-body py-4 px-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-center flex-grow-1">
          <h2 class="mb-2" style="font-weight: 700; color: #3b6ea5;">Edit Pengguna</h2>
          <p class="mb-0" style="color: #6c757d;">Ubah data pengguna terdaftar</p>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.reports.users') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      <form method="POST" action="{{ route('admin.reports.users.update', $user->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
          <label for="name" class="form-label">Nama</label>
          <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
        </div>
        <div class="d-flex justify-content-end gap-2">
          <a href="{{ route('admin.reports.users') }}" class="btn btn-light">Batal</a>
          <button type="submit" class="btn btn-primary">
            <i class="ri-save-line me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
