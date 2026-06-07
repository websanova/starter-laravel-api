<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sanctum:prune-expired --hours=1')->dailyAt('02:00')->timezone('America/New_York');
Schedule::command('users:prune-deleted')->dailyAt('02:05')->timezone('America/New_York');
Schedule::command('backup:run --only-db')->dailyAt('02:10')->timezone('America/New_York');
Schedule::command('backup:clean')->dailyAt('02:15')->timezone('America/New_York');
