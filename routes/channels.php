<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('personal-chat', function () {
    return true;
});


