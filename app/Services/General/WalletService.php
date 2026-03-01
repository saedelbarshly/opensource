<?php

namespace App\Services\General;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Model;

class WalletService
{
    protected User $user;
    protected Wallet $wallet;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth('api')->user();
        $this->wallet = $this->user->wallet()->firstOrCreate([]);
    }

    public static function make(?User $user = null): self
    {
        return new self($user);
    }

    // ---------- PUBLIC API ----------

    public function getBalance(): float
    {
        return (float) $this->wallet->balance;
    }

    public function getHoldBalance(): float
    {
        return (float) $this->wallet->hold_balance;
    }

    public function getAvailableBalance(): float
    {
        return $this->getBalance() - $this->getHoldBalance();
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getTransactions($type = null)
    {
        return $this->wallet->transactions()
            ->when($type, fn($query) => $query->where('type', $type))
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function deposit(float $amount, WalletTransactionType $type, ?string $status = 'completed', ?string $reference = null, ?Model $modelable = null)
    {
        return DB::transaction(function () use ($amount, $type, $status, $reference, $modelable) {
            $balanceBefore = $this->getBalance();
            $balanceAfter = $balanceBefore + $amount;

            $transaction = $this->wallet->transactions()->create([
                'user_id'        => $this->user->id,
                'modelable_type' => $modelable ? get_class($modelable) : null,
                'modelable_id'   => $modelable ? $modelable->id : null,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'amount'         => $amount,
                'type'           => $type,
                'status'         => $status,
                'reference'      => $reference,
            ]);

            $this->wallet->update(['balance' => $balanceAfter]);
            $this->wallet->refresh();

            return $transaction;
        });
    }

    public function withdrawal(float $amount, WalletTransactionType $type, ?string $status = 'completed', ?string $reference = null, ?Model $modelable = null)
    {
        return DB::transaction(function () use ($amount, $type, $status, $reference, $modelable) {
            if ($this->getAvailableBalance() < $amount) {
                throw new \Exception(__('Insufficient balance for withdrawal.'));
            }

            $balanceBefore = $this->getBalance();
            $balanceAfter = $balanceBefore - $amount;

            $transaction = $this->wallet->transactions()->create([
                'user_id'        => $this->user->id,
                'modelable_type' => $modelable ? get_class($modelable) : null,
                'modelable_id'   => $modelable ? $modelable->id : null,
                'balance_before' => $balanceBefore,
                'balance_after'  => $balanceAfter,
                'amount'         => $amount,
                'type'           => $type,
                'status'         => $status,
                'reference'      => $reference,
            ]);

            $this->wallet->update(['balance' => $balanceAfter]);
            $this->wallet->refresh();

            return $transaction;
        });
    }

    public function hold(float $amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->getAvailableBalance() < $amount) {
                throw new \Exception(__('Insufficient balance to hold.'));
            }

            $this->wallet->increment('hold_balance', $amount);
            $this->wallet->refresh();

            return $this->wallet;
        });
    }

    public function release(float $amount)
    {
        return DB::transaction(function () use ($amount) {
            if ($this->getHoldBalance() < $amount) {
                throw new \Exception(__('Insufficient hold balance to release.'));
            }

            $this->wallet->decrement('hold_balance', $amount);
            $this->wallet->refresh();

            return $this->wallet;
        });
    }
}
