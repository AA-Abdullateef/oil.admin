<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function store(ContactMessageRequest $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $message = ContactMessage::create([
            ...$request->validated(),
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return $this->success(
            [
                'id' => $message->id,
                'name' => $message->name,
                'email' => $message->email,
                'created_at' => $message->created_at->toIso8601String(),
            ],
            'Your message has been received. Our support team will respond as soon as possible.',
            201
        );
    }
}
