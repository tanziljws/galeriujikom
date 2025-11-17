@extends('layouts.app')
@section('content')
<section class="section-fullscreen section-alt py-5">
    <div class="container">
        <div class="text-center mb-4">
            <div class="hero-icon mb-3">
                <i class="fas fa-images"></i>
            </div>
            <h1 class="vm-title-center text-white">{{ $albumTitle }}</h1>
            <p class="vm-subtitle mb-0">
                <span class="badge bg-primary me-2">{{ $category }}</span>
                <span class="text-white">{{ count($photos) }} Foto</span>
            </p>
            <a href="{{ route('gallery') }}" class="btn btn-outline-primary mt-3">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Galeri
            </a>
        </div>

        <!-- Photo Grid -->
        <div class="row g-4">
            @foreach($photos as $photo)
                @php
                    $photo = (array)$photo;
                    $photoId = $photo['id'] ?? md5($photo['filename'] ?? uniqid());
                    $photoUrl = $photo['url'] ?? asset('uploads/gallery/' . ($photo['filename'] ?? ''));
                    $photoTitle = $photo['title'] ?? $albumTitle;
                    $photoCaption = $photo['caption'] ?? '';
                    $uploadedAt = isset($photo['uploaded_at']) ? \Carbon\Carbon::parse($photo['uploaded_at'])->format('d F Y') : '';
                @endphp
                
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="album-photo-item" 
                         data-id="{{ $photoId }}"
                         data-url="{{ $photoUrl }}"
                         data-title="{{ $photoTitle }}"
                         data-caption="{{ $photoCaption }}"
                         data-date="{{ $uploadedAt }}"
                         data-filename="{{ $photo['filename'] ?? '' }}">
                        <div class="ratio ratio-1x1">
                            <img src="{{ $photoUrl }}" alt="{{ $photoTitle }}" class="img-fluid" loading="lazy">
                        </div>
                        <div class="photo-overlay">
                            <i class="fas fa-search-plus"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(empty($photos))
            <div class="alert alert-info text-center mt-4">
                Tidak ada foto dalam album ini.
            </div>
        @endif
    </div>
</section>

<!-- Photo Detail Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="background: transparent; border: none;">
            <div class="modal-body p-0">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 bg-white rounded-circle p-2" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1050;"></button>
                
                <div class="text-center">
                    <!-- Photo -->
                    <div class="modal-image-container mb-3">
                        <img id="modalImage" src="" alt="" class="img-fluid rounded shadow-lg" style="max-height: 80vh; width: auto;">
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-success like-btn" data-action="like">
                            <i class="fas fa-thumbs-up me-1"></i>
                            <span class="like-count">0</span>
                        </button>
                        <button class="btn btn-danger dislike-btn" data-action="dislike">
                            <i class="fas fa-thumbs-down me-1"></i>
                            <span class="dislike-count">0</span>
                        </button>
                        <a href="#" id="modalDownloadBtn" class="btn btn-primary" onclick="handleDownload(event)">
                            <i class="fas fa-download me-1"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Login Warning -->
