<?php

namespace App\Enums;

enum ChangelogStatus: string
{
    case Backlog = 'backlog';
    case Checking = 'checking';
    case Checked = 'checked';
}
