<script setup lang="ts">
import {
    getMediaFromDirectory,
    storeVideo,
} from '@/actions/App/Http/Controllers/StoreMediaController';
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
import axios from 'axios';
import {
    PlayIcon,
    Share2Icon,
    Trash2Icon,
    VideoIcon,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { camera, off, on, shareFile, Events } from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

interface Video {
    name: string;
    url: string;
    path: string;
    size: number;
    modified: number;
}

const maxDuration = ref<number | null>(null);
const videos = ref<Video[]>([]);
const currentlyPlayingPath = ref('');
const isLoadingVideos = ref(false);
const deletingVideos = ref<Set<string>>(new Set());

// Fetch all videos
const fetchVideos = async () => {
    isLoadingVideos.value = true;
    try {
        const response = await axios.post(getMediaFromDirectory.url('videos'));
        videos.value = response.data;
    } catch {
        // Error fetching videos
    } finally {
        isLoadingVideos.value = false;
    }
};

// Record video
const recordVideo = async () => {
    const recorder = camera.recordVideo();

    if (maxDuration.value) {
        recorder.maxDuration(maxDuration.value);
    }

    await recorder;
};

// Event handler for video recorded
const handleVideoRecorded = async (payload: any) => {
    try {
        await axios.post(storeVideo.url(), { payload });
        await fetchVideos();
    } catch {
        // Error storing video
    }
};

// Event handler for video cancelled
const handleVideoCancelled = () => {
    // Video recording was cancelled
};

// Play video
const playVideo = (video: Video) => {
    currentlyPlayingPath.value = video.path;
};

// Close video player
const closePlayer = () => {
    currentlyPlayingPath.value = '';
};

// Share video
const shareVideo = async (video: Video) => {
    if (video.path) {
        await shareFile(
            'Check this out!',
            'Check this out!',
            video.path
        );
    }
};

// Delete video
const deleteVideo = async (video: Video) => {
    deletingVideos.value.add(video.name);
    try {
        await axios.delete(`/api/video/recordings/${video.name}`);

        // If currently playing this video, close the player
        if (currentlyPlayingPath.value === video.path) {
            currentlyPlayingPath.value = '';
        }

        await fetchVideos();
    } catch {
        // Error deleting video
    } finally {
        deletingVideos.value.delete(video.name);
    }
};

// Format file size
const formatSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

// Format date
const formatDate = (timestamp: number) => {
    return new Date(timestamp * 1000).toLocaleString();
};

// Get currently playing video
const currentlyPlayingVideo = computed(() => {
    if (!currentlyPlayingPath.value) return null;
    return videos.value.find(v => v.path === currentlyPlayingPath.value);
});

onMounted(async () => {
    on(Events.Camera.VideoRecorded, handleVideoRecorded);
    on(Events.Camera.VideoCancelled, handleVideoCancelled);
    await fetchVideos();
});

onUnmounted(() => {
    off(Events.Camera.VideoRecorded, handleVideoRecorded);
    off(Events.Camera.VideoCancelled, handleVideoCancelled);
});
</script>

<template>
    <AppLayout title="Video Recorder">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-red-500 to-pink-500 dark:from-red-600 dark:to-pink-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Video Recorder
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Configure your settings and capture stunning videos with your device camera!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">

            <!-- Settings Card -->
            <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                <CardContent class="">
                    <div class="space-y-2">
                        <Label class="text-base font-semibold">Max Duration (seconds)</Label>
                        <Input
                            v-model.number="maxDuration"
                            type="number"
                            placeholder="Leave empty for no limit"
                            class="border-2"
                        />
                        <p class="text-sm text-muted-foreground">
                            Optional: Set a maximum recording duration in seconds. Use your camera app's built-in controls for quality and camera selection.
                        </p>
                    </div>
                </CardContent>
            </Card>
                <Card class="bg-gradient-to-br from-slate-100 to-gray-100 dark:from-slate-800 dark:to-gray-900 border-2 ">
                    <CardContent class="">
                        <Button
                            @click="recordVideo"
                            class="py-6 w-full bg-gradient-to-br from-pink-500 to-rose-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <VideoIcon class="mr-2 size-7" />
                            Start Recording
                        </Button>
                    </CardContent>
                </Card>

            <!-- Now Playing Card -->
            <Card v-if="currentlyPlayingVideo" class="bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 border-2 border-indigo-200 dark:border-indigo-700">
                <CardContent class=" space-y-4">
                    <div>
                        <CardTitle class="text-indigo-900 dark:text-indigo-100 flex items-center">
                            <PlayIcon class="mr-2 size-6"/>
                            Now Playing
                        </CardTitle>
                        <CardDescription class="text-indigo-700 dark:text-indigo-300">{{ currentlyPlayingVideo.name }}</CardDescription>
                    </div>

                    <video
                        :src="currentlyPlayingVideo.url"
                        controls
                        class="rounded-lg shadow-2xl w-full h-auto border-2 border-white/50"
                    >
                        Your browser does not support the video tag.
                    </video>

                    <div class="flex gap-2 justify-end">
                        <Button
                            @click="shareVideo(currentlyPlayingVideo)"
                            class="bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0"
                        >
                            <Share2Icon class="mr-2 size-4" />
                            Share
                        </Button>
                        <Button
                            @click="closePlayer"
                            variant="outline"
                        >
                            Close
                        </Button>
                    </div>
                </CardContent>
            </Card>

                <!-- Videos List Card -->
                <Card v-if="videos.length > 0" class="bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900/30 dark:to-cyan-900/30 border-2 border-teal-200 dark:border-teal-700 ">
                    <CardHeader>
                        <CardTitle class="text-teal-900 dark:text-teal-100 flex items-center">
                            <VideoIcon class="mr-2 size-6"/>
                            My Videos ({{ videos.length }})
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-for="video in videos"
                            :key="video.name"
                            class="space-y-3 rounded-lg border-2 border-white/50 bg-white/50 dark:bg-gray-800/50 p-4 backdrop-blur-sm"
                        >
                            <!-- Video Info -->
                            <div class="space-y-1">
                                <p class="truncate text-sm font-medium">
                                    {{ video.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ formatSize(video.size) }} •
                                    {{ formatDate(video.modified) }}
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="grid grid-cols-3 gap-2">
                                <Button
                                    @click="playVideo(video)"
                                    size="sm"
                                    class="w-full bg-gradient-to-r from-green-500 to-emerald-500 text-white border-0"
                                    :disabled="deletingVideos.has(video.name)"
                                >
                                    <PlayIcon class="mr-2 size-4" />
                                    Play
                                </Button>
                                <Button
                                    @click="shareVideo(video)"
                                    size="sm"
                                    class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0"
                                    :disabled="deletingVideos.has(video.name)"
                                >
                                    <Share2Icon class="mr-2 size-4" />
                                    Share
                                </Button>
                                <Button
                                    @click="deleteVideo(video)"
                                    size="sm"
                                    class="w-full bg-gradient-to-r from-red-500 to-pink-500 text-white border-0"
                                    :disabled="deletingVideos.has(video.name)"
                                >
                                    <Trash2Icon class="mr-2 size-4" />
                                    Delete
                                </Button>
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
