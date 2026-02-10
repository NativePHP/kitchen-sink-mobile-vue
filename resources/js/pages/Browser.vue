<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { GlobeIcon, SquareArrowOutUpRightIcon, RectangleHorizontalIcon, ShieldCheckIcon } from 'lucide-vue-next';
import { Browser } from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const openInApp = async () => {
    await Browser.inApp('https://nativephp.com/mobile');
};

const openSystem = async () => {
    await Browser.open('https://nativephp.com');
};
</script>

<template>
    <AppLayout title="Browser">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-sky-500 to-blue-500 dark:from-sky-600 dark:to-blue-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Browser
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Seamlessly browse the web with multiple browser options tailored to your needs!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- In-App Browser Section -->
                <Card class="bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900/30 dark:to-teal-900/30 border-2 border-cyan-200 dark:border-cyan-700">
                <CardHeader>
                    <CardTitle class="flex items-center text-cyan-900 dark:text-cyan-100">
                        <RectangleHorizontalIcon class="mr-2 size-6"/>
                        In-App Browser
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm font-medium">
                        Opens URLs in an embedded browser within your app. Uses Custom Tabs on Android and SFSafariViewController on iOS. Perfect for keeping users within your app experience while browsing external content.
                    </p>
                    <Button
                        @click="openInApp"
                        class="w-full py-6 bg-gradient-to-br from-cyan-500 to-teal-500 text-white border-0 shadow-lg transition-all font-semibold text-xl"
                    >
                        <GlobeIcon class="mr-2 size-7"/>
                        Open in In-App Browser
                    </Button>
                </CardContent>
            </Card>

            <!-- System Browser Section -->
            <Card class="bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 border-2 border-indigo-200 dark:border-indigo-700">
                <CardHeader>
                    <CardTitle class="flex items-center text-indigo-900 dark:text-indigo-100">
                        <SquareArrowOutUpRightIcon class="mr-2 size-6"/>
                        System Browser
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm font-medium">
                        Opens URLs in the device's default browser app. This completely leaves your app and switches to the external browser. Useful when you want users to have the full browser experience or when the content requires it.
                    </p>
                    <Button
                        @click="openSystem"
                        class="w-full py-6 bg-gradient-to-br from-indigo-500 to-purple-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                    >
                        <SquareArrowOutUpRightIcon class="mr-2 size-7"/>
                        Open in System Browser
                    </Button>
                </CardContent>
            </Card>

                <!-- Auth Browser Section -->
                <Card class="bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900/30 dark:to-green-900/30 border-2 border-emerald-200 dark:border-emerald-700">
                    <CardHeader>
                        <CardTitle class="flex items-center text-emerald-900 dark:text-emerald-100">
                            <ShieldCheckIcon class="mr-2 size-6"/>
                            Authentication Browser
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-sm font-medium">
                            Specialized browser for OAuth authentication flows. Automatically handles redirect URLs with the nativephp:// scheme. Perfect for integrating with services like WorkOS, Auth0, or any OAuth provider. The browser session is isolated for security.
                        </p>
                    </CardContent>
                </Card>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>
