<?php // backend/tests/Feature/PageTest.php
namespace Tests\Feature;

use Tests\TestCase;

class PageTest extends TestCase
{
    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('個人網站');
    }
}
