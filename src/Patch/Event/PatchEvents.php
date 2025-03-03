<?php

namespace Meteor\Patch\Event;

final class PatchEvents
{
    public const PRE_APPLY = 'patch.pre-apply';
    public const POST_APPLY = 'patch.post-apply';
    public const PRE_ROLLBACK = 'patch.pre-rollback';
    public const POST_ROLLBACK = 'patch.post-rollback';
}
