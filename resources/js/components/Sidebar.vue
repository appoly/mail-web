<script setup lang="ts">
import { Sheet, SheetContent } from '@/components/ui/sheet'
import SidebarContent from './SidebarContent.vue'

defineProps<{
    searchQuery: string
    filters: Record<string, any>
    isOpen: boolean
    isMobile: boolean
}>()

defineEmits(['update:searchQuery', 'update:filters', 'update:isOpen'])
</script>

<template>
    <div>
        <Sheet v-if="isMobile" :open="isOpen" @update:open="$emit('update:isOpen', $event)">
            <SheetContent side="left" class="p-0 w-[280px] sm:w-[320px]">
                <div class="flex flex-col h-full">
                    <SidebarContent :search-query="searchQuery" :filters="filters" :is-mobile="isMobile"
                        @update:search-query="$emit('update:searchQuery', $event)"
                        @update:filters="$emit('update:filters', $event)"
                        @close-sidebar="$emit('update:isOpen', false)" />
                </div>
            </SheetContent>
        </Sheet>

        <div v-else class="w-64 border-r bg-card flex-col h-full">
            <SidebarContent :search-query="searchQuery" :filters="filters" :is-mobile="isMobile"
                @update:search-query="$emit('update:searchQuery', $event)"
                @update:filters="$emit('update:filters', $event)" />
        </div>
    </div>
</template>

