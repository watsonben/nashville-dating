<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatShortDate } from '@/utils/dateUtils';

const props = defineProps({
    show: {
        type: Boolean,
        required: true,
    },
    date: {
        type: Date,
        default: null,
    },
    option: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'book']);

const dayOfWeek = computed(() => {
    if (!props.date) return '';
    return props.date.toLocaleDateString('en-US', { weekday: 'long' });
});

const formattedDate = computed(() => {
    if (!props.date) return '';
    return formatShortDate(props.date);
});

const handleBook = () => {
    if (props.date && props.option) {
        // Create LMGTFY link with date details
        const searchQuery = `${props.option.title} in ${props.option.location} Nashville`;
        const lmgtfyUrl = `https://lmgtfy.com/?q=${encodeURIComponent(searchQuery)}`;
        window.open(lmgtfyUrl, '_blank');
        emit('book');
    }
};

const handleShare = async () => {
    const shareData = {
        title: `Date Night: ${props.option?.title}`,
        text: `Join me for ${props.option?.title} on ${formattedDate.value} at ${props.option?.time} in ${props.option?.location}`,
        url: window.location.href,
    };

    // Check if Web Share API is supported
    if (navigator.share) {
        try {
            await navigator.share(shareData);
        } catch (err) {
            // User cancelled sharing or error occurred
            if (err.name !== 'AbortError') {
                console.error('Error sharing:', err);
                copyToClipboard();
            }
        }
    } else {
        // Fallback to copying link
        copyToClipboard();
    }
};

const copyToClipboard = async () => {
    const text = `${props.option?.title} on ${formattedDate.value} at ${props.option?.time} in ${props.option?.location} - ${window.location.href}`;
    
    try {
        await navigator.clipboard.writeText(text);
        alert('Link copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy:', err);
        alert('Failed to copy link. Please copy manually.');
    }
};
</script>

<template>
    <Modal :show="show" @close="emit('close')" max-width="lg">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ option?.title }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ dayOfWeek }}, {{ formattedDate }}
                        </p>
                    </div>
                    <button
                        @click="emit('close')"
                        class="text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
                <p class="text-gray-700 text-base leading-relaxed">
                    {{ option?.description }}
                </p>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
                <div>
                    <div class="flex items-center text-gray-600 mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium">Time</span>
                    </div>
                    <p class="text-gray-900 font-semibold ml-7">{{ option?.time }}</p>
                </div>
                <div>
                    <div class="flex items-center text-gray-600 mb-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm font-medium">Location</span>
                    </div>
                    <p class="text-gray-900 font-semibold ml-7">{{ option?.location }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                <PrimaryButton
                    @click="handleBook"
                    class="flex-1 justify-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Book This Date
                </PrimaryButton>
                <SecondaryButton
                    @click="handleShare"
                    class="flex-1 justify-center"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    Share
                </SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
