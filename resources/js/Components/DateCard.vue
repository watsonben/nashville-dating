<script setup>
import { formatShortDate } from '@/utils/dateUtils';

const props = defineProps({
    date: {
        type: Date,
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['select']);

const handleClick = (option) => {
    emit('select', { date: props.date, option });
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105 hover:shadow-lg">
        <div class="bg-gradient-to-r from-pink-500 to-rose-500 p-4 text-white">
            <h3 class="text-xl font-semibold">
                {{ formatShortDate(date) }}
            </h3>
            <p class="text-sm opacity-90">Friday Night</p>
        </div>
        <div class="p-4 space-y-3">
            <div
                v-for="(option, index) in options"
                :key="index"
                class="border border-gray-200 rounded-md p-3 cursor-pointer transition-all hover:border-pink-500 hover:bg-pink-50"
                @click="handleClick(option)"
            >
                <h4 class="font-semibold text-gray-800">{{ option.title }}</h4>
                <p class="text-sm text-gray-600 mt-1">{{ option.description }}</p>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-xs text-gray-500">
                        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ option.time }}
                    </span>
                    <span class="text-xs text-gray-500">
                        <svg class="inline-block w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ option.location }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
