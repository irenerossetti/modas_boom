<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Events\NewMessage;
use App\Events\NewChat;
use App\Events\QrUpdated;
use App\Events\SessionDeleted;

class NotificationProxyController extends Controller
{
    protected function baseUrl()
    {
        return env('NOTIFICATIONS_URL_BASE', '');
    }

    protected function proxyGet($endpoint)
    {
        $url = rtrim($this->baseUrl(), '\\/') . '/' . ltrim($endpoint, '/');
        try {
            $res = Http::timeout(5)->get($url);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Upstream service unreachable
            \Log::error('NotificationProxyController: ConnectionException to ' . $url . ' - ' . $e->getMessage());
            return ['error' => 'notifications_unavailable', 'message' => 'No se pudo conectar al servicio de notificaciones: ' . $url, 'code' => 503];
        } catch (\Throwable $e) {
            \Log::error('NotificationProxyController: Error requesting ' . $url . ' - ' . $e->getMessage());
            return ['error' => 'notifications_error', 'message' => 'Error al solicitar servicio de notificaciones: ' . $e->getMessage(), 'code' => 500];
        }
        $json = null;
        try {
            $json = $res->json();
        } catch (\Throwable $t) {
            $json = null;
        }
        if ($json !== null) return $json;
        return $res->body();
    }

