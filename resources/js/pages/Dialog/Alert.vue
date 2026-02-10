<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle
} from '@/components/ui/card';

import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { Dialog, On, Off, Events } from '#nativephp';
import { BellIcon } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const openAlert = async () => {
    await Dialog.alert()
        .title('Alert')
        .message('This is an alert dialog.')
        .buttons(['OK', 'Cool', 'Cancel']);
};

const label = ref('');

const buttonPressed = (payload: any) => {
    label.value = payload.label;
};

onMounted(() => {
    On(Events.Alert.ButtonPressed, buttonPressed);
});

onUnmounted(() => {
    Off(Events.Alert.ButtonPressed, buttonPressed);
});

</script>

<template>
    <AppLayout title="Alert Dialog">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-rose-500 to-pink-500 dark:from-rose-600 dark:to-pink-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Alert Dialog
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Native alert dialogs with custom buttons and callbacks!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Alert Button Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                    <CardContent class="space-y-3 ">
                        <Button
                            @click="openAlert"
                            class="py-6 w-full bg-gradient-to-br from-rose-500 to-pink-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <BellIcon class="mr-2 size-7" />
                            Show Alert
                        </Button>
                    </CardContent>
                </Card>

                <!-- Result Card -->
                <div v-if="label">
                    <Card class="bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 border-2 border-purple-200 dark:border-purple-700">
                        <CardHeader>
                            <CardTitle class="text-purple-900 dark:text-purple-100 flex items-center">
                                <BellIcon class="mr-2 size-6"/>
                                Button Pressed
                            </CardTitle>
                            <CardDescription class="text-purple-700 dark:text-purple-300 text-base font-semibold">
                                You pressed button: {{ label }}
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </div>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>
