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
import { MapPinIcon } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { On, Off, Geolocation, Events } from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const result = ref('');

const checkPermissions = async () => {
    result.value = 'Checking permissions...';
    await Geolocation.checkPermissions();
};

const requestPermission = async () => {
    result.value = 'Requesting permissions...';
    await Geolocation.requestPermissions();
};

const getLocation = async () => {
    result.value = 'Getting location...';
    await Geolocation.getCurrentPosition();
};

const handlePermissionStatus = (payload: any) => {
    const { location, coarseLocation, fineLocation } = payload;
    result.value = `Permission Status: Location=${location}, Coarse=${coarseLocation}, Fine=${fineLocation}`;
};

const handlePermissionRequest = (payload: any) => {
    const { location, coarseLocation, fineLocation, message, needsSettings } = payload;
    if (location === 'permanently_denied') {
        result.value = `Permissions permanently denied. ${message ?? 'Please enable location in Settings.'}`;
    } else {
        result.value = `Permission Request Result: Location=${location}, Coarse=${coarseLocation}, Fine=${fineLocation}`;
    }
};

const handleLocationReceived = (payload: any) => {
    const { success, latitude, longitude, accuracy, timestamp, provider, error } = payload;
    if (success) {
        result.value = `Latitude: ${latitude}, Longitude: ${longitude}, Accuracy: (±${accuracy}m)`;
    } else {
        result.value = `Location Error: ${error ?? 'Unknown error'}`;
    }
};

onMounted(() => {
    On(Events.Geolocation.PermissionStatusReceived, handlePermissionStatus);
    On(Events.Geolocation.PermissionRequestResult, handlePermissionRequest);
    On(Events.Geolocation.LocationReceived, handleLocationReceived);
});

onUnmounted(() => {
    Off(Events.Geolocation.PermissionStatusReceived, handlePermissionStatus);
    Off(Events.Geolocation.PermissionRequestResult, handlePermissionRequest);
    Off(Events.Geolocation.LocationReceived, handleLocationReceived);
});
</script>

<template>
    <AppLayout title="Geolocation">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-green-500 to-emerald-500 dark:from-green-600 dark:to-emerald-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Geolocation
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Pinpoint your location with GPS precision and explore the world around you!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Buttons Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                <CardContent class="space-y-3 ">
                    <Button
                        @click="checkPermissions"
                        class="py-6 w-full bg-gradient-to-br from-blue-500 to-indigo-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                    >
                        <MapPinIcon class="mr-2 size-7"/>
                        Check Permissions
                    </Button>

                    <Button
                        @click="requestPermission"
                        class="py-6 w-full bg-gradient-to-br from-purple-500 to-pink-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                    >
                        <MapPinIcon class="mr-2 size-7"/>
                        Request Permission
                    </Button>

                    <Button
                        @click="getLocation"
                        class="py-6 w-full bg-gradient-to-br from-emerald-500 to-teal-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                    >
                        <MapPinIcon class="mr-2 size-7"/>
                        Get Location
                    </Button>
                </CardContent>
            </Card>

                <!-- Result Card -->
                <Card v-if="result" class="bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900/30 dark:to-blue-900/30 border-2 border-cyan-200 dark:border-cyan-700 ">
                    <CardHeader>
                        <CardTitle class="text-cyan-900 dark:text-cyan-100 flex items-center">
                            <MapPinIcon class="mr-2 size-6"/>
                            Result
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="p-4 bg-white/50 dark:bg-gray-800/50 rounded-lg border-2 border-white/50 backdrop-blur-sm">
                            <p class="text-sm whitespace-pre-wrap font-semibold">{{ result }}</p>
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
