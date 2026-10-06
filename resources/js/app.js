import './bootstrap';

// O Livewire traz o próprio Alpine: importar o "alpinejs" à parte subiria dois.
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import 'cally';

window.Alpine = Alpine;

Livewire.start();
