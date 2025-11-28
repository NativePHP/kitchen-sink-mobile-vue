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
import { BellIcon, LockOpenIcon } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { pushNotifications, on, off, Events, dialog } from '#nativephp';
import {sendPushNotification} from '@/actions/App/Http/Controllers/PushNotificationController';
import axios from 'axios';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const token = ref('');

const promptForPushNotifications = async () => {
    await pushNotifications.enroll();
};

const handlePushNotificationsToken = (payload: any) => {
    token.value = payload.token;
};

const sendNotification = async () => {
    const response = await axios.post(sendPushNotification.url(), {token: token.value})

    await dialog.toast(response.data.success ? 'Success' : 'Fail');
};

onMounted(() => {
    on(Events.PushNotification.TokenGenerated, handlePushNotificationsToken);
});

onUnmounted(() => {
    off(Events.PushNotification.TokenGenerated, handlePushNotificationsToken);
});
</script>

<template>
    <AppLayout title="Push Notifications">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-fuchsia-500 to-purple-500 dark:from-fuchsia-600 dark:to-purple-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Notifications
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Stay connected! Enable push notifications to never miss an update!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Buttons Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                    <CardContent class="space-y-3">
                        <Button
                            @click="promptForPushNotifications"
                            class="py-6 w-full bg-gradient-to-br from-purple-500 to-fuchsia-500 text-white border-0 shadow-lg transition-all text-base font-semibold"
                        >
                            <LockOpenIcon class="mr-2 size-7"/>
                            Request a Push Notification
                        </Button>

                        <Button
                            v-if="token"
                            @click="sendNotification"
                            class="py-6 w-full bg-gradient-to-br from-pink-500 to-rose-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <BellIcon class="mr-2 size-7"/>
                            Send Test Notification
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
