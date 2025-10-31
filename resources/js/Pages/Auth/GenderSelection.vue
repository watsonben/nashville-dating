<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    gender: '',
});

const submit = () => {
    form.post(route('gender.store'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Select Gender" />

        <div class="mb-4 text-sm text-gray-600">
            Please select your gender to complete your profile.
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="gender" value="Gender" />

                <div class="mt-2 space-y-2">
                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="form.gender"
                            value="male"
                            class="mr-2 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-700">Male</span>
                    </label>

                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="form.gender"
                            value="female"
                            class="mr-2 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-700">Female</span>
                    </label>

                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="form.gender"
                            value="non-binary"
                            class="mr-2 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-700">Non-binary</span>
                    </label>

                    <label class="flex items-center">
                        <input
                            type="radio"
                            v-model="form.gender"
                            value="other"
                            class="mr-2 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-700">Other</span>
                    </label>
                </div>

                <InputError class="mt-2" :message="form.errors.gender" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Continue
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
