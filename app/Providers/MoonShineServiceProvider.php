<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\CallResource;
use App\MoonShine\Resources\ContactResource;
use App\MoonShine\Resources\OrderResource;
use App\MoonShine\Pages\Dashboard;
use App\MoonShine\Pages\Calls;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     *
     */
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                CallResource::class,
                ContactResource::class,
                OrderResource::class,
            ])
            ->pages([
                Dashboard::class,
                ...$config->getPages(),
                Calls::class,
            ])
        ;
    }
}

