<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Quote as QuoteIcon } from 'lucide-vue-next'

const props = defineProps<{
  quote: string
  author: string
  class?: HTMLAttributes['class']
  forceLightMode?: boolean
}>()
</script>

<template>
  <div
    :class="
      cn(
        'relative rounded-xl border bg-gradient-to-br from-slate-50 to-zinc-100 p-6 shadow-sm',
        !forceLightMode && 'dark:from-slate-900 dark:to-zinc-900',
        props.class,
      )
    "
  >
    <div class="flex flex-col gap-4">
      <QuoteIcon :class="cn('size-8 text-slate-400', !forceLightMode && 'dark:text-slate-600')" />
      <blockquote :class="cn('text-lg leading-relaxed text-slate-700 italic', !forceLightMode && 'dark:text-slate-300')">
        "{{ quote }}"
      </blockquote>
      <footer :class="cn('text-sm font-medium text-slate-600', !forceLightMode && 'dark:text-slate-400')">
        — {{ author }}
      </footer>
    </div>
  </div>
</template>