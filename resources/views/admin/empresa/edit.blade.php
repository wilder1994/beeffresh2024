@extends('layouts.app')

@section('titulo', 'Contenido · Nosotros')
@section('cabecera', 'Página Nosotros (tienda)')

@section('contenido')
    <div class="max-w-3xl mx-auto">
        <p class="text-sm text-[var(--bf-muted)] mb-6">
            Este texto se muestra en la página pública <a href="{{ route('nosotros') }}" target="_blank" rel="noopener" class="font-medium text-[var(--bf-brand)] hover:underline">/nosotros</a>.
        </p>

        <form method="post" action="{{ route('admin.empresa.update') }}" class="space-y-6 bg-white rounded-2xl border border-amber-100/90 shadow-sm p-5 md:p-8">
            @csrf
            @method('PUT')

            <div>
                <label for="about_heading" class="block text-sm font-medium text-[var(--bf-ink)] mb-1">Título — primera columna</label>
                <input type="text" name="about_heading" id="about_heading" value="{{ old('about_heading', $profile->about_heading) }}" required maxlength="160"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm" />
                @error('about_heading')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="about_content" class="block text-sm font-medium text-[var(--bf-ink)] mb-1">Texto — primera columna</label>
                <textarea name="about_content" id="about_content" rows="5" required maxlength="20000"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm">{{ old('about_content', $profile->about_content) }}</textarea>
                @error('about_content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="promise_heading" class="block text-sm font-medium text-[var(--bf-ink)] mb-1">Título — segunda columna</label>
                <input type="text" name="promise_heading" id="promise_heading" value="{{ old('promise_heading', $profile->promise_heading) }}" required maxlength="160"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm" />
                @error('promise_heading')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="promise_content" class="block text-sm font-medium text-[var(--bf-ink)] mb-1">Texto — segunda columna</label>
                <textarea name="promise_content" id="promise_content" rows="5" required maxlength="20000"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm">{{ old('promise_content', $profile->promise_content) }}</textarea>
                @error('promise_content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="social_heading" class="block text-sm font-medium text-[var(--bf-ink)] mb-1">Título — redes sociales</label>
                <input type="text" name="social_heading" id="social_heading" value="{{ old('social_heading', $profile->social_heading) }}" required maxlength="160"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm" />
                @error('social_heading')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="social_facebook" class="block text-xs font-medium text-[var(--bf-muted)] mb-1">Facebook (URL)</label>
                    <input type="url" name="social_facebook" id="social_facebook" value="{{ old('social_facebook', $profile->social_facebook) }}" placeholder="https://..."
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm" />
                    @error('social_facebook')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="social_instagram" class="block text-xs font-medium text-[var(--bf-muted)] mb-1">Instagram (URL)</label>
                    <input type="url" name="social_instagram" id="social_instagram" value="{{ old('social_instagram', $profile->social_instagram) }}" placeholder="https://..."
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm" />
                    @error('social_instagram')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="social_twitter" class="block text-xs font-medium text-[var(--bf-muted)] mb-1">X / Twitter (URL)</label>
                    <input type="url" name="social_twitter" id="social_twitter" value="{{ old('social_twitter', $profile->social_twitter) }}" placeholder="https://..."
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm" />
                    @error('social_twitter')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="social_youtube" class="block text-xs font-medium text-[var(--bf-muted)] mb-1">YouTube (URL)</label>
                    <input type="url" name="social_youtube" id="social_youtube" value="{{ old('social_youtube', $profile->social_youtube) }}" placeholder="https://..."
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--bf-brand)] focus:ring-[var(--bf-crimson)]/35 text-sm" />
                    @error('social_youtube')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="btn bg-[var(--bf-red)] hover:brightness-110 text-white border-0">Guardar cambios</button>
            </div>
        </form>
    </div>
@endsection
