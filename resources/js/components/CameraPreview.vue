<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps<{
    active: boolean;
    facingMode?: 'user' | 'environment';
    onFrame?: (imageData: string) => void;
    frameInterval?: number;
}>();

const emit = defineEmits<{
    (e: 'started'): void;
    (e: 'stopped'): void;
    (e: 'error', message: string): void;
}>();

const videoRef = ref<HTMLVideoElement | null>(null);
const canvasRef = ref<HTMLCanvasElement | null>(null);
const stream = ref<MediaStream | null>(null);
const isActive = ref(false);
const errorMessage = ref('');

let frameLoopId: number | null = null;

const startCamera = async () => {
    if (isActive.value || !videoRef.value) return;

    try {
        errorMessage.value = '';

        const constraints: MediaStreamConstraints = {
            video: {
                facingMode: props.facingMode || 'environment',
                width: { ideal: 640 },
                height: { ideal: 480 }
            },
            audio: false
        };

        stream.value = await navigator.mediaDevices.getUserMedia(constraints);
        videoRef.value.srcObject = stream.value;
        await videoRef.value.play();

        isActive.value = true;
        emit('started');

        // Start frame capture loop
        startFrameLoop();

    } catch (err: any) {
        console.error('Camera error:', err);
        errorMessage.value = err.message || 'Failed to access camera';
        emit('error', errorMessage.value);
    }
};

const stopCamera = () => {
    // Stop frame loop
    if (frameLoopId !== null) {
        cancelAnimationFrame(frameLoopId);
        frameLoopId = null;
    }

    // Stop all tracks
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
    }

    // Clear video source
    if (videoRef.value) {
        videoRef.value.srcObject = null;
    }

    isActive.value = false;
    emit('stopped');
};

let lastFrameTime = 0;
const frameInterval = props.frameInterval || 200; // Default 5 fps (200ms)

const startFrameLoop = () => {
    const captureFrame = (timestamp: number) => {
        if (!isActive.value || !videoRef.value || !canvasRef.value || !props.onFrame) {
            frameLoopId = requestAnimationFrame(captureFrame);
            return;
        }

        // Throttle frame rate
        if (timestamp - lastFrameTime < frameInterval) {
            frameLoopId = requestAnimationFrame(captureFrame);
            return;
        }
        lastFrameTime = timestamp;

        const video = videoRef.value;
        const canvas = canvasRef.value;
        const ctx = canvas.getContext('2d');

        if (!ctx || video.readyState !== video.HAVE_ENOUGH_DATA) {
            frameLoopId = requestAnimationFrame(captureFrame);
            return;
        }

        // Set canvas size to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw video frame to canvas
        ctx.drawImage(video, 0, 0);

        // Convert to base64 JPEG (smaller than PNG)
        const imageData = canvas.toDataURL('image/jpeg', 0.7);

        // Send to callback
        props.onFrame(imageData);

        frameLoopId = requestAnimationFrame(captureFrame);
    };

    frameLoopId = requestAnimationFrame(captureFrame);
};

// Watch for active prop changes
watch(() => props.active, (newVal) => {
    if (newVal) {
        startCamera();
    } else {
        stopCamera();
    }
});

onMounted(() => {
    if (props.active) {
        startCamera();
    }
});

onUnmounted(() => {
    stopCamera();
});
</script>

<template>
    <div class="camera-preview relative">
        <!-- Video element (visible) -->
        <video
            ref="videoRef"
            class="w-full rounded-lg bg-black"
            autoplay
            playsinline
            muted
        ></video>

        <!-- Hidden canvas for frame capture -->
        <canvas ref="canvasRef" class="hidden"></canvas>

        <!-- Error overlay -->
        <div v-if="errorMessage" class="absolute inset-0 flex items-center justify-center bg-black/80 rounded-lg">
            <p class="text-red-400 text-center px-4">{{ errorMessage }}</p>
        </div>

        <!-- Loading overlay -->
        <div v-if="active && !isActive && !errorMessage" class="absolute inset-0 flex items-center justify-center bg-black/80 rounded-lg">
            <p class="text-white">Starting camera...</p>
        </div>
    </div>
</template>