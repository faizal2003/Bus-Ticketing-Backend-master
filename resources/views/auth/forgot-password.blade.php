@extends('layouts.auth')

@section('title', 'Lupa Password - Bus Ticketing')

@section('auth-title', 'Reset Password')
@section('auth-subtitle', 'Masukkan email untuk mendapatkan link reset password')

@section('auth-content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="mb-4">
            <p class="text-muted">Kami akan mengirim link reset password ke email Anda.</p>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-envelope"></i>
                </span>
                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                    name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-auth btn-lg">
                <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset
            </button>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="auth-link">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Login
            </a>
        </div>
    </form>
@endsection

@section('auth-footer')
    &copy; {{ date('Y') }} Bus Ticketing System. All rights reserved.
@endsection
