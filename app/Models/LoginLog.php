<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'email',
        'ip_address',
        'user_agent',
        'status',
        'failure_reason',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Registrar un login exitoso
     */
    public static function logSuccess($user, $request)
    {
        return self::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id,
            'email' => $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'success',
            'logged_at' => now(),
        ]);
    }

    /**
     * Registrar un intento de login fallido
     */
    public static function logFailure($email, $request, $reason = 'Invalid credentials')
    {
        return self::create([
            'user_id' => null,
            'tenant_id' => null,
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'failed',
            'failure_reason' => $reason,
            'logged_at' => now(),
        ]);
    }
}
