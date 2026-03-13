<?php

namespace App\Http\Controllers;

use App\Models\Hall;
use App\Service\HallService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\HallRequest;

class HallController extends Controller
{
    use ApiResponse;

    protected HallService $hallPriceService;

    public function __construct(HallService $hallPriceService)
    {
        $this->hallPriceService = $hallPriceService;
    }

    /**
     * Display a listing of the prices.
     */
    public function index(): JsonResponse
    {
        try {
            $prices = $this->hallPriceService->getAll();
            return $this->successResponse($prices, 'Prices retrieved successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while retrieving the prices.', 500);
        }
    }

    /**
     * Store a newly created price in storage.
     */
    public function store(HallRequest $request): JsonResponse
    {
        try {
            $price = $this->hallPriceService->create($request->validated());
            return $this->successResponse($price, 'Price created successfully.', 201);
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while creating the price.', 500);
        }
    }

    /**
     * Display the specified price.
     */
    public function show(int $id): JsonResponse
    {
        $price = $this->hallPriceService->getById($id);

        if (!$price) {
            return $this->errorResponse('Price not found.', 404);
        }

        return $this->successResponse($price, 'Price retrieved successfully.');
    }

    /**
     * Update the specified price in storage.
     */
    public function update(HallRequest $request, int $id): JsonResponse
    {
        try {
            $price = $this->hallPriceService->getById($id);

            if (!$price) {
                return $this->errorResponse('Price not found.', 404);
            }

            $updatedPrice = $this->hallPriceService->update($price, $request->validated());
            return $this->successResponse($updatedPrice, 'Price updated successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while updating the price.', 500);
        }
    }

    /**
     * Remove the specified price from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $price = $this->hallPriceService->getById($id);

            if (!$price) {
                return $this->errorResponse('Price not found.', 404);
            }

            $this->hallPriceService->delete($price);
            return $this->successResponse(null, 'Price deleted successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while deleting the price.', 500);
        }
    }
}
