<?php

namespace NextApps\PoeditorSync\Poeditor;

class UploadResponse
{
    protected array $content;

    public function __construct(array $content)
    {
        $this->content = $content;
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
