<?php

namespace Wotz\PoeditorSync\Tests\Validation;

use Illuminate\Support\Facades\Validator;
use Wotz\PoeditorSync\Tests\TestCase;
use Wotz\PoeditorSync\Validation\HasMatchingReplacements;

class HasMatchingReplacementsRuleTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        config()->set('app.fallback_locale', 'en');
        config()->set('poeditor-sync.locales', ['en', 'nl', 'fr']);
    }

    /** @test */
    public function it_passes_if_there_are_no_replacements()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some translation in English',
                'nl' => 'Some translation in Dutch',
                'fr' => 'Some translation in French',
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_passes_if_replacements_match_exact()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some translation :foo in English',
                'nl' => 'Some translation :foo in Dutch',
                'fr' => 'Some translation :foo in French',
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_passes_if_replacements_match_with_different_casing()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some translation :foo in English',
                'nl' => 'Some translation :Foo in Dutch',
                'fr' => 'Some translation :FOO in French',
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_passes_if_replacements_match_in_different_order()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some :foo translation :bar in English',
                'nl' => 'Some :bar translation :foo in Dutch',
                'fr' => 'Some :foo translation :bar in French',
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_passes_if_replacements_match_even_if_replacements_are_present_multiple_times()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some translation :foo in English',
                'nl' => 'Some :foo translation :foo in Dutch',
                'fr' => 'Some :foo translation :foo in :foo French',
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_fails_if_replacement_is_missing()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some translation :foo in English',
                'nl' => 'Some translation in Dutch',
                'fr' => 'Some translation :foo in French',
            ],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(['Missing replacement key \':foo\' in nl'], $validator->errors()->all());
    }

    /** @test */
    public function it_fails_if_replacement_is_not_in_main_locale()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some translation in English',
                'nl' => 'Some translation :foo in Dutch',
                'fr' => 'Some translation :foo in French',
            ],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame([
            'Unexpected replacement key \':foo\' in nl',
            'Unexpected replacement key \':foo\' in fr',
        ], $validator->errors()->all());
    }

    public function getValidator(array $data)
    {
        return Validator::make($data, [
            '*' => [
                new HasMatchingReplacements(),
            ],
        ]);
    }
}
