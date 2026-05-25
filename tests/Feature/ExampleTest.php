<?php

test('the web root is not exposed in the api-only backend', function (): void {
    $response = $this->get('/');

    $response->assertNotFound();
});
