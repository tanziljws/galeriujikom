@extends('layouts.admin')
@section('content')
<section class="section-fullscreen mb-4 section-alt py-3">
  <div class="container section-soft accented decor-gradient-top">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="text-center w-100">
        <h2 class="vm-title-center mb-1">Kelola Informasi</h2>
        <div class="vm-subtitle">Berita dan pengumuman sekolah</div>
      </div>
      <div class="ms-3 d-none d-md-block">
        <a href="{{ route('admin.informations.create') }}" class="btn btn-primary">+ Tambah Informasi</a>
      </div>
    </div>
  </div>
</section>
<div class="card-elevated p-3">
  @if(session('status'))
    <div class="alert alert-success mb-3">{{ session('status') }}</div>
  @endif
  <div class="row g-3">
    @forelse($items as $it)
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card h-100 shadow-animate" style="overflow:hidden">
          @php
            $img = $it->image_path ? (str_starts_with($it->image_path,'http') ? $it->image_path : asset('uploads/informations/'.$it->image_path)) : null;
          @endphp
          @if($img)
            <div class="ratio ratio-16x9"><img src="{{ $img }}" alt="thumb" style="object-fit:cover;width:100%;height:100%"></div>
          @endif
          <div class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <h6 class="mb-0 text-truncate" title="{{ $it->title }}">{{ $it->title }}</h6>
              <span class="badge bg-primary-subtle text-primary">{{ $it->category ?? 'Lainnya' }}</span>
            </div>
            <div class="text-muted small mb-2">{{ $it->date }}</div>
            <p class="text-secondary small mb-2 text-truncate" style="max-height: 2.4em; overflow: hidden;">{{ $it->description ?? '' }}</p>
            <div class="d-flex gap-1 mt-2 flex-wrap">
              <a href="{{ route('admin.informations.show', $it->id) }}" class="btn btn-sm btn-outline-primary" style="flex: 1 1 auto;"><i class="ri-eye-line me-1"></i> Detail</a>
              <a href="{{ route('admin.informations.edit', $it->id) }}" class="btn btn-sm btn-outline-warning" style="flex: 1 1 auto;"><i class="ri-edit-line me-1"></i> Edit</a>
              <form action="{{ route('admin.informations.destroy', $it->id) }}" method="POST" class="m-0 p-0" style="flex: 1 1 auto;" onsubmit="return confirm('Hapus informasi ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger w-100"><i class="ri-delete-bin-line me-1"></i> Hapus</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12 text-center text-muted py-4">Belum ada informasi.</div>
    @endforelse
  </div>
</div>
<div class="mt-3 d-md-none">
  <a href="{{ route('admin.informations.create') }}" class="btn btn-primary w-100">+ Tambah Informasi</a>
</div>
@endsection
