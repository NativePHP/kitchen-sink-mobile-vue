<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { On, Off } from '#nativephp';
import {
    BoxIcon,
    PlayIcon,
    StopCircleIcon,
    MapPinIcon,
    TrashIcon,
    AlertCircleIcon,
    CheckCircle2Icon,
    LayersIcon,
    ImageIcon,
    CrosshairIcon,
    CuboidIcon,
    RocketIcon
} from 'lucide-vue-next';
import { getRandomQuote } from '@/data/quotes';

// AR Event constants (matches @nativephp/ar Events export)
const ArEvents = {
    SessionStarted: 'NativePhp\\Ar\\Events\\ArSessionStarted',
    SessionStopped: 'NativePhp\\Ar\\Events\\ArSessionStopped',
    PlaneDetected: 'NativePhp\\Ar\\Events\\ArPlaneDetected',
    AnchorPlaced: 'NativePhp\\Ar\\Events\\ArAnchorPlaced',
    AnchorRemoved: 'NativePhp\\Ar\\Events\\ArAnchorRemoved',
    ImageDetected: 'NativePhp\\Ar\\Events\\ArImageDetected',
    TrackingStateChanged: 'NativePhp\\Ar\\Events\\ArTrackingStateChanged',
    HitTestResult: 'NativePhp\\Ar\\Events\\ArHitTestResult',
};

const randomQuote = getRandomQuote();

// AR State
const isSupported = ref<boolean | null>(null);
const isSessionActive = ref(false);
const trackingState = ref<string>('notAvailable');
const trackingReason = ref<string | null>(null);

// Configuration
const planeDetection = ref('both');
const lightEstimation = ref(true);
const showPlanes = ref(true);
const modelScale = ref(0.5);

// Available 3D models (loaded from public/models directory)
// Use full URLs so native AR view can download them
const getModelUrl = (path: string) => `${window.location.origin}${path}`;

const availableModels = ref([
    { name: 'Astronaut', url: getModelUrl('/models/Astronaut.glb') },
    { name: 'Astronaut 2', url: getModelUrl('/models/Astronaut (1).glb') },
    { name: 'Airplane', url: getModelUrl('/models/Small Airplane.glb') },
]);

// Detected data
const detectedPlanes = ref<Array<{
    id: string;
    type: string;
    center: number[];
    extent: number[];
}>>([]);

const placedAnchors = ref<Array<{
    id: string;
    position: number[];
    modelName?: string;
    modelUrl?: string;
    metadata: any;
}>>([]);

const lastHitTest = ref<{
    hit: boolean;
    position?: number[];
    planeId?: string;
    planeType?: string;
} | null>(null);

// Bridge call helper
async function bridgeCall(method: string, params: any = {}) {
    const response = await fetch('/_native/api/call', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ method, params })
    });

    const result = await response.json();

    if (result.status === 'error') {
        throw new Error(result.message || 'Native call failed');
    }

    const nativeResponse = result.data;
    if (nativeResponse && nativeResponse.data !== undefined) {
        return nativeResponse.data;
    }

    return nativeResponse;
}

// AR Functions
const checkSupport = async () => {
    try {
        const result = await bridgeCall('Ar.IsSupported');
        isSupported.value = result.supported;
    } catch (e) {
        console.error('Failed to check AR support:', e);
        isSupported.value = false;
    }
};

const startSession = async () => {
    try {
        const result = await bridgeCall('Ar.StartSession', {
            planeDetection: planeDetection.value,
            lightEstimation: lightEstimation.value,
            showPlanes: showPlanes.value,
            models: availableModels.value,
            modelScale: modelScale.value
        });
        if (result.started) {
            isSessionActive.value = true;
            trackingState.value = 'initializing';
        }
    } catch (e) {
        console.error('Failed to start AR session:', e);
    }
};

