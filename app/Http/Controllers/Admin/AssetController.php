<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Services\AssetPriceService;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function __construct(
        private readonly AssetPriceService $priceService,
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(): View
    {
        $assets = Asset::latest()->paginate(15);

        return view('admin.assets.index', compact('assets'));
    }

    public function create(): View
    {
        return view('admin.assets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedAsset($request);
        $data['symbol'] = strtoupper($data['symbol']);
        $data['current_price'] = $data['current_price'] ?? '0';

        $asset = Asset::create($data);
        $this->priceService->recordInitialPrice($asset);

        return redirect()->route('admin.assets.index')->with('success', 'Asset created.');
    }

    public function edit(Asset $asset): View
    {
        return view('admin.assets.edit', compact('asset'));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $data = $this->validatedAsset($request, $asset->id);
        $data['symbol'] = strtoupper($data['symbol']);

        $manualPrice = $data['current_price'] ?? null;
        unset($data['current_price']);

        $asset->update($data);

        if ($manualPrice !== null && bccomp((string) $manualPrice, (string) $asset->current_price, 8) !== 0) {
            $this->priceService->setManually($asset, (string) $manualPrice);
        }

        return redirect()->route('admin.assets.index')->with('success', 'Asset updated.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $asset->delete();

        return redirect()->route('admin.assets.index')->with('success', 'Asset removed.');
    }

    public function syncPrices(): RedirectResponse
    {
        $result = $this->priceService->syncAll();

        $this->auditLogService->log('admin_asset_prices_synced', metadata: $result);

        return redirect()
            ->route('admin.assets.index')
            ->with('success', "Price sync completed. Synced: {$result['synced']}; skipped: {$result['skipped']}; failed: {$result['failed']}.");
    }

    private function validatedAsset(Request $request, ?string $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:20', 'unique:assets,symbol'.($ignoreId ? ','.$ignoreId : '')],
            'type' => ['required', 'in:currency,crypto,share,commodity'],
            'current_price' => ['nullable', 'numeric', 'min:0'],
            'price_source' => ['nullable', 'in:coingecko,alphavantage,finnhub,manual'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
