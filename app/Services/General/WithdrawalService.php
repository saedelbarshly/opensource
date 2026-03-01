<?php

namespace App\Services\General;

use App\Models\User;
use App\Filter\WithdrawFilter;
use App\Models\WithdrawalRequest;
use App\Enums\WithdrawalStatus;
use App\Enums\WalletTransactionType;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    public function __construct(
        private readonly WalletService $walletService
    ) {}

    public static function make(User $user): self
    {
        return new self(new WalletService($user));
    }

    public function create(User $user, float $amount, string $iban): WithdrawalRequest
    {
        return DB::transaction(function () use ($user, $amount, $iban) {
            // Hold the amount in wallet
            $this->walletService->hold($amount);

            // Create withdrawal request
            return WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'iban' => $iban,
                'status' => WithdrawalStatus::PENDING,
            ]);
        });
    }

    public function approve(WithdrawalRequest $request, User $admin, ?string $note = null): WithdrawalRequest
    {
        return DB::transaction(function () use ($request, $admin, $note) {
            if (!$request->isPending()) {
                throw new \Exception(__('Only pending withdrawal requests can be approved.'));
            }

            $amount = (float) $request->amount;

            // Release the hold
            $this->walletService->release($amount);

            // Process the withdrawal
            $transaction = $this->walletService->withdrawal(
                amount: $amount,
                type: WalletTransactionType::WITHDRAWAL,
                status: 'completed',
                reference: "Withdrawal Request #{$request->id}",
                modelable: $request
            );

            // Update withdrawal request
            $request->update([
                'status' => WithdrawalStatus::APPROVED,
                'wallet_transaction_id' => $transaction->id,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'admin_note' => $note,
            ]);

            return $request->refresh();
        });
    }

    public function reject(WithdrawalRequest $request, User $admin, string $note): WithdrawalRequest
    {
        return DB::transaction(function () use ($request, $admin, $note) {
            if (!$request->isPending()) {
                throw new \Exception(__('Only pending withdrawal requests can be rejected.'));
            }

            // Release the hold
            $this->walletService->release((float) $request->amount);

            // Update withdrawal request
            $request->update([
                'status' => WithdrawalStatus::REJECTED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'admin_note' => $note,
            ]);

            return $request->refresh();
        });
    }

    public function list(User $user, WithdrawFilter $filter)
    {
        return WithdrawalRequest::where('user_id', $user->id)
            ->filter($filter)
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function listAll(?string $status = null)
    {
        return WithdrawalRequest::with(['user', 'approvedBy'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function show(int $id, ?User $user = null): WithdrawalRequest
    {
        $query = WithdrawalRequest::with(['user', 'approvedBy', 'transaction']);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        return $query->findOrFail($id);
    }
}
