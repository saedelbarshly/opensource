<?php

use PhpParser\Node\Expr\AssignOp\Mod;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    Modules\Media\Providers\MediaServiceProvider::class,
    Modules\Notification\Providers\NotificationServiceProvider::class,
];