const stopSession = async () => {
    try {
        const result = await bridgeCall('Ar.StopSession');
        if (result.stopped) {
            isSessionActive.value = false;
            trackingState.value = 'notAvailable';
            detectedPlanes.value = [];
            placedAnchors.value = [];
        }
    } catch (e) {
        console.error('Failed to stop AR session:', e);
    }
};

const placeAnchor = async (x: number = 0.5, y: number = 0.5) => {
    try {
        const result = await bridgeCall('Ar.PlaceAnchor', {
            x,
            y,
            metadata: { placedAt: new Date().toISOString() }
        });
        if (result.anchorId) {
            // Anchor will be added via event
            console.log('Anchor placed:', result.anchorId);
        }
    } catch (e) {
        console.error('Failed to place anchor:', e);
    }
};

const removeAnchor = async (anchorId: string) => {
    try {
        await bridgeCall('Ar.RemoveAnchor', { anchorId });
    } catch (e) {
        console.error('Failed to remove anchor:', e);
    }
};

const performHitTest = async (x: number = 0.5, y: number = 0.5) => {
    try {
        const result = await bridgeCall('Ar.HitTest', { x, y });
        lastHitTest.value = result;
    } catch (e) {
        console.error('Hit test failed:', e);
    }
};

const refreshPlanes = async () => {
    try {
        const result = await bridgeCall('Ar.GetPlanes');
        if (result.planes) {
            detectedPlanes.value = result.planes;
        }
    } catch (e) {
        console.error('Failed to get planes:', e);
    }
};

// Event handlers
const handlePlaneDetected = (payload: any) => {
    const existingIndex = detectedPlanes.value.findIndex(p => p.id === payload.planeId);
    const plane = {
        id: payload.planeId,
        type: payload.type,
        center: payload.center,
        extent: payload.extent
    };

    if (existingIndex >= 0) {
        detectedPlanes.value[existingIndex] = plane;
    } else {
        detectedPlanes.value.push(plane);
    }
};

const handleAnchorPlaced = (payload: any) => {
    placedAnchors.value.push({
        id: payload.anchorId,
        position: payload.position,
        modelName: payload.modelName,
        modelUrl: payload.modelUrl,
        metadata: payload.metadata
    });
};

const handleAnchorRemoved = (payload: any) => {
    placedAnchors.value = placedAnchors.value.filter(a => a.id !== payload.anchorId);
};

const handleTrackingStateChanged = (payload: any) => {
    trackingState.value = payload.state;
    trackingReason.value = payload.reason || null;
};

const handleSessionStarted = (payload: any) => {
    console.log('AR Session started:', payload.sessionId);
    isSessionActive.value = true;
};

const handleSessionStopped = (payload: any) => {
    console.log('AR Session stopped');
    isSessionActive.value = false;
    trackingState.value = 'notAvailable';
};

// Computed
const trackingStatusColor = computed(() => {
    switch (trackingState.value) {
        case 'normal': return 'from-green-500 to-emerald-500';
        case 'limited': return 'from-yellow-500 to-orange-500';
        default: return 'from-gray-500 to-slate-500';
    }
});

const trackingStatusText = computed(() => {
    if (trackingState.value === 'limited' && trackingReason.value) {
        const reasons: Record<string, string> = {
            'initializing': 'Initializing...',
            'excessiveMotion': 'Move slower',
            'insufficientFeatures': 'Point at textured surface',
            'relocalizing': 'Relocalizing...'
        };
        return reasons[trackingReason.value] || trackingReason.value;
    }
    return trackingState.value;
});

const planeDetectionOptions = [
    { value: 'horizontal', label: 'Horizontal Only' },
    { value: 'vertical', label: 'Vertical Only' },
    { value: 'both', label: 'Both (Recommended)' }
];

// Format position for display
const formatPosition = (pos: number[]) => {
    return pos.map(v => v.toFixed(2)).join(', ');
};

