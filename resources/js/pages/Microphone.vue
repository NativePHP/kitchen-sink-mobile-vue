<script setup lang="ts">
import {
    getMediaFromDirectory,
    storeAudio,
} from '@/actions/App/Http/Controllers/StoreMediaController';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import axios from 'axios';
import {
    MicIcon,
    PauseIcon,
    PlayIcon,
    Share2Icon,
    StopCircleIcon,
    Trash2Icon,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import {
    Off,
    On,
    ShareFile,
    Microphone,
    Events
} from '#nativephp';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

// Recording interface
interface Recording {
    name: string;
    url: string;
    path: string;
    size: number;
    modified: number;
}

// Recording states
type RecordingState = 'idle' | 'recording' | 'paused';
const recordingState = ref<RecordingState>('idle');
const recordings = ref<Recording[]>([]);
const isLoadingRecordings = ref(false);
const deletingRecordings = ref<Set<string>>(new Set());
let statusInterval: number | null = null;

// Fetch all recordings
const fetchRecordings = async () => {
    isLoadingRecordings.value = true;
    try {
        const response = await axios.post(getMediaFromDirectory.url('audio'));
        recordings.value = response.data;
    } catch {
        // Error fetching recordings
    } finally {
        isLoadingRecordings.value = false;
    }
};

// Get status from native
const updateStatus = async () => {
    try {
        const result = await Microphone.getStatus();

        // Map native status to our state
        // Native returns {status: 'idle'|'recording'|'paused'}
        if (result.status === 'recording') {
            recordingState.value = 'recording';
        } else if (result.status === 'paused') {
            recordingState.value = 'paused';
        } else {
            recordingState.value = 'idle';
        }

    } catch {
        // Error getting audio status
    }
};

// Start recording
const startRecording = async () => {
    // Optimistically set state immediately for better UX
    recordingState.value = 'recording';

    await Microphone.record();
    await updateStatus();

    // Start polling status while recording
    if (statusInterval === null) {
        statusInterval = window.setInterval(() => {
            updateStatus();
        }, 1000);
    }
};

// Pause recording
const pauseRecording = async () => {
    // Optimistically set state immediately for better UX
    recordingState.value = 'paused';

    await Microphone.pause();
    await updateStatus();
};

// Resume recording
const resumeRecording = async () => {
    // Optimistically set state immediately for better UX
    recordingState.value = 'recording';

    await Microphone.resume();
    await updateStatus();
};

// Stop recording
const stopRecording = async () => {
    // Optimistically set state immediately for better UX
    recordingState.value = 'idle';

    await Microphone.stop();
    await updateStatus();

    // Stop polling when recording ends
    if (statusInterval !== null) {
        clearInterval(statusInterval);
        statusInterval = null;
    }
};

// Event handler for recording complete
const handleRecordingComplete = async (payload: any) => {
    try {
        await axios.post(storeAudio.url(), { payload });
        // Refresh the recordings list
        await fetchRecordings();
    } catch {
        // Error storing audio
    }
};

// Share audio file
const shareRecording = async (recording: Recording) => {
    if (recording.path) {
        await ShareFile(
            'Audio Recording',
            'Check out this recording!',
            recording.path,
        );
    }
};

// Delete recording
const deleteRecording = async (recording: Recording) => {
    deletingRecordings.value.add(recording.name);
    try {
        await axios.delete(`/api/audio/recordings/${recording.name}`);
        // Refresh the recordings list
        await fetchRecordings();
    } catch {
        // Error deleting recording
    } finally {
        deletingRecordings.value.delete(recording.name);
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

// Computed properties for UI state
const isRecording = computed(() => recordingState.value === 'recording');
const isPaused = computed(() => recordingState.value === 'paused');
const isIdle = computed(() => recordingState.value === 'idle');

onMounted(async () => {
    // Listen for recording complete event
    On(Events.Microphone.Recorded, handleRecordingComplete);
    await fetchRecordings();
});

onUnmounted(() => {
    // Clean up event listener
    Off(Events.Microphone.Recorded, handleRecordingComplete,);

    // Clear status polling interval
    if (statusInterval !== null) {
        clearInterval(statusInterval);
    }
});
</script>

<template>
    <AppLayout title="Audio Recorder">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-orange-500 to-amber-500 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Microphone
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Record crystal-clear audio with professional controls at your fingertips! It even works while your device is locked!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Main Recording Card -->
                <Card class="bg-gradient-to-br from-slate-100 to-gray-100 dark:from-slate-800 dark:to-gray-900 ">
                <CardContent class="space-y-4 ">
                    <!-- Status Display -->
                    <div class="flex items-center justify-center py-8">
                        <div v-if="isRecording" class="flex items-center gap-3">
                            <div class="relative">
                                <div
                                    class="size-5 animate-pulse rounded-full bg-red-500 shadow-lg shadow-red-500/50"
                                ></div>
                                <div
                                    class="absolute inset-0 size-5 animate-ping rounded-full bg-red-500"
                                ></div>
                            </div>
                            <span class="text-4xl font-black bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent"
                                >ON AIR</span
                            >
                        </div>
                        <div
                            v-else-if="isPaused"
                            class="flex items-center gap-3"
                        >
                            <div
                                class="size-5 rounded-full bg-yellow-500 shadow-lg shadow-yellow-500/50"
                            ></div>
                            <span class="text-4xl font-black bg-gradient-to-r from-yellow-600 to-orange-600 bg-clip-text text-transparent"
                                >PAUSED</span
                            >
                        </div>
                        <div v-else class="flex items-center gap-3">
                            <div class="size-5 rounded-full bg-gray-400 shadow-lg"></div>
                            <span class="text-4xl font-black text-gray-500 dark:text-gray-400"
                                >READY</span
                            >
                        </div>
                    </div>

                    <!-- Control Buttons -->
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Record Button -->
                        <Button
                            v-if="isIdle"
                            @click="startRecording"
                            class="col-span-2 py-6 bg-gradient-to-br from-orange-500 to-amber-500  text-white border-0 shadow-lg  transition-all text-xl font-semibold"
                        >
                            <MicIcon class="mr-2 size-7" />
                            Record
                        </Button>

                        <!-- Pause Button -->
                        <Button
                            v-if="isRecording"
                            @click="pauseRecording"
                            class="w-full py-6 bg-gradient-to-br from-yellow-500 to-orange-500  text-white border-0 shadow-lg  transition-all"
                        >
                            <PauseIcon class="mr-2 size-7" />
                            Pause
                        </Button>

                        <!-- Resume Button -->
                        <Button
                            v-if="isPaused"
                            @click="resumeRecording"
                            class="w-full py-6 bg-gradient-to-br from-green-500 to-emerald-500  text-white border-0 shadow-lg  transition-all"
                        >
                            <PlayIcon class="mr-2 size-7" />
                            Resume
                        </Button>

                        <!-- Stop Button -->
                        <Button
                            v-if="!isIdle"
                            @click="stopRecording"
                            class="w-full py-6 bg-gradient-to-br from-red-600 to-rose-600  text-white border-0 shadow-lg  transition-all"
                        >
                            <StopCircleIcon class="mr-2 size-7" />
                            Stop
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Loading State -->
            <Card v-if="isLoadingRecordings && recordings.length === 0">
                <CardContent class="py-8">
                    <div class="text-center text-muted-foreground">
                        <Spinner class="mx-auto mb-2 size-8" />
                        <p>Loading recordings...</p>
                    </div>
                </CardContent>
            </Card>

            <!-- Recordings List Card -->
            <Card v-else-if="recordings.length > 0" class="bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 border-2 border-purple-200 dark:border-purple-700">
                <CardHeader>
                    <CardTitle class="flex items-center text-purple-900 dark:text-purple-100">
                        <Spinner
                            v-if="isLoadingRecordings"
                            class="mr-2 size-6"
                        />
                        <PlayIcon v-else class="mr-2 size-6" />
                        Recordings ({{ recordings.length }})
                    </CardTitle>
                    <CardDescription class="text-purple-700 dark:text-purple-300">
                        Your saved audio recordings
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-for="recording in recordings"
                        :key="recording.name"
                        class="space-y-3 rounded-lg border-2 border-white/50 bg-white/50 dark:bg-gray-800/50 p-4 backdrop-blur-sm"
                    >
                        <!-- Recording Info -->
                        <div class="space-y-1">
                            <p class="truncate text-sm font-semibold">
                                {{ recording.name }}
                            </p>
                            <p class="text-xs text-muted-foreground font-medium">
                                {{ formatSize(recording.size) }} •
                                {{ formatDate(recording.modified) }}
                            </p>
                        </div>

                        <!-- Audio Player -->
                        <audio :src="recording.url" controls class="w-full">
                            Your browser does not support the audio element.
                        </audio>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2">
                            <Button
                                @click="shareRecording(recording)"
                                size="sm"
                                class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0"
                                :disabled="
                                    deletingRecordings.has(recording.name)
                                "
                            >
                                <Share2Icon class="mr-2 size-4" />
                                Share
                            </Button>
                            <Button
                                @click="deleteRecording(recording)"
                                size="sm"
                                class="w-full bg-gradient-to-r from-red-500 to-pink-500 text-white border-0"
                                :disabled="
                                    deletingRecordings.has(recording.name)
                                "
                            >
                                <Spinner
                                    v-if="
                                        deletingRecordings.has(recording.name)
                                    "
                                    class="mr-2 size-4"
                                />
                                <Trash2Icon v-else class="mr-2 size-4" />
                                Delete
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

                <!-- Empty State -->
                <Card v-else class="">
                    <CardContent class="py-8">
                        <div class="text-center text-muted-foreground">
                            <MicIcon class="mx-auto mb-2 size-12 opacity-50" />
                            <p>No recordings yet</p>
                            <p class="text-sm">
                                Start recording to create your first audio file
                            </p>
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
