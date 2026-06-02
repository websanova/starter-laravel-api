<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-expired --hours=1')->daily();
Schedule::command('users:prune-deleted')->daily();
