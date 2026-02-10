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
import { BoltIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { Dialog } from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const message = ref('');

const showToast = async () => {
    await Dialog.toast(message.value);
};
</script>

<template>
    <AppLayout title="Toast">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-yellow-500 to-orange-500 dark:from-yellow-600 dark:to-orange-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Toast
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Pop up quick messages with style! Create custom toast notifications!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Toast Input Card -->
                <Card class="bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900/30 dark:to-yellow-900/30 border-2 border-amber-200 dark:border-amber-700 ">
                    <CardContent class="space-y-6 ">
                        <div class="space-y-2">
                            <Label class="text-base font-semibold">Message</Label>
                            <Input v-model="message" placeholder="Type your toast message" class="border-2" />
                        </div>

                        <Button
                            @click="showToast"
                            class="py-6 w-full bg-gradient-to-br from-yellow-500 to-orange-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <BoltIcon class="mr-2 size-7"/>
                            Show Toast
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
