<?php

use Laravel\Lumen\Testing\DatabaseTransactions;

class HomeTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->get('/');

        $this->assertEquals(
            "\"Hello world\"", $this->response->getContent()
        );
    }
}
