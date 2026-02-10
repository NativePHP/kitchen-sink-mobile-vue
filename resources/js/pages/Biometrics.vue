<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { FingerprintIcon, CheckCircle2Icon, AlertTriangleIcon } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { On, Off, Biometric, Events } from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const secure = ref(false);

const promptForBiometricID = async () => {
    await Biometric.prompt();
};

const handleBiometricAuth = (payload: any) => {
    const { success } = payload;
    secure.value = success;
};

onMounted(() => {
    On(Events.Biometrics.Completed, handleBiometricAuth);
});

onUnmounted(() => {
    Off(Events.Biometrics.Completed, handleBiometricAuth);
});
</script>

<template>
    <AppLayout title="Biometrics">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-teal-500 to-cyan-500 dark:from-teal-600 dark:to-cyan-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Biometrics
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Unlock secure access with your fingerprint or face recognition!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Scanner Button Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                    <CardContent class="">
                        <Button
                            @click="promptForBiometricID"
                            class="py-6 w-full bg-gradient-to-br from-teal-500 to-cyan-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <FingerprintIcon class="mr-2 size-7"/>
                            Request Biometric Access
                        </Button>
                    </CardContent>
                </Card>

                <div class="w-full pb-32">
                    <!-- Success State -->
                    <Alert v-if="secure" class="border-green-400 dark:border-green-600 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 border-2 shadow-lg">
                        <CheckCircle2Icon class="size-6 text-green-600 dark:text-green-400" />
                        <AlertDescription class="text-green-800 dark:text-green-100 text-base font-semibold">
                            Your account is verified and ready to use!
                        </AlertDescription>
                    </Alert>

                    <!-- Warning State -->
                    <Alert v-else class="border-yellow-400 dark:border-yellow-600 bg-gradient-to-br from-yellow-100 to-orange-100 dark:from-yellow-900/30 dark:to-orange-900/30 border-2 shadow-lg border-l-4">
                        <AlertTriangleIcon class="size-6 text-yellow-600 dark:text-yellow-400" />
                        <AlertDescription class="text-yellow-800 dark:text-yellow-100 text-base font-semibold">
                            This is a SECURE AREA - please authenticate yourself to continue!
                        </AlertDescription>
                    </Alert>
                </div>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
    </AppLayout>
</template>
