<?php

namespace App\Http\Controllers;

use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HoldController extends Controller
{
    /**
     * Подтверждение холда
     *
     * @param int $id Идентификатор холда
     * @return JsonResponse
     */
    public function confirm(int $id): JsonResponse
    {
        $service = new SlotService();
        return response()->json($service->confirm($id));
    }

    /**
     * Отмена холда
     *
     * @param int $id Идентификатор холда
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        $service = new SlotService();
        return response()->json($service->cancel($id));
    }

    /**
     * Создать бронь для слота
     *
     * @param Request $request Запрос
     * @param int $id Идентификатор слота
     * @return JsonResponse
     */
    public function hold(Request $request, int $id): JsonResponse
    {
        $key = $request->header("Idempotency-Key") ?? 0;
        $service = new SlotService();
        return response()->json($service->hold($id, $key));
    }
}
