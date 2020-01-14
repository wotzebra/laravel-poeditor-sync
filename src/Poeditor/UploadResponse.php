<?php

namespace NextApps\PoeditorSync\Poeditor;

class UploadResponse
{
    /**
     * The upload response content.
     *
     * @var array
     */
    protected $content;

    /**
     * Create a new POEditor upload response instance.
     *
     * @param array $content
     *
     * @return void
     */
    public function __construct(array $content)
    {
        $this->content = $content;
    }

    /**
     * Get the amount of terms that have been added.
     *
     * @return int
     */
    public function getAddedTermsCount()
    {
        return (int) $this->content['result']['terms']['added'];
    }

    /**
     * Get the amount of terms that have been deleted.
     *
     * @return int
     */
    public function getDeletedTermsCount()
    {
        return (int) $this->content['result']['terms']['deleted'];
    }

    /**
     * Get the amount of translations that have been added.
     *
     * @return int
     */
    public function getAddedTranslationsCount()
    {
        return (int) $this->content['result']['translations']['added'];
    }

    /**
     * Get the amount of translations that have been updated.
     *
     * @return int
     */
    public function getUpdatedTranslationsCount()
    {
        return (int) $this->content['result']['translations']['updated'];
    }
}
