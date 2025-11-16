<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Slot;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SlotService
{
    /**
     * Ключ редиса для актуальности кеша
     */
    const SLOT_AVAILABILITY_IS_ACTUAL = "SLOT_AVAILABILITY_IS_ACTUAL";

    /**
     * Ключ редиса для данных кеша
     */
    const SLOT_AVAILABILITY_CACHE = "SLOT_AVAILABILITY_CACHE";

    /**
     * Время жизни холда
     */
    const HOLD_ALIVE_MINUTES = 5;

    /**
     * Обновления кеша доступных слотов
     *
     * @return void
     */
    public static function refreshAvailabilityCache(): void
    {
        Redis::setex(self::SLOT_AVAILABILITY_IS_ACTUAL, 10, 1);
        $availability = Slot::select("id as slot_id", Slot::FIELD_CAPACITY, Slot::FIELD_REMAINING)->get()->all();
        Redis::setex(self::SLOT_AVAILABILITY_CACHE, 15, json_encode($availability));
    }

    /**
     * Создать холд для слота
     *
     * @param int $id Идентификатор слота
     * @param string $key Ключ идемпотентности
     * @return array
     */
    public function hold(int $id, string $key): array
    {
        $hold = Hold::where(Hold::FIELD_IDEMPOTENCY_KEY, $key)->first();
        if ($hold) {
            /**
             * @var Hold $hold
             */
            $diffInMinutes = Carbon::parse($hold->created_at)->diffInMinutes(Carbon::now());
            $holdStatus = $hold->status;
            if ($holdStatus == Hold::STATUS_HELD && $diffInMinutes > self::HOLD_ALIVE_MINUTES) {
                $holdStatus = Hold::STATUS_CANCELLED;
            }
            return [
                Hold::FIELD_ID => $hold->id,
                Hold::FIELD_SLOT_ID => $hold->slot_id,
                Hold::FIELD_STATUS => $holdStatus,
                Hold::FIELD_IDEMPOTENCY_KEY => $hold->idempotency_key,
            ];
        }

        return DB::transaction(function () use ($id, $key) {
            $slot = Slot::lockForUpdate()->find($id);
            /**
             * @var Slot $slot
             */
            if (!$slot) {
                return ["error" => "Slot not found", 404];
            }
            if ($slot->remaining <= 0) {
                return ["error" => "Slot full", 409];
            }
            $totalActiveHolds = Hold::where(Hold::FIELD_SLOT_ID, $id)
                ->where(Hold::FIELD_CREATED_AT, ">=", Carbon::now()->subMinutes(self::HOLD_ALIVE_MINUTES))
                ->whereIn(Hold::FIELD_STATUS, [Hold::STATUS_HELD, Hold::STATUS_CONFIRMED])
                ->count();
            if ($totalActiveHolds > $slot->capacity) {
                return ["error" => "Slot full", 409];
            }

            $data = [
                Hold::FIELD_SLOT_ID => $id,
                Hold::FIELD_STATUS => Hold::STATUS_HELD,
                Hold::FIELD_IDEMPOTENCY_KEY => $key,
            ];
            $hold = Hold::create($data);
            $data[Hold::FIELD_ID] = $hold->id;
            SlotService::refreshAvailabilityCache();
            return $data;
        });
    }

    /**
     * Подтверждение холда
     *
     * @param int $id Идентификатор холда
     * @return array
     */
    public function confirm(int $id): array
    {
        return DB::transaction(function () use ($id) {
            $hold = Hold::lockForUpdate()
                ->whereKey($id)
                ->where(Hold::FIELD_STATUS, Hold::STATUS_HELD)
                ->where(Hold::FIELD_CREATED_AT, ">=", Carbon::now()->subMinutes(self::HOLD_ALIVE_MINUTES))
                ->first();
            if (!$hold) {
                return ["error" => "Hold not found", 404];
            }
            /**
             * @var Hold $hold
             */
            $slot = Slot::lockForUpdate()->find($hold->slot_id);
            /**
             * @var Slot $slot
             */
            if (!$slot) {
                return ["error" => "Slot not found", 404];
            }
            if ($slot->remaining <= 0) {
                return ["error" => "Slot full", 409];
            }

            $slot->decrement(Slot::FIELD_REMAINING);
            $hold->update([Hold::FIELD_STATUS => Hold::STATUS_CONFIRMED]);
            SlotService::refreshAvailabilityCache();
            return ["status" => Hold::STATUS_CONFIRMED];
        });
    }

    /**
     * Отмена холда
     *
     * @param int $id Идентификатор холда
     * @return array
     */
    public function cancel(int $id): array
    {
        return DB::transaction(function () use ($id) {
            $hold = Hold::lockForUpdate()
                ->whereKey($id)
                ->where(Hold::FIELD_STATUS, Hold::STATUS_HELD)
                ->where(Hold::FIELD_CREATED_AT, ">=", Carbon::now()->subMinutes(self::HOLD_ALIVE_MINUTES))
                ->first();
            if (!$hold) {
                return ["error" => "Hold not found", 404];
            }

            $hold->update([Hold::FIELD_STATUS => Hold::STATUS_CANCELLED]);
            SlotService::refreshAvailabilityCache();
            return ["status" => Hold::STATUS_CANCELLED];
        });
    }
}
