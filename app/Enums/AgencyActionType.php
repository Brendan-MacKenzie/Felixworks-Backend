<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

class AgencyActionType extends Enum
{
    public const PostingUpdate = 'posting_updated';
    public const PostingRemoved = 'posting_removed';
    public const SendAvatar = 'user_send-avatar';
    public const AddToPool = 'user_add-to-pool';
    public const RemoveFromPool = 'user_remove-from-pool';
}
