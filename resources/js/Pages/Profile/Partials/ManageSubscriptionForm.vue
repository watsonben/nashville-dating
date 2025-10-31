<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { router } from '@inertiajs/vue3';
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
    if (confirm('Are you sure you want to cancel your subscription? You will still have access until the end of your billing period.')) {
        router.delete(route('subscription.cancel'));
    }
};

const resumeSubscription = () => {
    router.post(route('subscription.resume'));
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Manage Subscription
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                View and manage your monthly subscription ($9.99/month).
            </p>
        </header>

        <!-- Active Subscription -->
        <div v-if="subscription.subscribed" class="mt-6 space-y-4">
            <div class="rounded-lg border border-green-200 bg-green-50 p-4">
                <div class="flex items-start">
                    <svg class="mr-3 h-5 w-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-green-800">
                            Active Subscription
                        </h3>
                        <p class="mt-1 text-sm text-green-700">
                            Status: <span class="font-medium capitalize">{{ subscription.status }}</span>
                        </p>
                        <p v-if="subscription.ends_at" class="mt-1 text-sm text-green-600">
                            {{ subscription.status === 'canceled' ? 'Access ends' : 'Renews' }} on {{ new Date(subscription.ends_at).toLocaleDateString() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex gap-3">
                <DangerButton
                    v-if="subscription.status === 'active'"
                    @click="cancelSubscription"
                    type="button"
                >
                    Cancel Subscription
                </DangerButton>
                
                <SecondaryButton
                    v-if="subscription.status === 'canceled' && subscription.ends_at"
                    @click="resumeSubscription"
                    type="button"
                >
                    Resume Subscription
                </SecondaryButton>
            </div>
        </div>

        <!-- No Subscription -->
        <div v-else class="mt-6 space-y-4">
            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Monthly Subscription
                </h3>
                <p class="mt-2 text-2xl font-bold text-gray-900">
                    $9.99<span class="text-sm font-normal text-gray-600">/month</span>
                </p>
                <ul class="mt-3 space-y-2 text-sm text-gray-700">
                    <li class="flex items-center">
                        <svg class="mr-2 h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Access to all premium features
                    </li>
                    <li class="flex items-center">
                        <svg class="mr-2 h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Cancel anytime
                    </li>
                    <li class="flex items-center">
                        <svg class="mr-2 h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
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
    </section>
</template>
