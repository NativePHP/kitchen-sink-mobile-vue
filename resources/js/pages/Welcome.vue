<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    QrCode,
    Volume2,
    Video,
    Camera,
    Images,
    Fingerprint,
    MapPin,
    Smartphone,
    Lightbulb,
    Box
} from 'lucide-vue-next';
import Quote from '@/components/Quote.vue';
import { getRandomQuote } from '@/data/quotes';

const randomQuote = getRandomQuote();

const featuredDemos = [
    {
        title: 'Camera',
        icon: Camera,
        href: '/camera',
        gradient: 'from-blue-500 to-cyan-500 ',
    },
    {
        title: 'Video',
        icon: Video,
        href: '/video',
        gradient: 'from-red-500 to-pink-500 ',
    },
    {
        title: 'Gallery',
        icon: Images,
        href: '/gallery',
        gradient: 'from-purple-500 to-violet-500',
    },
    {
        title: 'Microphone',
        icon: Volume2,
        href: '/microphone',
        gradient: 'from-orange-500 to-amber-500 ',
    },
    {
        title: 'Scanner',
        icon: QrCode,
        href: '/scanner',
        gradient: 'from-indigo-500 to-blue-500',
    },
    {
        title: 'Location',
        icon: MapPin,
        href: '/geolocation',
        gradient: 'from-green-500 to-emerald-500',
    },
    {
        title: 'Biometrics',
        icon: Fingerprint,
        href: '/biometrics',
        gradient: 'from-teal-500 to-cyan-500',
    },
    {
        title: 'Info',
        icon: Smartphone,
        href: '/device',
        gradient: 'from-slate-500 to-gray-500',
    },
    {
        title: 'AR',
        icon: Box,
        href: '/ar',
        gradient: 'from-violet-500 to-purple-600',
    },
];
</script>

<template>
    <AppLayout>
        <Head title="NativePHP Demos" />
        <div class="space-y-4 bg-white min-h-screen">
            <!-- Hero Card -->
            <div
                class="bg-gradient-to-br from-blue-900  to-purple-600 text-white  pt-[var(--inset-top)]"
            >
                <CardHeader class="space-y-3 px-4">
                    <div class="flex items-start gap-4">
                        <div class="space-y-3">
                            <CardTitle class="text-white text-3xl pt-2 flex items-center font-black">
                                <p>NativePHP v2</p>
                                <svg class="size-6 ml-2 animate-bounce" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240.48 208.27"><title>Vue.js logo</title><path d="M539.77,407.87,512,456l-27.77-48.1H391.76L512,616.13,632.24,407.87Z" transform="translate(-391.76 -407.87)" style="fill:#41b883"/><path d="M539.77,407.87,512,456l-27.77-48.1H439.86l72.14,125,72.14-125Z" transform="translate(-391.76 -407.87)" style="fill:#34495e"/></svg>
                            </CardTitle>
                            <CardDescription class="text-lg text-white">
                                Explore the full power of native mobile capabilities built with Vue and Inertia.
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
            </div>
            <div class="px-4 relative w-full">
                <div class="space-y-1 ">
                    <div class="absolute top-0 right-0 -mt-5">
                        <Lightbulb class="size-40 text-yellow-400/10"/>
                    </div>
                    <p class="font-bold text-xl text-black ">
                        E.D.G.E.
                    </p>
                    <p class="text-base text-black leading-relaxed ">
                        Introducing Element Definition and Generation Engine, NativePHP's innovative rendering engine that transforms Blade components into native UI elements.
                    </p>
                </div>
            </div>
            <!-- Featured Demos -->
            <div class="px-4 space-y-4 -mx-4">
                <div
                    class="flex gap-3 overflow-x-auto pb-4 pl-4 pr-4 my-10 snap-x snap-mandatory scroll-smooth"
                    style="scrollbar-width: none; -ms-overflow-style: none;"
                >
                    <Card
                        v-for="demo in featuredDemos"
                        :key="demo.href"
                        :class="`bg-gradient-to-br ${demo.gradient} hover:shadow-lg snap-center snap-always transition-shadow cursor-pointer border-0 size-40 rounded-lg shrink-0`"
                    >
                        <Link :href="demo.href" class="block h-full">
                            <CardHeader class="p-2">
                                <div class="flex flex-col items-center justify-center text-center gap-1.5 h-full">
                                    <div
                                        class="size-16 rounded-full bg-white/30 dark:bg-white/20 flex items-center justify-center"
                                    >
                                        <component :is="demo.icon" class="size-12 text-white" />
                                    </div>
                                    <CardTitle class="text-sm font-bold text-white leading-tight">
                                        {{ demo.title }}
                                    </CardTitle>
                                </div>
                            </CardHeader>
                        </Link>
                    </Card>
                </div>
            </div>
            <!-- Quote -->
            <div class="px-4">
                <Quote :quote="randomQuote.quote" :author="randomQuote.author" :force-light-mode="true" />
            </div>
            <div class="pb-32"></div>
        </div>
    </AppLayout>
</template>
