@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-center">Test Stripe Integration</h2>
        
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Monto de Prueba</label>
            <input type="number" id="testAmount" value="10.00" step="0.01" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Tarjeta de Prueba</label>
            <div id="card-element" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <!-- Stripe Elements se insertará aquí -->
            </div>
            <div id="card-errors" class="text-red-600 text-sm mt-2 hidden"></div>
        </div>

        <button id="submit-payment" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Probar Pago
        </button>

        <div class="mt-4">
            <h3 class="font-semibold mb-2">Tarjetas de Prueba:</h3>
            <ul class="text-sm text-gray-600">
                <li>• 4242 4242 4242 4242 (Visa exitosa)</li>
                <li>• 4000 0000 0000 0002 (Visa declinada)</li>
                <li>• Cualquier fecha futura y CVC</li>
            </ul>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ config("services.stripe.publishable") }}');
const elements = stripe.elements();

const cardElement = elements.create('card', {
    style: {
        base: {
            fontSize: '16px',
            color: '#424770',
            '::placeholder': {
                color: '#aab7c4',
            },
        },
    },
});

cardElement.mount('#card-element');

cardElement.on('change', function(event) {
    const displayError = document.getElementById('card-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
        displayError.classList.remove('hidden');
    } else {
        displayError.textContent = '';
        displayError.classList.add('hidden');
    }
});

document.getElementById('submit-payment').addEventListener('click', async function() {
    const amount = document.getElementById('testAmount').value;
    
    // Crear Payment Intent
    const response = await fetch('/api/stripe/create-payment-intent', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            pedido_id: 1, // ID de prueba
            amount: parseFloat(amount)
        })
    });

    const data = await response.json();
    
    if (data.success) {
        const result = await stripe.confirmCardPayment(data.client_secret, {
            payment_method: {
                card: cardElement,
            }
        });

        if (result.error) {
            document.getElementById('card-errors').textContent = result.error.message;
            document.getElementById('card-errors').classList.remove('hidden');
        } else {
            alert('¡Pago exitoso! ID: ' + result.paymentIntent.id);
        }
    } else {
        alert('Error: ' + data.error);
    }
});
</script>
@endsection