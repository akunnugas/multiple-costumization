<?php

namespace Modules\Core\Livewire\Traits;

use Livewire\Attributes\On;

trait ChoicesMultiple
{
    public $choices = [];

    #[On('updated-multiple-select')]
    public function setChoicesMultipleValue($name, $values = [])
    {
        $this->choices[$name] = $values;
    }

    public function loadChoices()
    {
        if (!empty($this->choices)) {
            foreach ($this->data as $key => $item) {
                if (in_array($item['field'], array_keys($this->choices))) {
                    $this->data[$key]['values'] = $this->choices[$item['field']] ?? [];
                }
            }
        }
    }

    public function setMultipleChoiceValues($name, $values = [])
    {
        if (!empty($this->choices[$name])) {
            return;
        }

        $this->choices[$name] = $values;
    }
}
