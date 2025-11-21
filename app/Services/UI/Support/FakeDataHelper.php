<?php

namespace App\Services\UI\Support;

/**
 * Fake Data Helper
 *
 * Utility class for generating fake data compatible with UI components
 */
class FakeDataHelper
{
    /**
     * Generate a fake email based on first and last name
     *
     * @param string|null $firstName Optional first name (will generate if null)
     * @param string|null $lastName Optional last name (will generate if null)
     * @return string Email address
     */
    public static function email(?string $firstName = null, ?string $lastName = null): string
    {
        $firstName = $firstName ?? fake()->firstName();
        $lastName = $lastName ?? fake()->lastName();

        // Remove accents and special characters for email
        $first = self::sanitizeForEmail($firstName);
        $last = self::sanitizeForEmail($lastName);

        return $first . '.' . $last . '@' . fake()->freeEmailDomain();
    }

    /**
     * Generate a fake full name
     *
     * @return array ['name' => 'John Doe', 'first_name' => 'John', 'last_name' => 'Doe']
     */
    public static function fullName(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'name' => "$firstName $lastName",
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];
    }

    /**
     * Generate a fake name with compatible email
     *
     * @return array ['name' => 'John Doe', 'email' => 'john.doe@example.com']
     */
    public static function nameWithEmail(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'name' => "$firstName $lastName",
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => self::email($firstName, $lastName),
        ];
    }

    /**
     * Generate a fake password
     *
     * @param int $minLength Minimum password length
     * @return string Password
     */
    public static function password(int $minLength = 8): string
    {
        return fake()->password($minLength);
    }

    /**
     * Generate fake user data for registration forms
     *
     * @param array $roles Available roles to choose from
     * @return array User data with name, email, password, and role
     */
    public static function userData(array $roles = ['user', 'admin', 'moderator']): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $password = self::password(8);

        return [
            'name' => "$firstName $lastName",
            'email' => self::email($firstName, $lastName),
            'password' => $password,
            'password_confirmation' => $password,
            'role' => fake()->randomElement($roles),
        ];
    }

    /**
     * Sanitize a string to make it safe for use in email addresses
     *
     * @param string $string Input string
     * @return string Sanitized string (lowercase, no special chars or accents)
     */
    public static function sanitizeForEmail(string $string): string
    {
        // Convert to lowercase
        $string = strtolower($string);

        // Remove accents using transliteration
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        // Remove any non-alphanumeric characters
        $string = preg_replace('/[^a-z0-9]/', '', $string);

        return $string;
    }

    /**
     * Generate fake phone number
     *
     * @return string Phone number
     */
    public static function phoneNumber(): string
    {
        return fake()->phoneNumber();
    }

    /**
     * Generate fake address
     *
     * @return string Address
     */
    public static function address(): string
    {
        return fake()->address();
    }

    /**
     * Generate fake company name
     *
     * @return string Company name
     */
    public static function company(): string
    {
        return fake()->company();
    }
}
