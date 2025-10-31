<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    subscription: Object,
});

const processing = ref(false);

const subscribe = async () => {
    processing.value = true;
    
    try {
        const response = await fetch(route('subscription.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        
        const data = await response.json();
        
        if (data.url) {
            window.location.href = data.url;
        }
    } catch (error) {
        console.error('Error creating subscription:', error);
        processing.value = false;
    }
};

const cancelSubscription = () => {
    if (confirm('Are you sure you want to cancel your subscription?')) {
        router.delete(route('subscription.cancel'));
    }
};

const resumeSubscription = () => {
    router.post(route('subscription.resume'));
};
</script>

<template>
    <Head title="Subscription" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Subscription
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Active Subscription -->
                        <div v-if="subscription.subscribed" class="space-y-4">
                            <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                                <h3 class="text-lg font-semibold text-green-800">
                                    Active Subscription
                                </h3>
                                <p class="mt-2 text-green-700">
                                    Status: {{ subscription.status }}
                                </p>
                                <p v-if="subscription.ends_at" class="text-sm text-green-600">
                                    Ends at: {{ new Date(subscription.ends_at).toLocaleDateString() }}
                                </p>
                            </div>

                            <!-- Cancel or Resume buttons -->
                            <div class="flex gap-3">
                                <button
                                    v-if="subscription.status === 'active'"
                                    @click="cancelSubscription"
                                    type="button"
                                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
                                >
                                    Cancel Subscription
                                </button>
                                
                                <button
                                    v-if="subscription.status === 'canceled' && subscription.ends_at"
                                    @click="resumeSubscription"
                                    type="button"
                                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500"
                                >
                                    Resume Subscription
                                </button>
                            </div>
                        </div>

                        <!-- No Subscription -->
                        <div v-else class="space-y-4">
                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-6">
                                <h3 class="text-2xl font-bold text-gray-900">
                                    Monthly Subscription
                                </h3>
                                <p class="mt-2 text-3xl font-bold text-gray-900">
                                    $9.99<span class="text-base font-normal text-gray-600">/month</span>
                                </p>
                                <ul class="mt-4 space-y-2 text-gray-700">
                                    <li class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Access to all premium features
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Cancel anytime
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Secure payment via Stripe
                                    </li>
                                </ul>
                            </div>

                            <PrimaryButton
                                @click="subscribe"
                                :disabled="processing"
                                class="w-full justify-center"
                            >
                                {{ processing ? 'Processing...' : 'Subscribe Now' }}
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
