<script setup lang="ts">
import { ref } from 'vue';
import {
    ChevronUp as ChevronUpIcon,
    ChevronDown as ChevronDownIcon,
    X as XIcon
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const isPreviewOpen = ref<boolean>(false);
</script>


<template>
    <div class="flex flex-col h-full">
        <div class="flex-1 overflow-hidden">
            <slot></slot>
        </div>
        <div class="fixed inset-x-0 bottom-0 z-10 bg-background border-t">
            <div class="flex items-center justify-center p-2">
                <Button variant="ghost" size="sm" @click="isPreviewOpen = !isPreviewOpen">
                    <template v-if="isPreviewOpen">
                        <ChevronDownIcon class="h-4 w-4 mr-2" />
                        Close Preview
                    </template>
                    <template v-else>
                        <ChevronUpIcon class="h-4 w-4 mr-2" />
                        Open Preview
                    </template>
                </Button>
            </div>
        </div>
        <div :class="[
            'fixed inset-x-0 bottom-0 z-20 bg-background border-t transition-transform duration-300 ease-in-out transform',
            isPreviewOpen ? 'translate-y-0' : 'translate-y-full'
        ]" style="height: 80vh">
            <div class="flex items-center justify-between p-4 border-b">
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
