<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class AvatarHelper
{
    /**
     * Generate avatar initials from a user's display name.
     *
     * The logic takes the first letter of up to the first two whitespace-separated
     * words in the name, uppercased, and concatenates them.
     *
     * Examples:
     *   "Alice"           → "A"
     *   "John Doe"        → "JD"
     *   "Maria Clara Paz" → "MC"
     *   "Bob  Smith"      → "BS"  (double-space normalised by preg_split)
     *
     * @param  string  $name  The user's full name.
     * @return string One or two uppercase initials.
     */
    public static function getInitials(string $name): string
    {
        return collect(preg_split('/\s+/', trim($name)))
            ->filter()
            ->take(2)
            ->map(fn (string $part) => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }
}
