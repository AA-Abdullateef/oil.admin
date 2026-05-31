<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = ContactMessage::with('user')
            ->when($request->search, fn ($query) => $query->where(function ($query) use ($request) {
                $search = $request->search;

                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage): View
    {
        $contactMessage->load('user');

        return view('admin.contact-messages.show', compact('contactMessage'));
    }
}
