<?php

uses()->group('model.bookmark');

use App\Models\Bookmark;

test('url hash is set when a bookmark is created', function () {
    $bookmark = Bookmark::factory()->create(['url' => 'https://example.com']);

    expect($bookmark->url_hash)->toBe(Bookmark::hashUrl('https://example.com'));
});

test('url hash is updated when the url changes', function () {
    $bookmark = Bookmark::factory()->create(['url' => 'https://example.com']);

    $bookmark->update(['url' => 'https://other.com']);

    expect($bookmark->url_hash)->toBe(Bookmark::hashUrl('https://other.com'));
});

test('equivalent urls produce the same hash', function (string $variant) {
    expect(Bookmark::hashUrl($variant))->toBe(Bookmark::hashUrl('https://example.com/path?a=1&b=2'));
})->with([
    'uppercase scheme and host' => 'HTTPS://EXAMPLE.COM/path?a=1&b=2',
    'fragment' => 'https://example.com/path?a=1&b=2#section',
    'default port' => 'https://example.com:443/path?a=1&b=2',
    'trailing slash' => 'https://example.com/path/?a=1&b=2',
    'param order' => 'https://example.com/path?b=2&a=1',
    'empty params' => 'https://example.com/path?a=1&&b=2&',
]);

test('equivalent root urls produce the same hash', function (string $variant) {
    expect(Bookmark::hashUrl($variant))->toBe(Bookmark::hashUrl('https://example.com'));
})->with([
    'trailing slash' => 'https://example.com/',
    'empty query' => 'https://example.com?',
    'trailing slash and empty query' => 'https://example.com/?',
]);

test('different urls produce different hashes', function (string $variant) {
    expect(Bookmark::hashUrl($variant))->not->toBe(Bookmark::hashUrl('https://example.com/path?a=1&b=2'));
})->with([
    'different scheme' => 'http://example.com/path?a=1&b=2',
    'www subdomain' => 'https://www.example.com/path?a=1&b=2',
    'path case' => 'https://example.com/Path?a=1&b=2',
    'param value' => 'https://example.com/path?a=1&b=3',
    'non default port' => 'https://example.com:8443/path?a=1&b=2',
]);
