<div x-data="{
    isRecording: false,
    transcript: '',
    isProcessing: false,
    recognition: null,

    init() {
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.recognition.lang = 'ru-RU';

            this.recognition.onresult = (event) => {
                let finalTranscript = '';
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        finalTranscript += event.results[i][0].transcript;
                    }
                }
                if (finalTranscript) {
                    this.transcript += (this.transcript ? ' ' : '') + finalTranscript;
                }
            };

            this.recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                this.isRecording = false;
            };

            this.recognition.onend = () => {
                this.isRecording = false;
            };
        }
    },

    toggleRecording() {
        if (!this.recognition) {
            alert('Ваш браузер не поддерживает распознавание речи');
            return;
        }

        if (this.isRecording) {
            this.recognition.stop();
            this.isRecording = false;
        } else {
            this.transcript = '';
            this.recognition.start();
            this.isRecording = true;
        }
    },

    async applyCorrections() {
        if (!this.transcript.trim()) {
            return;
        }

        this.isProcessing = true;

        try {
            const response = await fetch('/api/voice-correction', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                },
                body: JSON.stringify({
                    photo_batch_id: @js($getRecord()?->id),
                    corrections: this.transcript
                })
            });

            const data = await response.json();

            if (data.success) {
                // Update form fields via Livewire
                if (data.updates) {
                    Object.keys(data.updates).forEach(key => {
                        const value = data.updates[key];
                        if (value !== null && value !== undefined) {
                            $wire.set('data.' + key, value);
                        }
                    });
                }

                this.transcript = '';
                $wire.dispatch('$refresh');
            } else {
                alert('Ошибка: ' + (data.error || 'Неизвестная ошибка'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ошибка при обработке');
        } finally {
            this.isProcessing = false;
        }
    }
}" class="mt-4">
    <div class="flex items-center gap-3">
        <button type="button"
                @click="toggleRecording()"
                :class="isRecording ? 'bg-red-600 animate-pulse' : 'bg-gray-700 hover:bg-gray-600'"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium transition-colors">
            <svg x-show="!isRecording" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
            </svg>
            <svg x-show="isRecording" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <rect x="6" y="6" width="12" height="12" rx="2"/>
            </svg>
            <span x-text="isRecording ? 'Остановить' : 'Голосовая правка'"></span>
        </button>

        <button type="button"
                x-show="transcript.trim()"
                @click="applyCorrections()"
                :disabled="isProcessing"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 disabled:bg-blue-800 disabled:opacity-50 rounded-lg text-white text-sm font-medium transition-colors">
            <svg x-show="!isProcessing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <svg x-show="isProcessing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-text="isProcessing ? 'Обработка...' : 'Применить'"></span>
        </button>
    </div>

    <div x-show="transcript || isRecording" class="mt-3">
        <div class="text-xs text-gray-400 mb-1">
            <span x-show="isRecording" class="text-red-400">● Запись...</span>
            <span x-show="!isRecording && transcript">Ваши правки:</span>
        </div>
        <div class="p-3 bg-gray-800 rounded-lg border border-gray-700 text-sm text-gray-300 min-h-[60px]">
            <span x-text="transcript || 'Говорите...'"></span>
        </div>
    </div>
</div>
