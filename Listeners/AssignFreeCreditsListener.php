<?php

declare(strict_types=1);

namespace Modules\User\Listeners;

use Illuminate\Auth\Events\Registered;
use Modules\Predict\Models\Profile;

/**
 * Listener per assegnare crediti iniziali gratuiti ai nuovi utenti.
 */
class AssignFreeCreditsListener
{
    /**
     * Crediti iniziali gratuiti per nuovi utenti.
     */
    private const FREE_STARTING_CREDITS = 500;

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Crea il profilo se non esiste
        $profile = Profile::firstOrCreate(
            ['user_id' => $user->id],
            ['credits' => self::FREE_STARTING_CREDITS]
        );

        // Se il profilo esisteva già ma aveva 0 crediti, assegna i crediti iniziali
        if (0 === $profile->credits) {
            $profile->update(['credits' => self::FREE_STARTING_CREDITS]);
        }
    }
}
