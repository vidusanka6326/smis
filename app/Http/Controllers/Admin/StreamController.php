<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStreamRequest;
use App\Http\Requests\Admin\UpdateStreamRequest;
use App\Models\Stream;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StreamController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Stream::class);

        return view('admin.streams.index', [
            'streams' => Stream::query()->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Stream::class);

        return view('admin.streams.create');
    }

    public function store(StoreStreamRequest $request): RedirectResponse
    {
        Stream::query()->create($request->validated());

        return redirect()
            ->route('admin.streams.index')
            ->with('status', __('Stream created successfully.'));
    }

    public function edit(Stream $stream): View
    {
        $this->authorize('update', $stream);

        return view('admin.streams.edit', [
            'stream' => $stream,
        ]);
    }

    public function update(UpdateStreamRequest $request, Stream $stream): RedirectResponse
    {
        $stream->update($request->validated());

        return redirect()
            ->route('admin.streams.index')
            ->with('status', __('Stream updated successfully.'));
    }

    public function destroy(Stream $stream): RedirectResponse
    {
        $this->authorize('delete', $stream);

        if ($stream->schoolClasses()->exists()) {
            return back()->withErrors([
                'stream' => __('Cannot delete a stream that is assigned to classes.'),
            ]);
        }

        $stream->delete();

        return redirect()
            ->route('admin.streams.index')
            ->with('status', __('Stream deleted successfully.'));
    }
}
