<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { LockKeyholeIcon, KeyIcon, Trash2Icon } from 'lucide-vue-next';
import { ref } from 'vue';
import { SecureStorage, Dialog } from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const key = ref('');
const value = ref('');
const retrieveKey = ref('');
const deleteKey = ref('');

const setSecureValue = async () => {
    if (!key.value || !value.value) {
        await Dialog.alert('Attention!', 'Please provide both key and value');
        return;
    }

    try {
        await SecureStorage.set(key.value, value.value);
        await Dialog.alert('Stored!', `Successfully stored value for key: ${key.value}`);
        key.value = '';
        value.value = '';
    } catch (e: any) {
        await Dialog.alert('Error', `Error storing value: ${e.message}`);
    }
};

const getSecureValue = async () => {
    if (!retrieveKey.value) {
        await Dialog.alert('Attention!', 'Please provide a key to retrieve');
        return;
    }

    try {
        const result = await SecureStorage.get(retrieveKey.value);
        if (result.value) {
            await Dialog.alert('Decrypted', `Successfully retrieved value for ${retrieveKey.value}: ${result.value}`);
        } else {
            await Dialog.alert(`Error retrieving '${retrieveKey.value}'`, 'No value found.');
        }
    } catch (e: any) {
        await Dialog.alert('Error', `Error retrieving value: ${e.message}`);
    }
};

const deleteSecureValue = async () => {
    if (!deleteKey.value) {
        await Dialog.alert('Attention!', 'Please provide a key to delete');
        return;
    }

    try {
        await SecureStorage.set(deleteKey.value, null);
        await Dialog.alert('Success', `Successfully deleted value for key: ${deleteKey.value}`);
        deleteKey.value = '';
    } catch (e: any) {
        await Dialog.alert('Error', `Error deleting value: ${e.message}`);
    }
};
</script>

<template>
    <AppLayout title="Secure Storage">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-500 dark:from-amber-600 dark:to-orange-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Secure Storage
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Encrypted, military-grade storage for your sensitive data on device!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Store Secure Value Card -->
                <Card class="bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 border-2 border-green-200 dark:border-green-700">
                <CardHeader>
                    <CardTitle class="text-green-900 dark:text-green-100 flex items-center">
                        <LockKeyholeIcon class="mr-2 size-6"/>
                        Store Secure Value
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label class="text-base font-semibold">Key</Label>
                        <Input v-model="key" placeholder="Enter a key name" class="border-2" />
                    </div>

                    <div class="space-y-2">
                        <Label class="text-base font-semibold">Value</Label>
                        <Input v-model="value" placeholder="Enter the value to store securely" class="border-2" />
                    </div>

                    <Button
                        @click="setSecureValue"
                        class="py-6 w-full bg-gradient-to-br from-green-500 to-emerald-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                    >
                        <LockKeyholeIcon class="mr-2 size-7"/>
                        Store Secure Value
                    </Button>
                </CardContent>
            </Card>

            <!-- Retrieve Secure Value Card -->
            <Card class="bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900/30 dark:to-cyan-900/30 border-2 border-blue-200 dark:border-blue-700">
                <CardHeader>
                    <CardTitle class="text-blue-900 dark:text-blue-100 flex items-center">
                        <KeyIcon class="mr-2 size-6"/>
                        Retrieve Secure Value
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label class="text-base font-semibold">Key</Label>
                        <Input v-model="retrieveKey" placeholder="Enter the key to retrieve" class="border-2" />
                    </div>

                    <Button
                        @click="getSecureValue"
                        class="py-6 w-full bg-gradient-to-br from-blue-500 to-cyan-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                    >
                        <KeyIcon class="mr-2 size-7"/>
                        Retrieve Secure Value
                    </Button>
                </CardContent>
            </Card>

                <!-- Delete Secure Value Card -->
                <Card class="bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900/30 dark:to-rose-900/30 border-2 border-red-200 dark:border-red-700 pb-32">
                    <CardHeader>
                        <CardTitle class="text-red-900 dark:text-red-100 flex items-center">
                            <Trash2Icon class="mr-2 size-6"/>
                            Delete Secure Value
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label class="text-base font-semibold">Key</Label>
                            <Input v-model="deleteKey" placeholder="Enter the key to delete" class="border-2" />
                        </div>

                        <Button
                            @click="deleteSecureValue"
                            class="py-6 w-full bg-gradient-to-br from-red-600 to-rose-600 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <Trash2Icon class="mr-2 size-7"/>
                            Delete Secure Value
                        </Button>
                    </CardContent>
                </Card>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>
