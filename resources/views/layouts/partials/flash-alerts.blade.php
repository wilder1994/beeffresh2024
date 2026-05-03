@if(session('error'))
    <div class="max-w-4xl mx-auto px-4 pt-4">
        <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded">{{ session('error') }}</div>
    </div>
@endif
@if(session('success'))
    <div class="max-w-4xl mx-auto px-4 pt-4">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 rounded">{{ session('success') }}</div>
    </div>
@endif
