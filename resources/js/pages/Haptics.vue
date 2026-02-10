<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card, CardContent,
    CardDescription,
    CardHeader,
    CardTitle
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { SmartphoneIcon, Sparkles } from 'lucide-vue-next';
import { Device } from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const vibrate = async () => {
    await Device.vibrate();
};
</script>

<template>
    <AppLayout title="Haptics">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-violet-500 to-purple-500 dark:from-violet-600 dark:to-purple-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Haptic Feedback
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Feel the buzz! Experience tactile vibration feedback on your device!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Vibration Button Card -->
                <Card class="bg-gradient-to-br from-slate-100 to-gray-100 dark:from-slate-800 dark:to-gray-900 border-2 border-violet-200 dark:border-violet-800 ">
                    <CardContent class="">
                        <Button
                            @click="vibrate"
                            class="py-6 w-full bg-gradient-to-br from-violet-500 to-purple-500 text-white border-0 shadow transition-all text-xl font-semibold"
                        >
                            <SmartphoneIcon class="mr-2 size-7 animate-pulse"/>
                            Vibrate!
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
