<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

final class ProfileModalRedirect
{
    public static function wantsModal(Request $request): bool
    {
        return $request->boolean('_profile_modal') || $request->query('modal') === '1';
    }

    /**
     * @param  array<string, mixed>  $with
     */
    public static function after(Request $request, array $with = []): RedirectResponse
    {
        if (self::wantsModal($request)) {
            return redirect()
                ->back()
                ->with(array_merge($with, ['open_profile_modal' => true]));
        }

        return Redirect::route('profile.edit')->with($with);
    }
}
