<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubMethodRequest;
use App\Models\Method;
use App\Models\SubMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubMethodController extends Controller
{
    public function index(Request $request): View
    {
        $subMethods = SubMethod::with('method')
            ->withCount('transactions')
            ->when($request->method_id, fn ($query) => $query->where('method_id', $request->method_id))
            ->when($request->status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($request->search, fn ($query) => $query->where(function ($search) use ($request) {
                $search->where('name', 'like', "%{$request->search}%")
                    ->orWhere('bank_name', 'like', "%{$request->search}%")
                    ->orWhere('network', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $methods = Method::orderBy('name')->get();

        return view('admin.sub-methods.index', compact('subMethods', 'methods'));
    }

    public function create(): View
    {
        return view('admin.sub-methods.create', [
            'methods' => Method::orderBy('name')->get(),
            'subMethod' => new SubMethod(['is_active' => true]),
        ]);
    }

    public function store(SubMethodRequest $request): RedirectResponse
    {
        SubMethod::create($request->validated());

        return redirect()->route('admin.sub-methods.index')->with('success', 'Sub-method created.');
    }

    public function edit(SubMethod $subMethod): View
    {
        return view('admin.sub-methods.edit', [
            'methods' => Method::orderBy('name')->get(),
            'subMethod' => $subMethod,
        ]);
    }

    public function update(SubMethodRequest $request, SubMethod $subMethod): RedirectResponse
    {
        $subMethod->update($request->validated());

        return redirect()->route('admin.sub-methods.index')->with('success', 'Sub-method updated.');
    }

    public function destroy(SubMethod $subMethod): RedirectResponse
    {
        if ($subMethod->transactions()->exists()) {
            return back()->with('error', 'This sub-method is already referenced by deposits or withdrawals.');
        }

        $subMethod->delete();

        return redirect()->route('admin.sub-methods.index')->with('success', 'Sub-method removed.');
    }
}
