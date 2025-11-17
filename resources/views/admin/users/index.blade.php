@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: white !important;">
    <div class="card-body py-4 px-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-center flex-grow-1">
          <h2 class="mb-2" style="font-weight: 700; color: #3b6ea5;">Manajemen Admin</h2>
          <p class="mb-0" style="color: #6c757d;">Kelola akun petugas administrator sistem</p>
        </div>
        <div>
          <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="ri-add-circle-line me-1"></i> Tambah Petugas
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

@if (session('status'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

@if (session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

<!-- Card Daftar Petugas -->
<div class="card border-0 shadow-sm rounded-4">
  <div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="card-title mb-0 fw-bold" style="color: #3b6ea5;">Daftar Petugas</h5>
      <span class="text-muted small">{{ $users->count() }} petugas terdaftar</span>
    </div>
    
    @if($users->count() > 0)
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th style="width: 50px">No</th>
              <th>Username</th>
              <th>Role</th>
              <th>Terdaftar</th>
              <th class="text-end" style="width: 200px">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($users as $i => $user)
            <tr>
              <td class="text-muted">{{ $i + 1 }}</td>
              <td>
                <div class="d-flex align-items-center">
                  <div class="bg-primary bg-opacity-10 text-primary p-2 rounded-circle me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="ri-user-line fs-5"></i>
                  </div>
                  <div>
                    <h6 class="mb-0">{{ $user->username }}</h6>
                    @if($user->id === auth('petugas')->id())
                      <small class="text-primary">(Anda)</small>
                    @endif
                  </div>
                </div>
              </td>
              <td>
                @if($user->role === 'admin')
                  <span class="badge bg-success">
                    <i class="ri-shield-check-line me-1"></i> Admin
                  </span>
                @else
                  <span class="badge bg-secondary">
                    <i class="ri-user-line me-1"></i> Guest
                  </span>
                @endif
              </td>
              <td>
                <small class="text-muted">{{ $user->created_at->format('d M Y') }}</small>
              </td>
              <td class="text-end">
                <div class="btn-group" role="group">
                  <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="ri-eye-line"></i> Detail
                  </a>
                  <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-warning">
                    <i class="ri-edit-line"></i> Edit
                  </a>
                  @if($user->id !== auth('petugas')->id())
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus petugas ini?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="ri-delete-bin-line"></i> Hapus
                      </button>
                    </form>
                  @endif
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
          <i class="ri-user-line text-muted" style="font-size: 4rem;"></i>
        </div>
        <h5 class="text-muted">Belum ada petugas</h5>
        <p class="text-muted">Mulai dengan menambahkan petugas baru</p>
      </div>
    @endif
  </div>
</div>

</div>
@endsection
