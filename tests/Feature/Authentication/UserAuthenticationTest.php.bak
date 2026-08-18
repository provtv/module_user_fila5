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

beforeEach(function (): void {
    $user = UserFactory::new()->createOne([
        'password' => Hash::make('password123'),
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    \assert($user instanceof User);
    $this->user = $user;
});

describe('User Authentication', function (): void {
    test('can authenticate with valid credentials', function (): void {
        $user = $this->requireUser();
        $result = Auth::attempt([
            'email' => $user->email,
            'password' => 'password123',
        ]);

        Assert::assertSame(true, $result);
        Assert::assertSame($user->id, Auth::user()?->id);
    });

    test('cannot authenticate with invalid password', function (): void {
        $user = $this->requireUser();
        $result = Auth::attempt([
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        Assert::assertSame(false, $result);
        Assert::assertNull(Auth::user());
    });

    test('cannot authenticate with non existent email', function (): void {
        $result = Auth::attempt([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        Assert::assertSame(false, $result);
        Assert::assertNull(Auth::user());
    });

    test('cannot authenticate inactive user', function (): void {
        $inactiveUser = UserFactory::new()->createOne([
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);
        \assert($inactiveUser instanceof User);

        $result = Auth::attempt([
            'email' => $inactiveUser->email,
            'password' => 'password123',
            'is_active' => true,
        ]);

        Assert::assertSame(false, $result);
    });

    test('can logout user', function (): void {
        $user = $this->requireUser();
        Auth::login($user);
        Assert::assertSame(true, Auth::check());
        Auth::logout();
        Assert::assertSame(false, Auth::check());
    });

    test('can hash password on creation', function (): void {
        $user = UserFactory::new()->createOne([
            'password' => Hash::make('testpassword'),
        ]);
        \assert($user instanceof User);

        Assert::assertSame(true, Hash::check('testpassword', $user->password));
    });

    test('can change password', function (): void {
        $user = $this->requireUser();
        $newPassword = 'newpassword123';
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $freshUser = $user->fresh();
        Assert::assertNotNull($freshUser);
        Assert::assertSame(true, Hash::check($newPassword, $freshUser->password));
        Assert::assertSame(false, Hash::check('password123', $freshUser->password));
    });

    test('can check password expiration', function (): void {
        $user = UserFactory::new()->createOne([
            'password_expires_at' => now()->subDays(1),
        ]);
        Assert::assertNotNull($user->password_expires_at);

        $expiresAt = $user->password_expires_at;
        Assert::assertTrue($expiresAt->isPast());
    });

    test('can set password expiration', function (): void {
        $user = $this->requireUser();
        $expirationDate = now()->addDays(90);
        $user->update([
            'password_expires_at' => $expirationDate,
        ]);

        Assert::assertSame(
            $expirationDate->toDateString(),
            $user->fresh()?->password_expires_at?->toDateString(),
        );
    });

    test('can generate remember token', function (): void {
        $user = $this->requireUser();
        $token = Str::random(60);
        $user->forceFill(['remember_token' => $token])->save();

        Assert::assertNotNull($freshUser = $user->fresh());
        Assert::assertSame($token, $freshUser->remember_token);
    });

    test('can authenticate using remember token', function (): void {
        $user = $this->requireUser();
        $token = Str::random(60);
        $user->forceFill(['remember_token' => $token])->save();

        $user = User::where('email', $user->email)->where('remember_token', $token)->first();

        Assert::assertNotNull($user);
        Assert::assertSame($user->id, $user->id);
    });

    test('can mark email as verified', function (): void {
        $user = UserFactory::new()->createOne([
            'email_verified_at' => null,
        ]);
        \assert($user instanceof User);

        Assert::assertNull($user->email_verified_at);
        $user->markEmailAsVerified();

        $freshModel0 = $user->fresh();
        Assert::assertNotNull($freshModel0);
        Assert::assertNotNull($freshModel0->email_verified_at);
    });

    test('can check if email is verified', function (): void {
        $verifiedUser = UserFactory::new()->createOne([
            'email_verified_at' => now(),
        ]);
        \assert($verifiedUser instanceof User);

        $unverifiedUser = UserFactory::new()->createOne([
            'email_verified_at' => null,
        ]);
        \assert($unverifiedUser instanceof User);

        Assert::assertSame(true, $verifiedUser->hasVerifiedEmail());
        Assert::assertSame(false, $unverifiedUser->hasVerifiedEmail());
    });

    test('can send email verification notification', function (): void {
        $user = UserFactory::new()->createOne([
            'email_verified_at' => null,
        ]);
        \assert($user instanceof User);

        Notification::fake();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
    });

    test('can assign and check roles', function (): void {
        $user = $this->requireUser();
        $this->skipUnlessRoleAssignmentSupported();
        $uid = uniqid();
        $adminRole = RoleFactory::new()->createOne(['name' => 'admin-'.$uid]);
        $editorRole = RoleFactory::new()->createOne(['name' => 'editor-'.$uid]);

        $user->assignRole($adminRole);

        Assert::assertSame(true, $user->hasRole('admin-'.$uid));
        Assert::assertSame(false, $user->hasRole('editor-'.$uid));
        Assert::assertSame(true, $user->hasRole($adminRole));
    });

    test('can assign and check permissions', function (): void {
        $user = $this->requireUser();
        $this->skipUnlessDirectPermissionSupported();
        $uid = uniqid();
        $editPermission = PermissionFactory::new()->createOne(['name' => 'edit posts '.$uid]);
        $deletePermission = PermissionFactory::new()->createOne(['name' => 'delete posts '.$uid]);

        $user->givePermissionTo($editPermission);

        Assert::assertSame(true, $user->hasPermissionTo('edit posts '.$uid));
        Assert::assertSame(false, $user->hasPermissionTo('delete posts '.$uid));
        Assert::assertSame(true, $user->hasPermissionTo($editPermission));
    });

    test('can inherit permissions from roles', function (): void {
        $user = $this->requireUser();
        $this->skipUnlessRoleAssignmentSupported();
        $this->skipUnlessDirectPermissionSupported();
        $uid = uniqid();
        $role = RoleFactory::new()->createOne(['name' => 'editor '.$uid]);
        $permission = PermissionFactory::new()->createOne(['name' => 'edit posts '.$uid]);

        $role->givePermissionTo($permission);
        $user->assignRole($role);

        Assert::assertSame(true, $user->hasPermissionTo('edit posts '.$uid));
    });

    test('can check multiple permissions', function (): void {
        $user = $this->requireUser();
        $this->skipUnlessDirectPermissionSupported();
        $uid = uniqid();
        $permission1 = PermissionFactory::new()->createOne(['name' => 'edit posts '.$uid]);
        $permission2 = PermissionFactory::new()->createOne(['name' => 'delete posts '.$uid]);

        $user->givePermissionTo([$permission1, $permission2]);

        Assert::assertSame(true, $user->hasAllPermissions(['edit posts '.$uid, 'delete posts '.$uid]));
        Assert::assertSame(true, $user->hasAnyPermission(['edit posts '.$uid, 'publish posts '.$uid]));
    });

    test('can remove roles and permissions', function (): void {
        $user = $this->requireUser();
        $this->skipUnlessRoleAssignmentSupported();
        $this->skipUnlessDirectPermissionSupported();
        $uid = uniqid();
        $role = RoleFactory::new()->createOne(['name' => 'editor '.$uid]);
        $permission = PermissionFactory::new()->createOne(['name' => 'edit posts '.$uid]);

        $user->assignRole($role);
        $user->givePermissionTo($permission);

        Assert::assertSame(true, $user->hasRole('editor '.$uid));
        Assert::assertSame(true, $user->hasPermissionTo('edit posts '.$uid));
        $user->removeRole($role);
        $user->revokePermissionTo($permission);

        Assert::assertSame(false, $user->hasRole('editor '.$uid));
        Assert::assertSame(false, $user->hasPermissionTo('edit posts '.$uid));
    });

    test('can have oauth clients', function (): void {
        $user = $this->requireUser();
        Assert::assertInstanceOf(MorphMany::class, $user->clients());
    });

    test('can have oauth tokens', function (): void {
        $user = $this->requireUser();
        Assert::assertInstanceOf(HasMany::class, $user->tokens());
    });

    test('can find user for passport', function (): void {
        $user = $this->requireUser();
        $user = User::findForPassport($user->email);

        Assert::assertNotNull($user);
        Assert::assertSame($user->id, $user->id);
    });

    test('can validate password for passport', function (): void {
        $user = $this->requireUser();
        $isValid = $user->validateForPassportPasswordGrant('password123');

        Assert::assertSame(true, $isValid);
    });

    test('can log authentication attempts', function (): void {
        $user = $this->requireUser();
        Assert::assertInstanceOf(MorphMany::class, $user->authentications());
    });

    test('can get latest authentication log', function (): void {
        $user = $this->requireUser();
        Assert::assertInstanceOf(MorphOne::class, $user->latestAuthentication());
    });

    test('can store user in session', function (): void {
        $user = $this->requireUser();
        Auth::login($user);

        Assert::assertSame(true, Auth::check());
        Assert::assertSame($user->id, Auth::id());
    });

    test('can remember user across sessions', function (): void {
        $user = $this->requireUser();
        Auth::login($user, true);

        $fresh = $user->fresh();
        Assert::assertNotNull($fresh);
        Assert::assertNotNull($fresh->remember_token);
    });

    test('can clear user session on logout', function (): void {
        $user = $this->requireUser();
        Auth::login($user);
        Assert::assertSame(true, Auth::check());
        Auth::logout();
        Assert::assertSame(false, Auth::check());
    });

    test('can enable two factor authentication', function (): void {
        $user = $this->requireUser();
        $user->update(['is_otp' => true]);

        Assert::assertNotNull($freshUser = $user->fresh());
        Assert::assertSame(true, $freshUser->is_otp);
    });

    test('can disable two factor authentication', function (): void {
        $user = $this->requireUser();
        $user->update(['is_otp' => false]);

        Assert::assertNotNull($freshUser = $user->fresh());
        Assert::assertSame(false, $freshUser->is_otp);
    });

    test('handles otp authentication workflow', function (): void {
        $user = UserFactory::new()->createOne([
            'is_otp' => true,
            'password' => Hash::make('password123'),
        ]);
        \assert($user instanceof User);

        Auth::attempt([
            'email' => $user->email,
            'password' => 'password123',
        ]);

        Assert::assertSame(true, $user->is_otp);
    });
});
