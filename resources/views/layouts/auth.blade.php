@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="logo-icon">
                        <i class="fas fa-bus"></i>
                    </div>
                    <h2>@yield('auth-title', 'Bus Ticketing System')</h2>
                    <p class="mb-0">@yield('auth-subtitle', 'Sistem Pemesanan Tiket Bus Online')</p>
                </div>

                <div class="auth-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

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

                    @yield('auth-content')
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-white mb-0">
                    @yield('auth-footer')
                </p>
            </div>
        </div>
    </div>
@endsection
