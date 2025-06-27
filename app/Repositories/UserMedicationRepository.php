<?php

namespace App\Repositories;

use App\Models\UserMedication;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserMedicationRepository
{
    /**
     * Get all medications for a user
     * 
     * @param User $user
     * @return Collection
     */
    public function getUserMedications(User $user): Collection
    {
        return $user->medications()->get();
    }

    /**
     * Add a medication to user's list
     * 
     * @param User $user
     * @param array $medicationData
     * @return UserMedication
     */
    public function addMedication(User $user, array $medicationData): UserMedication
    {
        return $user->medications()->create($medicationData);
    }

    /**
     * Remove a medication from user's list
     * 
     * @param User $user
     * @param string $rxcui
     * @return bool
     */
    public function removeMedication(User $user, string $rxcui): bool
    {
        return $user->medications()->where('rxcui', $rxcui)->delete() > 0;
    }

    /**
     * Check if user has a specific medication
     * 
     * @param User $user
     * @param string $rxcui
     * @return bool
     */
    public function userHasMedication(User $user, string $rxcui): bool
    {
        return $user->medications()->where('rxcui', $rxcui)->exists();
    }

    /**
     * Get a specific medication for a user
     * 
     * @param User $user
     * @param string $rxcui
     * @return UserMedication|null
     */
    public function getUserMedication(User $user, string $rxcui): ?UserMedication
    {
        return $user->medications()->where('rxcui', $rxcui)->first();
    }
} 