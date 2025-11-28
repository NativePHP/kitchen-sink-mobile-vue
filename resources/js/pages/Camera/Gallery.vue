<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
} from '@/components/ui/carousel';
import {storeGalleryMedia, deleteGalleryMedia} from '@/actions/App/Http/Controllers/StoreMediaController'
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { ImagePlusIcon, ImagesIcon, VideoIcon, Trash2Icon, Share2Icon } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { gallery, on, off, shareFile, Events } from '#nativephp';
import axios from 'axios';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

interface MediaFile {
    path: string;
    url?: string;
    type: 'image' | 'video';
    name?: string;
}

const selectedMedia = ref<MediaFile[]>([]);
const deletingMedia = ref<Set<number>>(new Set());

const pickImages = async () => {
    const picker = gallery();
    picker.images().multiple().maxItems(5);
    await picker;
};

const pickVideos = async () => {
    const picker = gallery();
    picker.videos().multiple().maxItems(5);
    await picker;
};

const pickAllMedia = async () => {
    const picker = gallery();
    picker.all().multiple().maxItems(10);
    await picker;
};

const handleMediaSelected = async (payload: any) => {
    const { success, files } = payload;
    if (success && files && Array.isArray(files)) {
        // Extract file paths
        const filePaths = files.map((file: any) =>
            typeof file === 'string' ? file : file.path
        );

        try {
            // Store files via API (wrap in payload to match Android format)
            const response = await axios.post(storeGalleryMedia.url(), { payload: { files: filePaths } });

            if (response.data.status === 'success' && response.data.media) {
                // Add stored media to the list
                const newMedia: MediaFile[] = response.data.media.map((item: any) => ({
                    path: item.path,
                    url: item.url,
                    type: item.type,
                    name: item.relative_path
                }));
                selectedMedia.value = [...selectedMedia.value, ...newMedia];
            }
        } catch {
            // Error storing gallery media
        }
    }
};

const shareMedia = async (media: MediaFile) => {
    if (media.path) {
        await shareFile('Check this out!', 'Check this out!', media.path);
    }
};

const removeMedia = async (index: number) => {
    const media = selectedMedia.value[index];
    if (!media.name) return;

    deletingMedia.value.add(index);
    try {
        await axios.post(deleteGalleryMedia.url(), {data: {path: media.name}});
        selectedMedia.value.splice(index, 1);
    } catch {
        // Error deleting media
    } finally {
        deletingMedia.value.delete(index);
    }
};

onMounted(() => {
    on(Events.Gallery.MediaSelected, handleMediaSelected);
});

onUnmounted(() => {
    off(Events.Gallery.MediaSelected, handleMediaSelected);
});
</script>

<template>
    <AppLayout title="Gallery">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-purple-500 to-violet-500 dark:from-purple-600 dark:to-violet-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Gallery Picker
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Select images and videos from your device gallery and share them with the world!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Picker Buttons Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                    <CardContent class="grid grid-cols-3 gap-3 ">
                        <Button
                            @click="pickImages"
                            class="flex-col h-auto py-6 bg-gradient-to-br from-pink-400 to-rose-400 text-white border-0 shadow-lg transition-all"
                        >
                            <ImagesIcon class="size-8 mb-2"/>
                            <span class="text-sm font-semibold">Images</span>
                        </Button>
                        <Button
                            @click="pickVideos"
                            class="flex-col h-auto py-6 bg-gradient-to-br from-blue-400 to-indigo-400 text-white border-0 shadow-lg transition-all"
                        >
                            <VideoIcon class="size-8 mb-2"/>
                            <span class="text-sm font-semibold">Videos</span>
                        </Button>
                        <Button
                            @click="pickAllMedia"
                            class="flex-col h-auto py-6 bg-gradient-to-br from-emerald-400 to-teal-400 text-white border-0 shadow-lg transition-all"
                        >
                            <ImagePlusIcon class="size-8 mb-2"/>
                            <span class="text-sm font-semibold">All Media</span>
                        </Button>
                    </CardContent>
                </Card>

                <!-- Selected Media Display with Carousel -->
                <Card v-if="selectedMedia.length > 0" class="bg-gradient-to-br from-purple-100 to-purple-300 dark:from-purple-900/30 dark:to-indigo-900/30 border-2 border-purple-200 dark:border-purple-700">
                    <CardHeader>
                        <CardTitle class="flex items-center text-orange-900 dark:text-orange-100">
                            <ImagesIcon class="mr-2 size-6"/>
                            Selected Media ({{ selectedMedia.length }})
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Carousel class="w-full">
                            <CarouselContent>
                                <CarouselItem
                                    v-for="(media, index) in selectedMedia"
                                    :key="index"
                                >
                                    <div class="p-1">
                                        <div class="relative rounded-lg border-2 border-white/50 overflow-hidden shadow-xl">
                                            <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-900 flex items-center justify-center">
                                                <img
                                                    v-if="media.type === 'image'"
                                                    :src="media.url"
                                                    :alt="`Image ${index + 1}`"
                                                    class="w-full h-full object-cover"
                                                />
                                                <video
                                                    v-else
                                                    :src="media.url"
                                                    class="w-full h-full object-cover"
                                                    controls
                                                />
                                            </div>
                                            <div class="p-3 flex gap-2 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm">
                                                <Button
                                                    @click="shareMedia(media)"
                                                    size="sm"
                                                    class="flex-1 bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0"
                                                    :disabled="deletingMedia.has(index)"
                                                >
                                                    <Share2Icon class="mr-2 size-4"/>
                                                    Share
                                                </Button>
                                                <Button
                                                    @click="removeMedia(index)"
                                                    size="sm"
                                                    class="flex-1 bg-gradient-to-r from-red-500 to-pink-500 text-white border-0"
                                                    :disabled="deletingMedia.has(index)"
                                                >
                                                    <Trash2Icon class="mr-2 size-4"/>
                                                    {{ deletingMedia.has(index) ? 'Removing...' : 'Remove' }}
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </CarouselItem>
                            </CarouselContent>
                        </Carousel>
                    </CardContent>
                </Card>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>
