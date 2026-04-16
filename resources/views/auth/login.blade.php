@extends('layouts.auth')

@section('title', 'Login - Bus Ticketing')

@section('auth-title', 'Login ke Sistem')
@section('auth-subtitle', 'Masukkan kredensial Anda untuk mengakses sistem')

@section('auth-content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="fas fa-envelope text-secondary"></i>
                </span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                    value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="fas fa-lock text-secondary"></i>
                </span>
                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                    name="password" placeholder="Masukkan password" required>
                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember & Forgot Password --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <a href="{{ route('password.request') }}" class="text-decoration-none small">Lupa password?</a>
        </div>

        {{-- Google Login --}}
        {{-- <div class="d-grid mb-3">
            <a href="{{ route('google.login') }}" class="btn btn-outline-danger btn-lg">
                <i class="fab fa-google me-2"></i>Login dengan Google
            </a>
        </div> --}}

        {{-- Divider --}}
        {{-- <div class="text-center mb-3">
            <span class="text-muted">atau</span>
        </div> --}}

        {{-- Submit --}}
        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </div>
    </form>

    {{-- Register Link --}}
    {{-- <div class="text-center mt-4">
        <p class="text-muted mb-0">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Daftar sekarang</a>
        </p>
    </div> --}}
@endsection

@section('auth-footer')
    &copy; {{ date('Y') }} Bus Ticketing System. All rights reserved.
@endsection

@push('scripts')
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
@endpush
