<?php

namespace App\Enums;

enum RegistryAuthType: string
{
    case None = 'none';
    case Basic = 'basic';
    case Token = 'token';
    case Composer = 'composer';
}
