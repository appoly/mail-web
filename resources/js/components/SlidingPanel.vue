<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronDown as ChevronDownIcon, ChevronUp as ChevronUpIcon, X as XIcon } from 'lucide-vue-next';
import { ref } from 'vue';

const isPreviewOpen = ref<boolean>(false);
</script>

<template>
    <div class="flex h-full flex-col">
        <div class="flex-1 overflow-hidden">
            <slot></slot>
        </div>
        <div class="bg-background fixed inset-x-0 bottom-0 z-10 border-t">
            <div class="flex items-center justify-center p-2">
                <Button variant="ghost" size="sm" @click="isPreviewOpen = !isPreviewOpen">
                    <template v-if="isPreviewOpen">
                        <ChevronDownIcon class="mr-2 h-4 w-4" />
                        Close Preview
                    </template>
                    <template v-else>
                        <ChevronUpIcon class="mr-2 h-4 w-4" />
                        Open Preview
                    </template>
                </Button>
            </div>
        </div>
        <div
            :class="[
                'bg-background fixed inset-x-0 bottom-0 z-20 transform border-t transition-transform duration-300 ease-in-out',
                isPreviewOpen ? 'translate-y-0' : 'translate-y-full',
            ]"
            style="height: 80vh"
        >
            <div class="flex items-center justify-between border-b p-4">
                <h3 class="font-medium">Email Preview</h3>
                <Button variant="ghost" size="icon" @click="isPreviewOpen = false">
                    <XIcon class="h-5 w-5" />
                    <span class="sr-only">Close preview</span>
                </Button>
            </div>
            <div class="h-[calc(80vh-57px)] overflow-hidden">
                <slot name="preview"></slot>
            </div>
        </div>
    </div>
</template>
