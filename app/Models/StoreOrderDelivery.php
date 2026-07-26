<?php

namespace App\Models;

use App\Enums\StorePackageCommandTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreOrderDelivery extends BaseModel
{
    use HasFactory;

    protected $casts = [
        'trigger' => StorePackageCommandTrigger::class,
        'repeat_index' => 'integer',
        'redispatch_count' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(StoreOrderItem::class, 'store_order_item_id');
    }

    public function packageCommand(): BelongsTo
    {
        return $this->belongsTo(StorePackageCommand::class, 'store_package_command_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function commandQueue(): BelongsTo
    {
        return $this->belongsTo(CommandQueue::class, 'command_queue_id');
    }
}
