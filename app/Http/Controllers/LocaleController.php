<?php

namespace App\Http\Controllers;

use App\Enums\AppLocale;
use App\Http\Requests\UpdateLocaleRequest;
use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __invoke(UpdateLocaleRequest $request): RedirectResponse
    {
        $locale = AppLocale::from($request->validated('locale'));

        $request->session()->put('locale', $locale->value);

        $user = $request->user();

        if ($user !== null) {
            $user->update(['locale' => $locale->value]);
        }

        return back();
    }
}
