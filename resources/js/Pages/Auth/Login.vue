<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    status: {
        type: String,
    },
});

const email = ref('');
const error = ref('');
const loggingIn = ref(false);

// Check if browser supports WebAuthn
const supportsWebAuthn = typeof PublicKeyCredential !== 'undefined';

const loginWithPasskey = async () => {
    if (!supportsWebAuthn) {
        error.value = 'Your browser does not support passkeys. Please use the magic link option instead.';
        return;
    }

    error.value = '';
    loggingIn.value = true;

    try {
        // Get authentication options from server
        const optionsResponse = await fetch(route('webauthn.login.options'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ email: email.value || undefined }),
        });

        if (!optionsResponse.ok) {
            throw new Error('Failed to get login options');
        }

        const options = await optionsResponse.json();
        
        // Convert base64url to Uint8Array
        options.challenge = Uint8Array.from(atob(options.challenge.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0));
        
        if (options.allowCredentials) {
            options.allowCredentials = options.allowCredentials.map(cred => ({
                ...cred,
                id: Uint8Array.from(atob(cred.id.replace(/-/g, '+').replace(/_/g, '/')), c => c.charCodeAt(0))
            }));
        }

        // Get the credential
        const credential = await navigator.credentials.get({ publicKey: options });

        // Convert credential to format expected by server
        const credentialJSON = {
            id: credential.id,
            type: credential.type,
            rawId: btoa(String.fromCharCode(...new Uint8Array(credential.rawId))),
            response: {
                clientDataJSON: btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON))),
                authenticatorData: btoa(String.fromCharCode(...new Uint8Array(credential.response.authenticatorData))),
                signature: btoa(String.fromCharCode(...new Uint8Array(credential.response.signature))),
                userHandle: credential.response.userHandle ? btoa(String.fromCharCode(...new Uint8Array(credential.response.userHandle))) : null,
            },
        };

        // Authenticate
        const loginResponse = await fetch(route('webauthn.login'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(credentialJSON),
        });

        if (!loginResponse.ok) {
            throw new Error('Authentication failed');
        }

        // Redirect to dashboard
        router.visit(route('dashboard'));
    } catch (err) {
        console.error('Passkey login error:', err);
        error.value = err.message || 'Failed to sign in with passkey. Please try again or use a magic link.';
        loggingIn.value = false;
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-4 text-sm text-gray-600">
            Sign in with your passkey for secure, password-free authentication.
        </div>

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div v-if="error" class="mb-4 text-sm font-medium text-red-600">
            {{ error }}
        </div>

        <form @submit.prevent="loginWithPasskey">
            <div>
                <InputLabel for="email" value="Email (optional)" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="email"
                    autofocus
                    autocomplete="username webauthn"
                />

                <p class="mt-1 text-xs text-gray-500">
                    Providing your email helps identify your passkey
                </p>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <Link
                    :href="route('magic-link.create')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Use magic link instead
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': loggingIn }"
                    :disabled="loggingIn || !supportsWebAuthn"
                >
                    Sign in with Passkey
                </PrimaryButton>
            </div>
        </form>

        <div v-if="!supportsWebAuthn" class="mt-4 rounded-md bg-yellow-50 p-4">
            <p class="text-sm text-yellow-800">
                Your browser does not support passkeys. Please 
                <Link
                    :href="route('magic-link.create')"
                    class="font-medium underline hover:text-yellow-900"
                >
                    use a magic link
                </Link>
                to sign in.
            </p>
        </div>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
                Don't have an account?
                <Link
                    :href="route('register')"
                    class="font-medium text-indigo-600 hover:text-indigo-500"
                >
                    Register
                </Link>
            </p>
        </div>
    </GuestLayout>
</template>
