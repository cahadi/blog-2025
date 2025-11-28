<?php
declare(strict_types=1);

namespace App\Models;

class Order
{
    public function __construct(
        public int $id,
        public int $userId,
        public array $items,
        public float $total,
        public string $status = 'pending',
        public string $createdAt,
        public string $updatedAt
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['id'] ?? 0),
            (int)($data['user_id'] ?? 0),
            (array)($data['items'] ?? []),
            (float)($data['total'] ?? 0.0),
            (string)($data['status'] ?? 'pending'),
            (string)($data['created_at'] ?? ''),
            (string)($data['updated_at'] ?? '')
        );
    }

    public function addItem(int $teaId, int $quantity, float $price): void
    {
        $this->items[] = [
            'tea_id' => $teaId,
            'quantity' => $quantity,
            'price' => $price
        ];
    }

    public function calculateTotal(): float
    {
        $total = 0;

        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    public function getFormattedTotal(): string
    {
        return number_format($this->total, 2, '.', ' ') . ' ₽';
    }

    public function getStatusText(): string
    {
        return match($this->status) {
            'pending' => 'Ожидает обработки',
            'processing' => 'В обработке',
            'completed' => 'Завершен',
            'cancelled' => 'Отменен',
            default => 'Неизвестный статус'
        };
    }
}