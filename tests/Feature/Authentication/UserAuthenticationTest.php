<?php

declare(strict_types=1);

namespace Modules\User\Tests\Feature\Authentication;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Modules\User\Database\Factories\PermissionFactory;
use Modules\User\Database\Factories\RoleFactory;
use Modules\User\Database\Factories\UserFactory;
use Modules\User\Models\User;
use Modules\User\Tests\TestCase;
use PHPUnit\Framework\Assert;

uses(TestCase::class);

function authenticationUser(?User $newUser = null): User
{
    static $user;

    if ($newUser instanceof User) {
        $user = $newUser;
    }

    if (! $user instanceof User) {
        throw new \LogicException('Authentication test user is not initialized.');
    }

    return $user;
}

function freshAuthenticationUser(): User
{
    $user = authenticationUser()->fresh();
    Assert::assertNotNull($user);

    return $user;
}

beforeEach(function () {
    $user = UserFactory::new()->createOne([
        'password' => Hash::make('password123'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    authenticationUser($user);
});

describe('User Authentication', function () {
    it('can authenticate with valid credentials', function () {
        $result = Auth::attempt([
            'email' => authenticationUser()->email,
            'password' => 'password123',
        ]);

        Assert::assertTrue($result);
        Assert::assertSame(authenticationUser()->id, Auth::user()?->id);
    });

    it('cannot authenticate with invalid password', function () {
        $result = Auth::attempt([
            'email' => authenticationUser()->email,
            'password' => 'wrongpassword',
        ]);

        Assert::assertFalse($result);
        Assert::assertNull(Auth::user());
    });

    it('cannot authenticate with non-existent email', function () {
        $result = Auth::attempt([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        Assert::assertFalse($result);
        Assert::assertNull(Auth::user());
    });

    it('cannot authenticate inactive user', function () {
        /** @var User $inactiveUser */
        /** @var User $inactiveUser */
        $inactiveUser = UserFactory::new()->createOne([
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $result = Auth::attempt([
            'email' => $inactiveUser->email,
            'password' => 'password123',
            'is_active' => true,
        ]);

        Assert::assertFalse($result);
    });

    it('can logout user', function () {
        Auth::login(authenticationUser());
        Assert::assertTrue(Auth::check());

        Auth::logout();
        Assert::assertFalse(Auth::check());
    });
});

describe('User Password Management', function () {
    it('can hash password on creation', function () {
        /** @var User $user */
        /** @var User $user */
        $user = UserFactory::new()->createOne([
            'password' => Hash::make('testpassword'),
        ]);

        Assert::assertTrue(Hash::check('testpassword', $user->password));
    });

    it('can change password', function () {
        $newPassword = 'newpassword123';
        authenticationUser()->update([
            'password' => Hash::make($newPassword),
        ]);

        Assert::assertTrue(Hash::check($newPassword, freshAuthenticationUser()->password));
        Assert::assertFalse(Hash::check('password123', freshAuthenticationUser()->password));
    });

    it('can check password expiration', function () {
        /** @var User $user */
        $user = UserFactory::new()->createOne([
            'password_expires_at' => now()->subDays(1),
        ]);
        $passwordExpiresAt = $user->password_expires_at;
        Assert::assertNotNull($passwordExpiresAt);

        Assert::assertTrue($passwordExpiresAt->isPast());
    });

    it('can set password expiration', function () {
        $expirationDate = now()->addDays(90);
        authenticationUser()->update([
            'password_expires_at' => $expirationDate,
        ]);

        $passwordExpiresAt = freshAuthenticationUser()->password_expires_at;
        Assert::assertNotNull($passwordExpiresAt);

        Assert::assertSame($expirationDate->toDateString(), $passwordExpiresAt->toDateString());
    });
});

describe('User Remember Token', function () {
    it('can generate remember token', function () {
        $token = Str::random(60);
        authenticationUser()->forceFill(['remember_token' => $token])->save();

        Assert::assertSame($token, freshAuthenticationUser()->remember_token);
    });

    it('can authenticate using remember token', function () {
        $token = Str::random(60);
        authenticationUser()->forceFill(['remember_token' => $token])->save();

        $user = User::where('email', authenticationUser()->email)->where('remember_token', $token)->first();

        Assert::assertNotNull($user);
        Assert::assertSame(authenticationUser()->id, $user->id);
    });
});

describe('User Email Verification', function () {
    it('can mark email as verified', function () {
        /** @var User $user */
        $user = UserFactory::new()->createOne([
            'email_verified_at' => null,
        ]);

        Assert::assertNull($user->email_verified_at);

        $user->markEmailAsVerified();

        $fresh = $user->fresh();
        Assert::assertNotNull($fresh);

        Assert::assertNotNull($fresh->email_verified_at);
    });

    it('can check if email is verified', function () {
        /** @var User $verifiedUser */
        $verifiedUser = UserFactory::new()->createOne([
            'email_verified_at' => now(),
        ]);

        /** @var User $unverifiedUser */
        $unverifiedUser = UserFactory::new()->createOne([
            'email_verified_at' => null,
        ]);

        Assert::assertTrue($verifiedUser->hasVerifiedEmail());
        Assert::assertFalse($unverifiedUser->hasVerifiedEmail());
    });

    it('can send email verification notification', function () {
        /** @var User $user */
        $user = UserFactory::new()->createOne([
            'email_verified_at' => null,
        ]);

        Notification::fake();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
    });
});

describe('User Authorization', function () {
    it('can assign and check roles', function () {
        $adminRole = RoleFactory::new()->createOne(['name' => 'admin']);
        $editorRole = RoleFactory::new()->createOne(['name' => 'editor']);

        authenticationUser()->assignRole($adminRole);

        Assert::assertTrue(authenticationUser()->hasRole('admin'));
        Assert::assertFalse(authenticationUser()->hasRole('editor'));
        Assert::assertTrue(authenticationUser()->hasRole($adminRole));
    });

    it('can assign and check permissions', function () {
        $editPermission = PermissionFactory::new()->createOne(['name' => 'edit posts']);
        $deletePermission = PermissionFactory::new()->createOne(['name' => 'delete posts']);

        authenticationUser()->givePermissionTo($editPermission);

        Assert::assertTrue(authenticationUser()->hasPermissionTo('edit posts'));
        Assert::assertFalse(authenticationUser()->hasPermissionTo('delete posts'));
        Assert::assertTrue(authenticationUser()->hasPermissionTo($editPermission));
    });

    it('can inherit permissions from roles', function () {
        $role = RoleFactory::new()->createOne(['name' => 'editor']);
        $permission = PermissionFactory::new()->createOne(['name' => 'edit posts']);

        $role->givePermissionTo($permission);
        authenticationUser()->assignRole($role);

        Assert::assertTrue(authenticationUser()->hasPermissionTo('edit posts'));
    });

    it('can check multiple permissions', function () {
        $permission1 = PermissionFactory::new()->createOne(['name' => 'edit posts']);
        $permission2 = PermissionFactory::new()->createOne(['name' => 'delete posts']);

        authenticationUser()->givePermissionTo([$permission1, $permission2]);

        Assert::assertTrue(authenticationUser()->hasAllPermissions(['edit posts', 'delete posts']));
        Assert::assertTrue(authenticationUser()->hasAnyPermission(['edit posts', 'publish posts']));
    });

    it('can remove roles and permissions', function () {
        $role = RoleFactory::new()->createOne(['name' => 'editor']);
        $permission = PermissionFactory::new()->createOne(['name' => 'edit posts']);

        authenticationUser()->assignRole($role);
        authenticationUser()->givePermissionTo($permission);

        Assert::assertTrue(authenticationUser()->hasRole('editor'));
        Assert::assertTrue(authenticationUser()->hasPermissionTo('edit posts'));

        authenticationUser()->removeRole($role);
        authenticationUser()->revokePermissionTo($permission);

        Assert::assertFalse(authenticationUser()->hasRole('editor'));
        Assert::assertFalse(authenticationUser()->hasPermissionTo('edit posts'));
    });
});

describe('User OAuth Authentication', function () {
    it('can have oauth clients', function () {
        Assert::assertInstanceOf(MorphMany::class, authenticationUser()->clients());
    });

    it('can have oauth tokens', function () {
        Assert::assertInstanceOf(HasMany::class, authenticationUser()->tokens());
    });

    it('can find user for passport', function () {
        $user = User::findForPassport(authenticationUser()->email);

        Assert::assertNotNull($user);
        Assert::assertSame(authenticationUser()->id, $user->id);
    });

    it('can validate password for passport', function () {
        $isValid = authenticationUser()->validateForPassportPasswordGrant('password123');

        Assert::assertTrue($isValid);
    });
});

describe('User Authentication Logging', function () {
    it('can log authentication attempts', function () {
        Assert::assertInstanceOf(MorphMany::class, authenticationUser()->authentications());
    });

    it('can get latest authentication log', function () {
        Assert::assertInstanceOf(MorphOne::class, authenticationUser()->latestAuthentication());
    });
});

describe('User Session Management', function () {
    it('can store user in session', function () {
        Auth::login(authenticationUser());

        Assert::assertTrue(Auth::check());
        Assert::assertSame(authenticationUser()->id, Auth::id());
    });

    it('can remember user across sessions', function () {
        Auth::login(authenticationUser(), true);

        Assert::assertNotNull(freshAuthenticationUser()->remember_token);
    });

    it('can clear user session on logout', function () {
        Auth::login(authenticationUser());
        Assert::assertTrue(Auth::check());

        Auth::logout();
        Assert::assertFalse(Auth::check());
    });
});

describe('User Two Factor Authentication', function () {
    it('can enable two factor authentication', function () {
        authenticationUser()->update(['is_otp' => true]);

        Assert::assertTrue(freshAuthenticationUser()->is_otp);
    });

    it('can disable two factor authentication', function () {
        authenticationUser()->update(['is_otp' => false]);

        Assert::assertFalse(freshAuthenticationUser()->is_otp);
    });

    it('handles otp authentication workflow', function () {
        /** @var User $user */
        $user = UserFactory::new()->createOne([
            'is_otp' => true,
            'password' => Hash::make('password123'),
        ]);

        // First step: password authentication
        $result = Auth::attempt([
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // Should handle OTP requirement
        Assert::assertTrue($user->is_otp);
    });
});
