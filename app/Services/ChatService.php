<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function createUser($user)
    {

        $response = Http::post(env('CHAT_BASE_URL') . '/brainvire-chat-base-app/api/v1/user/create', [
            'name'              => $user->name,
            'email'             => $user->email,
            'country'           => ! empty($user->country_code) ? $user->country_code : 'in',
            'phone'             => $user->phone,
            'userId'            => (string) $user->id,
            'tenant_id'         => [$user->tenant_id],
            'avatar'            => $user->avatar ?? $user->dp,
            'plan_expired_date' => $user->plan_expired_date,
        ]);

        if ($response->successful()) {
            $responseData = $response->json();

            $chatUserId = $responseData['data']['_id'] ?? null;
            if ($chatUserId) {
                $user['chat_user_id'] = $chatUserId;
                $user->save();
            }
        } else {
            Log::error('Failed to create chat user', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
        }
    }

    public function getUserProfile($email)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(env('CHAT_BASE_URL') . '/brainvire-chat-base-app/api/v1/user/get-profile', [
            'email' => $email,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'error'   => true,
            'status'  => $response->status(),
            'message' => $response->json()['message'] ?? 'Unknown error',
        ];
    }

    public function updateUser(string $chatUserId, string $columnName, $value, $email)
    {
        $token             = $this->getChatToken($chatUserId);
        $chatUser          = $this->getUserProfile($email);
        $existingTenantIds = $this->normalizeToStringArray($chatUser['data']['tenant_id'] ?? []);

        $payload = ['_id' => $chatUserId];

        if ($columnName === 'tenant_id') {
            $inputTenantIds       = $this->normalizeToStringArray($value);
            $mergedTenantIds      = array_unique(array_merge($inputTenantIds, $existingTenantIds));
            $payload['tenant_id'] = array_values($mergedTenantIds);
        } else {
            $payload['tenant_id'] = $existingTenantIds;
            $payload[$columnName] = $value;
        }

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->patch(env('CHAT_BASE_URL') . '/brainvire-chat-base-app/api/v1/user/update', $payload);

        return $response->json();
    }

    public function fetchExistingTenantIds(array $chatUserData)
    {
        $existingTenantIds = is_array($chatUserData['tenant_id'] ?? []) ? $chatUserData['tenant_id'] : [];

        $currentTenantId = (string) tenant('id');

        if (! in_array($currentTenantId, $existingTenantIds)) {
            $existingTenantIds[] = $currentTenantId;
        }
        return $existingTenantIds;
    }

    public function getChatToken(string $chatUserId)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(env('CHAT_BASE_URL') . '/brainvire-chat-base-app/api/v1/user/token', [
            'userId' => $chatUserId,
        ]);

        return $response->json()['data'];
    }

    public function createGroup(string $chatUserId, string $influencerId): ?string
    {
        $token = $this->getChatToken($chatUserId);

        $payload = [
            'groupMembers' => [$influencerId],
            'type'         => 'onetoone',
        ];

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->post(env('CHAT_BASE_URL') . '/brainvire-chat-base-app/api/v1/group/create', $payload);

        $responseData = $response->json();

        return $responseData['data'][0]['_id'] ?? null;
    }

    private function normalizeToStringArray($data): array
    {
        if (! is_array($data)) {
            $data = [$data];
        }
        return array_map('strval', $data);
    }
}
