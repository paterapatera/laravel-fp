<?php

namespace App\Dashboard;

use App\Models\User;
use App\Support\Result;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginAttempt
{
    private const int WindowSeconds = 60;

    private const int FailureLimit = 5;

    /**
     * @return Result<User, string>
     */
    public function authenticate(string $email, string $password): Result
    {
        $now = time();
        $recentFailures = $this->recentFailures($email, $now);

        if (count($recentFailures) >= self::FailureLimit) {
            return Result::err('rejected');
        }

        $authenticated = Auth::guard('web')->attempt([
            'email' => $email,
            'password' => $password,
        ], false);

        if (! $authenticated) {
            $recentFailures[] = $now;

            Cache::put($this->cacheKey($email), $recentFailures, self::WindowSeconds);

            return Result::err('rejected');
        }

        $user = Auth::guard('web')->user();

        if (! $user instanceof User) {
            Auth::guard('web')->logout();

            return Result::err('rejected');
        }

        return Result::ok($user);
    }

    private function cacheKey(string $email): string
    {
        return 'login-failures:'.hash('sha256', $email);
    }

    /**
     * @return list<int>
     */
    private function recentFailures(string $email, int $now): array
    {
        $stored = Cache::get($this->cacheKey($email), []);

        if (! is_array($stored)) {
            return [];
        }

        $threshold = $now - self::WindowSeconds;
        $recent = [];

        foreach ($stored as $timestamp) {
            if (is_int($timestamp) && $timestamp >= $threshold) {
                $recent[] = $timestamp;
            }
        }

        return $recent;
    }
}
