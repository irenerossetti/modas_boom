<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class QRController extends Controller
{
    public function showUploadForm()
    {
        return view('admin.upload-qr');
    }
    
    public function uploadQR(Request $request)
    {
        $request->validate([
            'qr_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);
        
        try {
            if ($request->hasFile('qr_image')) {
                $file = $request->file('qr_image');
                
                // Eliminar QR anterior si existe (PNG o SVG)
                $oldQrPng = public_path('images/qr/banco-ganadero-qr-real.png');
                $oldQrSvg = public_path('images/qr/banco-ganadero-qr-real.svg');
                if (file_exists($oldQrPng)) {
                    unlink($oldQrPng);
                }
                if (file_exists($oldQrSvg)) {
                    unlink($oldQrSvg);
                }
                
                // Guardar la nueva imagen como PNG
                $destinationPath = public_path('images/qr/banco-ganadero-qr-real.png');
                
                // Si es PNG, copiar directamente
                if ($file->getClientOriginalExtension() === 'png') {
                    $file->move(public_path('images/qr'), 'banco-ganadero-qr-real.png');
                } else {
                    // Convertir a PNG si es otro formato
                    $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
                    imagepng($image, $destinationPath);
                    imagedestroy($image);
                }
                
                return redirect()->back()->with('success', '¡QR de Banco Ganadero actualizado exitosamente!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al subir el QR: ' . $e->getMessage());
        }
        
        return redirect()->back()->with('error', 'No se pudo subir el archivo.');
    }
}