// Lifecycle
onMounted(() => {
    checkSupport();

    // Register AR event listeners
    On(ArEvents.PlaneDetected, handlePlaneDetected);
    On(ArEvents.AnchorPlaced, handleAnchorPlaced);
    On(ArEvents.AnchorRemoved, handleAnchorRemoved);
    On(ArEvents.TrackingStateChanged, handleTrackingStateChanged);
    On(ArEvents.SessionStarted, handleSessionStarted);
    On(ArEvents.SessionStopped, handleSessionStopped);
});

onUnmounted(() => {
    // Cleanup event listeners
    Off(ArEvents.PlaneDetected, handlePlaneDetected);
    Off(ArEvents.AnchorPlaced, handleAnchorPlaced);
    Off(ArEvents.AnchorRemoved, handleAnchorRemoved);
    Off(ArEvents.TrackingStateChanged, handleTrackingStateChanged);
    Off(ArEvents.SessionStarted, handleSessionStarted);
    Off(ArEvents.SessionStopped, handleSessionStopped);

    // Stop session if active
    if (isSessionActive.value) {
        stopSession();
    }
});
</script>

<template>
    <AppLayout title="AR">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-violet-500 to-purple-600 dark:from-violet-600 dark:to-purple-700 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                <BoxIcon class="size-8 mr-2" />
                                Augmented Reality
                            </CardTitle>
                            <CardDescription class="text-lg text-white/90">
                                Place virtual objects in the real world using ARCore & ARKit!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area -->
            <div class="space-y-4 px-4">
                <!-- Support Status -->
                <Alert v-if="isSupported === false" variant="destructive">
                    <AlertCircleIcon class="size-4" />
                    <AlertTitle>AR Not Supported</AlertTitle>
                    <AlertDescription>
                        This device doesn't support AR experiences. You need an ARCore (Android) or ARKit (iOS) compatible device.
                    </AlertDescription>
                </Alert>

                <Alert v-else-if="isSupported === true" class="bg-gradient-to-r from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 border-green-200 dark:border-green-700">
                    <CheckCircle2Icon class="size-4 text-green-600" />
                    <AlertTitle class="text-green-800 dark:text-green-200">AR Supported</AlertTitle>
                    <AlertDescription class="text-green-700 dark:text-green-300">
                        Your device supports augmented reality experiences.
                    </AlertDescription>
                </Alert>

                <!-- Tracking Status (when session active) -->
                <Card v-if="isSessionActive" class="overflow-hidden">
                    <div :class="['p-4 text-white', `bg-gradient-to-r ${trackingStatusColor}`]">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <CrosshairIcon class="size-5" />
                                <span class="font-semibold">Tracking: {{ trackingStatusText }}</span>
                            </div>
                            <Badge variant="secondary" class="bg-white/20 text-white border-0">
                                {{ detectedPlanes.length }} planes
                            </Badge>
                        </div>
                    </div>
                </Card>

                <!-- AR Settings Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <LayersIcon class="size-5 mr-2" />
                            AR Settings
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Plane Detection -->
                        <div class="space-y-2">
                            <Label class="text-base font-semibold">Plane Detection</Label>
                            <Select v-model="planeDetection" :disabled="isSessionActive">
                                <SelectTrigger class="border-2">
                                    <SelectValue placeholder="Choose detection mode..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="option in planeDetectionOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Light Estimation Toggle -->
                        <div class="flex items-center justify-between space-x-2 p-4 rounded-lg bg-gradient-to-r from-amber-100 to-yellow-100 dark:from-amber-900/30 dark:to-yellow-900/30 border-2 border-amber-200 dark:border-amber-700">
                            <div class="space-y-0.5">
                                <Label class="text-base font-semibold">Light Estimation</Label>
                                <div class="text-sm text-muted-foreground">
                                    Adjust virtual objects to match real lighting
                                </div>
                            </div>
                            <Switch v-model="lightEstimation" :disabled="isSessionActive" />
                        </div>

                        <!-- Model Scale Slider -->
                        <div class="space-y-2">
                            <Label class="text-base font-semibold">Model Scale: {{ modelScale.toFixed(1) }}x</Label>
                            <input
                                type="range"
                                v-model.number="modelScale"
                                min="0.1"
                                max="2.0"
                                step="0.1"
                                :disabled="isSessionActive"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700"
                            />
                            <div class="flex justify-between text-xs text-muted-foreground">
                                <span>0.1x (small)</span>
                                <span>2.0x (large)</span>
                            </div>
                        </div>

                        <!-- Start/Stop Session Button -->
                        <Button
                            v-if="!isSessionActive"
                            @click="startSession"
                            :disabled="!isSupported"
                            class="py-6 w-full bg-gradient-to-br from-violet-500 to-purple-600 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <PlayIcon class="mr-2 size-7" />
                            Start AR Session
                        </Button>

                        <Button
                            v-else
                            @click="stopSession"
                            class="py-6 w-full bg-gradient-to-br from-red-500 to-pink-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                        >
                            <StopCircleIcon class="mr-2 size-7" />
                            Stop AR Session
                        </Button>
                    </CardContent>
                </Card>

                <!-- Available Models Preview -->
                <Card v-if="!isSessionActive" class="bg-gradient-to-br from-indigo-100 to-violet-100 dark:from-indigo-900/30 dark:to-violet-900/30 border-2 border-indigo-200 dark:border-indigo-700">
                    <CardHeader>
                        <CardTitle class="text-indigo-900 dark:text-indigo-100 flex items-center">
                            <CuboidIcon class="mr-2 size-6" />
                            3D Models to Place ({{ availableModels.length }})
                        </CardTitle>
                        <CardDescription class="text-indigo-700 dark:text-indigo-300">
                            These models will be available to select and place in AR
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div
                            v-for="(model, index) in availableModels"
                            :key="index"
                            class="flex items-center gap-3 p-3 rounded-lg bg-white/50 dark:bg-gray-800/50 border border-white/50"
                        >
                            <div class="size-10 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                                <RocketIcon v-if="model.name.includes('Airplane')" class="size-5 text-white" />
                                <BoxIcon v-else class="size-5 text-white" />
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold text-indigo-900 dark:text-indigo-100">{{ model.name }}</div>
                                <div class="text-xs text-muted-foreground truncate">{{ model.url }}</div>
                            </div>
                            <Badge class="bg-gradient-to-r from-indigo-500 to-violet-500 text-white border-0">
                                GLB
                            </Badge>
                        </div>
                        <p class="text-sm text-indigo-600 dark:text-indigo-400 mt-3">
                            💡 Select a model in AR view, then tap surfaces to place it
                        </p>
                    </CardContent>
                </Card>

                <!-- Actions (when session active) -->
                <Card v-if="isSessionActive" class="bg-zinc-50 dark:bg-zinc-800/50">
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <MapPinIcon class="size-5 mr-2" />
                            Actions
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <Button
                            @click="() => placeAnchor(0.5, 0.5)"
                            class="w-full py-4 bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0"
                        >
                            <MapPinIcon class="mr-2 size-5" />
                            Place Anchor at Center
                        </Button>

                        <Button
                            @click="() => performHitTest(0.5, 0.5)"
                            variant="outline"
                            class="w-full py-4 border-2"
                        >
                            <CrosshairIcon class="mr-2 size-5" />
                            Hit Test at Center
                        </Button>

                        <Button
                            @click="refreshPlanes"
                            variant="outline"
                            class="w-full py-4 border-2"
                        >
                            <LayersIcon class="mr-2 size-5" />
                            Refresh Planes
                        </Button>
                    </CardContent>
                </Card>

                <!-- Hit Test Result -->
                <Card
                    v-if="lastHitTest"
                    :class="[
                        'border-2',
                        lastHitTest.hit
                            ? 'bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 border-green-200 dark:border-green-700'
                            : 'bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900/30 dark:to-rose-900/30 border-red-200 dark:border-red-700'
                    ]"
                >
                    <CardHeader>
                        <CardTitle class="flex items-center">
                            <CrosshairIcon class="size-5 mr-2" />
                            Hit Test Result
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="lastHitTest.hit" class="space-y-2">
                            <div class="flex items-center gap-2">
                                <Badge class="bg-gradient-to-r from-green-500 to-emerald-500 text-white border-0">
                                    {{ lastHitTest.planeType }}
                                </Badge>
                            </div>
                            <div class="text-sm font-mono">
                                Position: [{{ formatPosition(lastHitTest.position!) }}]
                            </div>
                        </div>
                        <div v-else class="text-red-600 dark:text-red-400 font-semibold">
                            No surface detected at that position
                        </div>
                    </CardContent>
                </Card>

                <!-- Detected Planes -->
                <Card
                    v-if="detectedPlanes.length > 0"
                    class="bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 border-2 border-blue-200 dark:border-blue-700"
                >
                    <CardHeader>
                        <CardTitle class="text-blue-900 dark:text-blue-100 flex items-center">
                            <LayersIcon class="mr-2 size-6" />
                            Detected Planes ({{ detectedPlanes.length }})
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div
                            v-for="plane in detectedPlanes"
                            :key="plane.id"
                            class="p-3 rounded-lg bg-white/50 dark:bg-gray-800/50 border border-white/50"
                        >
                            <div class="flex items-center justify-between">
                                <Badge :class="[
                                    'border-0 text-white',
                                    plane.type === 'horizontal'
                                        ? 'bg-gradient-to-r from-blue-500 to-cyan-500'
                                        : 'bg-gradient-to-r from-purple-500 to-pink-500'
                                ]">
                                    {{ plane.type }}
                                </Badge>
                                <span class="text-xs text-muted-foreground font-mono">
                                    {{ plane.extent[0].toFixed(1) }}m x {{ plane.extent[1].toFixed(1) }}m
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Placed Anchors -->
                <Card
                    v-if="placedAnchors.length > 0"
                    class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 border-2 border-amber-200 dark:border-amber-700"
                >
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-amber-900 dark:text-amber-100 flex items-center">
                                <MapPinIcon class="mr-2 size-6" />
                                Placed Anchors ({{ placedAnchors.length }})
                            </CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div
                            v-for="anchor in placedAnchors"
                            :key="anchor.id"
                            class="p-3 rounded-lg bg-white/50 dark:bg-gray-800/50 border border-white/50"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
                                        <CuboidIcon class="size-4 text-white" />
                                    </div>
                                    <div>
                                        <div v-if="anchor.modelName" class="font-semibold text-amber-900 dark:text-amber-100">
                                            {{ anchor.modelName }}
                                        </div>
                                        <div class="text-sm font-mono text-muted-foreground">
                                            [{{ formatPosition(anchor.position) }}]
                                        </div>
                                    </div>
                                </div>
                                <Button
                                    @click="() => removeAnchor(anchor.id)"
                                    size="sm"
                                    variant="ghost"
                                    class="text-red-500 hover:text-red-700 hover:bg-red-100"
                                >
                                    <TrashIcon class="size-4" />
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Instructions Card -->
                <Card class="bg-gradient-to-br from-slate-100 to-gray-100 dark:from-slate-800/50 dark:to-gray-800/50 border-2 border-slate-200 dark:border-slate-700">
                    <CardHeader>
                        <CardTitle class="flex items-center text-slate-900 dark:text-slate-100">
                            <ImageIcon class="mr-2 size-5" />
                            How to Use AR
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2 text-sm text-slate-700 dark:text-slate-300">
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Start an AR session to begin camera tracking</li>
                            <li>Point your device at flat surfaces (tables, floors, walls)</li>
                            <li>Wait for planes to be detected (shown in blue/magenta)</li>
                            <li><strong>Select a 3D model</strong> from the picker at the bottom</li>
                            <li><strong>Tap on detected surfaces</strong> to place the selected model</li>
                            <li>Switch models anytime to place different objects</li>
                        </ol>
                    </CardContent>
                </Card>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>