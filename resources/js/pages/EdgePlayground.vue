<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { LayoutIcon, PlayIcon, TrashIcon } from 'lucide-vue-next';
import { ref } from 'vue';
import { Edge, BridgeCall } from '#nativephp';

const componentType = ref('fab');
const componentData = ref('{\n  "icon": "add",\n  "label": "Create",\n  "url": "/create"\n}');
const lastResponse = ref<any>(null);
const error = ref<string | null>(null);
const logs = ref<string[]>([]);

const addLog = (message: string) => {
    const timestamp = new Date().toLocaleTimeString();
    logs.value.unshift(`[${timestamp}] ${message}`);
    if (logs.value.length > 20) {
        logs.value.pop();
    }
};

const setEdge = async () => {
    try {
        error.value = null;
        const data = JSON.parse(componentData.value);
        const component = { type: componentType.value, data };
        addLog(`Setting Edge: ${JSON.stringify(component)}`);

        const result = await Edge.set(component);
        lastResponse.value = result;
        addLog(`Response: ${JSON.stringify(result)}`);
    } catch (e: any) {
        error.value = e.message;
        addLog(`Error: ${e.message}`);
    }
};

const setEdgeSync = () => {
    try {
        error.value = null;
        const data = JSON.parse(componentData.value);
        const component = { type: componentType.value, data };
        addLog(`Setting Edge (sync): ${JSON.stringify(component)}`);

        Edge.setSync(component);
        addLog(`Sync call completed`);
    } catch (e: any) {
        error.value = e.message;
        addLog(`Error: ${e.message}`);
    }
};

const clearEdge = async () => {
    try {
        error.value = null;
        addLog(`Clearing Edge...`);
        const result = await Edge.clear();
        lastResponse.value = result;
        addLog(`Cleared. Response: ${JSON.stringify(result)}`);
    } catch (e: any) {
        error.value = e.message;
        addLog(`Error: ${e.message}`);
    }
};

const rawBridgeCall = async () => {
    try {
        error.value = null;
        const data = JSON.parse(componentData.value);
        const component = { type: componentType.value, data };
        addLog(`Raw BridgeCall: Edge.Set with ${JSON.stringify(component)}`);

        const result = await BridgeCall('Edge.Set', { components: [component] });
        lastResponse.value = result;
        addLog(`Response: ${JSON.stringify(result)}`);
    } catch (e: any) {
        error.value = e.message;
        addLog(`Error: ${e.message}`);
    }
};

const presets = [
    {
        name: 'Top Bar',
        type: 'top_bar',
        data: {
            title: 'My App',
            subtitle: 'Welcome',
            background_color: '#6366f1',
            text_color: '#ffffff',
        }
    },
    {
        name: 'FAB (Add)',
        type: 'fab',
        data: {
            icon: 'add',
            label: 'Add',
            url: '/add',
            container_color: '#10b981',
            content_color: '#ffffff',
        }
    },
    {
        name: 'FAB (Edit)',
        type: 'fab',
        data: {
            icon: 'edit',
            url: '/edit',
            size: 'small',
            position: 'end',
        }
    },
    {
        name: 'Bottom Nav',
        type: 'bottom_nav',
        data: {
            label_visibility: 'labeled',
            children: [
                { type: 'bottom_nav_item', data: { id: 'home', icon: 'home', url: '/', label: 'Home', active: true } },
                { type: 'bottom_nav_item', data: { id: 'search', icon: 'search', url: '/search', label: 'Search' } },
                { type: 'bottom_nav_item', data: { id: 'profile', icon: 'person', url: '/profile', label: 'Profile' } },
            ]
        }
    },
];

const applyPreset = (preset: typeof presets[0]) => {
    componentType.value = preset.type;
    componentData.value = JSON.stringify(preset.data, null, 2);
    addLog(`Applied preset: ${preset.name}`);
};

const clearLogs = () => {
    logs.value = [];
};
</script>

<template>
    <AppLayout title="Edge Playground">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-violet-600 to-purple-600 dark:from-violet-700 dark:to-purple-700 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                <LayoutIcon class="size-8 mr-2" />
                                Edge Playground
                            </CardTitle>
                            <CardDescription class="text-lg text-white/90">
                                Experiment with native Edge components from Vue
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area -->
            <div class="space-y-4 px-4">
                <!-- Presets Card -->
                <Card class="bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 border-2 border-amber-200 dark:border-amber-700">
                    <CardHeader>
                        <CardTitle class="text-amber-900 dark:text-amber-100">Quick Presets</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-2">
                            <Button
                                v-for="preset in presets"
                                :key="preset.name"
                                variant="outline"
                                size="sm"
                                @click="applyPreset(preset)"
                                class="bg-white/50 dark:bg-gray-800/50"
                            >
                                {{ preset.name }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Edge Control Card -->
                <Card class="bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 border-2 border-blue-200 dark:border-blue-700">
                    <CardHeader>
                        <CardTitle class="text-blue-900 dark:text-blue-100">Edge Component</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label>Component Type</Label>
                            <Input v-model="componentType" placeholder="StatusBar, NavigationBar, etc." />
                        </div>

                        <div class="space-y-2">
                            <Label>Component Data (JSON)</Label>
                            <textarea
                                v-model="componentData"
                                class="w-full h-24 p-3 rounded-md border bg-white/50 dark:bg-gray-800/50 font-mono text-sm"
                                placeholder='{"color": "#ff0000"}'
                            />
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <Button @click="setEdge" class="bg-blue-600 hover:bg-blue-700">
                                <PlayIcon class="size-4 mr-2" />
                                Set Edge (async)
                            </Button>
                            <Button @click="setEdgeSync" variant="outline">
                                Set Edge (sync)
                            </Button>
                            <Button @click="rawBridgeCall" variant="outline">
                                Raw BridgeCall
                            </Button>
                            <Button @click="clearEdge" variant="destructive">
                                <TrashIcon class="size-4 mr-2" />
                                Clear Edge
                            </Button>
                        </div>

                        <div v-if="error" class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg text-red-700 dark:text-red-300 text-sm">
                            {{ error }}
                        </div>
                    </CardContent>
                </Card>

                <!-- Response Card -->
                <Card v-if="lastResponse" class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900/30 dark:to-teal-900/30 border-2 border-emerald-200 dark:border-emerald-700">
                    <CardHeader>
                        <CardTitle class="text-emerald-900 dark:text-emerald-100">Last Response</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <pre class="p-3 bg-white/50 dark:bg-gray-800/50 rounded-lg text-sm overflow-auto">{{ JSON.stringify(lastResponse, null, 2) }}</pre>
                    </CardContent>
                </Card>

                <!-- Logs Card -->
                <Card class="bg-gradient-to-br from-gray-100 to-slate-100 dark:from-gray-900/30 dark:to-slate-900/30 border-2 border-gray-200 dark:border-gray-700">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <CardTitle class="text-gray-900 dark:text-gray-100">Logs</CardTitle>
                        <Button variant="ghost" size="sm" @click="clearLogs">Clear</Button>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-1 max-h-64 overflow-auto">
                            <div
                                v-for="(log, index) in logs"
                                :key="index"
                                class="p-2 bg-white/50 dark:bg-gray-800/50 rounded text-xs font-mono"
                            >
                                {{ log }}
                            </div>
                            <div v-if="logs.length === 0" class="text-sm text-muted-foreground text-center py-4">
                                No logs yet. Try setting an Edge component!
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>