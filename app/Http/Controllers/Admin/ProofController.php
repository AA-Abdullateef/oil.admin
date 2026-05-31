<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Services\ProofFileService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProofController extends Controller
{
    public function __construct(private readonly ProofFileService $proofFiles) {}

    public function deposit(Deposit $deposit): BinaryFileResponse
    {
        abort_if(! $deposit->depositProof?->proof, 404, 'Deposit proof is not available.');

        return $this->proofFiles->download($deposit->depositProof->proof);
    }

    public function withdrawal(Withdrawal $withdrawal): BinaryFileResponse
    {
        abort_if(! $withdrawal->withdrawalProof?->proof, 404, 'Withdrawal proof is not available.');

        return $this->proofFiles->download($withdrawal->withdrawalProof->proof);
    }
}
