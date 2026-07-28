<?php

declare(strict_types=1);

namespace Modules\User\Actions\Team;

use Modules\User\Models\TeamUser;
use Spatie\QueueableAction\QueueableAction;

class GetUserTeamsOptionAction
{
    use QueueableAction;

    /**
     * @return array<int|string, string>
     */
    public function execute(): array
    {
        $teams = TeamUser::where('user_id', authId())->get();

        /** @var array<int|string, string> $options */
        $options = ['' => '--- Select ---'];

        foreach ($teams as $teamUser) {
            $team = $teamUser->team;
            if (null === $team) {
                continue;
            }

            $key = $team->getKey();
            $name = $team->getAttribute('name');
            $options[is_string($key) ? $key : (string) $key] = is_string($name) ? $name : (string) $name;
        }

        return $options;
    }
}
