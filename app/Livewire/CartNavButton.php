<?php

namespace App\Livewire;

use App\Domain\Cart\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CartNavButton extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        if (! Auth::check()) {
            $this->count = 0;

            return;
        }

        $userId = (string) Auth::id();

        $this->count = (int) CartItem::query()
            ->whereHas('cart', fn ($q) => $q->where('user_id', $userId))
            ->sum('quantity');
    }

    public function render()
    {
        return view('livewire.cart-nav-button');
    }
}
