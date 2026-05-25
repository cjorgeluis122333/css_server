<?php

test('the web root remains unavailable for this api-only project', function (): void {
    $response = $this->get('/');

    $response->assertNotFound();
});
