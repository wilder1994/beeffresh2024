<?php

declare(strict_types=1);

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Services\Store\OfferAvailabilityService;
use App\Services\Store\OfferPricingService;
use Illuminate\View\View;

class OfferPublicController extends Controller
{
    public function __construct(
        private readonly OfferAvailabilityService $availability,
        private readonly OfferPricingService $pricing,
    ) {}

    public function show(Offer $offer): View
    {
        abort_unless($offer->is_active && $offer->isBundle(), 404);
        abort_if($this->availability->availableUnits($offer) <= 0, 404);

        $offer->load(['items.product.meatType', 'items.product.meatCut']);

        return view('public.offers.show', [
            'offer' => $offer,
            'referenceTotal' => $this->pricing->referenceTotal($offer),
            'offerTotal' => $this->pricing->offerTotal($offer),
            'available' => $this->availability->availableUnits($offer),
            'availabilityLabel' => $this->availability->availabilityLabel($offer),
        ]);
    }
}
