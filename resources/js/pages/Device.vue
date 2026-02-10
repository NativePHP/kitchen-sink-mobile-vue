<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { SmartphoneIcon, ZapIcon } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';
import { Device, System } from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const deviceId = ref('');
const deviceInfo = ref<Record<string, any>>({});
const batteryInfo = ref<{ batteryLevel: number; isCharging: boolean }>({
    batteryLevel: 0,
    isCharging: false
});
const isIos = ref(false);
const isAndroid = ref(false);

const loadDeviceInfo = async () => {
    const idResult = await Device.getId();
    deviceId.value = idResult.id;

    const infoResult = await Device.getInfo();
    const infoString = infoResult.info;
    deviceInfo.value = typeof infoString === 'string' ? JSON.parse(infoString) : infoString;
};

const loadBatteryInfo = async () => {
    const result = await Device.getBatteryInfo();
    const infoString = result.info;
    batteryInfo.value = typeof infoString === 'string' ? JSON.parse(infoString) : infoString;
};

onMounted(async () => {
    await loadDeviceInfo();
    await loadBatteryInfo();

    // Load platform info
    isIos.value = await System.isIos();
    isAndroid.value = await System.isAndroid();

    // Poll battery info every 5 seconds
    setInterval(loadBatteryInfo, 5000);
});
</script>

<template>
    <AppLayout title="Device Info">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-slate-600 to-gray-600 dark:from-slate-700 dark:to-gray-700 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Device Info
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Discover everything about your device - from model info to battery status!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Device ID Card -->
                <Card class="bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 border-2 border-blue-200 dark:border-blue-700">
                <CardHeader>
                    <CardTitle class="text-blue-900 dark:text-blue-100 flex items-center">
                        <SmartphoneIcon class="mr-2 size-6"/>
                        Device ID
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex flex-col items-start justify-center space-y-2">
                        <p class="text-sm text-muted-foreground font-semibold">Your unique device id:</p>
                        <Badge class="text-xs font-mono bg-gradient-to-r from-blue-500 to-indigo-500 text-white border-0 px-3 py-1">{{ deviceId }}</Badge>
                    </div>

                    <div class="flex items-center justify-between pt-2 p-3 rounded-lg bg-white/50 dark:bg-gray-800/50">
                        <p class="text-sm font-semibold">Is iOS?</p>
                        <p class="text-sm font-bold">{{ isIos ? 'True' : 'False' }}</p>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-lg bg-white/50 dark:bg-gray-800/50">
                        <p class="text-sm font-semibold">Is Android?</p>
                        <p class="text-sm font-bold">{{ isAndroid ? 'True' : 'False' }}</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Device Information Card -->
            <Card class="bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 border-2 border-purple-200 dark:border-purple-700">
                <CardHeader>
                    <CardTitle class="text-purple-900 dark:text-purple-100">Device Specs</CardTitle>
                </CardHeader>
                <CardContent class="space-y-2">
                    <div
                        v-for="(value, key) in deviceInfo"
                        :key="key"
                        class="flex items-center justify-between p-3 rounded-lg bg-white/50 dark:bg-gray-800/50"
                    >
                        <p class="text-sm font-semibold">{{ key }}</p>
                        <p class="text-sm font-bold">{{ typeof value === 'boolean' ? (value ? 'true' : 'false') : value }}</p>
                    </div>
                </CardContent>
            </Card>

                <!-- Battery Info Card -->
                <Card class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900/30 dark:to-teal-900/30 border-2 border-emerald-200 dark:border-emerald-700">
                    <CardHeader>
                        <CardTitle class="text-emerald-900 dark:text-emerald-100 flex items-center">
                            <ZapIcon class="mr-2 size-6"/>
                            Battery Info
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-white/50 dark:bg-gray-800/50">
                            <p class="text-sm font-semibold">batteryLevel</p>
                            <p class="text-lg font-black">{{ Math.round(batteryInfo.batteryLevel * 100) }}%</p>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-white/50 dark:bg-gray-800/50">
                            <p class="text-sm font-semibold">isCharging</p>
                            <p class="text-sm font-bold">{{ batteryInfo.isCharging ? 'true' : 'false' }}</p>
                        </div>

                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-8 mt-4 border-2 border-white/50 shadow-inner">
                            <div
                                class="bg-gradient-to-r from-emerald-500 to-teal-500 h-8 rounded-full flex items-center justify-center transition-all duration-300 shadow-lg"
                                :style="{ width: `${batteryInfo.batteryLevel * 100}%` }"
                            >
                                <ZapIcon v-if="batteryInfo.isCharging" class="size-5 text-white animate-pulse" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>
