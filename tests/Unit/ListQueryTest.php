<?php

use App\Support\ListQuery;
use Illuminate\Http\Request;

test('filters drops empty strings and trims values', function () {
    $request = Request::create('/', 'GET', [
        'search' => '  Ada  ',
        'gender' => '',
        'class_id' => null,
        'status' => 'active',
    ]);

    expect(ListQuery::filters($request, ['search', 'gender', 'class_id', 'status']))->toBe([
        'search' => 'Ada',
        'status' => 'active',
    ]);
});

test('per page only accepts allowed sizes', function () {
    expect(ListQuery::perPage(Request::create('/', 'GET', ['per_page' => 50])))->toBe(50)
        ->and(ListQuery::perPage(Request::create('/', 'GET', ['per_page' => 7])))->toBe(20)
        ->and(ListQuery::perPage(Request::create('/', 'GET')))->toBe(20);
});

test('paginate collection slices items and preserves query string', function () {
    $items = collect(range(1, 25));
    $request = Request::create('http://localhost/items', 'GET', [
        'page' => 2,
        'per_page' => 10,
        'search' => 'ada',
    ]);

    $paginator = ListQuery::paginateCollection($items, $request);

    expect($paginator->total())->toBe(25)
        ->and($paginator->perPage())->toBe(10)
        ->and($paginator->currentPage())->toBe(2)
        ->and($paginator->items())->toBe([11, 12, 13, 14, 15, 16, 17, 18, 19, 20])
        ->and($paginator->appends([])->url(1))->toContain('search=ada');
});
