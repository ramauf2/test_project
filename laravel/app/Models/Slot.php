<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id Идентификатор холда
 * @property int $capacity Емкость
 * @property int $remaining Осталось
 * @property Carbon $created_at Дата создания
 */
class Slot extends Model
{
    const FIELD_ID = "id";

    const FIELD_CAPACITY = "capacity";

    const FIELD_REMAINING = "remaining";

    protected $fillable = [
        "capacity",
        "remaining",
    ];
}
