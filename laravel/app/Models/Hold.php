<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id Идентификатор холда
 * @property int $slot_id Идентификатор слота
 * @property string $status Статус ('held', 'confirmed', 'cancelled')
 * @property string $idempotency_key Ключ запроса
 * @property Carbon $created_at Дата создания
 */
class Hold extends Model
{
    const FIELD_ID = "id";
    const FIELD_SLOT_ID = "slot_id";
    const FIELD_STATUS = "status";
    const FIELD_IDEMPOTENCY_KEY = "idempotency_key";
    const FIELD_CREATED_AT = "created_at";

    /**
     * Статусы бронирования "забронировано"
     */
    const STATUS_HELD = "held";

    /**
     * Статусы бронирования "подтверждено"
     */
    const STATUS_CONFIRMED = "confirmed";

    /**
     * Статусы бронирования "отменено"
     */
    const STATUS_CANCELLED = "cancelled";

    protected $fillable = [
        "slot_id",
        "status",
        "idempotency_key",
    ];
}
