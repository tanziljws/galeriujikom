@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: white !important;">
    <div class="card-body py-4 px-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-center flex-grow-1">
          <h2 class="mb-2" style="font-weight: 700; color: #3b6ea5;">Edit Petugas</h2>
          <p class="mb-0" style="color: #6c757d;">Ubah informasi akun petugas administrator</p>
        </div>
        <div>
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
          </a>
        </div>
      </div>
    </div>
  </div>

@if ($errors->any())
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<!-- Form Card -->
<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-4">
    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
      @csrf
      @method('PUT')
      
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="username" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
          <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required autofocus>
          @error('username')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="col-md-6 mb-3">
          <label for="role" class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
          <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
            <option value="">Pilih Role</option>
            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="guest" {{ old('role', $user->role) === 'guest' ? 'selected' : '' }}>Guest</option>
          </select>
          <small class="text-muted">Admin dapat mengelola petugas, Guest hanya dapat melihat</small>
          @error('role')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      
      <div class="alert alert-info">
        <i class="ri-information-line me-2"></i>
        <strong>Password:</strong> Kosongkan jika tidak ingin mengubah password
      </div>
      
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="password" class="form-label fw-semibold">Password Baru</label>
          <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
          <small class="text-muted">Minimal 8 karakter</small>
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        
        <div class="col-md-6 mb-3">
          <label for="password_confirmation" class="form-label fw-semibold">Konfirmasi Password Baru</label>
          <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
        </div>
      </div>
      
      <hr class="my-4">
      
      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="ri-save-line me-1"></i> Simpan Perubahan
        </button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-light">
          <i class="ri-close-line me-1"></i> Batal
        </a>
      </div>
    </form>
  </div>
</div>

</div>
@endsection
