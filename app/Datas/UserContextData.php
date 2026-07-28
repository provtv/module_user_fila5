<?php

declare(strict_types=1);

namespace Modules\User\Datas;

use Spatie\LaravelData\Data;

/**
 * Represents an immutable User Context Value Object.
 * Ensures consistency when passing user data across services and layers.
 */
class UserContextData extends Data
{
    /**
     * @param array<int, string> $roles
     */
    public function __construct(
        public readonly ?string $userId = null,
        public readonly string $email = '',
        public readonly bool $isAdministrator = false,
        public readonly array $roles = [],
    ) {
    }

    public static function fromUserModel(object $userModel): self
    {
        $rawId = property_exists($userModel, 'id') ? $userModel->id : null;
        $userId = $rawId !== null ? (string) $rawId : null;

        $roles = array_values(array_map(
            static fn (mixed $role): string => is_string($role) ? $role : (string) $role,
            is_array($userModel->roles ?? null) ? $userModel->roles : [],
        ));

        $rawEmail = $userModel->email ?? '';
        $email = is_string($rawEmail) ? $rawEmail : (string) $rawEmail;

        $rawRole = $userModel->role ?? '';
        $isAdmin = ! empty($rawRole) && 'admin' === strtolower(is_string($rawRole) ? $rawRole : (string) $rawRole);

        return new self(
            userId: $userId,
            email: $email,
            isAdministrator: $isAdmin,
            roles: $roles,
        );
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtolower($role), array_map('strtolower', $this->roles), true);
    }
}
