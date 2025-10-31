<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DateCard from '@/Components/DateCard.vue';
import { Head } from '@inertiajs/vue3';
import { getFridaysOfMonth } from '@/utils/dateUtils';
import { computed } from 'vue';

// Get all Fridays of the current month (computed to avoid recalculation)
const fridays = computed(() => getFridaysOfMonth());

// Sample date options for each Friday
const dateOptions = [
    {
        title: 'Coffee & Conversation',
        description: 'Meet at a cozy local coffee shop for casual conversation',
        time: '7:00 PM',
        location: 'Downtown Nashville'
    },
    {
        title: 'Dinner Date',
        description: 'Enjoy a nice dinner at a popular restaurant',
        time: '7:30 PM',
        location: 'The Gulch'
    },
    {
        title: 'Live Music Night',
        description: 'Experience Nashville\'s famous live music scene together',
        time: '8:00 PM',
        location: 'Broadway'
    }
];

const handleDateSelection = ({ date, option }) => {
    console.log('Selected:', date, option);
    // Future: Handle date selection logic here
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        This Month's Friday Dates
                    </h2>
                    <p class="text-gray-600">
                        Choose from three date options for each Friday
                    </p>
                </div>
                
                <div v-if="fridays.length === 0" class="text-center py-12">
                    <p class="text-gray-500">No Fridays available this month</p>
                </div>

                <div 
                    v-else
                    class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                >
                    <DateCard
                        v-for="friday in fridays"
                        :key="friday.getTime()"
                        :date="friday"
                        :options="dateOptions"
                        @select="handleDateSelection"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
