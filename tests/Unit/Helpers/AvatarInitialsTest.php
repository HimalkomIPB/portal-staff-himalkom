<?php

/**
 * Property-Based Tests: Avatar Initials dari Nama Pengguna
 *
 * Validates: Requirements 2.6
 *
 * Properti 4: Untuk sembarang nama pengguna yang terdiri dari satu atau lebih
 * kata, fungsi pembuat inisial avatar harus menghasilkan string yang terdiri
 * dari huruf kapital pertama dari kata pertama dan (jika ada) kata kedua.
 */

use App\Helpers\AvatarHelper;

// ---------------------------------------------------------------------------
// Dataset-driven property tests (arbitrary inputs)
// ---------------------------------------------------------------------------

dataset('single_word_names', [
    'lowercase' => ['alice', 'A'],
    'uppercase' => ['ALICE', 'A'],
    'mixed case' => ['Alice', 'A'],
    'single char' => ['X', 'X'],
    'single char low' => ['x', 'X'],
]);

dataset('two_word_names', [
    'typical' => ['John Doe', 'JD'],
    'lowercase words' => ['john doe', 'JD'],
    'mixed case words' => ['Maria clara', 'MC'],
]);

dataset('long_names', [
    'three words' => ['Maria Clara Souza', 'MC'],
    'four words' => ['Ana Paula Santos Lima', 'AP'],
    'five words' => ['Jose Carlos da Silva Junior', 'JC'],
]);

dataset('double_space_names', [
    'double space between words' => ['Bob  Smith', 'BS'],
    'leading spaces' => ['  Carol  White', 'CW'],
    'triple space' => ['Dave   Jones', 'DJ'],
    'tabs as whitespace' => ["Eve\tFox", 'EF'],
]);

// ---------------------------------------------------------------------------
// Test: single-word names produce exactly one uppercase initial
// ---------------------------------------------------------------------------

it(
    'returns one uppercase initial for a single-word name',
    function (string $name, string $expected) {
        $result = AvatarHelper::getInitials($name);

        expect($result)
            ->toBe($expected)
            ->toHaveLength(1);
    }
)->with('single_word_names');

// ---------------------------------------------------------------------------
// Test: two-word names produce exactly two uppercase initials
// ---------------------------------------------------------------------------

it(
    'returns two uppercase initials for a two-word name',
    function (string $name, string $expected) {
        $result = AvatarHelper::getInitials($name);

        expect($result)
            ->toBe($expected)
            ->toHaveLength(2);
    }
)->with('two_word_names');

// ---------------------------------------------------------------------------
// Test: long multi-word names only take the first two words
// ---------------------------------------------------------------------------

it(
    'returns only first two initials for long multi-word names',
    function (string $name, string $expected) {
        $result = AvatarHelper::getInitials($name);

        expect($result)
            ->toBe($expected)
            ->toHaveLength(2);
    }
)->with('long_names');

// ---------------------------------------------------------------------------
// Test: multiple/irregular whitespace is normalised correctly
// ---------------------------------------------------------------------------

it(
    'normalises double (or irregular) spaces and still returns correct initials',
    function (string $name, string $expected) {
        $result = AvatarHelper::getInitials($name);

        expect($result)->toBe($expected);
    }
)->with('double_space_names');

// ---------------------------------------------------------------------------
// Universal property: output is always 1–2 uppercase letters
// ---------------------------------------------------------------------------

it(
    'always returns 1 or 2 uppercase characters regardless of input',
    function (string $name) {
        $result = AvatarHelper::getInitials($name);

        expect($result)
            ->toMatch('/^[A-Z]{1,2}$/')
            ->not->toBeEmpty();
    }
)->with([
    'Alice',
    'John Doe',
    'Maria Clara Souza',
    'Bob  Smith',
    'X',
    'a b c d e f',
    'lowercase name',
    "Tab\tSeparated",
]);

// ---------------------------------------------------------------------------
// Universal property: initials are always uppercase regardless of input case
// ---------------------------------------------------------------------------

it(
    'always uppercases initials even when name is all lowercase',
    function (string $name, string $expected) {
        $result = AvatarHelper::getInitials($name);

        expect($result)->toBe(strtoupper($result));
        expect($result)->toBe($expected);
    }
)->with([
    ['alice wonder', 'AW'],
    ['bob', 'B'],
    ['carol anne smith', 'CA'],
]);
