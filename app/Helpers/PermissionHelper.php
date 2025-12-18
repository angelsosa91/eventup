<?php

if (!function_exists('user_can')) {
    /**
     * Verificar si el usuario actual tiene un permiso específico
     *
     * @param string $permission
     * @return bool
     */
    function user_can(string $permission): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Super admin tiene todos los permisos
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasPermission($permission);
    }
}

if (!function_exists('user_can_any')) {
    /**
     * Verificar si el usuario actual tiene alguno de los permisos especificados
     *
     * @param array $permissions
     * @return bool
     */
    function user_can_any(array $permissions): bool
    {
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
    }
}

if (!function_exists('user_can_all')) {
    /**
     * Verificar si el usuario actual tiene todos los permisos especificados
     *
     * @param array $permissions
     * @return bool
     */
    function user_can_all(array $permissions): bool
    {
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
    }
}

if (!function_exists('user_has_role')) {
    /**
     * Verificar si el usuario actual tiene un rol específico
     *
     * @param string $roleSlug
     * @return bool
     */
    function user_has_role(string $roleSlug): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->roles()->where('slug', $roleSlug)->exists();
    }
}

if (!function_exists('user_has_any_role')) {
    /**
     * Verificar si el usuario actual tiene alguno de los roles especificados
     *
     * @param array $roleSlugs
     * @return bool
     */
    function user_has_any_role(array $roleSlugs): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return auth()->user()->roles()->whereIn('slug', $roleSlugs)->exists();
    }
}

if (!function_exists('user_permissions')) {
    /**
     * Obtener todos los permisos del usuario actual
     *
     * @return \Illuminate\Support\Collection
     */
    function user_permissions()
    {
        if (!auth()->check()) {
            return collect([]);
        }

        $user = auth()->user();

        // Super admin tiene todos los permisos
        if ($user->isSuperAdmin()) {
            return \App\Models\Permission::all();
        }

        return $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }
}

if (!function_exists('user_permission_slugs')) {
    /**
     * Obtener todos los slugs de permisos del usuario actual
     *
     * @return array
     */
    function user_permission_slugs(): array
    {
        return user_permissions()->pluck('slug')->toArray();
    }
}

if (!function_exists('abort_unless_can')) {
    /**
     * Abortar con 403 si el usuario no tiene el permiso
     *
     * @param string $permission
     * @param string|null $message
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    function abort_unless_can(string $permission, ?string $message = null): void
    {
        if (!user_can($permission)) {
            abort(403, $message ?? 'No tienes permisos para realizar esta acción.');
        }
    }
}

if (!function_exists('abort_unless_any_permission')) {
    /**
     * Abortar con 403 si el usuario no tiene ninguno de los permisos
     *
     * @param array $permissions
     * @param string|null $message
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    function abort_unless_any_permission(array $permissions, ?string $message = null): void
    {
        if (!user_can_any($permissions)) {
            abort(403, $message ?? 'No tienes permisos para realizar esta acción.');
        }
    }
}

if (!function_exists('abort_unless_role')) {
    /**
     * Abortar con 403 si el usuario no tiene el rol
     *
     * @param string $roleSlug
     * @param string|null $message
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    function abort_unless_role(string $roleSlug, ?string $message = null): void
    {
        if (!user_has_role($roleSlug)) {
            abort(403, $message ?? 'No tienes el rol necesario para realizar esta acción.');
        }
    }
}
