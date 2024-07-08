<?php

namespace Revolution\Threads\Enums;

enum ReplyControl: string
{
    case EVERYONE = 'everyone';

    case FOLLOW = 'accounts_you_follow';

    case MENTIONED = 'mentioned_only';
}
