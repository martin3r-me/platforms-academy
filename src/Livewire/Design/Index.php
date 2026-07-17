<?php

namespace Platform\Academy\Livewire\Design;

use Livewire\Component;

/**
 * Design-Referenz („Deck") als echte In-App-Seite: zeigt die Kern-Muster der
 * Academy-UI mit den echten --ui-Tokens, damit die Referenz nicht von der
 * ausgelieferten Oberfläche abweichen kann. Statisch/presentational.
 */
class Index extends Component
{
    public function render()
    {
        return view('academy::livewire.design.index')->layout('platform::layouts.app');
    }
}
