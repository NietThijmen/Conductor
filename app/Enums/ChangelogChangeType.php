<?php

namespace App\Enums;

enum ChangelogChangeType: string
{
    case Breaking = 'breaking';
    case New = 'new';
    case Updated = 'updated';
}
