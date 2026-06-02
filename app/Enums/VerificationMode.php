<?php

namespace App\Enums;

enum VerificationMode: string
{
    case Disabled = 'disabled';
    case Auto = 'auto';
    case Required = 'required';
}
