<?php

namespace App\Models;

use Laravel\Passport\Token;

/**
 * Passport's access token under a name that does not clash with Sanctum's
 * tokens. It reads the same oauth_access_tokens table.
 */
class PassportToken extends Token {}
