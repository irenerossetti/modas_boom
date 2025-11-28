<?php

use App\Events\QrUpdated;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('qr endpoint normaliza un string base64 y devuelve json', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Event::fake();
    Http::fake([
        '*' => Http::response('iVBORw0KGgoAAAANSUhEUgAAAAUA', 200),
    ]);

    $response = $this->actingAs($admin)->get('/admin/notificaciones/qr');
    $response->assertOk();
    $response->assertJsonStructure(['qr']);
    $data = $response->json();
    expect($data['qr'])->toBe('iVBORw0KGgoAAAANSUhEUgAAAAUA');
    // our proxy also adds dataUrl for convenience
    expect($data['dataUrl'])->toBe('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA');
});

test('generate-qr despacha el evento QrUpdated si back respeta', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Event::fake();
    Http::fake([
        '*' => Http::response('iVBORw0KGgoAAAANSUhEUgAAAAUA', 200),
    ]);

    $response = $this->actingAs($admin)->post('/admin/notificaciones/generate-qr');
    $response->assertOk();
    $response->assertJsonStructure(['qr']);
    $data = $response->json();
    expect($data['dataUrl'])->toBe('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA');
    Event::assertDispatched(QrUpdated::class, function ($event) {
        return $event->qrBase64 === 'iVBORw0KGgoAAAANSUhEUgAAAAUA';
    });
});

test('qr endpoint devuelve dataUrl cuando backend retorna dataUrl string', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Event::fake();
    // dataUrl case (string)
    Http::fake([
        '*' => Http::response('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA', 200),
    ]);
    $res1 = $this->actingAs($admin)->get('/admin/notificaciones/qr');
    $res1->assertOk();
    $data1 = $res1->json();
    $res1->assertJsonStructure(['dataUrl']);
    expect($data1['dataUrl'])->toBe('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA');
    expect($data1['qr'])->toBe('iVBORw0KGgoAAAANSUhEUgAAAAUA');
});

test('qr endpoint devuelve svg cuando backend retorna svg string', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Event::fake();
    // SVG string case (not JSON payload)
    Http::fake([
        '*' => Http::response('<svg><rect /></svg>', 200),
    ]);
    $res2 = $this->actingAs($admin)->get('/admin/notificaciones/qr');
    $res2->assertOk();
    $data2 = $res2->json();
    // For SVG string, controller returns { svg: '<svg>..' }
    expect($data2['svg'])->toBe('<svg><rect /></svg>');
});

test('qr endpoint maneja base64 separado por comas', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Event::fake();
    // multi-part base64 separated by commas (whatsapp-like)
    Http::fake([
        '*' => Http::response('iVB, ORw0K, Gg==', 200),
    ]);
    $res3 = $this->actingAs($admin)->get('/admin/notificaciones/qr?format=base64');
    $res3->assertOk();
    $data3 = $res3->json();
    // the controller should return dataUrl constructed from the joined normalized base64
    expect($data3['qr'])->toBe('iVBORw0KGg==');
});

    it('qr endpoint returns image binary for ?format=image when upstream returns raw base64', function () {
        $b64 = 'iVBORw0KGgo=';
        Http::fake([
            env('NOTIFICATIONS_URL_BASE') . '/qr' => Http::response($b64, 200),
        ]);

        Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
        $admin = User::factory()->create(['id_rol' => 1]);
        $res = $this->actingAs($admin)->getJson('/admin/notificaciones/qr?format=image');
        expect($res->status())->toBe(200);
        expect($res->baseResponse->headers->get('content-type'))->toBe('image/png');
        // The returned body should be binary, so double-check by re-encoding
        $body = $res->baseResponse->getContent();
        expect(base64_encode($body))->toBe($b64);
    });

    it('qr endpoint returns image binary for ?format=image when upstream returns dataUrl', function () {
        $b64 = 'iVBORw0KGgo=';
        $dataurl = 'data:image/png;base64,' . $b64;
        Http::fake([
            env('NOTIFICATIONS_URL_BASE') . '/qr' => Http::response($dataurl, 200),
        ]);

        Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
        $admin = User::factory()->create(['id_rol' => 1]);
        $res = $this->actingAs($admin)->getJson('/admin/notificaciones/qr?format=image');
        expect($res->status())->toBe(200);
        expect($res->baseResponse->headers->get('content-type'))->toBe('image/png');
        $body = $res->baseResponse->getContent();
        expect(base64_encode($body))->toBe($b64);
    });

    it('qr endpoint returns 400 for ?format=image when upstream returns svg', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="10" height="10"/></svg>';
        Http::fake([
            env('NOTIFICATIONS_URL_BASE') . '/qr' => Http::response($svg, 200),
        ]);

        Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
        $admin = User::factory()->create(['id_rol' => 1]);
        $res = $this->actingAs($admin)->getJson('/admin/notificaciones/qr?format=image');
        expect($res->status())->toBe(400);
    });

