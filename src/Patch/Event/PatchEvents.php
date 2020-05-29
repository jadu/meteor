<?php

namespace Meteor\Patch\Event;

final class PatchEvents
{
    const PRE_APPLY = 'patch.pre-apply';
    const POST_APPLY = 'patch.post-apply';
    const PRE_ROLLBACK = 'patch.pre-rollback';
    const POST_ROLLBACK = 'patch.post-rollback';
}
