<?php

it('joshua cagaanan can open the homepage successfully', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
