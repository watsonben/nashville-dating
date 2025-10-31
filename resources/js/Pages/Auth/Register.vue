<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { base64UrlToUint8Array, arrayBufferToBase64 } from '@/utils/webauthn';

const form = useForm({
    name: '',
    email: '',
});

const error = ref('');
const registering = ref(false);

// Check if browser supports WebAuthn
const supportsWebAuthn = typeof PublicKeyCredential !== 'undefined';

const registerWithPasskey = async () => {
    if (!supportsWebAuthn) {
        error.value = 'Your browser does not support passkeys. Please use a modern browser.';
        return;
    }

    error.value = '';
    registering.value = true;

    try {
        // First, create the user account
        await new Promise((resolve, reject) => {
            form.post(route('register'), {
                onSuccess: () => resolve(),
                onError: () => reject(new Error('Registration failed')),
            });
        });

        // Then register the passkey
        const optionsResponse = await fetch(route('webauthn.register.options'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });

        if (!optionsResponse.ok) {
            throw new Error('Failed to get registration options');
        }

        const options = await optionsResponse.json();

        // Convert base64url to Uint8Array
        options.challenge = base64UrlToUint8Array(options.challenge);
        options.user.id = base64UrlToUint8Array(options.user.id);

        // Create the credential
        const credential = await navigator.credentials.create({ publicKey: options });

        // Convert credential to format expected by server
        const credentialJSON = {
            id: credential.id,
            type: credential.type,
            rawId: arrayBufferToBase64(credential.rawId),
            response: {
                clientDataJSON: arrayBufferToBase64(credential.response.clientDataJSON),
                attestationObject: arrayBufferToBase64(credential.response.attestationObject),
            },
        };

        // Register the credential
        const registerResponse = await fetch(route('webauthn.register'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(credentialJSON),
        });

        if (!registerResponse.ok) {
            throw new Error('Failed to register passkey');
        }

        // Redirect to gender selection
        router.visit(route('gender.create'));
    } catch (err) {
        console.error('Passkey registration error:', err);
        error.value = err.message || 'Failed to register passkey. Please try again.';
        registering.value = false;
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-4 text-sm text-gray-600">
            Create your account and set up a passkey for secure, password-free sign in.
        </div>

        <div v-if="error" class="mb-4 text-sm font-medium text-red-600">
            {{ error }}
        </div>

        <form @submit.prevent="registerWithPasskey">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': registering }"
                    :disabled="registering"
                >
                    Register with Passkey
                </PrimaryButton>
            </div>
        </form>

        <div v-if="!supportsWebAuthn" class="mt-4 text-sm text-red-600">
            Your browser does not support passkeys. Please use a modern browser like Chrome, Safari, or Edge.
        </div>
    </GuestLayout>
</template>
