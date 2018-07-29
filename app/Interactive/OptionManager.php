<?php

namespace App\Interactive;

use App\Commands\NewCommand;
use Illuminate\Support\Collection;
use App\InteractiveOptions\Editor;
use App\InteractiveOptions\Release;
use App\InteractiveOptions\CommitMessage;

class OptionManager
{
    /**
     * The interactive menu options.
     *
     * @var Collection
     */
    protected $interactiveMenuOptions;

    /**
     * Available Lambo config options.
     *
     * @var Collection
     */
    protected $availableConfigOptions;

    /**
     * QuestionPerformer constructor.
     *
     */
    public function __construct()
    {
        $this->hydrateAvailableConfigOptions();
        $this->hydrateInteractiveMenuOptions();
    }

    /**
     * Hydrate interactive menu options.
     *
     * @return void
     */
    public function hydrateInteractiveMenuOptions(): void
    {
        $this->interactiveMenuOptions = collect([
            [
                'key'   => 'release',
                'label' => 'The Laravel branch to use, dev or stable',
                'class' => Release::class,
            ],
            [
                'key'   => 'editor',
                'label' => 'Editor - to open project after installation',
                'class' => Editor::class,
            ],
            [
                'key'   => 'message',
                'label' => 'The commit message',
                'class' => CommitMessage::class,
            ],
        ]);
    }

    /**
     * Hydrate the available config options.
     *
     * @return void
     */
    protected function hydrateAvailableConfigOptions(): void
    {
        $this->availableConfigOptions = collect(config('lambo'))->keys();
    }

    /**
     * Get the interactive menu options.
     *
     * @return array
     */
    public function interactiveMenuOptions(): array
    {
        return $this->interactiveMenuOptions
            ->mapWithKeys(function ($item, $key) {
                return [ $item['key'] => $item['label'] ];
            })->all();
    }

    /**
     * Interactively performs the given option, by its key.
     *
     * @param string $optionKey
     * @param NewCommand $console
     * @return string
     */
    public function perform(string $optionKey, NewCommand $console): ?string
    {
        $option = $this->interactiveMenuOptions->firstWhere('key', $optionKey);

        $option = app($option['class'])->perform($console);

        if ($option->value() === null) {
            return null;
        }

        $this->setLamboConfig($option->key(), $option->value());

        return $option->value();
    }

    /**
     * Changes the key to value in Lambo config.
     *
     * @param string $key
     * @param $value
     * @return void
     */
    public function setLamboConfig(string $key, $value): void
    {
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        }

        config()->set("lambo.{$key}", $value);
    }
}
