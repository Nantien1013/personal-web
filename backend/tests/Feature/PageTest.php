<?php // backend/tests/Feature/PageTest.php
namespace Tests\Feature;

use Tests\TestCase;

class PageTest extends TestCase
{
    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('個人網站');
    }

    public function test_layout_has_nav_and_theme_toggle(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('data-theme-toggle', $html);
        foreach (['首頁','簡歷','收藏','單字庫'] as $label) {
            $this->assertStringContainsString($label, $html);
        }
    }
}
