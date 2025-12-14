<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Servicio Stripe alternativo usando HTTP requests directos
 * Usar solo si no se puede instalar el SDK oficial
 */
class StripeServiceFallback
{
    private $secretKey;
    private $baseUrl = 'https://api.stripe.com/v1/';

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret');
        
        if (!$this->secretKey) {
            throw new Exception('Stripe secret key no configurada');
        }
    }

    /**
     * Crear un Payment Intent usando HTTP
     */
    public function createPaymentIntent($amount, $currency = 'usd', $metadata = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($this->baseUrl . 'payment_intents', [
                'amount' => $amount * 100, // Centavos
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods[enabled]' => 'true'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'client_secret' => $data['client_secret'],
                    'payment_intent_id' => $data['id']
                ];
            } else {
                $error = $response->json();
                return [
                    'success' => false,
                    'error' => $error['error']['message'] ?? 'Error desconocido'
                ];
            }
        } catch (Exception $e) {
            Log::error('Stripe HTTP Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al conectar con Stripe: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crear Payment Link usando HTTP
     */
    public function createPaymentLink($amount, $currency = 'usd', $metadata = [])
    {
        try {
            // Primero crear un producto
            $productResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($this->baseUrl . 'products', [
                'name' => 'Pago Pedido #' . ($metadata['pedido_id'] ?? 'N/A'),
                'description' => 'Pago de pedido - Modas Boom'
            ]);

            if (!$productResponse->successful()) {
                throw new Exception('Error al crear producto');
            }

            $product = $productResponse->json();

            // Crear precio
            $priceResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($this->baseUrl . 'prices', [
                'unit_amount' => $amount * 100,
                'currency' => $currency,
                'product' => $product['id']
            ]);

            if (!$priceResponse->successful()) {
                throw new Exception('Error al crear precio');
            }

            $price = $priceResponse->json();

            // Crear Payment Link
            $linkResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($this->baseUrl . 'payment_links', [
                'line_items[0][price]' => $price['id'],
                'line_items[0][quantity]' => 1,
                'metadata' => $metadata
            ]);

            if ($linkResponse->successful()) {
                $link = $linkResponse->json();
                return [
                    'success' => true,
                    'payment_link_url' => $link['url'],
                    'payment_link_id' => $link['id']
                ];
            } else {
                $error = $linkResponse->json();
                return [
                    'success' => false,
                    'error' => $error['error']['message'] ?? 'Error al crear enlace'
                ];
            }
        } catch (Exception $e) {
            Log::error('Error creating payment link: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Confirmar Payment Intent
     */
    public function confirmPaymentIntent($paymentIntentId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey
            ])->get($this->baseUrl . 'payment_intents/' . $paymentIntentId);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['status'],
                    'payment_intent' => $data
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Error al obtener Payment Intent'
                ];
            }
        } catch (Exception $e) {
            Log::error('Error confirming payment intent: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar QR Code
     */
    public function generateQRCode($paymentUrl)
    {
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($paymentUrl);
        
        return [
            'success' => true,
            'qr_code_url' => $qrApiUrl,
            'payment_url' => $paymentUrl
        ];
    }
}