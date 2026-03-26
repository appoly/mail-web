<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronUp, Eye, X } from 'lucide-vue-next';
import { ref } from 'vue';

const isPreviewOpen = ref<boolean>(false);
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="flex-1 overflow-hidden">
            <slot></slot>
        </div>

        <!-- Toggle bar -->
        <div class="bg-card fixed inset-x-0 bottom-0 z-10 border-t shadow-sm">
            <div class="flex items-center justify-center py-2">
                <Button
                    variant="ghost"
                    size="sm"
                    @click="isPreviewOpen = !isPreviewOpen"
                    class="gap-2 text-sm font-medium"
                >
                    <template v-if="isPreviewOpen">
                        <X class="h-3.5 w-3.5" />
                        Close Preview
                    </template>
                    <template v-else>
                        <Eye class="h-3.5 w-3.5" />
                        Preview Email
                        <ChevronUp class="h-3 w-3 opacity-50" />
                    </template>
                </Button>
            </div>
        </div>

        <!-- Sliding panel -->
        <div
            :class="[
                'bg-card fixed inset-x-0 bottom-0 z-20 transform border-t shadow-lg transition-transform duration-300 ease-[cubic-bezier(0.25,1,0.5,1)]',
                isPreviewOpen ? 'translate-y-0' : 'translate-y-full',
            ]"
            style="height: 82vh"
        >
            <div class="flex items-center justify-between border-b px-4 py-3">
                <h3 class="text-sm font-semibold tracking-tight">Email Preview</h3>
                <Button variant="ghost" size="icon" @click="isPreviewOpen = false" class="h-7 w-7">
                    <X class="h-4 w-4" />
                    <span class="sr-only">Close preview</span>
                </Button>
            </div>
            <div class="h-[calc(82vh-49px)] overflow-hidden">
                <slot name="preview"></slot>
            </div>
        </div>
    </div>
</template>
