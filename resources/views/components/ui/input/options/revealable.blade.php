<x-ui.input.options.button
    x-data="{
        revealed: false,
        toggleReveal() {
            const input = $el.closest('[data-slot=input-actions]').parentElement.querySelector('input[data-control-id=input]');
            if (!input) return;
            
            this.revealed = !this.revealed;
            input.type = this.revealed ? 'text' : 'password';
        }
    }"
    x-on:click="toggleReveal()"
    x-bind:data-slot-revealed="revealed"
    x-bind:aria-label="revealed ? 'Hide password' : 'Show password'"
    x-bind:title="revealed ? 'Hide password' : 'Show password'"
>     
    <i class="ph"
       x-bind:class="revealed ? 'ph-eye-slash' : 'ph-eye'"
       aria-hidden="true"
       data-slot="icon"></i>
</x-ui.input.options.button>
