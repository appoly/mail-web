<script setup lang="ts">
import { Sheet, SheetContent } from '@/components/ui/sheet';
import SidebarContent from './SidebarContent.vue';

defineProps<{
    searchQuery: string;
    filters: Record<string, any>;
    isOpen: boolean;
    isMobile: boolean;
}>();

defineEmits(['update:searchQuery', 'update:filters', 'update:isOpen', 'update:settings']);
</script>

<template>
    <div>
        <Sheet v-if="isMobile" :open="isOpen" @update:open="$emit('update:isOpen', $event)">
            <SheetContent side="left" class="w-[280px] p-0 sm:w-[320px]">
                <div class="flex h-full flex-col">
                    <SidebarContent
                        :search-query="searchQuery"
                        :filters="filters"
                        :is-mobile="isMobile"
                        @update:search-query="$emit('update:searchQuery', $event)"
                        @update:filters="$emit('update:filters', $event)"
                        @update:settings="$emit('update:settings', $event)"
                        @close-sidebar="$emit('update:isOpen', false)"
                    />
                </div>
            </SheetContent>
        </Sheet>

        <div v-else class="bg-card h-full w-64 flex-col border-r">
            <SidebarContent
                :search-query="searchQuery"
                :filters="filters"
                :is-mobile="isMobile"
                @update:search-query="$emit('update:searchQuery', $event)"
                @update:filters="$emit('update:filters', $event)"
                @update:settings="$emit('update:settings', $event)"
            />
        </div>
    </div>
</template>
