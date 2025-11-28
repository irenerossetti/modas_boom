<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControlNotificacionesController extends Controller
{
    public function index(Request $request)
    {
        // Use server-side proxy endpoints to avoid exposing external tokens in the browser
        // We expose the proxy base path (admin/notificaciones) to the UI so the client can call local endpoints.
        $urlbase = url('/admin/notificaciones');
        // Pass the external socket URL to the view so the client can connect directly to the Node service
        $socketUrl = env('NOTIFICATIONS_SOCKET_URL', env('NOTIFICATIONS_URL_BASE', 'http://localhost:3000'));
        return view('control_notificaciones.index', compact('urlbase', 'socketUrl'));
    }
}
