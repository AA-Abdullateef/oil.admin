<?php

namespace App\Services;

use App\Models\Method;
use App\Models\SubMethod;
use Illuminate\Validation\ValidationException;

class PaymentMethodService
{
    public function resolveSubMethod(array $data): SubMethod
    {
        if (! empty($data['sub_method_id'])) {
            return SubMethod::with('method')
                ->active()
                ->findOrFail($data['sub_method_id']);
        }

        if (! empty($data['method_id'])) {
            $method = Method::with(['subMethods' => fn ($query) => $query->active()->orderBy('name')])
                ->findOrFail($data['method_id']);

            $subMethod = $method->subMethods->first();

            if ($subMethod) {
                return $subMethod;
            }
        }

        throw ValidationException::withMessages([
            'sub_method_id' => ['Select an active payment sub-method.'],
        ]);
    }
}
