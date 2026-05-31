<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\EarningSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EarningScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = EarningSchedule::with(['asset', 'creator'])
            ->latest()
            ->paginate(20);

        return view('admin.earning-schedules.index', compact('schedules'));
    }

    public function create(): View
    {
        $assets = Asset::where('status', Asset::STATUS_ACTIVE)->orderBy('symbol')->get();

        return view('admin.earning-schedules.create', compact('assets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedSchedule($request);
        $data['created_by'] = $request->user()->id;
        $data['next_run_at'] = $data['start_date'];

        EarningSchedule::create($data);

        return redirect()->route('admin.earning-schedules.index')->with('success', 'Earning schedule created.');
    }

    public function edit(EarningSchedule $earningSchedule): View
    {
        $assets = Asset::where('status', Asset::STATUS_ACTIVE)->orderBy('symbol')->get();

        return view('admin.earning-schedules.edit', compact('earningSchedule', 'assets'));
    }

    public function update(Request $request, EarningSchedule $earningSchedule): RedirectResponse
    {
        $earningSchedule->update($this->validatedSchedule($request));

        return redirect()->route('admin.earning-schedules.index')->with('success', 'Earning schedule updated.');
    }

    public function destroy(EarningSchedule $earningSchedule): RedirectResponse
    {
        $earningSchedule->delete();

        return redirect()->route('admin.earning-schedules.index')->with('success', 'Earning schedule deleted.');
    }

    public function pause(EarningSchedule $earningSchedule): RedirectResponse
    {
        $earningSchedule->update(['status' => EarningSchedule::STATUS_PAUSED]);

        return back()->with('success', 'Earning schedule paused.');
    }

    public function resume(EarningSchedule $earningSchedule): RedirectResponse
    {
        $earningSchedule->update(['status' => EarningSchedule::STATUS_ACTIVE]);

        return back()->with('success', 'Earning schedule resumed.');
    }

    private function validatedSchedule(Request $request): array
    {
        return $request->validate([
            'asset_id' => ['required', 'uuid', 'exists:assets,id'],
            'percentage' => ['required', 'numeric', 'gt:0', 'max:100'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'start_date' => ['required', 'date'],
            'next_run_at' => ['nullable', 'date'],
            'status' => ['required', 'in:active,paused'],
        ]);
    }
}
