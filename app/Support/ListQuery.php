<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

final class ListQuery
{
    public const DEFAULT_PER_PAGE = 20;

    /** @var list<int> */
    public const PER_PAGE_OPTIONS = [10, 20, 50, 100];

    /**
     * @param  list<string>  $keys
     * @return array<string, mixed>
     */
    public static function filters(Request $request, array $keys): array
    {
        $filters = [];

        foreach ($keys as $key) {
            $value = $request->input($key);

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === null || $value === '') {
                continue;
            }

            $filters[$key] = $value;
        }

        return $filters;
    }

    public static function perPage(Request $request, int $default = self::DEFAULT_PER_PAGE): int
    {
        $perPage = $request->integer('per_page', $default);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return $default;
        }

        return $perPage;
    }

    /**
     * @param  Builder<*>  $query
     * @return LengthAwarePaginator<*>
     */
    public static function paginate(Builder $query, Request $request, int $default = self::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return $query
            ->paginate(self::perPage($request, $default))
            ->withQueryString();
    }

    /**
     * @template TValue
     *
     * @param  Collection<int, TValue>|list<TValue>  $items
     * @return LengthAwarePaginator<int, TValue>
     */
    public static function paginateCollection(Collection|array $items, Request $request, int $default = self::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        $collection = $items instanceof Collection ? $items->values() : collect($items)->values();
        $perPage = self::perPage($request, $default);
        $page = max(1, $request->integer('page', 1));

        return new Paginator(
            $collection->forPage($page, $perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );
    }

    public static function isFiltered(array $filters): bool
    {
        return collect($filters)->contains(fn (mixed $value): bool => filled($value));
    }
}
