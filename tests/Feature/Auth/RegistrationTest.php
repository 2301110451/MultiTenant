<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(403);
});

test('new users can not register from central host', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertStatus(403);
});
