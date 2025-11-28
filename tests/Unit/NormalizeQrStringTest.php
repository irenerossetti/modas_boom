<?php

use App\Http\Controllers\NotificationProxyController;

it('returns null for empty or invalid strings', function () {
    $controller = new NotificationProxyController();
    $ref = new ReflectionClass($controller);
    $m = $ref->getMethod('normalizeQrString');
    $m->setAccessible(true);

    expect($m->invoke($controller, ''))->toBeNull();
    expect($m->invoke($controller, null))->toBeNull();
    expect($m->invoke($controller, 'not-base64-at-all'))->toBeNull();
    // invalid length not multiple of 4 should be considered invalid
    expect($m->invoke($controller, 'abcdabcdabc'))->toBeNull();
});

it('normalizes a dataUrl to base64', function () {
    $controller = new NotificationProxyController();
    $ref = new ReflectionClass($controller);
    $m = $ref->getMethod('normalizeQrString');
    $m->setAccessible(true);

    $value = 'data:image/png;base64,iVBORw0KGgo=';
    expect($m->invoke($controller, $value))->toBe('iVBORw0KGgo=');
});

it('normalizes raw base64 string', function () {
    $controller = new NotificationProxyController();
    $ref = new ReflectionClass($controller);
    $m = $ref->getMethod('normalizeQrString');
    $m->setAccessible(true);

    $value = 'iVBORw0KGgo=';
    expect($m->invoke($controller, $value))->toBe('iVBORw0KGgo=');
});

it('joins comma separated base64 chunks', function () {
    $controller = new NotificationProxyController();
    $ref = new ReflectionClass($controller);
    $m = $ref->getMethod('normalizeQrString');
    $m->setAccessible(true);

    $value = 'iVB, ORw0K, Ggo=';
    expect($m->invoke($controller, $value))->toBe('iVBORw0KGgo=');
});

it('removes non-base64 prefixes like "2@"', function () {
    $controller = new NotificationProxyController();
    $ref = new ReflectionClass($controller);
    $m = $ref->getMethod('normalizeQrString');
    $m->setAccessible(true);

    $value = '2@iVBORw0KGgo=';
    expect($m->invoke($controller, $value))->toBe('iVBORw0KGgo=');
});

it('returns null for svg strings', function () {
    $controller = new NotificationProxyController();
    $ref = new ReflectionClass($controller);
    $m = $ref->getMethod('normalizeQrString');
    $m->setAccessible(true);

    $svg = '<svg xmlns="http://www.w3.org/2000/svg"></svg>';
    expect($m->invoke($controller, $svg))->toBeNull();
});
