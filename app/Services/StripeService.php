<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class StripeService
{
    private $stripe;

    public function __construct()
    {
        // Verificar si Stripe está disponible
        if (!class_exists('\Stripe\Stripe')) {
            throw new Exception('Stripe SDK no está instalado. Ejecute: composer require stripe/stripe-php');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Crear un Payment Intent para procesar el pago
     */
    public function createPaymentIntent($amount, $currency = 'usd', $metadata = [])
    {
        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount * 100, // Stripe usa centavos
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ];
        } catch (\Stripe\Exception\CardException $e) {
            Log::error('Stripe Card Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error en la tarjeta: ' . $e->getMessage()
            ];
        } catch (\Stripe\Exception\RateLimitException $e) {
            Log::error('Stripe Rate Limit: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Demasiadas solicitudes. Intente más tarde.'
            ];
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            Log::error('Stripe Invalid Request: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Solicitud inválida: ' . $e->getMessage()
            ];
        } catch (\Stripe\Exception\AuthenticationException $e) {
            Log::error('Stripe Auth Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error de autenticación con Stripe'
            ];
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            Log::error('Stripe Connection Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error de conexión con Stripe'
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error de la API de Stripe'
            ];
        } catch (Exception $e) {
            Log::error('General Stripe Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error inesperado al procesar el pago'
            ];
        }
    }

    /**
     * Confirmar un Payment Intent
     */
    public function confirmPaymentIntent($paymentIntentId)
    {
        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            
            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'payment_intent' => $paymentIntent
            ];
        } catch (Exception $e) {
            Log::error('Error confirming payment intent: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear un enlace de pago (Payment Link) para QR
     */
    public function createPaymentLink($amount, $currency = 'usd', $metadata = [])
    {
        try {
            // Crear un producto temporal
            $product = \Stripe\Product::create([
                'name' => 'Pago Pedido #' . ($metadata['pedido_id'] ?? 'N/A'),
                'description' => 'Pago de pedido - Modas Boom',
            ]);

            // Crear un precio para el producto
            $price = \Stripe\Price::create([
                'unit_amount' => $amount * 100, // Centavos
                'currency' => $currency,
                'product' => $product->id,
            ]);

            // Crear el Payment Link
            $paymentLink = \Stripe\PaymentLink::create([
                'line_items' => [
                    [
                        'price' => $price->id,
                        'quantity' => 1,
                    ],
                ],
                'metadata' => $metadata,
            ]);

            return [
                'success' => true,
                'payment_link_url' => $paymentLink->url,
                'payment_link_id' => $paymentLink->id
            ];
        } catch (Exception $e) {
            Log::error('Error creating payment link: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generar QR Code para el enlace de pago
     */
    public function generateQRCode($paymentUrl)
    {
        // Usar una API gratuita para generar QR
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($paymentUrl);
        
        return [
            'success' => true,
            'qr_code_url' => $qrApiUrl,
            'payment_url' => $paymentUrl
        ];
    }

    /**
     * Verificar el estado de un pago
     */
    public function getPaymentStatus($paymentIntentId)
    {
        try {
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            
            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'payment_method' => $paymentIntent->payment_method ?? null
            ];
        } catch (Exception $e) {
            Log::error('Error getting payment status: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    /**
     * Reembolsar un pago
     */
    public function refundPayment($paymentIntentId)
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
            ]);

            return [
                'success' => true,
                'status' => $refund->status,
                'refund_id' => $refund->id
            ];
        } catch (Exception $e) {
            Log::error('Error refunding payment: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}