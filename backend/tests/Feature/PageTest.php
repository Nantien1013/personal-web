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

    public function test_home_has_nav_cards_and_socials(): void
    {
        $this->withoutVite();
        $html = $this->get('/')->assertOk()->getContent();
        foreach (['個人簡歷','收藏','單字庫','GitHub','Email'] as $t) {
            $this->assertStringContainsString($t, $html);
        }
    }

    public function test_resume_renders_sections(): void
    {
        $this->withoutVite();
        $html = $this->get('/resume')->assertOk()->getContent();
        foreach (['Education','Experience','Skills'] as $t) {
            $this->assertStringContainsString($t, $html);
        }
    }
}
