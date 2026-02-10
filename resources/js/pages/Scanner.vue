<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import Quote from '@/components/Quote.vue';
import { onMounted, onUnmounted, ref } from 'vue';
import { On, Off, Scanner, Events } from '#nativephp'
import { QrCodeIcon, XIcon } from 'lucide-vue-next';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const scannedData = ref('');
const scannedFormat = ref('');
const streaming = ref(false);
const requestedFormat = ref('all');
const scannedCodes = ref<Array<{ data: string; format: string; timestamp: string }>>([]);

const formatOptions = [
    { value: 'qr_code', label: 'QR Code' },
    { value: 'ean_13', label: 'EAN-13' },
    { value: 'ean_8', label: 'EAN-8' },
    { value: 'code_128', label: 'Code 128' },
    { value: 'code_39', label: 'Code 39' },
    { value: 'upca', label: 'UPC-A' },
    { value: 'upce', label: 'UPC-E' },
    { value: 'data_matrix', label: 'Data Matrix' },
    { value: 'pdf417', label: 'PDF417' },
    { value: 'aztec', label: 'Aztec' },
    { value: 'codabar', label: 'Codabar' },
    { value: 'itf', label: 'ITF' },
    { value: 'all', label: 'All Formats' },
];

const scanCode = async () =>  {
    await Scanner
        .scan()
        .prompt(streaming.value ? 'Scan codes continuously' : 'Scan a code')
        .formats([requestedFormat.value])
        .continuous(streaming.value);
};

const handleCodeScanned = (payload: any) => {
    if (streaming.value) {
        scannedCodes.value.push({
            data: payload.data || '',
            format: payload.format || 'Unknown',
            timestamp: new Date().toLocaleTimeString('en-US', { hour12: false }),
        });
        // Don't restart scanner - continuous mode should keep it open
    } else {
        scannedData.value = payload.data || '';
        scannedFormat.value = payload.format || 'Unknown';
    }
};

const clearScans = () => {
    scannedCodes.value = [];
    scannedData.value = '';
    scannedFormat.value = '';
};

onMounted(() => {
    On(Events.Scanner.CodeScanned, handleCodeScanned)
});

onUnmounted(() => {
    Off(Events.Scanner.CodeScanned, handleCodeScanned)
});
</script>

<template>
    <AppLayout title="Scanner">
        <div class="space-y-4">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-br from-indigo-500 to-blue-500 dark:from-indigo-600 dark:to-blue-600 text-white border-0 pb-8 pt-[var(--inset-top)]">
                <CardHeader class="space-y-3">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl flex items-center space-x-6 pt-2">
                                Scanner
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Scan QR codes and barcodes with lightning speed and precision!
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>

            <!-- Main Content Area with Horizontal Padding -->
            <div class="space-y-4 px-4">
                <!-- Scanner Settings Card -->
                <Card class="bg-zinc-50 dark:bg-zinc-800/50">
                <CardContent class="space-y-4 ">
                    <div class="flex items-center justify-between space-x-2 p-4 rounded-lg bg-gradient-to-r from-purple-100 to-indigo-100 dark:from-purple-900/30 dark:to-indigo-900/30 border-2 border-purple-200 dark:border-purple-700">
                        <div class="space-y-0.5">
                            <Label class="text-base font-semibold">Continuous Scanning</Label>
                            <div class="text-sm text-muted-foreground">
                                Enable to scan multiple codes in succession
                            </div>
                        </div>
                        <Switch v-model="streaming" />
                    </div>

                    <div class="space-y-2">
                        <Label class="text-base font-semibold">Format</Label>
                        <Select v-model="requestedFormat">
                            <SelectTrigger class="border-2">
                                <SelectValue placeholder="Choose format..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="option in formatOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <Button
                        @click="scanCode"
                        class="py-6 w-full bg-gradient-to-br from-indigo-500 to-blue-500 text-white border-0 shadow-lg transition-all text-xl font-semibold"
                    >
                        <QrCodeIcon class="mr-2 size-7"/>
                        {{ streaming ? 'Start Continuous Scan' : 'Scan Code' }}
                    </Button>
                </CardContent>
            </Card>

            <!-- Scanned Codes List (Continuous Mode) -->
            <Card v-if="streaming && scannedCodes.length > 0" class="bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 border-2 border-green-200 dark:border-green-700">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-green-900 dark:text-green-100 flex items-center">
                            <QrCodeIcon class="mr-2 size-6"/>
                            Scanned Codes ({{ scannedCodes.length }})
                        </CardTitle>
                        <Button @click="clearScans" size="sm" class="bg-gradient-to-r from-red-500 to-pink-500 text-white border-0">
                            <XIcon class="mr-2 size-4"/>
                            Clear
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="space-y-2">
                    <div
                        v-for="(scan, index) in scannedCodes"
                        :key="index"
                        class="p-4 rounded-lg bg-white/50 dark:bg-gray-800/50 border-2 border-white/50 backdrop-blur-sm"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <Badge class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0">{{ scan.format.toUpperCase().replace(/_/g, ' ') }}</Badge>
                                    <span class="text-xs text-muted-foreground font-semibold">{{ scan.timestamp }}</span>
                                </div>
                                <div class="font-mono text-sm break-all font-semibold">{{ scan.data }}</div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

                <!-- Single Scan Result -->
                <Card v-if="!streaming && scannedData" class="bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900/30 dark:to-yellow-900/30 border-2 border-amber-200 dark:border-amber-700">
                    <CardHeader>
                        <CardTitle class="text-amber-900 dark:text-amber-100 flex items-center">
                            <QrCodeIcon class="mr-2 size-6"/>
                            Scan Result
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-base font-semibold">Format</Label>
                            <div class="mt-1">
                                <Badge class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0 text-base px-3 py-1">{{ scannedFormat.toUpperCase().replace(/_/g, ' ') }}</Badge>
                            </div>
                        </div>

                        <div>
                            <Label class="text-base font-semibold">Data</Label>
                            <div class="mt-1 p-4 rounded-lg bg-white/50 dark:bg-gray-800/50 border-2 border-white/50 backdrop-blur-sm">
                                <div class="font-mono text-sm break-all font-semibold">{{ scannedData }}</div>
                            </div>
                        </div>

                        <Button @click="clearScans" class="w-full py-4 bg-gradient-to-r from-red-500 to-pink-500 text-white border-0 text-xl">
                            <XIcon class="mr-2 size-4"/>
                            Clear Result
                        </Button>
                    </CardContent>
                </Card>

                <!-- Quote -->
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" />
            </div>
        </div>
        <div class="pb-32"></div>
    </AppLayout>
</template>
