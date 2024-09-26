<?php

namespace Wotz\PoeditorSync\Tests\Validation;

use Illuminate\Support\Facades\Validator;
use Wotz\PoeditorSync\Tests\TestCase;
use Wotz\PoeditorSync\Validation\HasMatchingPluralization;

class HasMatchingPluralizationRuleTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        config()->set('app.fallback_locale', 'en');
        config()->set('poeditor-sync.locales', ['en', 'nl', 'fr']);
    }

    /** @test */
    public function it_passes_if_there_is_no_pluralization()
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
    public function it_passes_if_pluralization_without_ranges_matches()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some singular translation in English|Some plural translation in English',
                'nl' => 'Some singular translation in Dutch|Some plural translation in Dutch',
                'fr' => 'Some singular translation in French|Some plural translation in French',
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_passes_if_pluralization_with_ranges_matches()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => '{0} Some singular translation in English|[1, 10] Some plural translation in English|[10, *] Some more plural translation in English',
                'nl' => '{0} Some singular translation in Dutch|[1, 10] Some plural translation in Dutch|[10, *] Some more plural translation in Dutch',
                'fr' => '{0} Some singular translation in French|[1, 10] Some plural translation in French|[10, *] Some more plural translation in French',
            ],
        ]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_fails_if_pluralization_is_not_present_everywhere()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some singular translation in English|Some plural translation in English',
                'nl' => 'Some singular translation in Dutch',
                'fr' => 'Some singular translation in French|Some plural translation in French',
            ],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(['Missing pluralization in locale \'nl\''], $validator->errors()->all());

        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => 'Some translation in English',
                'nl' => 'Some singular translation in Dutch|Some plural translation in Duthc',
                'fr' => 'Some translation in French',
            ],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(['Unexpected pluralization in locale \'nl\''], $validator->errors()->all());
    }

    /** @test */
    public function it_fails_if_pluralization_ranges_do_not_match()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => '{0} Some singular translation in English|[1, 10] Some plural translation in English|[10, *] Some more plural translation in English',
                'nl' => '{0} Some singular translation in Dutch|[1, *] Some plural translation in Dutch',
                'fr' => '{0} Some singular translation in French|[1, 10] Some plural translation in French|[10, *] Some more plural translation in French',
            ],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(['Invalid pluralization in locale \'nl\''], $validator->errors()->all());
    }

    /** @test */
    public function it_fails_if_pluralization_order_does_not_match()
    {
        $validator = $this->getValidator([
            'some_translation_key' => [
                'en' => '{0} Some singular translation in English|[1, *] Some plural translation in English',
                'nl' => '[1, *] Some plural translation in Dutch|{0} Some singular translation in Dutch',
                'fr' => '{0} Some singular translation in French|[1, *] Some plural translation in French',
            ],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame(['Invalid pluralization in locale \'nl\''], $validator->errors()->all());
    }

    public function getValidator(array $data)
    {
        return Validator::make($data, [
            '*' => [
                new HasMatchingPluralization(),
            ],
        ]);
    }
}
