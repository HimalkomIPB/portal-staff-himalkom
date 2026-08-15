<?php

test('public registration screen is disabled', function () {
    $response = $this->get('/register');

    $response->assertStatus(404);
});
