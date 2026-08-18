<?php

declare(strict_types=1);

namespace Modules\User\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\User\Models\SocialiteUser;
use Webmozart\Assert\Assert;

trait HasSocialite
{
    /**
     * @return HasMany<SocialiteUser, $this>
     */
    public function socialiteUsers(): HasMany
    {
        return $this->hasMany(SocialiteUser::class);
    }

    public function getProviderField(string $provider, string $field): string
    {
        $socialiteUser = $this->socialiteUsers()->firstWhere(['provider' => $provider]);
        if ($socialiteUser === null) {
            throw new \Exception('SocialiteUser not found');
        }

        $res = $socialiteUser->{$field};
        Assert::string($res);

        return $res;
    }

    public function canAccessSocialite(): bool
    {
        return true;
    }
}
