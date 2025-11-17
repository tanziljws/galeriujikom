@extends('layouts.admin')
@section('content')
<section class="section-fullscreen mb-4 section-alt py-3">
  <div class="container section-soft accented decor-gradient-top">
    <div class="text-center py-1">
      <h2 class="vm-title-center mb-1">Detail Informasi</h2>
      <div class="vm-subtitle">Informasi lengkap berita/pengumuman</div>
    </div>
  </div>
</section>

<div class="card-elevated p-4">
  <div class="row">
    <div class="col-lg-8 mx-auto">
      <!-- Header -->
      <div class="mb-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h5 class="fw-semibold mb-0" style="font-size: 1.25rem;">{{ $item->title }}</h5>
          <span class="badge bg-primary-subtle text-primary">{{ $item->category ?? 'Lainnya' }}</span>
        </div>
        <div class="text-muted small">
          <i class="ri-calendar-line me-1"></i> {{ $item->date ? \Carbon\Carbon::parse($item->date)->translatedFormat('d F Y') : '-' }}
        </div>
      </div>

      <!-- Image -->
      @php
        $img = $item->image_path ? (str_starts_with($item->image_path,'http') ? $item->image_path : asset('uploads/informations/'.$item->image_path)) : null;
      @endphp
      @if($img)
        <div class="mb-3">
          <img src="{{ $img }}" alt="{{ $item->title }}" class="img-fluid rounded shadow-sm" style="width: 100%; max-height: 200px; object-fit: cover;">
        </div>
      @endif

      <!-- Description -->
      <div class="mb-4">
        <h5 class="fw-semibold mb-2">Deskripsi</h5>
        <p class="text-secondary">{{ $item->description }}</p>
      </div>

      <!-- Content -->
      @if($item->content)
        <div class="mb-4">
          <h5 class="fw-semibold mb-2">Konten Lengkap</h5>
          <div class="text-secondary" style="white-space: pre-wrap;">{{ $item->content }}</div>
        </div>
      @endif

      <!-- Meta Info -->
      <div class="border-top pt-3 mt-4">
        <div class="row text-muted small">
          <div class="col-md-6">
            <i class="ri-user-line me-1"></i> Dibuat oleh: Admin
          </div>
          <div class="col-md-6 text-md-end">
            <i class="ri-time-line me-1"></i> {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="d-flex gap-2 mt-4">
        <a href="{{ route('admin.informations.edit', $item->id) }}" class="btn btn-warning">
          <i class="ri-edit-line me-1"></i> Edit
        </a>
        <form action="{{ route('admin.informations.destroy', $item->id) }}" method="POST" class="m-0" onsubmit="return confirm('Hapus informasi ini?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger">
            <i class="ri-delete-bin-line me-1"></i> Hapus
          </button>
        </form>
        <a href="{{ route('admin.informations.index') }}" class="btn btn-light ms-auto">
          <i class="ri-arrow-left-line me-1"></i> Kembali
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
