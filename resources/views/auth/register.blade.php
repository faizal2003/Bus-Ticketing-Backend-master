@extends('layouts.auth')

@section('title', 'Registrasi - Bus Ticketing')

@section('auth-title', 'Buat Akun Baru')
@section('auth-subtitle', 'Daftar untuk mulai memesan tiket bus')

@section('auth-content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="name" class="form-label">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name') }}" placeholder="Nama lengkap" required autofocus>
                </div>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12 mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone"
                        name="phone" value="{{ old('phone') }}" placeholder="08123456789" required>
                </div>
                @error('phone')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        name="password" placeholder="Minimal 8 karakter" required>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                        placeholder="Ulangi password" required>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                <label class="form-check-label" for="terms">
                    Saya menyetujui <a href="#" class="auth-link">Syarat & Ketentuan</a> dan <a href="#"
                        class="auth-link">Kebijakan Privasi</a>
                </label>
            </div>
        </div>

        {{-- Google Register --}}
        <div class="d-grid gap-2 mb-3">
            <a href="{{ route('google.login') }}" class="btn btn-outline-danger btn-lg">
                <i class="fab fa-google me-2"></i>Daftar dengan Google
            </a>
        </div>

        {{-- Divider --}}
        <div class="text-center mb-3">
            <span class="text-muted">atau</span>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-auth btn-lg">
                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
            </button>
        </div>

        <div class="text-center mt-4">
            <p class="mb-0">Sudah punya akun?</p>
            <a href="{{ route('login') }}" class="auth-link">Login disini</a>
        </div>
    </form>
@endsection

@section('auth-footer')
    &copy; {{ date('Y') }} Bus Ticketing System. All rights reserved.
@endsection

@push('scripts')
    <script>
        // Password strength checker
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');

        function checkPasswordMatch() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Password tidak sama');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }

        passwordInput.addEventListener('input', checkPasswordMatch);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    </script>
@endpush
