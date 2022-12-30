<?php

namespace JesseGall\Resources\Concerns;

use JesseGall\Resources\Event;

/**
 * @mixin \JesseGall\Resources\Resource
 */
trait AutoloadRelations
{

    protected array $autoloadRelations = [];

    public function hookAutoloadRelations(): void
    {
        $this->listen(Event::INITIALIZED, fn() => $this->autoloadRelations());
        $this->listen(Event::HYDRATED, fn() => $this->autoloadRelations());
    }

    protected function autoloadRelations(): void
    {
        if (! $this->autoloadRelations) {
            return;
        }

        foreach ($this->autoloadRelations as $key => $class) {
            if (! $this->has($key)) {
                return;
            }

            $this->relation($key, $class);
        }
    }

}