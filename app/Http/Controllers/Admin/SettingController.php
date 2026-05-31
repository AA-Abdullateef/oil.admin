<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings) {}

    public function index(): View
    {
        $groups = Setting::where('group', '!=', 'payment')->get()->groupBy('group');

        return view('admin.settings.index', compact('groups'));
    }

    public function update(Request $request): RedirectResponse
    {
        // Build validation rules dynamically from DB so no
        // arbitrary keys can be injected
        $keys = Setting::where('group', '!=', 'payment')->pluck('key')->toArray();

        $input = collect($request->only($keys))
            ->map(fn ($v) => is_array($v) ? json_encode($v) : $v)
            ->toArray();

        $this->settings->bulkSet($input);

        return back()->with('success', 'Settings saved.');
    }
}
