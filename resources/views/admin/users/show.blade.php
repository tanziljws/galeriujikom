@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: white !important;">
    <div class="card-body py-4 px-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-center flex-grow-1">
          <h2 class="mb-2" style="font-weight: 700; color: #3b6ea5;">Detail Petugas</h2>
          <p class="mb-0" style="color: #6c757d;">Informasi lengkap akun petugas administrator</p>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
          </a>
          <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning">
            <i class="ri-edit-line me-1"></i> Edit
          </a>
          @if($user->id !== auth('petugas')->id())
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus petugas ini?')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-danger">
                <i class="ri-delete-bin-line me-1"></i> Hapus
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

<!-- Detail Card -->
<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-4">
    <div class="row">
      <div class="col-md-3 text-center mb-4 mb-md-0">
        <div class="bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3" style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
          <i class="ri-user-line" style="font-size: 4rem;"></i>
        </div>
        @if($user->id === auth('petugas')->id())
          <span class="badge bg-primary">Akun Anda</span>
        @endif
      </div>
      
      <div class="col-md-9">
        <h4 class="mb-4" style="color: #3b6ea5;">Informasi Petugas</h4>
        
        <div class="row mb-3">
          <div class="col-md-4">
            <strong class="text-muted">Username:</strong>
          </div>
          <div class="col-md-8">
            {{ $user->username }}
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-4">
            <strong class="text-muted">Role:</strong>
          </div>
          <div class="col-md-8">
            @if($user->role === 'admin')
              <span class="badge bg-success">
                <i class="ri-shield-check-line me-1"></i> Admin
              </span>
              <small class="text-muted ms-2">Dapat mengelola semua fitur termasuk petugas</small>
            @else
              <span class="badge bg-secondary">
                <i class="ri-user-line me-1"></i> Guest
              </span>
              <small class="text-muted ms-2">Hanya dapat melihat dan mengelola konten</small>
            @endif
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-4">
            <strong class="text-muted">Terdaftar Sejak:</strong>
          </div>
          <div class="col-md-8">
            {{ $user->created_at->format('d F Y, H:i') }} WIB
            <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small>
          </div>
        </div>
        
        <div class="row mb-3">
          <div class="col-md-4">
            <strong class="text-muted">Terakhir Diperbarui:</strong>
          </div>
          <div class="col-md-8">
            {{ $user->updated_at->format('d F Y, H:i') }} WIB
            <small class="text-muted">({{ $user->updated_at->diffForHumans() }})</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Permission Table -->
<div class="card border-0 shadow-sm rounded-4 mt-4">
  <div class="card-body p-4">
    <h5 class="mb-3" style="color: #3b6ea5; font-weight: 600;">Hak Akses</h5>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Fitur</th>
            <th class="text-center">Admin</th>
            <th class="text-center">Guest</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Login</td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
          </tr>
          <tr>
            <td>Logout</td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
          </tr>
          <tr>
            <td>Manajemen Admin</td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
            <td class="text-center"><i class="ri-close-line text-danger fs-5"></i></td>
          </tr>
          <tr>
            <td>Data Foto Galeri</td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
          </tr>
          <tr>
            <td>Kategori Galeri</td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
          </tr>
          <tr>
            <td>Manajemen Page (Halaman)</td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
          </tr>
          <tr>
            <td>Hapus Foto</td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
          </tr>
          <tr>
            <td>Tambah Foto</td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
          </tr>
          <tr>
            <td>View Homepage</td>
            <td class="text-center"><i class="ri-close-line text-danger fs-5"></i></td>
            <td class="text-center"><i class="ri-check-line text-success fs-5"></i></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div>
@endsection
