<?php

namespace Wotz\PoeditorSync\Poeditor;

class UploadResponse
{
    public function __construct(
        protected array $content
    ) {
    }

    public function getAddedTermsCount() : int
    {
        return (int) $this->content['result']['terms']['added'];
    }

    public function getDeletedTermsCount() : int
    {
        return (int) $this->content['result']['terms']['deleted'];
    }

    public function getAddedTranslationsCount() : int
    {
        return (int) $this->content['result']['translations']['added'];
    }

    public function getUpdatedTranslationsCount() : int
    {
        return (int) $this->content['result']['translations']['updated'];
    }
}
