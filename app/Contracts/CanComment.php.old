<?php

declare(strict_types=1);

namespace Modules\User\Contracts;

use Illuminate\Database\Eloquent\Model;
use Modules\Comment\Enums\NotificationSubscriptionType;
use Modules\Comment\Support\CommentatorProperties;

/**
 * Capacità Eloquent: modello che può commentare (commentator).
 *
 * Owner User — stesso pattern di UserContract::authentications() per PHPStan cross-module.
 *
 * @see Modules/Comment/docs/wiki/concepts/can-comment-contract-php84.md
 */
interface CanComment
{
    public function commentatorProperties(): CommentatorProperties;

    /** @return int|string|null */
    public function getKey();

    /** @return string */
    public function getMorphClass();

    public function notify(object $instance): void;

    public function subscribeToCommentNotifications(
        Model $hasComments,
        NotificationSubscriptionType $subscriptionType,
    ): self;

    public function unsubscribeFromCommentNotifications(
        Model $hasComments,
    ): self;

    public function unsubscribeFromAllCommentNotifications(): self;

    public function notificationSubscriptionType(
        Model $hasComment,
    ): ?NotificationSubscriptionType;
}
