@extends('layouts.app')

@section('content')
<section class="section-fullscreen section-alt py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <!-- Glass Card -->
                <div class="glass-card shadow-lg border-0">
                    <div class="card-body p-3 p-md-4">
                        <div class="text-center mb-3">
                            <div class="login-icon mb-2">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h2 class="h5 mb-1 text-dark">Login</h2>
                            <p class="text-muted small mb-0">Masuk untuk mengakses fitur galeri</p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label text-dark">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control glass-input @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="nama@email.com" required autofocus>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label text-dark">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control glass-input @error('password') is-invalid @enderror" 
                                           id="password" name="password" 
                                           placeholder="Masukkan password" required>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label text-dark" for="remember">
                                    Ingat saya
                                </label>
                            </div>

                            <!-- CAPTCHA -->
                            <div class="mb-3">
                                <label for="captcha" class="form-label text-dark">Verifikasi (Anti-Robot)</label>
                                <div class="captcha-box mb-2 p-3 border rounded text-center">
                                    <span class="captcha-question fs-5 fw-bold text-dark" id="captchaQuestion"></span>
                                </div>
                                <input type="text" class="form-control glass-input @error('captcha') is-invalid @enderror" 
                                       id="captcha" name="captcha" 
                                       placeholder="Ketik jawaban Anda" required autocomplete="off">
                                <input type="hidden" id="captcha_answer" name="captcha_answer">
                                @error('captcha')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>

                            <div class="text-center">
                                <p class="text-muted mb-0">
                                    Belum punya akun? 
                                    <a href="{{ route('register') }}" class="text-primary fw-bold">Daftar di sini</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="{{ route('gallery') }}" class="text-white">
                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Galeri
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Solid Card Effect */
.glass-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

/* Login Icon */
.login-icon {
    font-size: 3rem;
    color: #1A56DB;
    text-shadow: none;
}

/* Solid Input */
.glass-input {
    background: #ffffff !important;
    border: 1px solid #dee2e6 !important;
    border-left: 0 !important;
    color: #212529 !important;
}

.glass-input::placeholder {
    color: #6c757d;
}

.glass-input:focus {
    background: #ffffff !important;
    border-color: #1A56DB !important;
    box-shadow: 0 0 0 0.2rem rgba(26, 86, 219, 0.25) !important;
    color: #212529 !important;
}

/* Input Group */
.input-group-text {
    border-right: 0;
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
    color: #495057 !important;
}

/* CAPTCHA Box */
.captcha-box {
    background: #f8f9fa;
    backdrop-filter: none;
    border: 2px dashed #1A56DB !important;
}

/* Button */
.btn-primary {
    background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
    border: none;
    box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(74, 144, 226, 0.4);
}

/* Alert */
.alert {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    backdrop-filter: blur(5px);
}

.alert-danger {
    background: rgba(220, 53, 69, 0.2);
    border-color: rgba(220, 53, 69, 0.3);
}

.alert-success {
    background: rgba(25, 135, 84, 0.2);
    border-color: rgba(25, 135, 84, 0.3);
}
</style>

<script>
// Generate captcha once on page load
document.addEventListener('DOMContentLoaded', function() {
    const num1 = Math.floor(Math.random() * 10) + 1;
    const num2 = Math.floor(Math.random() * 10) + 1;
    const operators = ['+', '-', '×'];
    const operator = operators[Math.floor(Math.random() * operators.length)];
    
    let answer;
    let question;
    
    if (operator === '+') {
        answer = num1 + num2;
        question = `${num1} + ${num2} = ?`;
    } else if (operator === '-') {
        // Pastikan hasil tidak negatif
        const larger = Math.max(num1, num2);
        const smaller = Math.min(num1, num2);
        answer = larger - smaller;
        question = `${larger} - ${smaller} = ?`;
    } else {
        answer = num1 * num2;
        question = `${num1} × ${num2} = ?`;
    }
    
    document.getElementById('captchaQuestion').textContent = question;
    document.getElementById('captcha_answer').value = answer;
});
</script>
@endsection
