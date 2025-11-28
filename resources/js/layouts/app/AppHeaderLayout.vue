<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppHeader from '@/components/AppHeader.vue';
import AppShell from '@/components/AppShell.vue';
import type { BreadcrumbItemType } from '@/types';
import { edgeBuilder } from '#nativephp';
import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { home } from '@/routes';
import demo from '@/routes/demo';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    title: 'NativePHP',
});


</script>

<template>
    <AppShell class="flex-col">
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>
