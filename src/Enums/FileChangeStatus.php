<?php

namespace NietThijmen\ComposerChangelog\Enums;

enum FileChangeStatus: string
{
    case Added = 'added';
    case Removed = 'removed';
    case Modified = 'modified';
    case Unchanged = 'unchanged';
}
