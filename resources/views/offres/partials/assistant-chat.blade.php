<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6" x-data="assistantChat()">
    <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Assistant RH') }}</h3>

        <div class="space-y-4 mb-4 max-h-96 overflow-y-auto" x-ref="messages">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'text-right' : 'text-left'">
                    <div
                        :class="msg.role === 'user'
                            ? 'inline-block bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm max-w-[80%]'
                            : 'inline-block bg-gray-100 text-gray-900 rounded-lg px-4 py-2 text-sm max-w-[80%] whitespace-pre-wrap'"
                        x-html="msg.content"
                    ></div>
                </div>
            </template>
        </div>

        <div x-show="loading" class="flex items-center gap-2 text-sm text-gray-500 mb-4">
            <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span>{{ __('Réflexion en cours...') }}</span>
        </div>

        <div x-show="error" class="text-sm text-red-600 mb-4" x-text="error"></div>

        <form @submit.prevent="sendMessage" class="flex gap-2">
            <input
                type="text"
                x-model="message"
                placeholder="{{ __('Posez une question sur ce candidat...') }}"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                :disabled="loading"
            >
            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                :disabled="loading || !message.trim()"
            >
                {{ __('Envoyer') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function assistantChat() {
        return {
            messages: [],
            message: '',
            loading: false,
            error: null,

            init() {
                this.messages.push({
                    role: 'assistant',
                    content: '{{ $analyse->statut_analyse->value === 'completed' ? __("Bonjour ! Je suis votre assistant RH. Posez-moi des questions sur ce candidat, ses compétences, son score, ou ce que vous souhaitez savoir.") : __("L\'analyse de ce candidat n\'est pas encore terminée. Je ne peux pas répondre aux questions tant que l\'analyse n\'est pas complétée.") }}',
                });
            },

            async sendMessage() {
                if (!this.message.trim() || this.loading) return;

                const userMessage = this.message.trim();
                this.messages.push({ role: 'user', content: userMessage });
                this.message = '';
                this.loading = true;
                this.error = null;

                try {
                    const response = await fetch('{{ route("offres.analyses.assistant", [$offre, $analyse]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ message: userMessage }),
                    });

                    if (!response.ok) {
                        throw new Error('Une erreur est survenue.');
                    }

                    const data = await response.json();
                    this.messages.push({ role: 'assistant', content: data.response });

                    this.$nextTick(() => {
                        this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                    });
                } catch (e) {
                    this.error = e.message || 'Erreur de connexion. Veuillez réessayer.';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endpush
