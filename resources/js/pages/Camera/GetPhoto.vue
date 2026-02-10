<script setup lang="ts">
import { storePhoto } from '@/actions/App/Http/Controllers/StoreMediaController';
import { CameraIcon } from 'lucide-vue-next';
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
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';
import { On, Off, Dialog, Camera, Events} from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const photoDataUrl = ref('');

// Take a photo from camera
const takePhoto = async () => await Camera.getPhoto()

const handlePhotoTaken = (payload: any) => {
    axios.post(storePhoto.url(), { payload }).then((response) => {
        photoDataUrl.value = response.data.url;
    });
};
const handlePhotoCanceled = (payload: any) => {
  Dialog.toast('Photo canceled')
};

onMounted(() => {
    On(Events.Camera.PhotoTaken, handlePhotoTaken);
    On(Events.Camera.PhotoCancelled, handlePhotoCanceled);
});

onUnmounted(() => {
    Off(Events.Camera.PhotoTaken, handlePhotoTaken);
});
</script>

<template>
    <AppLayout title="Camera">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-blue-500 to-cyan-500 dark:from-blue-600 dark:to-cyan-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Camera
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Capture stunning photos with your device camera and unleash your creativity!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Camera Button Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                    <CardContent class="">
                        <Button
                            @click="takePhoto"
                            class="py-6 w-full bg-gradient-to-br from-blue-500 to-cyan-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <CameraIcon class="mr-2 size-7"/>
                            Take a Photo
                        </Button>
                    </CardContent>
                </Card>

                <!-- Photo Display Card -->
                <Card
                    v-if="photoDataUrl"
                    class="bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900/30 dark:to-yellow-900/30 border-2 border-amber-200 dark:border-amber-700 overflow-hidden"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center text-amber-900 dark:text-amber-100">
                            <CameraIcon class="mr-2 size-6"/>
                            Your Amazing Photo
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <img
                            :src="photoDataUrl"
                            class="w-full rounded-lg shadow-2xl border-2 border-white/50"
                            alt="Captured photo"
                        />
                    </CardContent>
                </Card>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>
