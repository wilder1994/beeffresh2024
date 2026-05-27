<form method="post" action="{{ route('admin.configuracion.empresa.nosotros') }}" class="bf-form-panel bf-form-panel-tight space-y-3">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 gap-3">
        <div>
            <label for="about_heading" class="bf-label normal-case">Título — primera columna</label>
            <input type="text" name="about_heading" id="about_heading" value="{{ old('about_heading', $profile->about_heading) }}" required maxlength="160" class="bf-input" />
            @error('about_heading')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="about_content" class="bf-label normal-case">Texto — primera columna</label>
            <textarea name="about_content" id="about_content" rows="4" required maxlength="20000" class="bf-textarea min-h-[6rem]">{{ old('about_content', $profile->about_content) }}</textarea>
            @error('about_content')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="promise_heading" class="bf-label normal-case">Título — segunda columna</label>
            <input type="text" name="promise_heading" id="promise_heading" value="{{ old('promise_heading', $profile->promise_heading) }}" required maxlength="160" class="bf-input" />
            @error('promise_heading')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="promise_content" class="bf-label normal-case">Texto — segunda columna</label>
            <textarea name="promise_content" id="promise_content" rows="4" required maxlength="20000" class="bf-textarea min-h-[6rem]">{{ old('promise_content', $profile->promise_content) }}</textarea>
            @error('promise_content')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="social_heading" class="bf-label normal-case">Título — redes sociales</label>
            <input type="text" name="social_heading" id="social_heading" value="{{ old('social_heading', $profile->social_heading) }}" required maxlength="160" class="bf-input" />
            @error('social_heading')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 pt-1">
            <div>
                <label for="social_whatsapp" class="bf-label-muted normal-case">WhatsApp (URL)</label>
                <input type="url" name="social_whatsapp" id="social_whatsapp" value="{{ old('social_whatsapp', $profile->social_whatsapp) }}" placeholder="https://wa.me/…" class="bf-input" />
                @error('social_whatsapp')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="social_tiktok" class="bf-label-muted normal-case">TikTok (URL)</label>
                <input type="url" name="social_tiktok" id="social_tiktok" value="{{ old('social_tiktok', $profile->social_tiktok) }}" placeholder="https://…" class="bf-input" />
                @error('social_tiktok')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="social_instagram" class="bf-label-muted normal-case">Instagram (URL)</label>
                <input type="url" name="social_instagram" id="social_instagram" value="{{ old('social_instagram', $profile->social_instagram) }}" placeholder="https://…" class="bf-input" />
                @error('social_instagram')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="social_facebook" class="bf-label-muted normal-case">Facebook (URL)</label>
                <input type="url" name="social_facebook" id="social_facebook" value="{{ old('social_facebook', $profile->social_facebook) }}" placeholder="https://…" class="bf-input" />
                @error('social_facebook')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="social_twitter" class="bf-label-muted normal-case">X / Twitter (URL)</label>
                <input type="url" name="social_twitter" id="social_twitter" value="{{ old('social_twitter', $profile->social_twitter) }}" placeholder="https://…" class="bf-input" />
                @error('social_twitter')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="social_youtube" class="bf-label-muted normal-case">YouTube (URL)</label>
                <input type="url" name="social_youtube" id="social_youtube" value="{{ old('social_youtube', $profile->social_youtube) }}" placeholder="https://…" class="bf-input" />
                @error('social_youtube')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="bf-form-actions justify-end gap-2">
        <a href="{{ route('nosotros') }}" target="_blank" rel="noopener" class="bf-btn-ghost">Vista previa</a>
        <button type="submit" class="bf-btn-primary">Guardar Nosotros</button>
    </div>
</form>
