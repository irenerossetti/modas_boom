@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6 text-center">Subir QR de Banco Ganadero</h2>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <form action="{{ route('admin.upload-qr') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">
                    Imagen del QR de Banco Ganadero
                </label>
                <input type="file" name="qr_image" accept="image/*" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, GIF. Máximo 5MB.</p>
                <p class="text-xs text-blue-600 mt-1">
                    <strong>Importante:</strong> Después de subir, recarga la página de la pasarela de pagos para ver el cambio.
                </p>
            </div>
            
            <button type="submit" 
                    class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                Subir QR
            </button>
        </form>
        
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-800">
                <strong>Instrucciones:</strong><br>
                1. Selecciona tu imagen del QR<br>
                2. Haz clic en "Subir QR"<br>
                3. Ve a la pasarela de pagos y recarga la página<br>
                4. Selecciona "QR Personalizado" para ver tu imagen
            </p>
        </div>
        
        @if(file_exists(public_path('images/qr/banco-ganadero-qr-real.png')) || file_exists(public_path('images/qr/banco-ganadero-qr-real.svg')))
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-2">QR Actual:</h3>
                @if(file_exists(public_path('images/qr/banco-ganadero-qr-real.png')))
                    <img src="{{ asset('images/qr/banco-ganadero-qr-real.png') }}" 
                         alt="QR Actual" 
                         class="w-full rounded-lg border">
                @else
                    <img src="{{ asset('images/qr/banco-ganadero-qr-real.svg') }}" 
                         alt="QR Actual" 
                         class="w-full rounded-lg border">
                @endif
            </div>
        @endif
    </div>
</div>
@endsection