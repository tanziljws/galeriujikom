@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: white !important;">
    <div class="card-body py-4 px-4">
      <div class="d-flex justify-content-between align-items-center">
        <div class="text-center flex-grow-1">
          <h2 class="mb-2" style="font-weight: 700; color: #3b6ea5;">Laporan Pengguna</h2>
          <p class="mb-0" style="color: #6c757d;">Daftar lengkap pengguna yang sudah mendaftar</p>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
          </a>
          <a href="{{ route('admin.reports.users.pdf') }}" class="btn btn-danger" target="_blank">
            <i class="ri-file-pdf-line me-1"></i> Export PDF
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Users Table -->
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
      @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('status') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif
      <div class="table-responsive">
        <table class="table table-hover" id="usersTable">
          <thead class="table-light">
            <tr>
              <th width="50">#</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Tanggal Daftar</th>
              <th>Terakhir Login</th>
              <th>Status</th>
              <th width="140">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $index => $user)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-2">
                      <i class="ri-user-line"></i>
                    </div>
                    <strong>{{ $user->name }}</strong>
                  </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->created_at->format('d M Y, H:i') }}</td>
                <td>
                  @if($user->updated_at->diffInDays($user->created_at) > 0)
                    {{ $user->updated_at->format('d M Y, H:i') }}
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
                <td>
                  @if($user->created_at->diffInDays(now()) < 7)
                    <span class="badge bg-success">Baru</span>
                  @else
                    <span class="badge bg-secondary">Aktif</span>
                  @endif
                </td>
                <td>
                  <div class="d-flex gap-2">
                    <a href="{{ route('admin.reports.users.edit', $user->id) }}" class="btn btn-sm btn-outline-warning">
                      <i class="ri-edit-line"></i>
                    </a>
                    <form action="{{ route('admin.reports.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Belum ada pengguna terdaftar</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
// Simple search functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.createElement('input');
  searchInput.type = 'text';
  searchInput.className = 'form-control mb-3';
  searchInput.placeholder = 'Cari nama atau email...';
  
  const table = document.getElementById('usersTable');
  if (table) {
    table.parentElement.insertBefore(searchInput, table);
    
    searchInput.addEventListener('keyup', function() {
      const filter = this.value.toLowerCase();
      const rows = table.querySelectorAll('tbody tr');
      
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });
  }
});
</script>
@endpush
@endsection