test('qr endpoint maneja property base64 en JSON object', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Http::fake([
        '*' => Http::response(['qr' => 'iVB, ORw0K, Gg==', 'base64' => 'iVBORw0KGg=='], 200),
    ]);
    $res = $this->actingAs($admin)->get('/admin/notificaciones/qr');
    $res->assertOk();
    $data = $res->json();
    // The controller should prioritize the 'base64' field and include dataUrl
    expect($data['qr'])->toBe('iVBORw0KGg==');
    expect($data['dataUrl'])->toBe('data:image/png;base64,iVBORw0KGg==');
});

it('qr endpoint returns image binary for ?format=image when upstream returns JSON with base64', function () {
    $b64 = 'iVBORw0KGgo=';
    Http::fake([
        env('NOTIFICATIONS_URL_BASE') . '/qr' => Http::response(['base64' => $b64], 200),
    ]);
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);
    $res = $this->actingAs($admin)->getJson('/admin/notificaciones/qr?format=image');
    expect($res->status())->toBe(200);
    expect($res->baseResponse->headers->get('content-type'))->toBe('image/png');
    expect(base64_encode($res->baseResponse->getContent()))->toBe($b64);
});

test('qr endpoint retorna qr (base64) cuando se pasa query format=base64 y backend responde dataUrl', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Http::fake([
        '*' => Http::response('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA', 200),
    ]);
    $res = $this->actingAs($admin)->get('/admin/notificaciones/qr?format=base64');
    $res->assertOk();
    $data = $res->json();
    expect($data['qr'])->toBe('iVBORw0KGgoAAAANSUhEUgAAAAUA');
});

test('generate-qr despacha evento con base64 aun cuando res devuelve dataUrl', function () {
    Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
    $admin = User::factory()->create(['id_rol' => 1]);

    Event::fake();
    Http::fake([
        '*' => Http::response('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA', 200),
    ]);

    $response = $this->actingAs($admin)->post('/admin/notificaciones/generate-qr');
    $response->assertOk();
    $response->assertJsonStructure(['dataUrl']);
    $data = $response->json();
    expect($data['dataUrl'])->toBe('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUA');
    Event::assertDispatched(QrUpdated::class, function ($event) {
        return $event->qrBase64 === 'iVBORw0KGgoAAAANSUhEUgAAAAUA';
    });
});

    it('generate-qr returns image binary for ?format=image and upstream returns base64', function () {
        $b64 = 'iVBORw0KGgo=';
        Http::fake([
            env('NOTIFICATIONS_URL_BASE') . '/generate-qr' => Http::response($b64, 200),
        ]);
        Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
        $admin = User::factory()->create(['id_rol' => 1]);
        Event::fake();
        $res = $this->actingAs($admin)->postJson('/admin/notificaciones/generate-qr?format=image');
        expect($res->status())->toBe(200);
        expect($res->baseResponse->headers->get('content-type'))->toBe('image/png');
        expect(base64_encode($res->baseResponse->getContent()))->toBe($b64);
        Event::assertNotDispatched(QrUpdated::class);
    });

    it('generate-qr returns image binary for ?format=image when upstream returns JSON with base64 and does not broadcast', function () {
        $b64 = 'iVBORw0KGgo=';
        Http::fake([
            env('NOTIFICATIONS_URL_BASE') . '/generate-qr' => Http::response(['base64' => $b64], 200),
        ]);
        Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
        $admin = User::factory()->create(['id_rol' => 1]);
        Event::fake();
        $res = $this->actingAs($admin)->postJson('/admin/notificaciones/generate-qr?format=image');
        expect($res->status())->toBe(200);
        expect($res->baseResponse->headers->get('content-type'))->toBe('image/png');
        expect(base64_encode($res->baseResponse->getContent()))->toBe($b64);
        Event::assertNotDispatched(QrUpdated::class);
    });

    it('generate-qr returns 400 for ?format=image when upstream returns svg', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><rect x="0" y="0" width="10" height="10"/></svg>';
        Http::fake([
            env('NOTIFICATIONS_URL_BASE') . '/generate-qr' => Http::response($svg, 200),
        ]);
        Rol::create(['nombre' => 'Administrador', 'habilitado' => true]);
        $admin = User::factory()->create(['id_rol' => 1]);
        $res = $this->actingAs($admin)->postJson('/admin/notificaciones/generate-qr?format=image');
        expect($res->status())->toBe(400);
    });
