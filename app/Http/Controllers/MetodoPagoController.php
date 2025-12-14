<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MetodoPagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $metodos = MetodoPago::orderBy('orden')->get();
        return view('metodos-pago.index', compact('metodos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('metodos-pago.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:manual,automatico,qr',
            'descripcion' => 'nullable|string',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'activo' => 'boolean',
            'orden' => 'integer|min:0'
        ]);

        $data = $request->all();
        
        // Asignar icono y color automáticamente basado en el tipo
        $iconosColores = $this->getIconoColorPorTipo($request->tipo);
        $data['icono'] = $iconosColores['icono'];
        $data['color'] = $iconosColores['color'];
        
        // Manejar subida de imagen QR
        if ($request->hasFile('qr_image')) {
            $file = $request->file('qr_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/qr'), $filename);
            $data['qr_image'] = 'images/qr/' . $filename;
        }

        MetodoPago::create($data);

        return redirect()->route('metodos-pago.index')
                        ->with('success', 'Método de pago creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MetodoPago $metodos_pago)
    {
        return view('metodos-pago.show', ['metodoPago' => $metodos_pago]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MetodoPago $metodos_pago)
    {
        return view('metodos-pago.edit', ['metodoPago' => $metodos_pago]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MetodoPago $metodos_pago)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:manual,automatico,qr',
            'descripcion' => 'nullable|string',
            'qr_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'activo' => 'boolean',
            'orden' => 'integer|min:0'
        ]);

        $data = $request->only(['nombre', 'tipo', 'descripcion', 'activo', 'orden']);
        
        // Manejar subida de imagen QR
        if ($request->hasFile('qr_image')) {
            // Eliminar imagen anterior si existe
            if ($metodos_pago->qr_image && file_exists(public_path($metodos_pago->qr_image))) {
                unlink(public_path($metodos_pago->qr_image));
            }
            
            $file = $request->file('qr_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images/qr'), $filename);
            $data['qr_image'] = 'images/qr/' . $filename;
        }

        $metodos_pago->update($data);

        return redirect()->route('metodos-pago.index')
                        ->with('success', 'Método de pago actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MetodoPago $metodos_pago)
    {
        // Eliminar imagen QR si existe
        if ($metodos_pago->qr_image && file_exists(public_path($metodos_pago->qr_image))) {
            unlink(public_path($metodos_pago->qr_image));
        }

        $metodos_pago->delete();

        return redirect()->route('metodos-pago.index')
                        ->with('success', 'Método de pago eliminado exitosamente.');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(MetodoPago $metodos_pago)
    {
        $metodos_pago->update(['activo' => !$metodos_pago->activo]);
        
        $status = $metodos_pago->activo ? 'activado' : 'desactivado';
        return redirect()->back()->with('success', "Método de pago {$status} exitosamente.");
    }

    /**
     * Obtener icono y color automáticamente basado en el tipo
     */
    private function getIconoColorPorTipo($tipo)
    {
        $configuraciones = [
            'manual' => [
                'icono' => 'fas fa-money-bill-wave',
                'color' => '#10B981'
            ],
            'automatico' => [
                'icono' => 'fab fa-stripe',
                'color' => '#3B82F6'
            ],
            'qr' => [
                'icono' => 'fas fa-qrcode',
                'color' => '#6366F1'
            ]
        ];

        return $configuraciones[$tipo] ?? [
            'icono' => 'fas fa-credit-card',
            'color' => '#6B7280'
        ];
    }
}
