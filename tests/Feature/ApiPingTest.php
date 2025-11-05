<?php

it('api ping returns successful response', function () {
    $response = $this->get('/api/ping');

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'data' => [
            'status' => 'ok'
        ],
        'message' => 'API is running correctly'
    ]);
});