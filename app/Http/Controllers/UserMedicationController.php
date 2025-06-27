<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddDrugRequest;
use App\Http\Requests\DeleteDrugRequest;
use App\Http\Resources\UserMedicationResource;
use App\Repositories\UserMedicationRepository;
use App\Services\RxNormService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class UserMedicationController extends Controller
{
    public function __construct(
        private UserMedicationRepository $medicationRepository,
        private RxNormService $rxNormService
    ) {}

    /**
     * Get all medications for the authenticated user
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $medications = $this->medicationRepository->getUserMedications($user);
        
        return UserMedicationResource::collection($medications);
    }

    /**
     * Add a drug to user's medication list
     * 
     * @param AddDrugRequest $request
     * @return JsonResponse
     */
    public function store(AddDrugRequest $request): JsonResponse
    {
        $rxcui = $request->rxcui;
        $user = $request->user();

        // Check if user already has this medication
        if ($this->medicationRepository->userHasMedication($user, $rxcui)) {
            return response()->json([
                'message' => 'This medication is already in your list.',
            ], 400);
        }

        // Validate rxcui with RxNorm API
        if (!$this->rxNormService->validateRxcui($rxcui)) {
            return response()->json([
                'message' => 'Invalid RxCUI provided.',
            ], 400);
        }

        // Get drug details from RxNorm API
        $drugDetails = $this->rxNormService->getDrugDetails($rxcui);

        if (!$drugDetails) {
            return response()->json([
                'message' => 'Could not retrieve drug information.',
            ], 400);
        }

        // Add medication to user's list
        $medication = $this->medicationRepository->addMedication($user, $drugDetails);

        return response()->json([
            'message' => 'Medication added successfully',
            'medication' => new UserMedicationResource($medication),
        ], 201);
    }

    /**
     * Remove a drug from user's medication list
     * 
     * @param DeleteDrugRequest $request
     * @return JsonResponse
     */
    public function destroy(DeleteDrugRequest $request): JsonResponse
    {
        $rxcui = $request->rxcui;
        $user = $request->user();

        // Check if user has this medication
        if (!$this->medicationRepository->userHasMedication($user, $rxcui)) {
            return response()->json([
                'message' => 'Medication not found in your list.',
            ], 404);
        }

        // Remove medication from user's list
        $this->medicationRepository->removeMedication($user, $rxcui);

        return response()->json([
            'message' => 'Medication removed successfully',
        ]);
    }
}
