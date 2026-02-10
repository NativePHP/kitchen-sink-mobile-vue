<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { ref } from 'vue';
import { Network } from '#nativephp';
import { SatelliteDishIcon, TriangleAlertIcon, SparklesIcon } from 'lucide-vue-next';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const connected = ref(false);
const status = ref('');
const isExpensive = ref(false);
const isConstrained = ref(false);

const getNetwork = async () => {
    // Reset state
    connected.value = false;
    status.value = '';
    isExpensive.value = false;
    isConstrained.value = false;

    const result = await Network.status();

    if (result.connected) {
        connected.value = true;
        status.value = result.type || 'Unknown';
    } else {
        status.value = 'Disconnected';
    }
};
</script>

<template>
    <AppLayout title="Network">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-teal-500 to-blue-500 dark:from-teal-600 dark:to-blue-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Network Status
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Check your internet connection type and stay informed about your network!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Network Check Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                    <CardContent>
                        <Button
                            @click="getNetwork"
                            class="py-6 w-full bg-gradient-to-br from-teal-500 to-cyan-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <SatelliteDishIcon class="mr-2 size-7"/>
                            Check Network Status
                        </Button>
                    </CardContent>
                </Card>

                <!-- Connection Status Alert -->
                <Alert v-if="connected" class="border-purple-400 dark:border-purple-600 bg-gradient-to-br from-purple-100 to-fuchsia-100 dark:from-purple-900/30 dark:to-fuchsia-900/30 border-2 shadow-lg">
                    <SparklesIcon  class="size-6 text-purple-600 dark:text-purple-400" />
                    <AlertTitle class="text-purple-900 dark:text-purple-100 text-lg font-bold">You are connected!</AlertTitle>
                    <AlertDescription class="text-purple-700 dark:text-purple-300 text-base font-semibold">
                        Connected via: {{ status.toUpperCase() }} - Like a gangsta!
                    </AlertDescription>
                </Alert>

                <!-- Disconnected Status Alert -->
                <Alert v-else-if="status" class="border-red-400 dark:border-red-600 bg-gradient-to-br from-red-100 to-orange-100 dark:from-red-900/30 dark:to-orange-900/30 border-2 shadow-lg ">
                    <TriangleAlertIcon class="size-6 text-red-600 dark:text-red-400" />
                    <AlertTitle class="text-red-900 dark:text-red-100 text-lg font-bold">Connection Issue</AlertTitle>
                    <AlertDescription class="text-red-700 dark:text-red-300 text-base font-semibold">
                        You are {{ status.toUpperCase() }}
                    </AlertDescription>
                </Alert>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>
