<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar HTTPS en producción
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Confiar en proxies (NGINX)
        $this->app['request']->server->set('HTTPS', 'on');

        // Directivas Blade para permisos
        $this->registerBladeDirectives();
    }

    /**
     * Registrar directivas Blade personalizadas para permisos
     */
    protected function registerBladeDirectives(): void
    {
        // @can('permission.slug') - Verificar si el usuario tiene un permiso específico
        Blade::if('can', function ($permission) {
            if (!auth()->check()) {
                return false;
            }

            $user = auth()->user();

            // Super admin tiene todos los permisos
            if ($user->isSuperAdmin()) {
                return true;
            }

            return $user->hasPermission($permission);
        });

        // @canany(['perm1', 'perm2']) - Verificar si el usuario tiene alguno de los permisos
        Blade::if('canany', function ($permissions) {
            if (!auth()->check()) {
                return false;
            }

            $user = auth()->user();

            // Super admin tiene todos los permisos
            if ($user->isSuperAdmin()) {
                return true;
            }

            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    return true;
                }
            }

            return false;
        });

        // @canall(['perm1', 'perm2']) - Verificar si el usuario tiene todos los permisos
        Blade::if('canall', function ($permissions) {
            if (!auth()->check()) {
                return false;
            }

            $user = auth()->user();

            // Super admin tiene todos los permisos
            if ($user->isSuperAdmin()) {
                return true;
            }

            foreach ($permissions as $permission) {
                if (!$user->hasPermission($permission)) {
                    return false;
                }
            }

            return true;
        });

        // @role('role-slug') - Verificar si el usuario tiene un rol específico
        Blade::if('role', function ($roleSlug) {
            if (!auth()->check()) {
                return false;
            }

            return auth()->user()->roles()->where('slug', $roleSlug)->exists();
        });

        // @hasanyrole(['role1', 'role2']) - Verificar si el usuario tiene alguno de los roles
        Blade::if('hasanyrole', function ($roleSlugs) {
            if (!auth()->check()) {
                return false;
            }

            return auth()->user()->roles()->whereIn('slug', $roleSlugs)->exists();
        });
    }
}
