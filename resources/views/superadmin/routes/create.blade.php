@extends('layouts.superadmin')

@section('title', 'Tambah Rute')

@section('content')
<div class="container mx-auto px-4">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-800">Tambah Rute Baru</h1>
        <a href="{{ route('superadmin.routes.index') }}" class="text-gray-600 hover:text-gray-900">Kembali</a>
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <form action="{{ route('superadmin.routes.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="origin_city">
                    Kota Asal
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('origin_city') border-red-500 @enderror" 
                    id="origin_city" name="origin_city" type="text" placeholder="Misal: Surabaya" value="{{ old('origin_city') }}" required>
                @error('origin_city')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="destination_city">
                    Kota Tujuan
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('destination_city') border-red-500 @enderror" 
                    id="destination_city" name="destination_city" type="text" placeholder="Misal: Jakarta" value="{{ old('destination_city') }}" required>
                @error('destination_city')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="status">
                    Status
                </label>
                <div class="relative">
                    <select class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline @error('status') border-red-500 @enderror" id="status" name="status">
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                @error('status')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Simpan Rute
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
