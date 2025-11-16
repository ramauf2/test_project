<?php

namespace App\Http\Controllers;

use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class AvailabilityController extends Controller
{
    /**
     * Получить список слотов
     *
     * @return JsonResponse
     */
    public function availability(): JsonResponse
    {
        if (!Redis::exists(SlotService::SLOT_AVAILABILITY_IS_ACTUAL)) {
            SlotService::refreshAvailabilityCache();
        }
        return response()->json(Redis::get(SlotService::SLOT_AVAILABILITY_CACHE));
    }
}