<div class="modal fade" id="loginWarningModal" tabindex="-1" aria-labelledby="loginWarningLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content border-0 shadow-lg" style="background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 18px; overflow: hidden;">
            <!-- Header dengan gradient transparan -->
            <div class="modal-header border-0" style="background: rgba(255, 165, 0, 0.2); backdrop-filter: blur(5px); padding: 1.25rem 1.5rem 1rem; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                <h5 class="modal-title text-white fw-bold w-100 text-center mb-0" id="loginWarningLabel" style="font-size: 1.1rem;">
                    <i class="fas fa-exclamation-triangle me-2"></i>Login Diperlukan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Body -->
            <div class="modal-body text-center py-4 px-4">
                <!-- Icon -->
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center" 
                         style="width: 70px; height: 70px; background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%); border-radius: 50%; box-shadow: 0 6px 18px rgba(255, 165, 0, 0.3);">
                        <i class="fas fa-lock fa-xl text-white"></i>
                    </div>
                </div>
                
                <!-- Title -->
                <h5 class="mb-2 fw-bold text-white" style="font-size: 1.15rem;">Silakan Login Terlebih Dahulu</h5>
                
                <!-- Description -->
                <p class="text-white-50 mb-3" style="font-size: 0.9rem; line-height: 1.5;">
                    Anda harus login untuk menggunakan fitur ini.<br>
                    Belum punya akun? Daftar sekarang, gratis!
                </p>
                
                <!-- Buttons -->
                <div class="d-grid gap-2">
                    <a href="{{ route('login') }}" class="btn text-white fw-semibold" 
                       style="background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%); border: none; border-radius: 10px; padding: 0.65rem; box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);">
                        <i class="fas fa-sign-in-alt me-2"></i>Login Sekarang
                    </a>
                    <a href="{{ route('register') }}" class="btn fw-semibold text-white" 
                       style="background: rgba(255, 255, 255, 0.1); border: 2px solid rgba(255, 255, 255, 0.3); border-radius: 10px; padding: 0.65rem;">
                        <i class="fas fa-user-plus me-2"></i>Daftar Akun Baru
                    </a>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="modal-footer border-0 justify-content-center" style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(5px); padding: 0.75rem; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <small class="text-white-50" style="font-size: 0.8rem;">
                    <i class="fas fa-shield-alt me-1"></i>Akun Anda aman dan terlindungi
                </small>
            </div>
        </div>
    </div>
</div>

