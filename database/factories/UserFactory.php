<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'recipient',
            'status' => 'active',
            'phone' => fake()->numerify('05########'),
            'phone_verified_at' => now(),
            'city' => fake()->city(),
        ];
    }

    /**
     * حساب غير مُفعّل البريد.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * مستخدم متبرع (donor).
     */
    public function donor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'donor',
        ]);
    }

    /**
     * مستخدم مستفيد (recipient).
     */
    public function recipient(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'recipient',
        ]);
    }

    /**
     * أدمن عادي.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * سوبر أدمن (صلاحيات كاملة).
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
        ]);
    }

    /**
     * حساب موقوف (suspended).
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_reason' => 'مخالفة الشروط',
        ]);
    }

    /**
     * رقم الهاتف غير موثّق (لاختبار middleware EnsurePhoneVerified).
     */
    public function phoneUnverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone_verified_at' => null,
        ]);
    }
}
