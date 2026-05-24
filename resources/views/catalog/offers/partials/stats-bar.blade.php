@props([
    'total' => 0,
    'active' => 0,
    'inactive' => 0,
    'lowStock' => 0,
])

<div class="bf-catalog-stats">
    <div class="bf-catalog-stat">
        <span class="bf-catalog-stat__value tabular-nums">{{ $total }}</span>
        <span class="bf-catalog-stat__label">Total</span>
    </div>
    <div class="bf-catalog-stat bf-catalog-stat--success">
        <span class="bf-catalog-stat__value tabular-nums">{{ $active }}</span>
        <span class="bf-catalog-stat__label">Activos</span>
    </div>
    <div class="bf-catalog-stat bf-catalog-stat--muted">
        <span class="bf-catalog-stat__value tabular-nums">{{ $inactive }}</span>
        <span class="bf-catalog-stat__label">Inactivos</span>
    </div>
    @if($lowStock > 0)
        <div class="bf-catalog-stat bf-catalog-stat--warn">
            <span class="bf-catalog-stat__value tabular-nums">{{ $lowStock }}</span>
            <span class="bf-catalog-stat__label">Stock bajo</span>
        </div>
    @endif
</div>