<style>
    .hero-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .hero-icon i {
        font-size: 2rem;
        color: white;
    }

    .album-photo-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .album-photo-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .album-photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .album-photo-item:hover img {
        transform: scale(1.1);
    }

    .photo-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .album-photo-item:hover .photo-overlay {
        opacity: 1;
    }

    .photo-overlay i {
        color: white;
        font-size: 2rem;
    }

    .modal-image-container {
        display: flex;
        align-items: center;
        justify-content: center;
        max-height: 70vh;
        overflow: hidden;
        background: transparent;
        border-radius: 8px;
    }
    
    .modal-image-container img {
        max-width: 100%;
        max-height: 70vh;
        width: auto;
        height: auto;
        object-fit: contain;
    }
    
    .modal-content {
        background: rgba(255, 255, 255, 0.98);
    }
    
    /* Like/Dislike Buttons */
    .like-btn.active {
        background-color: #198754 !important;
        color: white !important;
        border-color: #198754 !important;
    }
    
    .dislike-btn.active {
        background-color: #dc3545 !important;
        color: white !important;
        border-color: #dc3545 !important;
    }
    
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const photoModal = new bootstrap.Modal(document.getElementById('photoModal'));
        const loginWarningModal = new bootstrap.Modal(document.getElementById('loginWarningModal'));
        const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        const userName = '{{ auth()->check() ? auth()->user()->name : "" }}';
        let currentPhotoFilename = '';
        let currentPhotoId = '';
        
        // Handle photo click
        document.querySelectorAll('.album-photo-item').forEach(item => {
            item.addEventListener('click', function() {
                const photoId = this.getAttribute('data-id');
                const photoUrl = this.getAttribute('data-url');
                const photoTitle = this.getAttribute('data-title');
                const photoCaption = this.getAttribute('data-caption');
                const photoDate = this.getAttribute('data-date');
                const photoFilename = this.getAttribute('data-filename');
                
                currentPhotoFilename = photoFilename;
                currentPhotoId = photoId;
                
                // Update modal content
                document.getElementById('modalImage').src = photoUrl;
                
                // Store photo data for download
                document.getElementById('modalDownloadBtn').dataset.photoUrl = photoUrl;
                document.getElementById('modalDownloadBtn').dataset.photoId = photoId;
                document.getElementById('modalDownloadBtn').dataset.photoFilename = photoFilename;
                
                // Load stats (likes, dislikes, comments) via photo_id
                loadPhotoStatsById(photoId);
                
                // Show modal
                photoModal.show();
            });
        });
        
        // Load photo stats (likes/dislikes only; komentar dinonaktifkan)
        function loadPhotoStatsById(photoId) {
            fetch(`/gallery/photo-stats/${encodeURIComponent(photoId)}`)
                .then(response => response.json())
                .then(data => {
                    // Update like/dislike counts
                    document.querySelector('.like-count').textContent = (data.likes ?? 0);
                    document.querySelector('.dislike-count').textContent = (data.dislikes ?? 0);
                })
                .catch(error => console.error('Error loading stats:', error));
        }
        
        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Handle like/dislike buttons
        document.querySelectorAll('.like-btn, .dislike-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!isAuthenticated) {
                    photoModal.hide();
                    setTimeout(() => loginWarningModal.show(), 300);
                    return;
                }
                
                const action = this.getAttribute('data-action');
                handleReaction(action);
            });
        });
        
        // Handle reaction
        function handleReaction(type) {
            fetch('/gallery/react', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    photo_id: currentPhotoId,
                    reaction: type
                })
            })
            .then(response => response.json())
            .then(data => {
                // Update counts
                const stats = data.stats || data; // support both shapes
                document.querySelector('.like-count').textContent = (stats.likes ?? 0);
                document.querySelector('.dislike-count').textContent = (stats.dislikes ?? 0);
                
                // Update button states
                document.querySelector('.like-btn').classList.remove('active');
                document.querySelector('.dislike-btn').classList.remove('active');
                
                if ((data.your_reaction || (data.user_reaction)) === 'like') {
                    document.querySelector('.like-btn').classList.add('active');
                } else if ((data.your_reaction || (data.user_reaction)) === 'dislike') {
                    document.querySelector('.dislike-btn').classList.add('active');
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Komentar dinonaktifkan: tidak ada handler form komentar
        
    });
    
    // Global function for download handler
    function handleDownload(event) {
        event.preventDefault();
        
        const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        const photoModal = bootstrap.Modal.getInstance(document.getElementById('photoModal'));
        const loginWarningModal = new bootstrap.Modal(document.getElementById('loginWarningModal'));
        
        if (!isAuthenticated) {
            if (photoModal) photoModal.hide();
            setTimeout(() => {
                loginWarningModal.show();
            }, 300);
            return;
        }
        
        // Get photo data from button
        const btn = document.getElementById('modalDownloadBtn');
        const photoFilename = btn.dataset.photoFilename;
        const photoId = btn.dataset.photoId;
        
        console.log('Download clicked:', photoFilename, photoId);
        
        if (!photoId) {
            alert('Data foto tidak lengkap');
            return;
        }
        if (!photoFilename) {
            alert('Foto ini sumbernya eksternal sehingga tidak dapat diunduh.');
            return;
        }
        
        // Build download URL
        const downloadUrl = `/gallery/download?photo_id=${encodeURIComponent(photoId)}&filename=${encodeURIComponent(photoFilename)}`;
        
        console.log('Download URL:', downloadUrl);
        
        // Use fetch to download properly
        fetch(downloadUrl)
            .then(response => {
                if (!response.ok) throw new Error('Download failed');
                return response.blob();
            })
            .then(blob => {
                // Create blob URL
                const url = window.URL.createObjectURL(blob);
                
                // Create download link
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                
                // Clean filename - remove prefix
                let cleanFilename = photoFilename.replace(/^img_[a-f0-9]+_/i, '');
                a.download = cleanFilename;
                
                document.body.appendChild(a);
                a.click();
                
                // Cleanup
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                console.log('Download completed:', cleanFilename);
            })
            .catch(error => {
                console.error('Download error:', error);
                alert('Gagal mendownload foto. Silakan coba lagi.');
            });
    }
</script>
@endsection