    protected function proxyPost($endpoint, $payload = [])
    {
        $url = rtrim($this->baseUrl(), '\\/') . '/' . ltrim($endpoint, '/');
        try {
            $res = Http::timeout(5)->post($url, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('NotificationProxyController: ConnectionException to ' . $url . ' - ' . $e->getMessage());
            return ['error' => 'notifications_unavailable', 'message' => 'No se pudo conectar al servicio de notificaciones: ' . $url, 'code' => 503];
        } catch (\Throwable $e) {
            \Log::error('NotificationProxyController: Error posting to ' . $url . ' - ' . $e->getMessage());
            return ['error' => 'notifications_error', 'message' => 'Error al solicitar servicio de notificaciones: ' . $e->getMessage(), 'code' => 500];
        }
        $json = null;
        try {
            $json = $res->json();
        } catch (\Throwable $t) {
            $json = null;
        }
        if ($json !== null) return $json;
        return $res->body();
    }

    protected function proxyDelete($endpoint)
    {
        $url = rtrim($this->baseUrl(), '\\/') . '/' . ltrim($endpoint, '/');
        try {
            $res = Http::timeout(5)->delete($url);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('NotificationProxyController: ConnectionException to ' . $url . ' - ' . $e->getMessage());
            return ['error' => 'notifications_unavailable', 'message' => 'No se pudo conectar al servicio de notificaciones: ' . $url, 'code' => 503];
        } catch (\Throwable $e) {
            \Log::error('NotificationProxyController: Error deleting ' . $url . ' - ' . $e->getMessage());
            return ['error' => 'notifications_error', 'message' => 'Error al solicitar servicio de notificaciones: ' . $e->getMessage(), 'code' => 500];
        }
        $json = null;
        try {
            $json = $res->json();
        } catch (\Throwable $t) {
            $json = null;
        }
        if ($json !== null) return $json;
        return $res->body();
    }

    public function status()
    {
        $res = $this->proxyGet('/status');
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        return response()->json($res);
    }

    public function me()
    {
        $res = $this->proxyGet('/me');
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        return response()->json($res);
    }

    public function qr(\Illuminate\Http\Request $request)
    {
        // Fetch from upstream using format=base64 as requested
        $res = $this->proxyGet('/qr?format=base64');
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        
        $format = $request->query('format');
        $imageMode = $format === 'image';

        // Extract the base64 string from the response
        // Expected format: {"qr": "string", "base64": "string"}
        $qrBase64 = null;
        
        if (is_array($res)) {
            $qrBase64 = $res['base64'] ?? $res['qr'] ?? null;
        } elseif (is_object($res)) {
            $qrBase64 = $res->base64 ?? $res->qr ?? null;
        } elseif (is_string($res)) {
            $qrBase64 = $res;
        }

        // Normalize the base64 string
        $qrBase64 = $this->normalizeQrString($qrBase64);

        // If image mode is requested, return the binary image
        if ($imageMode) {
            if (!$qrBase64) {
                // Return a placeholder or 404 if no QR is available
                return response()->json(['error' => 'QR not available'], 404);
            }
            
            $decoded = base64_decode($qrBase64, true);
            if ($decoded === false) {
                return response()->json(['error' => 'Invalid base64 data'], 500);
            }
            
            return response($decoded, 200)->header('Content-Type', 'image/png');
        }

        // For other formats (json, base64), return the JSON response
        // This maintains compatibility with existing JS if it expects JSON
        if ($format === 'base64') {
            return response()->json(['qr' => $qrBase64]);
        }

        // Default: return the original response or the extracted QR
        if (is_array($res)) {
            // Ensure we have the normalized qr field
            $res['qr'] = $qrBase64;
            return response()->json($res);
        }

        return response()->json(['qr' => $qrBase64]);
    }

    public function generateQr(\Illuminate\Http\Request $request)
    {
        $res = $this->proxyPost('/generate-qr');
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        $format = $request->query('format');
        $imageMode = $format === 'image';
        $qr = null;
        if (is_string($res)) {
            $qr = $res;
        } elseif (is_array($res)) {
            if (isset($res['qr'])) $qr = $res['qr'];
            if (isset($res['base64'])) $qr = $res['base64'];
            if (isset($res['dataUrl'])) $qr = $res['dataUrl'];
            if (isset($res['svg'])) $qr = $res['svg'];
        } elseif (is_object($res)) {
            if (isset($res->qr)) $qr = $res->qr;
            if (isset($res->base64)) $qr = $res->base64;
            if (isset($res->dataUrl)) $qr = $res->dataUrl;
            if (isset($res->svg)) $qr = $res->svg;
        }
        if ($qr !== null) {
            // Forward any original shape too (dataUrl or svg or base64) while also
            // broadcasting event containing the raw value.
            $broadcastValue = $qr;
            // If qr is a dataUrl (data:image/png;base64,...), extract the base64 part
            if (is_string($qr) && str_starts_with($qr, 'data:')) {
                $parts = explode(',', $qr, 2);
                if (isset($parts[1])) {
                    $broadcastValue = $parts[1];
                }
            }
            // If qr is a base64-like string, try to normalize it and use that as broadcast value
            if (is_string($broadcastValue) && $norm = $this->normalizeQrString($broadcastValue)) {
                $broadcastValue = $norm;
            }
            // Only broadcast the event if not in image-only mode (the user wanted the base64 for display only)
            if (!($imageMode ?? false)) {
                event(new QrUpdated($broadcastValue));
            }
            // If original response was an object with dataUrl/svg, return it unchanged
            if (is_array($res) || is_object($res)) {
                // If response contains a 'qr' string, normalize and add dataUrl
                $arr = (array)$res;
                if (isset($arr['qr'])) {
                    $norm = $this->normalizeQrString($arr['qr']);
                    $arr['dataUrl'] = $norm ? 'data:image/png;base64,' . $norm : null;
                }
                // If client wants image only, return decoded binary and avoid broadcasting
                if ($imageMode) {
                    $b64 = $arr['qr'] ?? $arr['base64'] ?? null;
                    $norm = $b64 ? $this->normalizeQrString($b64) : null;
                    if ($norm && ($decoded = base64_decode($norm, true))) {
                        return response($decoded, 200)->header('Content-Type', 'image/png');
                    }
                    return response()->json(['error' => 'invalid_base64'], 400);
                }
                return response()->json($arr);
            }
            // For scalar responses (simple string), return the qr and also include dataUrl.
            // If the original response is a dataUrl string, extract base64 part for 'qr' and return original as 'dataUrl'
            if (is_string($qr) && str_starts_with($qr, 'data:')) {
                $parts = explode(',', $qr, 2);
                $base64Part = $parts[1] ?? null;
                $norm = $base64Part ? $this->normalizeQrString($base64Part) : null;
                if ($imageMode) {
                    if (!$norm) return response()->json(['error' => 'invalid_base64'], 400);
                    $decoded = base64_decode($norm, true);
                    if ($decoded === false) return response()->json(['error' => 'invalid_base64'], 400);
                    return response($decoded, 200)->header('Content-Type', 'image/png');
                }
                return response()->json(['qr' => $base64Part ?? $qr, 'dataUrl' => $qr]);
            }
            $norm = is_string($qr) ? $this->normalizeQrString($qr) : null;
            if ($imageMode) {
                if (!$norm) return response()->json(['error' => 'invalid_base64'], 400);
                $decoded = base64_decode($norm, true);
                if ($decoded === false) return response()->json(['error' => 'invalid_base64'], 400);
                return response($decoded, 200)->header('Content-Type', 'image/png');
            }
            return response()->json(['qr' => $norm ? $norm : $qr, 'dataUrl' => $norm ? 'data:image/png;base64,' . $norm : null]);
        }
        return response()->json($res);
    }

    public function deleteSession()
    {
        $res = $this->proxyPost('/delete-session');
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        event(new SessionDeleted());
        return response()->json($res);
    }

    public function chats(Request $request)
    {
        // preserve any querystring parameters and forward them to the upstream notifications server
        $qs = $request->getQueryString();
        $endpoint = '/chats' . ($qs ? '?' . $qs : '');
        $res = $this->proxyGet($endpoint);
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        return response()->json($res);
    }

    public function chatMessages($jid)
    {
        $res = $this->proxyGet('/chats/' . $jid);
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        return response()->json($res);
    }

    public function deleteChat($jid)
    {
        // Proxy a DELETE request to upstream notifications server
        $res = $this->proxyDelete('/chats/' . $jid);
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        return response()->json($res);
    }

    public function send(Request $request)
    {
        $payload = $request->only(['to', 'message']);
        $res = $this->proxyPost('/send', $payload);
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        // Broadcast new message to chat channel
        if (isset($payload['to']) && isset($payload['message'])) {
            $jid = $payload['to'] . '@s.whatsapp.net';
            event(new NewMessage($jid, $payload['message']));
            event(new NewChat($jid, substr($payload['message'], 0, 50)));
        }
        return response()->json($res);
    }

    public function sendFile(Request $request)
    {
        $payload = $request->only(['to', 'type', 'filename', 'mimetype', 'fileBase64']);
        $res = $this->proxyPost('/send-file', $payload);
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        if (isset($payload['to'])) {
            $jid = $payload['to'] . '@s.whatsapp.net';
            event(new NewMessage($jid, '[archivo enviado] ' . ($payload['filename'] ?? 'archivo')));
            event(new NewChat($jid, '[archivo]'));
        }
        return response()->json($res);
    }

    protected function normalizeQrString($s)
    {
        if (!$s || !is_string($s)) return null;
        $v = trim($s);

        // If it's an svg content, don't attempt to normalize to base64
        if (str_starts_with(ltrim($v), '<svg')) {
            return null;
        }

        // If it's a data URL, extract the base64 part after the first comma
        if (str_starts_with($v, 'data:')) {
            $parts = explode(',', $v, 2);
            if (!isset($parts[1])) return null;
            $v = $parts[1];
        }

        // Split on commas or whitespace and join
        $parts = preg_split('/[\s,]+/', $v);
        $parts = array_filter(array_map('trim', $parts));
        if (empty($parts)) return null;

        $filtered = array_map(function ($p) {
            // Remove prefix tokens like 'abc@' or '2@' if present, which are not part of the base64
            if (strpos($p, '@') !== false) {
                $p = substr($p, strpos($p, '@') + 1);
            }
            // remove any leading non-base64 char(s) then remove invalid characters
            $p = preg_replace('#^[^A-Za-z0-9+/=]*#', '', $p);
            $p = preg_replace('#[^A-Za-z0-9+/=]#', '', $p);
            return $p;
        }, $parts);

        $joined = implode('', $filtered);
        if ($joined === '' || (strlen($joined) % 4) !== 0) return null;
        return $joined;
    }

    public function block(Request $request)
    {
        $payload = $request->only(['to', 'action']);
        $res = $this->proxyPost('/block', $payload);
        if (is_array($res) && isset($res['error'])) return response()->json($res, $res['code'] ?? 503);
        return response()->json($res);
    }

    // rejectCall removed: rejecting calls is now handled in the notifications service (Baileys blocks incoming calls by default)
}
